<?php
$pageTitle = 'Product Form';

// Security Check - Must be logged in as admin
require_once 'auth_check.php';

require_once '../config/database.php';
require_once 'includes/header.php';

$conn = getDBConnection();

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$is_edit = $product_id > 0;

$product = null;
if ($is_edit) {
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
    $product = mysqli_fetch_assoc($result);
    if (!$product) {
        header('Location: products.php');
        exit();
    }
}

$error = '';
$success = '';

// Handle image upload
function handleImageUpload($file, $index) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return '';
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return '';
    }
    
    // Create uploads directory if not exists
    $upload_dir = '../uploads/products/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'product_' . time() . '_' . $index . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/products/' . $filename;
    }
    
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category_id = intval($_POST['category_id']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $weight = mysqli_real_escape_string($conn, $_POST['weight'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Handle image uploads
    $image = '';
    $image_2 = '';
    $image_3 = '';
    $image_4 = '';
    $image_5 = '';
    
    // Check if URL is provided or file is uploaded
    if (!empty($_POST['image_url'])) {
        $image = mysqli_real_escape_string($conn, $_POST['image_url']);
    } elseif (isset($_FILES['image_1'])) {
        $image = handleImageUpload($_FILES['image_1'], 1);
    }
    
    if (!empty($_POST['image_url_2'])) {
        $image_2 = mysqli_real_escape_string($conn, $_POST['image_url_2']);
    } elseif (isset($_FILES['image_2'])) {
        $image_2 = handleImageUpload($_FILES['image_2'], 2);
    }
    
    if (!empty($_POST['image_url_3'])) {
        $image_3 = mysqli_real_escape_string($conn, $_POST['image_url_3']);
    } elseif (isset($_FILES['image_3'])) {
        $image_3 = handleImageUpload($_FILES['image_3'], 3);
    }
    
    if (!empty($_POST['image_url_4'])) {
        $image_4 = mysqli_real_escape_string($conn, $_POST['image_url_4']);
    } elseif (isset($_FILES['image_4'])) {
        $image_4 = handleImageUpload($_FILES['image_4'], 4);
    }
    
    if (!empty($_POST['image_url_5'])) {
        $image_5 = mysqli_real_escape_string($conn, $_POST['image_url_5']);
    } elseif (isset($_FILES['image_5'])) {
        $image_5 = handleImageUpload($_FILES['image_5'], 5);
    }
    
    // Keep existing images if editing and no new upload
    if ($is_edit && $product) {
        if (empty($image)) $image = $product['image'];
        if (empty($image_2)) $image_2 = $product['image_2'];
        if (empty($image_3)) $image_3 = $product['image_3'];
        if (empty($image_4)) $image_4 = $product['image_4'];
        if (empty($image_5)) $image_5 = $product['image_5'];
    }
    
    if ($is_edit) {
        $query = "UPDATE products SET name='$name', description='$description', category_id=$category_id, 
                 price=$price, stock=$stock, weight='$weight', status='$status', image='$image', 
                 image_2='$image_2', image_3='$image_3', image_4='$image_4', image_5='$image_5' 
                 WHERE id=$product_id";
    } else {
        $query = "INSERT INTO products (name, description, category_id, price, stock, weight, status, image, image_2, image_3, image_4, image_5) 
                 VALUES ('$name', '$description', $category_id, $price, $stock, '$weight', '$status', '$image', '$image_2', '$image_3', '$image_4', '$image_5')";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = $is_edit ? 'Product updated successfully' : 'Product added successfully';
        if (!$is_edit) {
            // Redirect with success message
            $_SESSION['product_success'] = 'Product added successfully!';
            header('Location: products.php');
            exit();
        } else {
            // Reload product data after update
            $result = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
            $product = mysqli_fetch_assoc($result);
        }
    } else {
        $error = 'Failed to save product: ' . mysqli_error($conn);
    }
}

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>

<h2 style="margin-bottom: 2rem;"><?php echo $is_edit ? 'Edit Product' : 'Add New Product'; ?></h2>

<div class="card" style="max-width: 800px;">
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" class="form-control" 
                   value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" 
                       value="<?php echo $product['price'] ?? ''; ?>" required>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="stock">Stock *</label>
                <input type="number" id="stock" name="stock" class="form-control" 
                       value="<?php echo $product['stock'] ?? '0'; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="weight">Weight (e.g., 1kg, 500g, 2lbs)</label>
                <input type="text" id="weight" name="weight" class="form-control" 
                       placeholder="1kg" 
                       value="<?php echo htmlspecialchars($product['weight'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" class="form-control" required>
                <option value="active" <?php echo ($product['status'] ?? 'active') == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($product['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Product Images (Upload or URL)</label>
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem;">
                You can upload images (JPG, PNG, GIF, WebP - Max 5MB each) or provide URLs. Upload up to 5 images.
            </p>
            
            <!-- Image 1 -->
            <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <label for="image_1" style="font-weight: 600; color: var(--primary-color);">Image 1 (Main)</label>
                
                <?php if ($is_edit && !empty($product['image'])): ?>
                    <div style="margin: 0.5rem 0;">
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                             style="max-width: 150px; max-height: 150px; border-radius: 4px;">
                        <p style="font-size: 0.85rem; color: #6b7280;">Current Image</p>
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div>
                        <label for="image_1" style="font-size: 0.9rem;">Upload File:</label>
                        <input type="file" id="image_1" name="image_1" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               onchange="previewImage(this, 'preview_1')">
                        <div id="preview_1" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div>
                        <label for="image_url" style="font-size: 0.9rem;">Or Image URL:</label>
                        <input type="text" id="image_url" name="image_url" class="form-control" 
                               placeholder="https://example.com/image.jpg"
                               value="<?php echo htmlspecialchars($product['image'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Image 2 -->
            <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <label for="image_2" style="font-weight: 600;">Image 2 (Optional)</label>
                
                <?php if ($is_edit && !empty($product['image_2'])): ?>
                    <div style="margin: 0.5rem 0;">
                        <img src="../<?php echo htmlspecialchars($product['image_2']); ?>" 
                             style="max-width: 150px; max-height: 150px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div>
                        <input type="file" id="image_2" name="image_2" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               onchange="previewImage(this, 'preview_2')">
                        <div id="preview_2" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div>
                        <input type="text" name="image_url_2" class="form-control" 
                               placeholder="https://example.com/image2.jpg"
                               value="<?php echo htmlspecialchars($product['image_2'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Image 3 -->
            <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <label for="image_3" style="font-weight: 600;">Image 3 (Optional)</label>
                
                <?php if ($is_edit && !empty($product['image_3'])): ?>
                    <div style="margin: 0.5rem 0;">
                        <img src="../<?php echo htmlspecialchars($product['image_3']); ?>" 
                             style="max-width: 150px; max-height: 150px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div>
                        <input type="file" id="image_3" name="image_3" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               onchange="previewImage(this, 'preview_3')">
                        <div id="preview_3" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div>
                        <input type="text" name="image_url_3" class="form-control" 
                               placeholder="https://example.com/image3.jpg"
                               value="<?php echo htmlspecialchars($product['image_3'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Image 4 -->
            <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <label for="image_4" style="font-weight: 600;">Image 4 (Optional)</label>
                
                <?php if ($is_edit && !empty($product['image_4'])): ?>
                    <div style="margin: 0.5rem 0;">
                        <img src="../<?php echo htmlspecialchars($product['image_4']); ?>" 
                             style="max-width: 150px; max-height: 150px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div>
                        <input type="file" id="image_4" name="image_4" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               onchange="previewImage(this, 'preview_4')">
                        <div id="preview_4" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div>
                        <input type="text" name="image_url_4" class="form-control" 
                               placeholder="https://example.com/image4.jpg"
                               value="<?php echo htmlspecialchars($product['image_4'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            
            <!-- Image 5 -->
            <div style="border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                <label for="image_5" style="font-weight: 600;">Image 5 (Optional)</label>
                
                <?php if ($is_edit && !empty($product['image_5'])): ?>
                    <div style="margin: 0.5rem 0;">
                        <img src="../<?php echo htmlspecialchars($product['image_5']); ?>" 
                             style="max-width: 150px; max-height: 150px; border-radius: 4px;">
                    </div>
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 0.5rem;">
                    <div>
                        <input type="file" id="image_5" name="image_5" class="form-control" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               onchange="previewImage(this, 'preview_5')">
                        <div id="preview_5" style="margin-top: 0.5rem;"></div>
                    </div>
                    <div>
                        <input type="text" name="image_url_5" class="form-control" 
                               placeholder="https://example.com/image5.jpg"
                               value="<?php echo htmlspecialchars($product['image_5'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary">
                <?php echo $is_edit ? 'Update Product' : 'Add Product'; ?>
            </button>
            <a href="products.php" class="btn" style="background: #6b7280; color: #fff;">Cancel</a>
        </div>
    </form>
</div>

<?php
closeDBConnection($conn);
require_once 'includes/footer.php';
?>


<script>
// Image preview function
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '150px';
            img.style.maxHeight = '150px';
            img.style.borderRadius = '4px';
            img.style.border = '2px solid var(--success-color)';
            preview.appendChild(img);
            
            const text = document.createElement('p');
            text.textContent = 'Preview';
            text.style.fontSize = '0.85rem';
            text.style.color = 'var(--success-color)';
            text.style.marginTop = '0.25rem';
            preview.appendChild(text);
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
