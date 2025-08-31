<?php
session_start();
require "../db.php";

// Admin check
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$res = $conn->query("SELECT role FROM users WHERE id=$user_id");
$user = $res->fetch_assoc();
if(!$user || $user['role'] !== 'admin'){
    echo "Access denied!";
    exit;
}

// Get dates
$start_date = $_POST['start_date'] ?? date('Y-m-01');
$end_date = $_POST['end_date'] ?? date('Y-m-d');

// Calculate total sales
$totalSalesQuery = $conn->prepare("SELECT SUM(total_amount) AS total_sales FROM orders WHERE status='Completed' AND DATE(created_at) BETWEEN ? AND ?");
$totalSalesQuery->bind_param("ss", $start_date, $end_date);
$totalSalesQuery->execute();
$totalSales = $totalSalesQuery->get_result()->fetch_assoc()['total_sales'] ?? 0;

// Fetch recent orders
$orderSql = "SELECT o.id AS order_id, u.name AS user_name, o.total_amount, o.status, o.created_at
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE DATE(o.created_at) BETWEEN ? AND ?
             ORDER BY o.created_at DESC";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$ordersResult = $stmt->get_result();

// Fetch top products
$topProductsSql = "SELECT p.title, SUM(oi.quantity) AS total_sold 
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.id
                   JOIN orders o ON oi.order_id = o.id
                   WHERE o.status='Completed' AND DATE(o.created_at) BETWEEN ? AND ?
                   GROUP BY oi.product_id
                   ORDER BY total_sold DESC
                   LIMIT 5";
$stmt2 = $conn->prepare($topProductsSql);
$stmt2->bind_param("ss", $start_date, $end_date);
$stmt2->execute();
$topProductsResult = $stmt2->get_result();

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=full_report_'.$start_date.'_to_'.$end_date.'.csv');

$output = fopen('php://output', 'w');

// Total Sales
fputcsv($output, ['Total Sales', '$'.number_format($totalSales, 2)]);
fputcsv($output, []); // empty line

// Top Products
fputcsv($output, ['Top Products']);
fputcsv($output, ['Product Title', 'Total Sold']);
while($row = $topProductsResult->fetch_assoc()){
    fputcsv($output, $row);
}
fputcsv($output, []); // empty line

// Recent Orders
fputcsv($output, ['Recent Orders']);
fputcsv($output, ['Order ID', 'User Name', 'Total Amount', 'Status', 'Created At']);
while($row = $ordersResult->fetch_assoc()){
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
