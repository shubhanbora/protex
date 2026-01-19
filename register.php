<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pageTitle = 'Register - FitSupps';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/security.php';
require_once 'config/email.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        $conn = getDBConnection();
        
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validation
        if (empty($name)) {
            $error = 'Full name is required';
        } elseif (!isValidEmail($email)) {
            $error = 'Please enter a valid email address';
        } elseif (!isValidPhone($phone)) {
            $error = 'Please enter a valid 10-digit phone number';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } else {
            // Check if email or phone already exists
            $check_query = "SELECT id FROM users WHERE email = ? OR phone = ?";
            $check_result = executeQuery($conn, $check_query, "ss", [$email, $phone]);
            
            if ($check_result && $check_result->num_rows > 0) {
                $error = 'Email or phone already registered. Please login.';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate referral code
                $referral_code = strtoupper(substr($name, 0, 3) . rand(1000, 9999));
                
                // Check if referred by someone
                $referred_by = null;
                if (isset($_GET['ref']) && !empty($_GET['ref'])) {
                    $ref_code = $conn->real_escape_string($_GET['ref']);
                    $ref_query = "SELECT id FROM users WHERE referral_code = '$ref_code' LIMIT 1";
                    $ref_result = $conn->query($ref_query);
                    if ($ref_result && $ref_result->num_rows > 0) {
                        $referrer = $ref_result->fetch_assoc();
                        $referred_by = $referrer['id'];
                    }
                }
                
                if ($referred_by) {
                    $insert_query = "INSERT INTO users (full_name, email, phone, password, referral_code, referred_by) VALUES ('$name', '$email', '$phone', '$hashed_password', '$referral_code', $referred_by)";
                } else {
                    $insert_query = "INSERT INTO users (full_name, email, phone, password, referral_code) VALUES ('$name', '$email', '$phone', '$hashed_password', '$referral_code')";
                }
                
                $insert_result = $conn->query($insert_query);
                
                if ($insert_result) {
                    $user_id = $conn->insert_id;
                    
                    // Send welcome email
                    sendWelcomeEmail($email, $name);
                    
                    // Auto login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['login_time'] = time();
                    
                    header('Location: index.php');
                    exit();
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
        
        closeDBConnection($conn);
    }
}

require_once 'includes/header.php';
?>

<style>
.register-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.register-card {
    background: white;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 500px;
    border: 1px solid #f1f5f9;
}

.register-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.register-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.register-subtitle {
    color: #64748b;
    font-size: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f9fafb;
    box-sizing: border-box;
}

.form-input:focus {
    outline: none;
    border-color: #007bff;
    background: white;
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
}

.password-field {
    position: relative;
}

.password-input {
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    font-size: 1.1rem;
    padding: 0.5rem;
    border-radius: 6px;
    transition: all 0.3s ease;
    min-width: 44px;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: #007bff;
    background: rgba(0, 123, 255, 0.1);
}

.register-btn {
    width: 100%;
    background: #000000;
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.register-btn:hover {
    background: #1a1a1a;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.register-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #f1f5f9;
}

.register-footer a {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.register-footer a:hover {
    text-decoration: underline;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .register-container {
        padding: 1rem;
        min-height: 70vh;
    }
    
    .register-card {
        padding: 2rem 1.5rem;
        border-radius: 16px;
    }
    
    .register-title {
        font-size: 1.75rem;
    }
}
</style>

<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <h1 class="register-title">Join FitSupps</h1>
            <p class="register-subtitle">Create your account to start your fitness journey</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name" class="form-input" required 
                       placeholder="Enter your full name">
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" required 
                       placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" id="phone" name="phone" class="form-input" required 
                       placeholder="Enter your phone number">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" class="form-input password-input" required minlength="6"
                           placeholder="Create a password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye" id="passwordIcon"></i>
                    </button>
                </div>
                <small style="color: #666; font-size: 0.85rem;">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="password-field">
                    <input type="password" id="confirm_password" name="confirm_password" class="form-input password-input" required minlength="6"
                           placeholder="Confirm your password">
                    <button type="button" class="password-toggle" id="toggleConfirmPassword">
                        <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="register-footer">
            <p>Already have an account? <a href="login.php">Sign in here</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Simple and reliable password toggle
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle
    const passwordToggle = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    
    if (passwordToggle && passwordField && passwordIcon) {
        passwordToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        });
    }
    
    // Confirm password toggle
    const confirmToggle = document.getElementById('toggleConfirmPassword');
    const confirmField = document.getElementById('confirm_password');
    const confirmIcon = document.getElementById('confirmPasswordIcon');
    
    if (confirmToggle && confirmField && confirmIcon) {
        confirmToggle.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirmField.type === 'password') {
                confirmField.type = 'text';
                confirmIcon.className = 'fas fa-eye-slash';
            } else {
                confirmField.type = 'password';
                confirmIcon.className = 'fas fa-eye';
            }
        });
    }
});
</script>