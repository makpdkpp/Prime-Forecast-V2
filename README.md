# Prime Forecast V2

ระบบบริหารจัดการ Sales Forecast สำหรับองค์กร

## คุณสมบัติหลัก

- **3 ระดับสิทธิ์**: Admin, Team Admin, User
- **จัดการข้อมูลการขาย**: บันทึก แก้ไข โอนย้ายระหว่างทีม
- **Master Data**: บริษัท สินค้า อุตสาหกรรม ทีม และอื่นๆ
- **ความปลอดภัย**: 2FA, Rate limiting


## เทคโนโลยี

- Laravel 10 (PHP 8.1+)
- AdminLTE 3 + Bootstrap
- MySQL/MariaDB

## การติดตั้ง

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
