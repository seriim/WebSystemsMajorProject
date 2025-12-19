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

$pageTitle = 'Events & Milestones Reports';
$pageSubtitle = 'View upcoming birthdays, anniversaries, and events';

$conn = getDBConnection();

// Get filter parameters
$week = isset($_GET['week']) ? intval($_GET['week']) : 0; // 0 = this week, 1 = next week
$eventType = isset($_GET['event_type']) ? sanitizeInput($_GET['event_type']) : '';

$startDate = date('Y-m-d', strtotime("+$week weeks monday"));
$endDate = date('Y-m-d', strtotime("+$week weeks sunday"));

// Get birthday statistics (count for next 7 days) - includes both member DOB and Birthday events
$result = $conn->query("
    SELECT COUNT(*) as count FROM (
        SELECT DATE(CONCAT(
            CASE 
                WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
            END, '-', DATE_FORMAT(dob, '%m-%d')
        )) as next_birthday
        FROM Members 
        WHERE dob IS NOT NULL 
        AND DATE(CONCAT(
            CASE 
                WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
            END, '-', DATE_FORMAT(dob, '%m-%d')
        )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
        UNION ALL
        SELECT date as next_birthday
        FROM Events
        WHERE event_type = 'Birthday'
        AND date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ) as combined_birthdays
");
$birthdayStats = $result->fetch_assoc();

// Get top 5 upcoming birthdays for display - combine member DOB and Birthday events
$birthdays = [];

// Get birthdays from member DOB
$result = $conn->query("
    SELECT first_name, last_name, dob, 
           DATE(CONCAT(
               CASE 
                   WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                   THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                   ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
               END, '-', DATE_FORMAT(dob, '%m-%d')
           )) as next_birthday
    FROM Members 
    WHERE dob IS NOT NULL 
    AND DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(dob, '%m-%d')
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY next_birthday
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $birthdays[] = [
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'dob' => $row['dob'],
            'next_birthday' => $row['next_birthday']
        ];
    }
}

// Get Birthday events from Events table
$result = $conn->query("
    SELECT e.date as next_birthday, m.first_name, m.last_name, m.dob
    FROM Events e
    LEFT JOIN Members m ON e.member_id = m.mem_id
    WHERE e.event_type = 'Birthday'
    AND e.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY e.date
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Only add if not already in list (avoid duplicates)
        $exists = false;
        foreach ($birthdays as $existing) {
            if ($existing['next_birthday'] == $row['next_birthday'] && 
                $existing['first_name'] == $row['first_name'] && 
                $existing['last_name'] == $row['last_name']) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $birthdays[] = [
                'first_name' => $row['first_name'] ?: 'Unknown',
                'last_name' => $row['last_name'] ?: 'Member',
                'dob' => $row['dob'],
                'next_birthday' => $row['next_birthday']
            ];
        }
    }
}

// Sort by date and limit to 5
usort($birthdays, function($a, $b) {
    return strtotime($a['next_birthday']) - strtotime($b['next_birthday']);
});
$birthdays = array_slice($birthdays, 0, 5);

