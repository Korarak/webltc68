<?php
// filepath: /home/adm1n_ltc/webltc67/www/admin/personel_delete.php
include 'middleware.php';
session_start();
ob_start();
include '../condb/condb.php';

// ตรวจสอบว่า id ได้รับมาจาก URL หรือไม่
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $personel_id = $_GET['id'];

    // 1. ดึงข้อมูลรูปภาพเก่ามาก่อนลบ (เพื่อลบไฟล์ออกจาก Server)
    $img_query = "SELECT profile_image FROM personel_data WHERE id = ?";
    $stmt = $mysqli3->prepare($img_query);
    $stmt->bind_param("i", $personel_id);
    $stmt->execute();
    $stmt->bind_result($profile_image);
    $stmt->fetch();
    $stmt->close();

    // ถ้ามีรูปภาพและไฟล์มีอยู่จริง ให้ลบไฟล์
    if (!empty($profile_image) && file_exists($profile_image)) {
        unlink($profile_image);
    }

    // 2. ลบข้อมูลในตาราง work_detail ที่เชื่อมโยงกับบุคลากร
    $delete_work_detail_query = "DELETE FROM work_detail WHERE personel_id = ?";
    $stmt = $mysqli3->prepare($delete_work_detail_query);
    $stmt->bind_param("i", $personel_id);
    $stmt->execute();
    $stmt->close();

    // 3. ลบข้อมูลในตาราง personel_data
    $delete_personel_query = "DELETE FROM personel_data WHERE id = ?";
    $stmt = $mysqli3->prepare($delete_personel_query);
    $stmt->bind_param("i", $personel_id);
    
    if ($stmt->execute()) {
        // แจ้งเตือนแบบ Toast (ใช้ format เดียวกับ manage page)
        $_SESSION['toast_message'] = [
            'type' => 'success',
            'message' => '✅ ลบข้อมูลบุคลากรเรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['toast_message'] = [
            'type' => 'error',
            'message' => '❌ เกิดข้อผิดพลาดในการลบข้อมูล'
        ];
    }
    $stmt->close();

    // 4. [UX Improvement] สร้าง URL Redirect กลับไปหน้าเดิม + ตัวกรองเดิม
    // เอาค่า GET ทั้งหมดมา (เช่น page, search, filters) ยกเว้น 'id'
    $params = $_GET;
    unset($params['id']);
    $query_string = http_build_query($params);

    // กลับไปที่หน้า personel_manage.php พร้อมค่าเดิม
    header("Location: personel_manage.php?" . $query_string);
    exit();

} else {
    // กรณีไม่พบ ID
    $_SESSION['toast_message'] = [
        'type' => 'error',
        'message' => '❌ ไม่พบรหัสบุคลากรที่ต้องการลบ'
    ];
    header("Location: personel_manage.php");
    exit();
}
?>