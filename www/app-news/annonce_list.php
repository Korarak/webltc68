<?php
ob_start();
// PHP: เชื่อมต่อฐานข้อมูลและตั้งค่า
require '../condb/condb.php';

// รับ category_id จาก URL
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// ตรวจสอบการเชื่อมต่อ $mysqli1
if (!isset($mysqli1) || !$mysqli1 instanceof mysqli) {
    echo "<div class='mt-[84px] max-w-7xl mx-auto px-4 py-6 bg-red-100 text-red-700 rounded shadow'>เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล</div>";
    exit;
}

// ดึงชื่อหมวดหมู่
$category_name = '';
$cat_stmt = $mysqli1->prepare("SELECT name FROM categories WHERE id = ? AND visible = 1");
if ($cat_stmt) {
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();

    if ($cat_result->num_rows === 0) {
        echo "<div class='mt-[84px] max-w-7xl mx-auto px-4 py-6 bg-red-100 text-red-700 rounded shadow'>ไม่พบหมวดหมู่ข่าวที่คุณต้องการ</div>";
        $cat_stmt->close();
        exit;
    }
    $category_name = $cat_result->fetch_assoc()['name'];
    $cat_stmt->close();
} else {
    // กรณี prepare ล้มเหลว
    echo "<div class='mt-[84px] max-w-7xl mx-auto px-4 py-6 bg-red-100 text-red-700 rounded shadow'>เกิดข้อผิดพลาดในการประมวลผลคำสั่งฐานข้อมูล</div>";
    exit;
}


// Pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // ตรวจสอบไม่ให้ page ติดลบหรือเป็น 0
$start = ($page - 1) * $records_per_page;

// Search
$search_term = $_GET['search'] ?? '';
// ใช้ Prepared statement สำหรับการค้นหา เพื่อป้องกัน SQL Injection
$search_param = "%" . $search_term . "%";

// นับจำนวนทั้งหมด (Total Count Query)
$count_sql = "SELECT COUNT(*) AS total FROM news WHERE category_id = ? AND is_deleted = 0";
$params = "i";
$total_rows = 0;

if ($search_term) {
    $count_sql .= " AND title LIKE ?";
    $params .= "s";
}

$count_stmt = $mysqli1->prepare($count_sql);

if ($search_term) {
    $count_stmt->bind_param($params, $category_id, $search_param);
} else {
    $count_stmt->bind_param($params, $category_id);
}

$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_rows / $records_per_page);

// ดึงข่าวในหมวดหมู่ (News Fetch Query)
$sql = "SELECT id, title, uploader, upload_datetime FROM news WHERE category_id = ? AND is_deleted = 0";
if ($search_term) {
    $sql .= " AND title LIKE ?";
}
$sql .= " ORDER BY upload_datetime DESC LIMIT ?, ?";

$params_fetch = "i";
if ($search_term) {
    $params_fetch .= "s";
}
$params_fetch .= "ii";

$result = null;
$fetch_stmt = $mysqli1->prepare($sql);

if ($search_term) {
    $fetch_stmt->bind_param($params_fetch, $category_id, $search_param, $start, $records_per_page);
} else {
    $fetch_stmt->bind_param($params_fetch, $category_id, $start, $records_per_page);
}

$fetch_stmt->execute();
$result = $fetch_stmt->get_result();
$fetch_stmt->close();


// ฟังก์ชันแสดงวันที่ไทย
function formatDateTime($datetime) {
    $date = new DateTime($datetime);
    return $date->format('d') . ' ' . getThaiMonth($date->format('m')) . ' ' . ($date->format('Y') + 543) . ' เวลา ' . $date->format('H:i');
}
function getThaiMonth($monthNumber) {
    $thaiMonths = [
        '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
        '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
        '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
    ];
    return $thaiMonths[$monthNumber] ?? '';
}
?>

<nav class="text-sm text-gray-600 mt-[84px] mb-4 max-w-7xl mx-auto px-4">
  <ol class="list-reset flex items-center space-x-2">
    <li><a href="/" class="hover:underline hover:text-green-600 font-medium">หน้าแรก</a></li>
    <li>/</li>
    <li>
      <span class="text-green-700 font-bold"><?= htmlspecialchars($category_name) ?></span>
    </li>
  </ol>
</nav>

