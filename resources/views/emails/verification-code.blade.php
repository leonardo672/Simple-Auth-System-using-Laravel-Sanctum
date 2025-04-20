<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
            padding: 30px;
        }
        .code {
            font-size: 28px;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: 4px;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            color: #555;
            text-align: center;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            color: #111827;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="title">Your Email Verification Code</div>

        <p>Hello,</p>

        <p>Thank you for registering. Please use the following code to verify your email address:</p>

        <div class="code">
            {{ $code }}
        </div>

        <p>This code will expire in 10 minutes. If you didn’t request this, please ignore this email.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Homam-S. All rights reserved.
        </div>
    </div>
</body>
</html>
