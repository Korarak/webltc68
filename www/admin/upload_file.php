<?php
include 'middleware.php';

error_reporting(0);
header('Content-Type: application/json');

$target_dir = "../uploads/editor_files/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$response = ['success' => 0];

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $tmp_name = $_FILES['file']['tmp_name'];
    $name = $_FILES['file']['name'];
    $size = $_FILES['file']['size'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $new_name = uniqid() . '_' . time() . '.' . $ext;
    $target_file = $target_dir . $new_name;

    // Allowed extensions (exclude php, etc)
    $denied = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'js'];
    if (!in_array($ext, $denied)) {
        if (move_uploaded_file($tmp_name, $target_file)) {
            $response = [
                'success' => 1,
                'file' => [
                    // Return root-relative path so it works from /admin/ and /
                    'url' => '/uploads/editor_files/' . $new_name,
                    'name' => $name,
                    'size' => $size, // Editorjs attaches handles bytes
                    'extension' => $ext
                ]
            ];
        } else {
             $response['message'] = 'Failed to move uploaded file.';
        }
    } else {
         $response['message'] = 'File type not allowed.';
    }
} else {
     $response['message'] = 'No file uploaded or upload error.';
}

echo json_encode($response);
?>
