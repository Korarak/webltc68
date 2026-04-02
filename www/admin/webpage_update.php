<?php
header('Content-Type: application/json');
include 'middleware.php';
include '../condb/condb.php'; // Uses $mysqli4

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'delete') {
    $id = intval($input['id']);
    $stmt = $mysqli4->prepare("DELETE FROM web_pages WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'ลบเพจเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบ: ' . $mysqli4->error]);
    }

} elseif ($action === 'bulk_delete') {
    $ids = $input['ids'] ?? [];
    if (empty($ids)) {
        echo json_encode(['success' => false, 'message' => 'กรุณาเลือกรายการที่ต้องการลบ']);
        exit;
    }
    
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $types = str_repeat('i', count($ids));
    $stmt = $mysqli4->prepare("DELETE FROM web_pages WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'ลบรายการที่เลือกเรียบร้อยแล้ว']);
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการลบ: ' . $mysqli4->error]);
    }

} elseif ($action === 'toggle_visibility') {
    $id = intval($input['id']);
    // Get current status
    $res = $mysqli4->query("SELECT visible FROM web_pages WHERE id = $id");
    $current = $res->fetch_assoc();
    if ($current) {
        $new_status = $current['visible'] ? 0 : 1;
        $stmt = $mysqli4->prepare("UPDATE web_pages SET visible = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'อัปเดตสถานะเรียบร้อยแล้ว', 'visible' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดต: ' . $mysqli4->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูล']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid Action']);
}

$mysqli4->close();
?>
