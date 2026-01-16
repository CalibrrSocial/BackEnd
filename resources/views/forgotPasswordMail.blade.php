<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Calibrr</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; padding: 40px 20px;">
        <tr>
            <td style="background-color: #ffffff; border-radius: 12px; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="text-align: center; padding-bottom: 30px;">
                            <h1 style="color: #333333; font-size: 24px; margin: 0;">Calibrr</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 20px;">
                            <h2 style="color: #333333; font-size: 20px; margin: 0 0 10px 0;">Password Reset</h2>
                            <p style="color: #666666; font-size: 16px; line-height: 1.5; margin: 0;">
                                We received a request to reset your password. Your new temporary password is:
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px 0;">
                            <div style="background-color: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; padding: 20px; text-align: center;">
                                <span style="font-family: 'Courier New', monospace; font-size: 28px; font-weight: bold; color: #333333; letter-spacing: 2px;">
                                    {{ $my_password }}
                                </span>
                            </div>
                            <p style="color: #999999; font-size: 12px; text-align: center; margin-top: 10px;">
                                (Password is case-sensitive)
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top: 20px; border-top: 1px solid #eeeeee;">
                            <p style="color: #666666; font-size: 14px; line-height: 1.5; margin: 0;">
                                Please log in with this temporary password, then change it to something you'll remember in your account settings.
                            </p>
                            <p style="color: #999999; font-size: 12px; line-height: 1.5; margin: 20px 0 0 0;">
                                If you didn't request this password reset, please contact us immediately at contact@calibrr.com
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="text-align: center; padding: 20px;">
                <p style="color: #999999; font-size: 12px; margin: 0;">
                    © {{ date('Y') }} Calibrr Social. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
