<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasAnyRole(['Administrator', 'Clerk', 'Pastor'])) {
    header('Location: ' . BASE_URL . 'members/index.php');
    exit();
}

$pageTitle = 'Add Member';
$pageSubtitle = 'Register a new church member';

$conn = getDBConnection();

// Get ministries for dropdown
$ministries = [];
$ministriesResult = $conn->query("SELECT id, name FROM Ministries ORDER BY name");
if ($ministriesResult) {
    $ministries = $ministriesResult->fetch_all(MYSQLI_ASSOC);
} else {
    error_log("Error fetching ministries: " . $conn->error);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $middle_initials = sanitizeInput($_POST['middle_initials']);
    $last_name = sanitizeInput($_POST['last_name']);
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender = !empty(trim($_POST['gender'])) ? sanitizeInput($_POST['gender']) : null;
    $home_address1 = sanitizeInput($_POST['home_address1']);
    $home_address2 = sanitizeInput($_POST['home_address2']);
    $town = sanitizeInput($_POST['town']);
    $parish = sanitizeInput($_POST['parish']);
    $contact_home = !empty(trim($_POST['contact_home'])) ? sanitizeInput($_POST['contact_home']) : null;
    $contact_work = !empty(trim($_POST['contact_work'])) ? sanitizeInput($_POST['contact_work']) : null;
    $email = !empty(trim($_POST['email'])) ? sanitizeInput($_POST['email']) : null;
    $next_of_kin_name = !empty(trim($_POST['next_of_kin_name'])) ? sanitizeInput($_POST['next_of_kin_name']) : null;
    $next_of_kin_address = !empty(trim($_POST['next_of_kin_address'])) ? sanitizeInput($_POST['next_of_kin_address']) : null;
    $next_of_kin_relation = !empty(trim($_POST['next_of_kin_relation'])) ? sanitizeInput($_POST['next_of_kin_relation']) : null;
    $next_of_kin_contact = !empty(trim($_POST['next_of_kin_contact'])) ? sanitizeInput($_POST['next_of_kin_contact']) : null;
    $next_of_kin_email = !empty(trim($_POST['next_of_kin_email'])) ? sanitizeInput($_POST['next_of_kin_email']) : null;
    $status = sanitizeInput($_POST['status']);
    $date_joined = !empty($_POST['date_joined']) ? $_POST['date_joined'] : date('Y-m-d');
    $selected_ministries = isset($_POST['ministries']) ? $_POST['ministries'] : [];
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'First name and last name are required.';
    } else {
        // Truncate fields to match database column sizes (before converting to null)
        $first_name = truncateToLength($first_name, 50);
        $middle_initials = !empty(trim($middle_initials)) ? truncateToLength($middle_initials, 10) : null;
        $last_name = truncateToLength($last_name, 50);
        $home_address1 = !empty(trim($home_address1)) ? truncateToLength($home_address1, 100) : null;
        $home_address2 = !empty(trim($home_address2)) ? truncateToLength($home_address2, 100) : null;
        $town = !empty(trim($town)) ? truncateToLength($town, 50) : null;
        $parish = !empty(trim($parish)) ? truncateToLength($parish, 50) : null;
        $contact_home = $contact_home ? (!empty(trim($contact_home)) ? truncateToLength($contact_home, 20) : null) : null;
        $contact_work = $contact_work ? (!empty(trim($contact_work)) ? truncateToLength($contact_work, 20) : null) : null;
        $email = $email ? (!empty(trim($email)) ? truncateToLength($email, 100) : null) : null;
        $next_of_kin_name = $next_of_kin_name ? (!empty(trim($next_of_kin_name)) ? truncateToLength($next_of_kin_name, 100) : null) : null;
        $next_of_kin_address = $next_of_kin_address ? (!empty(trim($next_of_kin_address)) ? truncateToLength($next_of_kin_address, 150) : null) : null;
        $next_of_kin_relation = $next_of_kin_relation ? (!empty(trim($next_of_kin_relation)) ? truncateToLength($next_of_kin_relation, 50) : null) : null;
        $next_of_kin_contact = $next_of_kin_contact ? (!empty(trim($next_of_kin_contact)) ? truncateToLength($next_of_kin_contact, 20) : null) : null;
        $next_of_kin_email = $next_of_kin_email ? (!empty(trim($next_of_kin_email)) ? truncateToLength($next_of_kin_email, 100) : null) : null;
        
        $stmt = $conn->prepare("INSERT INTO Members (first_name, middle_initials, last_name, dob, gender, home_address1, home_address2, town, parish, contact_home, contact_work, email, next_of_kin_name, next_of_kin_address, next_of_kin_relation, next_of_kin_contact, next_of_kin_email, status, date_joined) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssssssssssssssssss", 
            $first_name, $middle_initials, $last_name, $dob, $gender, $home_address1, $home_address2, $town, $parish, $contact_home, $contact_work, $email, $next_of_kin_name, $next_of_kin_address, $next_of_kin_relation, $next_of_kin_contact, $next_of_kin_email, $status, $date_joined);
        
        if ($stmt->execute()) {
            $member_id = $conn->insert_id;
            
            // Add to ministries
            $ministryError = false;
            foreach ($selected_ministries as $ministry_id) {
                $ministry_id = intval($ministry_id);
                $stmt2 = $conn->prepare("INSERT INTO Ministry_Members (member_id, ministry_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $member_id, $ministry_id);
                if (!$stmt2->execute()) {
                    $error = 'Error adding ministry: ' . $conn->error;
                    $ministryError = true;
                    $stmt2->close();
                    break;
                }
                $stmt2->close();
            }
            
            if (!$ministryError) {
                $stmt->close();
                header('Location: ' . BASE_URL . 'members/view.php?id=' . $member_id . '&added=1');
                exit();
            } else {
                $stmt->close();
            }
        } else {
            $error = 'Error adding member: ' . $conn->error;
            $stmt->close();
        }
    }
}

