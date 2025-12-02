<?php 
    //include('../utils/auth.php');
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    //include('./utils/config.php');
    include '../dbs.project/config.php';
    $results =null;
    $report = null;

    if (isset($_GET['user_id']))
    {
        $order_type = isset($_GET['order_type']) ? $_GET['order_type']:'';
        $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']):0;
        $year = isset($_GET['year']) ? intval($_GET['year']):date('Y');
        if ($order_type === 'customer'){
            $sql="
                SELECT
                    u.user_id,
                    c.customer_id,
                    u.name,
                    u.email,
                    YEAR(o.order_date) AS year,
                    COUNT(DISTINCT o.order_id) AS total_orders,
                    SUM(oi.amt) AS total_plates_bought,
                    SUM(oi.amt * p.cost) AS total_paid
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                JOIN customers c ON u.user_id = c.user_id
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN plates p ON oi.plate_id = p.plate_id
                WHERE o.user_id = ? AND o.order_type = 'customer' AND YEAR(o.order_date) = ?
                GROUP BY u.user_id, c.customer_id, YEAR(o.order_date)
            ";
        } else{
            $sql="
                SELECT
                    u.user_id,
                    d.donor_id,
                    u.name,
                    u.email,
                    YEAR(o.order_date) AS year,
                    COUNT(DISTINCT o.order_id) AS total_donations,
                    SUM(oi.amt) AS total_plates_bought,
                    SUM(oi.amt * p.cost) AS total_paid
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                JOIN donors d ON u.user_id = d.user_id
                JOIN order_items oi ON o.order_id = oi.order_id
                JOIN plates p ON oi.plate_id = p.plate_id
                WHERE o.user_id = ? AND o.order_type = 'donation' AND YEAR(o.order_date) = ?
                GROUP BY u.user_id, d.donor_id, YEAR(o.order_date)
            ";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $year);
        $stmt->execute();
        $results = $stmt->get_result();
        $report = $results->fetch_assoc();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Annual Purchase Report for Customers and Donors</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #e3d09d;
        }
        h1{
            color: #4a3327;
        }
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
    <h1>Annual Purchase Report for Customers and Donors</h1>
    <form method="GET" action="">
        <input type="number" name="user_id" placeholder="Enter the user id" required> <br>
        <input type="number" name="year" placeholder="Enter the year you wish to look at" value="<?php echo date('Y')?>" required> <br>
        <label><strong>Input the type of user</strong></label><br>
        <select name = "order_type" required>
            <option value="customer">Customer</option>
            <option value="donor">Donor</option>
        </select><br>
        <input type="submit" value = "Generate Report">
    </form>
    
    <?php if ($report && $results->num_rows>0):?>
        <h3>Report for <?php echo htmlspecialchars($report['name'])?> (<?php echo htmlspecialchars($report['email'])?> ) for the year <?php echo htmlspecialchars($report['year'])?></h3>
        <table>
            <tr><th>User ID</th><td><?=$report['user_id']?></td></tr>
            <?php if ($type==='customer'):?>
                <tr><th>Customer ID</th><td><?php echo $report['customer_id']?></td></tr>
                <tr><th>Total Orders</th><td><?php echo $report['total_orders']?></td></tr>
            <?php else:?>
                <tr><th>Donor ID</th><td><?php echo $report['donor_id']?></td></tr>
                <tr><th>Total Orders</th><td><?php echo $report['total_donations']?></td></tr>
            <?php endif;?>
            <tr><th>Total Plates Purchased</th><td><?php echo $report['total_plates_bought']?></td></tr>
            <tr><th>Total Paid</th><td>$<?php echo $report['total_paid']?></td></tr>
        </table>
    <?php else:?>
        <p class = "no_results"> No data found.</p>
    <?php endif;?>
</body>
</html>