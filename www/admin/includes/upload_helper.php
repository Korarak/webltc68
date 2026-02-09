<?php
function uploadBase64Image($base64, $targetDir, $prefix = "img_", $ext = "jpg") {
    if (empty($base64)) return false;

    // ตรวจสอบและสร้างโฟลเดอร์
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // ตัด header เช่น data:image/jpeg;base64,
    if (strpos($base64, "base64,") !== false) {
        $base64 = explode("base64,", $base64)[1];
    }

    $data = base64_decode($base64);
    $fileName = $prefix . uniqid() . "." . $ext;
    $filePath = rtrim($targetDir, "/") . "/" . $fileName;

    if (file_put_contents($filePath, $data)) {
        return $filePath;
    }
    return false;
}
?>
