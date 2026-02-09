<?php
// badge_manage.php
include 'middleware.php';
session_start();
ob_start();
include '../condb/condb.php';

// --- AJAX Handler ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    // 1. Reorder Badges (ใช้งานจริง)
    if ($_POST['action'] == 'reorder_badges') {
        $order = $_POST['order']; // รับ array ของ id ตามลำดับใหม่
        if (is_array($order)) {
            foreach ($order as $position => $id) {
                $id = (int)$id;
                $sort_order = $position + 1;
                // อัปเดตลำดับลงฐานข้อมูล
                $mysqli4->query("UPDATE badges SET sort_order = $sort_order WHERE id = $id");
            }
            echo json_encode(['status' => 'success', 'msg' => 'บันทึกลำดับเรียบร้อย']);
            exit;
        }
    }

    // 2. Toggle Visibility
    if ($_POST['action'] == 'toggle_visibility') {
        $id = (int)$_POST['id'];
        $visible = (int)$_POST['visible'];
        $stmt = $mysqli4->prepare("UPDATE badges SET visible = ? WHERE id = ?");
        $stmt->bind_param("ii", $visible, $id);
        if($stmt->execute()) echo json_encode(['status' => 'success']);
        else echo json_encode(['status' => 'error']);
        exit;
    }
}

// --- PHP Logic CRUD ---

// Add Badge
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_badge'])) {
    $badge_name = trim($_POST['badge_name']);
    $badge_description = trim($_POST['badge_description']);
    $badge_icon = trim($_POST['badge_icon']);
    $badge_color = trim($_POST['badge_color']);
    $badge_url = trim($_POST['badge_url']);
    $visible = isset($_POST['visible']) ? 1 : 0;
    $badge_image = '';

    // Auto sort order (Max + 1)
    $res = $mysqli4->query("SELECT MAX(sort_order) as max_sort FROM badges");
    $row = $res->fetch_assoc();
    $sort_order = ($row['max_sort'] ?? 0) + 1;

    // Handle Image Upload
    if (isset($_FILES['badge_image']) && $_FILES['badge_image']['size'] > 0) {
        $upload_dir = '../uploads/badges/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $file_ext = strtolower(pathinfo($_FILES['badge_image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid('badge_') . '.' . $file_ext;
        if (move_uploaded_file($_FILES['badge_image']['tmp_name'], $upload_dir . $file_name)) {
            $badge_image = 'uploads/badges/' . $file_name;
        }
    }

    $stmt = $mysqli4->prepare("INSERT INTO badges (badge_name, badge_description, badge_icon, badge_image, badge_color, badge_url, visible, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssii", $badge_name, $badge_description, $badge_icon, $badge_image, $badge_color, $badge_url, $visible, $sort_order);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['msg' => 'เพิ่ม Badge สำเร็จ', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => 'เกิดข้อผิดพลาด: ' . $stmt->error, 'type' => 'error'];
    }
    header("Location: badge_manage.php");
    exit;
}

// Edit Badge
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_badge'])) {
    $id = (int)$_POST['id'];
    $badge_name = trim($_POST['badge_name']);
    $badge_description = trim($_POST['badge_description']);
    $badge_icon = trim($_POST['badge_icon']);
    $badge_color = trim($_POST['badge_color']);
    $badge_url = trim($_POST['badge_url']);
    
    // Get old image
    $res = $mysqli4->query("SELECT badge_image FROM badges WHERE id = $id");
    $old_img = $res->fetch_assoc()['badge_image'];
    $badge_image = $old_img;

    // Handle New Image
    if (isset($_FILES['badge_image']) && $_FILES['badge_image']['size'] > 0) {
        $upload_dir = '../uploads/badges/';
        $file_ext = strtolower(pathinfo($_FILES['badge_image']['name'], PATHINFO_EXTENSION));
        $file_name = uniqid('badge_') . '.' . $file_ext;
        if (move_uploaded_file($_FILES['badge_image']['tmp_name'], $upload_dir . $file_name)) {
            $badge_image = 'uploads/badges/' . $file_name;
            if($old_img && file_exists('../'.$old_img)) unlink('../'.$old_img);
        }
    }

    $stmt = $mysqli4->prepare("UPDATE badges SET badge_name=?, badge_description=?, badge_icon=?, badge_image=?, badge_color=?, badge_url=? WHERE id=?");
    $stmt->bind_param("ssssssi", $badge_name, $badge_description, $badge_icon, $badge_image, $badge_color, $badge_url, $id);
    
    if ($stmt->execute()) {
        $_SESSION['toast'] = ['msg' => 'แก้ไข Badge เรียบร้อย', 'type' => 'success'];
    } else {
        $_SESSION['toast'] = ['msg' => 'เกิดข้อผิดพลาด', 'type' => 'error'];
    }
    header("Location: badge_manage.php");
    exit;
}

