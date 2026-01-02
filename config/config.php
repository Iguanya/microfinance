<?PHP
/**
 * Define Database parameters for MySQL/MariaDB
 */
require_once __DIR__ . '/load_env.php';

define('DB_HOST', getenv('MYSQL_HOST'));
define('DB_USER', getenv('MYSQL_USER'));
define('DB_PASS', getenv('MYSQL_PASSWORD'));
define('DB_NAME', getenv('MYSQL_DATABASE'));
define('DB_PORT', getenv('MYSQL_PORT') ?: '3306');

// Using PDO for remote MySQL connection
// Note: Ensure the remote host allows connections from this environment's IP
define('DB_DSN', "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4");
?>