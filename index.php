<?php
/**
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */
require_once __DIR__ . '/config/config.php';
requireLogin();

$pageTitle = 'Dashboard';
$pageSubtitle = 'Overview of your church community';

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total Members
$result = $conn->query("SELECT COUNT(*) as count FROM Members WHERE status IN ('Member', 'Adherent')");
$stats['total_members'] = $result->fetch_assoc()['count'];

// This Month Attendance
$result = $conn->query("SELECT COUNT(*) as count FROM Attendance WHERE MONTH(date) = MONTH(CURRENT_DATE) AND YEAR(date) = YEAR(CURRENT_DATE)");
$stats['this_month'] = $result->fetch_assoc()['count'];

// Upcoming Events (next 2 weeks)
$result = $conn->query("SELECT COUNT(*) as count FROM Events WHERE date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)");
$stats['upcoming_events'] = $result->fetch_assoc()['count'];

// Special Needs (not in new schema, set to 0)
$stats['special_needs'] = 0;

// Active Ministries
$result = $conn->query("SELECT COUNT(*) as count FROM Ministries");
$stats['active_ministries'] = $result->fetch_assoc()['count'];

// Attendance trends (last 4 weeks) - get weekly totals
$attendanceTrends = [];
$result = $conn->query("
    SELECT 
        DATE_FORMAT(date, '%Y-%u') as week,
        DATE_FORMAT(MIN(date), '%M %d') as week_start,
        SUM(count) as total
    FROM Attendance 
    WHERE date >= DATE_SUB(CURRENT_DATE, INTERVAL 4 WEEK)
    GROUP BY DATE_FORMAT(date, '%Y-%u')
    ORDER BY week DESC
    LIMIT 4
");
$weeklyData = $result->fetch_all(MYSQLI_ASSOC);

// Reverse to show oldest to newest
$weeklyData = array_reverse($weeklyData);

if (count($weeklyData) > 0) {
    foreach ($weeklyData as $week) {
        $attendanceTrends[] = [
            'date' => $week['week_start'],
            'total' => intval($week['total'] ?? 0)
        ];
    }
} else {
    // If no data, show empty weeks
    for ($i = 3; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i weeks monday"));
        $attendanceTrends[] = [
            'date' => date('M d', strtotime($date)),
            'total' => 0
        ];
    }
}

// Upcoming birthdays (next 2 weeks)
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
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)
    ORDER BY DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(dob, '%m-%d')
    ))
    LIMIT 5
");
$upcomingBirthdays = $result->fetch_all(MYSQLI_ASSOC);

// Upcoming anniversaries (next 2 weeks) - using date_joined as anniversary date
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
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)
    ORDER BY DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(date_joined, '%m-%d')
    ))
    LIMIT 5
");
$upcomingAnniversaries = $result->fetch_all(MYSQLI_ASSOC);

// Upcoming events from Events table (next 2 weeks)
$result = $conn->query("
    SELECT e.*, m.first_name, m.last_name
    FROM Events e
    LEFT JOIN Members m ON e.member_id = m.mem_id
    WHERE e.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)
    ORDER BY e.date ASC
    LIMIT 5
");
$upcomingEvents = $result->fetch_all(MYSQLI_ASSOC);

// Get reminders data (next 7 days for birthdays/anniversaries, next 3 days for meetings)
$reminders = [];

// Upcoming birthdays for reminders (next 7 days)
$result = $conn->query("
    SELECT first_name, last_name, email, dob,
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
        $reminders[] = [
            'type' => 'birthday',
            'member' => $row['first_name'] . ' ' . $row['last_name'],
            'date' => $row['next_birthday'],
            'display_date' => date('M j', strtotime($row['next_birthday']))
        ];
    }
}

// Upcoming anniversaries for reminders (next 7 days)
$result = $conn->query("
    SELECT first_name, last_name, email, date_joined,
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
        $reminders[] = [
            'type' => 'anniversary',
            'member' => $row['first_name'] . ' ' . $row['last_name'],
            'date' => $row['next_anniversary'],
            'years' => $row['years'],
            'display_date' => date('M j', strtotime($row['next_anniversary']))
        ];
    }
}

