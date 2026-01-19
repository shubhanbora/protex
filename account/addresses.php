<?php
$pageTitle = 'My Addresses - FitSupps';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/header.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

requireLogin();

$conn = getDBConnection();
if (!$conn) {
    die("Database connection failed");
}

$user_id = getCurrentUserId();

$success = '';
$error = '';

// Check if addresses table exists
$check_table = "SHOW TABLES LIKE 'addresses'";
$table_result = $conn->query($check_table);

if (!$table_result || $table_result->num_rows === 0) {
    echo "<div style='padding: 20px; text-align: center;'>";
    echo "<h2>⚠️ Addresses table not found</h2>";
    echo "<p>Please run the database fix script first:</p>";
    echo "<a href='../fix-database.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fix Database</a>";
    echo "</div>";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'add' || $action === 'edit') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $full_name = $conn->real_escape_string($_POST['full_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $mobile = $conn->real_escape_string($_POST['mobile']);
        $flat_house = $conn->real_escape_string($_POST['flat_house']);
        $locality = $conn->real_escape_string($_POST['locality']);
        $landmark = $conn->real_escape_string($_POST['landmark']);
        $pincode = $conn->real_escape_string($_POST['pincode']);
        $city = $conn->real_escape_string($_POST['city']);
        $state = $conn->real_escape_string($_POST['state']);
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        
        if ($is_default) {
            $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");
        }
        
        if ($action === 'add') {
            $query = "INSERT INTO addresses (user_id, full_name, email, mobile, flat_house, locality, landmark, pincode, city, state, is_default) 
                     VALUES ($user_id, '$full_name', '$email', '$mobile', '$flat_house', '$locality', '$landmark', '$pincode', '$city', '$state', $is_default)";
        } else {
            $query = "UPDATE addresses SET full_name='$full_name', email='$email', mobile='$mobile', flat_house='$flat_house', 
                     locality='$locality', landmark='$landmark', pincode='$pincode', city='$city', state='$state', is_default=$is_default 
                     WHERE id=$id AND user_id=$user_id";
        }
        
        if ($conn->query($query)) {
            $success = 'Address saved successfully';
        } else {
            $error = 'Failed to save address: ' . $conn->error;
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $query = "DELETE FROM addresses WHERE id = $id AND user_id = $user_id";
        
        if ($conn->query($query)) {
            $success = 'Address deleted successfully';
        } else {
            $error = 'Failed to delete address: ' . $conn->error;
        }
    }
}

// Get all addresses
$query = "SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_default DESC, created_at DESC";
$result = $conn->query($query);

if (!$result) {
    $error = 'Failed to load addresses: ' . $conn->error;
}
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">My Addresses</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <button onclick="showAddressForm()" class="btn btn-primary" style="margin-bottom: 2rem;">
        <i class="fas fa-plus"></i> Add New Address
    </button>
    
    <div id="addressForm" style="display: none;" class="card" style="margin-bottom: 2rem;">
        <h3 id="formTitle">Add New Address</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="id" id="addressId" value="">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="mobile">Mobile Number *</label>
                    <input type="tel" id="mobile" name="mobile" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="flat_house">Flat / House / Apartment</label>
                <input type="text" id="flat_house" name="flat_house" class="form-control">
            </div>
            
            <div class="form-group">
                <label for="locality">Locality / Area / Street *</label>
                <input type="text" id="locality" name="locality" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="landmark">Landmark</label>
                <input type="text" id="landmark" name="landmark" class="form-control">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="pincode">Pincode *</label>
                    <input type="text" id="pincode" name="pincode" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="city">City / District *</label>
                    <input type="text" id="city" name="city" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="state">State *</label>
                    <input type="text" id="state" name="state" class="form-control" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_default" id="is_default">
                    Set as default address
                </label>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Save Address</button>
                <button type="button" onclick="hideAddressForm()" class="btn" style="background: #6b7280; color: #fff;">Cancel</button>
            </div>
        </form>
    </div>
    
    <div class="address-list">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($address = $result->fetch_assoc()): ?>
                <div class="card" style="margin-bottom: 1rem; position: relative;">
                    <?php if ($address['is_default']): ?>
                        <span style="position: absolute; top: 1rem; right: 1rem; background: var(--success-color); color: #fff; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.85rem;">
                            Default
                        </span>
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($address['full_name']); ?></h3>
                    <p style="margin: 0.5rem 0;">
                        <?php echo htmlspecialchars($address['flat_house']); ?><br>
                        <?php echo htmlspecialchars($address['locality']); ?><br>
                        <?php if ($address['landmark']): ?>
                            Landmark: <?php echo htmlspecialchars($address['landmark']); ?><br>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' - ' . $address['pincode']); ?>
                    </p>
                    <p style="margin: 0.5rem 0;">
                        <strong>Mobile:</strong> <?php echo htmlspecialchars($address['mobile']); ?>
                        <?php if ($address['email']): ?>
                            <br><strong>Email:</strong> <?php echo htmlspecialchars($address['email']); ?>
                        <?php endif; ?>
                    </p>
                    
                    <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                        <button onclick='editAddress(<?php echo json_encode($address); ?>)' class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Delete this address?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $address['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <i class="fas fa-map-marker-alt" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                <h2>No addresses saved</h2>
                <p style="color: #6b7280;">Add an address to place orders</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showAddressForm() {
    document.getElementById('addressForm').style.display = 'block';
    document.getElementById('formTitle').textContent = 'Add New Address';
    document.getElementById('formAction').value = 'add';
    document.getElementById('addressId').value = '';
    document.querySelector('form').reset();
    
    anime({
        targets: '#addressForm',
        opacity: [0, 1],
        translateY: [-20, 0],
        duration: 400,
        easing: 'easeOutQuad'
    });
}

function hideAddressForm() {
    anime({
        targets: '#addressForm',
        opacity: [1, 0],
        translateY: [0, -20],
        duration: 300,
        easing: 'easeInQuad',
        complete: function() {
            document.getElementById('addressForm').style.display = 'none';
        }
    });
}

function editAddress(address) {
    showAddressForm();
    document.getElementById('formTitle').textContent = 'Edit Address';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('addressId').value = address.id;
    document.getElementById('full_name').value = address.full_name;
    document.getElementById('email').value = address.email || '';
    document.getElementById('mobile').value = address.mobile;
    document.getElementById('flat_house').value = address.flat_house || '';
    document.getElementById('locality').value = address.locality;
    document.getElementById('landmark').value = address.landmark || '';
    document.getElementById('pincode').value = address.pincode;
    document.getElementById('city').value = address.city;
    document.getElementById('state').value = address.state;
    document.getElementById('is_default').checked = address.is_default == 1;
}
</script>

<?php
closeDBConnection($conn);
require_once '../includes/footer.php';
?>
