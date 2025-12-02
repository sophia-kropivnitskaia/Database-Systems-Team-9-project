<?php
include '../dbs.project/config.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
        SELECT password 
        FROM users 
        WHERE email = ?
        ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password);
        $stmt->fetch();

        // passwords stored with password_hash() so we need password_verify()
        if (password_verify($password, $db_password)) {
            $message = "Login successful";
            $toastClass = "bg-success";

            // redirect to homepage
            session_start();
            $_SESSION['email'] = $email;
            header("Location: homepage.php");
            exit();
        } else {
            $message = "Incorrect password";
            $toastClass = "bg-danger";
        }
    } else {
        $message = "Email not found";
        $toastClass = "bg-warning";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">

    <title>Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #e3d09d;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .message {
            background-color: #d4edda;
            color: #228d3bff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            position: relative;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .message .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: inherit;
            opacity: 0.5;
        }

        .message .close-btn:hover {
            opacity: 1;
        }

        form {
            background: white;
            padding: 30px;
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 10px;
        }

        h5 {
            text-align: center;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #4CAF50;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: black;
            color: white;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .text-center {
            text-align: center;
        }

        .account-link {
            margin-top: 20px;
            text-align: center;
        }

        .account-link p {
            margin: 5px 0;
        }

        .account-link a {
            color: navy;
            text-decoration: none;
            font-weight: 600;
        }

    </style>
</head>

<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo ($toastClass === 'bg-danger') ? 'error' : ''; ?>">
                <?php echo $message; ?>
                <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
            </div>
        <?php endif; ?>
        <form action="" method="post">
            <h1>WNK</h1>
            <h5>Login Into Your Account</h5>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">
            </div>
            
            <button type="submit">Login</button>
            
            <div class="account-link">
                <p>Don't have an account? <a href="./register.php">Create Account</a></p>
            </div>
        </form>
    </div>

</body>

</html>