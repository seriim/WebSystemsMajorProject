<?php
// Set 404 status code
http_response_code(404);

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    define('BASE_URL', $protocol . '://' . $host . $scriptPath . '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | Church Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--pastel-blue-lighter) 0%, var(--pastel-blue-light) 100%);
            padding: 40px 20px;
        }
        
        .error-container {
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        
        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            border-radius: 50%;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }
        
        .error-icon i {
            font-size: 64px;
            color: var(--blue-primary);
        }
        
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: var(--blue-primary);
            margin-bottom: 16px;
            line-height: 1;
        }
        
        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 12px;
        }
        
        .error-message {
            font-size: 16px;
            color: var(--text-light);
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .error-actions .btn {
            min-width: 150px;
        }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">
                Sorry, the page you are looking for doesn't exist or has been moved.<br>
                Please check the URL or return to the homepage.
            </p>
            <div class="error-actions">
                <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Go to Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>login.php" class="btn btn-secondary">
                    <i class="fas fa-sign-in-alt"></i>
                    Go to Login
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Go Back
                </a>
            </div>
        </div>
    </div>
</body>
</html>

