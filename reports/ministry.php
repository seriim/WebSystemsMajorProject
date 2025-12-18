<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'Ministry Participation Reports';
$pageSubtitle = 'View ministry participation and attendance';

$conn = getDBConnection();

// Get filter parameters
$ministryId = isset($_GET['ministry']) ? intval($_GET['ministry']) : 0;
$startDate = isset($_GET['start_date']) ? sanitizeInput($_GET['start_date']) : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? sanitizeInput($_GET['end_date']) : date('Y-m-t');

// Get ministries
$ministriesResult = $conn->query("SELECT id, name FROM Ministries ORDER BY name");
$ministries = $ministriesResult->fetch_all(MYSQLI_ASSOC);

// Get ministry participation
$query = "
    SELECT mi.name, 
           COUNT(DISTINCT mm.member_id) as member_count,
           COUNT(DISTINCT mma.id) as meeting_count,
           SUM(mma.count) as total_attendance
    FROM Ministries mi
    LEFT JOIN Ministry_Members mm ON mi.id = mm.ministry_id AND mm.status = 'Active'
    LEFT JOIN ministry_meeting_attendance mma ON mi.id = mma.ministry_id 
           AND mma.date BETWEEN '$startDate' AND '$endDate'
    WHERE 1=1
";

if ($ministryId) {
    $query .= " AND mi.id = $ministryId";
}

$query .= " GROUP BY mi.id ORDER BY mi.name";

$result = $conn->query($query);
$participation = $result->fetch_all(MYSQLI_ASSOC);

// Get detailed member list if ministry selected
$members = [];
if ($ministryId) {
    $stmt = $conn->prepare("SELECT m.*, mm.role, mm.joined_date FROM Members m INNER JOIN Ministry_Members mm ON m.mem_id = mm.member_id WHERE mm.ministry_id = ? AND mm.status = 'Active' ORDER BY m.last_name, m.first_name");
    $stmt->bind_param("i", $ministryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentReport = 'milestones';
?>

<div class="reports-tabs">
    <a href="<?php echo BASE_URL; ?>reports/index.php" class="report-tab">
        Membership
    </a>
    <a href="<?php echo BASE_URL; ?>reports/attendance.php" class="report-tab">
        Attendance
    </a>
    <a href="<?php echo BASE_URL; ?>reports/events.php" class="report-tab">
        Events
    </a>
    <a href="<?php echo BASE_URL; ?>reports/ministry.php" class="report-tab active">
        Milestones
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Ministry Participation Reports</h3>
            <p class="card-subtitle">View ministry participation and attendance statistics</p>
        </div>
    </div>
    
    <form method="GET" action="" class="mb-4" style="background: var(--pastel-blue-lighter); padding: 20px; border-radius: 8px;">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Ministry</label>
                    <select class="form-select" name="ministry">
                        <option value="">All Ministries</option>
                        <?php foreach ($ministries as $ministry): ?>
                            <option value="<?php echo $ministry['id']; ?>" <?php echo $ministryId == $ministry['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ministry['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo $startDate; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo $endDate; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Generate Report</button>
            </div>
        </div>
    </form>
    
    <h5 class="mb-3">Ministry Participation Summary</h5>
    <?php if (count($participation) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Ministry</th>
                    <th>Active Members</th>
                    <th>Meetings</th>
                    <th>Total Attendance</th>
                    <th>Average per Meeting</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participation as $row): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                    <td><span class="badge badge-success"><?php echo $row['member_count']; ?></span></td>
                    <td><?php echo $row['meeting_count'] ?: 0; ?></td>
                    <td><strong><?php echo $row['total_attendance'] ?: 0; ?></strong></td>
                    <td><?php echo $row['meeting_count'] > 0 ? round($row['total_attendance'] / $row['meeting_count'], 1) : 0; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-layer-group"></i>
        <h3>No data available</h3>
    </div>
    <?php endif; ?>
    
    <?php if ($ministryId && count($members) > 0): ?>
    <h5 class="mb-3 mt-4">Ministry Members</h5>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['first_name'] . ' ' . ($member['middle_initials'] ? $member['middle_initials'] . ' ' : '') . $member['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($member['email'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($member['home_phone'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($member['role'] ?: '-'); ?></td>
                    <td><?php echo formatDate($member['joined_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

