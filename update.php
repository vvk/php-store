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
    if (!empty($name) && $name != $item['name']) {
        $updated_parameters['name'] = $name;
    }

    $price = $_POST['price'];
    if ($price > 0 && $price != $item['price']) {
        $updated_parameters['price'] = $price;
    }

    $description = $_POST['description'];
    if ($description != $item['description']) {
        $updated_parameters['description'] = $description;
    }

    if (upload_image($image) && !empty($image)) {
        $updated_parameters['image'] = $image;
    }

    if (!update_item($id, $updated_parameters)) {
        error_log("Could not update item ID:$id.");
        return false;
    }

    bump_pages_groups_version();

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

        header('Location: item.php?id='.htmlspecialchars($id));
    } else {
        die("Failed to update item ID:$id.");
    }

} else if ($action == 'delete') {
    delete($id);
    header('Location: items.php');
}