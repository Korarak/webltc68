<?php
// filepath: /home/adm1n_ltc/webltc67/www/admin/personel_edit_modal.php
include 'middleware.php';
include '../condb/condb.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<p class="text-center text-red-600 p-4">ข้อผิดพลาด: ไม่พบ ID</p>';
    exit();
}

$personel_id = $_GET['id'];

// --- ส่วนที่ปรับแก้: รับค่า Query String ทั้งก้อน ---
$return_query = $_GET['return_query'] ?? '';

// ดึงข้อมูลบุคลากร
$query = "SELECT p.id, p.thai_id, p.fullname, p.Tel, p.E_mail, p.gender_id, p.education_level_id, p.education_detail, 
                 p.department_id, p.position_id, p.position_level_id, p.profile_image
          FROM personel_data p
          WHERE p.id = ?";
$stmt = $mysqli3->prepare($query);
$stmt->bind_param("i", $personel_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<p class="text-center text-red-600 p-4">ไม่พบข้อมูลบุคลากรนี้</p>';
    exit();
}

$personel = $result->fetch_assoc();
$stmt->close();

// ดึงข้อมูลงาน (Work Details)
$work_query = "SELECT wd.workbranch_id, wd.worklevel_id
               FROM work_detail wd
               WHERE wd.personel_id = ?";
$work_stmt = $mysqli3->prepare($work_query);
$work_stmt->bind_param("i", $personel_id);
$work_stmt->execute();
$work_result = $work_stmt->get_result();

$workbranches = [];
$worklevels = [];
while ($work = $work_result->fetch_assoc()) {
    $workbranches[] = $work['workbranch_id'];
    $worklevels[] = $work['worklevel_id'];
}
$work_stmt->close();

// --- Helper Functions ---
function getDepartmentGroups($selected_id = null) {
    global $mysqli3;
    $query = "SELECT d.id, d.department_name, 
                     CASE 
                         WHEN d.department_name LIKE 'ฝ่าย%' THEN 'ฝ่ายบริหาร'
                         WHEN d.department_name LIKE 'แผนก%' THEN 'แผนกวิชาการ'
                         ELSE 'อื่นๆ'
                     END as group_type
              FROM department d 
              ORDER BY d.department_name";
    
    $result = $mysqli3->query($query);
    $groups = ['ฝ่ายบริหาร' => [], 'แผนกวิชาการ' => [], 'อื่นๆ' => []];
    
    while ($row = $result->fetch_assoc()) {
        $row['selected'] = ($row['id'] == $selected_id) ? 'selected' : '';
        $groups[$row['group_type']][] = $row;
    }
    return $groups;
}

function getWorkbranchGroups($selected_ids = []) {
    global $mysqli3;
    $query = "SELECT wb.id, wb.workbranch_name, d.department_name,
                     CASE 
                         WHEN d.department_name LIKE 'ฝ่าย%' THEN d.department_name
                         ELSE 'แผนกวิชาการ'
                     END as group_name
              FROM workbranch wb
              LEFT JOIN department d ON wb.department_id = d.id
              ORDER BY d.department_name, wb.workbranch_name";
    
    $result = $mysqli3->query($query);
    $groups = [];
    
    while ($row = $result->fetch_assoc()) {
        $groupName = $row['group_name'];
        if (!isset($groups[$groupName])) {
            $groups[$groupName] = [];
        }
        $row['selected'] = in_array($row['id'], $selected_ids) ? 'selected' : '';
        $groups[$groupName][] = $row;
    }
    return $groups;
}

function getOptions($table, $id_field, $name_field, $selected_id = null) {
    global $mysqli3;
    $query = "SELECT $id_field, $name_field FROM $table ORDER BY $name_field";
    $result = $mysqli3->query($query);
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $selected = ($row[$id_field] == $selected_id) ? 'selected' : '';
        $options .= "<option value='{$row[$id_field]}' $selected>" . htmlspecialchars($row[$name_field]) . "</option>";
    }
    return $options;
}

