<?php
/**
 * Database Test Script
 * Tests database structure, data integrity, and functionality
 */

require_once __DIR__ . '/../config/database.php';

echo "========================================\n";
echo "Database Test Suite\n";
echo "========================================\n\n";

$conn = getDBConnection();
$errors = [];
$warnings = [];
$success = [];

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
if ($conn) {
    $success[] = "✓ Database connection successful";
    echo "   ✓ Connected to: " . DB_NAME . "\n\n";
} else {
    $errors[] = "✗ Database connection failed";
    die("Connection failed!\n");
}

// Test 2: Check all required tables exist
echo "2. Checking Required Tables...\n";
$required_tables = [
    'Roles',
    'Users',
    'Members',
    'Ministries',
    'Ministry_Members',
    'Events',
    'Attendance'
];

$existing_tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $existing_tables[] = $row[0];
}

foreach ($required_tables as $table) {
    if (in_array($table, $existing_tables)) {
        $success[] = "✓ Table '$table' exists";
        echo "   ✓ $table\n";
    } else {
        $errors[] = "✗ Table '$table' is missing";
        echo "   ✗ $table (MISSING)\n";
    }
}
echo "\n";

// Test 3: Check table structures
echo "3. Verifying Table Structures...\n";
$table_checks = [
    'Roles' => ['id', 'role_name', 'description'],
    'Users' => ['id', 'username', 'password', 'role', 'status'],
    'Members' => ['mem_id', 'first_name', 'last_name', 'email', 'status'],
    'Ministries' => ['id', 'name', 'description'],
    'Ministry_Members' => ['id', 'member_id', 'ministry_id'],
    'Events' => ['id', 'event_type', 'date', 'member_id', 'notes'],
    'Attendance' => ['id', 'date', 'ministry_id', 'count', 'recorded_by']
];

foreach ($table_checks as $table => $columns) {
    if (in_array($table, $existing_tables)) {
        $result = $conn->query("DESCRIBE $table");
        $table_columns = [];
        while ($row = $result->fetch_assoc()) {
            $table_columns[] = $row['Field'];
        }
        
        $missing = array_diff($columns, $table_columns);
        if (empty($missing)) {
            $success[] = "✓ Table '$table' has all required columns";
            echo "   ✓ $table structure OK\n";
        } else {
            $errors[] = "✗ Table '$table' missing columns: " . implode(', ', $missing);
            echo "   ✗ $table missing: " . implode(', ', $missing) . "\n";
        }
    }
}
echo "\n";

// Test 4: Check foreign key constraints
echo "4. Testing Foreign Key Constraints...\n";
$fk_checks = [
    "SELECT COUNT(*) as count FROM Users u LEFT JOIN Roles r ON u.role = r.id WHERE r.id IS NULL" => "Users with invalid role references",
    "SELECT COUNT(*) as count FROM Ministry_Members mm LEFT JOIN Members m ON mm.member_id = m.mem_id WHERE m.mem_id IS NULL" => "Ministry_Members with invalid member references",
    "SELECT COUNT(*) as count FROM Ministry_Members mm LEFT JOIN Ministries mi ON mm.ministry_id = mi.id WHERE mi.id IS NULL" => "Ministry_Members with invalid ministry references",
    "SELECT COUNT(*) as count FROM Events e LEFT JOIN Members m ON e.member_id = m.mem_id WHERE m.mem_id IS NULL" => "Events with invalid member references",
    "SELECT COUNT(*) as count FROM Attendance a LEFT JOIN Ministries m ON a.ministry_id = m.id WHERE m.id IS NULL" => "Attendance with invalid ministry references",
    "SELECT COUNT(*) as count FROM Attendance a LEFT JOIN Users u ON a.recorded_by = u.id WHERE a.recorded_by IS NOT NULL AND u.id IS NULL" => "Attendance with invalid user references"
];

foreach ($fk_checks as $query => $description) {
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $errors[] = "✗ $description: {$row['count']} invalid references";
        echo "   ✗ $description: {$row['count']} invalid\n";
    } else {
        $success[] = "✓ $description: OK";
        echo "   ✓ $description: OK\n";
    }
}
echo "\n";

// Test 5: Check default data
echo "5. Checking Default Data...\n";
$default_checks = [
    "SELECT COUNT(*) as count FROM Roles" => "Roles",
    "SELECT COUNT(*) as count FROM Users WHERE username = 'admin'" => "Admin user"
];

foreach ($default_checks as $query => $description) {
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $success[] = "✓ $description exists";
        echo "   ✓ $description: {$row['count']} found\n";
    } else {
        $warnings[] = "⚠ $description is missing";
        echo "   ⚠ $description: NOT FOUND\n";
    }
}
echo "\n";

// Test 6: Test basic CRUD operations
echo "6. Testing Basic Operations...\n";

