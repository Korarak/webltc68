<?php
// filepath: /home/adm1n_ltc/webltc67/www/admin/personel_manage.php
include 'middleware.php';
session_start();

// --- Toast Message Handling ---
$toast_data = null;
if (isset($_SESSION['toast_message'])) {
    $toast_data = $_SESSION['toast_message'];
    unset($_SESSION['toast_message']);
}
?>
<?php
ob_start();
include '../condb/condb.php';

// --- Functions ---
function getOptions($table, $id_field, $name_field, $selected_id = null) {
    global $mysqli3;
    $query = "SELECT $id_field, $name_field FROM $table ORDER BY $name_field";
    $result = $mysqli3->query($query);
    $options = '<option value="0">- ทั้งหมด -</option>';
    while ($row = $result->fetch_assoc()) {
        $selected = ($row[$id_field] == $selected_id) ? 'selected' : '';
        $options .= "<option value='{$row[$id_field]}' $selected>{$row[$name_field]}</option>";
    }
    return $options;
}

function getDepartmentGroups() {
    global $mysqli3;
    $query = "SELECT d.id, d.department_name, 
                     CASE 
                         WHEN d.department_name LIKE 'ฝ่าย%' THEN 'ฝ่ายบริหาร'
                         WHEN d.department_name LIKE 'แผนก%' THEN 'แผนกวิชาการ'
                         ELSE 'อื่นๆ'
                     END as group_type
              FROM department d 
              ORDER BY group_type, d.department_name";
    $result = $mysqli3->query($query);
    $groups = ['ฝ่ายบริหาร' => [], 'แผนกวิชาการ' => [], 'อื่นๆ' => []];
    while ($row = $result->fetch_assoc()) {
        $groups[$row['group_type']][] = $row;
    }
    return $groups;
}

function getWorkbranchGroups() {
    global $mysqli3;
    $query = "SELECT wb.id, wb.workbranch_name, d.department_name
              FROM workbranch wb
              LEFT JOIN department d ON wb.department_id = d.id
              ORDER BY d.department_name, wb.workbranch_name";
    $result = $mysqli3->query($query);
    $groups = [];
    while ($row = $result->fetch_assoc()) {
        $groupName = $row['department_name'] ?: 'อื่นๆ';
        $groups[$groupName][] = $row;
    }
    return $groups;
}

// --- Parameters & Filtering ---
$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

$filters = [
    'position_id' => $_GET['position_id'] ?? 0,
    'position_level_id' => $_GET['position_level_id'] ?? 0,
    'department_id' => $_GET['department_id'] ?? 0,
    'worklevel_id' => $_GET['worklevel_id'] ?? 0,
    'workbranch_id' => $_GET['workbranch_id'] ?? 0,
    'bin' => $_GET['bin'] ?? 0
];

// --- [UX Improvement] Build Current State String ---
// เก็บค่าปัจจุบันทั้งหมด (Page, Filters, Search) ไว้ในตัวแปรเดียว
// เพื่อส่งไปหน้า Add/Edit แล้วส่งกลับมาที่เดิมได้เป๊ะๆ
$current_params = array_merge($_GET, ['page' => $page]);
$current_query_string = http_build_query($current_params);

// Build SQL WHERE
$is_deleted = $filters['bin'] == 1 ? 1 : 0;
$where = "p.is_deleted = $is_deleted AND p.fullname LIKE ?";
$params = ["s", $search_param];

if ($filters['department_id'] != "0") {
    $where .= " AND (p.department_id = ? OR wb.department_id = ?)";
    $params[0] .= "ii"; $params[] = $filters['department_id']; $params[] = $filters['department_id'];
}
if ($filters['position_id'] != "0") { $where .= " AND p.position_id = ?"; $params[0] .= "i"; $params[] = $filters['position_id']; }
if ($filters['position_level_id'] != "0") { $where .= " AND p.position_level_id = ?"; $params[0] .= "i"; $params[] = $filters['position_level_id']; }
if ($filters['worklevel_id'] != "0") { $where .= " AND FIND_IN_SET(?, wd.worklevel_id)"; $params[0] .= "i"; $params[] = $filters['worklevel_id']; }
if ($filters['workbranch_id'] != "0") { $where .= " AND FIND_IN_SET(?, wd.workbranch_id)"; $params[0] .= "i"; $params[] = $filters['workbranch_id']; }