<div class="max-w-7xl mx-auto px-4 py-6 bg-white shadow-xl rounded-xl mb-8">
  <div class="text-center bg-green-700 text-white py-4 rounded-t-xl mb-6 shadow-lg">
    <h2 class="text-3xl font-extrabold tracking-wider">
      <i class="fas fa-bullhorn mr-3"></i><?= htmlspecialchars($category_name) ?>
    </h2>
  </div>

  <form class="mb-6 flex flex-col sm:flex-row gap-3 px-4" method="get">
    <input type="hidden" name="category_id" value="<?= $category_id ?>">
    <input type="text" name="search" placeholder="ค้นหาจากหัวข้อข่าว" value="<?= htmlspecialchars($search_term) ?>"
      class="flex-grow border border-gray-300 rounded-lg px-4 py-2.5 shadow-inner focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200">
    <button type="submit"
      class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md flex items-center justify-center gap-2 transition duration-200 transform hover:scale-[1.01]">
      <i class="fas fa-search"></i> <span class="hidden sm:inline">ค้นหา</span>
    </button>
  </form>

  <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-md">
    <table class="w-full table-auto text-left">
      <thead class="bg-green-600 text-white">
        <tr>
          <th class="px-4 py-3 w-[15%] min-w-[120px]">วันที่</th>
          <th class="px-4 py-3 w-[55%] min-w-[250px]">เรื่อง</th>
          <th class="px-4 py-3 w-[15%] min-w-[100px]">ผู้ประกาศ</th>
          <th class="px-4 py-3 w-[15%] min-w-[100px] text-center">รายละเอียด</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr class="border-b border-gray-200 hover:bg-green-50/50 transition duration-150">
              <td class="px-4 py-3 text-sm text-gray-700 font-medium whitespace-nowrap">
                <?= formatDateTime($row['upload_datetime']) ?>
              </td>
              <td class="px-4 py-3 text-sm text-gray-900 font-semibold">
                <?= nl2br(htmlspecialchars($row['title'])) ?>
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                <?= htmlspecialchars($row['uploader']) ?>
              </td>
              <td class="px-4 py-3 text-center whitespace-nowrap">
                <a href="annonce_detail.php?id=<?= $row['id'] ?>"
                  class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-sm px-3 py-1.5 rounded-full shadow-md transition duration-200 transform hover:scale-105">
                  <i class="fas fa-eye"></i> <span class="hidden md:inline">ดูรายละเอียด</span>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="4" class="text-center py-6 text-gray-500 text-lg">
            <i class="fas fa-box-open mr-2"></i> ไม่พบข่าวในหมวดหมู่นี้
            <?= $search_term ? "สำหรับคำค้นหา: \"<strong>" . htmlspecialchars($search_term) . "</strong>\"" : "" ?>
          </td></tr>
        <?php endif; ?>
      </tbody>
      <tfoot class="bg-green-500 text-white">
        <tr>
          <th colspan="4" class="px-4 py-2 text-center text-sm font-normal">
            แสดงข้อมูลทั้งหมด <?= number_format($total_rows) ?> รายการ
          </th>
        </tr>
      </tfoot>
    </table>
  </div>

  <?php if ($total_pages > 1): ?>
    <div class="mt-6 flex justify-center">
      <nav class="inline-flex space-x-1 p-2 bg-gray-100 rounded-lg shadow-inner">
        <?php
          // สร้าง Query string พื้นฐาน
          $base_query = "?category_id=" . $category_id . ($search_term ? "&search=" . urlencode($search_term) : "");
          
          // ปุ่ม Previous
          if ($page > 1) {
            echo '<a href="' . $base_query . '&page=' . ($page - 1) . '" class="px-3 py-1.5 rounded-full bg-white text-gray-700 hover:bg-green-100 font-medium transition duration-200"><i class="fas fa-chevron-left text-xs"></i> ก่อนหน้า</a>';
          }

          // แสดงเฉพาะเพจใกล้เคียง
          $start_page = max(1, $page - 2);
          $end_page = min($total_pages, $page + 2);
          
          if ($start_page > 1) {
            echo '<a href="' . $base_query . '&page=1" class="px-3 py-1.5 rounded-full bg-white text-gray-700 hover:bg-green-100 transition duration-200">1</a>';
            if ($start_page > 2) {
              echo '<span class="px-3 py-1.5 text-gray-500">...</span>';
            }
          }

          for ($i = $start_page; $i <= $end_page; $i++):
        ?>
          <a href="<?= $base_query ?>&page=<?= $i ?>"
            class="px-3 py-1.5 rounded-full font-semibold transition duration-200 <?= $i == $page ? 'bg-green-600 text-white shadow-lg scale-105' : 'bg-white text-gray-700 hover:bg-green-100' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>

        <?php
          if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
              echo '<span class="px-3 py-1.5 text-gray-500">...</span>';
            }
            echo '<a href="' . $base_query . '&page=' . $total_pages . '" class="px-3 py-1.5 rounded-full bg-white text-gray-700 hover:bg-green-100 transition duration-200">' . $total_pages . '</a>';
          }

          // ปุ่ม Next
          if ($page < $total_pages) {
            echo '<a href="' . $base_query . '&page=' . ($page + 1) . '" class="px-3 py-1.5 rounded-full bg-white text-gray-700 hover:bg-green-100 font-medium transition duration-200">ถัดไป <i class="fas fa-chevron-right text-xs"></i></a>';
          }
        ?>
      </nav>
    </div>
  <?php endif; ?>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
if (isset($mysqli1)) {
    $mysqli1->close();
}

$content = ob_get_clean();
include '../base.php';
?>