$departmentGroups = getDepartmentGroups($personel['department_id']);
$workbranchGroups = getWorkbranchGroups($workbranches);
?>

<form action="personel_update.php" method="POST" enctype="multipart/form-data" class="space-y-6" id="editForm">
    <input type="hidden" name="id" value="<?= $personel['id'] ?>">
    <input type="hidden" name="current_profile_image" value="<?= htmlspecialchars($personel['profile_image'] ?? '') ?>">
    <input type="hidden" name="profile_image_base64" id="profileBase64">
    
    <input type="hidden" name="return_query" value="<?= htmlspecialchars($return_query) ?>">

    <?php 
    if ($personel['profile_image']): 
        $img_path = $personel['profile_image'];
        // Check if path is relative and doesn't start with http/https
        if ($img_path && !preg_match("~^(?:f|ht)tps?://~i", $img_path)) {
            $img_path = "/" . ltrim($img_path, '/');
        }
    ?>
    <div class="text-center mb-6">
        <p class="text-xs text-gray-500 mb-2 uppercase tracking-wide">รูปโปรไฟล์ปัจจุบัน</p>
        <div class="relative inline-block">
            <img src="<?= htmlspecialchars($img_path) ?>" 
                 alt="<?= htmlspecialchars($personel['fullname']) ?>" 
                 class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md">
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-200">
        <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-user text-blue-500 mr-2"></i> ข้อมูลส่วนตัว
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">เลขบัตรประชาชน</label>
                <input type="text" value="<?= htmlspecialchars($personel['thai_id'] ?? '-') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-200 text-gray-500 cursor-not-allowed" readonly>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                <input type="text" name="fullname" value="<?= htmlspecialchars($personel['fullname']) ?>" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">เพศ</label>
                <select name="gender_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">- เลือกเพศ -</option>
                    <?= getOptions('gender', 'id', 'gender_name', $personel['gender_id']); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">ระดับการศึกษา</label>
                <select name="education_level_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">- เลือกระดับ -</option>
                    <?= getOptions('education_level', 'id', 'education_name', $personel['education_level_id']); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">วุฒิการศึกษา (ระบุสาขา)</label>
                <input type="text" name="education_detail" value="<?= htmlspecialchars($personel['education_detail'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>
    </div>

    <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-200">
        <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-address-card text-blue-500 mr-2"></i> ข้อมูลติดต่อ
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">เบอร์โทรศัพท์</label>
                <input type="text" name="Tel" value="<?= htmlspecialchars($personel['Tel'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">อีเมล</label>
                <input type="email" name="E_mail" value="<?= htmlspecialchars($personel['E_mail'] ?? '') ?>" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 transition-all">
            </div>
        </div>
    </div>

    <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-200">
        <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-briefcase text-blue-500 mr-2"></i> ตำแหน่งและสังกัด
        </h3>
        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ตำแหน่ง</label>
                    <select name="position_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="">- เลือกตำแหน่ง -</option>
                        <?= getOptions('positions', 'id', 'position_name', $personel['position_id']); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">วิทยฐานะ</label>
                    <select name="position_level_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                        <option value="">- เลือกวิทยฐานะ -</option>
                        <?= getOptions('position_level', 'id', 'level_name', $personel['position_level_id']); ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">แผนก/ฝ่าย (สังกัดหลัก)</label>
                <select name="department_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500">
                    <option value="">- เลือกสังกัด -</option>
                    <?php foreach($departmentGroups as $groupName => $departments): ?>
                        <?php if(!empty($departments)): ?>
                            <optgroup label="<?= htmlspecialchars($groupName) ?>">
                                <?php foreach($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>" <?= $dept['selected'] ?>>
                                        <?= htmlspecialchars($dept['department_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">สาขางาน (เลือกได้หลายข้อ)</label>
                    <select name="workbranch_id[]" multiple class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 h-32 text-sm bg-white">
                        <?php foreach($workbranchGroups as $groupName => $workbranches): ?>
                            <?php if(!empty($workbranches)): ?>
                                <optgroup label="<?= htmlspecialchars($groupName) ?>">
                                    <?php foreach($workbranches as $wb): ?>
                                        <option value="<?= $wb['id'] ?>" <?= $wb['selected'] ?>>
                                            <?= htmlspecialchars($wb['workbranch_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">* กด Ctrl ค้างเพื่อเลือกหลายรายการ</p>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">ระดับงาน (เลือกได้หลายข้อ)</label>
                    <select name="worklevel_id[]" multiple class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 h-32 text-sm bg-white">
                        <?php 
                        $worklevel_query = "SELECT id, work_level_name FROM worklevel ORDER BY work_level_name";
                        $worklevel_result = $mysqli3->query($worklevel_query);
                        while ($wl = $worklevel_result->fetch_assoc()) {
                            $selected = in_array($wl['id'], $worklevels) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($wl['id']) . "' $selected>" . htmlspecialchars($wl['work_level_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50/50 p-6 rounded-xl border border-gray-200">
        <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-2">
            <i class="fas fa-camera text-blue-500 mr-2"></i> อัปโหลดรูปโปรไฟล์
        </h3>
        
        <label class="block w-full cursor-pointer">
            <span class="sr-only">เลือกรูปภาพ</span>
            <input type="file" id="profileInput" accept="image/*" class="block w-full text-sm text-gray-500
              file:mr-4 file:py-2.5 file:px-4
              file:rounded-full file:border-0
              file:text-sm file:font-semibold
              file:bg-blue-50 file:text-blue-700
              hover:file:bg-blue-100 transition-colors
            "/>
        </label>
        
        <div id="profilePreviewContainer" class="mt-4 hidden text-center p-4 border-2 border-dashed border-gray-300 rounded-lg">
            <p class="text-sm font-medium text-gray-700 mb-2">ตัวอย่างรูปภาพที่จะบันทึก:</p>
            <img id="profilePreview" class="max-w-[150px] h-auto rounded-lg shadow-sm border border-gray-200 mx-auto">
        </div>
    </div>

    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 sticky bottom-0 bg-white pb-2 z-10">
        <button type="button" onclick="closeEditModal()" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-colors">
            ยกเลิก
        </button>
        <button type="submit" class="px-8 py-2.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all hover:translate-y-px submitBtn">
            <i class="fas fa-save mr-2"></i> บันทึกการแก้ไข
        </button>
    </div>
</form>

<div id="profileModal" class="fixed inset-0 hidden bg-black/80 items-center justify-center z-[100] p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 max-w-2xl w-full shadow-2xl flex flex-col max-h-[90vh]">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-crop-alt text-blue-600"></i> ปรับแต่งรูปภาพ
        </h3>
        <div class="flex-1 overflow-hidden bg-gray-900 rounded-lg relative min-h-[300px]">
            <img id="profileCropPreview" class="max-w-full max-h-full block"/>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <button type="button" id="profileModal_cancel" class="px-5 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 font-medium transition-colors">
                ยกเลิก
            </button>
            <button type="button" id="profileModal_confirm" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors shadow-lg shadow-blue-200">
                <i class="fas fa-check mr-1"></i> ยืนยันรูปภาพ
            </button>
        </div>
    </div>
</div>

<script>
// Script ส่วนการ Submit Form
document.getElementById('editForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const submitBtn = document.querySelector('.submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...';

    const formData = new FormData(this);

    fetch('personel_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Redirect ไปยัง URL ที่ได้รับกลับมา (ซึ่งจะมี Query String เดิมติดไปด้วย)
            window.location.href = data.redirect_url || 'personel_manage.php';
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกข้อมูลได้'));
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        console.error('Error:', error);
        alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
    });
});
</script>