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

$pageTitle = 'Reports';
$pageSubtitle = 'Generate and view church reports';

$conn = getDBConnection();

// Get statistics
$result = $conn->query("SELECT COUNT(*) as count FROM Members WHERE status IN ('Member', 'Adherent')");
$activeMembers = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Members WHERE status = 'Visitor'");
$inactiveMembers = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Ministries");
$totalMinistries = $result->fetch_assoc()['count'];

// Get members by ministry
$query = "SELECT mi.name, COUNT(mm.id) as active_members
    FROM Ministries mi
    LEFT JOIN Ministry_Members mm ON mi.id = mm.ministry_id
    GROUP BY mi.id
    ORDER BY active_members DESC";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$membersByMinistry = $result->fetch_all(MYSQLI_ASSOC);

$totalActiveInMinistries = array_sum(array_column($membersByMinistry, 'active_members'));

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentReport = 'membership';
if ($currentPage === 'attendance.php') $currentReport = 'attendance';
if ($currentPage === 'events.php') $currentReport = 'events';
if ($currentPage === 'ministry.php') $currentReport = 'milestones';
if ($currentPage === 'membership.php') $currentReport = 'membership';
?>

<div class="reports-tabs">
    <a href="<?php echo BASE_URL; ?>reports/index.php" class="report-tab <?php echo ($currentReport === 'membership' && $currentPage === 'index.php') ? 'active' : ''; ?>">
        Membership
    </a>
    <a href="<?php echo BASE_URL; ?>reports/attendance.php" class="report-tab <?php echo $currentReport === 'attendance' ? 'active' : ''; ?>">
        Attendance
    </a>
    <a href="<?php echo BASE_URL; ?>reports/events.php" class="report-tab <?php echo $currentReport === 'events' ? 'active' : ''; ?>">
        Events
    </a>
    <a href="<?php echo BASE_URL; ?>reports/ministry.php" class="report-tab <?php echo $currentReport === 'milestones' ? 'active' : ''; ?>">
        Milestones
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Active Members</div>
        <div class="stat-value"><?php echo $activeMembers; ?></div>
        <div class="stat-description">Members and adherents</div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-label">Inactive Members</div>
        <div class="stat-value"><?php echo $inactiveMembers; ?></div>
        <div class="stat-description">Visitors</div>
    </div>
    
    <div class="stat-card blue">
        <div class="stat-label">Ministries</div>
        <div class="stat-value"><?php echo $totalMinistries; ?></div>
        <div class="stat-description">Active ministries</div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Members by Ministry</h3>
                    <p class="card-subtitle">Distribution across ministries</p>
                </div>
                <div>
                    <a href="<?php echo BASE_URL; ?>reports/membership.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-external-link-alt"></i>
                        View Full Report
                    </a>
                    <a href="<?php echo BASE_URL; ?>reports/export_pdf.php?type=membership" 
                       class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            
            <?php if (count($membersByMinistry) > 0): ?>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ministry</th>
                            <th>Active Members</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($membersByMinistry as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><strong><?php echo $row['active_members']; ?></strong></td>
                            <td style="text-align: center;">
                                <div style="margin-bottom: 4px;"><?php 
                                $percentage = $totalActiveInMinistries > 0 ? round(($row['active_members'] / $totalActiveInMinistries) * 100, 1) : 0;
                                echo $percentage . '%';
                                ?></div>
                                <div style="width: 100%; background: var(--gray-medium); height: 8px; border-radius: 4px;">
                                    <div style="width: <?php echo $percentage; ?>%; background: var(--pastel-blue-dark); height: 8px; border-radius: 4px;"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-chart-bar"></i>
                <h3>No data available</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php include __DIR__ . '/../includes/footer.php'; ?>

