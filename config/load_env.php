<?php
/**
 * Simple .env loader
 * This script loads environment variables from a .env file into putenv(), $_ENV, and $_SERVER.
 * It is designed to work both locally and in environments like Replit.
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) continue;
        
        // Ensure there is an equals sign
        if (strpos($line, '=') === false) continue;

        $parts = explode('=', $line, 2);
        $name = trim($parts[0]);
        $value = trim($parts[1]);

        // Remove quotes from value if present
        $value = trim($value, '"\'');

        // Only set the variable if it's not already set in the environment
        // This allows system environment variables (like Replit secrets) to take precedence
        if (getenv($name) === false) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load the .env file from the project root
loadEnv(__DIR__ . '/../.env');
