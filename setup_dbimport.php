<?php
// setup_dbimport.php — Modernized, secure importer for PHP 7/8+

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'functions.php';

// -------------------------------
// VALIDATE SESSION VALUES
// -------------------------------
$required = ['db_host', 'db_user', 'db_pass', 'db_name', 'db_type'];
foreach ($required as $k) {
    if (!isset($_SESSION[$k])) {
        http_response_code(400);
        die('Missing session variable: ' . htmlspecialchars($k));
    }
}

// Determine SQL file
$fileSQL = ($_SESSION['db_type'] == 2)
    ? 'database/mangoo_test.sql'
    : 'database/mangoo_fresh.sql';

$progressFilename = $fileSQL . '_filepointer';
$errorFilename    = $fileSQL . '_error';

$maxRuntime = 2;  // seconds allowed per execution
$deadline   = microtime(true) + $maxRuntime;

// -------------------------------
// LOAD CONFIGURATION
// -------------------------------
require_once 'config/config.php';

// -------------------------------
// CONNECT TO DATABASE
// -------------------------------
try {
    if (!defined('DB_DSN')) {
        throw new Error("DB_DSN is not defined. Check config/config.php");
    }
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Could not connect to database: ' . htmlspecialchars($e->getMessage()));
}

// -------------------------------
// TRUNCATE ALL TABLES BEFORE IMPORT
// -------------------------------
$db->exec("SET FOREIGN_KEY_CHECKS = 0");
$stmt = $db->query("SHOW TABLES");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $table = $row[0];
    $db->exec("TRUNCATE TABLE `$table` ");
}
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

// -------------------------------
// OPEN SQL FILE
// -------------------------------
$fp = @fopen($fileSQL, 'r');
if (!$fp) {
    die('Failed to open SQL file: ' . htmlspecialchars($fileSQL));
}

// -------------------------------
// CHECK PREVIOUS ERRORS
// -------------------------------
if (file_exists($errorFilename)) {
    echo "Previous error encountered:<br><br>";
    echo nl2br(file_get_contents($errorFilename));
    exit;
}

// -------------------------------
// RESUME FROM PREVIOUS POSITION
// -------------------------------
$filePosition = 0;
if (file_exists($progressFilename)) {
    $filePosition = (int) file_get_contents($progressFilename);
    fseek($fp, $filePosition);
}

// -------------------------------
// PROCESS SQL COMMANDS
// -------------------------------
$query = '';
while (microtime(true) < $deadline && ($line = fgets($fp, 102400))) {

    // skip blank + comment lines
    if (trim($line) === '' || str_starts_with(trim($line), '--')) {
        continue;
    }

    $query .= $line;

    // Completed SQL statement?
    if (substr(trim($query), -1) === ';') {
        
        // Skip table creation if it exists (MySQL/MariaDB specific check)
        if (stripos($query, 'CREATE TABLE') !== false) {
            $query = str_ireplace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $query);
        }

        if (!db_query($db, $query)) {
            // If it's a "table already exists" error, we can ignore it if we didn't use IF NOT EXISTS
            $error_msg = db_error($db);
            if (stripos($error_msg, 'already exists') === false) {
                $error = "Error running query:\n"
                    . $query . "\n"
                    . "Database error: " . $error_msg;

                file_put_contents($errorFilename, $error);
                exit;
            }
        }

        // Reset & store progress
        $query = '';
        file_put_contents($progressFilename, ftell($fp));
    }
}

// -------------------------------
// END-OF-FILE CHECK
// -------------------------------
if (feof($fp)) {
    // Completed successfully
    @unlink($progressFilename);
    @unlink($errorFilename);

    if ($_SESSION['db_type'] == 2) {
        header('Location: setup_makeconf.php');
    } else {
        header('Location: setup_admin.php');
    }
    exit;
}

// -------------------------------
// PARTIAL PROGRESS DISPLAY
// -------------------------------
$progressBytes = ftell($fp);
$totalBytes    = filesize($fileSQL);

$percentage = ($totalBytes > 0)
    ? round(($progressBytes / $totalBytes) * 100, 2)
    : 0;

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <?php includeHead('Microfinance Management', 0); ?>
    <meta http-equiv="refresh" content="<?php echo $maxRuntime + 1; ?>">
    <link rel="stylesheet" href="css/setup.css"/>
</head>

<body>
<div class="content_center">
    <img src="ico/mangoo_l.png" style="width:380px; margin-top:3em; margin-bottom:2em;"/>
    <p class="heading">mangoO Setup Assistant</p>

    <div class="setup">
        <p>Database Import</p>

        <div class="progress">
            <div class="progress back"></div>
            <div class="progress over" style="width:<?php echo $percentage; ?>%;"></div>
        </div>

        <p style="position:relative; top:-33px;"><?php echo $percentage; ?>%</p>
        <p>Please wait for import to complete!<br>Do not leave this page!</p>
    </div>
</div>
</body>
</html>
