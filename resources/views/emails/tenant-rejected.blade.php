<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Department Registration Was Not Approved</title>
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
            background-color: #4b5563;
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
        .reason {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Department Registration Status</h1>
            <p>Important information about your registration request</p>
        </div>
        
        <div class="content">
            <h2>Hello {{ $tenantRequest->department_name }},</h2>
            
            <p>Thank you for your interest in the Accreditation Management System.</p>
            
            <p>After reviewing your department registration request, we regret to inform you that it was not approved at this time.</p>
            
            <div class="reason">
                <h3>Reason for Rejection:</h3>
                <p>{{ $tenantRequest->rejection_reason }}</p>
            </div>
            
            <p>You are welcome to submit a new registration request addressing the concerns mentioned above.</p>
            
            <a href="{{ route('tenant.request') }}" class="button">Submit New Request</a>
            
            <p>If you have any questions or need further clarification, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html> 