<?php
// Test email functionality
$to = "test@example.com";
$subject = "Test Email";
$message = "This is a test email.";
$headers = "From: noreply@fitsupps.com";

echo "<h2>🧪 Email Test</h2>";

if (mail($to, $subject, $message, $headers)) {
    echo "<p style='color: green;'>✅ Email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Email failed to send.</p>";
    echo "<p><strong>Issue:</strong> Local PHP mail() function is not configured.</p>";
}

echo "<h3>📧 Email Configuration Status:</h3>";
echo "<ul>";
echo "<li><strong>sendmail_path:</strong> " . ini_get('sendmail_path') . "</li>";
echo "<li><strong>SMTP:</strong> " . ini_get('SMTP') . "</li>";
echo "<li><strong>smtp_port:</strong> " . ini_get('smtp_port') . "</li>";
echo "</ul>";

echo "<h3>💡 Solutions:</h3>";
echo "<ol>";
echo "<li><strong>Use Gmail SMTP</strong> - Configure with Gmail credentials</li>";
echo "<li><strong>Development Mode</strong> - Show OTP on screen for testing</li>";
echo "<li><strong>File-based OTP</strong> - Save OTP to file for development</li>";
echo "<li><strong>Remove OTP</strong> - Use only password login</li>";
echo "</ol>";
?>