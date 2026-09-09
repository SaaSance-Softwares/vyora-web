<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>Reset Your Admin Password</h2>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>
        <a href="{{ url(env('ADMIN_PATH', 'occ') . '/reset-password/' . $token . '?email=' . urlencode($email)) }}" style="display: inline-block; padding: 10px 20px; background-color: #000; color: #fff; text-decoration: none; border-radius: 5px;">
            Reset Password
        </a>
    </p>
    <p>If you did not request a password reset, no further action is required.</p>
</body>
</html>
