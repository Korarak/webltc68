# Floating Badges System

## ขั้นตอนการติดตั้ง

### 1. อัปเดตฐานข้อมูล
รันไฟล์ SQL migration ในฐานข้อมูล `ltc_web`:
```sql
ALTER TABLE badges ADD COLUMN IF NOT EXISTS badge_image VARCHAR(255);
ALTER TABLE badges ADD COLUMN IF NOT EXISTS badge_url VARCHAR(255);
```

หรือใช้ phpMyAdmin รันคำสั่ง SQL จากไฟล์ `migrate_badges.sql`

### 2. สร้างโฟลเดอร์สำหรับเก็บรูปภาพ
```bash
mkdir -p /path/to/www/uploads/badges
chmod 755 /path/to/www/uploads/badges
```

### 3. เพิ่มไฟล์ไปยังหน้าหลัก (index.php)
เพิ่มบรรทัดด้านล่างนี้ลงในส่วน HTML `<body>` ของไฟล์ `index.php`:

```php
<?php include 'floating-badges.php'; ?>
```

## การใช้งาน

### หน้าจัดการ Admin
ไปที่: `/admin/badge_manage.php`

### ฟีเจอร์

1. **เพิ่ม Badge**
   - ชื่อ Badge (ถูกต้อง)
   - ไอคอน Font Awesome (ตัวเลือก)
   - รูปภาพ (ตัวเลือก - JPG, PNG, GIF, WebP)
   - สี (ถูกต้อง - Hex color)
   - ลิงค์ (ตัวเลือก)
   - คำอธิบาย (ตัวเลือก)
   - สถานะแสดง/ซ่อน

2. **แก้ไข Badge**
   - อัปเดตข้อมูล Badge
   - เปลี่ยนรูปภาพ (ลบรูปเก่าโดยอัตโนมัติ)

3. **ลบ Badge**
   - ลบข้อมูลและรูปภาพที่เกี่ยวข้อง

### ข้อมูล Database Schema

```sql
CREATE TABLE badges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    badge_name VARCHAR(100) NOT NULL,
    badge_description TEXT,
    badge_icon VARCHAR(100),
    badge_image VARCHAR(255),
    badge_color VARCHAR(7),
    badge_url VARCHAR(255),
    visible INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### ตัวอย่างการใช้ Font Awesome Icons

- `fa-star` - ดาว
- `fa-shopping-cart` - ตะกร้า
- `fa-robot` - หุ่นยนต์
- `fa-gift` - ของขวัญ
- `fa-trophy` - รางวัล
- `fa-heart` - หัวใจ
- `fa-bell` - ระฆัง

ดูรายชื่อไอคอนทั้งหมดได้ที่: https://fontawesome.com/icons

### ตัวอย่างข้อมูล Badge

```sql
INSERT INTO badges (badge_name, badge_description, badge_icon, badge_color, badge_url, visible) 
VALUES 
('สินค้าที่ระลึก 88 ปี', 'สินค้าพิเศษเฉลิมฉลองครบ 88 ปี', 'fa-shopping-cart', '#10b981', 'https://88y.loeitech.ac.th', 1),
('ประกวดทักษะ AI', 'การแข่งขันทักษะปัญญาประดิษฐ์', 'fa-robot', '#7c3aed', 'https://example.com', 1);
```

## ไฟล์ที่เกี่ยวข้อง

- `floating-badges.php` - ไฟล์หลักสำหรับแสดง floating badges
- `admin/badge_manage.php` - หน้าจัดการ badges
- `uploads/badges/` - โฟลเดอร์เก็บรูปภาพ
- `migrate_badges.sql` - SQL migration file

## หมายเหตุ

- Badge จะแสดงเฉพาะบนหน้าแรก (index.php หรือ /)
- Badge ที่มี `visible = 0` จะไม่แสดง
- Animation float จะหยุดเมื่อ hover ที่ badge
- รูปภาพ badge จะถูกแสดงแทน icon หากมีรูปภาพ
- ไฟล์รูปภาพเก่าจะถูกลบโดยอัตโนมัติเมื่อแก้ไข

## Troubleshooting

### ไม่สามารถอัปโหลดรูปภาพ
- ตรวจสอบสิทธิ์เข้าถึงโฟลเดอร์ `uploads/badges/` (ต้องเป็น 755)
- ตรวจสอบประเภทไฟล์ (ต้องเป็น JPG, PNG, GIF, WebP)
- ตรวจสอบขนาดไฟล์ (ต้องน้อยกว่า 2MB)

### Badge ไม่แสดง
- ตรวจสอบว่า `visible = 1` ในฐานข้อมูล
- ตรวจสอบว่าได้เรียก `floating-badges.php` ใน `index.php`
- ตรวจสอบ console ของ browser สำหรับข้อผิดพลาด

