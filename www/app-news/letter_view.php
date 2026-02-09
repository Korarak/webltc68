<?php
ob_start();
require '../condb/condb.php';

$id = $_GET['id'];

$sql = "SELECT * FROM letters WHERE letter_id=?";
$stmt = $mysqli2->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$letter = $result->fetch_assoc();

$stmt->close();
?>
<!-- Breadcrumb Navigation -->
<nav class="text-sm text-gray-600 mb-4 max-w-3xl mx-auto">
  <ol class="list-reset flex items-center space-x-2">
    <li><a href="/" class="hover:underline hover:text-green-600">หน้าแรก</a></li>
    <li>/</li>
    <li><a href="letter_list.php" class="hover:underline hover:text-green-600">จดหมายข่าว</a></li>
    <li>/</li>
    <li class="text-gray-400">รายละเอียด</li>
  </ol>
</nav>

<div class="max-w-3xl mx-auto bg-white p-6 mt-8 rounded-lg shadow">
  <!-- ปุ่มกลับ -->
  <div class="mb-6">
    <a href="letter_list.php"
       class="inline-block bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded transition">
      <i class="fas fa-arrow-left mr-1"></i> กลับ
    </a>
  </div>

  <!-- หัวข้อ -->
  <div class="mb-4">
    <label class="block text-gray-700 font-medium mb-1">หัวข้อจดหมายข่าว</label>
    <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700" 
           value="<?= htmlspecialchars($letter['letter_title']); ?>" readonly>
  </div>

  <!-- รูปภาพ -->
  <div class="mb-4">
    <label class="block text-gray-700 font-medium mb-1">ภาพประกอบ</label>
    <div class="w-full overflow-hidden rounded border">
      <img src="/<?= htmlspecialchars($letter['letter_attenmath']); ?>"
           alt="Attached File"
           class="w-full h-auto object-contain">
    </div>
  </div>

  <!-- วันที่สร้าง -->
  <div class="mb-4">
    <label class="block text-gray-700 font-medium mb-1">สร้างเมื่อ</label>
    <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700" 
           value="<?= htmlspecialchars($letter['letter_createtime']); ?>" readonly>
  </div>

  <!-- ผู้สร้าง -->
  <div class="mb-6">
    <label class="block text-gray-700 font-medium mb-1">สร้างโดย</label>
    <input type="text" class="w-full border rounded px-3 py-2 bg-gray-100 text-gray-700" 
           value="<?= htmlspecialchars($letter['letter_made']); ?>" readonly>
  </div>

  <!-- ปุ่มกลับล่าง -->
  <div class="text-center">
    <a href="letter_showall.php"
       class="inline-block bg-red-600 hover:bg-red-700 text-white text-sm px-5 py-2 rounded transition">
      <i class="fas fa-arrow-left mr-1"></i> กลับ
    </a>
  </div>
</div>

<?php
$content = ob_get_clean();
include '../base.php';
?>