closeDBConnection($conn);

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Add New Member</h3>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" data-validate>
        <div class="row">
            <div class="col-md-6">
                <h5 class="mt-3 mb-3">Personal Information</h5>
                
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Middle Initials</label>
                    <input type="text" class="form-control" name="middle_initials" maxlength="10">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" class="form-control" name="last_name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="dob">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select class="form-select" name="gender">
                        <option value="">Select...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-6">
                <h5 class="mt-3 mb-3">Contact Information</h5>
                
                <div class="form-group">
                    <label class="form-label">Home Address 1</label>
                    <input type="text" class="form-control" name="home_address1">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Home Address 2</label>
                    <input type="text" class="form-control" name="home_address2">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Town</label>
                    <input type="text" class="form-control" name="town">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Parish</label>
                    <input type="text" class="form-control" name="parish">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Home Phone</label>
                    <input type="tel" class="form-control" name="contact_home">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Work Phone</label>
                    <input type="tel" class="form-control" name="contact_work">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h5 class="mb-3">Next of Kin</h5>
                
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" name="next_of_kin_name">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="next_of_kin_address">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Relation</label>
                    <input type="text" class="form-control" name="next_of_kin_relation">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" class="form-control" name="next_of_kin_contact">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="next_of_kin_email">
                </div>
            </div>
            
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h5 class="mb-3">Church Information</h5>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        <option value="Visitor">Visitor</option>
                        <option value="Adherent">Adherent</option>
                        <option value="Member">Member</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Joined</label>
                    <input type="date" class="form-control" name="date_joined" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ministries</label>
                    <?php if (count($ministries) > 0): ?>
                        <select class="form-select" name="ministries[]" multiple size="6">
                            <?php foreach ($ministries as $ministry): ?>
                                <option value="<?php echo $ministry['id']; ?>"><?php echo htmlspecialchars($ministry['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted" style="display: block; margin-top: 6px; font-size: 12px;">
                            Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac) to select multiple ministries
                        </small>
                    <?php else: ?>
                        <div class="alert alert-info" style="margin-top: 8px;">
                            <i class="fas fa-info-circle"></i> No ministries available. <a href="<?php echo BASE_URL; ?>ministries/add.php">Add a ministry</a> first.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Save Member
            </button>
            <a href="<?php echo BASE_URL; ?>members/index.php" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

