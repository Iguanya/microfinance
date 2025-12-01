<?php
/**
 * Convert MySQL dump to SQLite compatible format
 */

$input_file = 'database/mangoo_test.sql';
$output_file = 'database/mangoo_sqlite.sql';

if (!file_exists($input_file)) {
    die("Error: $input_file not found\n");
}

$content = file_get_contents($input_file);

// Step 1: Extract ALTER TABLE PRIMARY KEY statements to apply inline
$primary_keys = [];
preg_match_all('/ALTER TABLE `(\w+)`\s+ADD PRIMARY KEY \(`(\w+)`\);/i', $content, $matches, PREG_SET_ORDER);
foreach ($matches as $match) {
    $primary_keys[$match[1]] = $match[2];
}

// Step 2: Extract ALTER TABLE AUTO_INCREMENT statements
$auto_increments = [];
preg_match_all('/ALTER TABLE `(\w+)`\s+MODIFY `(\w+)`[^;]+AUTO_INCREMENT/i', $content, $matches, PREG_SET_ORDER);
foreach ($matches as $match) {
    $auto_increments[$match[1]] = $match[2];
}

// Step 3: Remove all ALTER TABLE statements
$content = preg_replace('/ALTER TABLE.*?;/s', '', $content);

// Remove MySQL specific comments
$content = preg_replace('/^--.*$/m', '', $content);
$content = preg_replace('/\/\*![0-9]+ .*? \*\/;/s', '', $content);

// Remove SET statements
$content = preg_replace('/^SET .*?;$/m', '', $content);

// Remove LOCK/UNLOCK TABLES
$content = preg_replace('/LOCK TABLES.*?;/s', '', $content);
$content = preg_replace('/UNLOCK TABLES;/s', '', $content);

// Step 4: Process CREATE TABLE statements
$content = preg_replace_callback(
    '/CREATE TABLE `(\w+)` \((.*?)\) ENGINE.*?;/s',
    function ($matches) use ($primary_keys, $auto_increments) {
        $table_name = $matches[1];
        $fields = $matches[2];
        
        // Remove backticks
        $fields = str_replace('`', '', $fields);
        
        // Split into lines
        $lines = array_map('trim', explode("\n", $fields));
        $processed_lines = [];
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            // Remove trailing commas for processing
            $line = rtrim($line, ',');
            
            // Skip if it's a KEY/INDEX definition
            if (preg_match('/^(KEY|INDEX|UNIQUE KEY|FULLTEXT KEY)/i', $line)) {
                continue;
            }
            
            // Convert data types
            $line = preg_replace('/\bint\(\d+\)/i', 'INTEGER', $line);
            $line = preg_replace('/\btinyint\(\d+\)/i', 'INTEGER', $line);
            $line = preg_replace('/\bsmallint\(\d+\)/i', 'INTEGER', $line);
            $line = preg_replace('/\bmediumint\(\d+\)/i', 'INTEGER', $line);
            $line = preg_replace('/\bbigint\(\d+\)/i', 'INTEGER', $line);
            $line = preg_replace('/varchar\(\d+\)/i', 'TEXT', $line);
            $line = preg_replace('/\btext\b/i', 'TEXT', $line);
            $line = preg_replace('/\bdatetime\b/i', 'TEXT', $line);
            $line = preg_replace('/\btimestamp\b/i', 'TEXT', $line);
            $line = preg_replace('/\bdouble/i', 'REAL', $line);
            $line = preg_replace('/\bfloat/i', 'REAL', $line);
            
            // Remove UNSIGNED, ZEROFILL, COLLATE, etc.
            $line = preg_replace('/\s+UNSIGNED/i', '', $line);
            $line = preg_replace('/\s+ZEROFILL/i', '', $line);
            $line = preg_replace('/\s+COLLATE \w+/i', '', $line);
            
            // Extract field name
            if (preg_match('/^(\w+)\s/', $line, $field_match)) {
                $field_name = $field_match[1];
                
                // Check if this is the primary key field
                if (isset($primary_keys[$table_name]) && $primary_keys[$table_name] === $field_name) {
                    // Check if it's also auto-increment
                    if (isset($auto_increments[$table_name]) && $auto_increments[$table_name] === $field_name) {
                        $line = preg_replace('/^(\w+)\s+INTEGER\s+NOT NULL/i', '$1 INTEGER PRIMARY KEY AUTOINCREMENT', $line);
                    } else {
                        $line = preg_replace('/^(\w+)\s+INTEGER\s+NOT NULL/i', '$1 INTEGER PRIMARY KEY', $line);
                    }
                }
            }
            
            $processed_lines[] = $line;
        }
        
        $new_fields = implode(",\n  ", $processed_lines);
        return "CREATE TABLE $table_name (\n  $new_fields\n);";
    },
    $content
);

// Remove remaining backticks
$content = str_replace('`', '', $content);

// Enable foreign keys in SQLite
$sqlite_header = "PRAGMA foreign_keys = ON;\n\n";

// Clean up whitespace
$content = preg_replace('/\n{3,}/', "\n\n", $content);

// Write output
file_put_contents($output_file, $sqlite_header . $content);

echo "Conversion complete! Output saved to $output_file\n";
echo "Creating SQLite database...\n";

// Create SQLite database
$db_file = 'mangoo.db';
if (file_exists($db_file)) {
    unlink($db_file);
}

try {
    $pdo = new PDO("sqlite:$db_file");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents($output_file);
    
    // Execute SQL in smaller chunks to avoid issues
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(PRAGMA|--)/i', $statement)) {
            try {
                $pdo->exec($statement . ';');
                $count++;
            } catch (PDOException $e) {
                echo "Warning: Error executing statement (continuing...): " . $e->getMessage() . "\n";
                // Continue with next statement
            }
        } else if (preg_match('/^PRAGMA/i', $statement)) {
            $pdo->exec($statement . ';');
        }
    }
    
    echo "Executed $count SQL statements\n";
    
    // Test the database
    $result = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "Created " . count($tables) . " tables: " . implode(', ', $tables) . "\n";
    
    // Test user table
    $result = $pdo->query("SELECT COUNT(*) FROM user");
    $user_count = $result->fetchColumn();
    echo "Users in database: $user_count\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>