// Delete Badge
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    // Delete image file first
    $res = $mysqli4->query("SELECT badge_image FROM badges WHERE id = $id");
    if($row = $res->fetch_assoc()) {
        if($row['badge_image'] && file_exists('../'.$row['badge_image'])) unlink('../'.$row['badge_image']);
    }
    
    $mysqli4->query("DELETE FROM badges WHERE id = $id");
    $_SESSION['toast'] = ['msg' => 'ลบ Badge เรียบร้อย', 'type' => 'success'];
    header("Location: badge_manage.php");
    exit;
}

// Fetch Data Sorted by sort_order
$badges_result = $mysqli4->query("SELECT * FROM badges ORDER BY sort_order ASC"); 
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการ Badge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; }
        .ghost-class { opacity: 0.4; background: #f3f4f6; border: 2px dashed #9ca3af; }
        .sortable-drag { cursor: grabbing; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true });
                Toast.fire({ icon: '<?= $_SESSION['toast']['type'] ?>', title: '<?= $_SESSION['toast']['msg'] ?>' });
            });
        </script>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <div class="max-w-5xl mx-auto py-8 px-4">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-certificate text-purple-600"></i> จัดการ Badge
                </h1>
                <p class="text-gray-500 text-sm">ลากวางเพื่อจัดลำดับการแสดงผล</p>
            </div>
            <div class="flex gap-2">
                <a href="dashboard.php" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700 shadow-sm transition">
                    <i class="fas fa-arrow-left mr-2"></i>กลับ
                </a>
                <button onclick="openModal('badgeModal')" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-sm transition">
                    <i class="fas fa-plus mr-2"></i>เพิ่ม Badge
                </button>
            </div>
        </div>

        <div id="badgeList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php if ($badges_result->num_rows > 0): 
                while ($badge = $badges_result->fetch_assoc()): 
                    $badgeJson = htmlspecialchars(json_encode($badge), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden group hover:shadow-md transition relative" data-id="<?= $badge['id'] ?>">
                <div class="absolute top-2 right-2 text-gray-300 cursor-grab hover:text-gray-500 drag-handle p-2 z-10">
                    <i class="fas fa-grip-vertical text-lg"></i>
                </div>

                <div class="p-5 flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <?php if(!empty($badge['badge_image'])): ?>
                            <img src="../<?= htmlspecialchars($badge['badge_image']) ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-100 bg-gray-50">
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl shadow-sm" style="background-color: <?= $badge['badge_color'] ?>">
                                <i class="fas <?= htmlspecialchars($badge['badge_icon']) ?>"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex-1 min-w-0 pr-6">
                        <h3 class="font-bold text-gray-800 truncate" title="<?= htmlspecialchars($badge['badge_name']) ?>">
                            <?= htmlspecialchars($badge['badge_name']) ?>
                        </h3>
                        <p class="text-xs text-gray-500 line-clamp-2 mt-1 min-h-[2.5em]">
                            <?= htmlspecialchars($badge['badge_description'] ?: '-') ?>
                        </p>
                        
                        <?php if($badge['badge_url']): ?>
                            <a href="<?= htmlspecialchars($badge['badge_url']) ?>" target="_blank" class="text-xs text-blue-500 hover:underline mt-1 inline-block truncate w-full">
                                <i class="fas fa-link mr-1"></i>Link
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 border-t border-gray-100 flex justify-between items-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" onchange="toggleVisibility(<?= $badge['id'] ?>, this)" <?= $badge['visible'] ? 'checked' : '' ?>>
                        <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                        <span class="ms-2 text-xs font-medium text-gray-600"><?= $badge['visible'] ? 'แสดง' : 'ซ่อน' ?></span>
                    </label>

                    <div class="flex gap-2">
                        <button onclick='editBadge(<?= $badgeJson ?>)' class="text-gray-400 hover:text-orange-500 transition p-1" title="แก้ไข"><i class="fas fa-pen"></i></button>
                        <button onclick="confirmDelete(<?= $badge['id'] ?>)" class="text-gray-400 hover:text-red-500 transition p-1" title="ลบ"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
            <?php endwhile; 
            else: ?>
                <div class="col-span-full text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
                    <i class="fas fa-certificate text-gray-300 text-4xl mb-3"></i>
                    <p class="text-gray-500">ยังไม่มี Badge ในระบบ</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="badgeModal" class="fixed inset-0 z-50 hidden bg-black/50 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 transform scale-95 transition-transform duration-300" id="badgeModalContent">
            <form action="badge_manage.php" method="post" enctype="multipart/form-data" id="badgeForm">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800" id="modalTitle">เพิ่ม Badge ใหม่</h3>
                    <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
                </div>
                
                <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto custom-scroll">
                    <input type="hidden" name="id" id="badgeId">
                    <input type="hidden" name="add_badge" id="actionInput" value="1">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ Badge <span class="text-red-500">*</span></label>
                        <input type="text" name="badge_name" id="badgeName" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">คำอธิบายสั้นๆ</label>
                        <textarea name="badge_description" id="badgeDesc" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ไอคอน (FontAwesome)</label>
                            <input type="text" name="badge_icon" id="badgeIcon" placeholder="fa-star" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">สีพื้นหลัง</label>
                            <div class="flex items-center gap-2">
                                <input type="color" name="badge_color" id="badgeColor" class="h-10 w-10 border rounded cursor-pointer" value="#6366f1">
                                <span id="colorHex" class="text-xs text-gray-500">#6366f1</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ลิงก์ (URL)</label>
                        <input type="url" name="badge_url" id="badgeUrl" placeholder="https://" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">รูปภาพ (ถ้ามี)</label>
                        <input type="file" name="badge_image" id="badgeImage" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100">
                        <div id="currentImagePreview" class="mt-2 hidden">
                            <img src="" class="h-10 rounded border border-gray-200">
                        </div>
                    </div>

                    <div class="flex items-center pt-2" id="visibleContainer">
                        <input type="checkbox" name="visible" id="badgeVisible" class="w-4 h-4 text-purple-600 rounded" checked>
                        <label for="badgeVisible" class="ml-2 text-sm text-gray-700">แสดงผลทันที</label>
                    </div>
                </div>

                <div class="p-5 bg-gray-50 rounded-b-xl flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded-lg text-sm">ยกเลิก</button>
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 shadow-md text-sm">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- Drag & Drop ---
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('badgeList');
            if(el) {
                new Sortable(el, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'ghost-class',
                    onEnd: function (evt) {
                        // ส่งลำดับใหม่ไปบันทึกเมื่อลากเสร็จ
                        var order = this.toArray(); 
                        updateOrder(order);
                    }
                });
            }
            
            // Color Picker Listener
            document.getElementById('badgeColor').addEventListener('input', function(e) {
                document.getElementById('colorHex').innerText = e.target.value;
            });
        });

        // --- AJAX Save Order ---
        function updateOrder(orderArray) {
            const formData = new FormData();
            formData.append('action', 'reorder_badges');
            orderArray.forEach((id) => {
                formData.append('order[]', id);
            });

            fetch('badge_manage.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if(data.status === 'success') {
                        // Toast แจ้งเตือนเล็กๆ มุมจอ
                        const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 });
                        Toast.fire({ icon: 'success', title: 'บันทึกลำดับแล้ว' });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // --- Actions ---
        function toggleVisibility(id, checkbox) {
            const isVisible = checkbox.checked ? 1 : 0;
            const label = checkbox.parentElement.querySelector('span');
            label.innerText = isVisible ? 'แสดง' : 'ซ่อน';
            
            const formData = new FormData();
            formData.append('action', 'toggle_visibility');
            formData.append('id', id);
            formData.append('visible', isVisible);
            
            fetch('badge_manage.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 1500 });
                    if(data.status === 'success') Toast.fire({ icon: 'success', title: 'อัปเดตสถานะแล้ว' });
                });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'ยืนยันการลบ?', text: "ข้อมูลจะไม่สามารถกู้คืนได้", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
                confirmButtonText: 'ลบเลย'
            }).then((result) => {
                if (result.isConfirmed) window.location.href = '?delete_id=' + id;
            })
        }

        // --- Modal ---
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            const content = document.getElementById(modalId + 'Content');
            
            // Reset Form for Add Mode
            if (document.getElementById('badgeId').value === '') {
                document.getElementById('badgeForm').reset();
                document.getElementById('modalTitle').innerText = 'เพิ่ม Badge ใหม่';
                document.getElementById('actionInput').name = 'add_badge';
                document.getElementById('currentImagePreview').classList.add('hidden');
                document.getElementById('visibleContainer').classList.remove('hidden'); 
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95');
                content.classList.add('scale-100');
            }, 10);
        }

        function editBadge(data) {
            // Fill Data
            document.getElementById('badgeId').value = data.id;
            document.getElementById('actionInput').name = 'edit_badge';
            document.getElementById('modalTitle').innerText = 'แก้ไข Badge';
            
            document.getElementById('badgeName').value = data.badge_name;
            document.getElementById('badgeDesc').value = data.badge_description || '';
            document.getElementById('badgeIcon').value = data.badge_icon || '';
            document.getElementById('badgeColor').value = data.badge_color || '#6366f1';
            document.getElementById('colorHex').innerText = data.badge_color || '#6366f1';
            document.getElementById('badgeUrl').value = data.badge_url || '';
            
            // Image Preview
            const imgPreview = document.getElementById('currentImagePreview');
            if (data.badge_image) {
                imgPreview.classList.remove('hidden');
                imgPreview.querySelector('img').src = '../' + data.badge_image;
            } else {
                imgPreview.classList.add('hidden');
            }

            document.getElementById('visibleContainer').classList.add('hidden');
            openModal('badgeModal');
        }

        function closeModal() {
            const modal = document.getElementById('badgeModal');
            const content = document.getElementById('badgeModalContent');
            modal.classList.add('opacity-0');
            content.classList.remove('scale-100');
            content.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                document.getElementById('badgeId').value = ''; // Reset ID
            }, 300);
        }
    </script>
</body>
</html>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>