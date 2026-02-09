<?php
include 'middleware.php';
ob_start();
require 'db_news.php';

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $records_per_page;

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

// Fetch Categories
$categories = [];
if ($res_cat = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC")) {
    while($r = $res_cat->fetch_assoc()) $categories[$r['id']] = $r['name'];
}

// Filter Logic
$where = [];
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $where[] = "(title LIKE ? OR content LIKE ?)";
    $term = "%{$_GET['search']}%";
    $params[] = $term; $params[] = $term;
    $types .= "ss";
}
if (!empty($_GET['category'])) {
    $where[] = "category_id = ?";
    $params[] = $_GET['category'];
    $types .= "i";
}
if (!empty($_GET['date_from'])) {
    $where[] = "DATE(upload_datetime) >= ?";
    $params[] = $_GET['date_from'];
    $types .= "s";
}
if (!empty($_GET['date_to'])) {
    $where[] = "DATE(upload_datetime) <= ?";
    $params[] = $_GET['date_to'];
    $types .= "s";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count Total
$total_sql = "SELECT COUNT(*) AS total FROM news $where_sql";
$stmt = $conn->prepare($total_sql);
if(!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch Data
$sql = "SELECT * FROM news $where_sql ORDER BY upload_datetime DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$params[] = $start;
$params[] = $records_per_page;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$news_data = [];
$news_ids = [];
while($row = $result->fetch_assoc()) {
    $news_data[] = $row;
    $news_ids[] = $row['id'];
}

// Fetch Attachments count
$attach_map = [];
if (!empty($news_ids)) {
    $in = str_repeat('?,', count($news_ids) - 1) . '?';
    $stmt_att = $conn->prepare("SELECT news_id, Count(*) as cnt FROM attachments WHERE news_id IN ($in) GROUP BY news_id");
    $stmt_att->bind_param(str_repeat('i', count($news_ids)), ...$news_ids);
    $stmt_att->execute();
    $res_att = $stmt_att->get_result();
    while($row = $res_att->fetch_assoc()) $attach_map[$row['news_id']] = $row['cnt'];
}
?>

<div class="min-h-screen bg-gray-50/50 pb-12" x-data="{ checkAll: false, selected: [] }">
    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Header & Stats -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
             <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-blue-600 text-white p-2 rounded-lg shadow-md shadow-blue-200">
                        <i class="fas fa-newspaper"></i>
                    </span>
                    จัดการข่าวสาร
                </h1>
                <p class="text-gray-500 mt-1 ml-12">ระบบจัดการข่าวประชาสัมพันธ์ที่มีประสิทธิภาพสูงสุด</p>
             </div>
             
             <div class="flex gap-3">
                 <a href="news_add.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-blue-200 hover:shadow-xl transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus-circle text-lg"></i> <span class="font-medium">เขียนข่าวใหม่</span>
                 </a>
             </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-gray-500 text-sm font-medium">ข่าวทั้งหมด</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($total_rows) ?></h3>
                </div>
                <div class="bg-blue-50 text-blue-600 p-3 rounded-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-bullhorn text-xl"></i>
                </div>
            </div>
             <!-- Add more stats here if needed -->
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6" x-data="{ showFilter: false }">
             <div class="flex justify-between items-center cursor-pointer" @click="showFilter = !showFilter">
                 <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                     <i class="fas fa-search text-gray-400"></i> ค้นหาและกรองข้อมูล
                 </h3>
                 <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="showFilter ? 'rotate-180' : ''"></i>
             </div>
             
             <form action="news_manage.php" method="get" x-show="showFilter" x-transition.opacity class="mt-4 pt-4 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="text" name="search" value="<?= e($_GET['search']??'') ?>" placeholder="คำค้นหา..." class="w-full bg-gray-50 border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-100 outline-none">
                    
                    <select name="category" class="w-full bg-gray-50 border-gray-200 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-100 outline-none">
                        <option value="">ทุกหมวดหมู่</option>
                        <?php foreach($categories as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= (($_GET['category']??'')==$k)?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div class="flex gap-2 col-span-2">
                         <button type="submit" class="flex-1 bg-gray-800 text-white rounded-lg hover:bg-black transition">ค้นหา</button>
                         <a href="news_manage.php" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600">ล้าง</a>
                    </div>
                </div>
             </form>
        </div>

        <!-- Bulk Action Bar -->
        <div class="mb-4 flex items-center gap-3 bg-blue-50 border border-blue-100 p-3 rounded-xl text-blue-800 transition-all" 
             x-show="selected.length > 0" x-transition x-cloak>
            <i class="fas fa-check-square text-lg"></i>
            <span class="font-medium">เลือกอยู่ <span x-text="selected.length"></span> รายการ</span>
            <div class="h-6 w-px bg-blue-200 mx-1"></div>
            <button @click="bulkDelete()" class="text-sm font-semibold hover:text-red-600 flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-blue-100 shadow-sm hover:shadow-md transition-all text-red-500">
                <i class="fas fa-trash-alt"></i> ลบที่เลือก
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left">
                    <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-medium text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4 w-10 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4" 
                                       @change="selected = $el.checked ? <?= htmlspecialchars(json_encode($news_ids)) ?> : []">
                            </th>
                            <th class="px-6 py-4">หัวข้อข่าว</th>
                            <th class="px-6 py-4">หมวดหมู่</th>
                            <th class="px-6 py-4">ไฟล์แนบ</th>
                            <th class="px-6 py-4">วันที่ลง</th>
                            <th class="px-6 py-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(empty($news_data)): ?>
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">ไม่พบข้อมูลข่าวสาร</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach($news_data as $row): ?>
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" value="<?= $row['id'] ?>" x-model="selected" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col max-w-sm">
                                    <span class="text-sm font-bold text-gray-800 truncate" title="<?= e($row['title']) ?>"><?= e($row['title']) ?></span>
                                    <span class="text-xs text-gray-400 mt-0.5"><i class="fas fa-user-edit mr-1"></i> <?= e($row['uploader']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    <?= $categories[$row['category_id']] ?? 'ทั่วไป' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if(isset($attach_map[$row['id']])): ?>
                                    <span class="flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded w-fit">
                                        <i class="fas fa-paperclip"></i> <?= $attach_map[$row['id']] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-gray-300 text-xs">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600"><?= date('d/m/Y', strtotime($row['upload_datetime'])) ?></span>
                                <span class="text-xs text-gray-400 block"><?= date('H:i', strtotime($row['upload_datetime'])) ?> น.</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-all">
                                    <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-colors bg-white shadow-sm" title="แก้ไข"
                                       onclick="window.location='news_edit.php?id=<?= $row['id'] ?>'">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                    <button class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors bg-white shadow-sm" title="ลบ"
                                       onclick="deleteNews(<?= $row['id'] ?>)">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
             <!-- Clean Pagination -->
             <?php if($total_pages > 1): ?>
             <div class="px-6 py-4 border-t border-gray-100 flex justify-center">
                <nav class="flex gap-1" aria-label="Pagination">
                    <?php for($i=1; $i<=$total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($_GET['search']??'') ?>&category=<?= $_GET['category']??'' ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium transition-colors
                           <?= $i == $page ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
             </div>
             <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- Helper Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- Delete Single ---
    window.deleteNews = function(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลและไฟล์แนบจะถูกลบถาวร ไม่สามารถกู้คืนได้",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'ลบข้อมูล',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('news_update.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'delete', id: id })
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        Swal.fire('Deleted!', d.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error!', d.message, 'error');
                    }
                });
            }
        });
    }

    // --- Bulk Delete using Alpine.js Context ---
    // Since function is outside Alpine scope, we need the context or method
    // I will attach this to the global scope but it needs access to 'selected'
    // Actually, I can use a global function that Alpine calls.
    
    // NOTE: In the Alpine component above: <button @click="bulkDelete()">
    // We need to define bulkDelete inside the x-data or make it accessible.
    // Let's modify the x-data object slightly via script injection or global assignment if easier.
    
    // Better approach: Make the whole wrapper controlled by Alpine, which it is.
    // I can put the function in script and call it? No, Alpine methods are better.
    // But for simplicity in PHP mixing: I'll stick to a global function that reads from a hidden input or just logic.
    // Wait, Alpine `x-model="selected"` updates the array. I can access it if I had passed it out.
    // Let's rewrite the x-data to include the method directly.
    
    // Actually, I can just grab the checkboxes manually if I want to be "Robust".
    // Or just use Alpine's $data if possible.
    
    // Let's use standard JS for the button since it's easier to debug than inline Alpine for complex fetch.
    document.addEventListener('alpine:init', () => {
        Alpine.data('newsManage', () => ({
            checkAll: false,
            selected: [],
            
            bulkDelete() {
                Swal.fire({
                    title: `ลบ ${this.selected.length} รายการ?`,
                    text: "การกระทำนี้ไม่สามารถย้อนกลับได้",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'ยืนยันลบ',
                    cancelButtonText: 'ยกเลิก'
                }).then((r) => {
                    if (r.isConfirmed) {
                        fetch('news_update.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ action: 'bulk_delete', ids: this.selected })
                        })
                        .then(res => res.json())
                        .then(d => {
                            if(d.success) {
                                Swal.fire('Deleted!', d.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', d.message, 'error');
                            }
                        });
                    }
                });
            }
        }))
    });
</script>

<!-- Modify wrapper to use the data object -->
<script>
    // Inject attribute to the main div
    document.querySelector('[x-data]').setAttribute('x-data', 'newsManage');
</script>

<style>
    [x-cloak] { display: none !important; }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>