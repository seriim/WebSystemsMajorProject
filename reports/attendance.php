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

$pageTitle = 'Attendance Reports';
$pageSubtitle = 'View attendance statistics and trends';

$conn = getDBConnection();

// Get filter parameters
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Monthly attendance summary
$query = "SELECT date, SUM(count) as total_attendance
    FROM Attendance
    WHERE MONTH(date) = $month AND YEAR(date) = $year
    GROUP BY date
    ORDER BY date";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$monthlyData = $result->fetch_all(MYSQLI_ASSOC);

// Get ministry meeting attendance
$query = "SELECT mi.name, 
    COALESCE(SUM(a.count), 0) as total_attendance, 
    COUNT(a.id) as meeting_count
    FROM Ministries mi
    LEFT JOIN Attendance a ON mi.id = a.ministry_id 
        AND MONTH(a.date) = $month 
        AND YEAR(a.date) = $year
    GROUP BY mi.id
    HAVING meeting_count > 0
    ORDER BY total_attendance DESC";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$ministryAttendance = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentReport = 'attendance';
?>

<div class="reports-tabs">
    <a href="<?php echo BASE_URL; ?>reports/index.php" class="report-tab">
        Membership
    </a>
    <a href="<?php echo BASE_URL; ?>reports/attendance.php" class="report-tab active">
        Attendance
    </a>
    <a href="<?php echo BASE_URL; ?>reports/events.php" class="report-tab">
        Events
    </a>
    <a href="<?php echo BASE_URL; ?>reports/ministry.php" class="report-tab">
        Milestones
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Attendance Reports</h3>
            <p class="card-subtitle">Monthly attendance summary and trends</p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>reports/export_pdf.php?type=attendance&month=<?php echo $month; ?>&year=<?php echo $year; ?>" 
               class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
    
    <form method="GET" action="" class="mb-4" style="background: var(--pastel-blue-lighter); padding: 20px; border-radius: 8px;">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Month</label>
                    <select class="form-select" name="month">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $month == $i ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Year</label>
                    <input type="number" class="form-control" name="year" value="<?php echo $year; ?>" min="2020" max="<?php echo date('Y') + 1; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Generate Report</button>
            </div>
        </div>
    </form>
    
    <h5 class="mb-3">Monthly Attendance Trend</h5>
    <?php if (count($monthlyData) > 0): ?>
        <div style="position: relative; height: 300px; margin-bottom: 32px;">
            <canvas id="attendanceChart"></canvas>
        </div>
    <?php else: ?>
        <div class="empty-state" style="margin-bottom: 32px;">
            <i class="fas fa-chart-line"></i>
            <h3>No attendance data for this period</h3>
            <p>Select a different month or year to view attendance trends.</p>
        </div>
    <?php endif; ?>
    
    <h5 class="mb-3 mt-4">Ministry Meeting Attendance</h5>
    <?php if (count($ministryAttendance) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Ministry</th>
                    <th>Meetings</th>
                    <th>Total Attendance</th>
                    <th>Average per Meeting</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ministryAttendance as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['meeting_count']; ?></td>
                    <td><strong><?php echo $row['total_attendance'] ?: 0; ?></strong></td>
                    <td><?php echo $row['meeting_count'] > 0 ? round($row['total_attendance'] / $row['meeting_count'], 1) : 0; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-chart-line"></i>
        <h3>No attendance data for this period</h3>
    </div>
    <?php endif; ?>
</div>

<?php if (count($monthlyData) > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('attendanceChart');
    if (!canvas) return;
    
    const attendanceCtx = canvas.getContext('2d');
    if (!attendanceCtx) return;
    
    const monthlyData = <?php echo json_encode($monthlyData); ?>;
    const labels = monthlyData.map(function(d) { 
        return new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }); 
    });
    const data = monthlyData.map(function(d) { 
        return parseInt(d.total_attendance) || 0; 
    });
    
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Attendance',
                data: data,
                borderColor: '#7BB3C7',
                backgroundColor: 'rgba(123, 179, 199, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
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
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 50
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>

