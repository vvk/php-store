<?php

include_once 'shared.php';
include_once 'cache.php';

const MAX_BATCH_SIZE = 10000;

$limit = !array_key_exists('limit', $_GET) ? 1 : $_GET['limit'];

if (!is_numeric($limit) || $limit < 1) {
    die("Huh, very funny. Invalid items quantity or format specified.");
}

if ($limit > 1000 * 1000) {
    die("Wow-wow, hold on: $limit is too much. One million items per request is the maximum.");
}

$mysqli = get_client();
$mysqli->autocommit(false);
$stmt = $mysqli->prepare('INSERT INTO items(name, description, price) VALUES (?, ?, ?)');

$lorem = file_get_contents('lorem.txt');
$paragraphs = explode(PHP_EOL, $lorem);
$total_paragraphs = count($paragraphs);

while ($limit > 0) {
    $batch_size = min($limit, MAX_BATCH_SIZE);
    $mysqli->begin_transaction();
    for ($i = 0; $i < $batch_size; ++$i) {
        $name = 'Item '.uniqid('id:');
        $description = $paragraphs[rand(0, $total_paragraphs - 1)] . PHP_EOL . date(DATE_RFC822);
        $price = time();

        $stmt->bind_param('ssd', $name, $description, $price);
        if (!$stmt->execute()) {
            die("Fuck this shit: ".$mysqli->error);
        }
    }

    if ($mysqli->commit()) {
        echo "Generated batch of $batch_size new items. <br />";
    } else {
        die("Could not commit transaction.");
    }
    $limit -= $batch_size;
}

_free_resources($stmt, $mysqli);
invalidate_cache();
