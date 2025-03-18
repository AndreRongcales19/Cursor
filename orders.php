<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user's orders
$user_id = $_SESSION['user_id'];
$sql = "SELECT o.*, COUNT(oi.id) as item_count 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.user_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo">Online Market</a>
                <div class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php">Cart</a>
                        <a href="orders.php" class="active">My Orders</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Login</a>
                        <a href="register.php">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>My Orders</h1>

        <main>
            <?php if (!empty($result)): ?>
                <div class="orders-list">
                    <?php foreach ($result as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?php echo $order['id']; ?></h3>
                                <span class="order-date"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                            </div>
                            <div class="order-details">
                                <p>Status: <span class="status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></p>
                                <p>Total Amount: $<?php echo number_format($order['total_amount'], 2); ?></p>
                                <p>Items: <?php echo $order['item_count']; ?></p>
                            </div>
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-orders">
                    <p>You haven't placed any orders yet.</p>
                    <a href="index.php" class="btn">Start Shopping</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html> 