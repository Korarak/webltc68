<?php
include 'middleware.php';
$title = "แก้ไขจดหมายข่าว";
ob_start();
require 'db_letter.php';

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $title_input = $_POST['title'];
    $old_file = $_POST['old_file'];
    $target_file = $old_file;

    if (mb_strlen($title_input) > 255) {
        echo "<script>alert('Error: หัวข้อจดหมายต้องมีความยาวไม่เกิน 255 ตัวอักษร'); window.history.back();</script>";
        exit;
    }

    // Handle File Upload
    if (!empty($_FILES['file']['name'])) {
        $target_dir = "../uploads/newsletter/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

        $date = date('Ymd_His');
        $filename = "letter_" . $date . "_" . uniqid() . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        $target_physical = $target_dir . $filename;
        $target_db = "uploads/newsletter/" . $filename;
        
        if(move_uploaded_file($_FILES["file"]["tmp_name"], $target_physical)) {
            // Remove old file if exists
            // Fix path relative to admin/
            $old_file_physical = "../" . $old_file;
            if (file_exists($old_file_physical) && is_file($old_file_physical)) {
                unlink($old_file_physical);
            }
            $target_file = $target_db; // Update variable for DB save
        } else {
             // Debugging: Failed to move
             echo "<script>alert('Error: Failed to move uploaded file. Check permissions on uploads/newsletter/'); window.history.back();</script>";
             exit;
        }
    }

    $sql = "UPDATE letters SET letter_title=?, letter_attenmath=? WHERE letter_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $title_input, $target_file, $id);

    if ($stmt->execute()) {
        header("Location: letter_manage.php");
        exit;
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// Fetch Data
$id = $_GET['id'] ?? 0;
$sql = "SELECT * FROM letters WHERE letter_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$letter = $result->fetch_assoc();

if (!$letter) {
    echo "<script>alert('ไม่พบข้อมูล'); window.location='letter_manage.php';</script>";
    exit;
}
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="letter_manage.php" class="bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-200 p-2.5 rounded-xl shadow-sm transition-all">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">แก้ไขจดหมายข่าว</h1>
            <p class="text-gray-500 text-sm">แก้ไขข้อมูลจดหมายข่าว: <?= htmlspecialchars($letter['letter_title']) ?></p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8">
            <form action="letter_edit.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="id" value="<?= htmlspecialchars($letter['letter_id']) ?>">
                <input type="hidden" name="old_file" value="<?= htmlspecialchars($letter['letter_attenmath']) ?>">

                <!-- Title Input -->
                <div class="space-y-2">
                    <label for="title" class="text-sm font-semibold text-gray-700">หัวข้อจดหมายข่าว <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-heading text-gray-400"></i>
                        </div>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($letter['letter_title']) ?>" required maxlength="255"
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all">
                    </div>
                </div>

                <!-- Current File -->
                <div class="space-y-2">
                     <label class="text-sm font-semibold text-gray-700">ไฟล์ที่แนบอยู่</label>
                     <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex items-center gap-3">
                        <?php 
                            $view_path = "../" . $letter['letter_attenmath'];
                        ?>
                        <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $letter['letter_attenmath'])): ?>
                            <img src="<?= htmlspecialchars($view_path) ?>" class="w-16 h-16 object-cover rounded-lg border border-gray-300">
                        <?php else: ?>
                            <div class="w-16 h-16 bg-white rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 text-2xl">
                                <i class="fas fa-file"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-sm text-gray-600 break-all"><?= basename($letter['letter_attenmath']) ?></p>
                            <a href="<?= htmlspecialchars($view_path) ?>" target="_blank" class="text-xs text-blue-600 hover:underline">ดาวน์โหลด/ดู</a>
                        </div>
                     </div>
                </div>

                <!-- New File Upload -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">เปลี่ยนไฟล์แนบ (ถ้ามี)</label>
                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition-colors cursor-pointer relative group"
                         onclick="document.getElementById('file').click()">
                        <div class="space-y-1 text-center">
                            <div class="w-12 h-12 mx-auto bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-cloud-upload-alt text-xl"></i>
                            </div>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file" class="relative cursor-pointer bg-transparent rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>เลือกไฟล์ใหม่</span>
                                    <input id="file" name="file" type="file" class="sr-only" onchange="showFileName(this)">
                                </label>
                            </div>
                            <p id="file-name" class="text-sm font-medium text-blue-600 mt-2 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-blue-200 hover:shadow-xl transition-all font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> บันทึกการแก้ไข
                    </button>
                    <a href="letter_manage.php" class="px-6 py-2.5 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showFileName(input) {
    const name = input.files[0] ? input.files[0].name : '';
    const display = document.getElementById('file-name');
    if(name) {
        display.textContent = 'ไฟล์ที่เลือก: ' + name;
        display.classList.remove('hidden');
    } else {
        display.classList.add('hidden');
    }
}
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
