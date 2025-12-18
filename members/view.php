<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'View Member';
$pageSubtitle = 'Member details';

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'members/index.php');
    exit();
}

$conn = getDBConnection();
$member_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT m.*, GROUP_CONCAT(DISTINCT mi.name SEPARATOR ', ') as ministries FROM Members m LEFT JOIN Ministry_Members mm ON m.mem_id = mm.member_id LEFT JOIN Ministries mi ON mm.ministry_id = mi.id WHERE m.mem_id = ? GROUP BY m.mem_id");
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . BASE_URL . 'members/index.php');
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title"><?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . (($member['middle_initials'] ?? '') ? ($member['middle_initials'] . ' ') : '') . ($member['last_name'] ?? '')); ?></h3>
            <p class="card-subtitle">Member ID: <?php echo $member['mem_id']; ?></p>
        </div>
        <div>
            <?php if (hasAnyRole(['Administrator', 'Clerk', 'Pastor'])): ?>
            <a href="<?php echo BASE_URL; ?>members/edit.php?id=<?php echo $member['mem_id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>members/index.php" class="btn btn-secondary">
                Back to List
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <h5 class="mb-3">Personal Information</h5>
            <table class="table">
                <tr>
                    <th width="40%">Full Name</th>
                    <td><?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . (($member['middle_initials'] ?? '') ? ($member['middle_initials'] . ' ') : '') . ($member['last_name'] ?? '')); ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?php echo formatDate($member['dob'] ?? null); ?></td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td><?php echo htmlspecialchars($member['gender'] ?? '-'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h5 class="mb-3">Contact Information</h5>
            <table class="table">
                <tr>
                    <th width="40%">Home Address</th>
                    <td><?php echo htmlspecialchars(trim((($member['home_address1'] ?? '') . ' ' . ($member['home_address2'] ?? ''))) ?: '-'); ?></td>
                </tr>
                <tr>
                    <th>Town</th>
                    <td><?php echo htmlspecialchars($member['town'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Parish</th>
                    <td><?php echo htmlspecialchars($member['parish'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Home Phone</th>
                    <td><?php echo htmlspecialchars($member['contact_home'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Work Phone</th>
                    <td><?php echo htmlspecialchars($member['contact_work'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($member['email'] ?? '-'); ?></td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-6">
            <h5 class="mb-3">Next of Kin</h5>
            <table class="table">
                <tr>
                    <th width="40%">Name</th>
                    <td><?php echo htmlspecialchars($member['next_of_kin_name'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?php echo htmlspecialchars($member['next_of_kin_address'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Relation</th>
                    <td><?php echo htmlspecialchars($member['next_of_kin_relation'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Contact</th>
                    <td><?php echo htmlspecialchars($member['next_of_kin_contact'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo htmlspecialchars($member['next_of_kin_email'] ?? '-'); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h5 class="mb-3">Church Information</h5>
            <table class="table">
                <tr>
                    <th width="40%">Status</th>
                    <td><span class="badge badge-success"><?php echo ucfirst($member['status'] ?? 'Visitor'); ?></span></td>
                </tr>
                <tr>
                    <th>Date Joined</th>
                    <td><?php echo formatDate($member['date_joined'] ?? null); ?></td>
                </tr>
                <tr>
                    <th>Ministries</th>
                    <td><?php echo htmlspecialchars($member['ministries'] ?? '-'); ?></td>
                </tr>
                <?php if (!empty($member['min_id'])): ?>
                <tr>
                    <th>Ministry ID</th>
                    <td><?php echo htmlspecialchars($member['min_id']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($member['passing_date'])): ?>
                <tr>
                    <th>Passing Date</th>
                    <td><?php echo formatDate($member['passing_date']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

