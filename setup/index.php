<?PHP
/**
 * Setup & Migration Control Panel
 * Provides interface to run database migrations and check system status
 */

session_start();
require_once dirname(__DIR__) . '/functions.php';

// Check if migration should run
$migration_run = false;
$migration_logs = array();

if (isset($_POST['run_migration'])) {
    require_once 'db_migrate.php';
    $migration = new DatabaseMigration();
    $migration->migrate();
    $migration_logs = $migration->getLogs();
    $migration_run = true;
}

// Get database status
$db_link = connect();
$db_stats = array();

$tables = array('customer', 'employee', 'loans', 'savings', 'shares', 'user');
foreach ($tables as $table) {
    $count = $db_link->query("SELECT COUNT(*) as cnt FROM {$table}")->fetch(PDO::FETCH_ASSOC);
    $db_stats[$table] = $count['cnt'];
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>mangoO - Setup & Migration</title>
    <style>
        * { font-family: Arial, sans-serif; }
        body { background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #ff6600; }
        h2 { color: #333; border-bottom: 2px solid #ff6600; padding-bottom: 10px; }
        .status-ok { color: green; font-weight: bold; }
        .status-warn { color: orange; font-weight: bold; }
        .status-error { color: red; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #ff6600; color: white; }
        button { background: #ff6600; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background: #e55a00; }
        .logs { background: #f9f9f9; padding: 10px; border-left: 3px solid #ff6600; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; }
        .log-info { color: #0066cc; }
        .log-warn { color: #ff9900; }
        .log-error { color: #cc0000; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🥭 mangoO Microfinance Management - Setup</h1>
        
        <!-- Database Status -->
        <div class="card">
            <h2>Database Status</h2>
            <table>
                <tr>
                    <th>Table</th>
                    <th>Records</th>
                    <th>Status</th>
                </tr>
                <?PHP foreach ($db_stats as $table => $count): ?>
                <tr>
                    <td><?PHP echo $table; ?></td>
                    <td><?PHP echo $count; ?></td>
                    <td>
                        <?PHP 
                            if ($count > 0) {
                                echo '<span class="status-ok">✓ OK</span>';
                            } else {
                                echo '<span class="status-warn">⚠ Empty</span>';
                            }
                        ?>
                    </td>
                </tr>
                <?PHP endforeach; ?>
            </table>
        </div>
        
        <!-- Migration Control -->
        <div class="card">
            <h2>Database Migration</h2>
            <p>Check for missing data from the test database and insert any missing records.</p>
            <form method="POST">
                <button type="submit" name="run_migration" value="1">Run Migration Now</button>
            </form>
        </div>
        
        <!-- Migration Logs -->
        <?PHP if ($migration_run): ?>
        <div class="card">
            <h2>Migration Results</h2>
            <div class="logs">
                <?PHP foreach ($migration_logs as $log): ?>
                    <?PHP 
                        $class = 'log-info';
                        if (strpos($log, '[WARN]') !== false) $class = 'log-warn';
                        if (strpos($log, '[ERROR]') !== false) $class = 'log-error';
                    ?>
                    <div class="<?PHP echo $class; ?>"><?PHP echo htmlspecialchars($log); ?></div>
                <?PHP endforeach; ?>
            </div>
            <p>
                <a href="index.php" style="color: #ff6600; text-decoration: none;">← Back to Setup</a>
            </p>
        </div>
        <?PHP endif; ?>
        
        <!-- Quick Start -->
        <div class="card">
            <h2>Quick Start</h2>
            <p><strong>Default Login:</strong></p>
            <ul>
                <li>Username: <code>admin</code></li>
                <li>Password: <code>password</code></li>
            </ul>
            <p><a href="/" style="color: #ff6600; text-decoration: none;">→ Go to Application</a></p>
        </div>
    </div>
</body>
</html>
