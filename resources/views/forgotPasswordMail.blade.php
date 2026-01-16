<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Calibrr</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { font-size: 24px; margin: 0; }
        .content { margin-bottom: 30px; }
        .content h3 { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .content p { margin: 10px 0; }
        .password-box { background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .password-box .password { font-family: 'Courier New', monospace; font-size: 28px; font-weight: bold; color: #333; letter-spacing: 2px; }
        .password-box .note { font-size: 12px; color: #999; margin-top: 10px; }
        .footer { text-align: center; margin-top: 30px; }
        .app-link { color: #007bff; text-decoration: none; font-weight: bold; }
        .logo { text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Calibrr Social App</h2>
        </div>
        
        <div class="content">
            <h3>Password Reset</h3>
            <p>We received a request to reset your password. Your new temporary password is:</p>
            
            <div class="password-box">
                <span class="password">{{ $my_password }}</span>
                <p class="note">(Password is case-sensitive)</p>
            </div>
            
            <p>Please log in with this temporary password, then change it to something you'll remember in your account settings.</p>
            <p style="font-size: 12px; color: #666;">If you didn't request this password reset, please contact us at contact@calibrr.com</p>
        </div>
        
        <div class="footer">
            <p>Open Calibrr Social <a href="https://apps.apple.com/us/app/calibrr-social/id1377015871" class="app-link">HERE</a></p>
        </div>
        
        <div class="logo">
            <img src="https://calibrr-email-logo-1753077694.s3.amazonaws.com/calibrr-logo.png" alt="Calibrr Social App" style="max-width: 200px; height: auto;">
        </div>
    </div>
</body>
</html>
