# 📱 OTP Authentication Setup Guide

## ✅ What Was Implemented

Complete OTP-based authentication system using Fast2SMS!

---

## 🎯 Features

### 1. OTP Login
- User enters phone number
- Receives 6-digit OTP via SMS
- Verifies OTP to login
- No password needed!

### 2. Auto Registration
- If phone number doesn't exist
- User is redirected to complete registration
- Phone already verified

### 3. Security
- OTP expires in 5 minutes
- One-time use only
- Attempt tracking
- Old OTPs auto-deleted

---

## 📁 Files Created

1. `config/sms.php` - Fast2SMS integration
2. `login-otp.php` - OTP login page
3. `database/create_otp_table.sql` - Database schema

### Files Modified
1. `login.php` - Added "Login with OTP" button

---

## 🗄️ Database Setup

### Step 1: Run SQL
Open phpMyAdmin and run this SQL:

```sql
-- Create OTP table
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    purpose ENUM('login', 'register', 'verify') DEFAULT 'login',
    is_verified TINYINT(1) DEFAULT 0,
    attempts INT DEFAULT 0,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at DATETIME NULL,
    INDEX idx_phone (phone),
    INDEX idx_otp (otp),
    INDEX idx_expires (expires_at)
);

-- Add columns to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS phone_verified TINYINT(1) DEFAULT 0 AFTER phone;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME NULL AFTER phone_verified;
```

OR simply run the file:
```
database/create_otp_table.sql
```

---

## 🧪 How to Test

### Test 1: OTP Login Flow
1. Go to: `http://localhost/client/login.php`
2. Click "Login with OTP" button
3. Enter your 10-digit phone number
4. Click "Send OTP"
5. Check your phone for SMS
6. Enter the 6-digit OTP
7. Click "Verify & Login"
8. ✅ You're logged in!

### Test 2: New User Registration
1. Use OTP login with new phone number
2. After OTP verification
3. Redirected to registration page
4. Phone already filled and verified
5. Complete other details
6. ✅ Account created!

### Test 3: Existing User Login
1. Use OTP login with existing phone
2. After OTP verification
3. Directly logged in
4. ✅ No registration needed!

---

## 🔧 Configuration

### Fast2SMS Settings
File: `config/sms.php`

```php
define('FAST2SMS_API_KEY', 'YOUR_API_KEY_HERE');
define('FAST2SMS_SENDER_ID', 'FITSUP');
```

### OTP Settings
- **OTP Length:** 6 digits
- **Expiry Time:** 5 minutes
- **Resend Timer:** 60 seconds
- **Max Attempts:** Unlimited (can be limited)

---

## 📱 SMS Template

The SMS sent to users:
```
Your FitSupps OTP is: 123456. Valid for 5 minutes. Do not share with anyone.
```

---

## 🎨 UI Features

### OTP Login Page
- ✅ Beautiful gradient background
- ✅ Animated card entrance
- ✅ Icon-based inputs
- ✅ Auto-formatted phone input
- ✅ Large OTP input field
- ✅ Resend timer (60 seconds)
- ✅ Error/Success messages
- ✅ Responsive design

### Login Page
- ✅ Added "Login with OTP" button
- ✅ "OR" separator
- ✅ Green button for OTP option

---

## 🔐 Security Features

### 1. OTP Expiry
- OTP valid for only 5 minutes
- Auto-deleted after expiry

### 2. One-Time Use
- OTP marked as verified after use
- Cannot be reused

### 3. Attempt Tracking
- Failed attempts are logged
- Can implement max attempts limit

### 4. Phone Validation
- Only Indian mobile numbers (10 digits)
- Starting with 6, 7, 8, or 9

### 5. SQL Injection Protection
- All inputs sanitized
- Prepared statements used

---

## 🐛 Troubleshooting

### Issue 1: OTP Not Received
**Possible Reasons:**
- Fast2SMS API key incorrect
- Insufficient balance in Fast2SMS account
- Phone number format wrong
- Network delay

**Solution:**
1. Check Fast2SMS dashboard
2. Verify API key in `config/sms.php`
3. Check account balance
4. Wait 1-2 minutes for SMS

### Issue 2: "Invalid or Expired OTP"
**Reasons:**
- OTP expired (5 minutes)
- Wrong OTP entered
- OTP already used

**Solution:**
1. Click "Resend OTP"
2. Enter new OTP within 5 minutes
3. Check SMS for correct OTP

### Issue 3: Database Error
**Reason:**
- OTP table not created

**Solution:**
1. Run `database/create_otp_table.sql`
2. Check database connection
3. Verify table exists

---

## 💡 Advanced Features (Optional)

### 1. Rate Limiting
Limit OTP requests per phone:
```php
// In login-otp.php
$recent_otps = mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM otp_verifications 
     WHERE phone = '$phone' 
     AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)"
);
if ($recent_otps['count'] > 3) {
    $error = 'Too many OTP requests. Try after 1 hour.';
}
```

### 2. Max Attempts
Limit verification attempts:
```php
// In verify_otp step
if ($otp_record['attempts'] >= 3) {
    $error = 'Maximum attempts exceeded. Request new OTP.';
}
```

### 3. Blacklist
Block spam numbers:
```php
$blacklist = ['1234567890', '9876543210'];
if (in_array($phone, $blacklist)) {
    $error = 'This number is blocked.';
}
```

---

## 📊 Fast2SMS Dashboard

### Check SMS Status
1. Login to Fast2SMS
2. Go to "Reports" section
3. Check delivery status
4. View SMS logs

### Check Balance
1. Dashboard → Balance
2. Recharge if needed
3. Each SMS costs ~₹0.15-0.25

---

## ✅ Testing Checklist

- [ ] Database table created
- [ ] API key configured
- [ ] OTP login page accessible
- [ ] Phone number validation works
- [ ] OTP SMS received
- [ ] OTP verification works
- [ ] Existing user can login
- [ ] New user redirected to register
- [ ] Resend OTP works
- [ ] Timer countdown works
- [ ] Error messages display
- [ ] Success messages display

---

## 🚀 Go Live Checklist

Before production:

1. **Test with real numbers**
   - Test with 5-10 different numbers
   - Verify SMS delivery

2. **Check Fast2SMS balance**
   - Ensure sufficient credits
   - Set up auto-recharge

3. **Enable rate limiting**
   - Prevent spam
   - Limit OTP requests

4. **Monitor logs**
   - Track failed attempts
   - Check delivery rates

5. **Backup database**
   - Regular backups
   - Test restore process

---

## 📞 Support

### Fast2SMS Support
- Website: https://www.fast2sms.com
- Support: support@fast2sms.com
- Docs: https://docs.fast2sms.com

### Common Issues
- API errors → Check API key
- Balance issues → Recharge account
- Delivery issues → Check number format

---

**OTP Authentication is ready! Test it now!** 📱🔐
