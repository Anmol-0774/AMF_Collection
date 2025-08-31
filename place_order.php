<?php
session_start();
require "db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
// At the top of place_order.php, after session_start()
if (isset($_GET['address_id'])) {
    $address_id = intval($_GET['address_id']);
    
    // Fetch address details
    $address_sql = "SELECT * FROM user_addresses WHERE id = $address_id AND user_id = $user_id";
    $address_result = $conn->query($address_sql);
    
    if ($address_result->num_rows > 0) {
        $address = $address_result->fetch_assoc();
        $_POST['name'] = $address['recipient_name'];
        $_POST['phone'] = $address['phone'];
        $_POST['address'] = $address['address'] . ", " . $address['city'] . ", " . $address['district'] . ", " . $address['province'];
        if (!empty($address['landmark'])) {
            $_POST['address'] .= " (Landmark: " . $address['landmark'] . ")";
        }
    }
}

$user_id = $_SESSION['user_id'];

// Fetch cart
$stmt = $conn->prepare("SELECT c.quantity, p.id as product_id, p.price 
                        FROM cart c JOIN products p ON c.product_id = p.id 
                        WHERE c.user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();
$res = $stmt->get_result();
$cart_items = $res->fetch_all(MYSQLI_ASSOC);

if(empty($cart_items)){
    header("Location: checkout.php");
    exit;
}

// Calculate total
$total = 0;
foreach($cart_items as $item){
    $total += $item['price'] * $item['quantity'];
}

// Insert order
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?,?, 'Pending', NOW())");
$stmt->bind_param("id",$user_id,$total);
$stmt->execute();
$order_id = $stmt->insert_id;

// Insert order_items
$stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
foreach($cart_items as $item){
    $stmt->bind_param("iiid",$order_id,$item['product_id'],$item['quantity'],$item['price']);
    $stmt->execute();
}

// Clear cart
$stmt = $conn->prepare("DELETE FROM cart WHERE user_id=?");
$stmt->bind_param("i",$user_id);
$stmt->execute();

header("Location: order_success.php?id=$order_id");
exit;
?>
