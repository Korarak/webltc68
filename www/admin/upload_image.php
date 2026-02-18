<?php
include 'middleware.php';

// Validates and moves uploaded files for Editor.js Image Tool
error_reporting(0);
header('Content-Type: application/json');

$target_dir = "../uploads/editor_images/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$response = ['success' => 0];

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $name = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $new_name = uniqid() . '_' . time() . '.' . $ext;
    $target_file = $target_dir . $new_name;

    // Validation
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed)) {
        if (move_uploaded_file($tmp_name, $target_file)) {
            // บีบอัดภาพอัตโนมัติ
            require_once __DIR__ . '/../includes/optimize_image.php';
            optimizeImage($target_file, 1200, 80);

            $response = [
                'success' => 1,
                'file' => [
                    // Return root-relative path so it works from /admin/ and /
                    'url' => '/uploads/editor_images/' . $new_name, 
                ]
            ];
        }
    }
}

echo json_encode($response);
