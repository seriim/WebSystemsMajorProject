<?php 
// Include the database connection
include("db.php"); 
?>

<h2>Monthly Attendance Summary</h2>
<form method="GET">
    <!-- Form for selecting month and year -->
    <label>Month:</label>
    <input type="number" name="month" min="1" max="12" required>
    <label>Year:</label>
    <input type="number" name="year" required>
    <button type="submit">Generate</button>
</form>

<?php
// Check if month and year were submitted
if(isset($_GET["month"]) && isset($_GET["year"])) {
    $month = $_GET["month"];
    $year = $_GET["year"];

    // This query gets total attendance per ministry for the given month and year
    $sql = "SELECT m.name AS ministry, SUM(a.count) AS total_attendance
            FROM Attendance a
            JOIN Ministries m ON a.ministry_id = m.id
            WHERE MONTH(a.date) = $month AND YEAR(a.date) = $year
            GROUP BY m.name";
    $result = $conn->query($sql);

    // Arrays to hold labels (ministries) and data (attendance counts)
    $labels = [];
    $data = [];

    // Build an HTML table to show the raw numbers
    echo "<h3>Attendance for $month/$year (Table)</h3>";
    echo "<table border='1'><tr><th>Ministry</th><th>Total Attendance</th></tr>";

    while($row = $result->fetch_assoc()) {
        $labels[] = $row["ministry"];          // Add ministry name to labels
        $data[] = $row["total_attendance"];    // Add attendance count to data

        // Print each row of the table
        echo "<tr><td>".$row["ministry"]."</td><td>".$row["total_attendance"]."</td></tr>";
    }
    echo "</table>";

    // Convert arrays into JSON for Chart.js
    $labels_json = json_encode($labels);
    $data_json = json_encode($data);
}
?>

<!-- Where Chart.js will draw the bar chart -->
<canvas id="attendanceChart" width="600" height="300"></canvas>

<!-- Load Chart.js library from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
<?php if(isset($labels_json) && isset($data_json)) { ?>
    // Grab the canvas context so Chart.js knows where to draw
    const ctx = document.getElementById('attendanceChart').getContext('2d');

    // Creating a bar chart using ministries as labels and attendance counts as data
    const attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo $labels_json; ?>, // ministries on x-axis
            datasets: [{
                label: 'Attendance',
                data: <?php echo $data_json; ?>, // counts on y-axis
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { title: { display: true, text: 'Ministry' } }, // label for x-axis
                y: { beginAtZero: true, title: { display: true, text: 'Total Attendance' } } // label for y-axis
            }
        }
    });
<?php } ?>
</script>