<?php
session_start();
require "db.php";

// If user is already logged in as admin, destroy that session first
if (isset($_SESSION['admin_id'])) {
    session_destroy();
    session_start();
}

// Redirect if already logged in as customer
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Check if user is not an admin
                if ($user['role'] !== 'admin') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    header("Location: home.php");
                    exit();
                } else {
                    $error = "Please use the admin login for administrator accounts.";
                }
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "No account found with this email";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Customer Login - AMF Collection</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    :root{
      --bg-grad-1:#0f0c29;
      --bg-grad-2:#302b63;
      --bg-grad-3:#24243e;
      --text:#f5f7ff;
      --gold:#d4af37;
      --gold-soft:#ffd369;
      --radius: 18px;
    }

    body{
      background: linear-gradient(120deg,var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
      color: var(--text);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      height: 100vh;
      display: flex;
      align-items: center;
    }

    .auth-container {
      backdrop-filter: saturate(140%) blur(10px);
      background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
      border: 1px solid rgba(255,255,255,.12);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px rgba(0,0,0,.45);
      padding: 2rem;
      width: 100%;
      max-width: 450px;
    }

    .navbar-brand{
      font-weight: 700;
      letter-spacing:.5px;
      color: var(--gold) !important;
    }

    .btn-gold{
      background: linear-gradient(140deg, var(--gold) 0%, var(--gold-soft) 100%);
      color:#14151f; border:none; font-weight:600; border-radius: 999px; padding:.6rem 1rem;
      box-shadow: 0 6px 20px rgba(212,175,55,.25);
      transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
    }
    .btn-gold:hover{ transform: translateY(-2px); filter: brightness(1.05); box-shadow:0 10px 26px rgba(212,175,55,.35); }

    .form-control{ 
      background: rgba(255,255,255,.08); 
      border:1px solid rgba(255,255,255,.15); 
      color:#fff; 
      border-radius: 999px;
      padding: 0.75rem 1.5rem;
    }
    .form-control::placeholder{ color:#cbd1ff; opacity:.6; }
    .form-control:focus {
      background: rgba(255,255,255,.12);
      border-color: var(--gold-soft);
      box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
      color: #fff;
    }

    .auth-link {
      color: var(--gold-soft);
      text-decoration: none;
    }
    .auth-link:hover {
      color: var(--gold);
      text-decoration: underline;
    }

    .divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
      margin: 1.5rem 0;
    }
    
    .customer-icon {
      font-size: 3rem;
      color: var(--gold-soft);
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center">
    <div class="auth-container">
      <div class="text-center mb-4">
        <div class="customer-icon">
          <i class="bi bi-person-circle"></i>
        </div>
        <a class="navbar-brand d-flex align-items-center gap-2 justify-content-center" href="home.php">
          <i class="bi bi-gem"></i>
          <span>AMF Collection</span>
        </a>
        <h2 class="mt-3">Customer Login</h2>
        <p class="text-muted">Sign in to your customer account</p>
      </div>

      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> For administrators, please use the <a href="admin/login.php" class="alert-link">admin login</a>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <button type="submit" class="btn btn-gold w-100">Sign In</button>
      </form>

      <div class="divider"></div>

      <div class="text-center">
        <p class="mb-0">Don't have an account? <a href="signup.php" class="auth-link">Sign up</a></p>
        <p class="mt-2"><a href="#" class="auth-link">Forgot your password?</a></p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>