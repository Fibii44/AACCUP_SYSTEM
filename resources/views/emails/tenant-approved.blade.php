<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Department Registration Has Been Approved</title>
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
            background-color: #3b82f6;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9fafb;
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .credentials {
            background-color: #e5e7eb;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .credentials p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Congratulations!</h1>
            <p>Your department registration has been approved</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $tenantRequest->department_name }},</h2>
            
            <p>We're pleased to inform you that your department registration for the Accreditation Management System has been approved. Your department portal is now active and ready to use.</p>
            
            <p>You can access your department portal using the following credentials:</p>
            
            <div class="credentials">
                <p><strong>Portal URL:</strong> <a href="http://{{ $tenantRequest->domain }}.{{ config('app.domain') }}">{{ $tenantRequest->domain }}.{{ config('app.domain') }}</a></p>
                <p><strong>Email:</strong> {{ $tenantRequest->email }}</p>
                <p><strong>Password:</strong> {{ $tenantRequest->password ?? 'Contact administrator for password' }}</p>
            </div>
            
            <p><strong>Important:</strong> Please change your password after your first login for security purposes.</p>
            
            <p>Your portal is now set up with default settings. As the administrator, you can customize the appearance, manage users, and configure various aspects of your department's accreditation process.</p>
            
            <a href="http://{{ $tenantRequest->domain }}.{{ config('app.domain') }}" class="button">Visit Your Portal Now</a>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 