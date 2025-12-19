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
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Attendance Trends</h3>
                    <p class="card-subtitle">Weekly total attendance (last 4 weeks)</p>
                </div>
            </div>
            <div style="position: relative; height: 300px; padding: 20px;">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Upcoming Events</h3>
                    <p class="card-subtitle">Next 2 weeks</p>
                </div>
            </div>
            <?php if (count($upcomingBirthdays) > 0 || count($upcomingAnniversaries) > 0): ?>
                <div class="upcoming-events-list">
                    <?php if (count($upcomingBirthdays) > 0): ?>
                        <div class="event-section">
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
                        <div class="event-section" style="margin-top: 24px;">
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

<?php include __DIR__ . '/includes/footer.php'; ?>

