<?php
/**
 * init.php — ไฟล์ bootstrap กลาง
 * ถูก require จาก base.php ก่อนแสดงผลทุกหน้า
 */

// เริ่ม session (ถ้ายังไม่เริ่ม)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (ปิดในส่วนที่แสดงผลบน production)
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// เชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/condb/condb.php';
