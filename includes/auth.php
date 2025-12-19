<?php
/**
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */
require_once __DIR__ . '/../config/config.php';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT u.id, u.username, u.password, u.status, r.role_name 
                           FROM Users u 
                           JOIN Roles r ON u.role = r.id 
                           WHERE u.username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($user['status'] === 'Active' && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_name'] = $user['role_name'];
            
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
    
    $stmt->close();
    closeDBConnection($conn);
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . BASE_URL . 'login.php');
    exit();
}
?>

