<?php
session_start();
require "db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add_to_cart') {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        
        // If not found, add new item
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'quantity' => $quantity
            ];
        }
        
        // Also add to database if user is logged in
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            // Check if product already in user's cart
            $check_sql = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
            $check_result = $conn->query($check_sql);
            
            if ($check_result->num_rows > 0) {
                // Update quantity
                $update_sql = "UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id";
                $conn->query($update_sql);
            } else {
                // Insert new item
                $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
                $conn->query($insert_sql);
            }
        }
        
        echo json_encode([
            'success' => true,
            'cart_count' => count($_SESSION['cart'])
        ]);
    }
}