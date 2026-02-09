<?php
header('Content-Type: application/json');
include 'middleware.php';
include('db_news.php');

// Helper function for deletion
function deleteNews($conn, $id) {
    $id = intval($id);
    
    // 1. Get Attachments to delete files
    $stmt = $conn->prepare("SELECT file_path FROM attachments WHERE news_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['file_path']) && file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }
    }
    
    // 2. Delete Attachments Record (Optional if Cascade is set, but safe to do)
    $stmt = $conn->prepare("DELETE FROM attachments WHERE news_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // 3. Delete News Record
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'delete') {
    if (deleteNews($conn, $input['id'])) {
        echo json_encode(['success' => true, 'message' => 'ลบข่าวเรียบร้อยแล้ว']);
    } else {
         echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบ']);
    }

} elseif ($action === 'bulk_delete') {
    $ids = $input['ids'] ?? [];
    $successCount = 0;
    
    foreach ($ids as $id) {
        if (deleteNews($conn, $id)) {
            $successCount++;
        }
    }
    
    echo json_encode(['success' => true, 'message' => "ลบข่าวสำเร็จ $successCount รายการ"]);

} elseif ($action === 'update_field') { // Future proofing: Instant title edit?
    // Implement if needed for quick edits
    echo json_encode(['success' => false, 'message' => 'Not implemented yet']);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Action']);
}

$conn->close();
?>
