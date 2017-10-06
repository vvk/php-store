<?php
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

header("Location: item.php?id=$id");