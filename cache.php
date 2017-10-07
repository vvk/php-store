<?php

$memcached = new Memcached();
$memcached->addServer('localhost', 11211);

function get_from_cache($key) {
    global $memcached;
    return $memcached->get($key);
}

function put_to_cache($key, $value) {
    global $memcached;
    return $memcached->set($key, $value);
}

function increment_cache_value($key) {
    global $memcached;
    return $memcached->increment($key);
}

function get_sorted_ids($sort_by = 'id', $sort_dir = 'asc') {
    global $memcached;
    return $memcached->get("sorted:$sort_by:$sort_dir");
}

function set_sorted_ids($ids, $sort_by = 'id', $sort_dir = 'asc') {
    global $memcached;
    return $memcached->set("sorted:$sort_by:$sort_dir", $ids);
}

function get_item_from_cache($id) {
    global $memcached;
    return $memcached->get($id);
}

function get_items_from_cache($ids) {
    global $memcached;
    return $memcached->getMulti($ids);
}

function put_item_to_cache($id, $item) {
    global $memcached;
    return $memcached->set($id, $item);
}

function put_items_to_cache($items) {
    global $memcached;
    return $memcached->setMulti($items);
}

function evict_item($id) {
    global $memcached;
    return $memcached->deleteMulti($id);
}

function invalidate_cache($ids = NULL) {
    global $memcached;

    if (empty($ids)) {
        return $memcached->flush();
    }

    if (is_array($ids)) {
        return $memcached->deleteMulti($ids);
    } else {
        return $memcached->delete($ids);
    }
}

