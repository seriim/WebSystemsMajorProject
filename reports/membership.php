<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'Membership Reports';
$pageSubtitle = 'Generate membership statistics and reports';

$conn = getDBConnection();

// Get filter parameters
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$genderFilter = isset($_GET['gender']) ? sanitizeInput($_GET['gender']) : '';
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Build query
$query = "SELECT * FROM Members WHERE 1=1";

if ($statusFilter) {
    $query .= " AND status = '$statusFilter'";
}

if ($genderFilter) {
    $query .= " AND gender = '$genderFilter'";
}

$query .= " ORDER BY last_name, first_name";

$result = $conn->query($query);
$members = $result->fetch_all(MYSQLI_ASSOC);

// Statistics
$stats = [];
$stats['total'] = count($members);
$stats['by_status'] = [];
$stats['by_gender'] = [];

$result = $conn->query("SELECT status, COUNT(*) as count FROM Members GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $stats['by_status'][$row['status']] = $row['count'];
}

$result = $conn->query("SELECT gender, COUNT(*) as count FROM Members WHERE gender IS NOT NULL GROUP BY gender");
while ($row = $result->fetch_assoc()) {
    $stats['by_gender'][$row['gender']] = $row['count'];
}

// Age groups
$result = $conn->query("
    SELECT 
        CASE 
            WHEN dob IS NULL THEN 'Unknown'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 'Under 18'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 35 THEN '18-35'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 36 AND 55 THEN '36-55'
            WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 56 AND 70 THEN '56-70'
            ELSE 'Over 70'
        END as age_group,
        COUNT(*) as count
    FROM Members
    GROUP BY age_group
");
$ageGroups = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentReport = 'membership';
?>

<div class="reports-tabs">
    <a href="<?php echo BASE_URL; ?>reports/index.php" class="report-tab active">
        Membership
    </a>
    <a href="<?php echo BASE_URL; ?>reports/attendance.php" class="report-tab">
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
            <h3 class="card-title">Membership Reports</h3>
            <p class="card-subtitle">View membership statistics and details</p>
        </div>
    </div>
    
    <form method="GET" action="" class="mb-4" style="background: var(--pastel-blue-light); padding: 20px; border-radius: 8px;">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="member" <?php echo $statusFilter === 'member' ? 'selected' : ''; ?>>Member</option>
                        <option value="adherent" <?php echo $statusFilter === 'adherent' ? 'selected' : ''; ?>>Adherent</option>
                        <option value="visitor" <?php echo $statusFilter === 'visitor' ? 'selected' : ''; ?>>Visitor</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select class="form-select" name="gender">
                        <option value="">All Genders</option>
                        <option value="Male" <?php echo $genderFilter === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $genderFilter === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $genderFilter === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary" style="margin-top: 32px;">Filter</button>
                <a href="<?php echo BASE_URL; ?>reports/membership.php" class="btn btn-secondary" style="margin-top: 32px;">Clear</a>
            </div>
        </div>
    </form>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card blue">
                <div class="stat-label">Total Members</div>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card yellow">
                <div class="stat-label">By Status</div>
                <div style="font-size: 14px; margin-top: 8px;">
                    <?php foreach ($stats['by_status'] as $status => $count): ?>
                        <div><?php echo ucfirst($status); ?>: <?php echo $count; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card blue">
                <div class="stat-label">By Gender</div>
                <div style="font-size: 14px; margin-top: 8px;">
                    <?php foreach ($stats['by_gender'] as $gender => $count): ?>
                        <div><?php echo $gender; ?>: <?php echo $count; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <h5 class="mb-3">Members by Age Group</h5>
    <canvas id="ageGroupChart" height="100"></canvas>
    
    <h5 class="mb-3 mt-4">Member List</h5>
    <?php if (count($members) > 0): ?>
    <div class="table-container">
        <table class="table" id="membersTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Date Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                <tr>
                    <td><?php echo htmlspecialchars($member['first_name'] . ' ' . ($member['middle_initials'] ? $member['middle_initials'] . ' ' : '') . $member['last_name']); ?></td>
                    <td><span class="badge badge-success"><?php echo ucfirst($member['status']); ?></span></td>
                    <td><?php echo htmlspecialchars($member['gender'] ?: '-'); ?></td>
                    <td><?php echo formatDate($member['dob']); ?></td>
                    <td><?php echo htmlspecialchars($member['email'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars(($member['contact_home'] ?? '') ?: ($member['contact_work'] ?? '-')); ?></td>
                    <td><?php echo formatDate($member['date_joined']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <h3>No members found</h3>
    </div>
    <?php endif; ?>
</div>

<script>
const ageGroupCtx = document.getElementById('ageGroupChart').getContext('2d');
const ageGroupChart = new Chart(ageGroupCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($ageGroups, 'age_group')); ?>,
        datasets: [{
            label: 'Members',
            data: <?php echo json_encode(array_column($ageGroups, 'count')); ?>,
            backgroundColor: '#A8D5E2',
            borderColor: '#7BB3C7',
            borderWidth: 1
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
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

