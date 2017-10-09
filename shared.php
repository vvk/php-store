<?php

include 'cache.php';

const MYSQL_HOST = 'localhost';
const MYSQL_USER = 'root';
const MYSQL_PASSWORD = 'root'; // Yeah, it's plain text.
const DATABASE = 'store';

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

function validate_query(&$query) {
    if (!$query) {
        error_log('Failed to create MySQLi query.');
        return FALSE;
    }

    return TRUE;
}

function get_items_ids($sort_by = 'id', $sort_dir = 'asc', $limit = NULL, $offset = 0) {
    $mysqli = get_client();

    $query = "SELECT id FROM items ORDER BY $sort_by $sort_dir";

    if (!empty($limit)) {
        $query .= " LIMIT $limit OFFSET $offset";
    }

    $query = $mysqli->query($query);

    if (!validate_query($query)) {
        return FALSE;
    }

    $ids = array();

    while ($row = $query->fetch_row()) {
        $ids[] = $row[0];
    }

    _free_resources($query, $mysqli);

    return $ids;
}

function load_item($id) {
    $mysqli = get_client();

    $stmt = $mysqli->prepare('SELECT * FROM items WHERE id = ?');

    if (!validate_query($stmt)) {
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

    if (!validate_query($query)) {
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

    if (!validate_query($stmt)) {
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
        error_log(__FILE__.':'.__FUNCTION__.': Empty name for the item.');
        return false;
    }

    if ($price < 0) {
        error_log(__FILE__.':'.__FUNCTION__.': Negative price.');
        return false;
    }

    $mysqli = get_client();
    $stmt = $mysqli->prepare('INSERT INTO items(name, description, price, image) VALUES (?, ?, ?, ?)');

    if (!validate_query($stmt)) {
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