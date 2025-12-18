<?php
/**
 * Comprehensive Test Suite
 * Tests all pages, data loading, and database synchronization
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "Comprehensive System Test Suite\n";
echo "========================================\n\n";

$conn = getDBConnection();
$errors = [];
$warnings = [];
$success = [];

// Test 1: Verify all tables exist and have data
echo "1. Testing Database Tables and Data...\n";
$tables = ['Roles', 'Users', 'Members', 'Ministries', 'Ministry_Members', 'Events', 'Attendance'];

foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $success[] = "✓ Table '$table' has {$row['count']} records";
        echo "   ✓ $table: {$row['count']} records\n";
    } else {
        $warnings[] = "⚠ Table '$table' is empty";
        echo "   ⚠ $table: No records\n";
    }
}
echo "\n";

// Test 2: Test all main page queries
echo "2. Testing Main Page Queries...\n";

// Dashboard query (index.php)
$query = "SELECT COUNT(*) as count FROM Members";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Dashboard member count query works";
    echo "   ✓ Dashboard queries: OK\n";
} else {
    $errors[] = "✗ Dashboard query failed: " . $conn->error;
    echo "   ✗ Dashboard queries: Failed\n";
}

// Members listing query
$query = "SELECT m.*, GROUP_CONCAT(DISTINCT mi.name SEPARATOR ', ') as ministries
          FROM Members m
          LEFT JOIN Ministry_Members mm ON m.mem_id = mm.member_id
          LEFT JOIN Ministries mi ON mm.ministry_id = mi.id
          GROUP BY m.mem_id 
          ORDER BY m.last_name, m.first_name
          LIMIT 5";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Members listing query works";
    echo "   ✓ Members listing: OK\n";
} else {
    $errors[] = "✗ Members listing query failed: " . $conn->error;
    echo "   ✗ Members listing: Failed\n";
}

// Events query
$query = "SELECT e.*, m.first_name, m.last_name
          FROM Events e 
          LEFT JOIN Members m ON e.member_id = m.mem_id 
          ORDER BY e.date DESC 
          LIMIT 5";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Events query works";
    echo "   ✓ Events queries: OK\n";
} else {
    $errors[] = "✗ Events query failed: " . $conn->error;
    echo "   ✗ Events queries: Failed\n";
}

// Ministries query
$query = "SELECT m.*, COUNT(mm.id) as member_count 
          FROM Ministries m 
          LEFT JOIN Ministry_Members mm ON m.id = mm.ministry_id
          GROUP BY m.id 
          ORDER BY m.name";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Ministries query works";
    echo "   ✓ Ministries queries: OK\n";
} else {
    $errors[] = "✗ Ministries query failed: " . $conn->error;
    echo "   ✗ Ministries queries: Failed\n";
}

// Ministry view query (the one we just fixed)
$query = "SELECT m.*, mm.role FROM Members m 
          INNER JOIN Ministry_Members mm ON m.mem_id = mm.member_id 
          WHERE mm.ministry_id = 1 
          ORDER BY m.last_name, m.first_name
          LIMIT 5";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Ministry view query works";
    echo "   ✓ Ministry view query: OK\n";
} else {
    $errors[] = "✗ Ministry view query failed: " . $conn->error;
    echo "   ✗ Ministry view query: Failed\n";
}

// Attendance queries
$query = "SELECT a.*, m.name as ministry_name 
          FROM Attendance a 
          JOIN Ministries m ON a.ministry_id = m.id 
          ORDER BY a.date DESC 
          LIMIT 5";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Attendance queries work";
    echo "   ✓ Attendance queries: OK\n";
} else {
    $errors[] = "✗ Attendance query failed: " . $conn->error;
    echo "   ✗ Attendance queries: Failed\n";
}

// Users query
$query = "SELECT u.*, r.role_name FROM Users u 
          LEFT JOIN Roles r ON u.role = r.id 
          ORDER BY u.id";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Users query works";
    echo "   ✓ Users queries: OK\n";
} else {
    $errors[] = "✗ Users query failed: " . $conn->error;
    echo "   ✗ Users queries: Failed\n";
}
echo "\n";

// Test 3: Test data relationships
echo "3. Testing Data Relationships...\n";

// Test member-ministry relationships
$query = "SELECT COUNT(*) as count FROM Ministry_Members mm 
          LEFT JOIN Members m ON mm.member_id = m.mem_id 
          WHERE m.mem_id IS NULL";
$result = $conn->query($query);
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $success[] = "✓ All ministry memberships have valid member references";
    echo "   ✓ Ministry-Member relationships: OK\n";
} else {
    $errors[] = "✗ Found {$row['count']} orphaned ministry memberships";
    echo "   ✗ Ministry-Member relationships: {$row['count']} orphaned\n";
}

// Test event-member relationships
$query = "SELECT COUNT(*) as count FROM Events e 
          LEFT JOIN Members m ON e.member_id = m.mem_id 
          WHERE m.mem_id IS NULL";
$result = $conn->query($query);
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $success[] = "✓ All events have valid member references";
    echo "   ✓ Event-Member relationships: OK\n";
} else {
    $errors[] = "✗ Found {$row['count']} orphaned events";
    echo "   ✗ Event-Member relationships: {$row['count']} orphaned\n";
}

// Test attendance-ministry relationships
$query = "SELECT COUNT(*) as count FROM Attendance a 
          LEFT JOIN Ministries m ON a.ministry_id = m.id 
          WHERE m.id IS NULL";
$result = $conn->query($query);
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $success[] = "✓ All attendance records have valid ministry references";
    echo "   ✓ Attendance-Ministry relationships: OK\n";
} else {
    $errors[] = "✗ Found {$row['count']} orphaned attendance records";
    echo "   ✗ Attendance-Ministry relationships: {$row['count']} orphaned\n";
}

// Test user-role relationships
$query = "SELECT COUNT(*) as count FROM Users u 
          LEFT JOIN Roles r ON u.role = r.id 
          WHERE r.id IS NULL";
$result = $conn->query($query);
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $success[] = "✓ All users have valid role references";
    echo "   ✓ User-Role relationships: OK\n";
} else {
    $errors[] = "✗ Found {$row['count']} users with invalid roles";
    echo "   ✗ User-Role relationships: {$row['count']} invalid\n";
}
echo "\n";

// Test 4: Test column access (check for missing columns)
echo "4. Testing Column Access...\n";

$column_tests = [
    "SELECT first_name, last_name, email, contact_home, contact_work, status, date_joined FROM Members LIMIT 1" => "Members table columns",
    "SELECT event_type, date, member_id, notes FROM Events LIMIT 1" => "Events table columns",
    "SELECT name, description FROM Ministries LIMIT 1" => "Ministries table columns",
    "SELECT member_id, ministry_id, role FROM Ministry_Members LIMIT 1" => "Ministry_Members table columns",
    "SELECT date, ministry_id, count, recorded_by FROM Attendance LIMIT 1" => "Attendance table columns",
    "SELECT username, role, status FROM Users LIMIT 1" => "Users table columns"
];

foreach ($column_tests as $query => $description) {
    $result = $conn->query($query);
    if ($result) {
        $success[] = "✓ $description: OK";
        echo "   ✓ $description: OK\n";
    } else {
        $errors[] = "✗ $description failed: " . $conn->error;
        echo "   ✗ $description: Failed - " . $conn->error . "\n";
    }
}
echo "\n";

// Test 5: Test reports queries
echo "5. Testing Reports Queries...\n";

$reports_tests = [
    "SELECT COUNT(*) as count, status FROM Members GROUP BY status" => "Membership status report",
    "SELECT event_type, COUNT(*) as count FROM Events GROUP BY event_type" => "Event type summary",
    "SELECT m.name, COUNT(mm.id) as member_count FROM Ministries m LEFT JOIN Ministry_Members mm ON m.id = mm.ministry_id GROUP BY m.id" => "Ministry member counts",
    "SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(count) as total FROM Attendance GROUP BY month ORDER BY month DESC LIMIT 6" => "Monthly attendance"
];

foreach ($reports_tests as $query => $description) {
    $result = $conn->query($query);
    if ($result) {
        $success[] = "✓ $description: OK";
        echo "   ✓ $description: OK\n";
    } else {
        $errors[] = "✗ $description failed: " . $conn->error;
        echo "   ✗ $description: Failed - " . $conn->error . "\n";
    }
}
echo "\n";

// Test 6: Test upcoming events/birthdays queries (from reports)
echo "6. Testing Upcoming Events Queries...\n";

// Upcoming birthdays
$query = "SELECT first_name, last_name, dob FROM Members 
          WHERE MONTH(dob) = MONTH(CURDATE()) 
          AND DAY(dob) >= DAY(CURDATE())
          ORDER BY DAY(dob) 
          LIMIT 10";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Upcoming birthdays query works";
    echo "   ✓ Upcoming birthdays: OK\n";
} else {
    $errors[] = "✗ Upcoming birthdays query failed: " . $conn->error;
    echo "   ✗ Upcoming birthdays: Failed\n";
}

// Upcoming events
$query = "SELECT e.*, m.first_name, m.last_name 
          FROM Events e 
          JOIN Members m ON e.member_id = m.mem_id 
          WHERE e.date >= CURDATE() 
          ORDER BY e.date ASC 
          LIMIT 10";
$result = $conn->query($query);
if ($result) {
    $success[] = "✓ Upcoming events query works";
    echo "   ✓ Upcoming events: OK\n";
} else {
    $errors[] = "✗ Upcoming events query failed: " . $conn->error;
    echo "   ✗ Upcoming events: Failed\n";
}
echo "\n";

// Test 7: Test all role-based queries
echo "7. Testing Role-Based Access Queries...\n";

// Test user authentication query
$test_username = 'test_admin';
$stmt = $conn->prepare("SELECT u.id, u.username, u.password, u.status, r.role_name 
                        FROM Users u 
                        JOIN Roles r ON u.role = r.id 
                        WHERE u.username = ?");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify('test123', $user['password'])) {
        $success[] = "✓ User authentication query works";
        echo "   ✓ User authentication: OK\n";
    } else {
        $errors[] = "✗ Password verification failed for test user";
        echo "   ✗ User authentication: Password verification failed\n";
    }
} else {
    $errors[] = "✗ Test user not found";
    echo "   ✗ User authentication: Test user not found\n";
}
$stmt->close();
echo "\n";

// Test 8: Verify data consistency
echo "8. Testing Data Consistency...\n";

// Check for duplicate emails
$query = "SELECT email, COUNT(*) as count FROM Members 
          WHERE email IS NOT NULL AND email != '' 
          GROUP BY email 
          HAVING count > 1";
$result = $conn->query($query);
if ($result->num_rows == 0) {
    $success[] = "✓ No duplicate emails";
    echo "   ✓ Email uniqueness: OK\n";
} else {
    $warnings[] = "⚠ Found duplicate emails";
    echo "   ⚠ Email uniqueness: Found duplicates\n";
}

// Check ENUM values
$enum_checks = [
    "SELECT COUNT(*) as count FROM Members WHERE gender NOT IN ('Male', 'Female', 'Other') AND gender IS NOT NULL" => "Invalid gender values",
    "SELECT COUNT(*) as count FROM Members WHERE status NOT IN ('Member', 'Adherent', 'Visitor')" => "Invalid member status",
    "SELECT COUNT(*) as count FROM Users WHERE status NOT IN ('Active', 'Inactive')" => "Invalid user status",
    "SELECT COUNT(*) as count FROM Events WHERE event_type NOT IN ('Wedding', 'Birthday', 'Anniversary', 'Baptism', 'Death') AND event_type IS NOT NULL" => "Invalid event types"
];

foreach ($enum_checks as $query => $description) {
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $success[] = "✓ $description: OK";
        echo "   ✓ $description: OK\n";
    } else {
        $errors[] = "✗ $description: {$row['count']} invalid values";
        echo "   ✗ $description: {$row['count']} invalid\n";
    }
}
echo "\n";

// Summary
echo "========================================\n";
echo "Test Summary\n";
echo "========================================\n";
echo "Success: " . count($success) . "\n";
echo "Warnings: " . count($warnings) . "\n";
echo "Errors: " . count($errors) . "\n\n";

if (count($errors) > 0) {
    echo "ERRORS FOUND:\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  $warning\n";
    }
    echo "\n";
}

if (count($errors) == 0) {
    echo "✓ All tests passed! System is fully synced and working.\n";
    exit(0);
} else {
    echo "✗ Some tests failed. Please review the errors above.\n";
    exit(1);
}

closeDBConnection($conn);
?>

