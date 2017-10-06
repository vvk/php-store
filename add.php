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
} catch (Exception $e) {
    error_log($e);
    die("Could not add new item.");
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}

if ($result) {
    header('Location: items.php');
    exit();
} else {
    die("Failed to update the database: " . $mysqli->error);
}