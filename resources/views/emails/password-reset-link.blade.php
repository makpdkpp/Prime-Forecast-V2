<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีเซ็ตรหัสผ่าน - Prime Forecast V2</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .email-header {
            background: linear-gradient(135deg, #d32f2f 0%, #b71c1c 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }

        .email-body {
            padding: 32px 28px;
            color: #374151;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 12px 22px;
            background-color: #d32f2f;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 0;
        }

        .muted {
            color: #6b7280;
            font-size: 13px;
        }

        .email-footer {
            padding: 18px 28px 28px;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <h2 style="margin: 0;">Prime Forecast V2</h2>
        <p style="margin: 8px 0 0;">คำขอรีเซ็ตรหัสผ่าน</p>
    </div>

    <div class="email-body">
        <p>สวัสดีคุณ {{ $user->nname }} {{ $user->surename }},</p>
        <p>เราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ กรุณากดปุ่มด้านล่างเพื่อตั้งรหัสผ่านใหม่ (ลิงก์มีอายุ 1 ชั่วโมง)</p>

        <p style="text-align: center;">
            <a href="{{ $resetUrl }}" class="btn">รีเซ็ตรหัสผ่าน</a>
        </p>

        <p class="muted">
            หากปุ่มไม่ทำงาน คุณสามารถคัดลอกลิงก์นี้ไปเปิดในเบราว์เซอร์:<br>
            <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
        </p>

        <p class="muted">หากคุณไม่ได้เป็นผู้ขอรีเซ็ตรหัสผ่าน สามารถละเว้นอีเมลนี้ได้อย่างปลอดภัย</p>
    </div>

    <div class="email-footer">
        © {{ date('Y') }} Prime Forecast V2
    </div>
</div>
</body>
</html>
