<?php

include 'shared.php';

const PICTURES_DIR = '/var/www/html/images';

function upload_picture(&$generated_name) {
    if (!$_FILES['picture']['size']) {
        return true;
    }

    $pic = $_FILES['picture'];
    $extension = pathinfo(basename($pic['name']),PATHINFO_EXTENSION);

    $generated_image_name = uniqid().".$extension";
    $images_directory = getcwd().'/images';

    if (!file_exists($images_directory)) {
        mkdir($images_directory, 0777, true);
    }

    $destination = "$images_directory/$generated_image_name";

    if (!move_uploaded_file($pic['tmp_name'], $destination)) {
        error_log(__FILE__.':'.__FUNCTION__.': Something went wrong while the item picture was being uploaded.');
    }

    $imagick = new Imagick($destination);
    $imagick->thumbnailImage(64, 64, true);
    if (!$imagick->writeImage("$images_directory/t_$generated_image_name")) {
        error_log(__FILE__.':'.__FUNCTION__.': Could not save image.');
        return false;
    }

    $generated_name = $generated_image_name;

    return true;
}

$name = $_POST['name'];
$description = $_POST['description'];
$price = doubleval($_POST['price']);

if (empty($name)) {
    die("Item name can not be empty.");
}

if ($price < 0) {
    die("Price can not be negative.");
    // But can be zero, we're generous.
}

if (!upload_picture($generated_image_name)) {
    error_log('Could not load image for the item.');
}

$id = create_item($name, $price, $description, $generated_image_name);

if (!$id) {
    die("Could not create item.");
}

header("Location: item.php?id=$id");