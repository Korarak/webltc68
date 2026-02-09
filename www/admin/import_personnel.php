<?php
include 'middleware.php';
require '../condb/condb.php';
$mysqli3->set_charset("utf8mb4");

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $fileTmpPath = $_FILES['csv_file']['tmp_name'];
        $file = fopen($fileTmpPath, 'r');

        if (!$file) {
            $message = "<div class='bg-red-100 text-red-700 p-4 rounded'>❌ ไม่สามารถอ่านไฟล์ CSV ได้</div>";
        } else {
            $success = 0;
            $duplicate = 0;
            $error = 0;
            $rowNumber = 0;

            while (($row = fgetcsv($file, 1000, ",")) !== FALSE) {
                $rowNumber++;

                if ($rowNumber < 4) continue;

                $thai_id = trim($row[1]);
                $fullname = trim($row[2]);

                if (!empty($thai_id) && !empty($fullname)) {
                    @$fullname = mb_convert_encoding($fullname, "UTF-8", "auto");

                    $stmt = $mysqli3->prepare("SELECT COUNT(*) FROM personel_data WHERE thai_id = ?");
                    $stmt->bind_param("s", $thai_id);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();

                    if ($count == 0) {
                        $stmt = $mysqli3->prepare("INSERT INTO personel_data (thai_id, fullname) VALUES (?, ?)");
                        $stmt->bind_param("ss", $thai_id, $fullname);
                        if ($stmt->execute()) {
                            $success++;
                        } else {
                            $error++;
                        }
                        $stmt->close();
                    } else {
                        $duplicate++;
                    }
                }
            }

            fclose($file);

            $message = "<div class='bg-green-100 text-green-800 p-4 rounded'>
                ✅ เพิ่มใหม่: $success รายการ<br>
                ❌ ซ้ำ: $duplicate<br>
                ⚠️ ข้อผิดพลาด: $error
            </div>";
        }
    } else {
        $message = "<div class='bg-yellow-100 text-yellow-800 p-4 rounded'>⚠️ กรุณาเลือกไฟล์ CSV</div>";
    }
}
?>

<?php ob_start(); ?>
<div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-md rounded">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">📥 นำเข้าข้อมูลบุคลากรจาก CSV</h2>

    <?php if (!empty($message)) echo $message; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4 mt-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">เลือกไฟล์ CSV</label>
            <input type="file" name="csv_file" class="block w-full border border-gray-300 rounded p-2">
        </div>
        <div class="flex justify-between items-center">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
                📂 อัปโหลดและนำเข้า
            </button>
            <a href="personel_manage.php" class="text-gray-600 hover:underline">← ย้อนกลับ</a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
