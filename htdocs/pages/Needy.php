<?php 
session_start();

//include("config.php");

// if login page is set up remove comment
include '../dbs.project/config.php';

    // should redirect if not logged in
    if(!isset($_SESSION['email'])){
        header("Location: login.php");
        exit();
    }

    $email = $_SESSION['email'];

    // Fetch user information from database
    $stmt = $conn->prepare("SELECT user_id, name, user_type FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $name, $user_type);
    
    if(!$stmt->fetch()){
        echo "User not found.";
        exit();
    }
    
    $stmt->close();    

    if($user_type !== 'needy'){
        echo "This page for needy only";
        exit();
    }


    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plate_id'])){
        $plate_id = intval($_POST['plate_id']);

        $countQ ="SELECT COUNT(*) AS total FROM needy_reservations WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $countQ);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $countRes =mysqli_stmt_get_result($stmt);
        $countRow = mysqli_fetch_assoc($countRes);

        if($countRow['total'] >= 2){
            header("Location: Needy.php?limitReached=1");
            exit();
        }


        $availQ = "SELECT oi.order_item_id, oi.amt - COALESCE(nr.reserved_count, 0) AS available_amt
                   FROM order_items oi
                   JOIN orders o ON oi.order_id = o.order_id
                   LEFT JOIN(
                        SELECT plate_id, COUNT(*) AS reserved_count
                        FROM needy_reservations
                        GROUP BY plate_id
                    ) nr ON nr.plate_id = oi.plate_id
                    WHERE oi.plate_id = ? AND o.order_type = 'donation'
                    LIMIT 1";
        
        $stmt = mysqli_prepare($conn, $availQ);
        mysqli_stmt_bind_param($stmt, "i", $plate_id);
        mysqli_stmt_execute($stmt);
        $availRes =mysqli_stmt_get_result($stmt);
        $plateAvail = mysqli_fetch_assoc($availRes);


        if(!$plateAvail || $plateAvail['available_amt'] <= 0){
            header("Location: Needy.php?taken=0");
            exit();
        }

        $insertQ = "INSERT INTO needy_reservations (user_id, plate_id, reserved_at) VALUES (?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $insertQ);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $plate_id);
        mysqli_stmt_execute($stmt);


        header("Location: Needy.php?taken=1");
        exit();

    }


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

    <?php
    if(isset($_GET['limitReached'])): ?>
        <div class="alert_error">
            Limit reached. Cant take more than 2 plates.
        </div>
    <?php endif; ?>
    <?php 
    if(isset($_GET['taken'])): ?>
        <div class="alert_success">
            Plate successfully reserved!
        </div>

    <?php endif; ?>

    <div class="navigation">
        

        <a href="Needy.php">
            <img src="images/Frame3.png" alt="logo" class="wnk_logo">
        </a>

        <nav class="nav_link">

            <ul class="cust_options">
                <li>
                    <a href="#plateSection" class="plates">Donated Plates</a>
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
    
    <div class="heroSection">
        <div class="heroLeft">
            <h1 class="hero">
                Welcome, <?php echo htmlspecialchars($name); ?>!
            </h1>
        </div>

        <div class="heroRight">
            <h3 class="subHead">
                You can take up to 2 plates for free - already donated by our generous donors.
            </h3>
        
        </div>

        
    
    </div>
    
    <h1 id="plateSection">
        Free Donated Plates Available
    </h1>

    <div class="platesGrid">
        <?php
        $plateQuery = "SELECT oi.plate_id, p.description, p.cost, r.restaurant_name,
               oi.amt - COALESCE(nr.reserved_count, 0) AS available_amt
               FROM order_items oi
               JOIN orders o ON oi.order_id = o.order_id
               JOIN plates p ON oi.plate_id = p.plate_id
               JOIN restaurants r ON p.restaurant_id = r.restaurant_id
               LEFT JOIN (
                   SELECT plate_id, COUNT(*) AS reserved_count
                   FROM needy_reservations
                   GROUP BY plate_id
               ) nr ON nr.plate_id = oi.plate_id
               WHERE o.order_type = 'donation' AND oi.amt - COALESCE(nr.reserved_count,0) > 0";


        $result = mysqli_query($conn, $plateQuery);
        
        while($plate = mysqli_fetch_assoc($result)):
        ?>
            <div class="plateCard">
                <h3><?= htmlspecialchars($plate['description']) ?></h3>
                <p class="restaurantName">Restaurant: <?= htmlspecialchars($plate['restaurant_name']) ?></p>
                <p>Original Price: $<?= number_format($plate['cost'], 2) ?></p>
                <p>Still Available: <?= intval($plate['available_amt']) ?></p>

                <form action="" method="POST">
                    <input type="hidden" name="plate_id" value="<?= $plate['plate_id'] ?>">
                                
                    <button type="submit">Take for FREE</button>
                </form>

            </div>

            <?php endwhile; ?>
    </div>

    <script src="menu.js"></script>
</body>
</html>