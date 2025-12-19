<?php
/**
 * PDF Export Handler for Reports
 * Uses TCPDF library for PDF generation
 * 
 * Authors:
 * - Joshane Beecher (2304845)
 * - Abbygayle Higgins (2106327)
 * - Serena Morris (2208659)
 * - Jahzeal Simms (2202446)
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();

// Check if TCPDF is available, if not use simple HTML to PDF approach
if (!class_exists('TCPDF')) {
    // Fallback: Use simple HTML output that can be printed to PDF
    $useSimplePDF = true;
} else {
    $useSimplePDF = false;
}

$reportType = isset($_GET['type']) ? sanitizeInput($_GET['type']) : 'membership';
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$genderFilter = isset($_GET['gender']) ? sanitizeInput($_GET['gender']) : '';

$conn = getDBConnection();

// Generate PDF based on report type
if ($reportType === 'membership') {
    generateMembershipPDF($conn, $statusFilter, $genderFilter);
} elseif ($reportType === 'attendance') {
    generateAttendancePDF($conn, $month, $year);
} elseif ($reportType === 'events') {
    generateEventsPDF($conn);
} elseif ($reportType === 'ministry') {
    generateMinistryPDF($conn);
} else {
    die('Invalid report type');
}

function generateMembershipPDF($conn, $statusFilter, $genderFilter) {
    // Build query
    $query = "SELECT * FROM Members WHERE 1=1";
    if ($statusFilter) {
        $query .= " AND status = '$statusFilter'";
    }
    if ($genderFilter) {
        $query .= " AND gender = '$genderFilter'";
    }
    $query .= " ORDER BY last_name, first_name";
    $result = $conn->query($query);
    $members = $result->fetch_all(MYSQLI_ASSOC);
    
    // Statistics
    $stats = [];
    $result = $conn->query("SELECT status, COUNT(*) as count FROM Members GROUP BY status");
    while ($row = $result->fetch_assoc()) {
        $stats['by_status'][$row['status']] = $row['count'];
    }
    
    $result = $conn->query("SELECT gender, COUNT(*) as count FROM Members WHERE gender IS NOT NULL GROUP BY gender");
    while ($row = $result->fetch_assoc()) {
        $stats['by_gender'][$row['gender']] = $row['count'];
    }
    
    // Age groups
    $result = $conn->query("
        SELECT 
            CASE 
                WHEN dob IS NULL THEN 'Unknown'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 18 THEN 'Under 18'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 35 THEN '18-35'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 36 AND 55 THEN '36-55'
                WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 56 AND 70 THEN '56-70'
                ELSE 'Over 70'
            END as age_group,
            COUNT(*) as count
        FROM Members
        GROUP BY age_group
    ");
    $ageGroups = $result->fetch_all(MYSQLI_ASSOC);
    
    // Generate HTML for PDF
    $html = generateMembershipHTML($members, $stats, $ageGroups, $statusFilter, $genderFilter);
    outputPDF($html, 'Membership_Report_' . date('Y-m-d') . '.pdf');
}

function generateAttendancePDF($conn, $month, $year) {
    // Monthly attendance summary
    $query = "SELECT date, SUM(count) as total_attendance
        FROM Attendance
        WHERE MONTH(date) = $month AND YEAR(date) = $year
        GROUP BY date
        ORDER BY date";
    $result = $conn->query($query);
    $monthlyData = $result->fetch_all(MYSQLI_ASSOC);
    
    // Ministry attendance
    $query = "SELECT mi.name, 
        COALESCE(SUM(a.count), 0) as total_attendance, 
        COUNT(a.id) as meeting_count
        FROM Ministries mi
        LEFT JOIN Attendance a ON mi.id = a.ministry_id 
            AND MONTH(a.date) = $month 
            AND YEAR(a.date) = $year
        GROUP BY mi.id
        HAVING meeting_count > 0
        ORDER BY total_attendance DESC";
    $result = $conn->query($query);
    $ministryAttendance = $result->fetch_all(MYSQLI_ASSOC);
    
    $html = generateAttendanceHTML($monthlyData, $ministryAttendance, $month, $year);
    outputPDF($html, 'Attendance_Report_' . date('F_Y', mktime(0,0,0,$month,1,$year)) . '.pdf');
}

function generateEventsPDF($conn) {
    // Get upcoming birthdays
    $result = $conn->query("
        SELECT first_name, last_name, dob, 
               DATE(CONCAT(
                   CASE 
                       WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                       THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                       ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
                   END, '-', DATE_FORMAT(dob, '%m-%d')
               )) as next_birthday
        FROM Members 
        WHERE dob IS NOT NULL 
        AND DATE(CONCAT(
            CASE 
                WHEN DATE_FORMAT(dob, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
            END, '-', DATE_FORMAT(dob, '%m-%d')
        )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
        ORDER BY next_birthday
        LIMIT 10
    ");
    $birthdays = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get upcoming anniversaries
    $result = $conn->query("
        SELECT first_name, last_name, date_joined,
               DATE(CONCAT(
                   CASE 
                       WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                       THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                       ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
                   END, '-', DATE_FORMAT(date_joined, '%m-%d')
               )) as next_anniversary
        FROM Members 
        WHERE date_joined IS NOT NULL 
        AND DATE(CONCAT(
            CASE 
                WHEN DATE_FORMAT(date_joined, '%m-%d') >= DATE_FORMAT(CURRENT_DATE, '%m-%d') 
                THEN DATE_FORMAT(CURRENT_DATE, '%Y') 
                ELSE DATE_FORMAT(CURRENT_DATE, '%Y') + 1 
            END, '-', DATE_FORMAT(date_joined, '%m-%d')
        )) BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
        ORDER BY next_anniversary
        LIMIT 10
    ");
    $anniversaries = $result->fetch_all(MYSQLI_ASSOC);
    
    // Get upcoming events
    $result = $conn->query("
        SELECT e.*, m.first_name, m.last_name 
        FROM Events e
        JOIN Members m ON e.member_id = m.mem_id
        WHERE e.date >= CURRENT_DATE 
        AND e.date <= DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY)
        ORDER BY e.date
        LIMIT 10
    ");
    $events = $result->fetch_all(MYSQLI_ASSOC);
    
    $html = generateEventsHTML($birthdays, $anniversaries, $events);
    outputPDF($html, 'Events_Report_' . date('Y-m-d') . '.pdf');
}

function generateMinistryPDF($conn) {
    // Get ministry statistics
    $query = "SELECT mi.name, 
        COUNT(DISTINCT mm.member_id) as active_members,
        COUNT(DISTINCT a.id) as total_meetings,
        COALESCE(SUM(a.count), 0) as total_attendance
        FROM Ministries mi
        LEFT JOIN Ministry_Members mm ON mi.id = mm.ministry_id
        LEFT JOIN Attendance a ON mi.id = a.ministry_id
        GROUP BY mi.id
        ORDER BY active_members DESC";
    $result = $conn->query($query);
    $ministries = $result->fetch_all(MYSQLI_ASSOC);
    
    $html = generateMinistryHTML($ministries);
    outputPDF($html, 'Ministry_Report_' . date('Y-m-d') . '.pdf');
}

function generateMembershipHTML($members, $stats, $ageGroups, $statusFilter, $genderFilter) {
    $html = '<html><head><style>
        body { font-family: Times New Roman, serif; font-size: 12pt; }
        h1 { color: #000; font-size: 18pt; }
        h2 { color: #000; font-size: 14pt; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .stats { margin: 20px 0; }
        .stat-item { margin: 5px 0; }
    </style></head><body>';
    
    $html .= '<h1>Membership Report</h1>';
    $html .= '<p>Generated: ' . date('F j, Y') . '</p>';
    
    if ($statusFilter) $html .= '<p>Status Filter: ' . htmlspecialchars($statusFilter) . '</p>';
    if ($genderFilter) $html .= '<p>Gender Filter: ' . htmlspecialchars($genderFilter) . '</p>';
    
    $html .= '<div class="stats"><h2>Statistics</h2>';
    if (isset($stats['by_status'])) {
        foreach ($stats['by_status'] as $status => $count) {
            $html .= '<div class="stat-item"><strong>' . htmlspecialchars($status) . ':</strong> ' . $count . '</div>';
        }
    }
    if (isset($stats['by_gender'])) {
        foreach ($stats['by_gender'] as $gender => $count) {
            $html .= '<div class="stat-item"><strong>' . htmlspecialchars($gender) . ':</strong> ' . $count . '</div>';
        }
    }
    $html .= '</div>';
    
    if (!empty($ageGroups)) {
        $html .= '<h2>Age Group Distribution</h2><table><tr><th>Age Group</th><th>Count</th></tr>';
        foreach ($ageGroups as $group) {
            $html .= '<tr><td>' . htmlspecialchars($group['age_group']) . '</td><td>' . $group['count'] . '</td></tr>';
        }
        $html .= '</table>';
    }
    
    $html .= '<h2>Member List</h2>';
    $html .= '<table><tr><th>Name</th><th>Email</th><th>Status</th><th>Date Joined</th></tr>';
    foreach ($members as $member) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars(($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? '')) . '</td>';
        $html .= '<td>' . htmlspecialchars($member['email'] ?? '-') . '</td>';
        $html .= '<td>' . htmlspecialchars($member['status'] ?? '-') . '</td>';
        $html .= '<td>' . ($member['date_joined'] ? date('M j, Y', strtotime($member['date_joined'])) : '-') . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table></body></html>';
    
    return $html;
}

function generateAttendanceHTML($monthlyData, $ministryAttendance, $month, $year) {
    $html = '<html><head><style>
        body { font-family: Times New Roman, serif; font-size: 12pt; }
        h1 { color: #000; font-size: 18pt; }
        h2 { color: #000; font-size: 14pt; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
    </style></head><body>';
    
    $html .= '<h1>Attendance Report</h1>';
    $html .= '<p>Period: ' . date('F Y', mktime(0,0,0,$month,1,$year)) . '</p>';
    $html .= '<p>Generated: ' . date('F j, Y') . '</p>';
    
    $html .= '<h2>Monthly Attendance Summary</h2>';
    $html .= '<table><tr><th>Date</th><th>Total Attendance</th></tr>';
    foreach ($monthlyData as $data) {
        $html .= '<tr><td>' . date('M j, Y', strtotime($data['date'])) . '</td><td>' . $data['total_attendance'] . '</td></tr>';
    }
    $html .= '</table>';
    
    $html .= '<h2>Ministry Meeting Attendance</h2>';
    $html .= '<table><tr><th>Ministry</th><th>Total Attendance</th><th>Meetings</th></tr>';
    foreach ($ministryAttendance as $att) {
        $html .= '<tr><td>' . htmlspecialchars($att['name']) . '</td><td>' . $att['total_attendance'] . '</td><td>' . $att['meeting_count'] . '</td></tr>';
    }
    $html .= '</table></body></html>';
    
    return $html;
}

function generateEventsHTML($birthdays, $anniversaries, $events) {
    $html = '<html><head><style>
        body { font-family: Times New Roman, serif; font-size: 12pt; }
        h1 { color: #000; font-size: 18pt; }
        h2 { color: #000; font-size: 14pt; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
    </style></head><body>';
    
    $html .= '<h1>Events & Milestones Report</h1>';
    $html .= '<p>Generated: ' . date('F j, Y') . '</p>';
    
    $html .= '<h2>Upcoming Birthdays (Next 7 Days)</h2>';
    $html .= '<table><tr><th>Name</th><th>Date of Birth</th><th>Next Birthday</th></tr>';
    foreach ($birthdays as $bday) {
        $html .= '<tr><td>' . htmlspecialchars($bday['first_name'] . ' ' . $bday['last_name']) . '</td>';
        $html .= '<td>' . ($bday['dob'] ? date('M j, Y', strtotime($bday['dob'])) : '-') . '</td>';
        $html .= '<td>' . date('M j, Y', strtotime($bday['next_birthday'])) . '</td></tr>';
    }
    $html .= '</table>';
    
    $html .= '<h2>Upcoming Anniversaries (Next 7 Days)</h2>';
    $html .= '<table><tr><th>Name</th><th>Date Joined</th><th>Next Anniversary</th></tr>';
    foreach ($anniversaries as $ann) {
        $html .= '<tr><td>' . htmlspecialchars($ann['first_name'] . ' ' . $ann['last_name']) . '</td>';
        $html .= '<td>' . ($ann['date_joined'] ? date('M j, Y', strtotime($ann['date_joined'])) : '-') . '</td>';
        $html .= '<td>' . date('M j, Y', strtotime($ann['next_anniversary'])) . '</td></tr>';
    }
    $html .= '</table>';
    
    $html .= '<h2>Upcoming Events (Next 14 Days)</h2>';
    $html .= '<table><tr><th>Event Type</th><th>Date</th><th>Member</th><th>Notes</th></tr>';
    foreach ($events as $event) {
        $html .= '<tr><td>' . htmlspecialchars(ucfirst($event['event_type'])) . '</td>';
        $html .= '<td>' . date('M j, Y', strtotime($event['date'])) . '</td>';
        $html .= '<td>' . htmlspecialchars($event['first_name'] . ' ' . $event['last_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($event['notes'] ?? '-') . '</td></tr>';
    }
    $html .= '</table></body></html>';
    
    return $html;
}

function generateMinistryHTML($ministries) {
    $html = '<html><head><style>
        body { font-family: Times New Roman, serif; font-size: 12pt; }
        h1 { color: #000; font-size: 18pt; }
        h2 { color: #000; font-size: 14pt; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
    </style></head><body>';
    
    $html .= '<h1>Ministry Report</h1>';
    $html .= '<p>Generated: ' . date('F j, Y') . '</p>';
    
    $html .= '<h2>Ministry Statistics</h2>';
    $html .= '<table><tr><th>Ministry</th><th>Active Members</th><th>Total Meetings</th><th>Total Attendance</th></tr>';
    foreach ($ministries as $min) {
        $html .= '<tr><td>' . htmlspecialchars($min['name']) . '</td>';
        $html .= '<td>' . $min['active_members'] . '</td>';
        $html .= '<td>' . $min['total_meetings'] . '</td>';
        $html .= '<td>' . $min['total_attendance'] . '</td></tr>';
    }
    $html .= '</table></body></html>';
    
    return $html;
}

function outputPDF($html, $filename) {
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Use browser's print to PDF functionality
    // This is a simple approach that works without external libraries
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>PDF Export</title>
        <style>
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
            body { font-family: Times New Roman, serif; }
        </style>
        <script>
            window.onload = function() {
                window.print();
                setTimeout(function() {
                    window.close();
                }, 1000);
            }
        </script>
    </head>
    <body>
        <div class="no-print" style="padding: 20px; text-align: center;">
            <h2>Preparing PDF...</h2>
            <p>Your browser will open the print dialog. Select "Save as PDF" to download.</p>
        </div>
        ' . $html . '
    </body>
    </html>';
    exit;
}

closeDBConnection($conn);
?>

