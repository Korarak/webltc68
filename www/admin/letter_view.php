<?php
include 'middleware.php';
include 'db_letter.php';

$id = $_GET['id'];
$sql = "SELECT * FROM letters WHERE letter_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$letter = $result->fetch_assoc();

$stmt->close();
$conn->close();

$title = "ดูจดหมายข่าว";
ob_start();
?>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">ดูจดหมายข่าว</h1>

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">หัวข้อจดหมาย</label>
        <input type="text" class="w-full px-4 py-2 border rounded bg-gray-100 text-gray-700" 
               value="<?= htmlspecialchars($letter['letter_title']); ?>" readonly>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">ไฟล์แนบ</label>
        <div class="w-full">
            <?php $view_path = "../" . $letter['letter_attenmath']; ?>
            <img src="<?= htmlspecialchars($view_path); ?>" 
                 class="max-w-full h-auto border rounded shadow" 
                 alt="Attached File">
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 font-semibold mb-2">วันที่สร้าง</label>
        <input type="text" class="w-full px-4 py-2 border rounded bg-gray-100 text-gray-700" 
               value="<?= htmlspecialchars($letter['letter_createtime']); ?>" readonly>
    </div>

    <div class="mb-6">
        <label class="block text-gray-700 font-semibold mb-2">ผู้สร้าง</label>
        <input type="text" class="w-full px-4 py-2 border rounded bg-gray-100 text-gray-700" 
               value="<?= htmlspecialchars($letter['letter_made']); ?>" readonly>
    </div>

    <div class="text-center">
        <a href="letter_manage.php" 
           class="inline-block px-6 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded shadow">
            ← กลับหน้ารายการ
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
