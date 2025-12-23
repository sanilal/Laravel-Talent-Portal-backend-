<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            padding: 40px;
            text-align: center;
        }
        .content {
            background: white;
            border-radius: 8px;
            padding: 40px;
            margin-top: 20px;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: white;
            margin-bottom: 20px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }
        .otp-box {
            background: #f7fafc;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.8;
            margin: 20px 0;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #999;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">✨ Talents You Need</div>
        
        <div class="content">
            <div class="title">Verify Your Email Address</div>
            
            <p class="message">
                Hello {{ $user->first_name }},
            </p>
            
            <p class="message">
                Thank you for registering with Talents You Need! To complete your registration and activate your account, please use the verification code below:
            </p>
            
            <div class="otp-box">
                <div class="otp-code">{{ $otp }}</div>
            </div>
            
            <p class="message">
                This code will expire in <strong>10 minutes</strong>.
            </p>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                • Never share this code with anyone<br>
                • Our team will never ask for this code<br>
                • If you didn't request this code, please ignore this email
            </div>
            
            <p class="message">
                If you're having trouble, you can also verify your email by clicking the button below:
            </p>
            
            <a href="{{ config('app.frontend_url') }}/auth/verify-email?email={{ urlencode($user->email) }}" class="button">
                Verify Email
            </a>
            
            <div class="footer">
                <p>Need help? Contact our support team at support@talentsyouneed.com</p>
                <p>© {{ date('Y') }} Talents You Need. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>