<?php
/**
 * Enhanced Image Upload Handler for Products
 * Handles multiple image uploads with validation and optimization
 */

require_once 'auth_check.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Configuration
$upload_dir = '../uploads/products/';
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
$max_file_size = 5 * 1024 * 1024; // 5MB
$max_width = 1200;
$max_height = 1200;

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function resizeImage($source, $destination, $max_width, $max_height, $quality = 85) {
    $info = getimagesize($source);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = intval($width * $ratio);
    $new_height = intval($height * $ratio);
    
    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG
    if ($mime == 'image/png') {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
        $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
        imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save image
    switch ($mime) {
        case 'image/jpeg':
            $result = imagejpeg($new_image, $destination, $quality);
            break;
        case 'image/png':
            $result = imagepng($new_image, $destination, 9);
            break;
        case 'image/webp':
            $result = imagewebp($new_image, $destination, $quality);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($new_image);
    
    return $result;
}

try {
    $uploaded_files = [];
    $errors = [];
    
    // Handle multiple file uploads
    if (isset($_FILES['images'])) {
        $files = $_FILES['images'];
        
        // Normalize file array structure
        if (!is_array($files['name'])) {
            $files = [
                'name' => [$files['name']],
                'type' => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error' => [$files['error']],
                'size' => [$files['size']]
            ];
        }
        
        $file_count = count($files['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            $file_name = $files['name'][$i];
            $file_type = $files['type'][$i];
            $file_tmp = $files['tmp_name'][$i];
            $file_error = $files['error'][$i];
            $file_size = $files['size'][$i];
            
            // Skip empty files
            if ($file_error === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            
            // Check for upload errors
            if ($file_error !== UPLOAD_ERR_OK) {
                $errors[] = "Upload error for file $file_name: " . $file_error;
                continue;
            }
            
            // Validate file type
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "Invalid file type for $file_name. Allowed: JPEG, PNG, WebP";
                continue;
            }
            
            // Validate file size
            if ($file_size > $max_file_size) {
                $errors[] = "File $file_name is too large. Maximum size: 5MB";
                continue;
            }
            
            // Generate unique filename
            $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $unique_name = 'product_' . time() . '_' . ($i + 1) . '.' . $file_extension;
            $destination = $upload_dir . $unique_name;
            
            // Resize and save image
            if (resizeImage($file_tmp, $destination, $max_width, $max_height)) {
                $uploaded_files[] = [
                    'original_name' => $file_name,
                    'filename' => $unique_name,
                    'url' => 'uploads/products/' . $unique_name,
                    'size' => filesize($destination)
                ];
            } else {
                $errors[] = "Failed to process image: $file_name";
            }
        }
    }
    
    if (!empty($uploaded_files)) {
        echo json_encode([
            'success' => true,
            'message' => count($uploaded_files) . ' image(s) uploaded successfully',
            'files' => $uploaded_files,
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No files were uploaded',
            'errors' => $errors
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Upload failed: ' . $e->getMessage()
    ]);
}
?>