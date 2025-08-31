<?php
session_start();
require "db.php";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

$error = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if email already exists
        $check_email = $conn->query("SELECT id FROM users WHERE email='$email'");
        if ($check_email->num_rows > 0) {
            $error = "Email already registered";
        } else {
            // Hash password and create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role, created_at) 
                    VALUES ('$name', '$email', '$hashed_password', 'customer', NOW())";
            
            if ($conn->query($sql)) {
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'customer';
                
                header("Location: home.php");
                exit();
            } else {
                $error = "Error creating account: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - AMF Collection</title>
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
  <!-- Animate.css -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

  <style>
    :root{
      /* Luxury Indigo x Soft Gold */
      --bg-grad-1:#0f0c29;
      --bg-grad-2:#302b63;
      --bg-grad-3:#24243e;
      --surface:#111322; /* deep surface */
      --card:#171a2e; /* card base */
      --muted:#9aa0b4; /* muted text */
      --text:#f5f7ff; /* main text */
      --gold:#d4af37; /* metallic gold */
      --gold-soft:#ffd369; /* soft gold for UI */
      --accent:#7aa2ff; /* cool accent */
      --shadow: rgba(0,0,0,.45);
      --glass: rgba(255,255,255,.06);
      --glass-stroke: rgba(255,255,255,.12);
      --radius: 18px;
    }

    html,body{
      background: linear-gradient(120deg,var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
      color: var(--text);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial;
      height: 100vh;
      display: flex;
      align-items: center;
    }

    .auth-container {
      backdrop-filter: saturate(140%) blur(10px);
      background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
      border: 1px solid var(--glass-stroke);
      border-radius: var(--radius);
      box-shadow: 0 10px 30px var(--shadow);
      padding: 2rem;
      width: 100%;
      max-width: 450px;
    }

    .navbar-brand{
      font-family: 'Playfair Display', serif;
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

    .password-strength {
      height: 5px;
      margin-top: 5px;
      border-radius: 5px;
      background: #444;
    }
    
    .strength-weak { width: 33%; background: #ff4d4d; }
    .strength-medium { width: 66%; background: #ffa64d; }
    .strength-strong { width: 100%; background: #2eb82e; }
  </style>
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center">
    <div class="auth-container animate__animated animate__fadeIn">
      <div class="text-center mb-4">
        <a class="navbar-brand d-flex align-items-center gap-2 justify-content-center" href="home.php">
          <i class="bi bi-gem"></i>
          <span>AMF Collection</span>
        </a>
        <h2 class="mt-3">Create Account</h2>
        <p class="text-muted">Join the AMF community</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
          <div class="password-strength" id="passwordStrength"></div>
          <small class="text-muted">Must be at least 6 characters</small>
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="terms" required>
          <label class="form-check-label" for="terms">I agree to the <a href="#" class="auth-link">Terms & Conditions</a></label>
        </div>
        <button type="submit" class="btn btn-gold w-100">Create Account</button>
      </form>

      <div class="divider"></div>

      <div class="text-center">
        <p class="mb-0">Already have an account? <a href="login.php" class="auth-link">Sign in</a></p>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('passwordStrength');
    
    passwordInput.addEventListener('input', function() {
      const password = passwordInput.value;
      let strength = 0;
      
      if (password.length >= 6) strength += 1;
      if (password.length >= 8) strength += 1;
      if (/[A-Z]/.test(password)) strength += 1;
      if (/[0-9]/.test(password)) strength += 1;
      if (/[^A-Za-z0-9]/.test(password)) strength += 1;
      
      strengthBar.className = 'password-strength';
      
      if (password.length > 0) {
        if (strength < 2) {
          strengthBar.classList.add('strength-weak');
        } else if (strength < 4) {
          strengthBar.classList.add('strength-medium');
        } else {
          strengthBar.classList.add('strength-strong');
        }
      }
    });
  </script>
</body>
</html>