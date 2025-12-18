<?php 
include("dbconnect.php"); 
?>

<h2>Search Reports</h2>
<form method="GET">
    <!-- Form for searching by last name, email domain, role, or event keyword -->
    <label>Last Name:</label>
    <input type="text" name="lname">
    <label>Email Domain:</label>
    <input type="text" name="domain" placeholder="%@gmail.com">
    <label>Role Keyword:</label>
    <input type="text" name="role" placeholder="%Leader%">
    <label>Event Keyword:</label>
    <input type="text" name="event">
    <button type="submit">Search</button>
</form>

<?php
// Search by last name
if(isset($_GET["lname"])) {
    $lname = $_GET["lname"];

    // This query finds members whose last name starts with the given value
    $sql = "SELECT first_name, last_name, email 
            FROM Members 
            WHERE last_name LIKE '$lname%'";
    $result = $conn->query($sql);

    // Display results in a table
    echo "<h3>Members with Last Name like $lname</h3>";
    echo "<table border='1'><tr><th>First Name</th><th>Last Name</th><th>Email</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["first_name"]."</td><td>".$row["last_name"]."</td><td>".$row["email"]."</td></tr>";
    }
    echo "</table>";
}

// Search by email domain
if(isset($_GET["domain"])) {
    $domain = $_GET["domain"];

    // This query finds members whose email matches the given domain
    $sql = "SELECT first_name, last_name, email 
            FROM Members 
            WHERE email LIKE '$domain'";
    $result = $conn->query($sql);

   
    echo "<h3>Members with Email Domain $domain</h3>";
    echo "<table border='1'><tr><th>First Name</th><th>Last Name</th><th>Email</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["first_name"]."</td><td>".$row["last_name"]."</td><td>".$row["email"]."</td></tr>";
    }
    echo "</table>";
}

// Search by role keyword
if(isset($_GET["role"])) {
    $role = $_GET["role"];

    // This query finds ministry members whose role matches the keyword
    $sql = "SELECT m.name AS ministry, mem.first_name, mem.last_name, mm.role
            FROM Ministries m
            JOIN Ministry_Members mm ON m.id = mm.ministry_id
            JOIN Members mem ON mm.member_id = mem.mem_id
            WHERE mm.role LIKE '$role'";
    $result = $conn->query($sql);

    
    echo "<h3>Ministry Roles like $role</h3>";
    echo "<table border='1'><tr><th>Ministry</th><th>Member</th><th>Role</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["ministry"]."</td><td>".$row["first_name"]." ".$row["last_name"]."</td><td>".$row["role"]."</td></tr>";
    }
    echo "</table>";
}

// Search by event keyword
if(isset($_GET["event"])) {
    $event = $_GET["event"];

    // This query finds events whose notes contain the keyword
    $sql = "SELECT e.event_type, e.date, mem.first_name, mem.last_name, e.notes
            FROM Events e
            JOIN Members mem ON e.member_id = mem.mem_id
            WHERE e.notes LIKE '%$event%'";
    $result = $conn->query($sql);

    /
    echo "<h3>Events with Notes containing $event</h3>";
    echo "<table border='1'><tr><th>Event Type</th><th>Date</th><th>Member</th><th>Notes</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["event_type"]."</td><td>".$row["date"]."</td><td>".$row["first_name"]." ".$row["last_name"]."</td><td>".$row["notes"]."</td></tr>";
    }
    echo "</table>";
}
?>