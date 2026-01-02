<?php
// setup_dbcreate.php
// Creates database based on session variables set in setup.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Required fields EXCEPT password
$required = ['db_host', 'db_user', 'db_name'];
foreach ($required as $k) {
    if (!isset($_SESSION[$k]) || trim($_SESSION[$k]) === '') {
        http_response_code(400);
        die('Missing required session parameter: ' . htmlspecialchars($k));
    }
}

$dbHost = $_SESSION['db_host'];
$dbUser = $_SESSION['db_user'];
$dbPass = $_SESSION['db_pass'] ?? ''; // allow empty password
$dbName = $_SESSION['db_name'];

// Force TCP to avoid socket confusion (VERY IMPORTANT)
try {
    $dsn = "mysql:host=" . $dbHost . ";port=3306;charset=utf8mb4";
    $db = new PDO($dsn, $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $db->exec("CREATE DATABASE IF NOT EXISTS `" . $dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Success - closing connection for now as script continues or redirects
    $db = null;
} catch (PDOException $e) {
    http_response_code(500);
    die('Could not connect to MySQL at ' . htmlspecialchars($dbHost) . ': ' . $e->getMessage());
}

// Redirect
if (basename($_SERVER['PHP_SELF']) === 'setup_dbcreate.php') {
    header('Location: setup_dbimport.php');
    exit;
}