// Test INSERT
try {
    $test_name = "TEST_" . time();
    $stmt = $conn->prepare("INSERT INTO Members (first_name, last_name, status, date_joined) VALUES (?, ?, 'Visitor', CURDATE())");
    $stmt->bind_param("ss", $test_name, $test_name);
    if ($stmt->execute()) {
        $test_member_id = $conn->insert_id;
        $success[] = "✓ INSERT operation works";
        echo "   ✓ INSERT: Success (ID: $test_member_id)\n";
        
        // Test SELECT
        $stmt2 = $conn->prepare("SELECT * FROM Members WHERE mem_id = ?");
        $stmt2->bind_param("i", $test_member_id);
        $stmt2->execute();
        $result = $stmt2->get_result();
        if ($result->num_rows > 0) {
            $success[] = "✓ SELECT operation works";
            echo "   ✓ SELECT: Success\n";
        } else {
            $errors[] = "✗ SELECT operation failed";
            echo "   ✗ SELECT: Failed\n";
        }
        $stmt2->close();
        
        // Test UPDATE
        $new_name = $test_name . "_UPDATED";
        $stmt3 = $conn->prepare("UPDATE Members SET first_name = ? WHERE mem_id = ?");
        $stmt3->bind_param("si", $new_name, $test_member_id);
        if ($stmt3->execute()) {
            $success[] = "✓ UPDATE operation works";
            echo "   ✓ UPDATE: Success\n";
        } else {
            $errors[] = "✗ UPDATE operation failed";
            echo "   ✗ UPDATE: Failed\n";
        }
        $stmt3->close();
        
        // Test DELETE
        $stmt4 = $conn->prepare("DELETE FROM Members WHERE mem_id = ?");
        $stmt4->bind_param("i", $test_member_id);
        if ($stmt4->execute()) {
            $success[] = "✓ DELETE operation works";
            echo "   ✓ DELETE: Success\n";
        } else {
            $errors[] = "✗ DELETE operation failed";
            echo "   ✗ DELETE: Failed\n";
        }
        $stmt4->close();
        
    } else {
        $errors[] = "✗ INSERT operation failed: " . $conn->error;
        echo "   ✗ INSERT: Failed - " . $conn->error . "\n";
    }
    $stmt->close();
} catch (Exception $e) {
    $errors[] = "✗ CRUD test failed: " . $e->getMessage();
    echo "   ✗ CRUD Test Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 7: Check data consistency
echo "7. Checking Data Consistency...\n";

// Check for duplicate emails
$result = $conn->query("SELECT email, COUNT(*) as count FROM Members WHERE email IS NOT NULL AND email != '' GROUP BY email HAVING count > 1");
if ($result->num_rows > 0) {
    $warnings[] = "⚠ Duplicate emails found";
    echo "   ⚠ Found duplicate emails\n";
    while ($row = $result->fetch_assoc()) {
        echo "      - {$row['email']}: {$row['count']} occurrences\n";
    }
} else {
    $success[] = "✓ No duplicate emails";
    echo "   ✓ No duplicate emails\n";
}

// Check for invalid ENUM values
$enum_checks = [
    "SELECT COUNT(*) as count FROM Members WHERE gender NOT IN ('Male', 'Female', 'Other') AND gender IS NOT NULL" => "Invalid gender values",
    "SELECT COUNT(*) as count FROM Members WHERE status NOT IN ('Member', 'Adherent', 'Visitor')" => "Invalid member status values",
    "SELECT COUNT(*) as count FROM Users WHERE status NOT IN ('Active', 'Inactive')" => "Invalid user status values",
    "SELECT COUNT(*) as count FROM Events WHERE event_type NOT IN ('Wedding', 'Birthday', 'Anniversary', 'Baptism', 'Death') AND event_type IS NOT NULL" => "Invalid event types"
];

foreach ($enum_checks as $query => $description) {
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        $errors[] = "✗ $description: {$row['count']} invalid values";
        echo "   ✗ $description: {$row['count']} invalid\n";
    } else {
        $success[] = "✓ $description: OK";
        echo "   ✓ $description: OK\n";
    }
}
echo "\n";

// Test 8: Test transactions
echo "8. Testing Transactions...\n";
try {
    $conn->begin_transaction();
    
    $test_name = "TRANS_TEST_" . time();
    $stmt = $conn->prepare("INSERT INTO Members (first_name, last_name, status, date_joined) VALUES (?, ?, 'Visitor', CURDATE())");
    $stmt->bind_param("ss", $test_name, $test_name);
    $stmt->execute();
    $test_id = $conn->insert_id;
    $stmt->close();
    
    $conn->rollback();
    
    // Verify rollback worked
    $stmt2 = $conn->prepare("SELECT * FROM Members WHERE mem_id = ?");
    $stmt2->bind_param("i", $test_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    
    if ($result->num_rows == 0) {
        $success[] = "✓ Transactions work (rollback successful)";
        echo "   ✓ Transactions: OK\n";
    } else {
        $errors[] = "✗ Transaction rollback failed";
        echo "   ✗ Transactions: Rollback failed\n";
    }
    $stmt2->close();
} catch (Exception $e) {
    $errors[] = "✗ Transaction test failed: " . $e->getMessage();
    echo "   ✗ Transactions: Error - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 9: Check indexes
echo "9. Checking Indexes...\n";
$index_checks = [
    "SHOW INDEX FROM Users WHERE Key_name = 'username'" => "Users.username unique index",
    "SHOW INDEX FROM Members WHERE Key_name = 'email'" => "Members.email unique index"
];

foreach ($index_checks as $query => $description) {
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $success[] = "✓ $description exists";
        echo "   ✓ $description: OK\n";
    } else {
        $warnings[] = "⚠ $description missing";
        echo "   ⚠ $description: Missing\n";
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
    echo "✓ All critical tests passed! Database is working properly.\n";
    exit(0);
} else {
    echo "✗ Some tests failed. Please review the errors above.\n";
    exit(1);
}

closeDBConnection($conn);
?>

