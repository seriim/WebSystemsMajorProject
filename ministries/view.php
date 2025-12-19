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

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'ministries/index.php');
    exit();
}

$pageTitle = 'View Ministry';
$pageSubtitle = 'Ministry details and members';

$conn = getDBConnection();
$ministry_id = intval($_GET['id']);

// Get ministry
$stmt = $conn->prepare("SELECT * FROM Ministries WHERE id = ?");
$stmt->bind_param("i", $ministry_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . BASE_URL . 'ministries/index.php');
    exit();
}

$ministry = $result->fetch_assoc();
$stmt->close();

// Get members
$stmt = $conn->prepare("SELECT m.*, mm.role FROM Members m INNER JOIN Ministry_Members mm ON m.mem_id = mm.member_id WHERE mm.ministry_id = ? ORDER BY m.last_name, m.first_name");
$stmt->bind_param("i", $ministry_id);
$stmt->execute();
$result = $stmt->get_result();
$members = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get meeting attendance stats
$result = $conn->query("SELECT COUNT(*) as total_meetings, SUM(count) as total_attendance FROM Attendance WHERE ministry_id = $ministry_id");
$stats = $result->fetch_assoc();

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title"><?php echo htmlspecialchars($ministry['name']); ?></h3>
            <p class="card-subtitle"><?php echo htmlspecialchars($ministry['description'] ?: 'No description'); ?></p>
        </div>
        <div>
            <?php if (hasRole('Administrator')): ?>
            <a href="<?php echo BASE_URL; ?>ministries/edit.php?id=<?php echo $ministry['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>ministries/index.php" class="btn btn-secondary">
                Back to List
            </a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card blue">
                <div class="stat-label">Active Members</div>
                <div class="stat-value"><?php echo count($members); ?></div>
                <div class="stat-description">Current members</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card yellow">
                <div class="stat-label">Total Meetings</div>
                <div class="stat-value"><?php echo $stats['total_meetings'] ?: 0; ?></div>
                <div class="stat-description">Recorded meetings</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card blue">
                <div class="stat-label">Total Attendance</div>
                <div class="stat-value"><?php echo $stats['total_attendance'] ?: 0; ?></div>
                <div class="stat-description">All-time attendance</div>
            </div>
        </div>
    </div>
    
    <h5 class="mb-3">Ministry Members</h5>
    <?php if (count($members) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($member['first_name'] . ' ' . ($member['middle_initials'] ? $member['middle_initials'] . ' ' : '') . $member['last_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars(($member['contact_home'] ?? '') ?: ($member['contact_work'] ?? '-')); ?></td>
                    <td><?php echo htmlspecialchars($member['role'] ?? '-'); ?></td>
                    <td><?php echo formatDate($member['date_joined'] ?? null); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>members/view.php?id=<?php echo $member['mem_id']; ?>" class="btn btn-sm btn-secondary btn-icon" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <h3>No members in this ministry</h3>
        <p>Add members through the member management page.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

