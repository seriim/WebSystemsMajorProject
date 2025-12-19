<?php
/**
 * Database Backup Script
 * Church Management and Information System (CMIS)
 * 
 * This script exports the database to a SQL file for backup purposes.
 * Run this script via web browser or command line.
 * 
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */

require_once __DIR__ . '/config/config.php';
requireLogin();

if (!hasRole('Administrator')) {
    die('Access denied. Administrator privileges required.');
}

$conn = getDBConnection();

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="cmis_backup_' . date('Y-m-d_His') . '.sql"');

// Start output
echo "-- Church Management and Information System (CMIS) Database Backup\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- Database: " . DB_NAME . "\n\n";
echo "SET FOREIGN_KEY_CHECKS=0;\n\n";

// Get all tables
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

// Export each table
foreach ($tables as $table) {
    // Get table structure
    $result = $conn->query("SHOW CREATE TABLE `$table`");
    $row = $result->fetch_array();
    echo $row[1] . ";\n\n";
    
    // Get table data
    $result = $conn->query("SELECT * FROM `$table`");
    if ($result->num_rows > 0) {
        echo "INSERT INTO `$table` VALUES\n";
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $values = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $conn->real_escape_string($value) . "'";
                }
            }
            $rows[] = "(" . implode(',', $values) . ")";
        }
        echo implode(",\n", $rows) . ";\n\n";
    }
}

echo "SET FOREIGN_KEY_CHECKS=1;\n";

closeDBConnection($conn);
exit();
?>

