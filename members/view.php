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

// If user is a Member, restrict them to only viewing their own profile
if (hasRole('Member')) {
    $username = $_SESSION['username'];
    // Check if the member's email matches the user's username or if username is in email
    $memberEmail = $member['email'] ?? '';
    if (empty($memberEmail) || 
        (strtolower($memberEmail) !== strtolower($username) && 
         strpos(strtolower($memberEmail), strtolower($username)) === false)) {
        // Member trying to view someone else's profile - redirect to dashboard
        closeDBConnection($conn);
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

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
            <h5 class="info-section-title">Personal Information</h5>
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Full Name</span>
                    <span class="info-value"><?php echo htmlspecialchars(($member['first_name'] ?? '') . ' ' . (($member['middle_initials'] ?? '') ? ($member['middle_initials'] . ' ') : '') . ($member['last_name'] ?? '')); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date of Birth</span>
                    <span class="info-value"><?php echo formatDate($member['dob'] ?? null); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Gender</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['gender'] ?? '-'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <h5 class="info-section-title">Contact Information</h5>
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Home Address</span>
                    <span class="info-value"><?php echo htmlspecialchars(trim((($member['home_address1'] ?? '') . ' ' . ($member['home_address2'] ?? ''))) ?: '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Town</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['town'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Parish</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['parish'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Home Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['contact_home'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Work Phone</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['contact_work'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['email'] ?? '-'); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <h5 class="info-section-title">Next of Kin</h5>
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['next_of_kin_name'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['next_of_kin_address'] ?? '-'); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Relation</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['next_of_kin_relation'] ?? '-'); ?></span>
                </div>
                <?php if (!empty($member['next_of_kin_contact'])): ?>
                <div class="info-row">
                    <span class="info-label">Contact</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['next_of_kin_contact']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['next_of_kin_email'])): ?>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['next_of_kin_email']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <h5 class="info-section-title">Church Information</h5>
            <div class="info-section">
                <div class="info-row">
                    <span class="info-label">Status</span>
                    <span class="info-value"><span class="badge badge-status"><?php echo ucfirst($member['status'] ?? 'Visitor'); ?></span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date Joined</span>
                    <span class="info-value"><?php echo formatDate($member['date_joined'] ?? null); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ministries</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['ministries'] ?? '-'); ?></span>
                </div>
                <?php if (!empty($member['min_id'])): ?>
                <div class="info-row">
                    <span class="info-label">Ministry ID</span>
                    <span class="info-value"><?php echo htmlspecialchars($member['min_id']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['passing_date'])): ?>
                <div class="info-row">
                    <span class="info-label">Passing Date</span>
                    <span class="info-value"><?php echo formatDate($member['passing_date']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

