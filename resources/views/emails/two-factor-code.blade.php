<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รหัส OTP - Prime Forecast V2</title>
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
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            font-size: 15px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .otp-container {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            border: 2px dashed #d32f2f;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: bold;
            color: #d32f2f;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .otp-expiry {
            font-size: 13px;
            color: #999;
            margin-top: 10px;
        }
        .warning-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 5px;
        }
        .warning-box p {
            margin: 0;
            font-size: 14px;
            color: #856404;
            line-height: 1.5;
        }
        .warning-box strong {
            color: #d32f2f;
        }
        .security-tips {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .security-tips h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
        }
        .security-tips ul {
            margin: 0;
            padding-left: 20px;
        }
        .security-tips li {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .email-footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #999;
        }
        .email-footer a {
            color: #d32f2f;
            text-decoration: none;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .logo .prime {
            color: #ffffff;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="icon">🔐</div>
            <div class="logo">
                <span class="prime">Prime</span> Forecast V2
            </div>
            <p>ระบบยืนยันตัวตนแบบ 2 ขั้นตอน</p>
        </div>
        
        <div class="email-body">
            <div class="greeting">
                สวัสดี {{ $user->nname }} {{ $user->surename }},
            </div>
            
            <div class="message">
                <p>คุณได้ทำการเข้าสู่ระบบ <strong>Prime Forecast V2</strong> เนื่องจากคุณเปิดใช้งานการยืนยันตัวตนแบบ 2 ขั้นตอน (2FA) กรุณาใช้รหัส OTP ด้านล่างเพื่อยืนยันตัวตนของคุณ</p>
            </div>
            
            <div class="otp-container">
                <div class="otp-label">รหัส OTP ของคุณคือ</div>
                <div class="otp-code">{{ $code }}</div>
                <div class="otp-expiry">⏱️ รหัสนี้จะหมดอายุใน 5 นาที</div>
            </div>
            
            <div class="warning-box">
                <p><strong>⚠️ คำเตือนด้านความปลอดภัย:</strong></p>
                <p>• ห้ามแชร์รหัส OTP นี้กับผู้อื่นโดยเด็ดขาด</p>
                <p>• ทีมงาน Prime Forecast จะไม่มีทางขอรหัส OTP จากคุณ</p>
                <p>• หากคุณไม่ได้พยายามเข้าสู่ระบบ กรุณาเปลี่ยนรหัสผ่านของคุณทันที</p>
            </div>
            
            <div class="security-tips">
                <h3>💡 เคล็ดลับด้านความปลอดภัย</h3>
                <ul>
                    <li>ตรวจสอบ URL ของเว็บไซต์ให้แน่ใจว่าเป็นเว็บไซต์ที่ถูกต้อง</li>
                    <li>อย่าเปิดลิงก์ที่น่าสงสัยจากอีเมลหรือข้อความ</li>
                    <li>ใช้รหัสผ่านที่แข็งแรงและไม่ซ้ำกับบริการอื่น</li>
                    <li>เปลี่ยนรหัสผ่านเป็นประจำอย่างน้อยทุก 3 เดือน</li>
                </ul>
            </div>
            
            <div class="message">
                <p>หากคุณมีคำถามหรือต้องการความช่วยเหลือ กรุณาติดต่อทีมงานของเรา</p>
            </div>
        </div>
        
        <div class="email-footer">
            <p><strong>Prime Forecast V2</strong></p>
            <p>ระบบจัดการ Sales Forecast</p>
            <p style="margin-top: 15px;">
                อีเมลนี้ถูกส่งอัตโนมัติ กรุณาอย่าตอบกลับ
            </p>
            <p>© {{ date('Y') }} Prime Forecast. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
