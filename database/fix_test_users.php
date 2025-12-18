<?php
/**
 * Fix Test Users Password Hashes
 * This script regenerates password hashes for all test users
 */

require_once __DIR__ . '/../config/database.php';

$conn = getDBConnection();

// Generate a fresh password hash for 'test123'
$password_hash = password_hash('test123', PASSWORD_DEFAULT);

echo "========================================\n";
echo "Fixing Test Users Password Hashes\n";
echo "========================================\n\n";
echo "New password hash: $password_hash\n\n";

// Update all test users with the new password hash
$stmt = $conn->prepare("UPDATE Users SET password = ? WHERE username LIKE 'test_%'");
$stmt->bind_param("s", $password_hash);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo "✓ Updated $affected test users\n\n";
    
    // Verify the update
    $result = $conn->query("SELECT username FROM Users WHERE username LIKE 'test_%'");
    echo "Test users updated:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  - {$row['username']}\n";
    }
    
    echo "\n✓ All test users now have password: test123\n";
    echo "✓ You can now login with any test user\n";
} else {
    echo "✗ Error updating users: " . $conn->error . "\n";
}

$stmt->close();
closeDBConnection($conn);
?>

