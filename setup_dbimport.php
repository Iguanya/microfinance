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
// CONNECT TO DATABASE
// -------------------------------
$db = @mysqli_connect(
    $_SESSION['db_host'],
    $_SESSION['db_user'],
    $_SESSION['db_pass'],
    $_SESSION['db_name']
);

if (!$db) {
    die('Could not connect to database: ' . htmlspecialchars(mysqli_connect_error()));
}

mysqli_set_charset($db, 'utf8mb4');

// -------------------------------
// TRUNCATE ALL TABLES BEFORE IMPORT
// -------------------------------
$result = mysqli_query($db, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $table = $row[0];
    mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 0");
    mysqli_query($db, "TRUNCATE TABLE `$table`");
    mysqli_query($db, "SET FOREIGN_KEY_CHECKS = 1");
}

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

        if (!db_query($db, $query)) {

            $error = "Error running query:\n"
                . $query . "\n"
                . "MySQL error: " . db_error($db);

            file_put_contents($errorFilename, $error);
            exit;
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
