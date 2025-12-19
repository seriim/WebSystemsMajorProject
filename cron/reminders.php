<?php
/**
 * 
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */

require_once __DIR__ . '/../config/config.php';

// Only allow execution from command line or with secret key for web access
$allowed = false;
if (php_sapi_name() === 'cli') {
    $allowed = true;
} elseif (isset($_GET['key']) && $_GET['key'] === 'your_secret_key_here') {
    $allowed = true;
}

if (!$allowed) {
    die("Access denied. This script must be run from command line or with valid key.");
}

$conn = getDBConnection();
$reminders = [];

// Get upcoming birthdays (next 7 days)
$query = "
    SELECT first_name, last_name, email, dob,
           DATE(CONCAT(
               CASE 
                   WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                   THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                   ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
               END, '-', DATE_FORMAT(dob, '%m-%d')
           )) as next_birthday
    FROM Members 
    WHERE dob IS NOT NULL 
    AND email IS NOT NULL
    AND email != ''
    AND DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(dob, '%m-%d')
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY next_birthday
";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            'type' => 'birthday',
            'member' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'date' => $row['next_birthday'],
            'message' => "Upcoming Birthday: {$row['first_name']} {$row['last_name']} on " . date('F j', strtotime($row['next_birthday']))
        ];
    }
}

// Get upcoming anniversaries (next 7 days) - using date_joined as anniversary
$query = "
    SELECT first_name, last_name, email, date_joined,
           DATE(CONCAT(
               CASE 
                   WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                   THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                   ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
               END, '-', DATE_FORMAT(date_joined, '%m-%d')
           )) as next_anniversary,
           TIMESTAMPDIFF(YEAR, date_joined, CURRENT_DATE) + 
           CASE 
               WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
               THEN 0 
               ELSE 1 
           END as years
    FROM Members 
    WHERE date_joined IS NOT NULL 
    AND email IS NOT NULL
    AND email != ''
    AND DATE(CONCAT(
        CASE 
            WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
            THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
            ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
        END, '-', DATE_FORMAT(date_joined, '%m-%d')
    )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
    ORDER BY next_anniversary
";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            'type' => 'anniversary',
            'member' => $row['first_name'] . ' ' . $row['last_name'],
            'email' => $row['email'],
            'date' => $row['next_anniversary'],
            'years' => $row['years'],
            'message' => "Upcoming Anniversary: {$row['first_name']} {$row['last_name']} - {$row['years']} years on " . date('F j', strtotime($row['next_anniversary']))
        ];
    }
}

// Get upcoming ministry meetings (next 3 days)
$query = "
    SELECT m.name as ministry_name, a.date, a.count as expected_attendance,
           GROUP_CONCAT(DISTINCT CONCAT(mem.first_name, ' ', mem.last_name) SEPARATOR ', ') as leaders
    FROM Attendance a
    JOIN Ministries m ON a.ministry_id = m.id
    LEFT JOIN Ministry_Members mm ON m.id = mm.ministry_id AND mm.role LIKE '%Leader%'
    LEFT JOIN Members mem ON mm.member_id = mem.mem_id
    WHERE a.date BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY)
    GROUP BY a.id, m.name, a.date, a.count
    ORDER BY a.date
";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reminders[] = [
            'type' => 'ministry_meeting',
            'ministry' => $row['ministry_name'],
            'date' => $row['date'],
            'expected_attendance' => $row['expected_attendance'],
            'leaders' => $row['leaders'],
            'message' => "Upcoming Ministry Meeting: {$row['ministry_name']} on " . date('F j, Y', strtotime($row['date']))
        ];
    }
}

closeDBConnection($conn);

// Log reminders (in production, this would send emails) didnt get a chance to test it out or work on it more 
$logFile = __DIR__ . '/reminder_log.txt';
$logContent = "=== Reminder System Run: " . date('Y-m-d H:i:s') . " ===\n\n";

if (empty($reminders)) {
    $logContent .= "No reminders for the next 7 days.\n";
} else {
    $logContent .= "Found " . count($reminders) . " reminder(s):\n\n";
    
    foreach ($reminders as $reminder) {
        $logContent .= "Type: " . ucfirst($reminder['type']) . "\n";
        $logContent .= "Message: " . $reminder['message'] . "\n";
        
        if (isset($reminder['email'])) {
            $logContent .= "Email: " . $reminder['email'] . "\n";
        }
        
        if (isset($reminder['date'])) {
            $logContent .= "Date: " . date('F j, Y', strtotime($reminder['date'])) . "\n";
        }
        
        $logContent .= "\n";
        
        // In production, send email here
        // Example: mail($reminder['email'], 'Upcoming Event Reminder', $reminder['message']);
    }
}

$logContent .= "=== End of Reminder Run ===\n\n";

// Append to log file
file_put_contents($logFile, $logContent, FILE_APPEND);

// Output for command line
if (php_sapi_name() === 'cli') {
    echo $logContent;
} else {
    echo json_encode([
        'status' => 'success',
        'reminders_found' => count($reminders),
        'reminders' => $reminders
    ], JSON_PRETTY_PRINT);
}

?>

