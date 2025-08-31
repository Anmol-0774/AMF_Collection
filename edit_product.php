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
if($user['role'] !== 'admin'){
    echo "Access denied!";
    exit;
}

// Get product ID from URL
if(!isset($_GET['id'])){
    echo "Product ID missing!";
    exit;
}

$product_id = (int)$_GET['id'];
$product_res = $conn->query("SELECT * FROM products WHERE id=$product_id");
if($product_res->num_rows == 0){
    echo "Product not found!";
    exit;
}
$product = $product_res->fetch_assoc();

// Update product
if(isset($_POST['update_product'])){
    $title = $_POST['title'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];

    // Handle image upload
    $img = $product['img']; // Keep existing
    if(isset($_FILES['img']) && $_FILES['img']['error']==0){
        $imgName = time().'_'.$_FILES['img']['name'];
        move_uploaded_file($_FILES['img']['tmp_name'], "../uploads/".$imgName);
        $img = "uploads/".$imgName;
    }

    $stmt = $conn->prepare("UPDATE products SET title=?, price=?, category=?, img=?, description=?, stock=? WHERE id=?");
    $stmt->bind_param("sdsssii",$title,$price,$category,$img,$description,$stock,$product_id);
    $stmt->execute();
    $message = "Product updated successfully!";
    // Refresh product info
    $product_res = $conn->query("SELECT * FROM products WHERE id=$product_id");
    $product = $product_res->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product · AMF Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#12121e; color:#fff; }
.card { background:#1c1c2e; border:none; border-radius:12px; }
.btn-gold { background:#d4af37; color:#101018; border:none; }
.btn-gold:hover { background:#ffd369; color:#101018; }
</style>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Edit Product (ID: <?= $product['id'] ?>)</h2>

    <?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>

    <div class="card p-4">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($product['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="Clothes" <?= $product['category']=="Clothes"?"selected":"" ?>>Clothes</option>
                    <option value="Cosmetics" <?= $product['category']=="Cosmetics"?"selected":"" ?>>Cosmetics</option>
                    <option value="Footwear" <?= $product['category']=="Footwear"?"selected":"" ?>>Footwear</option>
                    <option value="Home Décor" <?= $product['category']=="Home Décor"?"selected":"" ?>>Home Décor</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" value="<?= $product['stock'] ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <img src="../<?= $product['img'] ?>" alt="<?= htmlspecialchars($product['title']) ?>" style="max-width:150px; border-radius:8px;">
            </div>
            <div class="mb-3">
                <label class="form-label">Change Image</label>
                <input type="file" name="img" class="form-control" accept="image/*">
            </div>
            <button type="submit" name="update_product" class="btn btn-gold w-100">Update Product</button>
        </form>
    </div>
</div>
</body>
</html>
