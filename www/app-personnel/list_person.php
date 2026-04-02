<?php
//list_person.php
$title = "บุคลากร";
ob_start();
require '../condb/condb.php';

// --- (ฟังก์ชัน PHP ทั้งหมดเหมือนเดิม ไม่มีการแก้ไข) ---
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

function getDivisions() {
    global $mysqli3;
    $query = "SELECT d.id, d.department_name FROM department d WHERE d.department_name LIKE 'ฝ่าย%' ORDER BY d.department_name";
    $result = $mysqli3->query($query);
    $divisions = [];
    while ($row = $result->fetch_assoc()) { $divisions[] = $row; }
    return $divisions;
}

function getDepartments() {
    global $mysqli3;
    $query = "SELECT d.id, d.department_name FROM department d WHERE d.department_name LIKE 'แผนก%' ORDER BY d.department_name";
    $result = $mysqli3->query($query);
    $departments = [];
    while ($row = $result->fetch_assoc()) { $departments[] = $row; }
    return $departments;
}

function getWorkbranchGroups() {
    global $mysqli3;
    $query = "SELECT wb.id, wb.workbranch_name, d.department_name,
              CASE WHEN d.department_name LIKE 'ฝ่าย%' THEN d.department_name ELSE 'แผนกวิชาการ' END as group_name
              FROM workbranch wb LEFT JOIN department d ON wb.department_id = d.id
              ORDER BY CASE WHEN d.department_name LIKE 'ฝ่าย%' THEN 1 ELSE 2 END, d.department_name, wb.workbranch_name";
    $result = $mysqli3->query($query);
    $groups = [];
    while ($row = $result->fetch_assoc()) {
        $groupName = $row['group_name'];
        if (!isset($groups[$groupName])) { $groups[$groupName] = []; }
        $groups[$groupName][] = $row;
    }
    return $groups;
}

$limit = isset($_GET['limit']) && $_GET['limit'] !== 'all' ? (int)$_GET['limit'] : 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$filters = [
    'position_id' => $_GET['position_id'] ?? 0,
    'position_level_id' => $_GET['position_level_id'] ?? 0,
    'division_id' => $_GET['division_id'] ?? 0,
    'department_id' => $_GET['department_id'] ?? 0,
    'worklevel_id' => $_GET['worklevel_id'] ?? 0,
    'workbranch_id' => $_GET['workbranch_id'] ?? 0
];

$where = "p.is_deleted = 0 AND p.fullname LIKE ?";
$params = ["s", $search];

if ($filters['division_id'] != "0") {
    $where .= " AND (p.department_id = ? OR wb.department_id = ?)";
    $params[0] .= "ii"; $params[] = $filters['division_id']; $params[] = $filters['division_id'];
}
if ($filters['department_id'] != "0") {
    $where .= " AND (p.department_id = ? OR wb.department_id = ?)";
    $params[0] .= "ii"; $params[] = $filters['department_id']; $params[] = $filters['department_id'];
}
$other_filters = ['position_id', 'position_level_id', 'worklevel_id', 'workbranch_id'];
foreach ($other_filters as $filter) {
    if ($filters[$filter] != "0") {
        if ($filter === 'worklevel_id' || $filter === 'workbranch_id') {
            $where .= " AND FIND_IN_SET(?, wd.$filter)";
        } else {
            $where .= " AND p.$filter = ?";
        }
        $params[0] .= "i"; $params[] = $filters[$filter];
    }
}

$sql_count = "SELECT COUNT(DISTINCT p.id) FROM personel_data p LEFT JOIN work_detail wd ON p.id = wd.personel_id LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id WHERE $where";
$stmt_count = $mysqli3->prepare($sql_count);
$stmt_count->bind_param(...$params);
$stmt_count->execute();
$stmt_count->bind_result($total_records);
$stmt_count->fetch();
$stmt_count->close();
$total_pages = ceil($total_records / $limit);

