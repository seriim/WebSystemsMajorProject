<?php 
include("dbconnect.php"); 
?>

<h2>Birthday List</h2>
<form method="GET">
    <!-- Form for selecting month or week -->
    <label>Month:</label>
    <input type="number" name="month" min="1" max="12">
    <label>Week:</label>
    <input type="number" name="week" min="1" max="52">
    <button type="submit">Generate</button>
</form>

<?php
// If a month was submitted, run query for birthdays in that month
if(isset($_GET["month"])) {
    $month = $_GET["month"];

    // This query gets all members whose birthday falls in the selected month
    $sql = "SELECT first_name, last_name, dob 
            FROM Members 
            WHERE MONTH(dob) = $month";
    $result = $conn->query($sql);

    // Display results for the selected month
    echo "<h3>Birthdays in Month $month</h3>";
    echo "<table border='1'><tr><th>Name</th><th>Date of Birth</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["first_name"]." ".$row["last_name"]."</td><td>".$row["dob"]."</td></tr>";
    }
    echo "</table>";
}

// If a week was submitted, run query for birthdays in that week
if(isset($_GET["week"])) {
    $week = $_GET["week"];

    // This query gets all members whose birthday falls in the selected week of the year
    $sql = "SELECT first_name, last_name, dob 
            FROM Members 
            WHERE WEEK(dob, 1) = $week";
    $result = $conn->query($sql);

    // Display results for the selected week
    echo "<h3>Birthdays in Week $week</h3>";
    echo "<table border='1'><tr><th>Name</th><th>Date of Birth</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["first_name"]." ".$row["last_name"]."</td><td>".$row["dob"]."</td></tr>";
    }
    echo "</table>";
}
?>