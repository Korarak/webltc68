<?php
header('Content-Type: application/json');
include 'middleware.php';
include('db_news.php');

// Helper function for soft deletion
function deleteNews($conn, $id) {
    $id = intval($id);
    // 1. Soft Delete News Record
    $stmt = $conn->prepare("UPDATE news SET is_deleted = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Helper function for restoration
function restoreNews($conn, $id) {
    $id = intval($id);
    $stmt = $conn->prepare("UPDATE news SET is_deleted = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Helper function for permanent deletion
function hardDeleteNews($conn, $id) {
    $id = intval($id);
    
    // First, delete attachments files if any
    $res = $conn->query("SELECT file_path FROM attachments WHERE news_id = $id");
    while($row = $res->fetch_assoc()) {
        $full_path = $_SERVER['DOCUMENT_ROOT'] . $row['file_path'];
        if(file_exists($full_path)) @unlink($full_path);
    }
    
    // Delete attachments records
    $conn->query("DELETE FROM attachments WHERE news_id = $id");
    
    // Delete news record
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

} elseif ($action === 'restore') {
    if (restoreNews($conn, $input['id'])) {
        echo json_encode(['success' => true, 'message' => 'คืนค่าข่าวเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการคืนค่า']);
    }

} elseif ($action === 'hard_delete') {
    if (hardDeleteNews($conn, $input['id'])) {
        echo json_encode(['success' => true, 'message' => 'ลบข่าวถาวรเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบถาวร']);
    }

} elseif ($action === 'bulk_restore') {
    $ids = $input['ids'] ?? [];
    $count = 0;
    foreach ($ids as $id) if (restoreNews($conn, $id)) $count++;
    echo json_encode(['success' => true, 'message' => "คืนค่าข่าวสำเร็จ $count รายการ"]);

} elseif ($action === 'bulk_hard_delete') {
    $ids = $input['ids'] ?? [];
    $count = 0;
    foreach ($ids as $id) if (hardDeleteNews($conn, $id)) $count++;
    echo json_encode(['success' => true, 'message' => "ลบข่าวถาวรสำเร็จ $count รายการ"]);

} elseif ($action === 'update_field') { // Future proofing: Instant title edit?
    // Implement if needed for quick edits
    echo json_encode(['success' => false, 'message' => 'Not implemented yet']);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Action']);
}

$conn->close();
?>