$query = "SELECT p.id, p.fullname, p.Tel, p.E_mail, p.department_id, p.position_id, p.position_level_id, p.profile_image, 
            d.department_name as main_department, pos.position_name, pl.level_name, 
            GROUP_CONCAT(DISTINCT wb.workbranch_name ORDER BY wb.workbranch_name SEPARATOR ', ') AS workbranch_names,
            GROUP_CONCAT(DISTINCT wl.work_level_name ORDER BY wl.work_level_name SEPARATOR ', ') AS worklevel_names,
            GROUP_CONCAT(DISTINCT wbd.department_name ORDER BY wbd.department_name SEPARATOR ', ') AS work_departments,
            GROUP_CONCAT(DISTINCT CONCAT(wb.workbranch_name, ' (', wl.work_level_name, ')') SEPARATOR '|||') AS exact_work_roles
          FROM personel_data p
          LEFT JOIN department d ON p.department_id = d.id
          LEFT JOIN positions pos ON p.position_id = pos.id
          LEFT JOIN position_level pl ON p.position_level_id = pl.id
          LEFT JOIN work_detail wd ON p.id = wd.personel_id
          LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
          LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id
          LEFT JOIN department wbd ON wb.department_id = wbd.id
          WHERE $where GROUP BY p.id 
          ORDER BY 
            CASE 
                WHEN SUM(CASE WHEN wl.id = 5 THEN 1 ELSE 0 END) > 0 THEN 1 
                WHEN SUM(CASE WHEN wl.id = 1 THEN 1 ELSE 0 END) > 0 THEN 2 
                WHEN p.position_id IN (3, 4) THEN 3 
                WHEN p.position_id IN (5, 6) THEN 4 
                WHEN p.position_id = 9 THEN 5 
                ELSE 6 
            END ASC,
            CASE WHEN p.position_id IN (3, 4) THEN p.position_level_id ELSE 0 END DESC,
            p.fullname ASC 
          LIMIT ?, ?";

$params[0] .= "ii"; $params[] = $start; $params[] = $limit;
$stmt = $mysqli3->prepare($query);
$stmt->bind_param(...$params);
$stmt->execute();
$result = $stmt->get_result();

$divisions = getDivisions();
$departments = getDepartments();
$workbranchGroups = getWorkbranchGroups();

// Logic ดึงชื่อที่เลือก (ย่อไว้เหมือนเดิม)
$selected_division_name = ''; $selected_department_name = ''; $selected_position_name = ''; $selected_workbranch_name = '';
if ($filters['division_id'] != 0) { /* ...Query Name... */ $div_stmt = $mysqli3->prepare("SELECT department_name FROM department WHERE id = ?"); $div_stmt->bind_param("i", $filters['division_id']); $div_stmt->execute(); $div_stmt->bind_result($selected_division_name); $div_stmt->fetch(); $div_stmt->close(); }
if ($filters['department_id'] != 0) { /* ...Query Name... */ $dept_stmt = $mysqli3->prepare("SELECT department_name FROM department WHERE id = ?"); $dept_stmt->bind_param("i", $filters['department_id']); $dept_stmt->execute(); $dept_stmt->bind_result($selected_department_name); $dept_stmt->fetch(); $dept_stmt->close(); }
if ($filters['position_id'] != 0) { /* ...Query Name... */ $pos_stmt = $mysqli3->prepare("SELECT position_name FROM positions WHERE id = ?"); $pos_stmt->bind_param("i", $filters['position_id']); $pos_stmt->execute(); $pos_stmt->bind_result($selected_position_name); $pos_stmt->fetch(); $pos_stmt->close(); }
if ($filters['workbranch_id'] != 0) { /* ...Query Name... */ $wb_stmt = $mysqli3->prepare("SELECT workbranch_name FROM workbranch WHERE id = ?"); $wb_stmt->bind_param("i", $filters['workbranch_id']); $wb_stmt->execute(); $wb_stmt->bind_result($selected_workbranch_name); $wb_stmt->fetch(); $wb_stmt->close(); }
?>

