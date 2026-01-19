<?php
$pageTitle = 'My Profile - FitSupps';
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    
    $query = "UPDATE users SET full_name = '$name', email = '$email', phone = '$phone' WHERE id = $user_id";
    
    if ($conn->query($query)) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $success = 'Profile updated successfully';
    } else {
        $error = 'Failed to update profile: ' . $conn->error;
    }
}

// Get user data
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);

if (!$result) {
    die("Failed to load user data: " . $conn->error);
}

$user = $result->fetch_assoc();

// Generate referral code if missing
if (empty($user['referral_code'])) {
    $referral_code = strtoupper(substr($user['full_name'], 0, 3) . rand(1000, 9999));
    $update_query = "UPDATE users SET referral_code = '$referral_code' WHERE id = $user_id";
    if ($conn->query($update_query)) {
        $user['referral_code'] = $referral_code;
    }
}
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">My Profile</h1>
    
    <div class="card" style="max-width: 600px;">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-control" 
                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Referral Code</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($user['referral_code'] ?? 'Not Generated'); ?>" readonly>
                <small style="color: #6b7280;">Share this code with friends to earn rewards</small>
            </div>
            
            <div class="form-group">
                <label>Reward Points</label>
                <input type="text" class="form-control" 
                       value="<?php echo $user['reward_points'] ?? 0; ?>" readonly>
            </div>
            
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>
</div>

<?php
closeDBConnection($conn);
require_once '../includes/footer.php';
?>
