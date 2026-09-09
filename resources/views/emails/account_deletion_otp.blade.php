<!DOCTYPE html>
<html>
<head>
    <title>Account Deletion OTP</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Account Deletion Request</h2>
    <p>You have requested to delete your account. To proceed, please use the following One-Time Password (OTP):</p>
    
    <div style="background-color: #f4f4f4; padding: 15px; margin: 20px 0; text-align: center; font-size: 24px; font-weight: bold; letter-spacing: 5px;">
        {{ $otp }}
    </div>
    
    <p>This OTP is valid for 10 minutes. If you did not request to delete your account, please ignore this email and your account will remain secure.</p>
    
    <p>Thank you,<br>The Team</p>
</body>
</html>
