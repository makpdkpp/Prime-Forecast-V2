# PHP2Shell deployment — 26 ตุลาคม 2026

## สถานะและขอบเขต

PHP2Shell เป็นช่องโหว่ใน PHP runtime ไม่ใช่ Laravel การแก้ source code หรืออัปเดต Composer อย่างเดียวไม่สามารถแพตช์ PHP บนโฮสต์ได้

PHP 8.2.35 ที่โฮสต์แจ้งเป็นรุ่นแก้ CVE-2026-109618 และ CVE-2026-109619 แล้ว เช่นเดียวกับ 8.3.36, 8.4.27 และ 8.5.12 ต้องยืนยันว่าเป็นเวอร์ชันที่ **web worker** ใช้งานจริงหลัง restart ไม่ใช่ตรวจเฉพาะ CLI หรือเวอร์ชันที่เลือกใน control panel

ประกาศ PHP วันที่ 23 ตุลาคมเพิ่มเติมเกี่ยวกับ CVE-2026-115560 แนะนำปิด `session.upload_progress.enabled` จนกว่าจะมี regular bugfix release ช่องทางนี้ต้องมีการสลับ native session serializer ขณะ runtime ซึ่งไม่พบใน application code ที่ตรวจครั้งนี้ แต่ควรปิดตามประกาศเพื่อป้องกันเพิ่มเติม ระบบนี้ไม่ได้ใช้ native PHP session upload progress; การอัปโหลดไฟล์และ Laravel session ยังใช้งานได้

## สิ่งที่แพตช์ใน repository นี้ทำ

- Composer ปฏิเสธ PHP รุ่นก่อนแพตช์ในแต่ละ branch และ branch ที่ยังไม่ได้ตรวจสอบ โดยไม่เปลี่ยนเวอร์ชัน dependencies
- `deploy/php/99-prime-security.ini` เป็น server configuration snippet ที่ปิด upload progress **ไฟล์นี้ไม่ได้ถูกโหลดอัตโนมัติจาก repository**
- `php artisan security:check-php-runtime` ตรวจเวอร์ชันและค่า upload progress ของ CLI ที่รันคำสั่ง พร้อมคืน exit code 1 เมื่อไม่ผ่าน คำสั่งนี้ไม่ตรวจโฮสต์จากระยะไกลและไม่ยืนยัน web SAPI

## ขั้นตอนบน host PHP 8.2.35

1. สำรอง configuration เดิม ตั้งค่า `session.upload_progress.enabled = 0` ใน **php.ini ที่ web SAPI โหลดจริง**, conf.d หรือ server/FPM pool configuration ที่มีผลก่อน request parsing ถ้าใช้ shared hosting ให้ผู้ให้บริการตั้งค่าในระดับนี้ อย่าอาศัย `ini_set()`, Laravel middleware หรือ FPM `.user.ini` เพราะสายเกินไปสำหรับการป้องกันช่วง parse request
2. สำหรับ FPM ผู้ดูแลสามารถใส่ `php_admin_flag[session.upload_progress.enabled] = off` ใน pool ของเว็บไซต์แทน snippet; สำหรับ Apache mod_php ให้ใช้ php.ini/server configuration ห้ามนำ directive ของ mod_php ไปใช้กับ FPM `.htaccess`
3. Restart PHP-FPM หรือ Apache ตาม SAPI ให้ **worker เดิมทั้งหมดหยุดทำงาน** รวมถึง persistent PHP application workers ที่เกี่ยวข้อง การคัดลอกไฟล์หรือ clear Laravel cache ไม่เพียงพอ
4. ตรวจด้วยเครื่องมือผู้ดูแล/control panel ที่ทำงานผ่าน web SAPI ว่า `PHP_VERSION` เป็น 8.2.35 และ `session.upload_progress.enabled` เป็น Off ตรวจ loaded ini ของ web SAPI ให้ตรงกับที่แก้ หลีกเลี่ยงการเปิด public `phpinfo()` หรือ diagnostic endpoint ค้างไว้ใน production
5. ติดตั้ง release ด้วยคำสั่งด้านล่างโดยใช้ PHP CLI ที่แพตช์แล้วด้วย จากนั้นทดสอบ login, 2FA, avatar upload และ export Excel/PDF บน staging ก่อน deploy จริง

```sh
composer install --no-dev --optimize-autoloader
composer check-platform-reqs --no-dev
php artisan security:check-php-runtime
```

ถ้า CLI ใช้ ini คนละชุดกับเว็บ ต้องตั้งค่าทั้งสองชุดตามหน้าที่ของแต่ละ SAPI อย่าใช้ `--ignore-platform-req=php` ใน production และอย่า override `config.platform.php` เพื่อซ่อนเวอร์ชันจริง

เครื่องพัฒนาที่ตรวจครั้งนี้ใช้ PHP 8.3.30 ต้องอัปเกรดเป็น 8.3.36+ ก่อน install ครั้งต่อไป การรัน test บน PHP เดิมไม่ยืนยันความปลอดภัยหรือความเข้ากันได้บน PHP 8.2.35 ของโฮสต์

แพตช์นี้ไม่เพิ่ม HTTP middleware เพื่ออ้างว่าป้องกัน PHP2Shell เพราะการโจมตีสามารถเกิดก่อน application code ทำงาน และไม่เปลี่ยน `memory_limit` เป็น unlimited บนโฮสต์ที่แพตช์แล้ว

หากโฮสต์เคยเปิดรับอินเทอร์เน็ตด้วย PHP รุ่นก่อนแพตช์ ควรตรวจ access/error logs และไฟล์ที่ถูกแก้ไขผิดปกติร่วมกับผู้ดูแลโฮสต์ การอัปเดต runtime ไม่ได้ลบ web shell ที่อาจถูกวางไว้ก่อนหน้า

PHP 8.2 สิ้นสุด security support วันที่ 31 ธันวาคม 2026 จึงควรวางแผนอัปเกรด branch ต่อไป

## แหล่งข้อมูลทางการ

- [PHP security advisory, 21 October 2026](https://www.php.net/index.php)
- [PHP informational advisory: Session upload progress, 23 October 2026](https://www.php.net/index.php)
- [NCSC-NL operational information](https://github.com/NCSC-NL/php2shell)
