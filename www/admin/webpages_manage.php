<?php
include 'middleware.php';
ob_start();
include '../condb/condb.php';

// Pagination settings
$records_per_page = 15;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $records_per_page;

function e($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

// Filter Logic
$where = [];
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $where[] = "(title LIKE ? OR slug LIKE ?)";
    $term = "%{$_GET['search']}%";
    $params[] = $term; $params[] = $term;
    $types .= "ss";
}
if (!empty($_GET['created_by'])) {
    $where[] = "created_by LIKE ?";
    $params[] = "%{$_GET['created_by']}%";
    $types .= "s";
}

$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count Stats
$stats = [
    'total' => $mysqli4->query("SELECT COUNT(*) as cnt FROM web_pages")->fetch_assoc()['cnt'],
    'visible' => $mysqli4->query("SELECT COUNT(*) as cnt FROM web_pages WHERE visible = 1")->fetch_assoc()['cnt'],
    'hidden' => $mysqli4->query("SELECT COUNT(*) as cnt FROM web_pages WHERE visible = 0")->fetch_assoc()['cnt']
];

// Count Total for Pagination
$total_sql = "SELECT COUNT(*) AS total FROM web_pages $where_sql";
$stmt_t = $mysqli4->prepare($total_sql);
if(!empty($params)) $stmt_t->bind_param($types, ...$params);
$stmt_t->execute();
$total_rows = $stmt_t->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $records_per_page);

// Fetch Data
$sql = "SELECT * FROM web_pages $where_sql ORDER BY updated_at DESC LIMIT ?, ?";
$stmt = $mysqli4->prepare($sql);
$all_params = array_merge($params, [$start, $records_per_page]);
$all_types = $types . "ii";
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$result = $stmt->get_result();

$pages_data = [];
$page_ids = [];
while($row = $result->fetch_assoc()) {
    $pages_data[] = $row;
    $page_ids[] = (int)$row['id'];
}
?>

