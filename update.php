<?php

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("$_SERVER[SERVER_PROTOCOL] 405");
    die();
}

$id = $_GET['id'];
$action = $_GET['action'];

if (empty($action) || empty($id)) {
    header("$_SERVER[SERVER_PROTOCOL] 400");
    die("Item ID or action is not specified.");
}