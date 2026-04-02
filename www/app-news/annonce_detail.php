<?php
ob_start();
include('../condb/condb.php');

// ตรวจสอบว่า ID ถูกส่งมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Invalid news ID.";
    exit;
}

$news_id = (int)$_GET['id'];

// ตรวจสอบการเชื่อมต่อ $mysqli1
if (!isset($mysqli1) || !$mysqli1 instanceof mysqli) {
    echo "Database connection failed.";
    exit;
}

// ดึงข้อมูลข่าว
$sql = "SELECT * FROM news WHERE id = ? AND is_deleted = 0";
$stmt = $mysqli1->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "ไม่พบข้อมูลข่าวนี้";
    $stmt->close();
    $mysqli1->close();
    exit;
}
$row = $result->fetch_assoc();
$category_id = $row['category_id'];
$stmt->close();

// OG tags (optional)
$og_title = "ข่าว | " . strip_tags($row['title']);
$og_description = mb_substr(strip_tags($row['content']), 0, 150, 'UTF-8') . "...";
$og_image = !empty($row['thumbnail']) ? "https://www.loeitech.ac.th/admin/" . $row['thumbnail'] : "https://www.loeitech.ac.th/images/OG-TAG-Website-Loeitech.jpg";
$og_url = "https://www.loeitech.ac.th" . $_SERVER['REQUEST_URI'];

// ดึงชื่อหมวดหมู่
$category_name = '';
if ($category_id) {
    $category_stmt = $mysqli1->prepare("SELECT name FROM categories WHERE id = ?");
    if ($category_stmt) {
        $category_stmt->bind_param("i", $category_id);
        $category_stmt->execute();
        $cat_result = $category_stmt->get_result();
        if ($cat_result->num_rows > 0) {
            $category_name = $cat_result->fetch_assoc()['name'];
        }
        $category_stmt->close();
    }
}

// ดึงไฟล์แนบ
$sql_attachments = "SELECT * FROM attachments WHERE news_id = ?";
$attachment_stmt = $mysqli1->prepare($sql_attachments);
$attachment_stmt->bind_param("i", $news_id);
$attachment_stmt->execute();
$result_attachments = $attachment_stmt->get_result();
$attachments = $result_attachments->num_rows > 0 ? $result_attachments->fetch_all(MYSQLI_ASSOC) : [];
$attachment_stmt->close();

// ตรวจหา YouTube ในเนื้อหา
preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([\w-]+)/', $row['content'], $matches);
$youtube_video_id = $matches[1] ?? '';

$mysqli1->close();
?>

<nav class="text-sm text-gray-600 mt-[84px] mb-4 max-w-4xl mx-auto px-4">
  <ol class="list-reset flex items-center space-x-2">
    <li><a href="/" class="hover:underline hover:text-green-600">หน้าแรก</a></li>
    <?php if ($category_id && $category_name): ?>
      <li>/</li>
      <li><a href="annonce_list.php?category_id=<?= $category_id ?>" class="hover:underline hover:text-green-600"><?= htmlspecialchars($category_name) ?></a></li>
    <?php endif; ?>
    <li>/</li>
    <li class="text-green-700 font-bold truncate max-w-[150px] sm:max-w-none">รายละเอียด</li>
  </ol>
</nav>

