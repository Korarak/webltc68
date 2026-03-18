<?php
$title = "เนื้อหาเว็บเพจ";
ob_start();
include '../condb/condb.php';

// ตรวจสอบ slug เช่น /page.php?slug=about-us
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    echo "<div class='max-w-4xl mx-auto text-center mt-20 text-gray-600'>
            <h2 class='text-2xl font-semibold mb-4'>ไม่พบหน้าที่คุณต้องการ</h2>
            <p>กรุณาตรวจสอบลิงก์อีกครั้ง</p>
          </div>";
    $content = ob_get_clean();
    include '../base.php';
    exit;
}

$slug = $_GET['slug'];

// ดึงข้อมูลเพจ
$stmt = $mysqli4->prepare("SELECT * FROM web_pages WHERE slug = ? AND visible = 1 LIMIT 1");
$stmt->bind_param("s", $slug);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

if (!$page) {
    echo "<div class='max-w-4xl mx-auto text-center mt-20 text-gray-600'>
            <h2 class='text-2xl font-semibold mb-4'>ไม่พบหน้าที่คุณต้องการ</h2>
            <p>เพจนี้อาจถูกลบหรือซ่อนอยู่</p>
          </div>";
    $content = ob_get_clean();
    include '../base.php';
    exit;
}

// ตั้งค่า title จากชื่อเพจ
$title = htmlspecialchars($page['title']);
?>

<!-- ส่วนเนื้อหาหลัก -->
<main class="max-w-6xl mx-auto px-4 py-8 mt-[72px]">
  <!-- Header -->
  <div class="bg-blue-600 text-white text-center py-4 rounded shadow mb-6">
    <h1 class="text-2xl font-semibold"><?= htmlspecialchars($page['title']) ?></h1>
  </div>

  <!-- Thumbnail -->
  <?php if (!empty($page['thumbnail'])): ?>
    <div class="flex justify-center mb-8">
      <img src="../<?= htmlspecialchars($page['thumbnail']) ?>" 
           alt="<?= htmlspecialchars($page['title']) ?>" 
           class="rounded-lg shadow max-h-[400px] w-auto object-cover">
    </div>
  <?php endif; ?>

  <!-- Page Content -->
  <!-- Page Content -->
  <div class="bg-white rounded-lg shadow-md p-8 leading-relaxed text-gray-800 content-area">
    <?= $page['content'] ?>
  </div>

  <!-- Meta Information -->
  <div class="mt-8 pt-6 border-t border-gray-200">
    <div class="flex flex-wrap justify-between items-center text-sm text-gray-500">
      <div>
        <strong>คำอธิบาย:</strong> 
        <?= $page['meta_description'] ? htmlspecialchars($page['meta_description']) : 'ไม่มีคำอธิบาย' ?>
      </div>
      <div>
        อัปเดตล่าสุด: <?= date('d/m/Y H:i', strtotime($page['updated_at'])) ?>
      </div>
    </div>
  </div>
</main>

<style>
/* 
  Reset and base styles for Editor.js content that aren't perfectly covered by Tailwind preflight
  but avoiding aggressive overrides so that inline styles (e.g., text-center) work.
*/
.content-area {
    /* Matches Editor.js base styling */
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    font-size: 16px;
    line-height: 1.6;
}

/* Ensure bold and italic work normally inside paragraphs */
.content-area b, .content-area strong {
    font-weight: 700;
}
.content-area i, .content-area em {
    font-style: italic;
}

/* Base link styling */
.content-area a {
    color: #2563eb;
    text-decoration: underline;
}
.content-area a.no-underline {
    text-decoration: none;
}
.content-area a:hover {
    color: #1d4ed8;
}

/* Lists that don't have inline tailwind */
.content-area ul {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}
.content-area ol {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}
.content-area li {
    margin-bottom: 0.25rem;
}

.content-area img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

/* Table styling for simple tables */
.content-area table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}
.content-area th, .content-area td {
    border: 1px solid #e5e7eb;
    padding: 0.5rem 0.75rem;
    text-align: left;
}
.content-area th {
    background-color: #f9fafb;
    font-weight: 600;
}

/* Blockquote */
.content-area blockquote {
    border-left: 4px solid #3b82f6; 
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
    color: #4b5563;
    background-color: #f3f4f6;
    border-radius: 0 0.5rem 0.5rem 0;
}

.content-area code {
    background-color: #f3f4f6;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 0.875em;
}

/* Responsive improvements */
@media (max-width: 640px) {
    .content-area {
        font-size: 15px;
    }
}
</style>

<?php
$content = ob_get_clean();
include '../base.php';