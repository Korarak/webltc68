<?php
include 'middleware.php';
ob_start();
include '../condb/condb.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $records_per_page;

// Filters
$where = [];
$params = [];

if (!empty($_GET['search'])) {
    $search_term = "%{$_GET['search']}%";
    $where[] = "(title LIKE ? OR slug LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($_GET['created_by'])) {
    $where[] = "created_by LIKE ?";
    $params[] = "%{$_GET['created_by']}%";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Query for pages
$sql = "SELECT * FROM web_pages $where_sql ORDER BY updated_at DESC LIMIT $start, $records_per_page";
$stmt = $mysqli4->prepare($sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total
$total_sql = "SELECT COUNT(*) AS total FROM web_pages $where_sql";
$total_stmt = $mysqli4->prepare($total_sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $records_per_page);
?>

<div class="max-w-6xl mx-auto mt-10 px-4">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">จัดการเว็บเพจ</h2>

    <!-- Filter Form -->
    <form class="grid grid-cols-1 md:grid-cols-5 gap-2 mb-6" action="webpages_manage.php" method="get">
        <input type="text" name="search" placeholder="ค้นหาชื่อเพจ / slug"
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
               class="px-4 py-2 border border-gray-300 rounded-md focus:ring focus:border-blue-400">

        <input type="text" name="created_by" placeholder="ผู้สร้าง"
               value="<?= htmlspecialchars($_GET['created_by'] ?? '') ?>"
               class="px-4 py-2 border border-gray-300 rounded-md">

        <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition col-span-1 md:col-span-1">
            ค้นหา
        </button>
    </form>

    <a href="webpage_add.php"
       class="inline-block mb-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">สร้างเพจใหม่</a>

    <!-- Pages Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="py-2 px-3 border-b">ID</th>
                    <th class="py-2 px-3 border-b">ชื่อเพจ</th>
                    <th class="py-2 px-3 border-b">Slug (URL)</th>
                    <th class="py-2 px-3 border-b">สถานะ</th>
                    <th class="py-2 px-3 border-b">ผู้สร้าง</th>
                    <th class="py-2 px-3 border-b">อัปเดตล่าสุด</th>
                    <th class="py-2 px-3 border-b">เครื่องมือ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 px-3 border-b"><?= $row['id']; ?></td>
                            <td class="py-2 px-3 border-b"><?= htmlspecialchars(substr($row['title'], 0, 100)); ?></td>
                            <td class="py-2 px-3 border-b text-blue-600"><?= htmlspecialchars($row['slug']); ?></td>
                            <td class="py-2 px-3 border-b">
                                <?= $row['visible'] ? '<span class="text-green-600">แสดง</span>' : '<span class="text-gray-400">ซ่อน</span>'; ?>
                            </td>
                            <td class="py-2 px-3 border-b"><?= htmlspecialchars($row['created_by']); ?></td>
                            <td class="py-2 px-3 border-b"><?= htmlspecialchars($row['updated_at']); ?></td>
                            <td class="py-2 px-3 border-b space-x-1">
                                <a href="../app-webpage/page.php?slug=<?= urlencode($row['slug']); ?>"
                                   class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs" target="_blank">ดู</a>
                                <a href="webpage_edit.php?id=<?= $row['id']; ?>"
                                   class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">แก้ไข</a>
                                <a href="webpage_delete.php?id=<?= $row['id']; ?>"
                                   onclick="return confirm('แน่ใจหรือไม่ว่าต้องการลบเพจนี้?');"
                                   class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">ลบ</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="py-4 text-center text-gray-500">ไม่พบข้อมูลเพจ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center mt-6">
        <nav class="inline-flex space-x-1">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="webpages_manage.php?page=<?= $i; ?>&<?= http_build_query($_GET); ?>"
                   class="px-3 py-1 border rounded <?= ($i == $page) ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-100' ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
