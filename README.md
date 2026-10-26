# Prime Forecast V2

ระบบบริหารจัดการ Sales Forecast สำหรับองค์กร

## คุณสมบัติหลัก

- **3 ระดับสิทธิ์**: Admin, Team Admin, User
- **จัดการข้อมูลการขาย**: บันทึก แก้ไข โอนย้ายระหว่างทีม
- **Master Data**: บริษัท สินค้า อุตสาหกรรม ทีม และอื่นๆ
- **ความปลอดภัย**: 2FA, Rate limiting


## เทคโนโลยี

- Laravel 12 (PHP ~8.2.35 / ~8.3.36 / ~8.4.27 / ~8.5.12)
- AdminLTE 3 + Bootstrap
- MySQL/MariaDB

## การติดตั้ง

ก่อน deploy อ่าน [แพตช์ PHP2Shell และการตั้งค่า PHP บนโฮสต์](deploy/PHP2SHELL.md)

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## สิทธิ์ผู้ใช้งาน

| Role | สิทธิ์ |
|------|--------|
| Admin | จัดการทั้งหมด |
| Team Admin | จัดการทีมตัวเอง |
| User | จัดการข้อมูลตนเอง |

## License

MIT License
130326
