<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasAnyRole(['Administrator', 'Ministry Leader', 'Clerk'])) {
    header('Location: ' . BASE_URL . 'attendance/index.php');
    exit();
}

$pageTitle = 'Ministry Meeting Attendance';
$pageSubtitle = 'Track attendance at ministry meetings';

$conn = getDBConnection();

// Get ministries
$ministriesResult = $conn->query("SELECT id, name FROM Ministries ORDER BY name");
$ministries = $ministriesResult->fetch_all(MYSQLI_ASSOC);

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Attendance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . BASE_URL . 'attendance/ministry-meetings.php?deleted=1');
    exit();
}

// Handle add
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $ministry_id = intval($_POST['ministry_id']);
    $count = intval($_POST['count']);
    $notes = sanitizeInput($_POST['notes']);
    $recorded_by = $_SESSION['user_id'];
    
    if (empty($date) || empty($ministry_id)) {
        $error = 'Date and ministry are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO ministry_meeting_attendance (date, ministry_id, count, notes, recorded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siisi", $date, $ministry_id, $count, $notes, $recorded_by);
        
        if ($stmt->execute()) {
            $success = 'Meeting attendance recorded successfully.';
            $_POST = array();
        } else {
            $error = 'Error adding record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get records
$ministryFilter = isset($_GET['ministry']) ? intval($_GET['ministry']) : 0;
$dateFilter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';

$query = "SELECT mma.*, mi.name as ministry_name, u.username as recorded_by_name 
          FROM Attendance mma 
          LEFT JOIN Ministries mi ON mma.ministry_id = mi.id 
          LEFT JOIN Users u ON mma.recorded_by = u.id 
          WHERE 1=1";

if ($ministryFilter) {
    $query .= " AND mma.ministry_id = $ministryFilter";
}

if ($dateFilter) {
    $query .= " AND mma.date = '$dateFilter'";
}

$query .= " ORDER BY mma.date DESC LIMIT 100";

$result = $conn->query($query);
$records = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Ministry Meeting Attendance</h3>
            <p class="card-subtitle">Record number of attendees at ministry meetings</p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="mb-4" style="background: var(--pastel-blue-light); padding: 20px; border-radius: 8px;">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" class="form-control" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'); ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Ministry *</label>
                    <select class="form-select" name="ministry_id" required>
                        <option value="">Select Ministry...</option>
                        <?php foreach ($ministries as $ministry): ?>
                            <option value="<?php echo $ministry['id']; ?>" <?php echo (isset($_POST['ministry_id']) && $_POST['ministry_id'] == $ministry['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ministry['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Number of Attendees *</label>
                    <input type="number" class="form-control" name="count" min="0" value="<?php echo isset($_POST['count']) ? $_POST['count'] : '0'; ?>" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
            <select class="form-select" name="ministry" style="max-width: 250px;">
                <option value="">All Ministries</option>
                <?php foreach ($ministries as $ministry): ?>
                    <option value="<?php echo $ministry['id']; ?>" <?php echo $ministryFilter == $ministry['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ministry['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" class="form-control" name="date" value="<?php echo $dateFilter; ?>" style="max-width: 200px;">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="<?php echo BASE_URL; ?>attendance/ministry-meetings.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>
    
    <?php if (count($records) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ministry</th>
                    <th>Number of Attendees</th>
                    <th>Notes</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo formatDate($record['date']); ?></td>
                    <td><?php echo htmlspecialchars($record['ministry_name']); ?></td>
                    <td><strong><?php echo $record['count']; ?></strong></td>
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
        <i class="fas fa-users"></i>
        <h3>No records found</h3>
        <p>Record your first ministry meeting attendance above.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

