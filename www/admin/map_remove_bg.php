<?php
include 'middleware.php';
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update DB to clear background_image for ID 1
    $stmt = $mysqli->prepare("UPDATE sys_maps SET background_image = '' WHERE id = 1");
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $mysqli->error]);
    }
}
?>
