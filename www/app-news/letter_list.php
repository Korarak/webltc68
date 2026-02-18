<?php
ob_start();
require '../condb/condb.php';

// Pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$start = ($page - 1) * $records_per_page;

// Search
$search_sql = "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_term = $mysqli2->real_escape_string($_GET['search']);
    $search_sql = "WHERE letter_title LIKE '%$search_term%'";
}

// Main Query
$sql = "SELECT * FROM letters $search_sql ORDER BY letter_createtime DESC LIMIT $start, $records_per_page";
$result = $mysqli2->query($sql);

// Total rows for pagination
$total_sql = "SELECT COUNT(*) AS total FROM letters $search_sql";
$total_rows = $mysqli2->query($total_sql)->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $records_per_page);

function formatDateTime($datetime) {
  $date = new DateTime($datetime);
  return $date->format('d') . ' ' . getThaiMonth($date->format('m')) . ' ' . ($date->format('Y') + 543) . ' เวลา ' . $date->format('H:i:s');
}

function getThaiMonth($monthNumber) {
  $thaiMonths = [
      '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
      '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
      '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
      '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
  ];
  return $thaiMonths[$monthNumber];
}
?>
<!-- Breadcrumb Navigation -->
<nav class="text-sm text-gray-600 mt-[84px] mb-4 max-w-7xl mx-auto px-4">
  <ol class="list-reset flex items-center space-x-2">
    <li><a href="/" class="hover:underline hover:text-green-600 font-medium">หน้าแรก</a></li>
    <li>/</li>
    <li><span class="text-green-700 font-bold">จดหมายข่าว</span></li>
  </ol>
</nav>

<div class="max-w-7xl mx-auto bg-white rounded shadow p-6 mb-10">
  <!-- Header -->
  <div class="bg-green-600 text-white text-center rounded-t-md py-4 mb-6">
    <h2 class="text-2xl font-bold"><i class="fas fa-envelope-open-text mr-2"></i>จดหมายข่าวทั้งหมด</h2>
  </div>

  <!-- Search -->
  <form class="mb-6" method="get" action="">
    <div class="flex flex-col sm:flex-row gap-2">
      <input type="text" name="search" placeholder="ค้นหาจากหัวข้อข่าว"
             value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
             class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center justify-center">
        <i class="fas fa-search mr-2"></i><span class="hidden sm:inline">ค้นหา</span>
      </button>
    </div>
  </form>

  <!-- Table -->
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto border-collapse">
      <thead class="bg-green-100 text-gray-700">
        <tr class="text-center text-sm font-semibold">
          <th class="py-3 px-4 min-w-[160px]">วันที่อัปโหลด</th>
          <th class="py-3 px-4 min-w-[240px]">หัวข้อข่าว</th>
          <th class="py-3 px-4">ภาพ</th>
          <th class="py-3 px-4">ผู้เผยแพร่</th>
          <th class="py-3 px-4">ดูเพิ่มเติม</th>
        </tr>
      </thead>
      <tbody class="text-sm">
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr class="text-center border-b hover:bg-gray-50">
              <td class="py-2 px-4"><?= formatDateTime($row['letter_createtime']) ?></td>
              <td class="py-2 px-4 text-left"><?= nl2br(htmlspecialchars(mb_strimwidth(strip_tags($row['letter_title']), 0, 150, '...'))) ?></td>
              <td class="py-2 px-4">
                <?php
                    // Path Logic: Database now has 'uploads/newsletter/filename'
                    // We just need to go up one level from 'app-news/'
                    $view_url = "../" . $row['letter_attenmath'];
                ?>
                <img src="<?= htmlspecialchars($view_url) ?>"
                     alt="thumbnail"
                     class="w-20 h-12 object-cover rounded border"
                     loading="lazy">
              </td>
              <td class="py-2 px-4"><?= htmlspecialchars($row['letter_made']) ?></td>
              <td class="py-2 px-4">
                <a href="letter_view.php?id=<?= $row['letter_id'] ?>"
                   class="inline-flex items-center text-sm text-blue-600 border border-blue-600 px-3 py-1.5 rounded hover:bg-blue-600 hover:text-white transition">
                  <i class="fas fa-eye mr-1"></i><span class="hidden md:inline">รายละเอียด</span>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="py-6 text-center text-gray-500">ไม่พบข้อมูลจดหมายข่าว</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="mt-6 flex justify-center space-x-1">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
      <a href="?page=<?= $i ?>&<?= isset($_GET['search']) ? 'search=' . urlencode($_GET['search']) : '' ?>"
         class="px-3 py-1 border rounded <?= $i == $page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>">
        <?= $i ?>
      </a>
    <?php endfor; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
include '../base.php';
?>
