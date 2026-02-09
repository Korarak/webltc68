<?php
include 'middleware.php';
include '../condb/condb.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ไม่พบผู้ใช้";
    $_SESSION['message_type'] = "danger";
    header("Location: admin-manage.php");
    exit();
}

$user_id = $_GET['id'];

// ตรวจสอบว่าไม่ใช่การลบตัวเอง
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['message'] = "ไม่สามารถลบบัญชีของตัวเองได้";
    $_SESSION['message_type'] = "danger";
    header("Location: admin-manage.php");
    exit();
}

// ดึงข้อมูลผู้ใช้ก่อนลบ
$stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['message'] = "ไม่พบผู้ใช้";
    $_SESSION['message_type'] = "danger";
    header("Location: admin-manage.php");
    exit();
}

// ลบผู้ใช้
$delete_stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    $_SESSION['message'] = "ลบผู้ใช้ \"{$user['username']}\" สำเร็จ";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "เกิดข้อผิดพลาดในการลบผู้ใช้";
    $_SESSION['message_type'] = "danger";
}

header("Location: admin-manage.php");
exit();
?>