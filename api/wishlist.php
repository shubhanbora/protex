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
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit();
    }
    
    // Check if already in wishlist
    $check_query = "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
        exit();
    }
    
    $query = "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)";
    $result = $conn->query($query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Added to wishlist successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
    
} elseif ($action === 'remove') {
    $wishlist_id = intval($data['wishlist_id']);
    
    if ($wishlist_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid wishlist ID']);
        exit();
    }
    
    $query = "DELETE FROM wishlist WHERE id = $wishlist_id AND user_id = $user_id";
    $result = $conn->query($query);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
