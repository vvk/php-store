<?php
$name = htmlspecialchars($_POST['name']);
$description = htmlspecialchars($_POST['description']);
$price = doubleval($_POST['price']);

$mysqli = new mysqli(
    'localhost',
    'root',
    'root',
    'store'
);
$query = sprintf(
    "INSERT INTO items(name, description, price) VALUES ('%s', '%s', '%s')",
    $mysqli->real_escape_string($name),
    $mysqli->real_escape_string($description),
    $mysqli->real_escape_string($price)
);
$result = $mysqli->query($query);

if ($result) {
    header('Location: items.php');
    exit();
} else {
    die("Failed to update the database: " . $mysqli->error);
}