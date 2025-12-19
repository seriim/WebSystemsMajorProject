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

if (!hasAnyRole(['Administrator', 'Pastor', 'Clerk'])) {
    header('Location: ' . BASE_URL . 'attendance/index.php');
    exit();
}

$pageTitle = 'Vestry Hours';
$pageSubtitle = 'Record minister\'s vestry appointments';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vestry_hours WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . BASE_URL . 'attendance/vestry.php?deleted=1');
    exit();
}

// Handle add
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $visitor_lastname = sanitizeInput($_POST['visitor_lastname']);
    $visitor_name = sanitizeInput($_POST['visitor_name']);
    $time_of_visit = !empty($_POST['time_of_visit']) ? $_POST['time_of_visit'] : null;
    $nature_of_visit = sanitizeInput($_POST['nature_of_visit']);
    $telephone_type = sanitizeInput($_POST['telephone_type']);
    $telephone = sanitizeInput($_POST['telephone']);
    $minister_comment = sanitizeInput($_POST['minister_comment']);
    $recorded_by = $_SESSION['user_id'];
    
    if (empty($date)) {
        $error = 'Date is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO vestry_hours (date, visitor_lastname, visitor_name, time_of_visit, nature_of_visit, telephone_type, telephone, minister_comment, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssi", $date, $visitor_lastname, $visitor_name, $time_of_visit, $nature_of_visit, $telephone_type, $telephone, $minister_comment, $recorded_by);
        
        if ($stmt->execute()) {
            $success = 'Vestry hour record added successfully.';
            $_POST = array();
        } else {
            $error = 'Error adding record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get records
$dateFilter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';
$query = "SELECT vh.*, u.username as recorded_by_name FROM vestry_hours vh LEFT JOIN Users u ON vh.recorded_by = u.id WHERE 1=1";
if ($dateFilter) {
    $query .= " AND vh.date = '$dateFilter'";
}
$query .= " ORDER BY vh.date DESC, vh.time_of_visit DESC LIMIT 100";
$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}
$records = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Vestry Hours</h3>
            <p class="card-subtitle">Record minister's vestry appointments</p>
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
                    <label class="form-label">Time of Visit</label>
                    <input type="time" class="form-control" name="time_of_visit" value="<?php echo isset($_POST['time_of_visit']) ? $_POST['time_of_visit'] : ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Visitor Lastname</label>
                    <input type="text" class="form-control" name="visitor_lastname" value="<?php echo isset($_POST['visitor_lastname']) ? htmlspecialchars($_POST['visitor_lastname']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" class="form-control" name="visitor_name" value="<?php echo isset($_POST['visitor_name']) ? htmlspecialchars($_POST['visitor_name']) : ''; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Telephone Type</label>
                    <select class="form-select" name="telephone_type">
                        <option value="">Select...</option>
                        <option value="Home" <?php echo (isset($_POST['telephone_type']) && $_POST['telephone_type'] === 'Home') ? 'selected' : ''; ?>>Home</option>
                        <option value="Cell" <?php echo (isset($_POST['telephone_type']) && $_POST['telephone_type'] === 'Cell') ? 'selected' : ''; ?>>Cell</option>
                        <option value="Work" <?php echo (isset($_POST['telephone_type']) && $_POST['telephone_type'] === 'Work') ? 'selected' : ''; ?>>Work</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Telephone</label>
                    <input type="tel" class="form-control" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Nature of Visit</label>
                    <textarea class="form-control" name="nature_of_visit" rows="2"><?php echo isset($_POST['nature_of_visit']) ? htmlspecialchars($_POST['nature_of_visit']) : ''; ?></textarea>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="form-label">Minister's Comment</label>
                    <textarea class="form-control" name="minister_comment" rows="3"><?php echo isset($_POST['minister_comment']) ? htmlspecialchars($_POST['minister_comment']) : ''; ?></textarea>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i>
            Add Record
        </button>
    </form>
    
    <div class="mb-3">
        <form method="GET" action="" class="d-flex gap-2">
            <input type="date" class="form-control" name="date" value="<?php echo $dateFilter; ?>" style="max-width: 200px;">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="<?php echo BASE_URL; ?>attendance/vestry.php" class="btn btn-secondary">Clear</a>
        </form>
    </div>
    
    <?php if (count($records) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Visitor</th>
                    <th>Telephone</th>
                    <th>Nature of Visit</th>
                    <th>Minister's Comment</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo formatDate($record['date']); ?></td>
                    <td><?php echo $record['time_of_visit'] ? date('h:i A', strtotime($record['time_of_visit'])) : '-'; ?></td>
                    <td><?php echo htmlspecialchars(trim(($record['visitor_name'] ?: '') . ' ' . ($record['visitor_lastname'] ?: '')) ?: '-'); ?></td>
                    <td>
                        <?php if ($record['telephone']): ?>
                            <?php echo htmlspecialchars($record['telephone_type'] ? $record['telephone_type'] . ': ' : ''); ?>
                            <?php echo htmlspecialchars($record['telephone']); ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($record['nature_of_visit'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($record['minister_comment'] ?: '-'); ?></td>
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
        <i class="fas fa-clock"></i>
        <h3>No records found</h3>
        <p>Add your first vestry hour record above.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

