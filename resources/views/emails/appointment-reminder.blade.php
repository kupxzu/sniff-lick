<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .appointment-details {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .appointment-details h2 {
            margin-top: 0;
            color: #667eea;
            font-size: 18px;
        }
        .detail-row {
            display: flex;
            margin: 10px 0;
        }
        .detail-label {
            font-weight: bold;
            min-width: 140px;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .clinic-info {
            margin-top: 15px;
            font-size: 13px;
        }
        .paw-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="paw-icon">🐾</div>
            <h1>{{ $clinicName }}</h1>
            <p style="margin: 5px 0 0 0; font-size: 14px;">Appointment Reminder</p>
        </div>

        <p>Dear <strong>{{ $clientName }}</strong>,</p>

        <p>This is a friendly reminder about your upcoming appointment with us today!</p>

        <div class="appointment-details">
            <h2>📅 Appointment Details</h2>
            <div class="detail-row">
                <span class="detail-label">Pet Name:</span>
                <span class="detail-value">{{ $petName }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ $appointmentDate }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span class="detail-value">{{ $appointmentTime }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Appointment Type:</span>
                <span class="detail-value">{{ $appointmentType }}</span>
            </div>
        </div>

        <p><strong>Important reminders:</strong></p>
        <ul>
            <li>Please arrive 10 minutes before your scheduled appointment</li>
            <li>Bring your pet's vaccination records if available</li>
            <li>If you need to reschedule or cancel, please contact us as soon as possible</li>
        </ul>

        <p>We look forward to seeing you and {{ $petName }} today!</p>

        <p>Best regards,<br>
        <strong>{{ $clinicName }} Team</strong></p>

        <div class="footer">
            <div class="clinic-info">
                <strong>Contact Us:</strong><br>
                📞 {{ $clinicPhone }}<br>
                📍 {{ $clinicAddress }}
            </div>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                This is an automated reminder. Please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
