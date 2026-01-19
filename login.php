<?php
$pageTitle = 'Login - FitSupps';
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/security.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Security token mismatch. Please try again.';
    } else {
        // Rate limiting
        $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!checkRateLimit('login_' . $client_ip, 5, 900)) { // 5 attempts per 15 minutes
            $error = 'Too many login attempts. Please try again in 15 minutes.';
        } else {
            $conn = getDBConnection();
            
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            
            // Input validation
            if (!isValidEmail($email)) {
                $error = 'Please enter a valid email address';
            } elseif (empty($password)) {
                $error = 'Password is required';
            } else {
                // Use prepared statement
                $query = "SELECT id, full_name, email, password, is_active FROM users WHERE email = ? AND is_active = 1";
                $result = executeQuery($conn, $query, "s", [$email]);
                
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password'])) {
                        // Regenerate session ID for security
                        session_regenerate_id(true);
                        
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['full_name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['login_time'] = time();
                        
                        // Clear rate limiting on successful login
                        unset($_SESSION['rate_limit']['login_' . $client_ip]);
                        
                        // Redirect to intended page or homepage
                        $redirect = $_GET['redirect'] ?? 'index.php';
                        header('Location: ' . $redirect);
                        exit();
                    } else {
                        $error = 'Invalid email or password';
                    }
                } else {
                    $error = 'Invalid email or password';
                }
            }
            
            closeDBConnection($conn);
        }
    }
}

require_once 'includes/header.php';
?>

<style>
.login-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.login-card {
    background: white;
    padding: 3rem;
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 450px;
    border: 1px solid #f1f5f9;
}

.login-header {
    text-align: center;
    margin-bottom: 2.5rem;
}

.login-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 0.5rem;
}

.login-subtitle {
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

.login-btn {
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

.login-btn:hover {
    background: #1a1a1a;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.login-footer {
    text-align: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #f1f5f9;
}

.login-footer a {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.login-footer a:hover {
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

/* Mobile Responsive */
@media (max-width: 768px) {
    .login-container {
        padding: 1rem;
        min-height: 70vh;
    }
    
    .login-card {
        padding: 2rem 1.5rem;
        border-radius: 16px;
    }
    
    .login-title {
        font-size: 1.75rem;
    }
}
</style>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to your FitSupps account</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?php echo csrfField(); ?>
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" required 
                       placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" class="form-input password-input" required 
                           placeholder="Enter your password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye" id="passwordIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="login-footer">
            <p>Don't have an account? <a href="register.php">Create one here</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Simple and reliable password toggle
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>