// Count Total
$sql_count = "SELECT COUNT(DISTINCT p.id) 
              FROM personel_data p 
              LEFT JOIN work_detail wd ON p.id = wd.personel_id 
              LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
              WHERE $where";
$stmt_count = $mysqli3->prepare($sql_count);
$stmt_count->bind_param(...$params);
$stmt_count->execute();
$stmt_count->bind_result($total_records);
$stmt_count->fetch();
$stmt_count->close();
$total_pages = $limit === 'all' ? 1 : ceil($total_records / $limit);

// Fetch Data
$query = "SELECT 
            p.id, p.fullname, p.Tel, p.E_mail, p.profile_image, 
            p.education_detail,
            d.department_name as main_department,
            pos.position_name, 
            el.education_name,
            GROUP_CONCAT(DISTINCT wb.workbranch_name ORDER BY wb.workbranch_name SEPARATOR ', ') AS workbranch_names
          FROM personel_data p
          LEFT JOIN department d ON p.department_id = d.id
          LEFT JOIN positions pos ON p.position_id = pos.id
          LEFT JOIN education_level el ON p.education_level_id = el.id
          LEFT JOIN work_detail wd ON p.id = wd.personel_id
          LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
          WHERE $where
          GROUP BY p.id
          ORDER BY p.fullname ASC";

if ($limit !== 'all') {
    $query .= " LIMIT ?, ?";
    $params[0] .= "ii";
    $params[] = $start;
    $params[] = $limit;
}

$stmt = $mysqli3->prepare($query);
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();

$departmentGroups = getDepartmentGroups();
$workbranchGroups = getWorkbranchGroups();
?>

