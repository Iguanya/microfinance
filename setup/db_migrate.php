<?PHP
/**
 * Database Migration Script
 * Checks for missing data from mangoo_test.sql and updates the database
 * Can be run on startup or manually to ensure data consistency
 */

require_once dirname(__DIR__) . '/functions.php';

class DatabaseMigration {
    private $db_link;
    private $sql_file;
    private $log = array();
    
    public function __construct() {
        $this->db_link = connect();
        $this->sql_file = dirname(__DIR__) . '/database/mangoo_test.sql';
    }
    
    /**
     * Main migration function
     * Returns true if successful, false otherwise
     */
    public function migrate() {
        if (!file_exists($this->sql_file)) {
            $this->log('ERROR', "SQL file not found: {$this->sql_file}");
            return false;
        }
        
        $this->log('INFO', 'Starting database migration...');
        
        // Read and parse SQL file
        $sql_content = file_get_contents($this->sql_file);
        
        // Extract all INSERT statements
        preg_match_all('/INSERT INTO `?(\w+)`?\s*\([^)]+\)\s*VALUES\s*([^;]+);/i', $sql_content, $matches, PREG_SET_ORDER);
        
        $total_records = 0;
        $inserted_records = 0;
        
        foreach ($matches as $match) {
            $table = strtolower($match[1]);
            $values_str = $match[2];
            
            // Parse individual value sets
            preg_match_all('/\(([^)]+)\)/', $values_str, $value_matches);
            
            foreach ($value_matches[1] as $value_line) {
                $total_records++;
                
                // Parse individual values
                $values = $this->parseValues($value_line);
                
                if (!$this->recordExists($table, $values)) {
                    if ($this->insertRecord($table, $values)) {
                        $inserted_records++;
                    }
                }
            }
        }
        
        $this->log('INFO', "Migration complete: {$inserted_records} new records inserted out of {$total_records} total.");
        return true;
    }
    
    /**
     * Parse a value line from SQL INSERT statement
     */
    private function parseValues($value_line) {
        $values = array();
        
        // Split by comma, but respect quoted strings
        $parts = preg_split('/,(?=(?:[^\']*\'[^\']*\')*[^\']*$)/', $value_line);
        
        foreach ($parts as $part) {
            $part = trim($part);
            // Remove quotes
            if (preg_match("/^'(.*)'$/", $part, $m)) {
                $values[] = $m[1];
            } elseif ($part === 'NULL') {
                $values[] = null;
            } else {
                $values[] = $part;
            }
        }
        
        return $values;
    }
    
    /**
     * Check if a record already exists in the database
     */
    private function recordExists($table, $values) {
        // Most records have an ID as the first column
        if (empty($values)) return false;
        
        $id_value = $values[0];
        
        // Skip zero/NULL IDs (dummy records)
        if ($id_value === '0' || $id_value === null || $id_value === 'NULL') {
            return true; // Skip dummy records
        }
        
        try {
            $sql = "SELECT 1 FROM {$table} WHERE {$table}_id = " . intval($id_value) . " LIMIT 1";
            $stmt = $this->db_link->query($sql);
            return $stmt && $stmt->fetch();
        } catch (Exception $e) {
            // Table might have different ID column name
            return false;
        }
    }
    
    /**
     * Insert a record into the database
     */
    private function insertRecord($table, $values) {
        try {
            // Get column names from database schema
            $columns = $this->getTableColumns($table);
            
            if (empty($columns)) {
                $this->log('WARN', "Could not get columns for table: {$table}");
                return false;
            }
            
            // Build INSERT statement
            $col_names = implode(', ', $columns);
            $placeholders = implode(', ', array_fill(0, count($values), '?'));
            
            $sql = "INSERT INTO {$table} ({$col_names}) VALUES ({$placeholders})";
            $stmt = $this->db_link->prepare($sql);
            
            // Prepare values (escape strings)
            $prepared_values = array();
            foreach ($values as $idx => $val) {
                if ($val === 'NULL' || $val === null) {
                    $prepared_values[] = null;
                } elseif (is_numeric($val) && !in_array($columns[$idx], array('cust_pic', 'empl_pic', 'user_id'))) {
                    $prepared_values[] = intval($val);
                } else {
                    $prepared_values[] = $val;
                }
            }
            
            $result = $stmt->execute($prepared_values);
            
            if ($result) {
                $this->log('DEBUG', "Inserted record into {$table}");
            }
            
            return $result;
        } catch (Exception $e) {
            $this->log('WARN', "Could not insert into {$table}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get column names for a table
     */
    private function getTableColumns($table) {
        try {
            $stmt = $this->db_link->query("PRAGMA table_info({$table})");
            $columns = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $columns[] = $row['name'];
            }
            
            return $columns;
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Log a message
     */
    private function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}";
        $this->log[] = $log_entry;
        
        if (php_sapi_name() === 'cli') {
            echo $log_entry . PHP_EOL;
        }
    }
    
    /**
     * Get all log entries
     */
    public function getLogs() {
        return $this->log;
    }
}

// Run migration if called directly
if (php_sapi_name() === 'cli' || (isset($_GET['run_migration']) && $_GET['run_migration'] === '1')) {
    $migration = new DatabaseMigration();
    $migration->migrate();
    
    foreach ($migration->getLogs() as $log) {
        echo $log . "\n";
    }
    
    if (php_sapi_name() !== 'cli') {
        exit;
    }
}
?>
