<?php
header('Content-Type: application/json');
include 'middleware.php';
include 'db_letter.php';

function deleteLetterFile($path) {
    if (empty($path)) return;
    
    // DB stores "uploads/newsletter/file.jpg"
    // Helper is in admin/, needs to delete "../uploads/newsletter/file.jpg"
    $physical_path = "../" . $path; // Simple prepending based on our new standard
    
    if (file_exists($physical_path) && is_file($physical_path)) {
        unlink($physical_path);
    } 
    // Fallback for legacy paths if needed (e.g. if they didn't have uploads/ prefix, but we fixed DB so should be fine)
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action'];

if ($action === 'delete' && !empty($input['id'])) {
    $id = (int)$input['id'];
    
    // Get file path first
    $stmt = $conn->prepare("SELECT letter_attenmath FROM letters WHERE letter_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    
    if ($row) {
        // Delete file
        deleteLetterFile($row['letter_attenmath']);
        
        // Delete Record
        $del_stmt = $conn->prepare("DELETE FROM letters WHERE letter_id = ?");
        $del_stmt->bind_param("i", $id);
        if ($del_stmt->execute()) {
            $response = ['success' => true, 'message' => 'ลบข้อมูลสำเร็จ'];
        } else {
            $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
    } else {
        $response = ['success' => false, 'message' => 'ไม่พบข้อมูล'];
    }

} elseif ($action === 'bulk_delete' && !empty($input['ids'])) {
    $ids = array_map('intval', $input['ids']);
    if (!empty($ids)) {
        $id_str = implode(',', $ids);
        
        // Get files
        $result = $conn->query("SELECT letter_attenmath FROM letters WHERE letter_id IN ($id_str)");
        while ($row = $result->fetch_assoc()) {
            deleteLetterFile($row['letter_attenmath']);
        }
        
        // Delete Records
        if ($conn->query("DELETE FROM letters WHERE letter_id IN ($id_str)")) {
            $response = ['success' => true, 'message' => 'ลบข้อมูล ' . count($ids) . ' รายการสำเร็จ'];
        } else {
            $response = ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
    }
}

echo json_encode($response);
$conn->close();
?>
