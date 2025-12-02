<?php
session_start();

include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$name =$_SESSION['name'];


$reservationQuery = "
              SELECT p.description, p.cost, r.restaurant_name, nr.reserved_at
              FROM needy_reservations nr
              JOIN plates p ON nr.plate_id = p.plate_id
              JOIN restaurants r ON p.restaurant_id = r.restaurant_id
              WHERE nr.user_id = ?
              ORDER BY nr.reserved_at DESC";

$stmt = mysqli_prepare($conn, $reservationQuery);
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
<body class="needy">
    <div class="navigation">
        

        <a href="Needy.php">
            <img src="images/Frame3.png" alt="logo" class="wnk_logo">
        </a>

        <nav class="nav_link">

            <ul class="cust_options">
                <li>
                    <a href="Needy.php#plateSection" class="plates">Donated Plates</a>
                </li>
                <li>
                    <a href="needyCheckout.php" class="reserve">My Selections</a>
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
                    <a href="logout.php" class="sign_off">Sign Out</a>
                </li>
            </ul>

        </div>
    </div>

    <div id="checkout">

        <h1 class="checkoutTitle">My Plates</h1>

        <?php if(mysqli_num_rows($result) > 0): ?>


            <table class="checkoutTable">
                <tr>
                    <th>Plate</th>
                    <th>Restaurant</th>
                    <th>Original Price</th>
                    <th>reserved_at</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($result)): 
                ?>

                    <tr>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['restaurant_name']) ?></td>
                        <td>$<?= number_format($row['cost'], 2) ?></td>
                        <td><?= date("M j, Y - g:i A", strtotime($row['reserved_at'])) ?></td>               
                    </tr>

                <?php endwhile; ?>

            </table>

            <form action="Confirm.php" method = "POST">

                <button type="submit" class="confirm_btn">
                    Confirm Pickup
                </button>

            </form>

        <?php else: ?>
            <p class="noReservationsCheckout">You have no plates to pickup.</p>
        <?php endif; ?>

    </div>
    
    <script src="menu.js"></script>
</body>
</html>