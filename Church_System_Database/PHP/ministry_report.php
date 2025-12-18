<?php 
include("dbconnect.php"); 
?>

<h2>Ministry Participation Report</h2>
<form method="GET">
    <!-- Form for selecting ministry and date range -->
    <label>Ministry ID:</label>
    <input type="number" name="ministry_id" required>
    <label>Start Date:</label>
    <input type="date" name="start_date" required>
    <label>End Date:</label>
    <input type="date" name="end_date" required>
    <button type="submit">Generate</button>
</form>

<?php
// If a ministry ID was submitted, run query for participation in that date range
if(isset($_GET["ministry_id"])) {
    $ministry_id = $_GET["ministry_id"];
    $start_date = $_GET["start_date"];
    $end_date = $_GET["end_date"];

    // This query gets members of the selected ministry and their attendance between the given dates
    $sql = "SELECT m.name AS ministry, mem.first_name, mem.last_name, a.date, a.count
            FROM Ministries m
            JOIN Ministry_Members mm ON m.id = mm.ministry_id
            JOIN Members mem ON mm.member_id = mem.mem_id
            LEFT JOIN Attendance a ON m.id = a.ministry_id
            WHERE m.id = $ministry_id
              AND a.date BETWEEN '$start_date' AND '$end_date'
            ORDER BY a.date";
    $result = $conn->query($sql);

    // Display results in a table
    echo "<h3>Participation Report for Ministry ID $ministry_id</h3>";
    echo "<table border='1'><tr><th>Ministry</th><th>Member</th><th>Date</th><th>Attendance</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["ministry"]."</td><td>".$row["first_name"]." ".$row["last_name"]."</td><td>".$row["date"]."</td><td>".$row["count"]."</td></tr>";
    }
    echo "</table>";
}
?>