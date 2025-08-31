<?php
session_name('AMF_ADMIN_SESSION');
session_start();

// Redirect to login if not authenticated as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require "../db.php"; // adjust path if needed

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php"); // redirect non-logged-in users
    exit;
}

// Check if user is admin
$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT role FROM users WHERE id=$user_id");
$user = $res->fetch_assoc();

if(!$user || $user['role'] !== 'admin'){
    echo "Access denied!";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard · AMF Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Admin Dashboard</a>
        <a class="btn btn-outline-light" href="logout.php">Logout</a>
    </div>
</nav>
<div class="container my-4">
    <h3>Welcome, Admin!</h3>
    <div class="list-group">
        <a href="admin_users.php" class="list-group-item list-group-item-action">Manage Users</a>
        <a href="reports.php" class="list-group-item list-group-item-action">Reports / Stats</a>
        <a href="products.php" class="list-group-item list-group-item-action">Manage Products</a>
        <a href="orders.php" class="list-group-item list-group-item-action">Manage Orders</a>
         <a href="admin_cancellations.php" class="list-group-item list-group-item-action">Cencellation Requests</a>

    </div>
</div>
</body>
</html>
