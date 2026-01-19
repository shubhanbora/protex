<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = getCurrentUserId();
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'add') {
    $product_id = intval($data['product_id']);
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        exit();
    }
    
    // Check if product exists and is available - simple query
    $check_query = "SELECT id, stock FROM products WHERE id = $product_id AND status = 'active' AND is_deleted = 0 AND stock > 0";
    $check_result = $conn->query($check_query);
    
    if (!$check_result || $check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not available']);
        exit();
    }
    
    $product = $check_result->fetch_assoc();
    
    // Check if already in cart - simple query
    $cart_check = "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $cart_result = $conn->query($cart_check);
    
    if ($cart_result && $cart_result->num_rows > 0) {
        $cart_item = $cart_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        
        // Check stock availability
        if ($new_quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit();
        }
        
        // Update quantity
        $query = "UPDATE cart SET quantity = $new_quantity WHERE user_id = $user_id AND product_id = $product_id";
        $result = $conn->query($query);
    } else {
        // Check stock availability
        if ($quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
            exit();
        }
        
        // Insert new
        $query = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        $result = $conn->query($query);
    }
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Added to cart successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
} elseif ($action === 'update') {
    $cart_id = intval($data['cart_id']);
    $change = intval($data['change']);
    
    // Make sure quantity doesn't go below 1
    $query = "UPDATE cart SET quantity = GREATEST(1, quantity + $change) WHERE id = $cart_id AND user_id = $user_id";
    $result = $conn->query($query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart: ' . $conn->error]);
    }
    
} elseif ($action === 'remove') {
    $cart_id = intval($data['cart_id']);
    
    $query = "DELETE FROM cart WHERE id = $cart_id AND user_id = $user_id";
    $result = $conn->query($query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