// Get anniversary statistics (count for next 7 days) - includes both member date_joined and Anniversary events
$result = $conn->query("
    SELECT COUNT(*) as count FROM (
        SELECT DATE(CONCAT(
            CASE 
                WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
            END, '-', DATE_FORMAT(date_joined, '%m-%d')
        )) as next_anniversary
        FROM Members 
        WHERE date_joined IS NOT NULL 
        AND DATE(CONCAT(
            CASE 
                WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
            END, '-', DATE_FORMAT(date_joined, '%m-%d')
        )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
        UNION ALL
        SELECT date as next_anniversary
        FROM Events
        WHERE event_type = 'Anniversary'
        AND date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ) as combined_anniversaries
");
$anniversaryStats = $result->fetch_assoc();

// Get top 5 upcoming anniversaries for display - combine member date_joined and Anniversary events
$anniversaries = [];

// Get anniversaries from member date_joined
$result = $conn->query("
    SELECT first_name, last_name, date_joined,
           DATE(CONCAT(
               CASE 
                   WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                   THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                   ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
               END, '-', DATE_FORMAT(date_joined, '%m-%d')
           )) as next_anniversary,
           TIMESTAMPDIFF(YEAR, date_joined, CURRENT_DATE) + 
           CASE 
               WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
               THEN 0 
               ELSE 1 
           END as years
    FROM Members 
    WHERE date_joined IS NOT NULL 
    AND DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(date_joined, '%m-%d')
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY next_anniversary
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $anniversaries[] = [
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'date_joined' => $row['date_joined'],
            'next_anniversary' => $row['next_anniversary'],
            'years' => $row['years']
        ];
    }
}

// Get Anniversary events from Events table
$result = $conn->query("
    SELECT e.date as next_anniversary, m.first_name, m.last_name, m.date_joined,
           TIMESTAMPDIFF(YEAR, COALESCE(m.date_joined, e.date), CURRENT_DATE) + 
           CASE 
               WHEN DATE_FORMAT(COALESCE(m.date_joined, e.date), '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
               THEN 0 
               ELSE 1 
           END as years
    FROM Events e
    LEFT JOIN Members m ON e.member_id = m.mem_id
    WHERE e.event_type = 'Anniversary'
    AND e.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY e.date
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Only add if not already in list (avoid duplicates)
        $exists = false;
        foreach ($anniversaries as $existing) {
            if ($existing['next_anniversary'] == $row['next_anniversary'] && 
                $existing['first_name'] == $row['first_name'] && 
                $existing['last_name'] == $row['last_name']) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $anniversaries[] = [
                'first_name' => $row['first_name'] ?: 'Unknown',
                'last_name' => $row['last_name'] ?: 'Member',
                'date_joined' => $row['date_joined'],
                'next_anniversary' => $row['next_anniversary'],
                'years' => $row['years'] ?: 0
            ];
        }
    }
}

// Sort by date and limit to 5
usort($anniversaries, function($a, $b) {
    return strtotime($a['next_anniversary']) - strtotime($b['next_anniversary']);
});
$anniversaries = array_slice($anniversaries, 0, 5);

// Get event statistics (count for next 14 days)
$query = "SELECT COUNT(*) as count FROM Events WHERE date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)";
if ($eventType) {
    $query .= " AND event_type = '$eventType'";
}
$result = $conn->query($query);
$eventStats = $result->fetch_assoc();

// Get top 5 upcoming events for display
$query = "SELECT e.*, m.first_name, m.last_name FROM Events e LEFT JOIN Members m ON e.member_id = m.mem_id WHERE e.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)";
if ($eventType) {
    $query .= " AND e.event_type = '$eventType'";
}
$query .= " ORDER BY e.date LIMIT 5";
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

<div class="card mb-4">
    <div class="card-header">
        <div>
            <h3 class="card-title">Events & Milestones Reports</h3>
            <p class="card-subtitle">View upcoming birthdays, anniversaries, and events</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>reports/export_pdf.php?type=events" 
               class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
</div>

<div class="upcoming-sections">
    <div class="upcoming-section-card">
        <h3 class="upcoming-section-title">Upcoming Birthdays</h3>
        <div class="upcoming-section-icon">
            <i class="fas fa-birthday-cake"></i>
        </div>
        <div class="upcoming-stat-value"><?php echo $birthdayStats['count']; ?></div>
        <div class="upcoming-stat-label">This Week</div>
        <?php if (count($birthdays) > 0): ?>
            <div class="upcoming-section-list">
                <?php foreach ($birthdays as $birthday): ?>
                    <div class="upcoming-item">
                        <strong><?php echo htmlspecialchars($birthday['first_name'] . ' ' . $birthday['last_name']); ?></strong>
                        <span><?php echo date('M d, Y', strtotime($birthday['next_birthday'])); ?></span>
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
            <i class="fas fa-heart" style="font-weight: 900;"></i>
        </div>
        <div class="upcoming-stat-value"><?php echo $anniversaryStats['count']; ?></div>
        <div class="upcoming-stat-label">This Week</div>
        <?php if (count($anniversaries) > 0): ?>
            <div class="upcoming-section-list">
                <?php foreach ($anniversaries as $anniversary): ?>
                    <div class="upcoming-item">
                        <strong><?php echo htmlspecialchars($anniversary['first_name'] . ' ' . $anniversary['last_name']); ?></strong>
                        <span><?php echo date('M d, Y', strtotime($anniversary['next_anniversary'])); ?></span>
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
            <i class="fas fa-calendar-alt"></i>
        </div>
        <div class="upcoming-stat-value"><?php echo $eventStats['count']; ?></div>
        <div class="upcoming-stat-label">Next 14 Days</div>
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

<?php include __DIR__ . '/../includes/footer.php'; ?>

