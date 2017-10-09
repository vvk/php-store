<?php

include_once 'constants.php';

$memcached = new Memcached();
$memcached->addServer('localhost', 11211);
$memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);

function &get_cache() {
    global $memcached;
    return $memcached;
}

function get_from_cache($key) {
    global $memcached;
    return $memcached->get($key);
}

function put_to_cache($key, $value, $expire = 0) {
    global $memcached;
    return $memcached->set($key, $value, $expire);
}

function increment_cache_value($key) {
    global $memcached;
    return $memcached->increment($key);
}

function decrement_cache_value($key) {
    global $memcached;
    return $memcached->decrement($key);
}

function increment_total_items() {
    return increment_cache_value(TOTAL_ITEMS_CACHE_KEY);
}

function decrement_total_items() {
    return decrement_cache_value(TOTAL_ITEMS_CACHE_KEY);
}

function get_sorted_ids($sort_by = DEFAULT_SORTING_FIELD, $sort_dir = DEFAULT_SORTING_DIRECTION) {
    global $memcached;
    return $memcached->get("sorted:$sort_by:$sort_dir");
}

function set_sorted_ids($ids, $sort_by = DEFAULT_SORTING_FIELD, $sort_dir = DEFAULT_SORTING_DIRECTION) {
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

function get_page_groups_version() {
    global $memcached;
    $version = $memcached->get(GROUP_VERSION_CACHE_KEY);
    if (!$version) {
        $version = bump_pages_groups_version();
    }

    return $version;
}

function bump_pages_groups_version() {
    global $memcached;
    return $memcached->increment(GROUP_VERSION_CACHE_KEY, 1, 1);
}