<?php
include '../dbs.project/config.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //inputs
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $address  = trim($_POST['address'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $user_type = trim($_POST['user_type'] ?? 'customer');
    $credit_card = trim($_POST['credit_card'] ?? '');

    // validation
    $errors = [];
    if ($username === '' || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }
    if ($address === '') {
        $errors[] = "Address is required.";
    }

    if (!$errors) {
        $checkEmailStmt = $conn->prepare("
            SELECT email 
            FROM users 
            WHERE email = ?
            ");
        $checkEmailStmt->bind_param("s", $email);
        $checkEmailStmt->execute();
        $checkEmailStmt->store_result();

        if ($checkEmailStmt->num_rows > 0) {
            $message = "Email ID already exists";
            $toastClass = "#007bffff"; 
        } else {
            // hash the password 
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (name, email, password, address, phone, user_type)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssss", $username, $email, $hash, $address, $phone, $user_type);

            if ($stmt->execute()) {
                // get user_id
                $user_id = $stmt->insert_id;
                $stmt->close();
        
                $subtype_success = false;
                
                switch($user_type) {
                    case 'restaurant':
                        $subStmt = $conn->prepare("
                            INSERT INTO restaurants (user_id, restaurant_name) 
                            VALUES (?, ?)"
                        );
                        $subStmt->bind_param("is", $user_id, $username);
                        $subtype_success = $subStmt->execute();
                        $subStmt->close();
                        break;
                        
                    case 'donor':
                        $subStmt = $conn->prepare("
                            INSERT INTO donors (user_id, credit_card)
                            VALUES (?, ?)"
                        );
                        $subStmt->bind_param("is", $user_id, $credit_card);
                        $subtype_success = $subStmt->execute();
                        $subStmt->close();
                        break;
                        
                    case 'needy':
                        $subStmt = $conn->prepare("
                            INSERT INTO needy (user_id) 
                            VALUES (?)"
                        );
                        $subStmt->bind_param("i", $user_id);
                        $subtype_success = $subStmt->execute();
                        $subStmt->close();
                        break;
                        
                    case 'customer':
                    default:
                        $subStmt = $conn->prepare("
                            INSERT INTO customers (user_id, credit_card) 
                            VALUES (?, ?)"
                        );
                        $subStmt->bind_param("is", $user_id, $credit_card);
                        $subtype_success = $subStmt->execute();
                        $subStmt->close();
                        break;
                }
                
                if ($subtype_success) {
                    $message = "Account created successfully";
                    $toastClass = "#16c940ff"; 
                    echo '<meta http-equiv="refresh" content="1.5;url=./login.php">';
                } else {
                    $message = "Account created but failed to set user type";
                    $toastClass = "#ffc107"; 
                }
            } else {
                $message = "Error: " . $stmt->error;
                $toastClass = "#ee1127ff"; 
                $stmt->close();
            }
        }

        $checkEmailStmt->close();
    } else {
        $message = implode(" ", $errors);
        $toastClass = "#dc3545";
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
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
            max-width: 380px;
        }
        .message {
            background-color: #28a745;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 10px;
        }
        h5 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 30px;
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            margin-top: 15px;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns="http://www.w3.org/2000/svg" width="12" height="8" viewBox="0 0 12 8"%3E%3Cpath fill="%23333" d="M6 8L0 0h12z"/%3E%3C/svg%3E');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            padding-right: 30px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: black;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .text-center {
            text-align: center;
        }
        .link {
            color: navy;
            text-decoration: none;
            font-weight: 600;
        }
        .link-admin {
            color: #326ce7ff;
            text-decoration: none;
            font-size: 14px;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="message" style="background-color: <?php echo htmlspecialchars($toastClass); ?>;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h1>WNK</h1>
            <h5>Create Your Account</h5>

            <form id="regForm" method="post">
                <label for="username">User Name</label>
                <input type="text" name="username" id="username" required minlength="3">

                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" required minlength="8">

                <label for="address">Address</label>
                <input type="text" name="address" id="address" required>

                <label for="user_type">User Type</label>
                <select name="user_type" id="user_type" required>
                    <option value="restaurant">Restaurant</option>
                    <option value="needy">Needy</option>
                    <option value="customer">Customer</option>
                    <option value="donor">Donor</option>
                </select>

                <label for="phone" id="phone_label">Phone</label>
                <input type="text" name="phone" id="phone" required>

                <div id="credit_card_section">
                    <label for="credit_card">Credit Card Information</label>
                    <input type="text" name="credit_card" id="credit_card" placeholder="XXXX-XXXX-XXXX-XXXX">
                </div>

                <button type="submit">Create Account</button>

                <p class="text-center" style="margin-top: 20px;">
                    <a href="./login.php" class="link">Login</a>
                </p>
                
                <p class="text-center" style="margin-top: 10px;">
                    <a href="./admin_login.php" class="link-admin">Login as an Administrator</a>
                </p>
            </form>
        </div>
    </div>

    <script>
      // show credit card and phone number based on user type
      document.getElementById('user_type').addEventListener('change', function() {
        const userType = this.value;
        const creditCardSection = document.getElementById('credit_card_section');
        const creditCardInput = document.getElementById('credit_card');
        const phoneInput = document.getElementById('phone');
        const phoneLabel = document.getElementById('phone_label');
        
        // credit card only for donor or customer
        if (userType === 'customer' || userType === 'donor') {
          creditCardSection.style.display = 'block';
          creditCardInput.required = true;
        } else {
          creditCardSection.style.display = 'none';
          creditCardInput.required = false;
          creditCardInput.value = '';
        }
        
        // phone optional for needy 
        if (userType === 'needy') {
          phoneInput.required = false;
          phoneLabel.innerHTML = '<i class="fa fa-phone"></i> Phone (optional)';
        } else {
          phoneInput.required = true;
          phoneLabel.innerHTML = '<i class="fa fa-phone"></i> Phone';
        }
      });
      
      // checks the rules
      document.getElementById('regForm').addEventListener('submit', function(e) {
        const u = this.username.value.trim();
        const em = this.email.value.trim();
        const pw = this.password.value;
        const addr = this.address.value.trim();
        const ph = this.phone.value.trim();
        const userType = document.getElementById('user_type').value;
        const cc = document.getElementById('credit_card').value.trim();

        let errs = [];
        if (u.length < 3) errs.push("Username must be at least 3 characters.");
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) errs.push("Enter a valid email address.");
        if (pw.length < 8) errs.push("Password must be at least 8 characters.");
        if (!addr) errs.push("Address is required.");
        if (userType !== 'needy' && !ph) errs.push("Phone is required.");
        if (ph && !/^[0-9\-\s\(\)\+]{7,20}$/.test(ph)) errs.push("Enter a valid phone number.");
        if ((userType === 'customer' || userType === 'donor') && !cc) errs.push("Credit card is required for customers and donors.");

        if (errs.length) {
          e.preventDefault();
          alert(errs.join('\\n'));
        }
      });
    </script>
</body>
</html>
