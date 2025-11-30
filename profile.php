<?php
session_start();
include '../dbs.project/config.php';

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$message = "";
$toastClass = "";

// Fetch current user information including user_type and user_id
$stmt = $conn->prepare("
    SELECT user_id, name, email, address, phone, user_type 
    FROM users 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($user_id, $name, $db_email, $address, $phone, $user_type);
$stmt->fetch();
$stmt->close();

// Fetch credit card if user is customer or donor
$credit_card = "";
if ($user_type === 'customer') {
    $stmt = $conn->prepare("
        SELECT credit_card 
        FROM customers 
        WHERE user_id = ?
        ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($credit_card);
    $stmt->fetch();
    $stmt->close();
} elseif ($user_type === 'donor') {
    $stmt = $conn->prepare("
        SELECT credit_card 
        FROM donors 
        WHERE user_id = ?
        ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($credit_card);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form inputs
    $new_name = trim($_POST['name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_address = trim($_POST['address'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $new_credit_card = trim($_POST['credit_card'] ?? '');
    
    if (!$errors) {
        if ($new_email !== $email) {
            $checkStmt = $conn->prepare("
                SELECT email 
                FROM users 
                WHERE email = ? AND email != ?
            ");
            $checkStmt->bind_param("ss", $new_email, $email);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                $message = "Email already exists for another account";
                $toastClass = "bg-danger";
            }
            $checkStmt->close();
        }
        
        // Update the user's information if no errors
        if ($message === "") {
            $updateStmt = $conn->prepare("
                UPDATE users 
                SET name = ?, email = ?, address = ?, phone = ? 
                WHERE email = ?
            ");
            $updateStmt->bind_param("sssss", $new_name, $new_email, $new_address, $new_phone, $email);
            
            if ($updateStmt->execute()) {
                if ($new_email !== $email) {
                    $_SESSION['email'] = $new_email;
                    $email = $new_email;
                }
                
                // Update credit card if user is customer or donor
                if ($user_type === 'customer' && $new_credit_card !== '') {
                    $ccStmt = $conn->prepare("
                        UPDATE customers 
                        SET credit_card = ? 
                        WHERE user_id = ?
                        ");
                    $ccStmt->bind_param("si", $new_credit_card, $user_id);
                    $ccStmt->execute();
                    $ccStmt->close();
                } elseif ($user_type === 'donor' && $new_credit_card !== '') {
                    $ccStmt = $conn->prepare("
                        UPDATE donors 
                        SET credit_card = ? 
                        WHERE user_id = ?
                        ");
                    $ccStmt->bind_param("si", $new_credit_card, $user_id);
                    $ccStmt->execute();
                    $ccStmt->close();
                }
                
                $message = "Profile updated successfully!";
                $toastClass = "bg-success";
            }
            
            $updateStmt->close();
        }
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
    <title>WNK Member Info</title>
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
        .mainTxt {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
        }
        h2 {
            margin-bottom: 10px;
            text-align: center;
        }
        form {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        input {
            width: 100%;
            padding: 6px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            margin-top: 15px;
            width: 100%;
            padding: 8px;
            background-color: #000000ff;
            color: white;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: green;
            color: white;
        }
        .error {
            background-color: #dc3545;
            color: white;
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
            <a href="customers.php">Customer</a> <!-- need an update to an actual page -->
            <a href="needy.php">Needy</a> <!-- need an update to an actual page -->
            <a href="plates.php">Plates</a>
            <a href="logout.php">Logout</a>
        </div>
        <div class="user-section">
            <span> <?php echo htmlspecialchars($user_name); ?></span>
        </div>
    </div>

    <div class="mainTxt">
        <div>
            <h2>WNK Member Information</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo ($toastClass === 'bg-success') ? 'success' : 'error'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>

                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($db_email ?? ''); ?>" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>" required>

                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>" required>

                <?php if ($user_type === 'customer' || $user_type === 'donor'): ?>
                    <label for="credit_card">Credit Card:</label>
                    <input type="text" id="credit_card" name="credit_card" value="<?php echo htmlspecialchars($credit_card ?? ''); ?>" placeholder="Enter credit card number">
                <?php endif; ?>

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>