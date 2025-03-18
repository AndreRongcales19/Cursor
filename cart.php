<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle remove from cart
if (isset($_POST['remove_item'])) {
    $cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
    if ($cart_id) {
        $stmt = $pdo->prepare("DELETE FROM shopping_cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cart_id, $_SESSION['user_id']]);
    }
}

// Handle update quantity
if (isset($_POST['update_quantity'])) {
    $cart_id = filter_input(INPUT_POST, 'cart_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    if ($cart_id && $quantity) {
        $stmt = $pdo->prepare("UPDATE shopping_cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    }
}

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.stock, p.image_url 
    FROM shopping_cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Online Market</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo">Online Market</a>
                <div class="nav-links">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="cart.php" class="active">Cart</a>
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
        <h1>Shopping Cart</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Your cart is empty.</p>
                <a href="index.php" class="btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
                            <form method="POST" class="quantity-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <label for="quantity_<?php echo $item['id']; ?>">Quantity:</label>
                                <input type="number" id="quantity_<?php echo $item['id']; ?>" name="quantity" 
                                       value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" required>
                                <button type="submit" name="update_quantity" class="update-btn">Update</button>
                            </form>
                            <form method="POST" class="remove-form">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                            </form>
                        </div>
                        <div class="stock-info">
                            <p>In Stock: <?php echo $item['stock']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <h2>Order Summary</h2>
                <div class="summary-details">
                    <p>Subtotal: $<?php echo number_format($total, 2); ?></p>
                    <p>Shipping: Calculated at checkout</p>
                    <p class="total">Total: $<?php echo number_format($total, 2); ?></p>
                </div>
                <div class="cart-actions">
                    <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
                    <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 