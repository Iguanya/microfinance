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
$mysqli = @mysqli_connect($dbHost, $dbUser, $dbPass, "", 3306);
if (!$mysqli) {
    http_response_code(500);
    die('Could not connect to MySQL at ' . htmlspecialchars($dbHost) . ': ' . mysqli_connect_error());
}

mysqli_set_charset($mysqli, 'utf8mb4');

// Validate DB name
if (!preg_match('/^[A-Za-z0-9_\-]+$/', $dbName)) {
    mysqli_close($mysqli);
    http_response_code(400);
    die('Invalid database name. Use only letters, numbers, underscore or dash.');
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `" . $dbName . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($mysqli, $sql)) {
    $err = mysqli_error($mysqli);
    mysqli_close($mysqli);
    http_response_code(500);
    die('Could not create database "' . htmlspecialchars($dbName) . '": ' . $err);
}

mysqli_close($mysqli);

// Redirect
if (basename($_SERVER['PHP_SELF']) === 'setup_dbcreate.php') {
    header('Location: setup_dbimport.php');
    exit;
}
