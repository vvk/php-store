<?php

include_once 'constants.php';

include 'cache.php';

function &get_client() {
    $mysqli = new mysqli(
        MYSQL_HOST,
        MYSQL_USER,
        MYSQL_PASSWORD,
        DATABASE
    );

    return $mysqli;
}

function _free_resources(&$stmt, &$mysqli) {
    if (!empty($stmt)) {
        $stmt->close();
    }

    if (!empty($mysqli)) {
        $mysqli->close();
    }
}

function validate_query(&$query, &$mysqli = null) {
    if (!$query) {
        $msg = 'Failed to create MySQLi query.';
        if (!empty($mysqli)) {
            $msg .= $mysqli->error;
        }
        error_log($msg.'. '.debug_backtrace());
        return FALSE;
    }

    return TRUE;
}

function load_item($id) {
    $mysqli = get_client();

    $stmt = $mysqli->prepare('SELECT * FROM items WHERE id = ?');

    if (!validate_query($stmt, $mysqli)) {
        return FALSE;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();
    _free_resources($stmt, $mysqli);

    if (!$result) {
        error_log("Could not retrieve item $id.");
        return FALSE;
    }

    $item = $result->fetch_assoc();

    if (!$item) {
        error_log("Item ID:$id not found.");
        return FALSE;
    }

    return $item;
}

function get_item($id) {
    $item = get_item_from_cache($id);
    if ($item) {
        return $item;
    }

    $item = load_item($id);
    if (!$item) {
        return FALSE;
    }

    put_item_to_cache($id, $item);
    return $item;
}

function load_total_items() {
    $mysqli = get_client();
    $query = $mysqli->query('SELECT count(id) FROM items');

    if (!validate_query($query, $mysqli)) {
        return FALSE;
    }

    $total = $query->fetch_row()[0];
    _free_resources($query, $mysqli);

    return $total;
}

function get_total_items() {
    $total = get_from_cache('total_items');

    if ($total) {
        return $total;
    }

    $total = load_total_items();
    put_to_cache('total_items', $total);

    return $total;
}

function load_items($ids) {
    if (!is_array($ids)) {
        $ids = array($ids);
    }

    $mysqli = get_client();
    $stmt = $mysqli->prepare('SELECT * FROM items WHERE id = ?');

    if (!validate_query($stmt, $mysqli)) {
        return FALSE;
    }

    $items = array();

    foreach ($ids as $id) {
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            error_log("Could not get statement result.");
            return FALSE;
        }

        $items[$id] = $result->fetch_assoc();
    }

    _free_resources($stmt, $mysqli);

    return $items;
}

function get_items($ids) {
    $items = get_items_from_cache($ids);

    if (!$items) {
        $items = load_items($ids);

        if (!$items) {
            return FALSE;
        }

        put_items_to_cache($items);
        return $items;
    }

    // Since get_items_from_cache used 'getMulti' only found items are returned,
    // so we have to check all required ids for existence and load not-cached-yet ones.
    $items_to_load = array();
    $restore_order = FALSE;

    foreach ($ids as $id) {
        if (empty($items[$id])) {
            $items_to_load[] = $id;
        }
    }

    if (!empty($items_to_load)) {
        $loaded_items = load_items($items_to_load);

        if (!$loaded_items) {
            return FALSE;
        }

        $items = $items + $loaded_items;
        $restore_order = TRUE;
    }

    // If not all items were found in cache and we had to load
    // items from database additionally we could lose requested order of ids,
    // so now we have to restore original order.
    if ($restore_order) {
        $tmp_items = array();

        foreach ($ids as $id) {
            $tmp_items[$id] = $items[$id];
        }

        $items = $tmp_items;
    }

    return $items;
}

function create_item($name, $price, $description = null, $image = null) {
    if (empty($name)) {
        error_log('Empty name for the item.');
        error_log(debug_backtrace());
        return false;
    }

    if ($price < 0) {
        error_log('Negative price.');
        error_log(debug_backtrace());
        return false;
    }

    $mysqli = get_client();
    $stmt = $mysqli->prepare('INSERT INTO items(name, description, price, image) VALUES (?, ?, ?, ?)');

    if (!validate_query($stmt, $mysqli)) {
        return false;
    }

    $stmt->bind_param('ssds', $name, $description, $price, $image);
    if (!$stmt->execute()) {
        error_log($mysqli->error);
        return false;
    }

    $id = $stmt->insert_id;
    $item = array(
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'description' => $description,
        'image' => $image);
    put_item_to_cache($id, $item);

    return $id;
}

