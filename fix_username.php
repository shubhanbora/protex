<?php
// Fix existing users with NULL usernames
require_once 'config/database.php';

$conn = getDBConnection();

echo "<h1>Fixing Existing Users</h1>";

// Get users with NULL usernames
$users_query = "SELECT id, full_name, email FROM users WHERE username IS NULL";
$users_result = mysqli_query($conn, $users_query);

if (mysqli_num_rows($users_result) > 0) {
    while ($user = mysqli_fetch_assoc($users_result)) {
        $username_base = strtolower(str_replace(' ', '', $user['full_name'])); // Remove spaces and make lowercase
        $username = $username_base;
        
        // Check if username already exists, if yes add number
        $counter = 1;
        while (true) {
            $check_username = "SELECT id FROM users WHERE username = '$username'";
            $username_result = mysqli_query($conn, $check_username);
            
            if (mysqli_num_rows($username_result) == 0) {
                break; // Username is unique
            }
            
            $username = $username_base . $counter;
            $counter++;
        }
        
        // Update user with new username
        $update_query = "UPDATE users SET username = '$username' WHERE id = " . $user['id'];
        if (mysqli_query($conn, $update_query)) {
            echo "✅ Updated user ID {$user['id']}: {$user['full_name']} → username: $username<br>";
        } else {
            echo "❌ Failed to update user ID {$user['id']}: " . mysqli_error($conn) . "<br>";
        }
    }
} else {
    echo "✅ No users with NULL usernames found.<br>";
}

echo "<br><strong>Fix completed! Now try registration again.</strong><br>";
echo "<a href='register.php'>← Back to Register</a>";

closeDBConnection($conn);
?>