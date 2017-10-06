<?php

const MYSQL_HOST = 'localhost';
const MYSQL_USER = 'root';
const MYSQL_PASSWORD = 'root'; // Yeah, it's plain text.
const DATABASE = 'store';

function &get_client() {
    $mysqli = new mysqli(
        MYSQL_HOST,
        MYSQL_USER,
        MYSQL_PASSWORD,
        DATABASE
    );

    return $mysqli;
}

function validate_query(&$query) {
    if (!$query) {
        error_log('Failed to create MySQLi query.');
        return FALSE;
    }

    return TRUE;
}

function get_items_ids($sort_by = 'id', $sort_dir = 'asc', $limit = NULL, $offset = 0) {
    $mysqli = get_client();

    $query = "SELECT id FROM items ORDER BY $sort_by $sort_dir";

    if (!empty($limit)) {
        $query .= " LIMIT $limit OFFSET $offset";
    }

    $query = $mysqli->query($query);

    if (!validate_query($query)) {
        return FALSE;
    }

    $ids = array();

    while ($row = $query->fetch_row()) {
        $ids[] = $row[0];
    }

    _free_resources($query, $mysqli);

    return $ids;
}

function get_item($id) {
    $mysqli = get_client();

    $stmt = $mysqli->prepare('SELECT * FROM items WHERE id = ?');

    if (!validate_query($stmt)) {
        return FALSE;
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $result = $stmt->get_result();
    _free_resources($stmt, $mysqli);

    if ($result) {
        return $result->fetch_assoc();
    } else {
        error_log("Could not retrieve item.");
        return FALSE;
    }
}

function get_total_items() {
    $mysqli = get_client();
    $query = $mysqli->query('SELECT count(id) FROM items');

    if (!validate_query($query)) {
        return FALSE;
    }

    $id = $query->fetch_row()[0];
    _free_resources($query, $mysqli);

    return $id;
}

function get_items_by_id(&$ids) {
    if (!is_array($ids)) {
        $ids = array($ids);
    }

    $mysqli = get_client();
    $stmt = $mysqli->prepare('SELECT * FROM items WHERE id = ?');

    if (!validate_query($stmt)) {
        return FALSE;
    }

    $items = array();

    foreach ($ids as $id) {
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $result = $stmt->get_result();

        if (!$result) {
            error_log("Could not get statement result.");
            return FALSE;
        }

        $items[$id] = $result->fetch_assoc();
    }

    _free_resources($stmt, $mysqli);

    return $items;
}

function _free_resources(&$stmt, &$mysqli) {
    if (!empty($stmt)) {
        $stmt->close();
    }

    if (!empty($mysqli)) {
        $mysqli->close();
    }
}