<div class="min-h-screen bg-slate-50 p-4 md:p-8 font-sans">
  <div class="max-w-7xl mx-auto">
    
    <?php if ($toast_data): ?>
    <div id="toastNotification" class="fixed top-5 right-5 z-50 transition-all duration-500 ease-out transform translate-y-0 opacity-100">
        <div class="<?= $toast_data['type'] === 'success' ? 'bg-green-600' : 'bg-red-600' ?> text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 border border-white/20">
            <i class="fas fa-<?= $toast_data['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> text-2xl"></i>
            <div>
                <h4 class="font-bold text-sm uppercase tracking-wider"><?= $toast_data['type'] === 'success' ? 'Success' : 'Error' ?></h4>
                <p class="text-sm font-medium"><?= htmlspecialchars($toast_data['message']) ?></p>
            </div>
            <button onclick="dismissToast()" class="ml-4 hover:bg-white/20 rounded-full p-1 transition"><i class="fas fa-times"></i></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white rounded-2xl p-6 mb-6 shadow-lg flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold flex items-center gap-3">
                <div class="bg-white/10 p-2 rounded-lg"><i class="fas fa-users-cog"></i></div>
                จัดการบุคลากร
            </h1>
            <p class="text-blue-200 mt-2 text-sm pl-1">จัดการข้อมูลพื้นฐาน ตำแหน่ง และสังกัดงานของบุคลากรทั้งหมด</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="personel_manage.php?bin=<?= $is_deleted ? '0' : '1' ?>" class="<?= $is_deleted ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-white/10 text-white border-white/20' ?> hover:bg-white/20 px-5 py-2.5 rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2 border">
                <i class="fas <?= $is_deleted ? 'fa-users' : 'fa-trash-alt' ?>"></i> <?= $is_deleted ? 'รายการปกติ' : 'ถังขยะ' ?>
            </a>
            <a href="personel_add.php?<?= $current_query_string ?>" class="bg-emerald-500 hover:bg-emerald-600 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2 border border-emerald-400">
                <i class="fas fa-plus"></i> เพิ่มใหม่
            </a>
            <a href="import_personnel.php" class="bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg transition-all hover:-translate-y-0.5 flex items-center gap-2 border border-amber-400">
                <i class="fas fa-file-import"></i> นำเข้า
            </a>
        </div>
    </div>

    <?php if($is_deleted): ?>
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-xl shadow-sm">
        <div class="flex items-center gap-3">
            <i class="fas fa-trash-restore text-amber-600 text-xl"></i>
            <div>
                <h4 class="font-bold text-amber-800">โหมดถังขยะ</h4>
                <p class="text-amber-700 text-sm">กำลังแสดงรายการบุคคลที่ถูกลบ คุณสามารถเลือกคืนค่าข้อมููล หรือลบออกถาวรได้</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="sticky top-20 z-30 bg-white/90 backdrop-blur-md rounded-2xl shadow-sm border border-gray-200 p-5 mb-6 transition-all duration-300">
        <form method="GET" id="filterForm" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-4">
                    <div class="relative group">
                        <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ค้นหาชื่อ-สกุล..." 
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                    </div>
                </div>
                <div class="lg:col-span-3">
                     <select name="department_id" class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 shadow-sm cursor-pointer hover:bg-white transition-colors" onchange="this.form.submit()">
                        <option value="0">📂 ทุกแผนก/ฝ่าย</option>
                        <?php foreach($departmentGroups as $groupName => $depts): ?>
                            <?php if($depts): ?>
                            <optgroup label="<?= $groupName ?>">
                                <?php foreach($depts as $d): ?>
                                    <option value="<?= $d['id'] ?>" <?= $filters['department_id'] == $d['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($d['department_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <select name="workbranch_id" class="w-full py-3 px-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 shadow-sm cursor-pointer hover:bg-white transition-colors" onchange="this.form.submit()">
                        <option value="0">💼 ทุกงานในสังกัด</option>
                        <?php foreach($workbranchGroups as $groupName => $wbs): ?>
                            <?php if($wbs): ?>
                            <optgroup label="<?= $groupName ?>">
                                <?php foreach($wbs as $w): ?>
                                    <option value="<?= $w['id'] ?>" <?= $filters['workbranch_id'] == $w['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($w['workbranch_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors shadow-md">
                        ค้นหา
                    </button>
                    <a href="personel_manage.php" class="px-4 flex items-center justify-center bg-gray-100 text-gray-600 rounded-xl hover:bg-red-50 hover:text-red-500 border border-gray-200 transition-colors shadow-sm" title="ล้างค่าการค้นหา">
                        <i class="fas fa-filter-circle-xmark text-lg"></i>
                    </a>
                </div>
            </div>
            
            <input type="hidden" name="bin" value="<?= $is_deleted ?>">

            <div class="pt-4 border-t border-gray-100 grid grid-cols-2 md:grid-cols-4 gap-4">
                <select name="position_id" class="py-2 px-3 bg-white border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="0">- ตำแหน่งทั้งหมด -</option>
                    <?= getOptions('positions', 'id', 'position_name', $filters['position_id']); ?>
                </select>
                <select name="position_level_id" class="py-2 px-3 bg-white border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="0">- วิทยฐานะทั้งหมด -</option>
                    <?= getOptions('position_level', 'id', 'level_name', $filters['position_level_id']); ?>
                </select>
                <select name="worklevel_id" class="py-2 px-3 bg-white border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="0">- ระดับงานทั้งหมด -</option>
                    <?= getOptions('worklevel', 'id', 'work_level_name', $filters['worklevel_id']); ?>
                </select>
                <select name="limit" class="py-2 px-3 bg-white border border-gray-200 rounded-lg text-sm focus:ring-1 focus:ring-blue-500" onchange="this.form.submit()">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>แสดง 10 รายการ</option>
                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>แสดง 20 รายการ</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>แสดง 50 รายการ</option>
                    <option value="all" <?= $limit === 'all' ? 'selected' : '' ?>>แสดงทั้งหมด</option>
                </select>
            </div>
        </form>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="text-gray-600 font-medium bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100">
            พบข้อมูล <span class="text-blue-600 font-bold text-lg"><?= number_format($total_records) ?></span> รายการ 
            <span class="text-sm text-gray-400 border-l border-gray-200 pl-3 ml-2">หน้า <?= $page ?> / <?= $total_pages ?></span>
        </div>
        
        <div class="bg-gray-100 p-1 rounded-xl flex shadow-inner">
            <button onclick="setView('card')" id="btnCardView" class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all">
                <i class="fas fa-th-large"></i> Card
            </button>
            <button onclick="setView('table')" id="btnTableView" class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all">
                <i class="fas fa-list"></i> Table
            </button>
        </div>
    </div>

    <div id="cardView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 hidden opacity-0 transition-opacity duration-300">
        <?php 
        if ($result->num_rows > 0):
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) { 
            // Fix: Access local uploads from admin/ via ../
            $p_img = $row['profile_image'];
            if($p_img && !preg_match("~^(?:f|ht)tps?://~i", $p_img)) {
                $p_img = "/" . ltrim($p_img, '/');
            }
            $imgSrc = $p_img ?: '/uploads/default.png';
        ?>
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 overflow-hidden group transition-all duration-300 hover:-translate-y-1 cursor-pointer"
             onclick="openEditModal(<?= $row['id'] ?>, this)">
            <div class="relative h-56 overflow-hidden bg-slate-100">
                <img src="<?= htmlspecialchars($imgSrc) ?>" class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-60 group-hover:opacity-80 transition-opacity"></div>
                
                <div class="absolute top-3 right-3 bg-white/90 backdrop-blur text-xs font-bold px-2 py-1 rounded text-gray-600 shadow-sm">
                    #<?= $row['id'] ?>
                </div>

                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10 gap-3">
                     <?php if(!$is_deleted): ?>
                     <button onclick="event.stopPropagation(); openEditModal(<?= $row['id'] ?>, this)" class="bg-white text-blue-600 p-3 rounded-full shadow-lg hover:bg-blue-50 hover:scale-110 transition transform" title="แก้ไข">
                        <i class="fas fa-edit text-lg"></i>
                     </button>
                     <a href="personel_delete.php?id=<?= $row['id'] ?>&<?= $current_query_string ?>" onclick="event.stopPropagation(); return confirm('ยืนยันการลบ <?= htmlspecialchars($row['fullname']) ?> ?')" class="bg-white text-red-500 p-3 rounded-full shadow-lg hover:bg-red-50 hover:scale-110 transition transform" title="ลบ">
                        <i class="fas fa-trash-alt text-lg"></i>
                     </a>
                     <?php else: ?>
                     <a href="personel_restore.php?id=<?= $row['id'] ?>&<?= $current_query_string ?>" onclick="event.stopPropagation(); return confirm('คืนค่าข้อมูล <?= htmlspecialchars($row['fullname']) ?> ?')" class="bg-white text-green-600 p-3 rounded-full shadow-lg hover:bg-green-50 hover:scale-110 transition transform" title="คืนค่า">
                        <i class="fas fa-undo text-lg"></i>
                     </a>
                     <a href="personel_delete.php?id=<?= $row['id'] ?>&permanent=1&<?= $current_query_string ?>" onclick="event.stopPropagation(); return confirm('ยืนยันลบถาวร <?= htmlspecialchars($row['fullname']) ?> ? (ไม่สามารถกู้คืนได้!)')" class="bg-white text-red-800 p-3 rounded-full shadow-lg hover:bg-red-100 hover:scale-110 transition transform" title="ลบถาวร">
                        <i class="fas fa-radiation text-lg"></i>
                     </a>
                     <?php endif; ?>
                </div>
            </div>
            <div class="p-5">
                <h3 class="text-lg font-bold text-gray-800 mb-1 truncate group-hover:text-blue-600 transition-colors" title="<?= htmlspecialchars($row['fullname']) ?>">
                    <?= htmlspecialchars($row['fullname']) ?>
                </h3>
                <p class="text-blue-600 text-xs font-bold uppercase tracking-wider mb-3 truncate bg-blue-50 inline-block px-2 py-0.5 rounded"><?= htmlspecialchars($row['position_name'] ?: '-') ?></p>
                
                <div class="space-y-2 text-sm text-gray-500 border-t border-gray-100 pt-3">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-building mt-1 text-gray-400 w-4"></i>
                        <span class="line-clamp-1 flex-1"><?= htmlspecialchars($row['main_department'] ?: '-') ?></span>
                    </div>
                    <?php if($row['workbranch_names']): ?>
                    <div class="flex items-start gap-2">
                        <i class="fas fa-briefcase mt-1 text-gray-400 w-4"></i>
                        <span class="line-clamp-1 flex-1 text-xs"><?= htmlspecialchars($row['workbranch_names']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <a href="personel_add_job.php?id=<?= $row['id'] ?>&<?= $current_query_string ?>" onclick="event.stopPropagation()" class="flex items-center justify-center gap-2 text-emerald-600 hover:text-white hover:bg-emerald-500 text-sm font-semibold px-4 py-2 rounded-lg transition-colors bg-emerald-50 border border-emerald-100 w-full">
                        <i class="fas fa-plus-circle"></i> จัดการภาระงาน
                    </a>
                </div>
            </div>
        </div>
        <?php } else: ?>
            <div class="col-span-full py-16 text-center">
                <div class="bg-white rounded-3xl p-8 inline-block shadow-sm border border-gray-100">
                    <div class="bg-gray-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-3xl">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700">ไม่พบข้อมูล</h3>
                    <p class="text-gray-500 mt-2">ลองปรับเปลี่ยนเงื่อนไขการค้นหาดูใหม่อีกครั้ง</p>
                    <a href="personel_manage.php" class="mt-4 inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">ล้างการค้นหา</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div id="tableView" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hidden opacity-0 transition-opacity duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm uppercase tracking-wider">
                        <th class="px-6 py-4 font-semibold w-16 text-center">#</th>
                        <th class="px-6 py-4 font-semibold">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-4 font-semibold">ตำแหน่ง</th>
                        <th class="px-6 py-4 font-semibold">วุฒิการศึกษา</th>
                        <th class="px-6 py-4 font-semibold">สังกัดหลัก</th>
                        <th class="px-6 py-4 font-semibold">งานในหน้าที่</th>
                        <th class="px-6 py-4 font-semibold text-center w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    if ($result->num_rows > 0):
                    $result->data_seek(0);
                    $count = $start + 1;
                    while ($row = $result->fetch_assoc()) { 
                    ?>
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                        <td class="px-6 py-4 text-center text-gray-400 text-sm"><?= $count++ ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <?php 
                                $p_img = $row['profile_image'];
                                if($p_img && !preg_match("~^(?:f|ht)tps?://~i", $p_img)) {
                                    $p_img = "/" . ltrim($p_img, '/');
                                }
                                if($p_img): 
                                ?>
                                    <img src="<?= htmlspecialchars($p_img) ?>" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-400"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                                <div class="font-semibold text-gray-800"><?= htmlspecialchars($row['fullname']) ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['position_name'] ?: '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="font-medium"><?= htmlspecialchars($row['education_name'] ?: '-') ?></div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($row['education_detail'] ?: '') ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600"><?= htmlspecialchars($row['main_department'] ?: '-') ?></td>
                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="<?= htmlspecialchars($row['workbranch_names']) ?>">
                            <?= htmlspecialchars($row['workbranch_names'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <?php if(!$is_deleted): ?>
                                <button onclick="openEditModal(<?= $row['id'] ?>, this)" class="text-blue-600 hover:bg-blue-100 p-2 rounded-lg transition-colors" title="แก้ไข">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="personel_delete.php?id=<?= $row['id'] ?>&<?= $current_query_string ?>" onclick="return confirm('ยืนยันการลบ?')" class="text-red-500 hover:bg-red-100 p-2 rounded-lg transition-colors" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <?php else: ?>
                                <a href="personel_restore.php?id=<?= $row['id'] ?>&<?= $current_query_string ?>" onclick="return confirm('คืนค่าข้อมูล?')" class="text-green-600 hover:bg-green-100 p-2 rounded-lg transition-colors" title="คืนค่า">
                                    <i class="fas fa-undo"></i>
                                </a>
                                <a href="personel_delete.php?id=<?= $row['id'] ?>&permanent=1&<?= $current_query_string ?>" onclick="return confirm('ยืนยันลบถาวร?')" class="text-red-800 hover:bg-red-100 p-2 rounded-lg transition-colors" title="ลบถาวร">
                                    <i class="fas fa-radiation"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php } else: ?>
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">ไม่พบข้อมูล</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="mt-8 flex justify-center">
        <nav class="inline-flex rounded-xl shadow-sm isolate">
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>" class="relative inline-flex items-center rounded-l-xl px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                <span class="sr-only">First</span>
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => max(1, $page - 1)])) ?>" class="relative inline-flex items-center px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page == 1 ? 'opacity-50 pointer-events-none' : '' ?>">
                <span class="sr-only">Previous</span>
                <i class="fas fa-chevron-left"></i>
            </a>

            <?php
            $range = 2;
            $start_page = max(1, $page - $range);
            $end_page = min($total_pages, $page + $range);

            if ($start_page > 1) {
                echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => 1])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">1</a>';
                if ($start_page > 2) echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>';
            }

            for ($i = $start_page; $i <= $end_page; $i++) {
                if ($i == $page) {
                    echo '<a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">' . $i . '</a>';
                } else {
                    echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $i])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">' . $i . '</a>';
                }
            }

            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) echo '<span class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">...</span>';
                echo '<a href="?' . http_build_query(array_merge($_GET, ['page' => $total_pages])) . '" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">' . $total_pages . '</a>';
            }
            ?>

            <a href="?<?= http_build_query(array_merge($_GET, ['page' => min($total_pages, $page + 1)])) ?>" class="relative inline-flex items-center px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page == $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">
                <span class="sr-only">Next</span>
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>" class="relative inline-flex items-center rounded-r-xl px-3 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 <?= $page == $total_pages ? 'opacity-50 pointer-events-none' : '' ?>">
                <span class="sr-only">Last</span>
                <i class="fas fa-angle-double-right"></i>
            </a>
        </nav>
    </div>
    <?php endif; ?>

  </div>