<div class="max-w-4xl mx-auto px-4 py-6 bg-white shadow-xl rounded-lg my-8">
  <div class="mb-6">
    <h2 class="text-3xl font-extrabold text-green-700 mb-2 border-b-2 border-green-200 pb-2">
      <?= htmlspecialchars($row['title']) ?>
    </h2>
    <div class="flex flex-wrap items-center text-sm text-gray-600 space-x-4">
        <p><strong>โดย:</strong> <?= htmlspecialchars($row['uploader']) ?></p>
        <p><strong>วันที่:</strong> <?= date('d/m/Y H:i', strtotime($row['upload_datetime'])) ?></p>
    </div>
  </div>

  <div class="prose max-w-none mb-6">
    <?= $row['content'] ?>
  </div>

  <?php if (!empty($youtube_video_id)): ?>
    <div class="aspect-w-16 aspect-h-9 mb-6 rounded-lg overflow-hidden shadow-lg">
      <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($youtube_video_id) ?>"
              title="YouTube video player"
              class="w-full h-full" 
              frameborder="0" 
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
              allowfullscreen>
      </iframe>
    </div>
  <?php endif; ?>

  <?php if (!empty($attachments)): ?>
    <div class="mt-8 space-y-6 border-t pt-6 border-gray-200">
      <h3 class="text-xl font-bold text-gray-700 flex items-center gap-2">
        <i class="fas fa-paperclip text-green-600"></i> ไฟล์แนบ
      </h3>
      <?php foreach ($attachments as $attachment):
        $raw_path = $attachment['file_path'];
        // Check existence and fallback to 'uploads/newsletter/' if needed
        $clean_path = str_replace(['../', './', 'admin/'], '', $raw_path);
        $clean_path = ltrim($clean_path, '/');
        
        // Default clean path (e.g. uploads/file.pdf or file.pdf)
        if (strpos($clean_path, 'uploads/') !== 0) {
             $possible_path_1 = 'uploads/' . $clean_path;
        } else {
             $possible_path_1 = $clean_path;
        }
        
        // Legacy path (e.g. uploads/newsletter/file.pdf)
        $possible_path_2 = 'uploads/newsletter/' . basename($clean_path);

        // Check 1
        if (file_exists(__DIR__ . "/../" . $possible_path_1)) {
            $final_path = $possible_path_1;
        } 
        // Check 2
        elseif (file_exists(__DIR__ . "/../" . $possible_path_2)) {
            $final_path = $possible_path_2;
        } 
        // Default to Path 1 if neither found (so link shows something)
        else {
            $final_path = $possible_path_1;
        }

        $file_url = "../" . $final_path;

        @$file_name = htmlspecialchars($attachment['file_name']);
        $ext = strtolower(pathinfo($clean_path, PATHINFO_EXTENSION));
      ?>
        <?php if ($ext === 'pdf'): ?>
          <div class="rounded border p-2 shadow-lg">
            <embed src="<?= $file_url ?>" type="application/pdf" width="100%" height="1000px" class="rounded border" />
            <p class="mt-4 text-sm text-gray-600 text-center">
              หาก PDF ไม่แสดง <a href="<?= $file_url ?>" download="<?= $file_name ?>" target="_blank" class="text-blue-600 underline hover:text-blue-800 font-medium">คลิกที่นี่เพื่อดาวน์โหลดไฟล์ PDF</a>
            </p>
          </div>
        <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
          <div class="mb-4 text-center p-4 bg-gray-50 rounded-lg shadow-inner">
            <a href="<?= $file_url ?>" data-lightbox="news-<?= $news_id ?>" data-title="<?= $file_name ?>">
              <img src="<?= $file_url ?>" alt="<?= $file_name ?>"
                   class="rounded shadow-md w-full max-h-[500px] object-contain mx-auto hover:opacity-90 transition duration-300 cursor-zoom-in">
            </a>
            <!-- <p class="text-sm text-gray-600 mt-2 font-medium"><?= $file_name ?></p> -->
          </div>

        <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi'])): ?>
          <div class="mb-6">
            <div class="bg-black rounded-xl overflow-hidden shadow-2xl">
              <video 
                controls 
                class="w-full aspect-video"
                preload="metadata"
                controlsList="nodownload" 
              >
                <source src="<?= $file_url ?>" type="video/<?= $ext === 'mp4' ? 'mp4' : ($ext === 'webm' ? 'webm' : ($ext === 'ogg' ? 'ogg' : 'mp4')) ?>">
                เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอ <a href="<?= $file_url ?>" class="text-blue-400 underline">ดาวน์โหลดวิดีโอ</a>
              </video>
            </div>
            <div class="flex justify-between items-center mt-3 px-2">
              <!-- <p class="text-sm text-gray-600 font-medium"><?= $file_name ?></p> -->
              <a href="<?= $file_url ?>" download="<?= $file_name ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1 transition">
                <i class="fas fa-download"></i>
                ดาวน์โหลดวิดีโอ
              </a>
            </div>
          </div>

        <?php elseif (in_array($ext, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar'])): ?>
          <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 text-center mb-4 shadow-sm">
            <?php 
              $icon = 'fas fa-file';
              if (strpos($ext, 'doc') !== false) $icon = 'fas fa-file-word text-blue-500';
              elseif (strpos($ext, 'xls') !== false) $icon = 'fas fa-file-excel text-green-500';
              elseif (strpos($ext, 'ppt') !== false) $icon = 'fas fa-file-powerpoint text-red-500';
              elseif (in_array($ext, ['zip', 'rar'])) $icon = 'fas fa-file-archive text-purple-500';
            ?>
            <i class="<?= $icon ?> text-3xl mb-2"></i>
            <!-- <p class="text-sm text-gray-700 font-medium mb-2"><?= $file_name ?> (<?= strtoupper($ext) ?>)</p> -->
            <a href="<?= $file_url ?>" download="<?= $file_name ?>" target="_blank" class="text-blue-600 underline hover:text-blue-800 font-semibold flex items-center justify-center gap-2">
                <i class="fas fa-download"></i> ดาวน์โหลดไฟล์แนบ
            </a>
          </div>
        <?php else: ?>
          <div class="p-4 bg-gray-100 rounded border text-center mb-4 shadow-sm">
            <i class="fas fa-file text-gray-500 text-3xl mb-2"></i>
            <!-- <p class="text-sm text-gray-700 font-medium mb-2"><?= $file_name ?></p> -->
            <a href="<?= $file_url ?>" download="<?= $file_name ?>" target="_blank" class="text-blue-600 underline hover:text-blue-800 font-semibold">ดาวน์โหลดไฟล์แนบ</a>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="mt-8 pt-4 border-t border-gray-200 text-center">
    <a href="annonce_list.php<?= ($category_id ? '?category_id=' . $category_id : '') ?>"
      class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-full shadow-lg transition duration-300 transform hover:scale-[1.02]">
      <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับไปรายการข่าว
    </a>
  </div>
</div>



<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
/* ----------------------------------------------------- */
/* Global Centering Fixes (for responsive navigation) */
/* ----------------------------------------------------- */

/* Breadcrumb Navigation: Max-width และจัดกลางด้วย mx-auto ถูกใช้ใน HTML แล้ว */
.max-w-4xl.mx-auto {
    /* ใช้ mx-auto ใน HTML เพื่อจัดกึ่งกลาง */
}

/* ----------------------------------------------------- */
/* Video Player Enhancements */
/* ----------------------------------------------------- */
.aspect-w-16 {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
    overflow: hidden;
}

.aspect-w-16 iframe, .aspect-w-16 video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

video {
  border-radius: 8px;
  background: #000;
}

video:focus {
  outline: none;
}

/* ปรับปรุงการแสดงผลของ controls บนเบราว์เซอร์ต่างๆ */
video::-webkit-media-controls-panel {
  background: rgba(0,0,0,0.5); /* ปรับให้ดูนุ่มนวลขึ้น */
}

video::-webkit-media-controls-play-button,
video::-webkit-media-controls-volume-slider,
video::-webkit-media-controls-mute-button {
  filter: none; /* ยกเลิก invert เพื่อให้เป็นไปตาม Theme ของเบราว์เซอร์ */
}

/* ----------------------------------------------------- */
/* Content Formatting (Prose) */
/* ----------------------------------------------------- */
/* หากเนื้อหาถูก render ด้วย editor และมี class 'prose' */
.prose {
    color: #374151; /* gray-700 */
    line-height: 1.75;
}

.prose h1, .prose h2, .prose h3, .prose h4 {
    color: #065f46; /* green-800 */
    font-weight: bold;
}

.prose a {
    color: #10b981; /* green-500 */
    text-decoration: underline;
    transition: color 0.2s;
}

.prose a:hover {
    color: #059669; /* green-600 */
}

/* Responsive adjustments */
@media (max-width: 768px) {
  video {
    max-height: 400px;
  }
}
</style>

<?php
$content = ob_get_clean();
include '../base.php';
?>