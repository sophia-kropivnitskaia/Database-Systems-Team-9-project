<?php
session_start();
//include("config.php");

include '../dbs.project/config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$plate_id = $_POST['plate_id'];
$quantity = $_POST['quantity'];

$insertQuery = "INSERT INTO donations (user_id, plate_id, amt, donated_at) VALUES (?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $insertQuery);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $plate_id, $quantity);
mysqli_stmt_execute($stmt);

$updatePlate = "UPDATE plates SET amt = amt - ? WHERE plate_id = ?";
$stmt2 = mysqli_prepare($conn, $updatePlate);
mysqli_stmt_bind_param($stmt2, "ii", $quantity, $plate_id);
mysqli_stmt_execute($stmt2);

header("Location: Donations.php");
exit();
?>