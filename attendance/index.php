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

$pageTitle = 'Attendance';
$pageSubtitle = 'Track church attendance records';

$conn = getDBConnection();

// Get recent activity counts
$result = $conn->query("SELECT COUNT(*) as count FROM sunday_school_attendance WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)");
$recentSundaySchool = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM vestry_hours WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)");
$recentVestry = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Attendance WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)");
$recentMeetings = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM church_service_attendance WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)");
$recentServices = $result->fetch_assoc()['count'];

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="attendance-grid">
    <a href="<?php echo BASE_URL; ?>attendance/sunday-school.php" class="attendance-card">
        <div class="attendance-card-header">
            <div class="attendance-card-icon blue">
                <i class="fas fa-child"></i>
            </div>
            <div class="attendance-card-info">
                <h3 class="attendance-card-title">Sunday School</h3>
                <p class="attendance-card-subtitle">Children's attendance tracking</p>
            </div>
        </div>
        <div class="attendance-card-stats">
            <div class="attendance-stat">
                <span class="attendance-stat-label">Last 30 days</span>
                <span class="attendance-stat-value"><?php echo $recentSundaySchool; ?> records</span>
            </div>
        </div>
        <div class="attendance-card-footer">
            <span class="attendance-card-link">Manage attendance <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
    
    <a href="<?php echo BASE_URL; ?>attendance/vestry.php" class="attendance-card">
        <div class="attendance-card-header">
            <div class="attendance-card-icon yellow">
                <i class="fas fa-clock"></i>
            </div>
            <div class="attendance-card-info">
                <h3 class="attendance-card-title">Vestry Hours</h3>
                <p class="attendance-card-subtitle">Minister appointments</p>
            </div>
        </div>
        <div class="attendance-card-stats">
            <div class="attendance-stat">
                <span class="attendance-stat-label">Last 30 days</span>
                <span class="attendance-stat-value"><?php echo $recentVestry; ?> records</span>
            </div>
        </div>
        <div class="attendance-card-footer">
            <span class="attendance-card-link">Manage appointments <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
    
    <a href="<?php echo BASE_URL; ?>attendance/ministry-meetings.php" class="attendance-card">
        <div class="attendance-card-header">
            <div class="attendance-card-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="attendance-card-info">
                <h3 class="attendance-card-title">Ministry Meetings</h3>
                <p class="attendance-card-subtitle">Group attendance tracking</p>
            </div>
        </div>
        <div class="attendance-card-stats">
            <div class="attendance-stat">
                <span class="attendance-stat-label">Last 30 days</span>
                <span class="attendance-stat-value"><?php echo $recentMeetings; ?> meetings</span>
            </div>
        </div>
        <div class="attendance-card-footer">
            <span class="attendance-card-link">Manage meetings <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
    
    <a href="<?php echo BASE_URL; ?>attendance/church-services.php" class="attendance-card">
        <div class="attendance-card-header">
            <div class="attendance-card-icon yellow">
                <i class="fas fa-church"></i>
            </div>
            <div class="attendance-card-info">
                <h3 class="attendance-card-title">Church Services</h3>
                <p class="attendance-card-subtitle">Service attendance records</p>
            </div>
        </div>
        <div class="attendance-card-stats">
            <div class="attendance-stat">
                <span class="attendance-stat-label">Last 30 days</span>
                <span class="attendance-stat-value"><?php echo $recentServices; ?> services</span>
            </div>
        </div>
        <div class="attendance-card-footer">
            <span class="attendance-card-link">Manage services <i class="fas fa-arrow-right"></i></span>
        </div>
    </a>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
