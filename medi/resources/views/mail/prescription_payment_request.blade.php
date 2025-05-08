<!-- resources/views/emails/prescription_payment_requested.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Prescription Payment Request</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background-color: #f4f4f4;
            color: #333333;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .details {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .details strong {
            color: #2c3e50;
        }
        .total-amount {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            color: #2c3e50;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .footer {
            font-size: 14px;
            color: #777777;
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Prescription Payment Request</h1>
        <p>Dear {{ $patient_name }},</p>
        <p>Your prescription order has been successfully created. Below are the details of your prescription:</p>

        <div class="details">
            <p><strong>Prescribed by:</strong> Dr. {{ $doctor_name }}</p>
            <p><strong>Hospital:</strong> {{ $hospital_name }}</p>
        </div>

        <p class="total-amount">Total Amount: ${{ number_format($total_amount, 2) }}</p>

        <p>To complete your order, please proceed to make the payment using the link below:</p>
        <a href="{{ $checkout_url }}" class="button">Complete Payment</a>

        <p>If you have any questions, please contact our support team.</p>

        <div class="footer">
            <p>Thank you for using our platform!</p>
            <p>© {{ date('Y') }} Your Healthcare Platform. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
