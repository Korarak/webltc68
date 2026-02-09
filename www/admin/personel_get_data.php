<?php
// filepath: /home/adm1n_ltc/webltc67/www/admin/personel_get_data.php
include 'middleware.php';
include '../condb/condb.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit();
}

$personel_id = $_GET['id'];

$query = "SELECT 
            p.id, p.fullname, p.Tel, p.E_mail, p.profile_image,
            d.department_name,
            pos.position_name, 
            pl.level_name, 
            GROUP_CONCAT(DISTINCT wb.workbranch_name SEPARATOR ', ') AS workbranch_names,
            GROUP_CONCAT(DISTINCT wl.work_level_name SEPARATOR ', ') AS worklevel_names
          FROM personel_data p
          LEFT JOIN department d ON p.department_id = d.id
          LEFT JOIN positions pos ON p.position_id = pos.id
          LEFT JOIN position_level pl ON p.position_level_id = pl.id
          LEFT JOIN work_detail wd ON p.id = wd.personel_id
          LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
          LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id
          WHERE p.id = ?
          GROUP BY p.id";

$stmt = $mysqli3->prepare($query);
$stmt->bind_param("i", $personel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $personel = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'data' => $personel
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Personel not found'
    ]);
}

$stmt->close();
?>