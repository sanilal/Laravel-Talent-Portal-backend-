<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .otp-box {
            background-color: #fff;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #2563eb;
            margin: 10px 0;
        }
        .message {
            margin: 20px 0;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Talents You Need</div>
            <h2>Verify Your Email Address</h2>
        </div>

        <div class="message">
            <p>Hi {{ $user->first_name }},</p>
            <p>Thank you for registering with Talents You Need! To complete your registration, please verify your email address using the code below:</p>
        </div>

        <div class="otp-box">
            <p style="margin: 0; font-size: 14px; color: #666;">Your Verification Code</p>
            <div class="otp-code">{{ $otp }}</div>
            <p style="margin: 0; font-size: 12px; color: #666;">Valid for 15 minutes</p>
        </div>

        <div class="message">
            <p>Enter this code in the verification page to activate your account.</p>
        </div>

        <div class="warning">
            <strong>⚠️ Security Notice:</strong><br>
            If you didn't request this verification code, please ignore this email. Your account is secure.
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Talents You Need. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>