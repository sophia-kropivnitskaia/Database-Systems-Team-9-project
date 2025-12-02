<?php
// ---- MySQL settings (Windows MAMP defaults) ----
$servername = "localhost";
$username   = "root";
$password   = "";           // Windows MAMP default is empty string ""
$dbname     = "WNK"; // <-- use the DB name you created in phpMyAdmin

// ---- connect ----
$conn = new mysqli($servername, $username, $password, $dbname);

// error check
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

// set charset for proper text handling
if (!$conn->set_charset("utf8mb4")) {
    die("Error setting charset: " . $conn->error);
}
?>
