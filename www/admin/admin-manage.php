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
$types = '';

if (!empty($_GET['search'])) {
    $search_term = "%{$_GET['search']}%";
    $where[] = "(username LIKE ?)";
    $params[] = $search_term;
    $types .= 's';
}

if (!empty($_GET['id'])) {
    $where[] = "id = ?";
    $params[] = $_GET['id'];
    $types .= 'i';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Query for users
$sql = "SELECT * FROM users $where_sql ORDER BY created_at DESC LIMIT $start, $records_per_page";
$stmt = $mysqli->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Count total
$total_sql = "SELECT COUNT(*) AS total FROM users $where_sql";
$total_stmt = $mysqli->prepare($total_sql);
if ($params) {
    $total_stmt->bind_param($types, ...$params);
}
$total_stmt->execute();
$total_rows = $total_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $records_per_page);
?>

<div class="max-w-6xl mx-auto mt-10 px-4">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl p-6 shadow-lg mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-3">
            <i class="fas fa-users-cog"></i>
            จัดการผู้ดูแลระบบ
        </h2>
        <p class="text-blue-100 mt-2">จัดการบัญชีผู้ใช้และสิทธิ์การเข้าถึงระบบ</p>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 border border-gray-200">
        <form class="grid grid-cols-1 md:grid-cols-4 gap-4" action="admin-manage.php" method="get">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-search mr-1 text-blue-600"></i>
                    ค้นหาผู้ใช้
                </label>
                <input type="text" name="search" placeholder="ค้นหาชื่อผู้ใช้..."
                       value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-id-card mr-1 text-blue-600"></i>
                    รหัสผู้ใช้
                </label>
                <input type="number" name="id" placeholder="รหัสผู้ใช้"
                       value="<?= htmlspecialchars($_GET['id'] ?? '') ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-filter"></i>
                    ค้นหา
                </button>
            </div>

            <div class="flex items-end">
                <a href="admin-manage.php"
                   class="w-full bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-refresh"></i>
                    ล้างค่า
                </a>
            </div>
        </form>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center mb-6">
        <div class="text-sm text-gray-600 bg-gray-100 px-4 py-2 rounded-lg">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>
            พบทั้งหมด <span class="font-bold text-blue-700"><?= $total_rows ?></span> รายการ
        </div>
        <a href="admin-add.php"
           class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
            <i class="fas fa-user-plus"></i>
            เพิ่มผู้ดูแลใหม่
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-id-card mr-1"></i>
                            ID
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-user mr-1"></i>
                            ชื่อผู้ใช้
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-calendar mr-1"></i>
                            สร้างเมื่อ
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <i class="fas fa-cog mr-1"></i>
                            การจัดการ
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($user = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        #<?= $user['id']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($user['username']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-2 text-gray-400"></i>
                                        <?= date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="admin-edit.php?id=<?= $user['id']; ?>"
                                           class="inline-flex items-center px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-xs font-semibold transition-colors">
                                            <i class="fas fa-edit mr-1"></i>
                                            แก้ไข
                                        </a>
                                        <?php if ($user['id'] != @$_SESSION['user_id']): ?>
                                            <a href="admin-delete.php?id=<?= $user['id']; ?>"
                                               onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ \"<?= htmlspecialchars($user['username']) ?>\"? การดำเนินการนี้ไม่สามารถย้อนกลับได้');"
                                               class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors">
                                                <i class="fas fa-trash mr-1"></i>
                                                ลบ
                                            </a>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1.5 bg-gray-400 text-white rounded-lg text-xs font-semibold cursor-not-allowed">
                                                <i class="fas fa-ban mr-1"></i>
                                                ไม่สามารถลบตัวเองได้
                                            </span>
                                        <?php endif; ?>
                                        <!-- <a href="admin-reset-password.php?id=<?= $user['id']; ?>"
                                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 
                                           hover:bg-blue-700 text-white rounded-lg 
                                           text-xs font-semibold transition-colors">
                                            <i class="fas fa-key mr-1"></i>
                                            รีเซ็ตรหัสผ่าน
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <i class="fas fa-users-slash text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium mb-2">ไม่พบข้อมูลผู้ใช้</p>
                                    <p class="text-sm">ลองเปลี่ยนเงื่อนไขการค้นหาหรือเพิ่มผู้ใช้ใหม่</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-between items-center mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="text-sm text-gray-600">
            แสดง <span class="font-semibold"><?= min($records_per_page, $result->num_rows) ?></span> จาก 
            <span class="font-semibold"><?= $total_rows ?></span> รายการ
        </div>
        
        <nav class="flex space-x-1">
            <!-- Previous Page -->
            <?php if ($page > 1): ?>
                <a href="admin-manage.php?page=<?= $page - 1; ?>&<?= http_build_query($_GET); ?>"
                   class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                    <i class="fas fa-chevron-left mr-1 text-xs"></i>
                    ก่อนหน้า
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed flex items-center">
                    <i class="fas fa-chevron-left mr-1 text-xs"></i>
                    ก่อนหน้า
                </span>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php 
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <a href="admin-manage.php?page=<?= $i; ?>&<?= http_build_query($_GET); ?>"
                   class="px-3 py-2 border rounded-lg text-sm font-medium transition-colors <?= ($i == $page) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' ?>">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>

            <!-- Next Page -->
            <?php if ($page < $total_pages): ?>
                <a href="admin-manage.php?page=<?= $page + 1; ?>&<?= http_build_query($_GET); ?>"
                   class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                    ถัดไป
                    <i class="fas fa-chevron-right ml-1 text-xs"></i>
                </a>
            <?php else: ?>
                <span class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-400 cursor-not-allowed flex items-center">
                    ถัดไป
                    <i class="fas fa-chevron-right ml-1 text-xs"></i>
                </span>
            <?php endif; ?>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
// เพิ่มการยืนยันการลบที่ละเอียดมากขึ้น
document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('a[onclick*="confirm"]');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const username = this.closest('tr').querySelector('td:nth-child(2) .text-gray-900').textContent;
            const confirmed = confirm(`⚠️ คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้ "${username}"?\n\nการดำเนินการนี้จะ:\n• ลบบัญชีผู้ใช้นี้ถาวร\n• ผู้ใช้จะไม่สามารถเข้าสู่ระบบได้อีก\n• ไม่สามารถย้อนกลับได้`);
            
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });
});

// เพิ่มการค้นหาแบบ real-time (optional)
document.querySelector('input[name="search"]').addEventListener('input', function(e) {
    // สามารถเพิ่ม debounce และ AJAX search ได้ที่นี่
    if (this.value.length >= 3) {
        // Auto-submit หลังจากพิมพ์ 3 ตัวอักษร (optional)
        // this.form.submit();
    }
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>