<?php
// Application configuration
session_start();

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Jamaica');

// Base URL
define('BASE_URL', 'http://localhost/labs/MajorProjectFinal/');

// Handle logout (must be after BASE_URL is defined)
if (isset($_GET['logout'])) {
    // Clear all session variables
    $_SESSION = array();
    
    // Destroy the session cookie if it exists
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Redirect to login page
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Helper function to check user role
function hasRole($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role_name'] ?? '';
    return $userRole === $requiredRole;
}

// Helper function to check if user has any of the required roles
function hasAnyRole($requiredRoles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    $userRole = $_SESSION['role_name'] ?? '';
    return in_array($userRole, $requiredRoles);
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'login.php');
        exit();
    }
}

// Require specific role
function requireRole($requiredRole) {
    requireLogin();
    if (!hasRole($requiredRole)) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

// Sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format date for display
function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') {
        return '-';
    }
    return date('M d, Y', strtotime($date));
}

// Format datetime for display
function formatDateTime($datetime) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return '-';
    }
    return date('M d, Y h:i A', strtotime($datetime));
}

// Truncate string to match database column length
function truncateToLength($string, $maxLength) {
    if ($string === null) {
        return null;
    }
    return mb_substr($string, 0, $maxLength);
}
?>

