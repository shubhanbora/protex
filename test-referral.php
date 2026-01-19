<?php
// Test referral system
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'config/session.php';

if (!isLoggedIn()) {
    echo "<h2>Please login first</h2>";
    echo "<a href='login.php'>Login</a>";
    exit();
}

$conn = getDBConnection();
$user_id = getCurrentUserId();

echo "<h2>🎁 Referral System Test</h2>";

// Get user data
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();

echo "<h3>📋 Current User Data:</h3>";
echo "<p><strong>Name:</strong> " . htmlspecialchars($user['full_name'] ?? 'Not set') . "</p>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($user['email'] ?? 'Not set') . "</p>";
echo "<p><strong>Current Referral Code:</strong> " . htmlspecialchars($user['referral_code'] ?? 'NULL') . "</p>";
echo "<p><strong>Reward Points:</strong> " . ($user['reward_points'] ?? 0) . "</p>";

// Generate referral code if missing
if (empty($user['referral_code'])) {
    echo "<h3>⚠️ Referral Code Missing - Generating...</h3>";
    
    $name_part = strtoupper(substr($user['full_name'] ?? 'USER', 0, 3));
    $number_part = rand(1000, 9999);
    $referral_code = $name_part . $number_part;
    
    echo "<p>Generated Code: <strong>$referral_code</strong></p>";
    
    $update_query = "UPDATE users SET referral_code = '$referral_code' WHERE id = $user_id";
    if ($conn->query($update_query)) {
        echo "<p>✅ Referral code updated successfully!</p>";
        $user['referral_code'] = $referral_code;
    } else {
        echo "<p>❌ Failed to update referral code: " . $conn->error . "</p>";
    }
}

// Show referral link
if (!empty($user['referral_code'])) {
    $referral_link = "http://" . $_SERVER['HTTP_HOST'] . "/fitsuup/register.php?ref=" . $user['referral_code'];
    echo "<h3>🔗 Your Referral Link:</h3>";
    echo "<div style='background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<input type='text' value='$referral_link' style='width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;' readonly>";
    echo "<br><br>";
    echo "<button onclick='copyToClipboard()' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Copy Link</button>";
    echo "</div>";
}

// Get referral statistics
$ref_query = "SELECT COUNT(*) as total_referrals FROM users WHERE referred_by = $user_id";
$ref_result = $conn->query($ref_query);
$ref_data = $ref_result ? $ref_result->fetch_assoc() : ['total_referrals' => 0];

echo "<h3>📊 Referral Statistics:</h3>";
echo "<p><strong>Total Referrals:</strong> " . $ref_data['total_referrals'] . "</p>";

// Show referred users
if ($ref_data['total_referrals'] > 0) {
    echo "<h4>👥 Referred Users:</h4>";
    $referred_query = "SELECT full_name, email, created_at FROM users WHERE referred_by = $user_id ORDER BY created_at DESC";
    $referred_result = $conn->query($referred_query);
    
    if ($referred_result && $referred_result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Name</th><th>Email</th><th>Joined Date</th></tr>";
        
        while ($referred_user = $referred_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($referred_user['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($referred_user['email']) . "</td>";
            echo "<td>" . date('M j, Y', strtotime($referred_user['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Test referral registration
echo "<h3>🧪 Test Referral Registration:</h3>";
echo "<p>To test the referral system:</p>";
echo "<ol>";
echo "<li>Copy your referral link above</li>";
echo "<li>Open it in incognito/private window</li>";
echo "<li>Register a new account</li>";
echo "<li>Come back here and refresh to see the new referral</li>";
echo "</ol>";

echo "<br><p><a href='account/referral.php'>Go to Referral Page</a> | <a href='account/profile.php'>Go to Profile</a></p>";

$conn->close();
?>

<script>
function copyToClipboard() {
    const input = document.querySelector('input[readonly]');
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>