</div>

<div id="editModal" class="fixed inset-0 hidden z-[9999]" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modalPanel">
                <div class="bg-white px-6 pb-4 pt-5 sm:p-6 sm:pb-4 border-b border-gray-100 flex justify-between items-center sticky top-0 z-10">
                    <h3 class="text-xl font-bold leading-6 text-gray-900 flex items-center gap-2">
                        <div class="bg-blue-100 p-2 rounded-lg text-blue-600"><i class="fas fa-user-edit"></i></div>
                        แก้ไขข้อมูลบุคลากร
                    </h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 bg-gray-100 hover:bg-red-50 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div id="editModalContent" class="bg-white px-6 py-6 max-h-[calc(100vh-200px)] overflow-y-auto custom-scrollbar"></div>
            </div>
        </div>
    </div>
</div>

<div id="profileModal" class="fixed inset-0 hidden bg-black/80 items-center justify-center z-[100] p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 max-w-2xl w-full shadow-2xl flex flex-col max-h-[90vh]">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-crop-alt text-blue-600"></i> ปรับแต่งรูปภาพ
        </h3>
        <div class="flex-1 overflow-hidden bg-gray-900 rounded-lg relative min-h-[300px]">
            <img id="profileCropPreview" class="max-w-full max-h-full block"/>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" id="profileModal_cancel" class="px-5 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition-colors">ยกเลิก</button>
            <button type="button" id="profileModal_confirm" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors shadow-lg">ยืนยันรูปภาพ</button>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
