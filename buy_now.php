<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please login first.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all cart items
$cart_items = $conn->query("SELECT c.product_id, c.quantity, p.price, p.stock 
                            FROM cart c 
                            JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = $user_id");

if ($cart_items->num_rows == 0) {
    echo "Your cart is empty!";
    exit;
}

$total = 0;
while ($row = $cart_items->fetch_assoc()) {
    if ($row['quantity'] > $row['stock']) {
        echo "Not enough stock for product ID " . $row['product_id'];
        exit;
    }
    $total += $row['price'] * $row['quantity'];
}

// Create order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'completed', NOW())");
$stmt->bind_param("id", $user_id, $total);
$stmt->execute();
$order_id = $stmt->insert_id;

// Insert order items and update stock
$cart_items->data_seek(0); // reset pointer
while ($row = $cart_items->fetch_assoc()) {
    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("iiid", $order_id, $row['product_id'], $row['quantity'], $row['price']);
    $stmt2->execute();

    // Reduce stock
    $new_stock = $row['stock'] - $row['quantity'];
    $conn->query("UPDATE products SET stock=$new_stock WHERE id=".$row['product_id']);
}

// Clear cart
$conn->query("DELETE FROM cart WHERE user_id=$user_id");

echo "Order placed successfully!";
?>
