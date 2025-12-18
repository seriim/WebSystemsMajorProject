<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();

if (!hasAnyRole(['Administrator', 'Clerk/Secretary', 'Pastor/Clergy'])) {
    header('Location: ' . BASE_URL . 'members/index.php');
    exit();
}

$pageTitle = 'Add Member';
$pageSubtitle = 'Register a new church member';

$conn = getDBConnection();

// Get ministries for dropdown
$ministriesResult = $conn->query("SELECT id, name FROM ministries ORDER BY name");
$ministries = $ministriesResult->fetch_all(MYSQLI_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitizeInput($_POST['first_name']);
    $middle_initials = sanitizeInput($_POST['middle_initials']);
    $last_name = sanitizeInput($_POST['last_name']);
    $dob = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender = sanitizeInput($_POST['gender']);
    $home_address_1 = sanitizeInput($_POST['home_address_1']);
    $home_address_2 = sanitizeInput($_POST['home_address_2']);
    $town = sanitizeInput($_POST['town']);
    $parish = sanitizeInput($_POST['parish']);
    $home_phone = sanitizeInput($_POST['home_phone']);
    $work_phone = sanitizeInput($_POST['work_phone']);
    $email = sanitizeInput($_POST['email']);
    $next_of_kin_name = sanitizeInput($_POST['next_of_kin_name']);
    $next_of_kin_address = sanitizeInput($_POST['next_of_kin_address']);
    $next_of_kin_relation = sanitizeInput($_POST['next_of_kin_relation']);
    $next_of_kin_contact = sanitizeInput($_POST['next_of_kin_contact']);
    $next_of_kin_email = sanitizeInput($_POST['next_of_kin_email']);
    $status = sanitizeInput($_POST['status']);
    $date_joined = !empty($_POST['date_joined']) ? $_POST['date_joined'] : date('Y-m-d');
    $date_baptism = !empty($_POST['date_baptism']) ? $_POST['date_baptism'] : null;
    $baptised_by = sanitizeInput($_POST['baptised_by']);
    $marital_status = sanitizeInput($_POST['marital_status']);
    $spouse_name = sanitizeInput($_POST['spouse_name']);
    $number_dependents = intval($_POST['number_dependents']);
    $employer = sanitizeInput($_POST['employer']);
    $employer_address = sanitizeInput($_POST['employer_address']);
    $employer_phone = sanitizeInput($_POST['employer_phone']);
    $occupation = sanitizeInput($_POST['occupation']);
    $other_skills = sanitizeInput($_POST['other_skills']);
    $special_needs = sanitizeInput($_POST['special_needs']);
    $selected_ministries = isset($_POST['ministries']) ? $_POST['ministries'] : [];
    
    if (empty($first_name) || empty($last_name)) {
        $error = 'First name and last name are required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO members (first_name, middle_initials, last_name, dob, gender, home_address_1, home_address_2, town, parish, home_phone, work_phone, email, next_of_kin_name, next_of_kin_address, next_of_kin_relation, next_of_kin_contact, next_of_kin_email, status, date_joined, date_baptism, baptised_by, marital_status, spouse_name, number_dependents, employer, employer_address, employer_phone, occupation, other_skills, special_needs) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Type string: 23 strings + 1 integer (number_dependents) + 6 strings = 30 total
        $stmt->bind_param("sssssssssssssssssssssssiss sssss", 
            $first_name, $middle_initials, $last_name, $dob, $gender, $home_address_1, $home_address_2, $town, $parish, $home_phone, $work_phone, $email, $next_of_kin_name, $next_of_kin_address, $next_of_kin_relation, $next_of_kin_contact, $next_of_kin_email, $status, $date_joined, $date_baptism, $baptised_by, $marital_status, $spouse_name, $number_dependents, $employer, $employer_address, $employer_phone, $occupation, $other_skills, $special_needs);
        
        if ($stmt->execute()) {
            $member_id = $conn->insert_id;
            
            // Add to ministries
            foreach ($selected_ministries as $ministry_id) {
                $ministry_id = intval($ministry_id);
                $stmt2 = $conn->prepare("INSERT INTO ministry_members (member_id, ministry_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $member_id, $ministry_id);
                $stmt2->execute();
                $stmt2->close();
            }
            
            $stmt->close();
            header('Location: ' . BASE_URL . 'members/index.php?added=1');
            exit();
        } else {
            $error = 'Error adding member: ' . $conn->error;
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
                
                <div class="form-group">
                    <label class="form-label">Marital Status</label>
                    <select class="form-select" name="marital_status">
                        <option value="">Select...</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Spouse Name</label>
                    <input type="text" class="form-control" name="spouse_name">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Number of Dependents</label>
                    <input type="number" class="form-control" name="number_dependents" min="0" value="0">
                </div>
            </div>
            
            <div class="col-md-6">
                <h5 class="mt-3 mb-3">Contact Information</h5>
                
                <div class="form-group">
                    <label class="form-label">Home Address 1</label>
                    <input type="text" class="form-control" name="home_address_1">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Home Address 2</label>
                    <input type="text" class="form-control" name="home_address_2">
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
                    <input type="tel" class="form-control" name="home_phone">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Work Phone</label>
                    <input type="tel" class="form-control" name="work_phone">
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
            
            <div class="col-md-6">
                <h5 class="mb-3">Employment & Skills</h5>
                
                <div class="form-group">
                    <label class="form-label">Employer</label>
                    <input type="text" class="form-control" name="employer">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Employer Address</label>
                    <input type="text" class="form-control" name="employer_address">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Employer Phone</label>
                    <input type="tel" class="form-control" name="employer_phone">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Occupation</label>
                    <input type="text" class="form-control" name="occupation">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Other Skills</label>
                    <textarea class="form-control" name="other_skills" rows="3"></textarea>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <h5 class="mb-3">Church Information</h5>
                
                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        <option value="visitor">Visitor</option>
                        <option value="adherent">Adherent</option>
                        <option value="member">Member</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Joined</label>
                    <input type="date" class="form-control" name="date_joined" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date of Baptism</label>
                    <input type="date" class="form-control" name="date_baptism">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Baptised By</label>
                    <input type="text" class="form-control" name="baptised_by">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Ministries</label>
                    <select class="form-select" name="ministries[]" multiple size="6">
                        <?php foreach ($ministries as $ministry): ?>
                            <option value="<?php echo $ministry['id']; ?>"><?php echo htmlspecialchars($ministry['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted" style="display: block; margin-top: 6px; font-size: 12px;">
                        Hold <kbd>Ctrl</kbd> (Windows) or <kbd>Cmd</kbd> (Mac) to select multiple ministries
                    </small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Special Needs</label>
                    <textarea class="form-control" name="special_needs" rows="3" placeholder="Any special needs or requirements..."></textarea>
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

