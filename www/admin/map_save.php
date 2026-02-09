<?php
include 'middleware.php';
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $map_json = $_POST['map_json'] ?? '';
    
    // Check if ID 1 exists
    $check = $mysqli->query("SELECT id FROM sys_maps WHERE id = 1");
    
    if ($check->num_rows > 0) {
        $stmt = $mysqli->prepare("UPDATE sys_maps SET map_json = ? WHERE id = 1");
        $stmt->bind_param("s", $map_json);
    } else {
        $stmt = $mysqli->prepare("INSERT INTO sys_maps (id, map_json) VALUES (1, ?)");
        $stmt->bind_param("s", $map_json);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $mysqli->error]);
    }
}
?>
