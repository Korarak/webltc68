<?php
include 'middleware.php';
include '../condb/condb.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $mysqli4->prepare("DELETE FROM web_pages WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>
            alert('ลบเพจเรียบร้อยแล้ว');
            window.location.href = 'webpages_manage.php';
        </script>";
        exit;
    } else {
        echo "<script>
            alert('เกิดข้อผิดพลาดในการลบ: " . $mysqli4->error . "');
            window.location.href = 'webpages_manage.php';
        </script>";
        exit;
    }
} else {
    header("Location: webpages_manage.php");
    exit;
}
?>
