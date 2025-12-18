<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'Events & Milestones Reports';
$pageSubtitle = 'View upcoming birthdays, anniversaries, and events';

$conn = getDBConnection();

// Get filter parameters
$week = isset($_GET['week']) ? intval($_GET['week']) : 0; // 0 = this week, 1 = next week
$eventType = isset($_GET['event_type']) ? sanitizeInput($_GET['event_type']) : '';

$startDate = date('Y-m-d', strtotime("+$week weeks monday"));
$endDate = date('Y-m-d', strtotime("+$week weeks sunday"));

// Upcoming birthdays (next 7 days)
$result = $conn->query("
    SELECT first_name, last_name, dob, 
           DATE_FORMAT(dob, '%m-%d') as month_day,
           CASE 
               WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
               THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
               ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
           END as next_year
    FROM Members 
    WHERE dob IS NOT NULL 
    AND DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(dob, '%m-%d')
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(dob, '%m-%d')
    ))
    LIMIT 10
");
$birthdays = $result->fetch_all(MYSQLI_ASSOC);

// Upcoming anniversaries (next 7 days) - using date_joined as anniversary date
$result = $conn->query("
    SELECT first_name, last_name, date_joined,
           DATE_FORMAT(date_joined, '%m-%d') as month_day
    FROM Members 
    WHERE date_joined IS NOT NULL 
    AND DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(date_joined, '%m-%d')
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(date_joined, '%m-%d')
    ))
    LIMIT 10
");
$anniversaries = $result->fetch_all(MYSQLI_ASSOC);

// Upcoming events (next 14 days)
$query = "SELECT e.*, m.first_name, m.last_name FROM Events e LEFT JOIN Members m ON e.member_id = m.mem_id WHERE e.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)";
if ($eventType) {
    $query .= " AND e.event_type = '$eventType'";
}
$query .= " ORDER BY e.date LIMIT 10";
$result = $conn->query($query);
$events = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentReport = 'events';
?>

<div class="reports-tabs">
    <a href="<?php echo BASE_URL; ?>reports/index.php" class="report-tab">
        Membership
    </a>
    <a href="<?php echo BASE_URL; ?>reports/attendance.php" class="report-tab">
        Attendance
    </a>
    <a href="<?php echo BASE_URL; ?>reports/events.php" class="report-tab active">
        Events
    </a>
    <a href="<?php echo BASE_URL; ?>reports/ministry.php" class="report-tab">
        Milestones
    </a>
</div>

<div class="upcoming-sections">
    <div class="upcoming-section-card">
        <h3 class="upcoming-section-title">Upcoming Birthdays</h3>
        <div class="upcoming-section-icon">
            <i class="fas fa-birthday-cake"></i>
        </div>
        <?php if (count($birthdays) > 0): ?>
            <div class="upcoming-section-list">
                <?php foreach ($birthdays as $birthday): ?>
                    <div class="upcoming-item">
                        <strong><?php echo htmlspecialchars($birthday['first_name'] . ' ' . $birthday['last_name']); ?></strong>
                        <span><?php echo formatDate($birthday['dob']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="upcoming-empty-message">No birthdays this week</p>
        <?php endif; ?>
    </div>
    
    <div class="upcoming-section-card">
        <h3 class="upcoming-section-title">Upcoming Anniversaries</h3>
        <div class="upcoming-section-icon">
            <i class="fas fa-heart"></i>
        </div>
        <?php if (count($anniversaries) > 0): ?>
            <div class="upcoming-section-list">
                <?php foreach ($anniversaries as $anniversary): ?>
                    <div class="upcoming-item">
                        <strong><?php echo htmlspecialchars($anniversary['first_name'] . ' ' . $anniversary['last_name']); ?></strong>
                        <span><?php echo formatDate($anniversary['date_joined']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="upcoming-empty-message">No anniversaries this week</p>
        <?php endif; ?>
    </div>
    
    <div class="upcoming-section-card">
        <h3 class="upcoming-section-title">Upcoming Events</h3>
        <div class="upcoming-section-icon">
            <i class="fas fa-calendar"></i>
        </div>
        <?php if (count($events) > 0): ?>
            <div class="upcoming-section-list">
                <?php foreach ($events as $event): ?>
                    <div class="upcoming-item">
                        <strong><?php echo htmlspecialchars(ucfirst($event['event_type'])); ?></strong>
                        <?php if ($event['first_name']): ?>
                            <span><?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></span>
                        <?php endif; ?>
                        <span><?php echo formatDate($event['date']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="upcoming-empty-message">No events scheduled for this period</p>
        <?php endif; ?>
    </div>
</div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

