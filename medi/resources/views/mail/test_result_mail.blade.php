<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Result Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px; border: 2px solid #3498db; border-radius: 10px; background-color: #ffffff; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <h2 style="color: #2c3e50; text-align: center; background-color: #e6f3fa; padding: 10px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px;">Test Result Notification</h2>
        <p>Dear {{ $patient_name }},</p>
        <p>We are pleased to inform you that your test results are now available. Below are the details:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <tr>
                <td style="padding: 8px; border: 1px solid #3498db; font-weight: bold; background-color: #e6f3fa;">Patient Name:</td>
                <td style="padding: 8px; border: 1px solid #3498db;">{{ $patient_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #3498db; font-weight: bold; background-color: #e6f3fa;">Gender:</td>
                <td style="padding: 8px; border: 1px solid #3498db;">{{ $gender }}</td>
            </tr>

            <tr>
                <td style="padding: 8px; border: 1px solid #3498db; font-weight: bold; background-color: #e6f3fa;">Doctor:</td>
                <td style="padding: 8px; border: 1px solid #3498db;">{{ $doctor_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #3498db; font-weight: bold; background-color: #e6f3fa;">Hospital:</td>
                <td style="padding: 8px; border: 1px solid #3498db;">{{ $hospital_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #3498db; font-weight: bold; background-color: #e6f3fa;">Diagnostic Center:</td>
                <td style="padding: 8px; border: 1px solid #3498db;">{{ $diagnostic_center_name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #3498db; font-weight: bold; background-color: #e6f3fa;">Test Result:</td>
                <td style="padding: 8px; border: 1px solid #3498db;"> {{ implode(', ', $diagnostic_result) }}</td>


            </tr>
        </table>

        <p>Please contact your doctor or the diagnostic center for further interpretation of your results or to schedule a follow-up appointment.</p>
        <p>If you have any questions, feel free to reach out to us at <a href="mailto:support@hospital.com" style="color: #3498db; text-decoration: none;">support@hospital.com</a>.</p>

        <p style="margin-top: 20px;">Best regards,<br>
        The {{ $hospital_name }} Team</p>

        <div style="text-align: center; margin-top: 20px; padding-top: 10px; border-top: 2px solid #3498db; font-size: 12px; color: #777;">
            <p>This is an automated email, please do not reply directly. For support, contact <a href="mailto:support@hospital.com" style="color: #3498db; text-decoration: none;">support@hospital.com</a>.</p>
        </div>
    </div>
</body>
</html>