function upload_image(&$generated_name_output) {
    if (empty($_FILES['image']) || !$_FILES['image']['size']) {
        return true;
    }

    $image = $_FILES['image'];
    $extension = pathinfo(basename($image['name']),PATHINFO_EXTENSION);

    $generated_image_name = uniqid().".$extension";
    $images_directory = getcwd().'/images';

    if (!file_exists($images_directory)) {
        mkdir($images_directory, 0777, true);
    }

    $destination = "$images_directory/$generated_image_name";

    if (!move_uploaded_file($image['tmp_name'], $destination)) {
        error_log('Something went wrong while the item image was being uploaded.');
        error_log(debug_backtrace());
    }

    $imagick = new Imagick($destination);
    $imagick->thumbnailImage(64, 64, true);
    if (!$imagick->writeImage("$images_directory/t_$generated_image_name")) {
        error_log('Could not save image.');
        error_log(debug_backtrace());
        return false;
    }

    $generated_name_output = $generated_image_name;

    return true;
}

function update_item($id, $updates) {
    $mysqli = get_client();
    $lambda = function ($key) {
        return "$key = ?";
    };
    $keys = implode(',', array_map($lambda, array_keys($updates)));
    $stmt = $mysqli->prepare("UPDATE items SET $keys WHERE id = ?");

    if (!validate_query($stmt, $mysqli)) {
        return false;
    }

    $types = "";
    $values = array();
    foreach ($updates as $key => $value) {
        $types .= ($key == 'price' ? 'd' : 's');
        $values[] = $value;
    }
    $types .= 'i';
    $values[] = $id;
    $stmt->bind_param($types, ...$values);

    if (!$stmt->execute()) {
        error_log("Could not update item ID:$id: ".$mysqli->error);
        return false;
    }
    _free_resources($stmt, $mysqli);

    if (!invalidate_cache($id)) {
        error_log("Could not delete item ID:$id from cache: ".get_cache()->getResultMessage());
        return false;
    }

    return true;
}

function delete_item($id) {
    $mysqli = get_client();
    $stmt = $mysqli->prepare('DELETE FROM items WHERE id = ?');
    if (!validate_query($stmt, $mysqli)) {
        return false;
    }

    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        error_log("Could not delete item ID:$id: ".$mysqli->error);
        return false;
    }
    _free_resources($stmt, $mysqli);

    if (!invalidate_cache($id)) {
        error_log("Could not delete item ID:$id from cache: ".get_cache()->getResultMessage());
        return false;
    }

    return true;
}
function get_items_ids_sorted($limit, $offset = 0, $sort_by = DEFAULT_SORTING_FIELD, $sort_dir = DEFAULT_SORTING_DIRECTION) {
    $total = get_total_items();

    if ($total == 0) {
        return array();
    }

    // Normalize offset.
    $offset = max(0, $offset);
    $last_index = $offset + $limit;

    // Group where first required id is.
    $start_group_index = floor($offset / GROUP_SIZE);
    $end_group_index = floor($last_index / GROUP_SIZE);

    $groups = array();
    $pages_version = get_page_groups_version();

    for ($i = $start_group_index; $i <= $end_group_index; ++$i) {
        $group_key = build_group_key($i, $pages_version, $sort_by, $sort_dir);
        $next_group = get_group($group_key);

        foreach ($next_group as $id) {
            $groups[] = $id;
        }
    }

    $offset_inside_group = $offset % GROUP_SIZE;

    return array_slice($groups, $offset_inside_group, $limit);
}

function fill_group($group_key) {
    break_group_key($group_key, $group_index, $version, $sort_by, $sort_dir);
    $offset = $group_index * GROUP_SIZE;

    $mysqli = get_client();
    $query = "SELECT id FROM items ORDER BY $sort_by $sort_dir LIMIT ".GROUP_SIZE." OFFSET ?";
    $stmt = $mysqli->prepare($query);
    if (!validate_query($stmt, $mysqli)) {
        return false;
    }
    $stmt->bind_param('i', $offset);
    if (!$stmt->execute()) {
        error_log("Failed to fill group $group_key: ".$mysqli->error);
    }

    $result = $stmt->get_result();
    $group = array();
    while ($row = $result->fetch_row()) {
        $group[] = $row[0];
    }
    _free_resources($stmt, $mysqli);

    if (!put_to_cache($group_key, $group)) {
        return false;
    }

    return $group;
}

function get_group($group_key) {
    $group = get_from_cache($group_key);

    if ($group) {
        return $group;
    }

    $group = fill_group($group_key);

    if (!$group) {
        error_log("Could not fill group $group_key.");
        return false;
    }

    return $group;
}

function build_group_key($index, $version = 0, $sort_by = DEFAULT_SORTING_FIELD, $sort_dir = DEFAULT_SORTING_DIRECTION) {
    return "g:$version:$sort_by:$sort_dir:$index";
}

function break_group_key($group_key, &$index, &$version, &$sort_by, &$sort_dir) {
    $details = explode(':', $group_key);

    if (count($details) != 5) {
        error_log('count '.count($details).' while key: '.$group_key.' details: '.print_r($details, true));
        return false;
    }

    $version = $details[1];
    $sort_by = $details[2];
    $sort_dir = $details[3];
    $index = $details[4];

    return true;
}