<?php

include 'cache.php';

get_cache()->flush();
header('Location: items.php');