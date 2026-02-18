<?php
include 'middleware.php';
?>
<?php
function thai_datetime($datetime) {
    setlocale(LC_TIME, 'th_TH.UTF-8', 'Thai_Thailand.UTF-8', 'th_TH');
    $timestamp = strtotime($datetime);
    return date('l, d F Y H:i:s', $timestamp);
}
?>
<?php
ob_start();
include 'db_letter.php';

$carousel_id = $_GET['carousel_id'] ?? null;
if (!$carousel_id || !is_numeric($carousel_id)) die("Invalid carousel ID");

$sql = "SELECT * FROM carousel WHERE carousel_id='$carousel_id'";
$result = $conn->query($sql);
$carousel = $result->fetch_assoc();
if (!$carousel) die("Carousel slide not found");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carousel_no = $_POST['carousel_no'] ?? null;
    $carousel_text1 = $_POST['carousel_text1'] ?? null;
    $carousel_text2 = $_POST['carousel_text2'] ?? null;

    if (is_numeric($carousel_no)) {
        $carousel_no = intval($carousel_no);
        $sql = "UPDATE carousel SET carousel_no='$carousel_no', carousel_text1='$carousel_text1', carousel_text2='$carousel_text2'";

        // Update paths for consistency (../uploads/carousel/)
        if (isset($_FILES['carousel_pic']) && $_FILES['carousel_pic']['error'] == 0) {
            $target_dir = "../uploads/carousel/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
            
            $file_ext = pathinfo($_FILES['carousel_pic']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_ext;
            
            $physical_path = $target_dir . $filename;
            $db_path = "uploads/carousel/" . $filename;

            if (move_uploaded_file($_FILES['carousel_pic']['tmp_name'], $physical_path)) {
                // บีบอัดภาพอัตโนมัติ
                require_once __DIR__ . '/../includes/optimize_image.php';
                optimizeImage($physical_path, 1600, 82);

                // Delete old file
                $old_physical = "../" . $carousel['carousel_pic'];
                if (file_exists($old_physical) && is_file($old_physical)) unlink($old_physical);
                
                $sql .= ", carousel_pic='$db_path'";
            } else {
                echo "Error uploading file.";
            }
        }

        $sql .= " WHERE carousel_id='$carousel_id'";

        if ($conn->query($sql) === TRUE) {
            header("Location: carousel_manage.php");
            exit;
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Invalid data.";
    }
}
?>

<div class="max-w-2xl mx-auto mt-8 bg-white p-6 shadow rounded-lg">
    <h1 class="text-2xl font-bold mb-4">แก้ไขภาพสไลด์</h1>
    <form method="post" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label for="carousel_no" class="block font-medium">ลำดับของป้าย</label>
            <input type="number" id="carousel_no" name="carousel_no"
                   class="w-full border rounded px-3 py-2"
                   value="<?= htmlspecialchars($carousel['carousel_no']) ?>" required>
        </div>
        <div>
            <label for="carousel_pic" class="block font-medium">อัปโหลดภาพใหม่ (ถ้าต้องการ)</label>
            <input type="file" id="carousel_pic" name="carousel_pic" accept="image/*"
                   class="w-full border rounded px-3 py-2">
            <?php 
                $view_path = $carousel['carousel_pic'];
                if (strpos($view_path, 'http') !== 0 && strpos($view_path, '/') !== 0) {
                    $view_path = "../" . $view_path;
                }
            ?>
            <img src="<?= htmlspecialchars($view_path) ?>"
                 alt="Current Image" class="w-48 h-auto mt-2 rounded shadow">
        </div>
        <div>
            <label for="carousel_text1" class="block font-medium">ข้อความบรรทัดบน</label>
            <input type="text" id="carousel_text1" name="carousel_text1"
                   class="w-full border rounded px-3 py-2"
                   value="<?= htmlspecialchars($carousel['carousel_text1']) ?>">
        </div>
        <div>
            <label for="carousel_text2" class="block font-medium">ข้อความบรรทัดล่าง</label>
            <input type="text" id="carousel_text2" name="carousel_text2"
                   class="w-full border rounded px-3 py-2"
                   value="<?= htmlspecialchars($carousel['carousel_text2']) ?>">
        </div>
        <div class="flex gap-4">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow">
                💾 บันทึกการแก้ไข
            </button>
            <a href="carousel_manage.php"
               class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded shadow">
                🔙 ย้อนกลับ
            </a>
        </div>
    </form>
</div>

<?php $conn->close(); ?>
<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
