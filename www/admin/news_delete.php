<?php
ob_start();
include 'middleware.php';
include('db_news.php');

// ตรวจสอบว่ามีการส่งค่า ID ของข่าวมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='text-red-600 text-center mt-10'>Invalid news ID.</div>";
    exit;
}

$news_id = (int)$_GET['id'];

// Query ข้อมูลข่าว
$sql = "SELECT * FROM news WHERE id = $news_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div class='text-red-600 text-center mt-10'>News not found.</div>";
    exit;
}

$row = $result->fetch_assoc();

// ตรวจสอบการยืนยันการลบข่าว
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    $delete_sql = "DELETE FROM news WHERE id = $news_id";

    if ($conn->query($delete_sql) === TRUE) {
        echo "<div class='text-green-600 text-center mt-10'>ลบข่าวเรียบร้อยแล้ว</div>";
         header("Location: news_manage.php");
        exit;
    } else {
        echo "<div class='text-red-600 text-center mt-10'>Error deleting record: " . $conn->error . "</div>";
    }

    $conn->close();
    exit;
}
?>

<div class="max-w-3xl mx-auto mt-16 p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold text-red-600 mb-4">ยืนยันการลบข่าว</h2>
    <p class="mb-6 text-gray-700">คุณแน่ใจหรือไม่ว่าต้องการลบข่าวนี้?</p>

    <div class="border rounded-lg p-4 bg-gray-50 shadow-sm mb-6">
        <h3 class="text-xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($row['title']) ?></h3>
        <div class="prose max-w-none mb-2"><?= $row['content'] ?></div>
        <p class="text-sm text-gray-500">โดย: <?= htmlspecialchars($row['uploader'] ?: 'ไม่ระบุ') ?> | วันที่: <?= date('d/m/Y H:i', strtotime($row['upload_datetime'])) ?></p>
    </div>

    <form action="news_delete.php?id=<?= $news_id ?>" method="post" class="flex gap-4">
        <button type="submit" name="confirm_delete"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded shadow transition">
            🗑️ ลบข่าว
        </button>
        <a href="news_manage.php"
           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded shadow transition">
            ❌ ยกเลิก
        </a>
    </form>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
