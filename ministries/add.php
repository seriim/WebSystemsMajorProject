<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasRole('Administrator')) {
    header('Location: ' . BASE_URL . 'ministries/index.php');
    exit();
}

$pageTitle = 'Add Ministry';
$pageSubtitle = 'Create a new ministry group';

$conn = getDBConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name']);
    $description = sanitizeInput($_POST['description']);
    
    if (empty($name)) {
        $error = 'Ministry name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO Ministries (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: ' . BASE_URL . 'ministries/index.php?added=1');
            exit();
        } else {
            $error = 'Error adding ministry: ' . $conn->error;
        }
    }
}

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Ministry</h3>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" data-validate>
        <div class="form-group">
            <label class="form-label">Ministry Name *</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="4" placeholder="Describe the ministry's purpose and activities..."></textarea>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Ministry
            </button>
            <a href="<?php echo BASE_URL; ?>ministries/index.php" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

