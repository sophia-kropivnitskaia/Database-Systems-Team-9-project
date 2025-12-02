<?php
session_start();
include("config.php");

if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit();
}


$user_id = $_SESSION['user_id'];
$plate_id = $_POST['plate_id'];
$quantity = intval($_POST['quantity']);

if(!$plate_id){
    header("Location: Customer.php?error=no_plate");
    exit();
}

$query = "INSERT INTO reservations(user_id, plate_id, quantity) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $plate_id, $quantity);
mysqli_stmt_execute($stmt);


$update = "UPDATE plates
           SET amt = amt - ?
           WHERE plate_id = ? AND amt >= ?";

$updateStmt =mysqli_prepare($conn, $update);
mysqli_stmt_bind_param($updateStmt, "iii", $quantity, $plate_id, $quantity);
mysqli_stmt_execute($updateStmt);



$sold = "UPDATE plates
         SET status = 'sold_out'
         WHERE plate_id = ? AND amt = 0";


$soldStmt = mysqli_prepare($conn, $sold);
mysqli_stmt_bind_param($soldStmt, "i", $plate_id);
mysqli_stmt_execute($soldStmt);

header("Location: Reservations.php?reserved=success");
exit();
?>