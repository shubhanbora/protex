<?php
$pageTitle = 'Refer & Earn - FitSupps';
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

// Get referral count
$ref_query = "SELECT COUNT(*) as total_referrals FROM users WHERE referred_by = $user_id";
$ref_result = $conn->query($ref_query);
$ref_data = $ref_result ? $ref_result->fetch_assoc() : ['total_referrals' => 0];

$referral_link = "http://" . $_SERVER['HTTP_HOST'] . "/fitsuup/register.php?ref=" . $user['referral_code'];
?>

<div class="container">
    <h1 style="font-size: 2rem; margin-bottom: 2rem;">Refer & Earn</h1>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <div class="card" style="text-align: center;">
            <i class="fas fa-users" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $ref_data['total_referrals']; ?></h2>
            <p style="color: #6b7280;">Total Referrals</p>
        </div>
        
        <div class="card" style="text-align: center;">
            <i class="fas fa-gift" style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;"></i>
            <h2 style="font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo $user['reward_points']; ?></h2>
            <p style="color: #6b7280;">Reward Points</p>
        </div>
    </div>
    
    <div class="card">
        <h2 style="margin-bottom: 1.5rem;">Your Referral Code</h2>
        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem;">
            <input type="text" id="referralCode" value="<?php echo htmlspecialchars($user['referral_code']); ?>" 
                   class="form-control" readonly style="flex: 1; font-size: 1.5rem; font-weight: bold; text-align: center;">
            <button onclick="copyCode()" class="btn btn-primary">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>
        
        <h3 style="margin-bottom: 1rem;">Share Your Referral Link</h3>
        <div style="display: flex; gap: 1rem; align-items: center;">
            <input type="text" id="referralLink" value="<?php echo htmlspecialchars($referral_link); ?>" 
                   class="form-control" readonly style="flex: 1;">
            <button onclick="copyLink()" class="btn btn-primary">
                <i class="fas fa-copy"></i> Copy Link
            </button>
        </div>
    </div>
    
    <div class="card">
        <h2 style="margin-bottom: 1.5rem;">How It Works</h2>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                    1
                </div>
                <h3>Share</h3>
                <p style="color: #6b7280;">Share your referral code or link with friends</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                    2
                </div>
                <h3>Register</h3>
                <p style="color: #6b7280;">Your friend registers using your code</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 60px; height: 60px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1rem;">
                    3
                </div>
                <h3>Earn</h3>
                <p style="color: #6b7280;">You earn 100 reward points instantly!</p>
            </div>
        </div>
    </div>
</div>

<script>
function copyCode() {
    const codeInput = document.getElementById('referralCode');
    codeInput.select();
    document.execCommand('copy');
    showNotification('Referral code copied!', 'success');
}

function copyLink() {
    const linkInput = document.getElementById('referralLink');
    linkInput.select();
    document.execCommand('copy');
    showNotification('Referral link copied!', 'success');
}
</script>

<?php
closeDBConnection($conn);
require_once '../includes/footer.php';
?>
