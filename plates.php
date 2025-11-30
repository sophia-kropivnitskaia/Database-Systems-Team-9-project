<?php

session_start();
include '../dbs.project/config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

// Fetch logged-in user's name
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

// Fetch all available plates with restaurant name
$query = "
    SELECT P.plate_id, P.description, P.cost, P.amt, P.available_from, P.available_until, U.name as restaurant_name
    FROM plates P
    JOIN restaurants R ON P.restaurant_id = R.restaurant_id
    JOIN users U ON R.user_id = U.user_id
    WHERE P.status = 'available' AND P.amt > 0
    ORDER BY P.available_from ASC
";
$result = $conn->query($query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WNK Plates</title>
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
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 18px;
        }
        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
            font-size: 18px;
        }
        .centerTxt {
            margin: 45px;
            text-align: center;
        }
        table {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th {
            background-color: #333;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .no-plates {
            padding: 40px;
            text-align: center;
            color: #666;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="logo">WNK</div>
        <div class="nav-links">
            <a href="homepage.php">Homepage</a>
            <a href="profile.php">Profile</a>
            <a href="restaurant.php">Restaurant</a>
            <a href="Donors.php">Donors</a> <!-- need an update to an actual page -->
            <a href="Customer.php">Customer</a> <!-- need an update to an actual page -->
            <a href="Needy.php">Needy</a> <!-- need an update to an actual page -->
            <a href="plates.php">Plates</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="user-section">
            <span> <?php echo htmlspecialchars($user_name); ?></span>
        </div>
    </div>
    
    <div class="centerTxt">
        <h1>Available Plates</h1>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Restaurant</th>
                        <th>Description</th>
                        <th>Available From</th>
                        <th>Available Until</th>
                        <th>Price ($)</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['restaurant_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo date('M d, Y g:i A', strtotime($row['available_from'])); ?></td>
                            <td><?php echo date('M d, Y g:i A', strtotime($row['available_until'])); ?></td>
                            <td>$<?php echo number_format($row['cost'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['amt']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-plates">
                <p>No plates available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
    
</body>
</html>