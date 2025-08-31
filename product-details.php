<?php
session_start();
require "db.php";

// Get product ID from URL
if(!isset($_GET['id'])){
    echo "Product not found.";
    exit;
}
$productId = (int)$_GET['id'];

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i",$productId);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows==0){
    echo "Product not found.";
    exit;
}
$product = $res->fetch_assoc();

// Handle Add to Cart or Buy Now
if(isset($_POST['add_to_cart']) || isset($_POST['buy_now'])){
    if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit;
    }
    $user_id = $_SESSION['user_id'];
    $quantity = max(1,(int)$_POST['quantity']);

    // Check if product already in cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id=? AND product_id=?");
    $stmt->bind_param("ii",$user_id,$productId);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows>0){
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity=quantity+? WHERE user_id=? AND product_id=?");
        $stmt->bind_param("iii",$quantity,$user_id,$productId);
        $stmt->execute();
    } else {
        // Insert new cart item
        $stmt = $conn->prepare("INSERT INTO cart(user_id,product_id,quantity,added_at) VALUES(?,?,?,NOW())");
        $stmt->bind_param("iii",$user_id,$productId,$quantity);
        $stmt->execute();
    }

    if(isset($_POST['buy_now'])){
        header("Location: checkout.php");
        exit;
    } else {
        $msg = "Added to cart!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $product['title']; ?> · AMF Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{ background:#12121e; color:#fff; font-family:'Poppins',sans-serif; }
.btn-gold{ background:#d4af37; color:#101018; border:none; }
.btn-gold:hover{ background:#ffd369; color:#101018; }
</style>
</head>
<body>
<div class="container py-5">
  <a href="home.php" class="btn btn-outline-light mb-4"><i class="bi bi-arrow-left"></i> Back to home</a>
  <?php if(isset($msg)) echo "<div class='alert alert-success'>$msg</div>"; ?>
  <div class="row">
    <div class="col-md-6">
      <img src="<?= $product['img']; ?>" class="img-fluid rounded" alt="<?= $product['title']; ?>">
    </div>
    <div class="col-md-6">
      <h2><?= $product['title']; ?></h2>
      <h4 class="text-warning">$<?= number_format($product['price'],2); ?></h4>
      <p><?= $product['description']; ?></p>
      <p><strong>Category:</strong> <?= $product['category']; ?></p>
      <p><strong>Stock:</strong> <?= $product['stock']; ?></p>
      <form method="post" class="d-flex gap-2">
        <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock']; ?>" class="form-control w-25">
        <button type="submit" name="add_to_cart" class="btn btn-gold">Add to Cart</button>
        <button type="submit" name="buy_now" class="btn btn-warning">Buy Now</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