<div class="min-h-screen bg-gray-50/50 pb-12" x-data="webPageManage()">
    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Header & Action -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
             <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-indigo-600 text-white p-2 rounded-lg shadow-md shadow-indigo-100">
                        <i class="fas fa-file-code"></i>
                    </span>
                    จัดการเว็บเพจ
                </h1>
                <p class="text-gray-500 mt-1 ml-12">สร้างและปรับแต่งเนื้อหาหน้าเว็บแบบ Notion-style ได้อย่างอิสระ</p>
             </div>
             
             <div class="flex gap-3">
                 <a href="webpage_add.php" class="bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-indigo-100 hover:shadow-xl transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fas fa-plus-circle text-lg"></i> <span class="font-medium">สร้างเพจใหม่</span>
                 </a>
             </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">เนื้อหาทั้งหมด</p>
                    <h3 class="text-3xl font-black text-gray-800 mt-1"><?= number_format($stats['total']) ?></h3>
                </div>
                <div class="bg-indigo-50 text-indigo-500 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-copy text-2xl"></i>
                </div>
            </div>
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all border-l-4 border-l-green-500">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">กำลังแสดงผล</p>
                    <h3 class="text-3xl font-black text-green-600 mt-1"><?= number_format($stats['visible']) ?></h3>
                </div>
                <div class="bg-green-50 text-green-500 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all border-l-4 border-l-gray-300">
                <div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-wider">ถูกซ่อนไว้</p>
                    <h3 class="text-3xl font-black text-gray-400 mt-1"><?= number_format($stats['hidden']) ?></h3>
                </div>
                <div class="bg-gray-50 text-gray-400 p-4 rounded-2xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-eye-slash text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6" x-data="{ open: <?= !empty($_GET['search']) ? 'true' : 'false' ?> }">
             <div class="flex justify-between items-center cursor-pointer" @click="open = !open">
                 <h3 class="font-semibold text-gray-700 flex items-center gap-2">
                     <i class="fas fa-filter text-indigo-400"></i> ค้นหาและกรองข้อมูล
                 </h3>
                 <i class="fas fa-chevron-down text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
             </div>
             
             <form action="webpages_manage.php" method="get" x-show="open" x-transition.opacity class="mt-4 pt-4 border-t border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="<?= e($_GET['search']??'') ?>" placeholder="ค้นหาชื่อเพจ หรือ slug..." class="w-full bg-gray-50 border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 outline-none border transition-all">
                    </div>
                    
                    <input type="text" name="created_by" value="<?= e($_GET['created_by']??'') ?>" placeholder="ผู้สร้าง..." class="w-full bg-gray-50 border-gray-200 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-indigo-100 outline-none border transition-all">
                    
                    <div class="flex gap-2">
                         <button type="submit" class="flex-1 bg-gray-800 text-white rounded-lg hover:bg-black transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2 font-medium">
                            <i class="fas fa-search"></i> ค้นหา
                         </button>
                         <a href="webpages_manage.php" class="px-6 py-2.5 border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors flex items-center justify-center font-medium">ล้าง</a>
                    </div>
                </div>
             </form>
        </div>

        <!-- Bulk Action Bar -->
        <div class="mb-4 flex items-center gap-3 bg-indigo-50 border border-indigo-100 p-3 rounded-xl text-indigo-800 transition-all shadow-sm" 
             x-show="selected.length > 0" x-transition x-cloak>
            <i class="fas fa-check-circle text-lg"></i>
            <span class="font-medium">เลือกอยู่ <span x-text="selected.length"></span> รายการ</span>
            <div class="h-6 w-px bg-indigo-200 mx-1"></div>
            <button @click="bulkDelete()" class="text-sm font-semibold hover:text-red-600 flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg border border-indigo-100 shadow-sm hover:shadow-md transition-all text-red-500">
                <i class="fas fa-trash-alt"></i> ลบที่เลือก
            </button>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left">
                    <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-400 font-bold text-[10px] uppercase tracking-widest">
                        <tr>
                            <th class="px-6 py-5 w-10 text-center">
                                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" 
                                       @change="selected = $el.checked ? <?= htmlspecialchars(json_encode($page_ids)) ?> : []">
                            </th>
                            <th class="px-6 py-5">ชื่อเพจ / URL</th>
                            <th class="px-6 py-5">สถานะ</th>
                            <th class="px-6 py-5">ผู้สร้าง</th>
                            <th class="px-6 py-5">อัปเดตล่าสุด</th>
                            <th class="px-6 py-5 text-center">เครื่องมือ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(empty($pages_data)): ?>
                            <tr><td colspan="6" class="px-6 py-20 text-center text-gray-300 italic">ไม่พบข้อมูลเว็บเพจในขณะนี้</td></tr>
                        <?php endif; ?>
                        
                        <?php foreach($pages_data as $row): ?>
                        <tr class="hover:bg-indigo-50/20 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" value="<?= $row['id'] ?>" x-model="selected" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <?php if(!empty($row['thumbnail'])): ?>
                                        <img src="../<?= $row['thumbnail'] ?>" class="w-10 h-10 rounded-lg object-cover border border-gray-100">
                                    <?php else: ?>
                                        <div class="w-10 h-10 rounded-lg bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-300 text-xs italic">No Img</div>
                                    <?php endif; ?>
                                    <div class="flex flex-col max-w-sm">
                                        <span class="text-sm font-bold text-gray-800 truncate" title="<?= e($row['title']) ?>"><?= e($row['title']) ?></span>
                                        <span class="text-[11px] text-indigo-500 font-medium">/<?= e($row['slug']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button @click="toggleVisibility(<?= $row['id'] ?>)" 
                                        :class="status_map[<?= $row['id'] ?>] === undefined ? (<?= $row['visible'] ?> ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400') : (status_map[<?= $row['id'] ?>] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400')"
                                        class="px-2.5 py-1 rounded-full text-[11px] font-bold transition-all border border-transparent hover:border-current flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                    <span x-text="status_map[<?= $row['id'] ?>] === undefined ? (<?= $row['visible'] ?> ? 'กำลังแสดง' : 'ซ่อนอยู่') : (status_map[<?= $row['id'] ?>] ? 'กำลังแสดง' : 'ซ่อนอยู่')"></span>
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs text-gray-600 flex items-center gap-2">
                                    <div class="p-1 bg-gray-100 rounded-md"><i class="fas fa-user text-[10px] text-gray-400"></i></div>
                                    <?= e($row['created_by']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-600"><?= date('d M Y', strtotime($row['updated_at'])) ?></div>
                                <div class="text-[10px] text-gray-400 mt-0.5"><?= date('H:i', strtotime($row['updated_at'])) ?> น.</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-40 group-hover:opacity-100 transition-all">
                                    <button @click="copyLink('<?= e($row['slug']) ?>')" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-green-600 hover:bg-green-50 hover:border-green-200 transition-colors bg-white shadow-sm" title="คัดลอกลิงค์">
                                        <i class="fas fa-link text-[10px]"></i>
                                    </button>
                                    <a href="../app-webpage/page.php?slug=<?= urlencode($row['slug']) ?>" target="_blank" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-colors bg-white shadow-sm" title="ดูหน้าเว็บ">
                                        <i class="fas fa-external-link-alt text-[10px]"></i>
                                    </a>
                                    <a href="webpage_edit.php?id=<?= $row['id'] ?>" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-orange-500 hover:bg-orange-50 hover:border-orange-200 transition-colors bg-white shadow-sm" title="แก้ไข">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </a>
                                    <button @click="deletePage(<?= $row['id'] ?>)" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors bg-white shadow-sm" title="ลบ">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
             <!-- Pagination -->
             <?php if($total_pages > 1): ?>
             <div class="px-6 py-6 border-t border-gray-100 flex justify-center bg-gray-50/30">
                <nav class="flex items-center gap-1.5" aria-label="Pagination">
                    <?php 
                    $range = 2;
                    for($i=1; $i<=$total_pages; $i++): 
                        if($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)):
                    ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($_GET['search']??'') ?>&created_by=<?= urlencode($_GET['created_by']??'') ?>" 
                           class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-bold transition-all
                           <?= $i == $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100 scale-110' : 'bg-white text-gray-400 hover:bg-white hover:text-indigo-600 border border-gray-100 hover:border-indigo-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php elseif($i == $page - $range - 1 || $i == $page + $range + 1): ?>
                        <span class="text-gray-300">...</span>
                    <?php endif; endfor; ?>
                </nav>
             </div>
             <?php endif; ?>
        </div>
        
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function webPageManage() {
        return {
            selected: [],
            status_map: {}, // Tracking local state for visibility toggles

            init() {
                console.log('WebPage Manager Initialized');
            },

            copyLink(slug) {
                const url = `${window.location.origin}/app-webpage/page.php?slug=${slug}`;
                navigator.clipboard.writeText(url).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'คัดลอกลิงค์แล้ว',
                        text: url,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                });
            },

            toggleVisibility(id) {
                fetch('webpage_update.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'toggle_visibility', id: id })
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        this.status_map[id] = d.visible;
                    } else {
                        Swal.fire('Error', d.message, 'error');
                    }
                });
            },

            deletePage(id) {
                Swal.fire({
                    title: 'ยืนยันการลบ?',
                    text: "ต้องการลบหน้าเว็บนี้ใช่หรือไม่?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((r) => {
                    if (r.isConfirmed) {
                        this.doAction('delete', { id: id });
                    }
                });
            },

            bulkDelete() {
                Swal.fire({
                    title: `ลบ ${this.selected.length} รายการ?`,
                    text: "ไม่สามารถกู้คืนข้อมูลได้ในภายหลัง",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'ลบที่เลือก',
                    cancelButtonText: 'ยกเลิก'
                }).then((r) => {
                    if (r.isConfirmed) {
                        this.doAction('bulk_delete', { ids: this.selected });
                    }
                });
            },

            doAction(action, data) {
                fetch('webpage_update.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: action, ...data })
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: d.message, timer: 1000, showConfirmButton: false })
                            .then(() => location.reload());
                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', d.message, 'error');
                    }
                });
            }
        }
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    /* Premium font - try to use Sarabun if loaded, otherwise fallback */
    body { font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
