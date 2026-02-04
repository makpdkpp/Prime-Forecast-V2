<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }
        .header {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            background-color: white;
            padding: 30px;
            margin-top: 20px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #0056b3;
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
        .info-box {
            background-color: #f0f8ff;
            border-left: 4px solid #0056b3;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PrimeForecast</h1>
            <p>ระบบจัดการ Sales Forecast</p>
        </div>
        
        <div class="content">
            <h2>สวัสดีคุณ {{ $user->nname }} {{ $user->surename }}</h2>
            
            <p>คุณได้รับเชิญให้เข้าใช้งานระบบ PrimeForecast</p>
            
            <div class="info-box">
                <strong>ข้อมูลบัญชีของคุณ:</strong><br>
                Email: <strong>{{ $user->email }}</strong><br>
                ชื่อ-นามสกุล: <strong>{{ $user->nname }} {{ $user->surename }}</strong>
            </div>
            
            <p>กรุณากดปุ่มด้านล่างเพื่อตั้งรหัสผ่านและเริ่มใช้งานระบบ:</p>
            
            <div style="text-align: center;">
                <a href="{{ $invitationUrl }}" class="button">ตั้งรหัสผ่านและเข้าใช้งาน</a>
            </div>
            
            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                หรือคัดลอกลิงก์นี้ไปวางในเบราว์เซอร์:<br>
                <a href="{{ $invitationUrl }}">{{ $invitationUrl }}</a>
            </p>
            
            <p style="margin-top: 30px; font-size: 12px; color: #999;">
                <strong>หมายเหตุ:</strong> ลิงก์นี้จะหมดอายุภายใน 7 วัน
            </p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} PrimeForecast. All rights reserved.</p>
            <p>หากคุณไม่ได้ขอเข้าใช้งานระบบนี้ กรุณาเพิกเฉยต่ออีเมลนี้</p>
        </div>
    </div>
</body>
</html>
