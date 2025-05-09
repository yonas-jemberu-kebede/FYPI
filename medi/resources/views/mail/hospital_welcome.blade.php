<!-- resources/views/emails/hospital_welcome.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Platform</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset default styles for email clients */
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: 'Helvetica Neue', Arial, sans-serif;
            color: #333333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #2c3e50;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            font-weight: 500;
        }
        .content {
            padding: 30px;
        }
        .content p {
            font-size: 16px;
            margin: 0 0 15px;
        }
        .credentials {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .credentials ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .credentials li {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .credentials li strong {
            color: #2c3e50;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #ecf0f1;
            font-size: 14px;
            color: #7f8c8d;
            border-top: 1px solid #dfe6e9;
        }
        .footer p {
            margin: 0;
        }
        /* Responsive design */
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 10px;
            }
            .header h1 {
                font-size: 20px;
            }
            .content {
                padding: 20px;
            }
            .button {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, {{ $hospital_name }}!</h1>
        </div>
        <div class="content">
            <p>Your hospital account has been successfully created on our platform.</p>
            <p>Below are your login credentials:</p>
            <div class="credentials">
                <ul>
                    <li><strong>Email:</strong> {{ $hospital_email }}</li>
                    <li><strong>Password:</strong> {{ $password }}</li>
                </ul>
            </div>
            <p>Please log in to your account and change your password for security.</p>
            {{-- <a href="{{ url('/login') }}" class="button">Log In Here</a> --}}
            <p>Thank you for joining our platform!</p>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Your Healthcare Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
