<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasRole('Administrator')) {
    header('Location: ' . BASE_URL . 'ministries/index.php');
    exit();
}

$pageTitle = 'Edit Ministry';
$pageSubtitle = 'Update ministry information';

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'ministries/index.php');
    exit();
}

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

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    if (empty($name)) {
        $error = 'Ministry name is required.';
    } else {
        $stmt = $conn->prepare("UPDATE Ministries SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $ministry_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: ' . BASE_URL . 'ministries/view.php?id=' . $ministry_id . '&updated=1');
            exit();
        } else {
            $error = 'Error updating ministry: ' . $conn->error;
        }
    }
}

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Ministry</h3>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" data-validate>
        <div class="form-group">
            <label class="form-label">Ministry Name *</label>
            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($ministry['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4"><?php echo htmlspecialchars($ministry['description']); ?></textarea>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Update Ministry
            </button>
            <a href="<?php echo BASE_URL; ?>ministries/view.php?id=<?php echo $ministry_id; ?>" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