// --- Toast Logic ---
function dismissToast() {
    const toast = document.getElementById('toastNotification');
    if(toast) {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 500);
    }
}
// Auto dismiss after 3 seconds
setTimeout(dismissToast, 3000);

// --- Cropper ---
let cropper;
function initImageCrop(inputId, modalId, previewId, hiddenInputId, aspectRatio = 3/4, outputWidth = 600, outputHeight = 800) {
    const uploadInput = document.getElementById(inputId);
    const cropModal = document.getElementById(modalId);
    const imagePreview = document.getElementById(previewId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const previewContainer = document.getElementById('profilePreviewContainer');
    const finalPreview = document.getElementById('profilePreview');

    if (!uploadInput) return;

    uploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF) เท่านั้น');
            uploadInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            imagePreview.src = reader.result;
            cropModal.classList.remove('hidden');
            if (cropper) cropper.destroy();
            cropper = new Cropper(imagePreview, {
                aspectRatio: aspectRatio,
                viewMode: 1,
                autoCropArea: 0.8,
                movable: true,
                rotatable: true,
                scalable: true,
                zoomable: true
            });
        };
        reader.readAsDataURL(file);
    });

    const cancelBtn = document.getElementById(modalId + "_cancel");
    const confirmBtn = document.getElementById(modalId + "_confirm");

    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            if (cropper) cropper.destroy();
            cropModal.classList.add('hidden');
            uploadInput.value = '';
            if (previewContainer) previewContainer.classList.add('hidden');
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            if (!cropper) return;
            const canvas = cropper.getCroppedCanvas({ width: outputWidth, height: outputHeight });
            if (finalPreview) finalPreview.src = canvas.toDataURL("image/jpeg", 0.9);
            if (previewContainer) previewContainer.classList.remove('hidden');
            if (hiddenInput) hiddenInput.value = canvas.toDataURL("image/jpeg", 0.9);
            cropper.destroy();
            cropModal.classList.add('hidden');
        });
    }
}

