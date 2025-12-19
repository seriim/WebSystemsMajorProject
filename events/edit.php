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

if (!hasAnyRole(['Administrator', 'Clerk'])) {
    header('Location: ' . BASE_URL . 'events/index.php');
    exit();
}

$pageTitle = 'Edit Event';
$pageSubtitle = 'Update event information';

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'events/index.php');
    exit();
}

$conn = getDBConnection();
$event_id = intval($_GET['id']);

// Get event data
$stmt = $conn->prepare("SELECT * FROM Events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . BASE_URL . 'events/index.php');
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

// Get members
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
        $stmt = $conn->prepare("UPDATE Events SET event_type=?, date=?, member_id=?, notes=? WHERE id=?");
        $stmt->bind_param("ssisi", $event_type, $date, $member_id, $notes, $event_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            header('Location: ' . BASE_URL . 'events/view.php?id=' . $event_id . '&updated=1');
            exit();
        } else {
            $error = 'Error updating event: ' . $conn->error;
        }
    }
}

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit Event</h3>
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
                        <option value="Wedding" <?php echo $event['event_type'] === 'Wedding' ? 'selected' : ''; ?>>Wedding</option>
                        <option value="Birthday" <?php echo $event['event_type'] === 'Birthday' ? 'selected' : ''; ?>>Birthday</option>
                        <option value="Anniversary" <?php echo $event['event_type'] === 'Anniversary' ? 'selected' : ''; ?>>Anniversary</option>
                        <option value="Baptism" <?php echo $event['event_type'] === 'Baptism' ? 'selected' : ''; ?>>Baptism</option>
                        <option value="Death" <?php echo $event['event_type'] === 'Death' ? 'selected' : ''; ?>>Death</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" class="form-control" name="date" value="<?php echo $event['date']; ?>" required>
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
                            <option value="<?php echo $member['mem_id']; ?>" <?php echo $event['member_id'] == $member['mem_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="3" maxlength="300"><?php echo htmlspecialchars($event['notes'] ?: ''); ?></textarea>
            <small class="form-text text-muted">Maximum 300 characters</small>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Update Event
            </button>
            <a href="<?php echo BASE_URL; ?>events/view.php?id=<?php echo $event_id; ?>" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

