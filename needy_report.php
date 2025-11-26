<?php
//include('../utils/auth.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('./utils/config.php');
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']):0;
$year = isset($_GET['year']) ? intval($_GET['year']):date('Y');
$report = null;
if ($user_id>0)
{
    $sql="
        SELECT
            u.user_id,
            n.needy_id,
            u.name,
            u.email,
            YEAR(o.order_date) AS year,
            COUNT(DISTINCT o.order_id) AS total_pickups,
            SUM(oi.amt) AS total_plates_pickedup
        FROM orders o
        JOIN needy n ON o.user_id = n.user_id
        JOIN users u ON n.user_id = u.user_id
        JOIN order_items oi ON o.order_id=oi.order_id
        WHERE o.user_id=? AND o.order_type='pickup' AND YEAR(o.order_date)=?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $year);
    $stmt->execute();
    $report = $stmt->get_result()->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Needy Report</title>
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
    <h1>Annual Needy Report on Pickups</h1>
    <form method="GET" action="">
        <input type="number" name="user_id" placeholder="Enter the user's id" required> <br>
        <input type="number" name="year" placeholder="Enter the year you wish to look at" value="<?php echo date('Y')?>" required> <br>
        <input type="submit" value = "Generate Report">
    </form>
    
    <?php if ($report):?>
        <h3>Report for <?php echo htmlspecialchars($report['name'])?>(<?php echo htmlspecialchars($report['email'])?>) for the year <?php echo htmlspecialchars($report['year'])?></h3>
        <table>
            <tr><th>User ID</th><td><?php echo $report['user_id']?></td></tr>
            <tr><th>Needy ID</th><td><?php echo $report['needy_id']?></td></tr>
            <tr><th>Total Pickups</th><td><?php echo $report['total_pickups']?></td></tr>
            <tr><th>Total Plates Picked Up</th><td><?php echo $report['total_plates_pickedup']?></td></tr>
        </table>
    <?php else:?>
        <p class = "no_results"> No data found.</p>
    <?php endif;?>
</body>
</html>