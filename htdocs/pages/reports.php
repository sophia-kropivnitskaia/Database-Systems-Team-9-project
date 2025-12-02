<?php

session_start();
include '../dbs.project/config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// fetch logged-in users name
$stmt = $conn->prepare("
    SELECT name 
    FROM users 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WNK Homepage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #e3d09d;
        }
        .top-bar {
            background-color: #f5ddb8;
            padding: 55px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 4px solid #d4a574;
        }
        .logo {
            font-weight: bold;
            font-size: 28px;
        }
        .navigation {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .navigation a {
            text-decoration: none;
            color: black;
            font-weight: 500;
            font-size: 18px;
        }
        .userSection {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 18px;
        }
        .centerTxt {
            margin: 45px;
            text-align: center;
        }
        .report-links {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            margin-top: 40px;
        }
        .report-links a {
            display: block;
            width: 300px;
            padding: 15px 30px;
            background-color: black;
            color: white;
            text-decoration: none;           
            font-size: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="logo">WNK</div>
        <div class="navigation">
            <a href="homepage.php">Homepage</a>
            <a href="profile.php">Profile</a>
            <a href="restaurant.php">Restaurant</a>
            <a href="Donors.php">Donors</a> <!-- need an update to an actual page -->
            <a href="Customer.php">Customer</a> <!-- need an update to an actual page -->
            <a href="Needy.php">Needy</a> <!-- need an update to an actual page -->
            <a href="plates.php">Plates</a>
            <a href="reports.php">Reports</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="userSection">
            <span> <?php echo htmlspecialchars($user_name); ?></span>
        </div>
    </div>
    
    <div class="centerTxt">
        <h1>Choose a report you want to generate</h1>
        
        <div class="report-links">
            <a href="donation_report.php">Donation Report</a>
            <a href="needy_report.php">Needy Report</a>
            <a href="purchase_report.php">Purchase Report</a>
            <a href="restaurant_report.php">Restaurant Report</a>
            <a href="members.php">Member Lookup</a>
        </div>
    </div>
    
</body>
</html>
