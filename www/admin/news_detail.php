<?php
include 'middleware.php';
ob_start();
include('db_news.php');

// ตรวจสอบ ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='text-red-600 text-center mt-10'>ไม่พบข่าวที่ต้องการดู</div>";
    exit;
}

$news_id = (int) $_GET['id'];

// ดึงข่าว
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ? AND is_deleted = 0");
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "<div class='text-red-600 text-center mt-10'>ไม่พบข่าวที่ต้องการดู</div>";
    exit;
}
$row = $result->fetch_assoc();
$stmt->close();

// ดึงไฟล์แนบ
$stmt_files = $conn->prepare("SELECT * FROM attachments WHERE news_id = ?");
$stmt_files->bind_param("i", $news_id);
$stmt_files->execute();
$result_attachments = $stmt_files->get_result();
$attachments = $result_attachments->fetch_all(MYSQLI_ASSOC);
$stmt_files->close();

$conn->close();
?>

<div class="max-w-4xl mx-auto p-6">
    <!-- ปุ่มกลับด้านบน -->
    <div class="mb-4">
        <a href="news_manage.php" class="inline-block bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
            ← ย้อนกลับ
        </a>
    </div>

    <!-- ข้อมูลข่าว -->
    <h2 class="text-2xl font-bold text-gray-800 mb-2"><?= htmlspecialchars($row['title']) ?></h2>
    <p class="text-sm text-gray-500 mb-1">
        <strong>ผู้เขียน:</strong> <?= htmlspecialchars($row['uploader'] ?: 'ไม่ระบุ') ?>
    </p>
    <p class="text-sm text-gray-500 mb-4">
        <strong>วันที่ลงข่าว:</strong> <?= date('d/m/Y H:i', strtotime($row['upload_datetime'])) ?>
    </p>

    <!-- เนื้อหาข่าว (จาก Quill Editor) -->
    <div class="prose max-w-none mb-6">
        <?= $row['content'] ?>
    </div>

    <!-- YouTube (ตรวจหาและแสดงอัตโนมัติ) -->
    <?php
    preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([\w-]+)/', $row['content'], $matches);
    $youtube_video_id = $matches[1] ?? '';

    if (!empty($youtube_video_id)): ?>
        <div class="mb-6">
            <iframe class="w-full aspect-video rounded shadow"
                src="https://www.youtube.com/embed/<?= htmlspecialchars($youtube_video_id) ?>"
                frameborder="0" allowfullscreen></iframe>
        </div>
    <?php endif; ?>

    <!-- ไฟล์แนบ -->
    <?php if (!empty($attachments)): ?>
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">ไฟล์แนบ:</h3>

            <?php foreach ($attachments as $file): ?>
                <?php
                // Path Logic: Preserve subdirectories, ensure relative to www/
                $clean_path = str_replace(['../', './', 'admin/'], '', $file['file_path']);
                $clean_path = ltrim($clean_path, '/');
                if (strpos($clean_path, 'uploads/') !== 0) $clean_path = 'uploads/' . $clean_path;
                $file_url = htmlspecialchars("../" . $clean_path);
                
                @$file_name = htmlspecialchars($file['file_name']);
                $ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
                ?>

                <?php if ($ext === 'pdf'): ?>
                    <div class="mb-6">
                        <embed src="<?= $file_url ?>" type="application/pdf" width="100%" height="1200px" class="rounded border" />
                        <p class="mt-2 text-sm text-gray-600">
                            หากไฟล์ไม่แสดงผล <a href="<?= $file_url ?>" target="_blank" class="text-blue-600 underline">ดาวน์โหลด PDF</a>
                        </p>
                    </div>

                <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                    <div class="mb-4 text-center">
                        <a href="<?= $file_url ?>" target="_blank">
                            <img src="<?= $file_url ?>" alt="<?= $file_name ?>"
                                class="rounded shadow w-full max-h-[500px] object-contain hover:opacity-80 transition">
                        </a>
                        <p class="text-sm text-gray-600 mt-1"><?= $file_name ?></p>
                    </div>

                <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                    <!-- ส่วนแสดงผลวิดีโอ -->
                    <div class="mb-6">
                        <div class="bg-black rounded-lg overflow-hidden shadow-lg">
                            <video 
                                controls 
                                class="w-full aspect-video"
                                poster="" <!-- สามารถเพิ่ม thumbnail ได้ที่นี่ -->
                            >
                                <source src="<?= $file_url ?>" type="video/<?= $ext === 'mp4' ? 'mp4' : ($ext === 'webm' ? 'webm' : 'ogg') ?>">
                                เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอ
                                <a href="<?= $file_url ?>" class="text-blue-400 underline">ดาวน์โหลดวิดีโอ</a>
                            </video>
                        </div>
                        <div class="flex justify-between items-center mt-2 px-2">
                            <p class="text-sm text-gray-600"><?= $file_name ?></p>
                            <a href="<?= $file_url ?>" download class="text-blue-600 hover:text-blue-800 text-sm flex items-center gap-1">
                                <i class="fas fa-download"></i>
                                ดาวน์โหลดวิดีโอ
                            </a>
                        </div>
                    </div>

                <?php elseif (in_array($ext, ['doc', 'docx'])): ?>
                    <div class="p-4 bg-gray-100 rounded border text-center mb-4">
                        <i class="fas fa-file-word text-blue-500 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-700 mb-2"><?= $file_name ?></p>
                        <a href="<?= $file_url ?>" target="_blank" class="text-blue-600 underline">ดาวน์โหลดไฟล์ Word</a>
                    </div>

                <?php else: ?>
                    <div class="p-4 bg-gray-100 rounded border text-center mb-4">
                        <i class="fas fa-file text-gray-500 text-3xl mb-2"></i>
                        <p class="text-sm text-gray-700 mb-2"><?= $file_name ?></p>
                        <a href="<?= $file_url ?>" target="_blank" class="text-blue-600 underline">ดาวน์โหลดไฟล์แนบ</a>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- ปุ่มกลับด้านล่าง -->
    <div class="mt-6">
        <a href="news_manage.php" class="inline-block bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
            ← ย้อนกลับ
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>