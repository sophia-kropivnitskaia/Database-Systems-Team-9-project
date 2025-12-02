<?php

//restaurants: A restaurant can advertise to sell their surplus plates at WNK within a
//desired time window. They would describe the plate, its fixed price, and the quantity
//available for sale. WNK members may reserve and pick up one or more plates within
//the advertised window. The WNK tracks the inventory and closes this sell when the
//items are sold out. When a WNK member picks up a plate, the payment is
//automatically charged to the credit card. For simplicity, you do not need to implement
//the payment process.

session_start();
include '../dbs.project/config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];

$message = "";
$toastClass = "";

// Fetch logged-in user's name, user_id and user_type
$stmt = $conn->prepare("
    SELECT user_id, name, user_type 
    FROM users 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $user_name, $user_type);
$stmt->fetch();
$stmt->close();

// Check if user is a restaurant
if($user_type !== 'restaurant'){
    echo "This page is for restaurants only";
    exit();
}

// Get restaurant_id from the restaurants table using user_id
$stmt = $conn->prepare("
    SELECT restaurant_id 
    FROM restaurants 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($restaurant_id);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect inputs
    $plate_name = trim($_POST['plate_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $available_from = trim($_POST['avalible_from'] ?? '');
    $available_until = trim($_POST['avalible_until'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');

    // Basic validation
    $errors = [];
    if ($plate_name === '') {
        $errors[] = "Plate name is required.";
    }
    if ($description === '') {
        $errors[] = "Description is required.";
    }
    if ($available_from === '') {
        $errors[] = "Available from time is required.";
    }
    if ($available_until === '') {
        $errors[] = "Available until time is required.";
    }
    if ($price === '' || $price <= 0) {
        $errors[] = "Valid price is required.";
    }
    if ($quantity === '' || $quantity <= 0) {
        $errors[] = "Valid quantity is required.";
    }

    if (!$errors) {
        
        // combining plate_name and description: "Plate Name: Description"
        $full_description = $plate_name . ": " . $description;
        
        // Insert into plates table with restaurant_id
        // schema: plate_id, restaurant_id, description, cost, amt, available_from, available_until, status, created_at
        $status = 'available'; // Default status
        $stmt = $conn->prepare("
            INSERT INTO plates (restaurant_id, description, cost, amt, available_from, available_until, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isdisss", $restaurant_id, $full_description, $price, $quantity, $available_from, $available_until, $status);
        
        if ($stmt->execute()) {
            $message = "Plate added successfully!";
            $toastClass = "bg-success";
        } else {
            $message = "Error: " . $stmt->error;
            $toastClass = "bg-danger";
        }
        $stmt->close();
    } else {
        $message = implode(" ", $errors);
        $toastClass = "bg-danger";
    }
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WNK Restaurant</title>
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
        .content {
            margin: 40px;
            text-align: center;
        }
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .form-container label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            text-align: left;
        }
        .form-container input,
        .form-container textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        .form-container button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: black;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }
        .message {
            max-width: 600px;
            margin: 20px auto;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            font-weight: 500;
        }
        .bg-success {
            color: white;
            background-color: #208a39ff;
        }
        .bg-danger {
            color: white;
            background-color: #e00b21ff;
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
            <a href="donors.php">Donors</a> <!-- need an update to an actual page -->
            <a href="Customers.php">Customer</a> <!-- need an update to an actual page -->
            <a href="needy.php">Needy</a> <!-- need an update to an actual page -->
            <a href="plates.php">Plates</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="user-section">
            <span> <?php echo htmlspecialchars($user_name ?? 'User'); ?></span>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo $toastClass; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="content">
        <h1>Welcome to WNK Restaurants!</h1>
        <div style="max-width: 700px; margin: 20px auto; text-align: center;">
            <p style="font-size: 16px; color: #333; line-height: 1.6; display: inline-block; text-align: left;">
                Here you can advertise your surplus plates to sell at WNK. Please fill out the information below.<br><br>
                • Provide the available from date and time<br>
                • Provide the available until date and time<br>
                • Provide the description of the plate<br>
                • Provide a fixed price<br>
                • Provide quantity for each plate
            </p>
        </div>
    </div>
    
    <div class="form-container">
        <form method="POST" action="">
            <label for="plate_name">Name of the Plate:</label>
            <input type="text" id="plate_name" name="plate_name" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            
            <label for="avalible_from">Avalible from:</label>
            <input type="datetime-local" id="avalible_from" name="avalible_from" required>

            <label for="avalible_until">Avalible until:</label>
            <input type="datetime-local" id="avalible_until" name="avalible_until" required>
            
            <label for="price">Price ($):</label>
            <input type="number" id="price" name="price" step="0.01" min="0" required>
            
            <label for="quantity">Quantity Available:</label>
            <input type="number" id="quantity" name="quantity" min="1" required>
            
            <button type="submit">Submit </button>
        </form>
    </div>
</body>
</html>