// Upcoming ministry meetings for reminders (next 3 days)
$result = $conn->query("
    SELECT m.name as ministry_name, a.date, a.count as expected_attendance
    FROM Attendance a
    JOIN Ministries m ON a.ministry_id = m.id
    WHERE a.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY)
    ORDER BY a.date
    LIMIT 5
");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            'type' => 'ministry_meeting',
            'ministry' => $row['ministry_name'],
            'date' => $row['date'],
            'expected_attendance' => $row['expected_attendance'],
            'display_date' => date('M j, Y', strtotime($row['date']))
        ];
    }
}

// Sort reminders by date
usort($reminders, function($a, $b) {
    return strtotime($a['date']) - strtotime($b['date']);
});

closeDBConnection($conn);

include __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Total Members</div>
        <div class="stat-value"><?php echo $stats['total_members']; ?></div>
        <div class="stat-description">Active members in database</div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-label">This Month</div>
        <div class="stat-value"><?php echo $stats['this_month']; ?></div>
        <div class="stat-description">Total attendance records</div>
    </div>
    
    <div class="stat-card blue">
        <div class="stat-label">Upcoming Events</div>
        <div class="stat-value"><?php echo $stats['upcoming_events']; ?></div>
        <div class="stat-description">In the next 2 weeks</div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-label">Special Needs</div>
        <div class="stat-value"><?php echo $stats['special_needs']; ?></div>
        <div class="stat-description">Members requiring attention</div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card" style="display: flex; flex-direction: column;">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Attendance Trends</h3>
                    <p class="card-subtitle">Weekly total attendance (last 4 weeks)</p>
                </div>
            </div>
            <div style="position: relative; height: 300px; margin-bottom: 0; flex-shrink: 0;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card" style="display: flex; flex-direction: column;">
            <div class="card-header" style="flex-shrink: 0;">
                <div>
                    <h3 class="card-title">Upcoming Events</h3>
                    <p class="card-subtitle">Next 2 weeks</p>
                </div>
            </div>
            <?php if (count($upcomingBirthdays) > 0 || count($upcomingAnniversaries) > 0 || count($upcomingEvents) > 0): ?>
                <div class="upcoming-events-list" style="flex: 1; min-height: 0; overflow-y: auto;">
                    <?php if (count($upcomingEvents) > 0): ?>
                        <div class="event-section">
                            <h5 class="event-section-title">
                                <i class="fas fa-calendar-alt" style="color: var(--blue-primary); margin-right: 8px;"></i>
                                Events
                            </h5>
                            <?php foreach ($upcomingEvents as $event): ?>
                                <div class="event-item">
                                    <div class="event-item-content">
                                        <strong class="event-name"><?php echo htmlspecialchars(ucfirst($event['event_type'])); ?></strong>
                                        <?php if ($event['first_name']): ?>
                                            <span class="event-member"><?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></span>
                                        <?php endif; ?>
                                        <span class="event-date"><?php echo formatDate($event['date']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($upcomingBirthdays) > 0): ?>
                        <div class="event-section" style="margin-top: <?php echo count($upcomingEvents) > 0 ? '12px' : '0'; ?>;">
                            <h5 class="event-section-title">
                                <i class="fas fa-birthday-cake" style="color: var(--blue-primary); margin-right: 8px;"></i>
                                Birthdays
                            </h5>
                            <?php foreach ($upcomingBirthdays as $birthday): ?>
                                <div class="event-item">
                                    <div class="event-item-content">
                                        <strong class="event-name"><?php echo htmlspecialchars($birthday['first_name'] . ' ' . $birthday['last_name']); ?></strong>
                                        <span class="event-date"><?php echo formatDate($birthday['dob']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($upcomingAnniversaries) > 0): ?>
                        <div class="event-section" style="margin-top: 12px;">
                            <h5 class="event-section-title">
                                <i class="fas fa-heart" style="color: var(--blue-primary); margin-right: 8px;"></i>
                                Anniversaries
                            </h5>
                            <?php foreach ($upcomingAnniversaries as $anniversary): ?>
                                <div class="event-item">
                                    <div class="event-item-content">
                                        <strong class="event-name"><?php echo htmlspecialchars($anniversary['first_name'] . ' ' . $anniversary['last_name']); ?></strong>
                                        <span class="event-date"><?php echo formatDate($anniversary['date_joined']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No upcoming events</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Attendance Chart - Wait for DOM and Chart.js to be ready
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('attendanceChart');
    if (!canvas) {
        console.error('Attendance chart canvas not found');
        return;
    }
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.error('Could not get 2D context for chart');
        return;
    }
    
    // Check if Chart is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library not loaded');
        return;
    }
    
    const attendanceData = <?php echo json_encode(array_column($attendanceTrends, 'total')); ?>;
    const attendanceLabels = <?php echo json_encode(array_column($attendanceTrends, 'date')); ?>;
    
    // Only create chart if we have data
    if (attendanceData.length > 0) {
        const attendanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: attendanceLabels,
                datasets: [{
                    label: 'Attendance',
                    data: attendanceData,
                    borderColor: '#4A90E2',
                    backgroundColor: 'rgba(74, 144, 226, 0.08)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total Attendance: ' + context.parsed.y + ' people';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Total Attendance Count'
                        },
                        ticks: {
                            stepSize: 50,
                            callback: function(value) {
                                return value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Week Starting'
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                elements: {
                    point: {
                        radius: 4,
                        hoverRadius: 6,
                        backgroundColor: '#4A90E2',
                        borderColor: '#fff',
                        borderWidth: 2
                    }
                }
            }
        });
    } else {
        // Show message if no data
        canvas.parentElement.innerHTML = '<div class="empty-state"><i class="fas fa-chart-line"></i><h3>No attendance data available</h3><p>Attendance records will appear here once data is added.</p></div>';
    }
});
</script>

