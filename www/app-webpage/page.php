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
<div class="bg-white rounded-lg shadow-md p-6 leading-relaxed text-gray-800 
            prose prose-lg max-w-none 
            prose-headings:text-gray-800
            prose-p:text-gray-700
            prose-ul:list-disc prose-ul:pl-6
            prose-ol:list-decimal prose-ol:pl-6
            prose-li:my-1
            prose-a:text-blue-600 prose-a:no-underline hover:prose-a:text-blue-800
            prose-blockquote:border-l-blue-400
            prose-table:border prose-table:border-gray-300
            prose-th:bg-gray-100
            prose-img:rounded-lg
            prose-strong:text-gray-800
            prose-em:text-gray-700
            content-area">
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
/* Editor.js / Notion-like Styling Override */
.content-area {
    /* Use system fonts or Inter to match Notion/Editor.js look, overriding global IBM Plex Sans Thai */
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
    line-height: 1.7;
    font-size: 1.1em; /* Slightly larger for readability */
    color: #37352f; /* Notion text color */
}

.content-area h1 {
    font-size: 2.2em;
    font-weight: 700;
    margin: 1em 0 0.5em;
    color: #111;
    line-height: 1.3;
}

.content-area h2 {
    font-size: 1.75em;
    font-weight: 600;
    margin: 1.4em 0 0.4em;
    color: #111;
    line-height: 1.3;
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 0.3em;
}

.content-area h3 {
    font-size: 1.4em;
    font-weight: 600;
    margin: 1.2em 0 0.2em;
    color: #111;
    line-height: 1.3;
}

.content-area p {
    margin: 0.8em 0;
    min-height: 1em; /* Handle empty paragraphs */
}

/* สไตล์สำหรับลิสต์ */
.content-area ul {
    list-style-type: disc;
    margin: 1em 0;
    padding-left: 2em;
}

.content-area ol {
    list-style-type: decimal;
    margin: 1em 0;
    padding-left: 2em;
}

.content-area li {
    margin: 0.5em 0;
    display: list-item;
}

/* สไตล์สำหรับ nested lists */
.content-area ul ul, 
.content-area ol ul {
    list-style-type: circle;
    margin: 0.5em 0;
}

.content-area ol ol,
.content-area ul ol {
    list-style-type: lower-latin;
    margin: 0.5em 0;
}

.content-area a {
    color: #2563eb;
    text-decoration: underline;
}

.content-area a:hover {
    color: #1d4ed8;
}

.content-area img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
}

.content-area table {
    width: 100%;
    border-collapse: collapse;
    margin: 1em 0;
}

.content-area table, 
.content-area th, 
.content-area td {
    border: 1px solid #d1d5db;
}

.content-area th, 
.content-area td {
    padding: 0.5em 1em;
    text-align: left;
}

.content-area th {
    background-color: #f3f4f6;
    font-weight: bold;
}

.content-area blockquote {
    border-left: 4px solid #d1d5db;
    padding-left: 1em;
    margin: 1em 0;
    font-style: italic;
    color: #6b7280;
}

.content-area code {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 0.25rem;
    font-family: monospace;
}

.content-area pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1em;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1em 0;
}

.content-area pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
}
</style>

<?php
$content = ob_get_clean();
include '../base.php';