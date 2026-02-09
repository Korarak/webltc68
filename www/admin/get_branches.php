<?php
include '../condb/condb.php';

$department_id = $_GET['department_id'] ?? 0;
if ($department_id == 0) {
    echo json_encode([]);
    exit;
}

$query = "SELECT id, workbranch_name FROM workbranch WHERE department_id = ?";
$stmt = $mysqli3->prepare($query);
$stmt->bind_param("i", $department_id);
$stmt->execute();
$result = $stmt->get_result();

$branches = [];
while ($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

echo json_encode($branches);
?>