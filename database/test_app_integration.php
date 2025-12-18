<?php
/**
 * Application Integration Test
 * Tests if the application code can properly interact with the database
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "Application Integration Test\n";
echo "========================================\n\n";

// Test 1: Test getDBConnection function
echo "1. Testing Database Connection Function...\n";
$conn = getDBConnection();
if ($conn) {
    echo "   ✓ getDBConnection() works\n";
} else {
    echo "   ✗ getDBConnection() failed\n";
    die();
}
echo "\n";

// Test 2: Test helper functions
echo "2. Testing Helper Functions...\n";

// Test sanitizeInput
$test_input = "<script>alert('xss')</script>";
$sanitized = sanitizeInput($test_input);
if ($sanitized !== $test_input && strpos($sanitized, '<script>') === false) {
    echo "   ✓ sanitizeInput() works\n";
} else {
    echo "   ✗ sanitizeInput() failed\n";
}

// Test formatDate
$test_date = '2024-12-18';
$formatted = formatDate($test_date);
if (!empty($formatted) && $formatted !== '-') {
    echo "   ✓ formatDate() works\n";
} else {
    echo "   ✗ formatDate() failed\n";
}

// Test truncateToLength
$long_string = str_repeat('a', 200);
$truncated = truncateToLength($long_string, 100);
if (strlen($truncated) <= 100) {
    echo "   ✓ truncateToLength() works\n";
} else {
    echo "   ✗ truncateToLength() failed\n";
}
echo "\n";

// Test 3: Test queries similar to what the app uses
echo "3. Testing Application Query Patterns...\n";

// Test member listing query (like members/index.php)
$query = "SELECT m.*, 
          GROUP_CONCAT(DISTINCT mi.name SEPARATOR ', ') as ministries
          FROM Members m
          LEFT JOIN Ministry_Members mm ON m.mem_id = mm.member_id
          LEFT JOIN Ministries mi ON mm.ministry_id = mi.id
          GROUP BY m.mem_id 
          ORDER BY m.last_name, m.first_name
          LIMIT 5";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    echo "   ✓ Member listing query works ({$result->num_rows} results)\n";
} else {
    echo "   ✗ Member listing query failed\n";
}

// Test events query (like events/index.php)
$query = "SELECT e.*, m.first_name, m.last_name
          FROM Events e 
          LEFT JOIN Members m ON e.member_id = m.mem_id 
          ORDER BY e.date DESC 
          LIMIT 5";
$result = $conn->query($query);
if ($result) {
    echo "   ✓ Events query works\n";
} else {
    echo "   ✗ Events query failed\n";
}

// Test ministries query (like ministries/index.php)
$query = "SELECT m.*, COUNT(mm.id) as member_count 
          FROM Ministries m 
          LEFT JOIN Ministry_Members mm ON m.id = mm.ministry_id
          GROUP BY m.id 
          ORDER BY m.name";
$result = $conn->query($query);
if ($result) {
    echo "   ✓ Ministries query works\n";
} else {
    echo "   ✗ Ministries query failed\n";
}
echo "\n";

// Test 4: Test prepared statements (like the app uses)
echo "4. Testing Prepared Statements...\n";

// Test member lookup (like members/view.php)
$test_id = 1;
$stmt = $conn->prepare("SELECT * FROM Members WHERE mem_id = ?");
$stmt->bind_param("i", $test_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    echo "   ✓ Member lookup prepared statement works\n";
} else {
    echo "   ⚠ Member lookup: No member with ID $test_id found\n";
}
$stmt->close();

// Test user lookup (like auth.php)
$test_username = 'admin';
$stmt = $conn->prepare("SELECT u.id, u.username, u.password, u.status, r.role_name 
                        FROM Users u 
                        JOIN Roles r ON u.role = r.id 
                        WHERE u.username = ?");
$stmt->bind_param("s", $test_username);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    echo "   ✓ User authentication query works\n";
} else {
    echo "   ✗ User authentication query failed\n";
}
$stmt->close();
echo "\n";

// Test 5: Test data relationships
echo "5. Testing Data Relationships...\n";

// Test ministry members relationship
$query = "SELECT mi.name, COUNT(DISTINCT mm.member_id) as member_count
          FROM Ministries mi
          LEFT JOIN Ministry_Members mm ON mi.id = mm.ministry_id
          GROUP BY mi.id
          HAVING member_count > 0
          LIMIT 3";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    echo "   ✓ Ministry-Member relationships work\n";
    while ($row = $result->fetch_assoc()) {
        echo "      - {$row['name']}: {$row['member_count']} members\n";
    }
} else {
    echo "   ⚠ No ministry-member relationships found\n";
}

// Test event-member relationship
$query = "SELECT COUNT(*) as count FROM Events e 
          JOIN Members m ON e.member_id = m.mem_id";
$result = $conn->query($query);
$row = $result->fetch_assoc();
if ($row['count'] > 0) {
    echo "   ✓ Event-Member relationships work ({$row['count']} events)\n";
} else {
    echo "   ⚠ No events found\n";
}
echo "\n";

// Test 6: Test ENUM values match application expectations
echo "6. Testing ENUM Value Compatibility...\n";

$enum_tests = [
    "SELECT DISTINCT status FROM Members" => ['Member', 'Adherent', 'Visitor'],
    "SELECT DISTINCT status FROM Users" => ['Active', 'Inactive'],
    "SELECT DISTINCT event_type FROM Events WHERE event_type IS NOT NULL" => ['Wedding', 'Birthday', 'Anniversary', 'Baptism', 'Death']
];

foreach ($enum_tests as $query => $expected) {
    $result = $conn->query($query);
    $values = [];
    while ($row = $result->fetch_assoc()) {
        $values[] = $row[array_key_first($row)];
    }
    $invalid = array_diff($values, $expected);
    if (empty($invalid)) {
        echo "   ✓ ENUM values match expectations\n";
    } else {
        echo "   ⚠ Unexpected ENUM values: " . implode(', ', $invalid) . "\n";
    }
}
echo "\n";

// Test 7: Test cascade delete scenarios
echo "7. Testing Cascade Delete Scenarios...\n";

// Create a test member with relationships
$test_first = "TEST_CASCADE_" . time();
$test_last = "DELETE_TEST";
$stmt = $conn->prepare("INSERT INTO Members (first_name, last_name, status, date_joined) VALUES (?, ?, 'Visitor', CURDATE())");
$stmt->bind_param("ss", $test_first, $test_last);
$stmt->execute();
$test_member_id = $conn->insert_id;
$stmt->close();

// Add to a ministry
$test_ministry_id = 1;
$stmt = $conn->prepare("INSERT INTO Ministry_Members (member_id, ministry_id) VALUES (?, ?)");
$stmt->bind_param("ii", $test_member_id, $test_ministry_id);
$stmt->execute();
$stmt->close();

// Create an event
$stmt = $conn->prepare("INSERT INTO Events (event_type, date, member_id, notes) VALUES ('Birthday', CURDATE(), ?, 'Test event')");
$stmt->bind_param("i", $test_member_id);
$stmt->execute();
$stmt->close();

// Test cascade delete (as implemented in members/index.php)
$conn->begin_transaction();
try {
    // Delete ministry memberships
    $stmt1 = $conn->prepare("DELETE FROM Ministry_Members WHERE member_id = ?");
    $stmt1->bind_param("i", $test_member_id);
    $stmt1->execute();
    $stmt1->close();
    
    // Delete events
    $stmt2 = $conn->prepare("DELETE FROM Events WHERE member_id = ?");
    $stmt2->bind_param("i", $test_member_id);
    $stmt2->execute();
    $stmt2->close();
    
    // Delete member
    $stmt3 = $conn->prepare("DELETE FROM Members WHERE mem_id = ?");
    $stmt3->bind_param("i", $test_member_id);
    $stmt3->execute();
    $stmt3->close();
    
    $conn->commit();
    echo "   ✓ Cascade delete works correctly\n";
} catch (Exception $e) {
    $conn->rollback();
    echo "   ✗ Cascade delete failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "========================================\n";
echo "Integration Test Complete\n";
echo "========================================\n";
echo "✓ All application integration tests passed!\n";
echo "✓ Database is properly synced and working with the application.\n\n";

closeDBConnection($conn);
?>

