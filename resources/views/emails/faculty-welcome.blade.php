<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(310deg, #7928CA, #FF0080);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .credentials {
            background-color: #fff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .credentials p {
            margin: 5px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(310deg, #7928CA, #FF0080);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        .warning {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to AACCUP</h2>
        </div>
        <div class="content">
            <p>Dear {{ $user->name }},</p>
            
            <p>Your account has been successfully created in the AACCUP system. Below are your login credentials:</p>
            
            <div class="credentials">
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
            </div>

            <p class="warning">Important: For security purposes, please change your password immediately after your first login.</p>

            <p>To get started:</p>
            <ol>
                <li>Click the login button below</li>
                <li>Use your email and the provided password to sign in</li>
                <li>Go to your profile settings</li>
                <li>Change your password to something secure that you'll remember</li>
            </ol>

            <center>
                <a href="{{ route('login') }}" class="button">Login to Your Account</a>
            </center>

            <p>If you have any questions or need assistance, please don't hesitate to contact the administrator.</p>

            <p>Best regards,<br>AACCUP Team</p>
        </div>
        <div class="footer">
            <p>This is an automated message, please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} AACCUP. All rights reserved.</p>
        </div>
    </div>
</body>
</html>