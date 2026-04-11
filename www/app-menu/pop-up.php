<?php
// PHP: ดึงข้อมูลสไลด์/รูปภาพสำหรับ Modal จากฐานข้อมูล
@include('./condb/condb.php');

$letter = null;

// ตรวจสอบว่ามีการเชื่อมต่อ $mysqli2 และเป็น Object
if (isset($mysqli2) && $mysqli2 instanceof mysqli) {
    // กำหนดเงื่อนไข: visible = 1 และ slide_show = 1 (ใช้สำหรับ Popup/Banner พิเศษ)
    $sql = "SELECT carousel_pic FROM carousel WHERE visible = 1 AND slide_show = 1 ORDER BY carousel_no ASC LIMIT 1";
    
    // ใช้ Prepared Statement เพื่อความปลอดภัย
    $stmt = $mysqli2->prepare($sql);
    
    // ตรวจสอบความสำเร็จของการ Prepare และ Execute
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        
        // ดึงข้อมูลแถวแรกออกมา
        $letter = $result->fetch_assoc();
        
        $stmt->close();
    }
    
    // ไม่มีการปิด $mysqli2 ที่นี่ หากไฟล์อื่นต้องการใช้
    // แต่ถ้าต้องการปิด ควรใช้เงื่อนไขเช่น $mysqli2->close();
}

// ตรวจสอบว่าอยู่หน้าแรกหรือไม่
$current_page = basename($_SERVER['REQUEST_URI']);
$is_homepage = ($current_page === '' || $current_page === 'index.php'); // เพิ่ม index.php ด้วยเผื่อมีการเรียกใช้

// **เงื่อนไขสำคัญ:** แสดง Modal เมื่อ:
// 1. อยู่บนหน้าแรก ($is_homepage)
// 2. มีข้อมูลรูปภาพ ($letter ไม่ใช่ null)
// 3. ฟิลด์ 'carousel_pic' ไม่ว่างเปล่า
if ($is_homepage && !empty($letter['carousel_pic'])):
?>





<div id="myModal" class="fixed inset-0 z-[10000] flex items-start md:items-center justify-center bg-black bg-opacity-60 hidden">
  <div class="bg-white rounded-lg shadow-lg max-w-[80vw] max-h-[90vh] relative p-4 animate-fade-in overflow-auto mx-4 mb-4 mt-[90px] md:m-4">
    <button id="closeModal" class="absolute top-2 right-2 text-gray-600 hover:text-red-500 text-2xl font-bold z-10 bg-white/80 w-8 h-8 rounded-full flex items-center justify-center">&times;</button>
    
    <div class="flex justify-center items-center">
      <?php
          // Robust Path Logic
          $clean_path = str_replace(['../', './', 'admin/'], '', $letter['carousel_pic']);
          $clean_path = ltrim($clean_path, '/');
          if (strpos($clean_path, 'uploads/') !== 0) $clean_path = 'uploads/' . $clean_path;
          $img_src = "../" . $clean_path;
      ?>
      <img src="<?= htmlspecialchars($img_src); ?>"
           alt="Modal Image"
           class="max-w-full max-h-[80vh] object-contain rounded shadow"
           loading="lazy" />
    </div>
  </div>
</div>

<style>
  @keyframes fade-in {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }
  .animate-fade-in {
    animation: fade-in 0.3s ease-out;
  }
</style>

<script>
  window.onload = function () {
    const modal = document.getElementById("myModal");
    const closeModal = document.getElementById("closeModal");

    if (modal) {
        // Show modal
        modal.classList.remove("hidden");

        // Close on X click
        closeModal.onclick = () => modal.classList.add("hidden");

        // Close on outside click
        window.onclick = function (event) {
          if (event.target === modal) {
            modal.classList.add("hidden");
          }
        }
    }
  };
</script>

<?php 
// สิ้นสุดเงื่อนไขการแสดง Modal
endif; 
?>