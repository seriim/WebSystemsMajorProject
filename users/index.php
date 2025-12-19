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
requireRole('Administrator');

$pageTitle = 'Users';
$pageSubtitle = 'Manage system users';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    if ($id != $_SESSION['user_id']) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Delete related records first (cascade delete)
            // Set recorded_by to NULL in Attendance records
            $stmt1 = $conn->prepare("UPDATE Attendance SET recorded_by = NULL WHERE recorded_by = ?");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            $stmt1->close();
            
            // Now delete the user
            $stmt2 = $conn->prepare("DELETE FROM Users WHERE id = ?");
            $stmt2->bind_param("i", $id);
            
            if ($stmt2->execute()) {
                $conn->commit();
                $stmt2->close();
                header('Location: ' . BASE_URL . 'users/index.php?deleted=1');
                exit();
            } else {
                throw new Exception('Error deleting user: ' . $conn->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            header('Location: ' . BASE_URL . 'users/index.php?error=' . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header('Location: ' . BASE_URL . 'users/index.php?error=' . urlencode('You cannot delete your own account'));
        exit();
    }
}

// Get users
$query = "SELECT u.*, r.role_name FROM Users u LEFT JOIN Roles r ON u.role = r.id ORDER BY u.id";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <div></div>
    <a href="<?php echo BASE_URL; ?>users/add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Add User
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">System Users</h3>
            <p class="card-subtitle"><?php echo count($users); ?> users</p>
        </div>
    </div>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">User deleted successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    
    <?php if (count($users) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['role_name'] ?? 'N/A'); ?></td>
                    <td><span class="badge badge-<?php echo ($user['status'] ?? 'Inactive') === 'Active' ? 'success' : 'danger'; ?>"><?php echo ucfirst($user['status'] ?? 'Inactive'); ?></span></td>
                    <td>-</td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger btn-icon btn-delete" onclick="return confirm('Are you sure?')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php else: ?>
                            <span class="text-muted" style="font-size: 12px;">Current User</span>
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
        <h3>No users found</h3>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

