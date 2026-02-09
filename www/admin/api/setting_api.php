<?php
header('Content-Type: application/json');
include '../../condb/condb.php';

// Helper function to send JSON response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get raw POST data
$input = json_decode(file_get_contents('php://input'), true);
$action = $_POST['action'] ?? $input['action'] ?? '';
$table = $_POST['table'] ?? $input['table'] ?? '';

// Validate action and table
$allowed_tables = ['department', 'positions', 'position_level', 'gender', 'education_level', 'worklevel', 'workbranch'];
if (!in_array($table, $allowed_tables)) {
    sendResponse(false, 'Invalid table');
}

try {
    switch ($action) {
        case 'add':
            $fields = [];
            $values = [];
            $types = "";
            $params = [];
            
            // Map table fields
            $field_map = [
                'department' => ['department_name'],
                'positions' => ['position_name'],
                'position_level' => ['level_name'],
                'gender' => ['gender_name'],
                'education_level' => ['education_name'],
                'worklevel' => ['work_level_name'],
                'workbranch' => ['workbranch_name', 'department_id']
            ];
            
            foreach ($field_map[$table] as $field) {
                $val = $_POST[$field] ?? $input[$field] ?? null;
                if ($val === null && $field != 'department_id') { // department_id can be null or we might skip validation for now
                     throw new Exception("Missing field: $field");
                }
                $fields[] = $field;
                $values[] = "?";
                $types .= "s"; // Assuming all strings for simplicity, adjust for int if needed
                $params[] = $val;
            }
            
            // Special handling for department_id int type
            if ($table === 'workbranch') {
                $types = "si"; // string, int
            }

            $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
            $stmt = $mysqli3->prepare($sql);
            if (!$stmt) throw new Exception($mysqli3->error);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                sendResponse(true, 'เพิ่มข้อมูลสำเร็จ');
            } else {
                throw new Exception($stmt->error);
            }
            break;

        case 'edit':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            if (!$id) throw new Exception('Missing ID');
            
            $set_clause = [];
            $types = "";
            $params = [];
            
             // Map table fields
             $field_map = [
                'department' => ['department_name'],
                'positions' => ['position_name'],
                'position_level' => ['level_name'],
                'gender' => ['gender_name'],
                'education_level' => ['education_name'],
                'worklevel' => ['work_level_name'],
                'workbranch' => ['workbranch_name', 'department_id']
            ];
            
            foreach ($field_map[$table] as $field) {
                $val = $_POST[$field] ?? $input[$field] ?? null;
                 if ($val !== null) {
                    $set_clause[] = "$field = ?";
                    $types .= ($field == 'department_id') ? "i" : "s";
                    $params[] = $val;
                }
            }
            
            if (empty($set_clause)) throw new Exception('No data to update');
            
            $params[] = $id;
            $types .= "i"; // ID is int
            
            $sql = "UPDATE $table SET " . implode(', ', $set_clause) . " WHERE id = ?";
            $stmt = $mysqli3->prepare($sql);
            if (!$stmt) throw new Exception($mysqli3->error);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                sendResponse(true, 'แก้ไขข้อมูลสำเร็จ');
            } else {
                throw new Exception($stmt->error);
            }
            break;

        case 'delete':
            $id = $_POST['id'] ?? $input['id'] ?? null;
            if (!$id) throw new Exception('Missing ID');
            
            $sql = "DELETE FROM $table WHERE id = ?";
            $stmt = $mysqli3->prepare($sql);
            if (!$stmt) throw new Exception($mysqli3->error);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                sendResponse(true, 'ลบข้อมูลสำเร็จ');
            } else {
                throw new Exception($stmt->error);
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
} catch (Exception $e) {
    sendResponse(false, $e->getMessage());
}
?>
