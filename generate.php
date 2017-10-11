<?php

include_once 'shared.php';
include_once 'cache.php';

$limit = empty($_GET['limit']) ? 1 : $_GET['limit'];

$mysqli = get_client();
$mysqli->autocommit(false);
$mysqli->begin_transaction();
$stmt = $mysqli->prepare('INSERT INTO items(name, description, price) VALUES (?, ?, ?)');

$lorem = file_get_contents('lorem.txt');
$paragraphs = explode(PHP_EOL, $lorem);
$total_paragraphs = count($paragraphs);

for ($i = 0; $i < $limit; ++$i) {
    $name = 'Item '.uniqid('id:');
    $description = $paragraphs[rand(0, $total_paragraphs - 1)];
    $price = time();

    $stmt->bind_param('ssd', $name, $description, $price);
    if (!$stmt->execute()) {
        die("Fuck this shit: ".$mysqli->error);
    }
}

if ($mysqli->commit()) {
    echo "Generated $limit new items.";
    invalidate_cache();
} else {
    die("Could not commit transaction.");
}
