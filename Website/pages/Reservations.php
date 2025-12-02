<?php 
session_start();
//include("config.php");

include '../dbs.project/config.php';


if(!isset($_SESSION['email'])){
        header("Location: login.php");
        exit();
}

$email = $_SESSION['email'];
$user_id = $_SESSION['user_id'];
$name =$_SESSION['name'];

$resQuery = "SELECT p.description, p.cost, r.reserved_at, res.restaurant_name
             FROM reservations r
             JOIN plates p ON r.plate_id = p.plate_id 
             JOIN restaurants res ON p.restaurant_id = res.restaurant_id
             WHERE r.user_id = ?
             ORDER BY r.reserved_at DESC";

$stmt = mysqli_prepare($conn, $resQuery);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>WNK</title>
</head>
<body>
    <div class="navigation">
        

        <a href="Customer.php">
            <img src="images/Frame3.png" alt="logo" class="wnk_logo">
        </a>

        <nav class="nav_link">

            <ul class="cust_options">
                <li>
                    <a href="Customer.php#plateSection" class="plates">Browse Plates</a>
                </li>
                <li>
                    <a href="Reservations.php" class="reserve">My Reservations</a>
                </li>
            </ul>

        </nav>

        <div class="rightside">

            <img src="images/pfp.jpg" alt="profile" class="pfp">
            
            <p>
                <?php echo htmlspecialchars($name); ?>
            </p>

            <img src="images/menu_24dp_000000_FILL0_wght400_GRAD0_opsz24.svg" id="menuTogg" alt="menus_drop">

            <ul class="sidebar">
                <li>
                    <a href="Checkout.php" class="checkout">Checkout</a>
                </li>
                <li>
                    <a href="logout.php" class="sign_off">Sign Out</a>
                </li>
            </ul>

        </div>
    </div>

        <h1 id="reservation">My Reservations</h1>

        <div class="platesGrid">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>

                <?php
                $formatDate = date("M j, Y g:i A", strtotime($row['reserved_at']));
                ?>

                <div class="plateCard">

                    <h3><?= htmlspecialchars($row['description']) ?></h3>
                    <p>Price: $<?= number_format($row['cost'], 2) ?></p>
                    <p class="restaurantName">Restaurant: <?= htmlspecialchars($row['restaurant_name']) ?></p>
                    <p>Reserved on: <?= $formatDate ?></p>

                </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p class="NoRes">You have not reserved any plates yet.</p>
        <?php endif; ?>
        </div>
    </div>

    <script src="menu.js"></script>
</body>
</html>