<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'View Event';
$pageSubtitle = 'Event details';

if (!isset($_GET['id'])) {
    header('Location: ' . BASE_URL . 'events/index.php');
    exit();
}

$conn = getDBConnection();
$event_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT e.*, m.first_name, m.last_name FROM Events e LEFT JOIN Members m ON e.member_id = m.mem_id WHERE e.id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: ' . BASE_URL . 'events/index.php');
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title"><?php echo htmlspecialchars(ucfirst($event['event_type'])); ?></h3>
            <p class="card-subtitle">Event ID: <?php echo $event['id']; ?></p>
        </div>
        <div>
            <?php if (hasAnyRole(['Administrator', 'Clerk'])): ?>
            <a href="<?php echo BASE_URL; ?>events/edit.php?id=<?php echo $event['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>events/index.php" class="btn btn-secondary">
                Back to List
            </a>
        </div>
    </div>
    
    <table class="table">
        <tr>
            <th width="30%">Event Type</th>
            <td><span class="badge badge-success"><?php echo ucfirst($event['event_type']); ?></span></td>
        </tr>
        <tr>
            <th>Date</th>
            <td><?php echo formatDate($event['date']); ?></td>
        </tr>
        <?php if ($event['first_name']): ?>
        <tr>
            <th>Member</th>
            <td><?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($event['notes']): ?>
        <tr>
            <th>Notes</th>
            <td><?php echo nl2br(htmlspecialchars($event['notes'])); ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