<div class="w-full mt-[72px] p-4">
    <div class="grid grid-cols-12 gap-6">

        <div class="hidden xl:block xl:col-span-1">
            </div>

        <div class="col-span-12 xl:col-span-11 flex flex-col lg:flex-row lg:gap-6">

            <main class="flex-1 w-full">
            
            <div class="bg-gradient-to-r from-green-600 to-green-700 text-white text-center py-4 rounded-xl shadow-lg mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold mb-1"><i class="fas fa-users mr-2"></i>ค้นหาบุคลากร</h1>
                <p class="text-sm text-green-100">ผลการค้นหา: <span class="font-bold text-lg"><?= number_format($total_records) ?></span> รายการ</p>
            </div>

            <div class="lg:hidden mb-6">
                <div class="bg-white rounded-xl shadow-md p-4 mb-4 border border-green-100">
                <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                    <i class="fas fa-search text-green-600 mr-2"></i>ค้นหาด้วยชื่อ
                </h3>
                
                <form method="GET" id="mobileSearchForm" class="flex flex-col sm:flex-row gap-2">
                    <input type="text" name="search" placeholder="🔍 พิมพ์ชื่อบุคลากร..." 
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                        class="flex-1 border-2 border-green-300 px-4 py-3 rounded-lg focus:ring-2 focus:ring-green-400 focus:border-green-400 transition text-sm">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors whitespace-nowrap">
                    <i class="fas fa-search mr-2"></i>ค้นหา
                    </button>
                </form>
                </div>

                <details class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl shadow-md p-4 border border-blue-200 group">
                <summary class="cursor-pointer flex items-center justify-between font-bold text-blue-800 mb-4">
                    <span><i class="fas fa-filter text-blue-600 mr-2"></i>แสดง/ซ่อนตัวกรอง</span>
                    <i class="fas fa-chevron-down group-open:rotate-180 transition"></i>
                </summary>

                <form method="GET" id="mobileFilterForm" class="space-y-4">
                    
                    <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-briefcase text-blue-600 mr-2"></i>ตำแหน่ง
                    </label>
                    <select name="position_id" class="w-full border-2 border-blue-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-blue-400 transition text-sm bg-white">
                        <option value="0">- เลือกตำแหน่ง -</option>
                        <?php 
                        $pos_query = "SELECT id, position_name FROM positions ORDER BY position_name";
                        $pos_result = $mysqli3->query($pos_query);
                        while ($p = $pos_result->fetch_assoc()) {
                        $selected = $filters['position_id'] == $p['id'] ? 'selected' : '';
                        echo "<option value='{$p['id']}' {$selected}>" . htmlspecialchars($p['position_name']) . "</option>";
                        }
                        ?>
                    </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-building text-purple-600 mr-2"></i>ฝ่าย
                        </label>
                        <select name="division_id" class="w-full border-2 border-purple-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-purple-400 transition text-sm bg-white">
                        <option value="0">- เลือกฝ่าย -</option>
                        <?php foreach ($divisions as $div) {
                            $selected = $filters['division_id'] == $div['id'] ? 'selected' : '';
                            echo "<option value='{$div['id']}' {$selected}>" . htmlspecialchars($div['department_name']) . "</option>";
                        } ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-school text-emerald-600 mr-2"></i>แผนก
                        </label>
                        <select name="department_id" class="w-full border-2 border-emerald-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-emerald-400 transition text-sm bg-white">
                        <option value="0">- เลือกแผนก -</option>
                        <?php foreach ($departments as $dept) {
                            $selected = $filters['department_id'] == $dept['id'] ? 'selected' : '';
                            echo "<option value='{$dept['id']}' {$selected}>" . htmlspecialchars($dept['department_name']) . "</option>";
                        } ?>
                        </select>
                    </div>
                    </div>

                    <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        <i class="fas fa-tasks text-cyan-600 mr-2"></i>ประเภทงาน
                    </label>
                    <select name="workbranch_id" class="w-full border-2 border-cyan-300 px-4 py-2 rounded-lg focus:ring-2 focus:ring-cyan-400 transition text-sm bg-white">
                        <option value="0">- เลือกประเภทงาน -</option>
                        <?php foreach($workbranchGroups as $groupName => $workbranches): ?>
                        <?php if(!empty($workbranches)): ?>
                            <optgroup label="<?= htmlspecialchars($groupName) ?>">
                            <?php foreach($workbranches as $wb): ?>
                                <option value="<?= $wb['id'] ?>" <?= ($filters['workbranch_id'] == $wb['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($wb['workbranch_name']) ?>
                                </option>
                            <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    </div>

                    <details class="bg-white rounded-lg p-3 border border-gray-200 group">
                    <summary class="cursor-pointer flex items-center justify-between font-semibold text-gray-700 text-sm">
                        <span><i class="fas fa-sliders-h mr-2"></i>ตัวกรองเพิ่มเติม</span>
                        <i class="fas fa-chevron-down group-open:rotate-180 transition text-xs"></i>
                    </summary>
                    
                    <div class="mt-3 space-y-3">
                        <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fas fa-certificate text-orange-600 mr-2"></i>วิทยฐานะ
                        </label>
                        <select name="position_level_id" class="w-full border-2 border-orange-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-orange-400 transition text-xs bg-white">
                            <option value="0">- เลือก -</option>
                            <?= getOptions('position_level', 'id', 'level_name', $filters['position_level_id']) ?>
                        </select>
                        </div>

                        <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fas fa-chart-line text-red-600 mr-2"></i>ระดับงาน
                        </label>
                        <select name="worklevel_id" class="w-full border-2 border-red-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-red-400 transition text-xs bg-white">
                            <option value="0">- เลือก -</option>
                            <?= getOptions('worklevel', 'id', 'work_level_name', $filters['worklevel_id']) ?>
                        </select>
                        </div>

                        <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">
                            <i class="fas fa-list text-indigo-600 mr-2"></i>จำนวนแสดง
                        </label>
                        <select name="limit" class="w-full border-2 border-indigo-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-indigo-400 transition text-xs bg-white">
                            <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 รายการ</option>
                            <option value="48" <?= $limit == 48 ? 'selected' : '' ?>>48 รายการ</option>
                            <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 รายการ</option>
                            <option value="999" <?= $limit >= 999 ? 'selected' : '' ?>>แสดงทั้งหมด</option>
                        </select>
                        </div>
                    </div>
                    </details>

                    <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i>ใช้ตัวกรอง
                    </button>
                    <a href="?" class="w-full text-center bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        <i class="fas fa-times-circle mr-1"></i>ล้างตัวกรอง
                    </a>
                    </div>

                    <?php foreach($_GET as $key => $value): ?>
                    <?php if(!in_array($key, ['position_id', 'position_level_id', 'division_id', 'department_id', 'worklevel_id', 'workbranch_id', 'limit', 'page'])): ?>
                        <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </form>
                </details>
            </div>

            <div class="mb-4 flex flex-wrap gap-2">
                <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                <span class="bg-green-200 text-green-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    🔍 <?= htmlspecialchars($_GET['search']) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['search' => ''])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
                
                <?php if($filters['position_id'] != 0): ?>
                <span class="bg-blue-200 text-blue-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    📋 <?= htmlspecialchars($selected_position_name) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['position_id' => '0'])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
                
                <?php if($filters['division_id'] != 0): ?>
                <span class="bg-purple-200 text-purple-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    🏢 <?= htmlspecialchars($selected_division_name) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['division_id' => '0'])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
                
                <?php if($filters['department_id'] != 0): ?>
                <span class="bg-emerald-200 text-emerald-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    🎓 <?= htmlspecialchars($selected_department_name) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['department_id' => '0'])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
                
                <?php if($filters['workbranch_id'] != 0): ?>
                <span class="bg-cyan-200 text-cyan-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    ✓ <?= htmlspecialchars($selected_workbranch_name) ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['workbranch_id' => '0'])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
                
                <?php if($filters['position_level_id'] != 0): ?>
                <span class="bg-orange-200 text-orange-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    ⭐ 
                    <?php $pl = $mysqli3->query("SELECT level_name FROM position_level WHERE id = {$filters['position_level_id']}")->fetch_assoc(); echo htmlspecialchars($pl['level_name'] ?? ''); ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['position_level_id' => '0'])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
                
                <?php if($filters['worklevel_id'] != 0): ?>
                <span class="bg-red-200 text-red-800 px-3 py-1 rounded-full text-xs sm:text-sm font-semibold inline-flex items-center gap-2">
                    📊 
                    <?php $wl = $mysqli3->query("SELECT work_level_name FROM worklevel WHERE id = {$filters['worklevel_id']}")->fetch_assoc(); echo htmlspecialchars($wl['work_level_name'] ?? ''); ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['worklevel_id' => '0'])) ?>" class="hover:opacity-70 cursor-pointer">✕</a>
                </span>
                <?php endif; ?>
            </div>

            <?php if ($total_records > 0): ?>
                <?php
                // Fetch all records for the current page
                $persons_data = [];
                while ($row = $result->fetch_assoc()) {
                    $persons_data[] = $row;
                }
                
                // Determine View Mode
                $view_mode = $_GET['view'] ?? 'card';
                $show_toggle = ($filters['department_id'] != 0 || $filters['workbranch_id'] != 0);
                $is_org_view = ($show_toggle && $view_mode === 'org');
                ?>

                <?php if ($show_toggle): ?>
                <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4 bg-white p-3 rounded-xl shadow-sm border border-green-100">
                    <h2 class="text-lg font-bold text-green-800 flex items-center gap-2">
                        <i class="fas fa-sitemap mt-0.5"></i> รูปแบบการแสดงผล
                    </h2>
                    <div class="bg-gray-100 rounded-lg p-1 shadow-inner inline-flex">
                        <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'card'])) ?>" class="px-4 py-2 text-sm font-semibold rounded-md transition-all duration-200 <?= (!$is_org_view) ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-800' ?>">
                            <i class="fas fa-th-large mr-1"></i> การ์ด
                        </a>
                        <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'org'])) ?>" class="px-4 py-2 text-sm font-semibold rounded-md transition-all duration-200 <?= ($is_org_view) ? 'bg-white text-green-700 shadow-sm' : 'text-gray-500 hover:text-gray-800' ?>">
                            <i class="fas fa-sitemap mr-1"></i> ผังองค์กร
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php
                // Helper function to render a single person card
                $renderCard = function($row) {
                    $profile = $row['profile_image'] ?? '';
                    $imgSrc = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22500%22%3E%3Crect fill=%22%23d1fae5%22 width=%22400%22 height=%22500%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 font-family=%22Arial%22 font-size=%2248%22 fill=%22%23059669%22%3E👤%3C/text%3E%3C/svg%3E';
                    if (!empty($profile)) {
                        if (preg_match('#^https?://#i', $profile)) {
                            $imgSrc = $profile;
                        } else {
                            $candidate = __DIR__ . '/../' . ltrim($profile, '/');
                            if (file_exists($candidate) && is_file($candidate)) {
                                $imgSrc = '/' . ltrim($profile, '/');
                            } else {
                                $candidateAdmin = __DIR__ . '/../admin/' . ltrim($profile, '/');
                                if (file_exists($candidateAdmin) && is_file($candidateAdmin)) {
                                    $imgSrc = '/admin/' . ltrim($profile, '/');
                                }
                            }
                        }
                    }
                    ?>
                    <div class="bg-white shadow-lg rounded-xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer person-card group h-full flex flex-col w-full border border-gray-100" data-id="<?= htmlspecialchars($row['id']) ?>">
                        <div class="aspect-[3/4] bg-gradient-to-br from-green-50 to-green-100 relative overflow-hidden flex-shrink-0">
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($row['fullname']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
                        </div>
                        <div class="p-3 sm:p-4 flex flex-col flex-1">
                            <h3 class="text-sm font-bold text-green-800 mb-2 line-clamp-2"><?= htmlspecialchars($row['fullname']) ?></h3>
                            <div class="space-y-1.5 text-xs text-gray-600 flex-1">
                                <?php if($row['position_name']): ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-briefcase text-green-600 flex-shrink-0 mt-0.5 w-3 text-center"></i>
                                    <span class="flex-1 line-clamp-1" title="<?= htmlspecialchars($row['position_name']) ?>"><?= htmlspecialchars($row['position_name']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($row['main_department']): ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-building text-green-600 flex-shrink-0 mt-0.5 w-3 text-center"></i>
                                    <span class="flex-1 line-clamp-1" title="<?= htmlspecialchars($row['main_department']) ?>"><?= htmlspecialchars($row['main_department']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($row['position_name'] == 'ข้าราชการครู' && !empty($row['level_name'])): ?>
                                <div class="flex items-start gap-2 pt-0.5">
                                    <i class="fas fa-certificate text-orange-500 flex-shrink-0 mt-0.5 w-3 text-center"></i>
                                    <span class="text-orange-700 font-bold italic">วิทยฐานะ: <?= htmlspecialchars($row['level_name']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php
                                $tasks = array_filter([$row['worklevel_names'], $row['workbranch_names']]);
                                if(!empty($tasks)): 
                                    $task_str = join(', ', $tasks);
                                ?>
                                <div class="flex items-start gap-2">
                                    <i class="fas fa-tasks text-blue-500 flex-shrink-0 mt-0.5 w-3 text-center"></i>
                                    <span class="flex-1 line-clamp-2" title="<?= htmlspecialchars($task_str) ?>"><?= htmlspecialchars($task_str) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3 pt-2 border-t border-gray-100 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex-shrink-0">
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-3 rounded-lg text-xs font-semibold transition-colors flex justify-center items-center gap-1">
                                    <i class="fas fa-eye"></i> ดูรายละเอียด
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php
                };
                ?>

                <?php if ($is_org_view): ?>
                    <!-- Org Chart View -->
                    <?php
                    $heads = [];
                    $members = [];
                    foreach ($persons_data as $p) {
                        $is_head = false;
                        $exact_roles = explode('|||', $p['exact_work_roles'] ?? '');
                        
                        // Robust normalization for Thai department names (space vs dash)
                        $norm_dept = str_replace([' ', '-'], '', $selected_department_name);
                        $norm_wb = str_replace([' ', '-'], '', $selected_workbranch_name);

                        foreach ($exact_roles as $role) {
                            $norm_role = str_replace([' ', '-'], '', $role);

                            if ($filters['department_id'] != 0 && !empty($selected_department_name)) {
                                if (strpos($norm_role, $norm_dept) !== false && (strpos($role, 'หัวหน้างาน') !== false || strpos($role, 'หัวหน้าแผนก') !== false)) {
                                    $is_head = true; break;
                                }
                            }
                            if ($filters['workbranch_id'] != 0 && !empty($selected_workbranch_name)) {
                                if (strpos($norm_role, $norm_wb) !== false && strpos($role, 'หัวหน้า') !== false) {
                                    $is_head = true; break;
                                }
                            }
                        }
                        
                        if ($is_head) {
                            $heads[] = $p;
                        } else {
                            $members[] = $p;
                        }
                    }
                    ?>
                    
                    <div class="org-chart-container py-10 px-2 sm:px-6 mb-8 bg-gradient-to-b from-green-50 to-white rounded-2xl shadow-sm border border-green-100 w-full">
                        <div class="flex flex-col items-center w-full">
                            <!-- Heads Section -->
                            <div class="flex flex-wrap justify-center gap-6 sm:gap-8 mb-2">
                                <?php foreach ($heads as $head): ?>
                                    <div class="w-56 sm:w-64 relative">
                                        <div class="absolute -top-4 w-full flex justify-center z-10">
                                            <span class="bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full shadow-md border border-yellow-500 flex items-center gap-1">
                                                <i class="fas fa-star text-xs"></i> หัวหน้าแผนก
                                            </span>
                                        </div>
                                        <?php $renderCard($head); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Connector Indicator -->
                            <?php if (!empty($members) && !empty($heads)): ?>
                            <div class="flex flex-col items-center justify-center my-4 text-green-400">
                                <div class="w-[2px] h-6 sm:h-8 bg-green-400"></div>
                                <i class="fas fa-chevron-down text-lg"></i>
                            </div>
                            <?php endif; ?>

                            <!-- Members Section -->
                            <?php if (!empty($members)): ?>
                                <div class="flex flex-wrap justify-center gap-4 sm:gap-6 mt-2 max-w-7xl">
                                    <?php foreach ($members as $member): ?>
                                        <div class="w-44 sm:w-56">
                                            <?php $renderCard($member); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- Normal Grid Card View -->
                    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6 mb-8">
                        <?php foreach ($persons_data as $row) { 
                            $renderCard($row);
                        } ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl shadow border border-gray-200">
                <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">ไม่พบข้อมูล</h3>
                <p class="text-gray-500 mb-4 text-sm">ลองเปลี่ยนเงื่อนไขการค้นหา</p>
                <a href="?" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold inline-flex items-center gap-2 text-sm">
                    <i class="fas fa-undo"></i> ล้างการค้นหา
                </a>
                </div>
            <?php endif; ?>

            <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex justify-center">
                <nav class="flex flex-wrap gap-2 justify-center">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>"
                        class="px-3 py-2 border border-green-300 bg-white text-green-700 rounded-lg hover:bg-green-50 font-semibold transition-colors text-xs">
                    <i class="fas fa-chevron-left mr-1"></i>ก่อนหน้า
                    </a>
                <?php endif; ?>

                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): 
                ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                        class="px-3 py-2 border rounded-lg font-semibold transition-colors text-xs <?= $i == $page ? 'bg-green-600 text-white border-green-600' : 'bg-white text-green-700 border-green-300 hover:bg-green-50' ?>">
                    <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>"
                        class="px-3 py-2 border border-green-300 bg-white text-green-700 rounded-lg hover:bg-green-50 font-semibold transition-colors text-xs">
                    ถัดไป<i class="fas fa-chevron-right ml-1"></i>
                    </a>
                <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>

            </main>

            <aside class="hidden lg:block lg:w-80 lg:flex-shrink-0 mb-6 lg:mb-0">
            
            <div class="sticky top-[88px] space-y-4 max-h-[calc(100vh-120px)] overflow-y-auto pr-2">
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white text-center py-4 rounded-xl shadow-lg">
                <h2 class="text-xl font-bold"><i class="fas fa-filter mr-2"></i>ตัวกรอง</h2>
                <p class="text-xs text-green-100">ค้นหาบุคลากร</p>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 mb-4 border border-green-100">
                <h3 class="text-sm font-bold text-green-800 mb-3 flex items-center">
                    <i class="fas fa-search text-green-600 mr-2"></i>ค้นหา
                </h3>
                <form method="GET" id="sidebarSearchForm" class="space-y-2">
                    <input type="text" name="search" placeholder="ชื่อบุคลากร..." 
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                        class="w-full border-2 border-green-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-green-400 transition text-sm">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i>ค้นหา
                    </button>
                    <a href="?" class="block w-full text-center bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold transition-colors text-sm">
                    <i class="fas fa-redo mr-1"></i>ล้าง
                    </a>
                </form>
                </div>

                <form method="GET" id="sidebarFilterForm" class="space-y-3">
                
                <div class="bg-white rounded-xl shadow-md p-4 border border-blue-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-briefcase text-blue-600 mr-2"></i>ตำแหน่ง
                    </label>
                    <select name="position_id" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-blue-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-400 transition text-xs bg-white hover:border-blue-400 cursor-pointer">
                    <option value="0">- เลือก -</option>
                    <?php 
                    $pos_query = "SELECT id, position_name FROM positions ORDER BY position_name";
                    $pos_result = $mysqli3->query($pos_query);
                    while ($p = $pos_result->fetch_assoc()) {
                        $selected = $filters['position_id'] == $p['id'] ? 'selected' : '';
                        echo "<option value='{$p['id']}' {$selected}>" . htmlspecialchars($p['position_name']) . "</option>";
                    }
                    ?>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-purple-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-building text-purple-600 mr-2"></i>ฝ่าย
                    </label>
                    <select name="division_id" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-purple-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-purple-400 transition text-xs bg-white hover:border-purple-400 cursor-pointer">
                    <option value="0">- เลือก -</option>
                    <?php foreach ($divisions as $div) {
                        $selected = $filters['division_id'] == $div['id'] ? 'selected' : '';
                        echo "<option value='{$div['id']}' {$selected}>" . htmlspecialchars($div['department_name']) . "</option>";
                    } ?>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-emerald-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-school text-emerald-600 mr-2"></i>แผนก
                    </label>
                    <select name="department_id" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-emerald-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-emerald-400 transition text-xs bg-white hover:border-emerald-400 cursor-pointer">
                    <option value="0">- เลือก -</option>
                    <?php foreach ($departments as $dept) {
                        $selected = $filters['department_id'] == $dept['id'] ? 'selected' : '';
                        echo "<option value='{$dept['id']}' {$selected}>" . htmlspecialchars($dept['department_name']) . "</option>";
                    } ?>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-cyan-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-tasks text-cyan-600 mr-2"></i>ประเภทงาน
                    </label>
                    <select name="workbranch_id" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-cyan-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-cyan-400 transition text-xs bg-white hover:border-cyan-400 cursor-pointer">
                    <option value="0">- เลือก -</option>
                    <?php foreach($workbranchGroups as $groupName => $workbranches): ?>
                        <?php if(!empty($workbranches)): ?>
                        <optgroup label="<?= htmlspecialchars($groupName) ?>">
                            <?php foreach($workbranches as $wb): ?>
                            <option value="<?= $wb['id'] ?>" <?= ($filters['workbranch_id'] == $wb['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($wb['workbranch_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-orange-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-certificate text-orange-600 mr-2"></i>วิทยฐานะ
                    </label>
                    <select name="position_level_id" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-orange-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-orange-400 transition text-xs bg-white hover:border-orange-400 cursor-pointer">
                    <option value="0">- เลือก -</option>
                    <?= getOptions('position_level', 'id', 'level_name', $filters['position_level_id']) ?>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-red-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-chart-line text-red-600 mr-2"></i>ระดับงาน
                    </label>
                    <select name="worklevel_id" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-red-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-red-400 transition text-xs bg-white hover:border-red-400 cursor-pointer">
                    <option value="0">- เลือก -</option>
                    <?= getOptions('worklevel', 'id', 'work_level_name', $filters['worklevel_id']) ?>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-indigo-100">
                    <label class="block text-xs font-bold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-list text-indigo-600 mr-2"></i>จำนวนแสดง
                    </label>
                    <select name="limit" onchange="document.getElementById('sidebarFilterForm').submit()" class="w-full border-2 border-indigo-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-indigo-400 transition text-xs bg-white hover:border-indigo-400 cursor-pointer">
                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20 รายการ</option>
                    <option value="48" <?= $limit == 48 ? 'selected' : '' ?>>48 รายการ</option>
                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100 รายการ</option>
                    <option value="999" <?= $limit >= 999 ? 'selected' : '' ?>>แสดงทั้งหมด</option>
                    </select>
                </div>

                <div class="bg-white rounded-xl shadow-md p-4 border border-gray-200 sticky bottom-0">
                    <a href="?" class="block w-full text-center bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg font-semibold transition-colors text-sm">
                    <i class="fas fa-times mr-1"></i>ล้างทั้งหมด
                    </a>
                </div>

                <?php foreach($_GET as $key => $value): ?>
                    <?php if(!in_array($key, ['position_id', 'position_level_id', 'division_id', 'department_id', 'worklevel_id', 'workbranch_id', 'limit', 'page'])): ?>
                    <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                </form>
            </div>
            </aside>
            
        </div>
    </div>
</div>

<div id="personModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-[9999] flex items-start justify-center pt-24 md:pt-28 px-4 pb-24 md:pb-8" onclick="closePersonModalOutside(event)">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-4 md:mx-auto p-6 relative max-h-[calc(100vh-12rem)] md:max-h-[85vh] overflow-y-auto" onclick="event.stopPropagation();">
    <button class="absolute top-3 right-3 text-gray-500 hover:text-red-500 z-10" onclick="closePersonModal()">
      <i class="fas fa-times text-xl"></i>
    </button>
    <div id="personModalContent">
      <p class="text-center text-gray-500">กำลังโหลดข้อมูล...</p>
    </div>
  </div>
</div>

<script>
function closePersonModal() {
  document.getElementById('personModal').classList.add('hidden');
}

function closePersonModalOutside(event) {
  if (event.target.id === 'personModal') closePersonModal();
}

document.querySelectorAll('.person-card').forEach(card => {
  card.addEventListener('click', function() {
    const id = this.getAttribute('data-id');
    const modal = document.getElementById('personModal');
    const modalContent = document.getElementById('personModalContent');
    
    modal.classList.remove('hidden');
    modalContent.innerHTML = '<p class="text-center text-gray-500 animate-pulse">กำลังโหลดข้อมูล...</p>';
    
    fetch(`../api/person_detail.php?id=${id}`)
      .then(res => res.text())
      .then(html => modalContent.innerHTML = html)
      .catch(() => modalContent.innerHTML = '<p class="text-center text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>');
  });
});

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closePersonModal();
});

function copyText(text, btn){
  if (!navigator.clipboard) { 
      // Fallback for non-https or older browsers
      const textArea = document.createElement("textarea");
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      try {
          document.execCommand('copy');
          const original = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check"></i>';
          setTimeout(()=>{ btn.innerHTML = original; }, 1200);
      } catch (err) {
          console.error('Fallback: Oops, unable to copy', err);
      }
      document.body.removeChild(textArea);
      return;
  }
  const original = btn.innerHTML;
  navigator.clipboard.writeText(text).then(()=>{
    btn.innerHTML = '<i class="fas fa-check"></i> คัดลอกแล้ว';
    setTimeout(()=>{
      btn.innerHTML = original;
    }, 1200);
  });
}
</script>

<style>
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.person-card { transition: all 0.3s ease; }
.person-card:hover { transform: translateY(-8px); }

/* Smooth scrollbar for sidebar */
aside::-webkit-scrollbar {
  width: 6px;
}
aside::-webkit-scrollbar-track {
  background: transparent;
}
aside::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
aside::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../base.php';
?>