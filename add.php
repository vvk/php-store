<?php

include 'shared.php';

const PICTURES_DIR = '/var/www/html/images';

$name = $_POST['name'];
$description = $_POST['description'];
$price = doubleval($_POST['price']);

if (empty($name)) {
    die("Item name can not be empty.");
}

if ($price <= 0) {
    die("Price must be positive decimal number.");
    // But can be zero, we're generous.
}

if (!upload_image($generated_image_name)) {
    error_log('Could not load image for the item.');
}

$id = create_item($name, $price, $description, $generated_image_name);

if (!$id) {
    die("Could not create item.");
}

increment_total_items();
bump_pages_groups_version();
header("Location: item.php?id=$id");