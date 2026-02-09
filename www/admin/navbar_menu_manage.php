<?php
// navbar_menu_manage.php
include 'middleware.php';
session_start();
ob_start();
include '../condb/condb.php';

// --- AJAX Handler ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // 1. Update Position Type
    if ($_POST['action'] == 'update_position') {
        $id = (int)$_POST['menu_id'];
        $new_pos = $_POST['new_position'];
        $stmt = $mysqli4->prepare("UPDATE main_menus SET position_type = ? WHERE menu_id = ?");
        $stmt->bind_param("si", $new_pos, $id);
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error', 'msg' => $stmt->error]);
        exit;
    }

    // 2. Reorder Main
    if ($_POST['action'] == 'reorder_main') {
        $order = $_POST['order'];
        if (is_array($order)) {
            foreach ($order as $position => $id) {
                $pos = (int)$position + 1;
                $mysqli4->query("UPDATE main_menus SET menu_order = $pos WHERE menu_id = " . (int)$id);
            }
            echo json_encode(['status' => 'success']);
            exit;
        }
    }

    // 3. Reorder Sub
    if ($_POST['action'] == 'reorder_sub') {
        $order = $_POST['order'];
        if (is_array($order)) {
            foreach ($order as $position => $id) {
                $pos = (int)$position + 1;
                $mysqli4->query("UPDATE sub_main_menus SET submenu_order = $pos WHERE submenu_id = " . (int)$id);
            }
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
}

// --- PHP Logic: Add/Edit/Delete ---

// Add Main Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_main_menu') {
    $menu_name = trim($_POST['menu_name']);
    $menu_link = trim($_POST['menu_link']) ?: NULL;
    $is_dropdown = isset($_POST['is_dropdown']) ? 1 : 0;
    $target_blank = isset($_POST['target_blank']) ? 1 : 0; // Receive target_blank
    
    $max_res = $mysqli4->query("SELECT MAX(menu_order) as max_o FROM main_menus");
    $menu_order = ($max_res->fetch_assoc()['max_o'] ?? 0) + 1;
    $position_type = $_POST['position_type'] ?? 'topnav';

    $stmt = $mysqli4->prepare("INSERT INTO main_menus (menu_name, menu_link, is_dropdown, menu_order, position_type, target_blank) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiisi", $menu_name, $menu_link, $is_dropdown, $menu_order, $position_type, $target_blank);
    
    if ($stmt->execute()) $_SESSION['toast'] = ['msg' => 'เพิ่มเมนูหลักสำเร็จ', 'type' => 'success'];
    else $_SESSION['toast'] = ['msg' => 'เกิดข้อผิดพลาด', 'type' => 'error'];
    header("Location: navbar_menu_manage.php"); exit;
}

// Add Sub Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_submenu') {
    $menu_id = $_POST['menu_id'];
    $submenu_name = trim($_POST['submenu_name']);
    $submenu_link = trim($_POST['submenu_link']);
    $target_blank = isset($_POST['target_blank']) ? 1 : 0; // Receive target_blank
    
    $max_res = $mysqli4->query("SELECT MAX(submenu_order) as max_o FROM sub_main_menus WHERE menu_id = $menu_id");
    $submenu_order = ($max_res->fetch_assoc()['max_o'] ?? 0) + 1;
    
    $parent_res = $mysqli4->query("SELECT position_type FROM main_menus WHERE menu_id = $menu_id");
    $position_type = $parent_res->fetch_assoc()['position_type'] ?? 'topnav';

    $stmt = $mysqli4->prepare("INSERT INTO sub_main_menus (menu_id, submenu_name, submenu_link, submenu_order, position_type, target_blank) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issisi", $menu_id, $submenu_name, $submenu_link, $submenu_order, $position_type, $target_blank);
    
    if ($stmt->execute()) $_SESSION['toast'] = ['msg' => 'เพิ่มเมนูย่อยสำเร็จ', 'type' => 'success'];
    else $_SESSION['toast'] = ['msg' => 'เกิดข้อผิดพลาด', 'type' => 'error'];
    header("Location: navbar_menu_manage.php"); exit;
}

