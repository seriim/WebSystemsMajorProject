<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

$pageTitle = 'Events';
$pageSubtitle = 'Schedule and manage church events';

$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete']) && hasAnyRole(['Administrator', 'Clerk'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Events WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: ' . BASE_URL . 'events/index.php?deleted=1');
        exit();
    } else {
        $error = 'Error deleting event: ' . $conn->error;
        $stmt->close();
        header('Location: ' . BASE_URL . 'events/index.php?error=' . urlencode($error));
        exit();
    }
}

// Get statistics
$result = $conn->query("SELECT COUNT(*) as count FROM Events WHERE date >= CURRENT_DATE");
$upcoming = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Events WHERE date < CURRENT_DATE");
$past = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM Events");
$total = $result->fetch_assoc()['count'];

// Get upcoming events
$result = $conn->query("
    SELECT e.*, m.first_name, m.last_name
    FROM Events e 
    LEFT JOIN Members m ON e.member_id = m.mem_id 
    WHERE e.date >= CURRENT_DATE 
    ORDER BY e.date ASC 
    LIMIT 10
");
$upcomingEvents = $result->fetch_all(MYSQLI_ASSOC);

// Get past events
$result = $conn->query("
    SELECT e.*, m.first_name, m.last_name
    FROM Events e 
    LEFT JOIN Members m ON e.member_id = m.mem_id 
    WHERE e.date < CURRENT_DATE 
    ORDER BY e.date DESC 
    LIMIT 10
");
$pastEvents = $result->fetch_all(MYSQLI_ASSOC);

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Upcoming Events</div>
        <div class="stat-value"><?php echo $upcoming; ?></div>
        <div class="stat-description">Scheduled events</div>
    </div>
    
    <div class="stat-card yellow">
        <div class="stat-label">Past Events</div>
        <div class="stat-value"><?php echo $past; ?></div>
        <div class="stat-description">Completed events</div>
    </div>
    
    <div class="stat-card blue">
        <div class="stat-label">Total Events</div>
        <div class="stat-value"><?php echo $total; ?></div>
        <div class="stat-description">All time</div>
    </div>
</div>

<div class="page-actions">
    <div></div>
    <?php if (hasAnyRole(['Administrator', 'Clerk', 'Pastor'])): ?>
    <a href="<?php echo BASE_URL; ?>events/add.php" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Add Event
    </a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success">Event deleted successfully.</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Upcoming Events</h3>
                    <p class="card-subtitle">Events scheduled for the future</p>
                </div>
            </div>
            
            <?php if (count($upcomingEvents) > 0): ?>
                <?php foreach ($upcomingEvents as $event): ?>
                <div style="padding: 16px; border-bottom: 1px solid var(--gray-medium);">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0 0 8px 0; font-size: 16px;">
                                <?php echo htmlspecialchars(ucfirst($event['event_type'])); ?>
                            </h5>
                            <?php if ($event['first_name']): ?>
                                <p style="margin: 0 0 4px 0; color: var(--text-light); font-size: 13px;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?>
                                </p>
                            <?php endif; ?>
                            <p style="margin: 4px 0; color: var(--text-light); font-size: 13px;">
                                <i class="fas fa-calendar"></i> <?php echo formatDate($event['date']); ?>
                            </p>
                            <?php if (!empty($event['notes'])): ?>
                                <p style="margin: 8px 0 0 0; font-size: 14px;"><?php echo htmlspecialchars(substr($event['notes'], 0, 100)); ?><?php echo strlen($event['notes']) > 100 ? '...' : ''; ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="badge badge-success"><?php echo ucfirst($event['event_type']); ?></span>
                        </div>
                    </div>
                    <?php if (hasAnyRole(['Administrator', 'Clerk'])): ?>
                    <div style="margin-top: 12px;">
                        <a href="<?php echo BASE_URL; ?>events/view.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        <a href="<?php echo BASE_URL; ?>events/edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No upcoming events scheduled</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title">Past Events</h3>
                    <p class="card-subtitle">Completed events history</p>
                </div>
            </div>
            
            <?php if (count($pastEvents) > 0): ?>
                <?php foreach ($pastEvents as $event): ?>
                <div style="padding: 16px; border-bottom: 1px solid var(--gray-medium);">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0 0 8px 0; font-size: 16px;">
                                <?php echo htmlspecialchars(ucfirst($event['event_type'])); ?>
                            </h5>
                            <?php if ($event['first_name']): ?>
                                <p style="margin: 0 0 4px 0; color: var(--text-light); font-size: 13px;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?>
                                </p>
                            <?php endif; ?>
                            <p style="margin: 4px 0; color: var(--text-light); font-size: 13px;">
                                <i class="fas fa-calendar"></i> <?php echo formatDate($event['date']); ?>
                            </p>
                            <?php if (!empty($event['notes'])): ?>
                                <p style="margin: 8px 0 0 0; font-size: 14px;"><?php echo htmlspecialchars(substr($event['notes'], 0, 100)); ?><?php echo strlen($event['notes']) > 100 ? '...' : ''; ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="badge badge-warning"><?php echo ucfirst($event['event_type']); ?></span>
                        </div>
                    </div>
                    <?php if (hasAnyRole(['Administrator', 'Clerk'])): ?>
                    <div style="margin-top: 12px;">
                        <a href="<?php echo BASE_URL; ?>events/view.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        <a href="<?php echo BASE_URL; ?>events/edit.php?id=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-check"></i>
                <h3>No past events</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

