<?php
$db_url = parse_url(getenv('DATABASE_URL'));

define('DB_HOST', $db_url['host']);
define('DB_PORT', $db_url['port'] ?? 3306);
define('DB_NAME', ltrim($db_url['path'], '/'));
define('DB_USER', $db_url['user']);
define('DB_PASS', $db_url['pass']);

define(
    'DB_DSN',
    "mysql:host=" . DB_HOST .
    ";port=" . DB_PORT .
    ";dbname=" . DB_NAME .
    ";charset=utf8mb4"
);
