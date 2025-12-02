<?php
//include('../utils/auth.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//include('./utils/config.php');
include '../dbs.project/config.php';
$res_id = isset($_GET['restaurant_id']) ? intval($_GET['restaurant_id']):0;
$year = isset($_GET['year']) ? intval($_GET['year']):date('Y');
$report = null;
if ($res_id>0)
{
    $stmt = $conn->prepare("
        SELECT
            r.restaurant_name,
            YEAR(o.order_date) AS year,
            COUNT(DISTINCT o.order_id) AS total_orders,
            SUM(oi.amt) AS total_plates_sold,
            SUM(oi.amt * p.cost) AS total_revenue
        FROM restaurants r
        JOIN plates p ON r.restaurant_id = p.restaurant_id
        JOIN order_items oi ON p.plate_id=oi.plate_id
        JOIN orders o ON oi.order_id = o.order_id
        WHERE YEAR(o.order_date)=? AND r.restaurant_id = ?
        GROUP BY r.restaurant_id, YEAR(o.order_date)
    ");
    $stmt->bind_param("ii", $res_id, $year);
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Activity Report for Restaurant</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #e3d09d;
        }
        h1{color: #4a3327;}
        input[type="text"] {
            padding: 5px;
            width: 350px;
        }
        input[type="submit"]{
            padding: 10px 20px;
            background-color: green;
            color: white;
        }
        table{
            border-collapse: collapse;
            width: 100%;
        }
        th, td{
            border: 1px solid #111111;
            padding: 8px;
            text-align: left;
        }
        .no-results{
            color: #7d0101;
            margin: 30px;
        }
    </style>
</head>

<body>
    <h1>Restaurant Annual Activity Report</h1>
    <form method="GET" action="">
        <input type="number" name="restaurant_id" placeholder="Enter the restaurant's id" required> <br>
        <input type="number" name="year" placeholder="Enter the year you wish to look at" value="<?php echo date('Y')?>" required> <br>
        <input type="submit" value = "Generate Report">
    </form>
    
    <?php if ($report):?>
        <h3>Report for <?php echo htmlspecialchars($report['restaurant_name'])?> for the year <?php echo htmlspecialchars($report['year'])?></h3>
        <table>
            <tr><th>Total Orders</th><td><?php echo $report['total_orders']?></td></tr>
            <tr><th>Total Plates Sold</th><td><?php echo $report['total_plates_sold']?></td></tr>
            <tr><th>Total Revenue Generated</th><td>$<?php echo $report['total_revenue']?></td></tr>
        </table>
    <?php else:?>
        <p class = "no_results"> No data found.</p>
    <?php endif;?>
</body>
</html>