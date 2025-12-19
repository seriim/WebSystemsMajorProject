<?php
/**
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'church_system_database');

// Create database connection
function getDBConnection() {
    try {
        // Try connecting via socket first (XAMPP default)
        $socket = '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
        if (file_exists($socket)) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, null, $socket);
        } else {
            // Fallback to TCP/IP connection
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        }
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Close database connection
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>

