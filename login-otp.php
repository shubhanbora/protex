<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/email.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$step = isset($_POST['step']) ? $_POST['step'] : 'email';
$error = '';
$success = '';
$email = '';

$conn = getDBConnection();

// Step 1: Send OTP to Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'send_otp') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    
    if (!isValidEmail($email)) {
        $error = 'Invalid email address. Please enter a valid email.';
    } else {
        // Generate OTP
        $otp = generateEmailOTP();
        
        // Fix timezone and expiry calculation
        $current_time = date('Y-m-d H:i:s');
        $expires_at = date('Y-m-d H:i:s', time() + (5 * 60)); // 5 minutes from now
        
        // Delete old OTPs for this email
        mysqli_query($conn, "DELETE FROM otp_verifications WHERE phone = '$email' AND expires_at < NOW()");
        
        // Save OTP to database (using phone field to store email for compatibility)
        $query = "INSERT INTO otp_verifications (phone, otp, purpose, created_at, expires_at) 
                  VALUES ('$email', '$otp', 'email_login', '$current_time', '$expires_at')";
        
        if (mysqli_query($conn, $query)) {
            // Send OTP via Email
            $email_result = sendEmailOTP($email, $otp);
            
            if ($email_result['success']) {
                $success = 'OTP sent successfully to ' . $email;
                $error = ''; // Clear any previous error message
                $step = 'verify_otp';
                
                // Show OTP on screen if email service is unavailable (development mode)
                if (isset($email_result['dev_otp'])) {
                    $success .= '<br><div style="background: #fff3cd; padding: 15px; margin-top: 15px; border-radius: 8px; border: 1px solid #ffeaa7;">';
                    $success .= '<strong>📧 Email Service Unavailable</strong><br>';
                    $success .= '<strong>🔑 Your OTP:</strong> <span style="background: #333; color: #fff; padding: 8px 15px; border-radius: 5px; font-family: monospace; font-size: 18px; letter-spacing: 2px;">' . $email_result['dev_otp'] . '</span><br>';
                    $success .= '<small>Use this OTP to login (Development Mode)</small>';
                    $success .= '</div>';
                }
            } else {
                $error = 'Failed to send OTP email. Please try again.';
            }
        } else {
            $error = 'Database error. Please try again.';
        }
    }
}

// Step 2: Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'verify_otp') {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $entered_otp = $_POST['otp'];
    
    // Simple OTP check using PHP time comparison
    $query = "SELECT * FROM otp_verifications 
              WHERE phone = '$email' 
              AND otp = '$entered_otp' 
              AND purpose = 'email_login'
              AND is_verified = 0 
              ORDER BY created_at DESC 
              LIMIT 1";
    
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $otp_record = mysqli_fetch_assoc($result);
        
        // Check if OTP is expired using PHP
        $expires_timestamp = strtotime($otp_record['expires_at']);
        $current_timestamp = time();
        
        if ($expires_timestamp > $current_timestamp) {
            // OTP is valid
            mysqli_query($conn, "UPDATE otp_verifications SET is_verified = 1, verified_at = NOW() WHERE id = " . $otp_record['id']);
            
            // Check if user exists
            $user_query = "SELECT * FROM users WHERE email = '$email'";
            $user_result = mysqli_query($conn, $user_query);
            
            if ($user_result && mysqli_num_rows($user_result) > 0) {
                // User exists - Login
                $user = mysqli_fetch_assoc($user_result);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_phone'] = $user['phone'];
                
                // Update last login
                mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);
                
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                header('Location: ' . $redirect);
                exit();
            } else {
                // New user - Redirect to complete registration
                $_SESSION['verified_email'] = $email;
                header('Location: register.php?email=' . urlencode($email));
                exit();
            }
        } else {
            $error = 'OTP has expired. Please request a new one.';
            $success = ''; // Clear any previous success message
            $step = 'email';
        }
    } else {
        $error = 'Invalid OTP. Please try again.';
        $success = ''; // Clear any previous success message
        $step = 'verify_otp';
    }
}

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with OTP - FitSupps</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .otp-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 60px;
            margin-bottom: 10px;
        }

        .logo h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 5px;
        }

        .logo p {
            color: #666;
            font-size: 0.95rem;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }

        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .otp-input {
            text-align: center;
            font-size: 1.5rem;
            letter-spacing: 10px;
            font-weight: bold;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .resend-timer {
            text-align: center;
            margin-top: 15px;
            color: #666;
        }

        .resend-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }

        .resend-link:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .info-text {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <div class="logo">
            <div class="logo-icon">💪</div>
            <h1>FitSupps</h1>
            <p>Login with OTP</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 'email'): ?>
            <!-- Step 1: Enter Email Address -->
            <form method="POST" action="">
                <input type="hidden" name="step" value="send_otp">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control" 
                            placeholder="Enter your email address"
                            required
                            autofocus
                        >
                    </div>
                    <p class="info-text">We'll send you a 6-digit OTP via email</p>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-paper-plane"></i> Send OTP
                </button>
            </form>

        <?php elseif ($step === 'verify_otp'): ?>
            <!-- Step 2: Verify OTP -->
            <form method="POST" action="">
                <input type="hidden" name="step" value="verify_otp">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input 
                            type="text" 
                            id="otp" 
                            name="otp" 
                            class="form-control otp-input" 
                            placeholder="000000"
                            pattern="[0-9]{6}"
                            maxlength="6"
                            required
                            autofocus
                        >
                    </div>
                    <p class="info-text">OTP sent to <?php echo htmlspecialchars($email); ?></p>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-check"></i> Verify & Login
                </button>

                <div class="resend-timer">
                    <span id="timer">Resend OTP in <strong>60</strong>s</span>
                    <a href="#" class="resend-link" id="resend-link" style="display: none;" onclick="resendOTP()">
                        <i class="fas fa-redo"></i> Resend OTP
                    </a>
                </div>
            </form>
        <?php endif; ?>

        <div class="back-link">
            <a href="login.php">
                <i class="fas fa-arrow-left"></i> Back to Password Login
            </a>
        </div>
    </div>

    <script>
        // OTP input auto-format
        const otpInput = document.getElementById('otp');
        if (otpInput) {
            otpInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Resend OTP timer
        <?php if ($step === 'verify_otp'): ?>
        let timeLeft = 60;
        const timerElement = document.getElementById('timer');
        const resendLink = document.getElementById('resend-link');

        const countdown = setInterval(function() {
            timeLeft--;
            timerElement.innerHTML = 'Resend OTP in <strong>' + timeLeft + '</strong>s';
            
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerElement.style.display = 'none';
                resendLink.style.display = 'inline';
            }
        }, 1000);

        function resendOTP() {
            window.location.href = 'login-email-otp.php';
        }
        <?php endif; ?>
    </script>
</body>
</html>