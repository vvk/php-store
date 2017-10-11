<?php

include 'cache.php';

if (get_cache()->flush()) {
    error_log("Cache has been invalidated.");
} else {
    error_log("Failed to invalidate cache.");
}
header('Location: items.php');