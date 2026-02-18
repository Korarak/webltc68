<?php
include 'middleware.php';
include 'db_letter.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $user = $_POST['user'];

    if (mb_strlen($title) > 255) {
        echo "<script>alert('Error: หัวข้อจดหมายต้องมีความยาวไม่เกิน 255 ตัวอักษร'); window.history.back();</script>";
        exit;
    }

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Change to ../uploads/newsletter/ (Physical)
        $target_dir = "../uploads/newsletter/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        // Rename logic
        $date = date('Ymd_His');
        $filename = "letter_" . $date . "_" . uniqid() . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION); 
        
        $target_file = $target_dir . $filename;
        $db_path = "uploads/newsletter/" . $filename; // Store as agreed: uploads/newsletter/file

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // บีบอัดภาพอัตโนมัติ
            require_once __DIR__ . '/../includes/optimize_image.php';
            optimizeImage($target_file, 800, 80);

            $sql = "INSERT INTO letters (letter_title, letter_attenmath, letter_made) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $title, $db_path, $user);

            if ($stmt->execute()) {
                header("Location: letter_manage.php");
                exit;
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "Error: File upload failed";
    }
}
$conn->close();
?>
