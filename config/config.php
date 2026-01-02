<?PHP
/**
 * Define Database parameters for PostgreSQL
 */
$db_url = parse_url(getenv('DATABASE_URL'));
define('DB_DSN', "pgsql:host=" . $db_url['host'] . ";port=" . ($db_url['port'] ?? 5432) . ";dbname=" . ltrim($db_url['path'], '/') . ";user=" . $db_url['user'] . ";password=" . $db_url['pass']);
?>