# Meme Hub - PHP & MySQL CRUD Web App

เว็บแอปพลิเคชันคลังรวม Meme สำหรับดู ค้นหา เพิ่ม แก้ไข และลบข้อมูลมีม พัฒนาด้วย PHP 8+, MySQL/MariaDB, PDO และ CSS แบบ Responsive

## ความสามารถ
- แสดงคลังมีมพร้อมรูปภาพ หมวดหมู่ ผู้สร้าง และจำนวน likes
- ค้นหาตามชื่อ คำบรรยาย หรือหมวดหมู่ และกรองหมวดหมู่
- เพิ่ม / แก้ไข / ลบมีม (Delete ทำผ่าน POST)
- กำหนดหมวดหมู่ ผู้สร้าง และสถานะเผยแพร่/ฉบับร่าง
- มีข้อมูลตัวอย่างและรูป SVG ภายในโปรเจกต์ ใช้งานหน้าตัวอย่างได้โดยไม่ต้องพึ่งรูปจากเว็บภายนอก
- ใช้ PDO prepared statements, escaping ด้วย `htmlspecialchars` และ CSRF token ในฟอร์ม

## เทคโนโลยี
- PHP 8.0 หรือใหม่กว่า
- MySQL 8.0+ หรือ MariaDB 10.5+
- Apache (แนะนำ XAMPP) และ PHP PDO MySQL extension

## วิธีติดตั้งบน XAMPP (Windows)
1. ติดตั้ง/เปิด XAMPP แล้ว Start **Apache** และ **MySQL**
2. คัดลอกโฟลเดอร์ `meme-hub` ไปไว้ที่ `C:\xampp\htdocs\meme-hub`
3. เปิด `http://localhost/phpmyadmin` แล้วเลือกแท็บ **Import** จากนั้นเลือก `database/schema.sql` และกด Import (ไฟล์นี้สร้างฐานข้อมูล `meme_hub` และข้อมูลตัวอย่างให้)
4. ตรวจสอบการเชื่อมต่อใน `config/database.php` ค่าเริ่มต้นคือ host `127.0.0.1`, database `meme_hub`, username `root`, password ว่าง ซึ่งตรงกับ XAMPP แบบติดตั้งใหม่ส่วนใหญ่
5. เปิด `http://localhost/meme-hub/`

หรือ import ผ่าน terminal:

```bash
mysql -u root -p < database/schema.sql
```

หาก MySQL ไม่มีรหัสผ่านให้ใช้ `mysql -u root < database/schema.sql`.

## ตั้งค่าฐานข้อมูลด้วย Environment Variables (ทางเลือก)
ไฟล์ `config/database.php` รองรับ `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` และจะใช้ค่าจาก environment ก่อนค่าปริยาย

## โครงสร้างโปรเจกต์

```text
meme-hub/
├── index.php                 # READ: รายการ ค้นหา กรองมีม
├── meme_form.php             # CREATE / UPDATE
├── delete_meme.php           # DELETE (POST + CSRF)
├── config/database.php       # PDO MySQL connection
├── includes/                 # header, footer, validation helpers
├── assets/style.css          # Responsive UI
├── assets/memes/             # ภาพตัวอย่าง SVG
├── database/schema.sql       # DDL + sample data
└── docs/                     # เอกสารและภาพหน้าจอ
```

## โครงสร้างฐานข้อมูล
มี 3 ตาราง รวม 25 ฟิลด์:
- `users` - 7 ฟิลด์
- `categories` - 6 ฟิลด์
- `memes` - 12 ฟิลด์

ดูรายละเอียดชนิดข้อมูล Primary Key, Foreign Key และความสัมพันธ์ได้ใน `docs/database-design.md` และเอกสารรายงาน

## GitHub
โปรเจกต์นี้จัดโครงสร้างให้พร้อม commit แล้ว แต่ยังไม่ได้เผยแพร่ไปยังบัญชี GitHub ใด ๆ จากสภาพแวดล้อมนี้ หากต้องการอัปโหลด:

```bash
git init
git add .
git commit -m "Initial commit: Meme Hub PHP CRUD"
# สร้าง repository ว่างบน GitHub แล้วนำ URL มาแทนค่าด้านล่าง
git branch -M main
git remote add origin https://github.com/YOUR-USERNAME/meme-hub.git
git push -u origin main
```

## หมายเหตุ
- `database/schema.sql` เป็นสคริปต์ติดตั้งฐานข้อมูลเริ่มต้น มีคำสั่ง `DROP TABLE IF EXISTS` สำหรับตารางทั้งสาม จึงควร import เฉพาะฐานข้อมูลใหม่/ข้อมูลทดลองเท่านั้น เพราะจะลบข้อมูลเดิมในตารางชื่อเดียวกัน
- โครงงานนี้เป็นตัวอย่างสำหรับการศึกษา ยังไม่มีระบบเข้าสู่ระบบ/สิทธิ์ผู้ดูแลจริง ควรเพิ่ม authentication และ authorization ก่อนเปิดใช้งานบนอินเทอร์เน็ต
