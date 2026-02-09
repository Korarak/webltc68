<?php
include 'middleware.php';
ob_start();
require 'db_letter.php';

// Pagination
$results_per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start_from = ($page - 1) * $results_per_page;

// Search & Filter
$search = $_GET['search'] ?? '';
$where_clauses = [];
$params = [];
$types = "";

if ($search) {
    $where_clauses[] = "letter_title LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

// Count Total
$count_sql = "SELECT COUNT(*) as total FROM letters $where_sql";
$stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_rows = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $results_per_page);

// Fetch Data
$sql = "SELECT * FROM letters $where_sql ORDER BY letter_id DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$params[] = $start_from;
$params[] = $results_per_page;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$letters = [];
$letter_ids = [];
while($row = $result->fetch_assoc()) {
    $letters[] = $row;
    $letter_ids[] = $row['letter_id'];
}
?>

<div class="min-h-screen bg-gray-50/50 pb-12" x-data="{ checkAll: false, selected: [] }">
    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-blue-600 text-white p-2 rounded-lg shadow-md shadow-blue-200">
                        <i class="fas fa-envelope-open-text"></i>
                    </span>
                    จัดการจดหมายข่าว
                </h1>
                <p class="text-gray-500 mt-1 ml-12">ระบบจัดการจดหมายข่าวและวารสารประชาสัมพันธ์</p>
            </div>
            
            <a href="letter_add.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-blue-200 hover:shadow-xl transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                <i class="fas fa-plus-circle text-lg"></i> <span class="font-medium">เพิ่มจดหมายข่าว</span>
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between group hover:shadow-md transition-all">
                <div>
                    <p class="text-gray-500 text-sm font-medium">จดหมายทั้งหมด</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1"><?= number_format($total_rows) ?></h3>
                </div>
                <div class="bg-blue-50 text-blue-600 p-3 rounded-xl group-hover:scale-110 transition-transform">
                    <i class="fas fa-mail-bulk text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 mb-6">
            <form action="letter_manage.php" method="get" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="ค้นหาหัวข้อจดหมาย..." 
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all">
                </div>
                <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-xl hover:bg-black transition-colors shadow-lg shadow-gray-200">
                    ค้นหา
                </button>
                <?php if($search): ?>
                    <a href="letter_manage.php" class="px-4 py-2 border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600 flex items-center justify-center">
                        ล้างค่า
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bulk Action -->
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
                                       @change="selected = $el.checked ? <?= htmlspecialchars(json_encode($letter_ids)) ?> : []">
                            </th>
                            <th class="px-6 py-4">หัวข้อ</th>
                            <th class="px-6 py-4">ไฟล์แนบ</th>
                            <th class="px-6 py-4">วันที่ลง</th>
                            <th class="px-6 py-4">ผู้ลง</th>
                            <th class="px-6 py-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if(empty($letters)): ?>
                            <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">ไม่พบวารสาร/จดหมายข่าว</td></tr>
                        <?php endif; ?>

                        <?php foreach($letters as $row): ?>
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" value="<?= $row['letter_id'] ?>" x-model="selected" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div x-data="{ expanded: false, fullText: '<?= htmlspecialchars($row['letter_title'], ENT_QUOTES) ?>' }">
                                    <div class="text-sm font-bold text-gray-800">
                                        <span x-text="expanded ? fullText : fullText.slice(0, 50) + (fullText.length > 50 ? '...' : '')"></span>
                                    </div>
                                    <button x-show="fullText.length > 50" 
                                            @click="expanded = !expanded" 
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 focus:outline-none">
                                        <span x-text="expanded ? 'ย่อลง' : 'อ่านเพิ่มเติม'"></span>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php 
                                    $view_path = "../" . $row['letter_attenmath'];
                                ?>
                                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $row['letter_attenmath'])): ?>
                                    <div class="h-10 w-10 rounded-lg overflow-hidden border border-gray-200">
                                        <img src="<?= htmlspecialchars($view_path); ?>" class="w-full h-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <a href="<?= htmlspecialchars($view_path); ?>" target="_blank" class="flex items-center gap-2 text-blue-600 hover:underline bg-blue-50 px-3 py-1 rounded-lg w-fit text-sm">
                                        <i class="fas fa-file-download"></i> ดาวน์โหลด
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600"><?= date('d/m/Y', strtotime($row['letter_createtime'])) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs text-gray-500">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <span class="text-sm text-gray-600"><?= htmlspecialchars($row['letter_made']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-all">
                                    <a href="letter_view.php?id=<?= $row['letter_id'] ?>" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors bg-white shadow-sm" title="ดู">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>
                                    <a href="letter_edit.php?id=<?= $row['letter_id'] ?>" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 transition-colors bg-white shadow-sm" title="แก้ไข">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <button onclick="deleteLetter(<?= $row['letter_id'] ?>)" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors bg-white shadow-sm" title="ลบ">
                                        <i class="fas fa-trash-alt text-xs"></i>
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
            <div class="px-6 py-4 border-t border-gray-100 flex justify-center">
                <nav class="flex gap-1">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"
                           class="w-8 h-8 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= $i == $page ? 'bg-blue-600 text-white shadow-md shadow-blue-200' : 'bg-white text-gray-500 hover:bg-gray-50 border border-gray-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('letterManage', () => ({
            checkAll: false,
            selected: [],
            bulkDelete() {
                Swal.fire({
                    title: `ยืนยันลบ ${this.selected.length} รายการ?`,
                    text: "ข้อมูลและไฟล์จะถูกลบถาวร",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก',
                    reverseButtons: true
                }).then((r) => {
                    if (r.isConfirmed) {
                        fetch('letter_update.php', {
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

    // Wrapper injection
    document.querySelector('[x-data]').setAttribute('x-data', 'letterManage');

    function deleteLetter(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "ข้อมูลจะถูกลบถาวร",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('letter_update.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ action: 'delete', id: id })
                })
                .then(r => r.json())
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
</script>
<style>
    [x-cloak] { display: none !important; }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
