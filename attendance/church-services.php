<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasAnyRole(['Administrator', 'Clerk', 'Pastor'])) {
    header('Location: ' . BASE_URL . 'attendance/index.php');
    exit();
}

$pageTitle = 'Church Service Attendance';
$pageSubtitle = 'Record number of attendees at church services';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Attendance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . BASE_URL . 'attendance/church-services.php?deleted=1');
    exit();
}

// Handle add
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $service_type = sanitizeInput($_POST['service_type']);
    $number_attended = intval($_POST['number_attended']);
    $notes = sanitizeInput($_POST['notes']);
    $recorded_by = $_SESSION['user_id'];
    
    if (empty($date)) {
        $error = 'Date is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO church_service_attendance (date, service_type, number_attended, notes, recorded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisi", $date, $service_type, $number_attended, $notes, $recorded_by);
        
        if ($stmt->execute()) {
            $success = 'Service attendance recorded successfully.';
            $_POST = array();
        } else {
            $error = 'Error adding record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get records
$dateFilter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';
$query = "SELECT csa.*, u.username as recorded_by_name FROM Attendance csa LEFT JOIN Users u ON csa.recorded_by = u.id WHERE 1=1";
if ($dateFilter) {
    $query .= " AND csa.date = '$dateFilter'";
}
$query .= " ORDER BY csa.date DESC LIMIT 100";
$result = $conn->query($query);
$records = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Church Service Attendance</h3>
            <p class="card-subtitle">Record number of members at church services</p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="mb-4" style="background: var(--pastel-blue-lighter); padding: 20px; border-radius: 8px;">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" class="form-control" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Service Type</label>
                    <input type="text" class="form-control" name="service_type" placeholder="e.g., Sunday Morning, Evening Service" value="<?php echo isset($_POST['service_type']) ? htmlspecialchars($_POST['service_type']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Number Attended *</label>
                    <input type="number" class="form-control" name="number_attended" min="0" value="<?php echo isset($_POST['number_attended']) ? $_POST['number_attended'] : '0'; ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes" rows="2"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Record Attendance
        </button>
    </form>
    
    <div class="mb-3">
        <form method="GET" action="" class="d-flex gap-2">
            <input type="date" class="form-control" name="date" value="<?php echo $dateFilter; ?>" style="max-width: 200px;">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="<?php echo BASE_URL; ?>attendance/church-services.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>
    
    <?php if (count($records) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Service Type</th>
                    <th>Number Attended</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo formatDate($record['date']); ?></td>
                    <td><?php echo htmlspecialchars($record['service_type'] ?: '-'); ?></td>
                    <td><strong><?php echo $record['number_attended']; ?></strong></td>
                    <td><?php echo htmlspecialchars($record['notes'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($record['recorded_by_name']); ?></td>
                    <td>
                        <a href="?delete=<?php echo $record['id']; ?>" class="btn btn-sm btn-danger btn-icon btn-delete" onclick="return confirm('Are you sure?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-church"></i>
        <h3>No records found</h3>
        <p>Record your first church service attendance above.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

