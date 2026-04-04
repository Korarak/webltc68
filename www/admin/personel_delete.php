<?php
// filepath: /home/adm1n_ltc/webltc68/admin/personel_delete.php
include 'middleware.php';
session_start();
include '../condb/condb.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $personel_id = $_GET['id'];
    $is_permanent = isset($_GET['permanent']) && $_GET['permanent'] == 1;

    if ($is_permanent) {
        // --- 1. Hard Delete (ลบถาวร) ---
        // ดึงชื่อไฟล์รูปภาพมาลบจริง
        $stmt_img = $mysqli3->prepare("SELECT profile_image FROM personel_data WHERE id = ?");
        $stmt_img->bind_param("i", $personel_id);
        $stmt_img->execute();
        $stmt_img->bind_result($profile_image);
        $stmt_img->fetch();
        $stmt_img->close();

        if ($profile_image && !preg_match("~^(?:f|ht)tps?://~i", $profile_image)) {
            $full_path = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($profile_image, '/');
            if (file_exists($full_path)) @unlink($full_path);
        }

        // ลบข้อมูลจากตารางที่เกี่ยวข้อง
        $mysqli3->query("DELETE FROM work_detail WHERE personel_id = $personel_id");
        $stmt = $mysqli3->prepare("DELETE FROM personel_data WHERE id = ?");
    } else {
        // --- 2. Soft Delete (ย้ายไปถังขยะ) ---
        $stmt = $mysqli3->prepare("UPDATE personel_data SET is_deleted = 1 WHERE id = ?");
    }

    $stmt->bind_param("i", $personel_id);
    
    if ($stmt->execute()) {
        $_SESSION['toast_message'] = [
            'type' => 'success',
            'message' => $is_permanent ? '🔥 ลบข้อมูลบุคลากรและไฟล์ถาวรแล้ว' : '✅ ย้ายข้อมูลบุคลากรไปไว้ในถังขยะแล้ว'
        ];
    } else {
        $_SESSION['toast_message'] = [
            'type' => 'error',
            'message' => '❌ เกิดข้อผิดพลาดในการดำเนินการ'
        ];
    }
    $stmt->close();

    // กลับไปที่หน้าเดิมพร้อมตัวกรองเดิม
    $params = $_GET;
    unset($params['id'], $params['permanent']);
    $query_string = http_build_query($params);

    header("Location: personel_manage.php?" . $query_string);
    exit();

} else {
    header("Location: personel_manage.php");
    exit();
}
?>