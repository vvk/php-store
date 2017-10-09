<?php

const MYSQL_HOST = 'localhost';
const MYSQL_USER = 'root';
const MYSQL_PASSWORD = 'root'; // Yeah, it's plain text.
const DATABASE = 'store';

const DEFAULT_SORTING_FIELD = 'id';
const DEFAULT_SORTING_DIRECTION = 'asc';
const DEFAULT_PAGE_INDEX = 0;
const DEFAULT_ITEMS_PER_PAGE = 25;

const GROUP_SIZE = 1000;
const GROUP_VERSION_CACHE_KEY = 'g:v';
const TOTAL_ITEMS_CACHE_KEY = 'total_items';
