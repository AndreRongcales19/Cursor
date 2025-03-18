<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch cart items and calculate total
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.stock, p.image_url 
    FROM shopping_cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate stock availability
    $stock_error = false;
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch();
        
        if ($product['stock'] < $item['quantity']) {
            $stock_error = true;
            $error = "Sorry, some items in your cart are no longer available in the requested quantity.";
            break;
        }
    }

    if (!$stock_error) {
        $shipping_address = filter_input(INPUT_POST, 'shipping_address', FILTER_SANITIZE_STRING);
        $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
        $postal_code = filter_input(INPUT_POST, 'postal_code', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

        if ($shipping_address && $city && $postal_code && $phone) {
            $pdo->beginTransaction();
            try {
                // Create order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, shipping_address, city, postal_code, phone, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$_SESSION['user_id'], $total, $shipping_address, $city, $postal_code, $phone]);
                $order_id = $pdo->lastInsertId();

                // Add order items and update stock
                foreach ($cart_items as $item) {
                    // Add order item
                    $stmt = $pdo->prepare("
                        INSERT INTO order_items (order_id, product_id, quantity, price) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);

                    // Update product stock
                    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $stmt->execute([$item['quantity'], $item['product_id']]);
                }

                // Clear cart
                $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE user_id = ?");
                $stmt->execute([$_SESSION['user_id']]);

                $pdo->commit();
                header("Location: order_confirmation.php?id=" . $order_id);
                exit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "An error occurred while processing your order. Please try again.";
            }
        } else {
            $error = "Please fill in all required fields.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Online Market</title>
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
                        <a href="orders.php">My Orders</a>
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
        <h1>Checkout</h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="checkout-container">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <div class="checkout-items">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="checkout-item">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-info">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                                <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="total">
                    <h3>Total Amount: $<?php echo number_format($total, 2); ?></h3>
                </div>
            </div>

            <div class="shipping-details">
                <h2>Shipping Details</h2>
                <form method="POST" class="checkout-form">
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address*</label>
                        <textarea id="shipping_address" name="shipping_address" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="city">City*</label>
                        <input type="text" id="city" name="city" required>
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Postal Code*</label>
                        <input type="text" id="postal_code" name="postal_code" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number*</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <button type="submit" class="place-order-btn">Place Order</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 