<?php
// Working checkout page
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.stock 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ? AND p.status = 'active' AND p.is_deleted = 0";

$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();

if (!$cart_result || $cart_result->num_rows === 0) {
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
$cart_items = [];
while ($item = $cart_result->fetch_assoc()) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $cart_items[] = $item;
}

// Get user addresses
$addr_query = "SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
$addr_stmt = $conn->prepare($addr_query);
$addr_stmt->bind_param("i", $user_id);
$addr_stmt->execute();
$addr_result = $addr_stmt->get_result();

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$shipping_charges = 50; // Fixed shipping
$discount = 0;
$coupon_applied = '';

// Handle coupon application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = trim($_POST['coupon_code']);
    if (strtoupper($coupon_code) === 'SAVE10') {
        $discount = $subtotal * 0.10; // 10% discount
        $coupon_applied = $coupon_code;
        $success = 'Coupon SAVE10 applied! You saved ₹' . number_format($discount, 2);
    } else {
        $error = 'Invalid coupon code';
    }
}

// Free shipping above ₹1000
if ($subtotal > 1000) {
    $shipping_charges = 0;
}

$total = $subtotal - $discount + $shipping_charges;

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $address_id = intval($_POST['address_id']);
    $payment_method = trim($_POST['payment_method']);
    
    if ($address_id <= 0) {
        $error = 'Please select a delivery address';
    } elseif (!in_array($payment_method, ['cod', 'online', 'card'])) {
        $error = 'Please select a valid payment method';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create order
            $order_query = "INSERT INTO orders (user_id, address_id, subtotal, discount, shipping_charges, total_amount, payment_method, payment_status, order_status, coupon_code, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', ?, NOW())";
            
            $order_stmt = $conn->prepare($order_query);
            $order_stmt->bind_param("iidddsss", $user_id, $address_id, $subtotal, $discount, $shipping_charges, $total, $payment_method, $coupon_applied);
            
            if (!$order_stmt->execute()) {
                throw new Exception('Failed to create order: ' . $order_stmt->error);
            }
            
            $order_id = $conn->insert_id;
            
            // Add order items
            foreach ($cart_items as $item) {
                $item_subtotal = $item['price'] * $item['quantity'];
                $item_query = "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                
                $item_stmt = $conn->prepare($item_query);
                $item_stmt->bind_param("iisdid", $order_id, $item['product_id'], $item['name'], $item['price'], $item['quantity'], $item_subtotal);
                
                if (!$item_stmt->execute()) {
                    throw new Exception('Failed to add order items: ' . $item_stmt->error);
                }
                
                // Update product stock
                $stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
                $stock_stmt = $conn->prepare($stock_query);
                $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stock_stmt->execute();
            }
            
            // Clear cart
            $clear_query = "DELETE FROM cart WHERE user_id = ?";
            $clear_stmt = $conn->prepare($clear_query);
            $clear_stmt->bind_param("i", $user_id);
            $clear_stmt->execute();
            
            $conn->commit();
            
            // Redirect to success page
            header('Location: order-success.php?order_id=' . $order_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Order placement failed: ' . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">🛒 Checkout</h1>
    
    <?php if ($error): ?>
        <div class="alert alert-error" style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success" style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <!-- Delivery Address Section -->
                <div class="card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
                    <h2 style="margin-bottom: 1.5rem; color: #1e293b;">📍 Select Delivery Address</h2>
                    
                    <?php if ($addr_result && $addr_result->num_rows > 0): ?>
                        <?php while ($address = $addr_result->fetch_assoc()): ?>
                            <label style="display: block; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px; margin-bottom: 1rem; cursor: pointer; transition: border-color 0.2s;">
                                <input type="radio" name="address_id" value="<?php echo $address['id']; ?>" 
                                       <?php echo $address['is_default'] ? 'checked' : ''; ?> required>
                                <strong><?php echo htmlspecialchars($address['full_name']); ?></strong>
                                <?php if ($address['is_default']): ?>
                                    <span style="background: #10b981; color: #fff; padding: 0.2rem 0.5rem; border-radius: 10px; font-size: 0.75rem; margin-left: 0.5rem;">Default</span>
                                <?php endif; ?>
                                <p style="margin: 0.5rem 0 0 1.5rem; color: #6b7280;">
                                    <?php echo htmlspecialchars($address['flat_house']); ?><br>
                                    <?php echo htmlspecialchars($address['locality']); ?><br>
                                    <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' - ' . $address['pincode']); ?><br>
                                    Mobile: <?php echo htmlspecialchars($address['mobile']); ?>
                                </p>
                            </label>
                        <?php endwhile; ?>
                        
                        <a href="account/addresses.php" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">
                            <i class="fas fa-plus"></i> Add New Address
                        </a>
                    <?php else: ?>
                        <p style="color: #6b7280; margin-bottom: 1rem;">No addresses found. Please add an address first.</p>
                        <a href="account/addresses.php" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 8px;">
                            Add Address
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Method Section -->
                <div class="card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2 style="margin-bottom: 1.5rem; color: #1e293b;">💳 Payment Method</h2>
                    
                    <label style="display: block; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px; margin-bottom: 1rem; cursor: pointer; transition: border-color 0.2s;">
                        <input type="radio" name="payment_method" value="cod" checked required>
                        <strong>💵 Cash on Delivery</strong>
                        <p style="color: #6b7280; font-size: 0.9rem; margin: 0.5rem 0 0 1.5rem;">Pay when your order is delivered</p>
                    </label>
                    
                    <label style="display: block; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px; margin-bottom: 1rem; cursor: pointer; transition: border-color 0.2s;">
                        <input type="radio" name="payment_method" value="online" required>
                        <strong>💳 Online Payment</strong>
                        <p style="color: #6b7280; font-size: 0.9rem; margin: 0.5rem 0 0 1.5rem;">Pay using UPI, Net Banking, or Wallet</p>
                    </label>
                    
                    <label style="display: block; padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px; cursor: pointer; transition: border-color 0.2s;">
                        <input type="radio" name="payment_method" value="card" required>
                        <strong>💳 Credit/Debit Card</strong>
                        <p style="color: #6b7280; font-size: 0.9rem; margin: 0.5rem 0 0 1.5rem;">Pay using your credit or debit card</p>
                    </label>
                </div>
            </div>
            
            <!-- Order Summary Section -->
            <div>
                <div class="card" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: sticky; top: 2rem;">
                    <h2 style="margin-bottom: 1.5rem; color: #1e293b;">📋 Order Summary</h2>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0;">
                            <div>
                                <p style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p style="color: #6b7280; font-size: 0.9rem;">Qty: <?php echo $item['quantity']; ?></p>
                            </div>
                            <p style="font-weight: 600; color: #1e293b;">
                                ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Coupon Section -->
                    <div style="margin: 1.5rem 0; padding: 1rem; background: #f8fafc; border-radius: 8px;">
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" name="coupon_code" placeholder="Enter coupon code" 
                                   style="flex: 1; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <button type="submit" name="apply_coupon" 
                                    style="padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer;">
                                Apply
                            </button>
                        </div>
                        <p style="font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem;">Try: SAVE10 for 10% off</p>
                    </div>
                    
                    <!-- Price Breakdown -->
                    <div style="border-top: 2px solid #e2e8f0; padding-top: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #10b981;">
                            <span>Discount:</span>
                            <span>-₹<?php echo number_format($discount, 2); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Shipping:</span>
                            <span><?php echo $shipping_charges > 0 ? '₹' . number_format($shipping_charges, 2) : 'FREE'; ?></span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e2e8f0;">
                            <strong style="font-size: 1.2rem;">Total:</strong>
                            <strong style="font-size: 1.5rem; color: #3b82f6;">
                                ₹<?php echo number_format($total, 2); ?>
                            </strong>
                        </div>
                    </div>
                    
                    <button type="submit" name="place_order" class="btn" 
                            style="width: 100%; margin-top: 1.5rem; padding: 1rem; font-size: 1.1rem; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                        🛒 Place Order
                    </button>
                </div>
            </div>
        </div>
    </form>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="cart.php" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #6b7280; color: white; text-decoration: none; border-radius: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Cart
        </a>
        <a href="index.php" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #6b7280; color: white; text-decoration: none; border-radius: 8px; margin-left: 1rem;">
            <i class="fas fa-home"></i> Continue Shopping
        </a>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

input[type="radio"]:checked + strong {
    color: #3b82f6;
}

label:has(input[type="radio"]:checked) {
    border-color: #3b82f6 !important;
    background: #f0f9ff;
}

@media (max-width: 768px) {
    .container > form > div {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php
$conn->close();
require_once 'includes/footer.php';
?>