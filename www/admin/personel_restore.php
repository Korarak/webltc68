<?php
// filepath: /home/adm1n_ltc/webltc68/www/admin/personel_restore.php
include 'middleware.php';
session_start();
include '../condb/condb.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $personel_id = $_GET['id'];

    // คืนค่าสถานะเป็น 0
    $restore_query = "UPDATE personel_data SET is_deleted = 0 WHERE id = ?";
    $stmt = $mysqli3->prepare($restore_query);
    $stmt->bind_param("i", $personel_id);
    
    if ($stmt->execute()) {
        $_SESSION['toast_message'] = [
            'type' => 'success',
            'message' => '✅ คืนค่าข้อมูลบุคลากรเรียบร้อยแล้ว'
        ];
    } else {
        $_SESSION['toast_message'] = [
            'type' => 'error',
            'message' => '❌ เกิดข้อผิดพลาดในการคืนค่าข้อมูล'
        ];
    }
    $stmt->close();

    // รักษาค่าตัวกรองเดิมไว้
    $params = $_GET;
    unset($params['id']);
    $query_string = http_build_query($params);

    header("Location: personel_manage.php?" . $query_string);
    exit();

} else {
    header("Location: personel_manage.php");
    exit();
}
?>
