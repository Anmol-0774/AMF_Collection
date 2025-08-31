<?php
session_start();
require "../db.php";

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Get cancellation requests
$cancellations_sql = "SELECT o.*, u.name as user_name 
                      FROM orders o 
                      JOIN users u ON o.user_id = u.id 
                      WHERE o.status = 'cancelled' AND o.cancellation_status = 'pending'
                      ORDER BY o.cancelled_at DESC";
$cancellations_result = $conn->query($cancellations_sql);
$cancellations = [];
if ($cancellations_result->num_rows > 0) {
    while ($row = $cancellations_result->fetch_assoc()) {
        $cancellations[] = $row;
    }
}

// Handle cancellation approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action']; // 'approve' or 'reject'
    
    if ($action == 'approve') {
        $sql = "UPDATE orders SET cancellation_status = 'approved' WHERE id = $order_id";
        $message = "Cancellation request approved.";
    } else {
        $reason = mysqli_real_escape_string($conn, $_POST['rejection_reason']);
        $sql = "UPDATE orders SET cancellation_status = 'rejected', status = 'confirmed' WHERE id = $order_id";
        $message = "Cancellation request rejected.";
        
        // If rejecting, we need to deduct stock again
        $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = $order_id";
        $items_result = $conn->query($items_sql);
        
        while ($item = $items_result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $product_id");
        }
    }
    
    if ($conn->query($sql)) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Error processing request: " . $conn->error;
    }
    
    header("Location: admin_cancellations.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cancellations - AMF Collection</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg-grad-1: #0f0c29;
            --bg-grad-2: #302b63;
            --bg-grad-3: #24243e;
            --surface: #111322;
            --card: #171a2e;
            --muted: #9aa0b4;
            --text: #f5f7ff;
            --gold: #d4af37;
            --gold-soft: #ffd369;
            --glass: rgba(255,255,255,.06);
            --glass-stroke: rgba(255,255,255,.12);
            --radius: 18px;
            --shadow: rgba(0,0,0,.45);
        }

        body {
            background: linear-gradient(120deg, var(--bg-grad-1) 0%, var(--bg-grad-2) 55%, var(--bg-grad-3) 100%) fixed;
            color: var(--text);
            font-family: 'Poppins', sans-serif;
        }

        .navbar {
            backdrop-filter: saturate(140%) blur(10px);
            background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
            border-bottom: 1px solid var(--glass-stroke);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: .5px;
            color: var(--gold) !important;
        }

        .nav-link, .text-white {
            color: #e9ecff !important;
            opacity: .85;
            transition: .25s ease;
        }

        .nav-link:hover, .text-white:hover {
            color: var(--gold-soft) !important;
            opacity: 1;
        }

        .admin-container {
            backdrop-filter: saturate(140%) blur(10px);
            background: linear-gradient(180deg, rgba(12,12,22,.9), rgba(12,12,22,.65));
            border: 1px solid var(--glass-stroke);
            border-radius: var(--radius);
            box-shadow: 0 10px 30px var(--shadow);
            padding: 2rem;
            margin: 2rem auto;
        }

        .card {
            background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.03));
            border: 1px solid var(--glass-stroke);
            border-radius: var(--radius);
            box-shadow: 0 10px 30px var(--shadow);
        }

        .btn-gold {
            background: linear-gradient(140deg, var(--gold) 0%, var(--gold-soft) 100%);
            color: #14151f;
            border: none;
            font-weight: 600;
            border-radius: 999px;
            padding: .6rem 1rem;
            box-shadow: 0 6px 20px rgba(212,175,55,.25);
            transition: transform .25s ease, box-shadow .25s ease, filter .25s ease;
        }

        .btn-gold:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
            box-shadow: 0 10px 26px rgba(212,175,55,.35);
        }

        .btn-outline-light {
            border-radius: 999px;
            border: 1px solid var(--glass-stroke);
            backdrop-filter: blur(6px);
        }

        .status-badge {
            padding: 0.35em 0.65em;
            border-radius: 999px;
            font-size: 0.75em;
            font-weight: 700;
        }

        .status-pending { background-color: #6c757d; color: white; }
        .status-confirmed { background-color: #17a2b8; color: white; }
        .status-shipped { background-color: #ffc107; color: black; }
        .status-completed { background-color: #28a745; color: white; }
        .status-cancelled { background-color: #dc3545; color: white; }
        .status-cancellation-pending { background-color: #fd7e14; color: white; }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #fff;
            letter-spacing: .2px;
        }

        .section-sub {
            color: var(--muted);
        }

        .modal-content {
            background: var(--card);
            border: 1px solid var(--glass-stroke);
            color: var(--text);
        }

        .modal-header {
            border-bottom: 1px solid var(--glass-stroke);
        }

        .modal-footer {
            border-top: 1px solid var(--glass-stroke);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="home.php">
                <i class="bi bi-gem"></i>
                <span>AMF Collection - Admin Panel</span>
            </a>
            
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a href="admin_orders.php" class="text-white text-decoration-none">Orders</a>
                <a href="admin_cancellations.php" class="text-white text-decoration-none fw-bold">Cancellations</a>
                <a href="admin_products.php" class="text-white text-decoration-none">Products</a>
                <a href="logout.php" class="text-white text-decoration-none">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="admin-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="section-title mb-0"><i class="bi bi-x-circle me-2"></i>Manage Cancellation Requests</h2>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (empty($cancellations)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                    <h4 class="mt-3 section-title">No pending cancellation requests</h4>
                    <p class="section-sub">All cancellation requests have been processed.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Order Date</th>
                                <th>Amount</th>
                                <th>Cancellation Reason</th>
                                <th>Requested On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cancellations as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo $order['user_name']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($order['cancellation_reason']); ?></td>
                                    <td><?php echo date('M j, Y, g:i a', strtotime($order['cancelled_at'])); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-success me-2">Approve</button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $order['id']; ?>">Reject</button>
                                        
                                        <!-- Reject Modal -->
                                        <div class="modal fade" id="rejectModal<?php echo $order['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Reject Cancellation Request</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <div class="mb-3">
                                                                <label for="rejection_reason" class="form-label">Reason for Rejection</label>
                                                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required placeholder="Please provide a reason for rejecting this cancellation request"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>