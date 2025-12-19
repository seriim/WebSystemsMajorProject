<?php
/**
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */
require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$error = '';
require_once __DIR__ . '/includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Church Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-wrapper">
        <!-- Left Section - Blue Gradient -->
        <div class="login-left">
            <div class="login-left-content">
            </div>
            <!-- Cloud-like wavy divider -->
            <svg class="wavy-divider" viewBox="0 0 200 1500" preserveAspectRatio="none">
                <path d="M0,0 Q60,150 0,300 T0,600 Q60,750 0,900 T0,1200 Q60,1350 0,1500 L0,1500 L200,1500 L200,0 Z" fill="white"/>
            </svg>
        </div>
        
        <!-- Right Section - White Form -->
        <div class="login-right">
            <div class="login-form-container">
                <h2 class="login-form-title">Sign in to your account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger login-alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="" class="login-form" id="loginForm">
                    <div class="form-field-wrapper">
                        <label for="username" class="form-field-label">Username</label>
                        <div class="input-wrapper">
                            <input type="text" 
                                   class="form-field-input" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Enter your username" 
                                   required 
                                   autofocus>
                            <i class="fas fa-check input-checkmark"></i>
                        </div>
                    </div>
                    
                    <div class="form-field-wrapper">
                        <label for="password" class="form-field-label">Password</label>
                        <div class="input-wrapper">
                            <input type="password" 
                                   class="form-field-input" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password" 
                                   required>
                            <i class="fas fa-check input-checkmark"></i>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-wrapper">
                            <input type="checkbox" checked>
                            <span class="checkbox-custom"></span>
                            <span class="checkbox-label">Remember me</span>
                        </label>
                    </div>
                    
                    <button type="submit" name="login" class="btn-signin">
                        Sign In
                    </button>
                    
                    <div class="login-help-text">
                        <small>Default credentials: <strong>admin</strong> / <strong>admin123</strong></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Show checkmarks when input is valid
        document.querySelectorAll('.form-field-input').forEach(input => {
            input.addEventListener('input', function() {
                const checkmark = this.nextElementSibling;
                if (this.value.length > 0 && this.checkValidity()) {
                    checkmark.style.opacity = '1';
                } else {
                    checkmark.style.opacity = '0';
                }
            });
            
            input.addEventListener('blur', function() {
                const checkmark = this.nextElementSibling;
                if (this.value.length > 0 && this.checkValidity()) {
                    checkmark.style.opacity = '1';
                }
            });
        });
    </script>
</body>
</html>
