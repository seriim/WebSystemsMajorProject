<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'Attendance Reports';
$pageSubtitle = 'View attendance statistics and trends';

$conn = getDBConnection();

// Get filter parameters
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Monthly attendance summary
$result = $conn->query("
    SELECT date, SUM(number_attended) as total_attendance
    FROM Attendance
    WHERE MONTH(date) = $month AND YEAR(date) = $year
    GROUP BY date
    ORDER BY date
");
$monthlyData = $result->fetch_all(MYSQLI_ASSOC);

// Get ministry meeting attendance
$result = $conn->query("
    SELECT mi.name, SUM(mma.count) as total_attendance, COUNT(mma.id) as meeting_count
    FROM Ministries mi
    LEFT JOIN ministry_meeting_attendance mma ON mi.id = mma.ministry_id
    WHERE MONTH(mma.date) = $month AND YEAR(mma.date) = $year
    GROUP BY mi.id
    ORDER BY total_attendance DESC
");
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
    <canvas id="attendanceChart" height="100"></canvas>
    
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

<script>
const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(attendanceCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_map(function($d) { return date('M d', strtotime($d['date'])); }, $monthlyData)); ?>,
        datasets: [{
            label: 'Attendance',
            data: <?php echo json_encode(array_column($monthlyData, 'total_attendance')); ?>,
            borderColor: '#7BB3C7',
            backgroundColor: 'rgba(123, 179, 199, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