// Edit Main Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_main_menu') {
    $id = $_POST['menu_id'];
    $name = $_POST['menu_name'];
    $link = $_POST['menu_link'];
    $is_drop = isset($_POST['is_dropdown']) ? 1 : 0;
    $target_blank = isset($_POST['target_blank']) ? 1 : 0; // Receive target_blank
    $pos = $_POST['position_type'];
    
    $stmt = $mysqli4->prepare("UPDATE main_menus SET menu_name=?, menu_link=?, is_dropdown=?, position_type=?, target_blank=? WHERE menu_id=?");
    $stmt->bind_param("ssisii", $name, $link, $is_drop, $pos, $target_blank, $id);
    
    if ($stmt->execute()) $_SESSION['toast'] = ['msg' => 'แก้ไขเรียบร้อย', 'type' => 'success'];
    header("Location: navbar_menu_manage.php"); exit;
}

// Edit Sub Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'edit_submenu') {
    $id = $_POST['submenu_id'];
    $name = $_POST['submenu_name'];
    $link = $_POST['submenu_link'];
    $target_blank = isset($_POST['target_blank']) ? 1 : 0; // Receive target_blank
    
    $stmt = $mysqli4->prepare("UPDATE sub_main_menus SET submenu_name=?, submenu_link=?, target_blank=? WHERE submenu_id=?");
    $stmt->bind_param("ssii", $name, $link, $target_blank, $id);
    
    if ($stmt->execute()) $_SESSION['toast'] = ['msg' => 'แก้ไขเรียบร้อย', 'type' => 'success'];
    header("Location: navbar_menu_manage.php"); exit;
}

// Delete
if (isset($_GET['delete_menu'])) {
    $id = (int)$_GET['menu_id'];
    $mysqli4->query("DELETE FROM sub_main_menus WHERE menu_id = $id");
    $mysqli4->query("DELETE FROM main_menus WHERE menu_id = $id");
    $_SESSION['toast'] = ['msg' => 'ลบเรียบร้อย', 'type' => 'success'];
    header("Location: navbar_menu_manage.php"); exit;
}
if (isset($_GET['delete_submenu'])) {
    $id = (int)$_GET['submenu_id'];
    $mysqli4->query("DELETE FROM sub_main_menus WHERE submenu_id = $id");
    $_SESSION['toast'] = ['msg' => 'ลบเรียบร้อย', 'type' => 'success'];
    header("Location: navbar_menu_manage.php"); exit;
}

// --- Fetch Data ---
$menus_result = $mysqli4->query("SELECT * FROM main_menus ORDER BY menu_order ASC");
$topnav_menus = [];
$sidebar_menus = [];
$both_menus = [];

while ($menu = $menus_result->fetch_assoc()) {
    $sub_res = $mysqli4->query("SELECT * FROM sub_main_menus WHERE menu_id = " . $menu['menu_id'] . " ORDER BY submenu_order ASC");
    $menu['submenus'] = $sub_res->fetch_all(MYSQLI_ASSOC);
    
    if ($menu['position_type'] == 'topnav') $topnav_menus[] = $menu;
    elseif ($menu['position_type'] == 'sidebar') $sidebar_menus[] = $menu;
    else $both_menus[] = $menu;
}

