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

// Add new user
if(isset($_POST['add_user'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if($check->num_rows > 0){
        $message = "Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?,?,?,?,NOW())");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        $message = "User added successfully!";
    }
}

// Delete user
if(isset($_GET['delete'])){
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE id=$delete_id");
    header("Location: admin_users.php");
    exit;
}

// Fetch all users
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Users · AMF Collection</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { font-family:'Poppins',sans-serif; background:#12121e; color:#fff; }
.card, .table { background:#1c1c2e; border:none; border-radius:12px; }
.btn-gold { background:#d4af37; color:#101018; border:none; }
.btn-gold:hover { background:#ffd369; color:#101018; }
</style>
</head>
<body>
<div class="container my-5">
    <h2 class="mb-4">User Management</h2>

    <?php if(isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>

    <!-- Add User Form -->
    <div class="card p-3 mb-4">
        <h5>Add New User</h5>
        <form method="post">
            <div class="row g-3">
                <div class="col-md-3"><input type="text" name="name" class="form-control" placeholder="Name" required></div>
                <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="col-md-2">
                    <select name="role" class="form-select" required>
                        <option value="">Role</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                </div>
                <div class="col-md-1"><button type="submit" name="add_user" class="btn btn-gold w-100">Add</button></div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card p-3">
        <table class="table table-dark table-hover align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['role'] ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-outline-light btn-sm mb-1">Edit</a>
                        <a href="admin_users.php?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
