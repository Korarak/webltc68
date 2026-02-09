<?php
header('Content-Type: application/json');
include 'middleware.php';
include 'db_letter.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'toggle_visible') {
    $c_id = intval($input['id']);
    $current_status = intval($input['status']);
    $new_status = $current_status == 1 ? 0 : 1;
    
    $stmt = $conn->prepare("UPDATE carousel SET visible = ? WHERE carousel_id = ?");
    $stmt->bind_param("ii", $new_status, $c_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'new_status' => $new_status, 'message' => 'Updated visibility successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

} elseif ($action === 'toggle_popup') {
    $c_id = intval($input['id']);
    $current_status = intval($input['status']);
    
    if ($current_status == 0) { // Turning ON popup
        // Check if another popup exists (optional rule: only 1 popup allowed?)
        // The previous code had a check, let's keep it but maybe allow override?
        // User requested "Powerful", maybe 1 popup is a strict rule.
        
        // Strict Rule: Only 1 popup allowed. Disable others first ??
        // Actually, let's stick to the previous logic: if one exists, warn.
        // OR better: Auto-disable others? Let's check logic.
        // The old code blocked it. Let's return error if > 0.
        
        $chk = $conn->query("SELECT carousel_id FROM carousel WHERE slide_show = 1 AND carousel_id != $c_id");
        if ($chk->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'มีป้ายอื่นเป็น Pop-up อยู่แล้ว (อนุญาตเพียง 1 รายการ)']);
            exit;
        }
    }
    
    $new_status = $current_status == 1 ? 0 : 1;
    $stmt = $conn->prepare("UPDATE carousel SET slide_show = ? WHERE carousel_id = ?");
    $stmt->bind_param("ii", $new_status, $c_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'new_status' => $new_status, 'message' => 'Updated popup status successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

} elseif ($action === 'reorder') {
    $order = $input['order']; // Array of IDs in new order
    
    // We will update 'carousel_no' based on index
    $sql = "UPDATE carousel SET carousel_no = ? WHERE carousel_id = ?";
    $stmt = $conn->prepare($sql);
    
    foreach ($order as $index => $id) {
        $rank = $index + 1;
        $id = intval($id);
        $stmt->bind_param("ii", $rank, $id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Reordered successfully']);

} elseif ($action === 'delete') {
    $c_id = intval($input['id']);
    
    // Get file path to delete
    $result = $conn->query("SELECT carousel_pic FROM carousel WHERE carousel_id = $c_id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $physical_path = "../" . $row['carousel_pic'];
        if ($row['carousel_pic'] && file_exists($physical_path)) {
            unlink($physical_path);
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM carousel WHERE carousel_id = ?");
    $stmt->bind_param("i", $c_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
