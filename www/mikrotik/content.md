# MikroTik Page - สรุปเนื้อหาทั้งหมด

## โครงสร้างหน้าเว็บ (Sections)

### 1. Hero Banner
- **หัวข้อ:** MikroTik - วิทยาลัยเทคนิคเลย
- **คำอธิบาย:** สร้างมืออาชีพด้านระบบเครือข่าย ปูพื้นฐานสู่ความเป็นเลิศ พร้อมใบรับรองมาตรฐานระดับโลก MTCNA

### 2. Intro
- โครงการ MikroTik สำหรับสถานศึกษา เพื่อให้นักเรียนนักศึกษาได้เรียนรู้การใช้งานอุปกรณ์เครือข่ายมาตรฐานสากล พร้อมโอกาสในการสอบใบรับรอง MTCNA

### 3. รู้จักกับ MTCNA (About MTCNA)
- **MTCNA Course** — MikroTik Certified Network Associate
- ใบรับรองพื้นฐานที่ครอบคลุมความรู้เบื้องต้นเกี่ยวกับ MikroTik RouterOS และการจัดการเครือข่าย
- **อุปกรณ์ที่ใช้ในการเรียนรู้:**
  - คอมพิวเตอร์ Notebook สำหรับ Config
  - สาย LAN จำนวน 3 เส้น
  - MikroTik RouterBOARD (ทางวิทยาลัยจัดเตรียมให้)
- **เงื่อนไขการสอบ:**
  - เรียนรู้เนื้อหาครบตามหลักสูตร
  - สอบผ่านเกณฑ์ 60% ขึ้นไป (Online Exam)
  - รับใบรับรอง MTCNA จาก MikroTik ทันที
- ใบรับรองมาตรฐานสากลที่ทั่วโลกยอมรับ ครอบคลุมพื้นฐาน Wi-Fi, Routing, Firewall

---

### 4. MikroTik in LoeiTech — การประยุกต์ใช้งานในวิทยาลัยเทคนิคเลย

#### 4.1 การบูรณาการในหลักสูตรการเรียนการสอน

**วิชาที่ 1:** ระดับ ปวช. (วิชา 20901-2006) — ระบบรักษาความปลอดภัยคอมพิวเตอร์เบื้องต้น
- หัวข้อหลัก: การป้องกันเครือข่ายไร้สาย (Basic WiFi Security)
  - การตั้งค่า Security Profiles (WPA2/WPA3) บน MikroTik Access Point
  - การทำ MAC Address Filtering เพื่อคัดกรองอุปกรณ์
  - การสร้าง Guest WiFi แยกจากระบบเครือข่ายหลักเพื่อความปลอดภัย
  - การตรวจสอบผู้บุกรุกผ่านทาง Log และ Torch ของ MikroTik

**วิชาที่ 2:** ระดับ ปวส. (วิชา 31901-2011) — การจัดการระบบเครือข่ายเบื้องต้น
- หัวข้อหลัก: พื้นฐานการ Routing และ Firewall
  - IP & DHCP: การจัดสรรไอพีแอดเดรสและการทำ Static Lease
  - NAT (Network Address Translation): การทำ Masquerade เพื่อออกอินเทอร์เน็ต
  - Firewall Filter Rules: การบล็อกเว็บไซต์ไม่พึงประสงค์ และการป้องกันการโจมตีเบื้องต้น
  - Static Route: การกำหนดเส้นทางข้อมูลในเครือข่าย

**วิชาที่ 3:** ระดับ ปวช. (วิชา 20901-9203) — การอินเทอร์เฟสในระบบสมองกลฝังตัวและไอโอที
- หัวข้อหลัก: MikroTik as an IoT Gateway
  - การตั้งค่า MikroTik ให้เป็น WiFi Infrastructure ที่เสถียรสำหรับอุปกรณ์ ESP32
  - การจัดการ Traffic ของโปรโตคอล MQTT เพื่อรับ-ส่งข้อมูลกับ EMQX Server
  - การใช้คุณสมบัติ Queue บน MikroTik เพื่อจำกัด Bandwidth ให้กับอุปกรณ์ IoT

#### 4.2 โครงสร้างเครือข่ายวิทยาลัย (Campus Network Infrastructure)
- **สถาปัตยกรรมระบบ:** Hybrid Networking (Hardware + Software Virtualization)
- **Core Router:** MikroTik CCR (Cloud Core Router) — Main Gateway รองรับ Traffic ทั้งวิทยาลัย
- **Virtual Firewall & Controller:** MikroTik CHR (Cloud Hosted Router) บน Proxmox VE
- **Network Services:**
  1. VLAN Segmentation — แยกวงเครือข่ายตามแผนกและอาคารเรียน
  2. Hotspot & Radius — ระบบพิสูจน์ตัวตนนักศึกษาผ่าน User Manager
  3. High Availability — Backup Config และ Snapshot บน Proxmox

---

### 5. ภาพกิจกรรม
- แกลเลอรีภาพอัตโนมัติจากโฟลเดอร์ `images/gallery/`

### 6. วิทยากรและที่ปรึกษา
| ชื่อ | ไฟล์รูป | บทบาท | ใบรับรอง |
|------|---------|-------|----------|
| นายกรรัก พร้อมจะบก (Mr. Korarak Promjabok) | mr-korarak-mtcna-mtctce.png | Trainer / Network Admin | MTCNA, MTCTCE |
| น.ส. ชนิดาภา ลานนท์ (Ms. Chanidapa Lanon) | miss-chanidapha-mtcna-mtctce.png | Trainer | MTCNA, MTCTCE |
| นาย ชัยยุทธ์ บุตรเต (Mr. Chaiyut Buttae) | mr-chaiyut-mtcna-mtctce.png | Trainer | MTCNA, MTCTCE |
| นางสาวสวรินทร์ จันทร์สว่าง (Ms. Sawarin Chansawang) | miss-sawarin-mtcna.png | Assistant Trainer | MTCNA |

### 7. Footer
- **ที่อยู่:** วิทยาลัยเทคนิคเลย, 38 ถนนนกแก้ว ต.กุดป่อง อ.เมือง จ.เลย 42000
- **โทร:** 042-811-591
- **อีเมล:** info@loeitech.ac.th
- **ลิงก์:** Facebook, YouTube, TikTok, ระบบ EDR

---

## Navigation Menu
หน้าแรก | หลักสูตร | MikroTik in LoeiTech | กิจกรรม | ที่ปรึกษา | ติดต่อเรา | TH/EN