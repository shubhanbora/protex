<?php
// Debug version for addresses page
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Addresses Debug</h2>";

require_once '../config/database.php';
echo "<p>✅ Database config loaded</p>";

require_once '../config/session.php';
echo "<p>✅ Session config loaded</p>";

// Check if user is logged in
if (!isLoggedIn()) {
    echo "<p>❌ User not logged in</p>";
    echo "<a href='../login.php'>Login</a>";
    exit();
}
echo "<p>✅ User is logged in</p>";

$conn = getDBConnection();
if (!$conn) {
    echo "<p>❌ Database connection failed</p>";
    exit();
}
echo "<p>✅ Database connected</p>";

$user_id = getCurrentUserId();
echo "<p>User ID: $user_id</p>";

// Check if addresses table exists
$check_table = "SHOW TABLES LIKE 'addresses'";
$table_result = $conn->query($check_table);

if (!$table_result || $table_result->num_rows === 0) {
    echo "<p>❌ Addresses table does not exist!</p>";
    echo "<p><strong>Solution:</strong> Run the database fix script:</p>";
    echo "<a href='../fix-database.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fix Database</a>";
    exit();
}
echo "<p>✅ Addresses table exists</p>";

// Check table structure
echo "<h3>📋 Table Structure:</h3>";
$structure_query = "DESCRIBE addresses";
$structure_result = $conn->query($structure_query);

if ($structure_result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check user's addresses
echo "<h3>📍 User's Addresses:</h3>";
$query = "SELECT * FROM addresses WHERE user_id = $user_id ORDER BY is_default DESC, created_at DESC";
$result = $conn->query($query);

if (!$result) {
    echo "<p>❌ Query failed: " . $conn->error . "</p>";
} else {
    echo "<p>✅ Query executed successfully</p>";
    echo "<p>Number of addresses: " . $result->num_rows . "</p>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Mobile</th><th>City</th><th>Default</th><th>Created</th></tr>";
        
        while ($address = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $address['id'] . "</td>";
            echo "<td>" . htmlspecialchars($address['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($address['mobile']) . "</td>";
            echo "<td>" . htmlspecialchars($address['city']) . "</td>";
            echo "<td>" . ($address['is_default'] ? 'Yes' : 'No') . "</td>";
            echo "<td>" . $address['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No addresses found for this user.</p>";
        echo "<p><strong>You can add a new address using the form below:</strong></p>";
        
        // Simple add address form
        echo "<form method='POST' style='border: 1px solid #ddd; padding: 20px; margin: 20px 0;'>";
        echo "<h4>Add Test Address</h4>";
        echo "<input type='hidden' name='action' value='add'>";
        echo "<p><input type='text' name='full_name' placeholder='Full Name' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='email' name='email' placeholder='Email' style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='mobile' placeholder='Mobile' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='flat_house' placeholder='Flat/House' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='locality' placeholder='Locality' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='landmark' placeholder='Landmark' style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='pincode' placeholder='Pincode' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='city' placeholder='City' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><input type='text' name='state' placeholder='State' required style='width: 200px; padding: 5px;'></p>";
        echo "<p><label><input type='checkbox' name='is_default'> Make Default</label></p>";
        echo "<p><button type='submit' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px;'>Add Address</button></p>";
        echo "</form>";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>📝 Form Submission Result:</h3>";
    
    $action = $_POST['action'];
    echo "<p>Action: $action</p>";
    
    if ($action === 'add') {
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
        
        $query = "INSERT INTO addresses (user_id, full_name, email, mobile, flat_house, locality, landmark, pincode, city, state, is_default) 
                 VALUES ($user_id, '$full_name', '$email', '$mobile', '$flat_house', '$locality', '$landmark', '$pincode', '$city', '$state', $is_default)";
        
        echo "<p>Query: $query</p>";
        
        if ($conn->query($query)) {
            echo "<p>✅ Address added successfully!</p>";
            echo "<p><a href='addresses-debug.php'>Refresh to see new address</a></p>";
        } else {
            echo "<p>❌ Failed to add address: " . $conn->error . "</p>";
        }
    }
}

echo "<br><p><a href='addresses.php'>Go to actual addresses page</a></p>";

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>