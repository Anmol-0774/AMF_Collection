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

// Get user ID to edit
if(!isset($_GET['id'])){
    header("Location: admin_users.php");
    exit;
}
$edit_id = (int)$_GET['id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $edit_id);
$stmt->execute();
$result = $stmt->get_result();
$edit_user = $result->fetch_assoc();
if(!$edit_user){
    echo "User not found!";
    exit;
}

// Handle form submission
if(isset($_POST['update_user'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Update password only if entered
    if(!empty($_POST['password'])){
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $email, $password, $role, $edit_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $email, $role, $edit_id);
    }

    $stmt->execute();
    $message = "User updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User · AMF Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#12121e; color:#fff; }
.card { background:#1c1c2e; border:none; border-radius:12px; padding:20px; }
.btn-gold { background:#d4af37; color:#101018; border:none; }
.btn-gold:hover { background:#ffd369; color:#101018; }
</style>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">Edit User</h2>

    <?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>

    <div class="card">
        <form method="post">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($edit_user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($edit_user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label>Role</label>
                <select name="role" class="form-select" required>
                    <option value="admin" <?= $edit_user['role']=='admin'?'selected':'' ?>>Admin</option>
                    <option value="customer" <?= $edit_user['role']=='customer'?'selected':'' ?>>Customer</option>
                </select>
            </div>
            <button type="submit" name="update_user" class="btn btn-gold">Update User</button>
            <a href="admin_users.php" class="btn btn-outline-light">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
