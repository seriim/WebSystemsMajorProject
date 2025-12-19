<?php
/**
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */
require_once __DIR__ . '/../config/config.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Church Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">Church Management System</div>
                <div class="sidebar-subtitle">Faith Community Church</div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?php echo BASE_URL; ?>index.php" class="nav-item <?php echo ($currentPage === 'index.php' || strpos($currentPath, '/index.php') !== false) && strpos($currentPath, '/members') === false && strpos($currentPath, '/attendance') === false && strpos($currentPath, '/events') === false && strpos($currentPath, '/ministries') === false && strpos($currentPath, '/reports') === false && strpos($currentPath, '/users') === false ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
                <?php if (!hasRole('Member')): ?>
                <a href="<?php echo BASE_URL; ?>members/index.php" class="nav-item <?php echo strpos($currentPath, '/members') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Members</span>
                </a>
                <?php else: ?>
                <?php
                // For Members, show link to their own profile
                $conn = getDBConnection();
                $username = $_SESSION['username'];
                $stmt = $conn->prepare("SELECT mem_id FROM Members WHERE email = ? OR email LIKE ? LIMIT 1");
                $emailPattern = "%$username%";
                $stmt->bind_param("ss", $username, $emailPattern);
                $stmt->execute();
                $result = $stmt->get_result();
                $memberProfile = $result->fetch_assoc();
                $stmt->close();
                closeDBConnection($conn);
                if ($memberProfile): ?>
                <a href="<?php echo BASE_URL; ?>members/view.php?id=<?php echo $memberProfile['mem_id']; ?>" class="nav-item <?php echo strpos($currentPath, '/members') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>attendance/index.php" class="nav-item <?php echo strpos($currentPath, '/attendance') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Attendance</span>
                </a>
                <a href="<?php echo BASE_URL; ?>events/index.php" class="nav-item <?php echo strpos($currentPath, '/events') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Events</span>
                </a>
                <a href="<?php echo BASE_URL; ?>ministries/index.php" class="nav-item <?php echo strpos($currentPath, '/ministries') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-layer-group"></i>
                    <span>Ministries</span>
                </a>
                <a href="<?php echo BASE_URL; ?>reports/index.php" class="nav-item <?php echo strpos($currentPath, '/reports') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <?php if (hasRole('Administrator')): ?>
                <a href="<?php echo BASE_URL; ?>users/index.php" class="nav-item <?php echo strpos($currentPath, '/users') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <button class="btn btn-secondary w-100" onclick="window.location.href='<?php echo BASE_URL; ?>?logout=1'">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="top-header">
                <div class="header-left">
                    <div class="header-title">
                        <h1><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                        <p><?php echo isset($pageSubtitle) ? $pageSubtitle : 'Overview of your church community'; ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($_SESSION['role_name']); ?></div>
                    </div>
                    <a href="<?php echo BASE_URL; ?>?logout=1" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
            
            <div class="content-area">

