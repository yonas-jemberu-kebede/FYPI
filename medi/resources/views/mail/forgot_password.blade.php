<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <style>
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: #f5f7fa;
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }
    .container {
      max-width: 640px;
      margin: 24px auto;
      background: linear-gradient(145deg, #ffffff, #f0f2f5);
      padding: 32px;
      border-radius: 16px;
      box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .header {
      text-align: center;
      padding-bottom: 24px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    .header h1 {
      color: #1a1a1a;
      font-size: 28px;
      font-weight: 700;
      margin: 0;
      letter-spacing: -0.025em;
    }
    .content {
      color: #2d3748;
      font-size: 16px;
      line-height: 1.7;
      padding: 24px 0;
    }
    .content p {
      margin: 0 0 16px;
    }
    .otp-box {
      background: linear-gradient(145deg, #e2e8f0, #edf2f7);
      border-radius: 12px;
      padding: 16px;
      font-size: 18px;
      font-weight: 600;
      text-align: center;
      margin: 24px 0;
      color: #1a202c;
      box-shadow: inset 4px 4px 8px rgba(0, 0, 0, 0.05),inset -4px -4px 8px rgba(255, 255, 255, 0.8);
      transition: all 0.3s ease;
      letter-spacing: 2px;
    }
    .content a {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s ease;
    }
    .content a:hover {
      color: #2563eb;
      text-decoration: underline;
    }
    .footer {
      text-align: center;
      font-size: 14px;
      color: #6b7280;
      margin-top: 24px;
      padding-top: 24px;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
    .footer a {
      color: #3b82f6;
      text-decoration: none;
      font-weight: 500;
      margin: 0 8px;
      transition: color 0.2s ease;
    }
    .footer a:hover {
      color: #2563eb;
      text-decoration: underline;
    }
    @media (max-width: 600px) {
      .container {
        margin: 16px;
        padding: 24px;
        border-radius: 12px;
      }
      .header h1 {
        font-size: 24px;
      }
      .content {
        font-size: 15px;
      }
      .otp-box {
        font-size: 16px;
        padding: 12px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1>Password Recovery</h1>
    </div>
    <div class="content">
      <p>Hello,</p>
      <p>We received a request to recover the password for your account associated with {{ $user_email }}. Below is your one-time password (OTP) to reset your password:</p>
      <div class="otp-box">
        {{ $otp }}
      </div>
      <p>Please use this OTP to reset your password. For security reasons, this OTP is valid for a limited time. If you need further assistance, contact our support team at <a href="mailto:support@yourcompany.com">mediConnect@gmail.com</a>.</p>
      <p>Thank you,<br>The MediConnect Integrated Healthcare Solution Team</p>
    </div>
    <div class="footer">
      <p>© 2025 MediConnect Integrated Healthcare Solution. All rights reserved.</p>
    </div>
  </div>
</body>
</html>
