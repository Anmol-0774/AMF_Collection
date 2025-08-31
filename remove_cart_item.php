<?php
session_start();
require "db.php";

if(!isset($_SESSION['user_id'])) exit;

if(isset($_POST['cart_id'])){
    $cart_id = (int)$_POST['cart_id'];
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
    $stmt->bind_param("ii",$cart_id,$user_id);
    $stmt->execute();
}
?>
