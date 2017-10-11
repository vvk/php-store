<?php

include 'shared.php';

const PICTURES_DIR = '/var/www/html/images';

$name = $_POST['name'];
$description = $_POST['description'];
$price = doubleval($_POST['price']);

if (empty($name)) {
    die("Item name can not be empty.");
}

if (strlen($name) > 200) {
    die("Maximum name length is 200 symbols.");
}

if (strlen($description) > 1000) {
    die("Maximum description length is 1000.");
}

if ($price <= 0 || $price > (1000 * 1000 * 1000)) {
    die("Price must be positive decimal number between 0 and 1000000000 (one billion).");
}

$generated_image_name = null;
if (is_uploaded_file($_FILES['image']['tmp_name'])) {
    if ($_FILES['image']['size'] > 1024 * 1024 * 2) {
        die("Image size is too large. Maximum size is 2 MB.");
    }

    if (!upload_image($generated_image_name)) {
        die('Could not load image for the item.');
    }
}

$id = create_item($name, $price, $description, $generated_image_name);

if (!$id) {
    die("Could not create item.");
}

increment_total_items();
bump_pages_groups_version();
header("Location: item.php?id=$id");