<!-- Reminders Section -->
<div class="row" style="margin-top: 24px;">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">
                        <i class="fas fa-bell" style="color: var(--blue-primary); margin-right: 8px;"></i>
                        Reminders
                    </h3>
                    <p class="card-subtitle">Upcoming birthdays, anniversaries, and ministry meetings</p>
                </div>
            </div>
            <div class="card-body">
                <?php if (count($reminders) > 0): ?>
                    <div class="reminders-list">
                        <?php foreach ($reminders as $reminder): ?>
                            <div class="reminder-item">
                                <div class="reminder-icon">
                                    <?php if ($reminder['type'] === 'birthday'): ?>
                                        <i class="fas fa-birthday-cake"></i>
                                    <?php elseif ($reminder['type'] === 'anniversary'): ?>
                                        <i class="fas fa-heart"></i>
                                    <?php else: ?>
                                        <i class="fas fa-calendar-alt"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="reminder-content">
                                    <div class="reminder-title">
                                        <?php if ($reminder['type'] === 'birthday'): ?>
                                            <?php echo htmlspecialchars($reminder['member']); ?>'s Birthday
                                        <?php elseif ($reminder['type'] === 'anniversary'): ?>
                                            <?php echo htmlspecialchars($reminder['member']); ?>'s Anniversary
                                            <?php if (isset($reminder['years'])): ?>
                                                <span class="reminder-years">(<?php echo $reminder['years']; ?> years)</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($reminder['ministry']); ?> Meeting
                                            <?php if (isset($reminder['expected_attendance'])): ?>
                                                <span class="reminder-attendance">(Expected: <?php echo $reminder['expected_attendance']; ?>)</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="reminder-date">
                                        <i class="fas fa-calendar"></i> <?php echo $reminder['display_date']; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>No reminders</h3>
                        <p>You're all caught up! No upcoming reminders for the next 7 days.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

