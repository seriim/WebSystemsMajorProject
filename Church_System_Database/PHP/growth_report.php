<?php 

include("db.php"); 
?>

<h2>Membership Growth Report</h2>
<form method="GET">
    <label>Year:</label>
    <input type="number" name="year" required>
    <button type="submit">Generate</button>
</form>

<?php
// Input validation
if(isset($_GET["year"])) {
    $year = $_GET["year"];

    // This query counts how many new members joined each month in the given year
    $sql = "SELECT MONTH(date_joined) AS month, COUNT(*) AS new_members
            FROM Members
            WHERE YEAR(date_joined) = $year
            GROUP BY MONTH(date_joined)
            ORDER BY month";
    $result = $conn->query($sql);

    // Initializing arrays to hold labels (months) and data (counts) for Chart.js
    $labels = [];
    $data = [];

    // Building an HTML table to show the raw numbers with the chart
    echo "<h3>New Members in $year (Table)</h3>";
    echo "<table border='1'><tr><th>Month</th><th>New Members</th></tr>";

    while($row = $result->fetch_assoc()) {
        $labels[] = $row["month"];       // add month number to labels array
        $data[] = $row["new_members"];   // add count to data array

        // print each row of the table
        echo "<tr><td>".$row["month"]."</td><td>".$row["new_members"]."</td></tr>";
    }
    echo "</table>";

    $labels_json = json_encode($labels);
    $data_json = json_encode($data);
}
?>

<!-- Where Chart.js will draw the line chart -->
<canvas id="growthChart" width="600" height="300"></canvas>

<!-- This loads Chart.js library from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
<?php if(isset($labels_json) && isset($data_json)) { ?>
    // This grabs the canvas context so Chart.js knows where to draw
    const ctx = document.getElementById('growthChart').getContext('2d');

    // Creating a line chart using the labels (months) and data (new members)
    const growthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $labels_json; ?>, // months go on the x-axis
            datasets: [{
                label: 'New Members in <?php echo $year; ?>',
                data: <?php echo $data_json; ?>, // counts go on the y-axis
                fill: false,
                borderColor: 'rgba(75, 192, 192, 1)',
                tension: 0.1 // smooth curve
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: { display: true, text: 'Month' } // label for x-axis
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'New Members' } // label for y-axis
                }
            }
        }
    });
<?php } ?>
</script>