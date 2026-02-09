<?php
// ปิดการแสดงผล Error บนหน้าเว็บ
/* error_reporting(0);
ini_set('display_errors', 0); */

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
include '../condb/condb.php';

// ตรวจสอบการเชื่อมต่อ
if ($mysqli3->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// ตรวจสอบว่าเป็น GET หรือ POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $thai_id = isset($data["thai_id"]) ? trim($data["thai_id"]) : "";
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    $thai_id = isset($_GET["thai_id"]) ? trim($_GET["thai_id"]) : "";
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit();
}

// ถ้าไม่มีค่า thai_id ให้หยุดทำงาน
if (empty($thai_id)) {
    exit();
}

// ตรวจสอบความถูกต้องของ Thai ID
if (!preg_match("/^\d{13}$/", $thai_id)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid Thai ID"]);
    exit();
}

// คำสั่ง SQL ตรวจสอบ Thai ID
$stmt = $mysqli3->prepare("SELECT * FROM personel_data WHERE thai_id = ?");
$stmt->bind_param("s", $thai_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Found", "thai_id" => $thai_id]);
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Not Found"]);
}

// ปิดการเชื่อมต่อ
$stmt->close();
$mysqli3->close();
?>
