<?php
    // start php session to store user info
    session_start();

    // include database connection
    //mysqli connection to MySQL database
    include("config.php");


    // if login page is set up 
    

    // should redirect if not logged in
    if(!isset($_SESSION['user_id'])){
    
        header("Location: login.php");
        exit();
    }

    $user_id =  $_SESSION['user_id'];
    $name = $_SESSION['name'];
    $user_type = $_SESSION['user_type'];    

    if($user_type !== 'customer'){
        echo "This page for customers only";
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

<body>
    
    <div class="navigation">
        

        <a href="Customer.php">
            <img src="images/Frame3.png" alt="logo" class="wnk_logo">
        </a>

        <nav class="nav_link">

            <ul class="cust_options">
                <li>
                    <a href="#plateSection" class="plates">Browse Plates</a>
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
    
    <div class="heroSection">
        <div class="heroLeft">
            <h1 class="hero">
                Welcome, <?php echo htmlspecialchars($name); ?>!
            </h1>
        </div>

        <div class="heroRight">
            <h3 class="subHead">
                Discover fresh surplus plates from local restaurants at great prices.
                Browse today's selections, reserve your favorites, and help reduce food waste.
            </h3>
        
        </div>

        
    
    </div>
    
    <h1 id="plateSection">
        Available Plates
    </h1>

    <div class="platesGrid">
        <?php
        $plateQuery = "SELECT p.plate_id, p.description, p.cost, p.amt, r.restaurant_name, available_from, available_until, status
                        FROM plates p
                        JOIN restaurants r ON p.restaurant_id = r.restaurant_id
                        WHERE p.status = 'available'";
        $result = mysqli_query($conn, $plateQuery);
        
        while($plate = mysqli_fetch_assoc($result)):
        ?>
            <div class="plateCard">
                <h3><?= htmlspecialchars($plate['description']) ?></h3>
                <p class="restaurantName">Restaurant: <?= htmlspecialchars($plate['restaurant_name']) ?></p>
                <p>Price: $<?= number_format($plate['cost'], 2) ?></p>
                <p>Available: <?= intval($plate['amt']) ?></p>

                <form action="reserve.php" method="POST">
                    <input type="hidden" name="plate_id" value="<?= $plate['plate_id'] ?>">

                    <label for="quantity_<?= $plate['plate_id'] ?>">Quantity:</label>
                    <input type="number"
                                id="quantity_<?= $plate['plate_id'] ?>"
                                name="quantity"
                                min="1"
                                max="<?= intval($plate['amt']) ?>"
                                value="1"
                                required>
                                
                    <button type="submit">Reserve</button>
                </form>

            </div>

            <?php endwhile; ?>
    </div>

    <script src="menu.js"></script>
</body>
</html>