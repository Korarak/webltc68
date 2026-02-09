<?php
include 'middleware.php';
require_once '../config.php';

header('Content-Type: application/json');

if ($_FILES['bg_image']) {
    $target_dir = "../uploads/";
    $filename = time() . '_' . basename($_FILES["bg_image"]["name"]);
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($_FILES["bg_image"]["tmp_name"], $target_file)) {
        // Update DB
        // Check if ID 1 exists or insert
        $check = $mysqli->query("SELECT id FROM sys_maps WHERE id = 1");
        if ($check->num_rows > 0) {
            $stmt = $mysqli->prepare("UPDATE sys_maps SET background_image = ? WHERE id = 1");
            $stmt->bind_param("s", $filename);
        } else {
            $stmt = $mysqli->prepare("INSERT INTO sys_maps (id, background_image) VALUES (1, ?)");
            $stmt->bind_param("s", $filename);
        }
        $stmt->execute();
        
        echo json_encode(['success' => true, 'filename' => $filename]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
}
?>
