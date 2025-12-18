<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'Ministries';
$pageSubtitle = 'Manage ministry groups';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && hasRole('Administrator')) {
    $id = intval($_GET['delete']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete related records first (cascade delete)
        // Delete ministry memberships
        $stmt1 = $conn->prepare("DELETE FROM Ministry_Members WHERE ministry_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();
        
        // Delete attendance records
        $stmt2 = $conn->prepare("DELETE FROM Attendance WHERE ministry_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $stmt2->close();
        
        // Now delete the ministry
        $stmt3 = $conn->prepare("DELETE FROM Ministries WHERE id = ?");
        $stmt3->bind_param("i", $id);
        
        if ($stmt3->execute()) {
            $conn->commit();
            $stmt3->close();
            header('Location: ' . BASE_URL . 'ministries/index.php?deleted=1');
            exit();
        } else {
            throw new Exception('Error deleting ministry: ' . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        header('Location: ' . BASE_URL . 'ministries/index.php?error=' . urlencode($e->getMessage()));
        exit();
    }
}

// Get ministries with member counts
$query = "SELECT m.*, COUNT(mm.id) as member_count 
          FROM Ministries m 
          LEFT JOIN Ministry_Members mm ON m.id = mm.ministry_id
          GROUP BY m.id 
          ORDER BY m.name";
$result = $conn->query($query);
$ministries = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <div></div>
    <?php if (hasRole('Administrator')): ?>
    <a href="<?php echo BASE_URL; ?>ministries/add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Add Ministry
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Ministry Groups</h3>
            <p class="card-subtitle"><?php echo count($ministries); ?> ministries</p>
        </div>
    </div>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Ministry deleted successfully.</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
    
    <?php if (count($ministries) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Active Members</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ministries as $ministry): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($ministry['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($ministry['description'] ?: '-'); ?></td>
                    <td><span class="badge badge-success"><?php echo $ministry['member_count']; ?></span></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?php echo BASE_URL; ?>ministries/view.php?id=<?php echo $ministry['id']; ?>" class="btn btn-sm btn-secondary btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (hasRole('Administrator')): ?>
                            <a href="<?php echo BASE_URL; ?>ministries/edit.php?id=<?php echo $ministry['id']; ?>" class="btn btn-sm btn-primary btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $ministry['id']; ?>" class="btn btn-sm btn-danger btn-icon btn-delete" title="Delete" onclick="return confirm('Are you sure?')">
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
        <i class="fas fa-layer-group"></i>
        <h3>No ministries found</h3>
        <p>Start by adding your first ministry group.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

