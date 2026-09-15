<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Request</title>
</head>
<body>
    <h2>Reset Your Password</h2>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Please click the link below to reset your password:</p>
    <p><a href="{{ url('/reset-password?token=' . $token . '&email=' . urlencode($email)) }}">Reset Password</a></p>
    <p>If you did not request a password reset, no further action is required.</p>
</body>
</html>
