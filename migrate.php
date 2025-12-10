<?php
/**
 * Database Migration Script
 * Run this script to create the MySQL database tables
 */

require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/Database.php';

use App\Database;

echo "Starting database migration...\n";

try {
    Database::migrate();
    echo "✓ Migration completed successfully!\n";
    echo "Database tables have been created.\n";
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
