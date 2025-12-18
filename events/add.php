<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasAnyRole(['Administrator', 'Clerk', 'Pastor'])) {
    header('Location: ' . BASE_URL . 'events/index.php');
    exit();
}

$pageTitle = 'Add Event';
$pageSubtitle = 'Record a new church event or milestone';

$conn = getDBConnection();

// Get members for dropdown
$membersResult = $conn->query("SELECT mem_id, first_name, last_name FROM Members ORDER BY last_name, first_name");
$members = $membersResult->fetch_all(MYSQLI_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_type = sanitizeInput($_POST['event_type']);
    $date = $_POST['date'];
    $member_id = !empty($_POST['member_id']) ? intval($_POST['member_id']) : 0;
    $notes = sanitizeInput($_POST['notes']);
    
    if (empty($event_type) || empty($date) || empty($member_id)) {
        $error = 'Event type, date, and member are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO Events (event_type, date, member_id, notes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $event_type, $date, $member_id, $notes);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: ' . BASE_URL . 'events/index.php?added=1');
            exit();
        } else {
            $error = 'Error adding event: ' . $conn->error;
        }
    }
}

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Event</h3>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" data-validate>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Event Type *</label>
                    <select class="form-select" name="event_type" required>
                        <option value="">Select...</option>
                        <option value="Wedding">Wedding</option>
                        <option value="Birthday">Birthday</option>
                        <option value="Anniversary">Anniversary</option>
                        <option value="Baptism">Baptism</option>
                        <option value="Death">Death</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" class="form-control" name="date" value="<?php echo isset($_POST['date']) ? $_POST['date'] : date('Y-m-d'); ?>" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Member *</label>
                    <select class="form-select" name="member_id" required>
                        <option value="">Select Member...</option>
                        <?php foreach ($members as $member): ?>
                            <option value="<?php echo $member['mem_id']; ?>" <?php echo (isset($_POST['member_id']) && $_POST['member_id'] == $member['mem_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="3" placeholder="Additional notes about the event..." maxlength="300"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
            <small class="form-text text-muted">Maximum 300 characters</small>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Event
            </button>
            <a href="<?php echo BASE_URL; ?>events/index.php" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

