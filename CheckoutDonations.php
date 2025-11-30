<?php
session_start();

include("config.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$name =$_SESSION['name'];

$user_type = $_SESSION['user_type'];
$matches = false;
$error ='';

// If user clicks VALIDATE
if(isset($_POST['validate'])){
    $inputCard = trim($_POST['credit_card']);

    $table ="donors";

    $q = "SELECT credit_card FROM $table WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $q);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $dbResult = mysqli_stmt_get_result($stmt);
    $cardRow = mysqli_fetch_assoc($dbResult);

    if($cardRow){
        $storedCard = trim($cardRow['credit_card']);

        if($storedCard === $inputCard){
            $matches = true;
        } else {
            $matches = false;
            $error = "Invalid credit card — please try again";
        }
    } else {
        $matches = false;
        $error = "You do not have a credit card associated with your account.";
    }
}

$donation = "
              SELECT p.description, p.cost, d.amt, d.donated_at, r.restaurant_name
              FROM donations d
              JOIN plates p ON d.plate_id = p.plate_id
              JOIN restaurants r ON p.restaurant_id = r.restaurant_id
              WHERE d.user_id = ?
              ORDER BY d.donated_at DESC";

$stmt = mysqli_prepare($conn, $donation);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WNK</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="donor">
    <div class="navigation">
        

        <a href="Doner.php">
            <img src="images/Frame3.png" alt="logo" class="wnk_logo">
        </a>

        <nav class="nav_link">

            <ul class="cust_options">
                <li>
                    <a href="Doner.php#plateSection" class="plates">Browse Plates</a>
                </li>
                <li>
                    <a href="Donations.php" class="reserve">My Donations</a>
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
                    <a href="CheckoutDonations.php" class="checkout">Checkout</a>
                </li>
                <li>
                    <a href="logout.php" class="sign_off">Sign Out</a>
                </li>
            </ul>

        </div>
    </div>

    <div id="checkout">

        <h1 class="checkoutTitle">My Donations Checkout</h1>

        <?php if(!$matches): ?>
            <form action="" method="POST">
                <label>Your Credit Card</label>
                <input type="password" name="credit_card" maxlength="19" required>
                <button type="submit" name="validate">Validate Card</button>
            </form>
        <?php endif; ?>

    <!-- valid -->
        <?php if($matches):  ?>
            <p style="color:green;">Credit card verified!</p>

            <form action="Confirm.php" method="POST">
                <button type="submit" class="confirm_btn">
                    Confirm Order
                </button>
            </form>
        <?php endif; ?>

        <?php if(mysqli_num_rows($result) > 0): ?>

            <?php
            $totalPrice = 0; 
            ?>

            <table class="checkoutTable">
                <tr>
                    <th>Plate</th>
                    <th>Restaurant</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Donated At</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $lineTot = $row['cost'] * $row['amt'];
                    $totalPrice += $lineTot;
                ?>

                    <tr>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= htmlspecialchars($row['restaurant_name']) ?></td>
                        <td>$<?= number_format($row['cost'], 2) ?></td>
                        <td><?= intval($row['amt']) ?></td>
                        <td><?= date("M j, Y - g:i A", strtotime($row['donated_at'])) ?></td>               
                    </tr>

                <?php endwhile; ?>

                <tr class="totalRow">
                    <td colspan="2"><strong>Total</strong></td>
                    <td colspan="3"><strong>$<?= number_format($totalPrice, 2) ?></strong></td>
                </tr>

            </table>


        <?php else: ?>
            <p class="noReservationsCheckout">You have no reserved plates to checkout.</p>
        <?php endif; ?>

    </div>
    
    <script src="menu.js"></script>

</body>
</html>