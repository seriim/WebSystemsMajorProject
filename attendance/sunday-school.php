<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasAnyRole(['Administrator', 'Clerk', 'Ministry Leader'])) {
    header('Location: ' . BASE_URL . 'attendance/index.php');
    exit();
}

$pageTitle = 'Sunday School Attendance';
$pageSubtitle = 'Track children\'s attendance by age groups';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM sunday_school_attendance WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . BASE_URL . 'attendance/sunday-school.php?deleted=1');
    exit();
}

// Handle add/edit
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $category = $_POST['category'];
    $name = sanitizeInput($_POST['name']);
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $next_of_kin_name = sanitizeInput($_POST['next_of_kin_name']);
    $next_of_kin_contact = sanitizeInput($_POST['next_of_kin_contact']);
    $attended = isset($_POST['attended']) ? 1 : 0;
    $recorded_by = $_SESSION['user_id'];
    
    if (empty($date) || empty($category)) {
        $error = 'Date and category are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO sunday_school_attendance (date, category, name, dob, next_of_kin_name, next_of_kin_contact, attended, recorded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssii", $date, $category, $name, $dob, $next_of_kin_name, $next_of_kin_contact, $attended, $recorded_by);
        
        if ($stmt->execute()) {
            $success = 'Attendance record added successfully.';
            $_POST = array(); // Clear form
        } else {
            $error = 'Error adding record: ' . $conn->error;
        }
        $stmt->close();
    }
}

// Get records
$categoryFilter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$dateFilter = isset($_GET['date']) ? sanitizeInput($_GET['date']) : '';

$query = "SELECT ssa.*, u.username as recorded_by_name FROM sunday_school_attendance ssa LEFT JOIN Users u ON ssa.recorded_by = u.id WHERE 1=1";

if ($categoryFilter) {
    $query .= " AND ssa.category = '$categoryFilter'";
}

if ($dateFilter) {
    $query .= " AND ssa.date = '$dateFilter'";
}

$query .= " ORDER BY ssa.date DESC, ssa.category LIMIT 100";

$result = $conn->query($query);
$records = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<?php if (hasAnyRole(['Administrator', 'Clerk', 'Ministry Leader'])): ?>
<div class="card" id="addRecordForm">
    <div class="card-header">
        <h3 class="card-title">Add Attendance Record</h3>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" class="attendance-form">
        <div class="form-section">
            <h4 class="form-section-title">Basic Information</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Date *</label>
                        <input type="date" class="form-control" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Age Category *</label>
                        <select class="form-select" name="category" required>
                            <option value="">Select category...</option>
                            <option value="ages_3_under" <?php echo (isset($_POST['category']) && $_POST['category'] === 'ages_3_under') ? 'selected' : ''; ?>>Ages 3 and Under</option>
                            <option value="ages_9_11" <?php echo (isset($_POST['category']) && $_POST['category'] === 'ages_9_11') ? 'selected' : ''; ?>>Ages 9-11</option>
                            <option value="ages_12_above" <?php echo (isset($_POST['category']) && $_POST['category'] === 'ages_12_above') ? 'selected' : ''; ?>>Ages 12 and Above</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label">Attended</label>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="attended" id="attended" <?php echo (isset($_POST['attended']) || !isset($_POST['name'])) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="attended">Present</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h4 class="form-section-title">Child Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" placeholder="Child's name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" name="dob" value="<?php echo isset($_POST['dob']) ? $_POST['dob'] : ''; ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-section">
            <h4 class="form-section-title">Parent/Guardian Information</h4>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Parent/Guardian Name</label>
                        <input type="text" class="form-control" name="next_of_kin_name" value="<?php echo isset($_POST['next_of_kin_name']) ? htmlspecialchars($_POST['next_of_kin_name']) : ''; ?>" placeholder="Parent or guardian name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="next_of_kin_contact" value="<?php echo isset($_POST['next_of_kin_contact']) ? htmlspecialchars($_POST['next_of_kin_contact']) : ''; ?>" placeholder="Phone number">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Record
            </button>
            <button type="reset" class="btn btn-secondary">
                Clear Form
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="attendance-actions-bar">
    <div class="attendance-filters">
        <form method="GET" action="" class="attendance-filter-form">
            <select class="form-select" name="category">
                <option value="">All Categories</option>
                <option value="ages_3_under" <?php echo $categoryFilter === 'ages_3_under' ? 'selected' : ''; ?>>Ages 3 and Under</option>
                <option value="ages_9_11" <?php echo $categoryFilter === 'ages_9_11' ? 'selected' : ''; ?>>Ages 9-11</option>
                <option value="ages_12_above" <?php echo $categoryFilter === 'ages_12_above' ? 'selected' : ''; ?>>Ages 12+</option>
            </select>
            <input type="date" class="form-control" name="date" value="<?php echo $dateFilter; ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if ($categoryFilter || $dateFilter): ?>
            <a href="<?php echo BASE_URL; ?>attendance/sunday-school.php" class="btn btn-secondary btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Attendance Records</h3>
            <p class="card-subtitle">View and manage Sunday School attendance records</p>
        </div>
    </div>
    
    <?php if (count($records) > 0): ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>DOB</th>
                    <th>Next of Kin</th>
                    <th>Contact</th>
                    <th>Attended</th>
                    <th>Recorded By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo formatDate($record['date']); ?></td>
                    <td>
                        <?php
                        $categories = [
                            'ages_3_under' => 'Ages 3 and Under',
                            'ages_9_11' => 'Ages 9-11',
                            'ages_12_above' => 'Ages 12+'
                        ];
                        echo $categories[$record['category']] ?? $record['category'];
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($record['name'] ?: '-'); ?></td>
                    <td><?php echo formatDate($record['dob']); ?></td>
                    <td><?php echo htmlspecialchars($record['next_of_kin_name'] ?: '-'); ?></td>
                    <td><?php echo htmlspecialchars($record['next_of_kin_contact'] ?: '-'); ?></td>
                    <td>
                        <?php if ($record['attended']): ?>
                            <span class="badge badge-success">Yes</span>
                        <?php else: ?>
                            <span class="badge badge-danger">No</span>
                        <?php endif; ?>
                    </td>
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
        <i class="fas fa-child"></i>
        <h3>No records found</h3>
        <p>Add your first Sunday School attendance record above.</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

