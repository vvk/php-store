<?php

include_once 'shared.php';
include_once 'cache.php';

function update($id) {
    $updated_parameters = array();
    $item = get_item($id);
    if (!$item) {
        header('Location: 404.php');
        return false;
    }

    $name = $_POST['name'];

    if (empty($name)) {
        die("Item name can not be empty.");
    }

    if (strlen($name) > 200) {
        die("Maximum name length is 200 symbols.");
    }

    if ($name != $item['name']) {
        $updated_parameters['name'] = $name;
    }

    $price = $_POST['price'];

    if ($price <= 0 || $price > 2147483647) {
        die("Price must be positive decimal number between 0 and 2147483647, but was " . htmlspecialchars($price));
    }

    if ($price != $item['price']) {
        $updated_parameters['price'] = $price;
        // We must reset pages cache here, since sorting by price is allowed.
        $reset_pages_cache = true;
    }

    $description = $_POST['description'];
    if ($description != $item['description']) {
        $updated_parameters['description'] = $description;
    }

    if (strlen($description) > 1000) {
        die("Maximum description length is 1000.");
    }

    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']['size'] > 1024 * 1024 * 2) {
            die("Image size is too large. Maximum size is 2 MB.");
        }

        if (upload_image($image)) {
            $updated_parameters['image'] = $image;
        } else {
            die('Could not load image for the item.');
        }
    }

    if (empty($updated_parameters)) {
        return true;
    }

    if (!update_item($id, $updated_parameters)) {
        error_log("Could not update item ID:$id.");
        return false;
    }

    if (isset($reset_pages_cache)) {
        bump_pages_groups_version();
    }

    return true;
}

function delete($id) {
    if (!delete_item($id)) {
        error_log("Could not delete item ID:$id.");
        return false;
    }

    decrement_total_items();
    bump_pages_groups_version();

    return true;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("$_SERVER[SERVER_PROTOCOL] 405");
    die();
}

$id = $_POST['id'];
$action = $_POST['action'];

if (empty($action) || empty($id)) {
    header("$_SERVER[SERVER_PROTOCOL] 400");
    die("Item ID or action is not specified.");
}

$action = strtolower($action);

if ($action == 'update') {
    if (update($id)) {
        header('Location: item.php?id=' . htmlspecialchars($id));
    } else {
        die("Failed to update item ID:$id.");
    }

} else if ($action == 'delete') {
    delete($id);
    header('Location: items.php');
}
