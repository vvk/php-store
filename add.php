<?php

const PICTURES_DIR = '/var/www/html/images';

function update_picture($id, &$mysqli) {
    if (!$_FILES['picture']['size']) {
        return;
    }

    $pic = $_FILES['picture'];
    $uploaded_picture_name = basename($pic['name']);
    $mime_type = $pic['type'];



    $extension = pathinfo($uploaded_picture_name,PATHINFO_EXTENSION);
    $new_name = uniqid() . ".$extension";
    $destination = getcwd() . "/${new_name}";

    if (!move_uploaded_file($pic['tmp_name'], $destination)) {
        die("Something went wrong while the item picture was being uploaded.");
    }

    try {
        error_log("Name: $new_name, ID: $id");
        $stmt = $mysqli->prepare('UPDATE items SET image_url = ? WHERE id = ?');
        $stmt->bind_param('si', $new_name, $id);
        $stmt->execute();
        $result = $stmt->affected_rows;

        if (!$result) {
            die("Could not upload image.");
        }
    } catch (Exception $e) {
        error_log($e);
        die("Could not upload image.");
    }
}

$name = htmlspecialchars($_POST['name']);
$description = htmlspecialchars($_POST['description']);
$price = doubleval($_POST['price']);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli(
    'localhost',
    'root',
    'root',
    'store'
);

try {
    $stmt = $mysqli->prepare('INSERT INTO items(name, description, price) VALUES (?, ?, ?)');
    $stmt->bind_param('ssd', $name, $description, $price);
    $stmt->execute();

    $result = $stmt->affected_rows;
    $id = $stmt->insert_id;
} catch (Exception $e) {
    error_log($e);
    die("Could not add new item.");
} finally {
    if ($stmt) {
        $stmt->close();
    }
}

if (!$result || !$id) {
    error_log($mysqli->error);
    die('Could not create new item.');
}

update_picture($id, $mysqli);

header("Location: item.php?id=$id");