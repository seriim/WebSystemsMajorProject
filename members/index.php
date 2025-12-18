<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

// Restrict Members from viewing the full member list
if (hasRole('Member')) {
    // Members can only view their own profile - redirect to their profile if found
    $conn = getDBConnection();
    $username = $_SESSION['username'];
    
    // Try to find member by username (assuming username might match email or be similar)
    $stmt = $conn->prepare("SELECT mem_id FROM Members WHERE email = ? OR email LIKE ? LIMIT 1");
    $emailPattern = "%$username%";
    $stmt->bind_param("ss", $username, $emailPattern);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $member = $result->fetch_assoc();
        $stmt->close();
        closeDBConnection($conn);
        header('Location: ' . BASE_URL . 'members/view.php?id=' . $member['mem_id']);
        exit();
    } else {
        $stmt->close();
        closeDBConnection($conn);
        // If no member found, redirect to dashboard
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

$pageTitle = 'Members';
$pageSubtitle = 'Manage church membership records';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && hasAnyRole(['Administrator', 'Clerk'])) {
    $id = intval($_GET['delete']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete related records first (cascade delete)
        // Delete ministry memberships
        $stmt1 = $conn->prepare("DELETE FROM Ministry_Members WHERE member_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();
        
        // Delete events
        $stmt2 = $conn->prepare("DELETE FROM Events WHERE member_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
        
        // Now delete the member
        $stmt3 = $conn->prepare("DELETE FROM Members WHERE mem_id = ?");
        $stmt3->bind_param("i", $id);
        
        if ($stmt3->execute()) {
            $conn->commit();
            $stmt3->close();
            header('Location: ' . BASE_URL . 'members/index.php?deleted=1');
            exit();
        } else {
            throw new Exception('Error deleting member: ' . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: ' . BASE_URL . 'members/index.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

// Get search term
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$query = "SELECT m.*, 
          GROUP_CONCAT(DISTINCT mi.name SEPARATOR ', ') as ministries
          FROM Members m
          LEFT JOIN Ministry_Members mm ON m.mem_id = mm.member_id
          LEFT JOIN Ministries mi ON mm.ministry_id = mi.id
          WHERE 1=1";

if ($search) {
    $query .= " AND (m.first_name LIKE '%$search%' OR m.last_name LIKE '%$search%' OR m.email LIKE '%$search%')";
}

$query .= " GROUP BY m.mem_id ORDER BY m.last_name, m.first_name";

$result = $conn->query($query);
$members = $result->fetch_all(MYSQLI_ASSOC);

$totalMembers = count($members);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="page-actions">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="search" class="form-control" placeholder="Search members..." value="<?php echo htmlspecialchars($search); ?>" onchange="window.location.href='?search='+this.value">
        </div>
        <?php if (hasAnyRole(['Administrator', 'Clerk', 'Pastor'])): ?>
        <a href="<?php echo BASE_URL; ?>members/add.php" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Add Member
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Member Directory</h3>
            <p class="card-subtitle"><?php echo $totalMembers; ?> members found</p>
        </div>
    </div>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Member deleted successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    
    <?php if (count($members) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Ministry</th>
                    <th>Status</th>
                    <th>Member Since</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($member['first_name'] . ' ' . ($member['middle_initials'] ? $member['middle_initials'] . ' ' : '') . $member['last_name']); ?></strong>
                    </td>
                    <td>
                        <?php if (!empty($member['email'])): ?>
                            <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($member['email']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($member['contact_home'])): ?>
                            <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($member['contact_home']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($member['contact_work'])): ?>
                            <div><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($member['contact_work']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($member['ministries'] ?: '-'); ?></td>
                    <td><span class="badge badge-success"><?php echo ucfirst($member['status']); ?></span></td>
                    <td><?php echo formatDate($member['date_joined']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?php echo BASE_URL; ?>members/view.php?id=<?php echo $member['mem_id']; ?>" class="btn btn-sm btn-secondary btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasAnyRole(['Administrator', 'Clerk', 'Pastor'])): ?>
                            <a href="<?php echo BASE_URL; ?>members/edit.php?id=<?php echo $member['mem_id']; ?>" class="btn btn-sm btn-primary btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasAnyRole(['Administrator', 'Clerk'])): ?>
                            <a href="?delete=<?php echo $member['mem_id']; ?>" class="btn btn-sm btn-danger btn-icon btn-delete" title="Delete" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-users"></i>
        <h3>No members found</h3>
        <p>Start by adding your first member.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