// --- View Logic ---
function setView(view) {
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');
    const btnCard = document.getElementById('btnCardView');
    const btnTable = document.getElementById('btnTableView');
    
    localStorage.setItem('personnelViewMode', view);

    if (view === 'card') {
        cardView.classList.remove('hidden');
        setTimeout(() => cardView.classList.remove('opacity-0'), 10);
        tableView.classList.add('hidden', 'opacity-0');
        btnCard.className = "px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all bg-white text-blue-600 shadow-md";
        btnTable.className = "px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all text-gray-500 hover:bg-gray-200";
    } else {
        tableView.classList.remove('hidden');
        setTimeout(() => tableView.classList.remove('opacity-0'), 10);
        cardView.classList.add('hidden', 'opacity-0');
        btnTable.className = "px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all bg-white text-blue-600 shadow-md";
        btnCard.className = "px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all text-gray-500 hover:bg-gray-200";
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const savedView = localStorage.getItem('personnelViewMode') || 'card';
    setView(savedView);
});

// --- Modal Logic ---
const modal = document.getElementById('editModal');
const backdrop = document.getElementById('modalBackdrop');
const panel = document.getElementById('modalPanel');
const currentQueryString = '<?= $current_query_string ?>';

function openEditModal(personelId, btn = null) {
    // Show loading on button if clicked
    let originalContent = '';
    if(btn) {
        originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
    }

    modal.classList.remove('hidden');
    // Prepare Modal UI (Show skeleton or spinner first)
    document.getElementById('editModalContent').innerHTML = `<div class="flex justify-center py-12"><i class="fas fa-circle-notch fa-spin text-4xl text-blue-500"></i></div>`;
    
    setTimeout(() => {
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    }, 10);

    const url = `personel_edit_modal.php?id=${personelId}&return_query=${encodeURIComponent(currentQueryString)}`;
    
    fetch(url)
        .then(res => res.text())
        .then(html => {
            document.getElementById('editModalContent').innerHTML = html;
            if (typeof initImageCrop === 'function') {
                 setTimeout(() => initImageCrop("profileInput", "profileModal", "profileCropPreview", "profileBase64"), 100);
            }
        })
        .catch(err => {
            document.getElementById('editModalContent').innerHTML = `<div class="text-center text-red-500 py-8"><i class="fas fa-exclamation-triangle"></i> โหลดข้อมูลไม่สำเร็จ</div>`;
        })
        .finally(() => {
            // Restore button state
            if(btn) {
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        });
}

function closeEditModal() {
    backdrop.classList.add('opacity-0');
    panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

modal.addEventListener('click', (e) => {
    if (e.target === backdrop || e.target.closest('#modalPanel') === null) {
        closeEditModal();
    }
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>