function renderMenuCard($menu, $colorClass) {
    $menuJson = htmlspecialchars(json_encode($menu), ENT_QUOTES, 'UTF-8');
    $newTabBadge = ($menu['target_blank'] == 1) ? '<i class="fas fa-external-link-alt text-[10px] text-gray-400 ml-1" title="เปิดแท็บใหม่"></i>' : '';
    ?>
    <div class="bg-white p-3 rounded-lg border-l-4 border-<?= $colorClass ?>-500 shadow-sm hover:shadow-md transition mb-3 group relative cursor-default" data-id="<?= $menu['menu_id'] ?>">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 overflow-hidden w-full">
                <div class="drag-handle text-gray-300 hover:text-gray-500 cursor-grab px-1"><i class="fas fa-grip-vertical"></i></div>
                <div class="truncate w-full">
                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-1">
                        <?= htmlspecialchars($menu['menu_name']) ?>
                        <?php if($menu['is_dropdown']): ?><i class="fas fa-caret-down text-gray-400 text-xs"></i><?php endif; ?>
                    </h4>
                    <div class="text-[10px] text-gray-400 truncate flex items-center">
                        <?= htmlspecialchars($menu['menu_link'] ?? '-') ?>
                        <?= $newTabBadge ?>
                    </div>
                </div>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity absolute right-2 top-2 bg-white pl-2">
                <button type="button" onclick='editMain(<?= $menuJson ?>)' class="text-orange-400 hover:text-orange-600 p-1"><i class="fas fa-pen"></i></button>
                <button type="button" onclick="confirmDelete('?delete_menu=1&menu_id=<?= $menu['menu_id'] ?>')" class="text-red-400 hover:text-red-600 p-1"><i class="fas fa-trash"></i></button>
            </div>
        </div>

        <div class="sub-menu-container mt-2 pl-4 space-y-1 pt-2 border-t border-dashed border-gray-100 min-h-[5px]" data-parent-id="<?= $menu['menu_id'] ?>">
            <?php foreach($menu['submenus'] as $sub): 
                $subJson = htmlspecialchars(json_encode($sub), ENT_QUOTES, 'UTF-8');
                $subNewTab = ($sub['target_blank'] == 1) ? '<i class="fas fa-external-link-alt text-[8px] text-gray-400 ml-1" title="เปิดแท็บใหม่"></i>' : '';
            ?>
                <div class="sub-menu-item flex justify-between items-center bg-gray-50 p-1 rounded text-xs group/sub hover:bg-gray-100 cursor-default" data-id="<?= $sub['submenu_id'] ?>">
                    <div class="flex items-center gap-2 overflow-hidden w-full">
                        <span class="sub-drag-handle text-gray-300 cursor-grab hover:text-gray-500"><i class="fas fa-ellipsis-v"></i></span>
                        <span class="truncate text-gray-600 flex items-center">
                            <?= htmlspecialchars($sub['submenu_name']) ?>
                            <?= $subNewTab ?>
                        </span>
                    </div>
                    <div class="flex gap-1 opacity-0 group-hover/sub:opacity-100">
                        <button type="button" onclick='editSub(<?= $subJson ?>)' class="text-orange-400 hover:text-orange-600 text-[10px]"><i class="fas fa-pen"></i></button>
                        <button type="button" onclick="confirmDelete('?delete_submenu=1&submenu_id=<?= $sub['submenu_id'] ?>')" class="text-red-400 hover:text-red-600 text-[10px]"><i class="fas fa-times"></i></button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button type="button" onclick="openAddSubModal(<?= $menu['menu_id'] ?>, '<?= htmlspecialchars($menu['menu_name']) ?>')" 
                class="w-full mt-2 text-[10px] text-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded py-1 transition">
            + เพิ่มย่อย
        </button>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเมนู</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; }
        .ghost-class { opacity: 0.4; background: #f3f4f6; border: 2px dashed #ccc; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 h-screen overflow-hidden flex flex-col">

    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                Toast.fire({ icon: '<?= $_SESSION['toast']['type'] ?>', title: '<?= $_SESSION['toast']['msg'] ?>' });
            });
        </script>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <div class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm z-10">
        <div>
            <h1 class="text-lg font-bold text-gray-800"><i class="fas fa-columns text-indigo-600 mr-2"></i>จัดการโครงสร้างเมนู</h1>
            <p class="text-xs text-gray-500">ลากเมนูข้ามกล่องเพื่อเปลี่ยนตำแหน่งการแสดงผล</p>
        </div>
        <button type="button" onclick="openModal('mainMenuModal')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm shadow transition">
            <i class="fas fa-plus mr-1"></i> สร้างเมนู
        </button>
    </div>

    <div class="flex-1 overflow-hidden p-4">
        <div class="grid grid-cols-12 gap-4 h-full">
            
            <div class="col-span-3 flex flex-col h-full bg-white rounded-xl border border-orange-200 shadow-sm overflow-hidden">
                <div class="bg-orange-50 p-3 border-b border-orange-100 flex justify-between items-center">
                    <span class="font-bold text-orange-800 text-sm"><i class="fas fa-bars mr-2"></i>Sidebar</span>
                    <span class="bg-white text-orange-600 text-xs px-2 py-0.5 rounded-full shadow-sm count-badge"><?= count($sidebar_menus) ?></span>
                </div>
                <div class="flex-1 p-3 overflow-y-auto bg-orange-50/20 sortable-list space-y-2" data-pos="sidebar">
                    <?php foreach($sidebar_menus as $m) renderMenuCard($m, 'orange'); ?>
                </div>
            </div>

            <div class="col-span-9 flex flex-col gap-4 h-full">
                
                <div class="flex-1 flex flex-col bg-white rounded-xl border border-purple-200 shadow-sm overflow-hidden">
                    <div class="bg-purple-50 p-3 border-b border-purple-100 flex justify-between items-center">
                        <span class="font-bold text-purple-800 text-sm"><i class="fas fa-window-maximize mr-2"></i>Top Navbar</span>
                        <span class="bg-white text-purple-600 text-xs px-2 py-0.5 rounded-full shadow-sm count-badge"><?= count($topnav_menus) ?></span>
                    </div>
                    <div class="flex-1 p-3 overflow-y-auto bg-purple-50/20 sortable-list grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 content-start" data-pos="topnav">
                        <?php foreach($topnav_menus as $m) renderMenuCard($m, 'purple'); ?>
                    </div>
                </div>

                <div class="h-1/3 flex flex-col bg-white rounded-xl border border-indigo-200 shadow-sm overflow-hidden">
                    <div class="bg-indigo-50 p-3 border-b border-indigo-100 flex justify-between items-center">
                        <span class="font-bold text-indigo-800 text-sm"><i class="fas fa-clone mr-2"></i>Both</span>
                        <span class="bg-white text-indigo-600 text-xs px-2 py-0.5 rounded-full shadow-sm count-badge"><?= count($both_menus) ?></span>
                    </div>
                    <div class="flex-1 p-3 overflow-y-auto bg-indigo-50/20 sortable-list grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 content-start" data-pos="both">
                        <?php foreach($both_menus as $m) renderMenuCard($m, 'indigo'); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="mainMenuModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" style="transition: opacity 0.3s ease;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300">
            <form action="navbar_menu_manage.php" method="post">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800" id="mainModalTitle">เพิ่มเมนูหลัก</h3>
                    <button type="button" onclick="closeModal('mainMenuModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    <input type="hidden" name="action" id="mainAction" value="add_main_menu">
                    <input type="hidden" name="menu_id" id="mainMenuId">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเมนู <span class="text-red-500">*</span></label>
                        <input type="text" name="menu_name" id="mainMenuName" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ลิงก์ URL</label>
                        <input type="text" name="menu_link" id="mainMenuLink" placeholder="#" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="target_blank" id="mainTargetBlank" value="1" class="w-4 h-4 text-indigo-600 rounded cursor-pointer">
                        <label for="mainTargetBlank" class="ml-2 text-sm text-gray-700 cursor-pointer">เปิดในแท็บใหม่ (_blank)</label>
                    </div>

                    <div id="posSelectDiv">
                        <label class="block text-sm font-medium text-gray-700 mb-1">ตำแหน่งเริ่มต้น</label>
                        <select name="position_type" id="mainMenuPos" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="topnav">Top Navbar</option>
                            <option value="sidebar">Sidebar</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-2">
                        <input type="checkbox" name="is_dropdown" id="mainIsDropdown" class="w-4 h-4 text-indigo-600 rounded">
                        <label for="mainIsDropdown" class="ml-2 text-sm text-gray-700">มีเมนูย่อย (Dropdown)</label>
                    </div>
                </div>
                <div class="p-5 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                    <button type="button" onclick="closeModal('mainMenuModal')" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg text-sm">ยกเลิก</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md text-sm">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div id="subMenuModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity opacity-0 pointer-events-none" style="transition: opacity 0.3s ease;">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300">
            <form action="navbar_menu_manage.php" method="post">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800" id="subModalTitle">เพิ่มเมนูย่อย</h3>
                    <button type="button" onclick="closeModal('subMenuModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                <div class="p-5 space-y-4">
                    <input type="hidden" name="action" id="subAction" value="add_submenu">
                    <input type="hidden" name="submenu_id" id="subMenuId">
                    
                    <div>
                        <label class="block text-xs font-bold text-indigo-600 uppercase mb-1">ภายใต้เมนูหลัก</label>
                        <div class="flex items-center gap-2 bg-indigo-50 px-3 py-2 rounded border border-indigo-100 text-indigo-800 font-semibold text-sm">
                            <i class="fas fa-folder-open"></i> <span id="parentMenuNameDisplay">...</span>
                            <input type="hidden" name="menu_id" id="parentMenuIdInput">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเมนูย่อย <span class="text-red-500">*</span></label>
                        <input type="text" name="submenu_name" id="subMenuName" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ลิงก์ URL <span class="text-red-500">*</span></label>
                        <input type="text" name="submenu_link" id="subMenuLink" required placeholder="/page.php" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="target_blank" id="subTargetBlank" value="1" class="w-4 h-4 text-indigo-600 rounded cursor-pointer">
                        <label for="subTargetBlank" class="ml-2 text-sm text-gray-700 cursor-pointer">เปิดในแท็บใหม่ (_blank)</label>
                    </div>
                </div>

                <div class="p-5 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                    <button type="button" onclick="closeModal('subMenuModal')" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg text-sm">ยกเลิก</button>
                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 shadow-md text-sm">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // Sortable Main Menus
            document.querySelectorAll('.sortable-list').forEach(container => {
                new Sortable(container, {
                    group: 'main_menus',
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'ghost-class',
                    onAdd: function (evt) {
                        const item = evt.item;
                        const newPos = evt.to.getAttribute('data-pos');
                        const menuId = item.getAttribute('data-id');
                        
                        item.className = item.className.replace(/border-\w+-500/, '');
                        let newColor = (newPos === 'sidebar') ? 'orange' : (newPos === 'topnav' ? 'purple' : 'indigo');
                        item.classList.add(`border-${newColor}-500`);
                        
                        updateBadges();

                        const formData = new FormData();
                        formData.append('action', 'update_position');
                        formData.append('menu_id', menuId);
                        formData.append('new_position', newPos);
                        fetch('navbar_menu_manage.php', { method: 'POST', body: formData })
                            .then(res => res.json())
                            .then(data => {
                                const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 });
                                if(data.status === 'success') Toast.fire({ icon: 'success', title: `ย้ายไป ${newPos} แล้ว` });
                            });
                        
                        updateOrder('reorder_main', this.toArray());
                    },
                    onUpdate: function (evt) {
                        updateOrder('reorder_main', this.toArray());
                    }
                });
            });

            // Sortable Sub Menus
            document.querySelectorAll('.sub-menu-container').forEach(container => {
                new Sortable(container, {
                    group: 'submenus',
                    animation: 150,
                    handle: '.sub-drag-handle',
                    ghostClass: 'ghost-class',
                    onEnd: function (evt) {
                        updateOrder('reorder_sub', this.toArray());
                    }
                });
            });
        });

        function updateOrder(action, orderArray) {
            const formData = new FormData();
            formData.append('action', action);
            orderArray.forEach(id => formData.append('order[]', id));
            fetch('navbar_menu_manage.php', { method: 'POST', body: formData });
        }

        function updateBadges() {
            document.querySelectorAll('.sortable-list').forEach(list => {
                const count = list.children.length;
                const badge = list.parentElement.querySelector('.count-badge');
                if(badge) badge.textContent = count;
            });
        }

        function confirmDelete(url) {
            Swal.fire({
                title: 'ยืนยันการลบ?', text: "ข้อมูลจะถูกลบถาวร", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
                confirmButtonText: 'ลบเลย'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = url;
            })
        }

        // Modal Functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = modal.querySelector('div[class*="transform"]');
            
            // Reset Forms for Add Mode
            if(modalId === 'mainMenuModal' && document.getElementById('mainMenuId').value === '') {
                document.getElementById('mainAction').value = 'add_main_menu';
                document.getElementById('mainModalTitle').innerText = 'เพิ่มเมนูหลัก';
                document.getElementById('mainMenuId').value = '';
                document.getElementById('mainMenuName').value = '';
                document.getElementById('mainMenuLink').value = '';
                document.getElementById('mainIsDropdown').checked = false;
                document.getElementById('mainTargetBlank').checked = false; // Reset checkbox
                document.getElementById('posSelectDiv').classList.remove('hidden');
            }
            // Reset Forms for Sub Menu
            if(modalId === 'subMenuModal' && document.getElementById('subMenuId').value === '') {
                document.getElementById('subTargetBlank').checked = false; // Reset checkbox
            }

            modal.classList.remove('hidden', 'pointer-events-none');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = modal.querySelector('div[class*="transform"]');
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden', 'pointer-events-none');
                // Reset IDs
                if(modalId === 'mainMenuModal') document.getElementById('mainMenuId').value = '';
                if(modalId === 'subMenuModal') document.getElementById('subMenuId').value = '';
            }, 300);
        }

        function editMain(data) {
            document.getElementById('mainAction').value = 'edit_main_menu';
            document.getElementById('mainModalTitle').innerText = 'แก้ไขเมนู: ' + data.menu_name;
            document.getElementById('mainMenuId').value = data.menu_id;
            document.getElementById('mainMenuName').value = data.menu_name;
            document.getElementById('mainMenuLink').value = data.menu_link;
            document.getElementById('mainMenuPos').value = data.position_type;
            document.getElementById('mainIsDropdown').checked = (data.is_dropdown == 1);
            document.getElementById('mainTargetBlank').checked = (data.target_blank == 1); // Set checkbox status
            
            openModal('mainMenuModal');
        }

        function openAddSubModal(parentId, parentName) {
            document.getElementById('subAction').value = 'add_submenu';
            document.getElementById('subModalTitle').innerText = 'เพิ่มเมนูย่อย';
            document.getElementById('subMenuId').value = '';
            document.getElementById('subMenuName').value = '';
            document.getElementById('subMenuLink').value = '';
            document.getElementById('subTargetBlank').checked = false; // Reset
            document.getElementById('parentMenuIdInput').value = parentId;
            document.getElementById('parentMenuNameDisplay').innerText = parentName;
            openModal('subMenuModal');
        }

        function editSub(data) {
            document.getElementById('subAction').value = 'edit_submenu';
            document.getElementById('subModalTitle').innerText = 'แก้ไขเมนูย่อย';
            document.getElementById('subMenuId').value = data.submenu_id;
            document.getElementById('subMenuName').value = data.submenu_name;
            document.getElementById('subMenuLink').value = data.submenu_link;
            document.getElementById('subTargetBlank').checked = (data.target_blank == 1); // Set checkbox status
            
            document.getElementById('parentMenuIdInput').value = data.menu_id;
            document.getElementById('parentMenuNameDisplay').innerText = "(ID: " + data.menu_id + ")"; 
            openModal('subMenuModal');
        }
    </script>

</body>
</html>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>