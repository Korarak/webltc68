<?php
include 'middleware.php';
?>
<?php
ob_start();
include '../condb/condb.php';
require_once '../include/SecurityHelper.php';

// ฟังก์ชันอัพโหลด base64 image
function uploadBase64Image($base64_string, $upload_path, $prefix = "profile_") {
    if (empty($base64_string)) {
        return null;
    }

    // ตรวจสอบว่าเป็น base64 image จริงหรือไม่
    if (preg_match('/^data:image\/(\w+);base64,/', $base64_string, $type)) {
        $data = substr($base64_string, strpos($base64_string, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            return null;
        }

        $data = base64_decode($data);
        if ($data === false) {
            return null;
        }
    } else {
        return null;
    }

    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0755, true);
    }

    $filename = $prefix . uniqid() . '.' . $type;
    $file_path = $upload_path . $filename;

    if (file_put_contents($file_path, $data)) {
        return $file_path;
    }

    return null;
}

// ตรวจสอบว่า id ได้รับมาจาก URL หรือไม่
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $personel_id = $_GET['id'];

    // ดึงข้อมูลบุคลากรจากฐานข้อมูล
    $query = "SELECT p.id, p.thai_id, p.fullname, p.Tel, p.E_mail, p.gender_id, p.education_level_id, p.education_detail, 
                     p.department_id, p.position_id, p.position_level_id, p.profile_image, 
                     d.department_name, pos.position_name, pl.level_name, g.gender_name, el.education_name
              FROM personel_data p
              LEFT JOIN department d ON p.department_id = d.id
              LEFT JOIN positions pos ON p.position_id = pos.id
              LEFT JOIN position_level pl ON p.position_level_id = pl.id
              LEFT JOIN gender g ON p.gender_id = g.id
              LEFT JOIN education_level el ON p.education_level_id = el.id
              WHERE p.id = ? AND p.is_deleted = 0";
    $stmt = $mysqli3->prepare($query);
    $stmt->bind_param("i", $personel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // ตรวจสอบว่ามีข้อมูลบุคลากร
    if ($result->num_rows > 0) {
        $personel = $result->fetch_assoc();
        $personel['thai_id'] = SecurityHelper::decrypt($personel['thai_id']);
        
        // ดึงข้อมูลงานที่รับผิดชอบ
        $work_query = "SELECT wd.workbranch_id, wd.worklevel_id, 
                              wb.workbranch_name, wl.work_level_name
                       FROM work_detail wd
                       LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
                       LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id
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
        
    } else {
        $_SESSION['message'] = "ไม่พบข้อมูลบุคลากรนี้";
        $_SESSION['message_type'] = "danger";
        header("Location: personel_manage.php");
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['message'] = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล";
    $_SESSION['message_type'] = "danger";
    header("Location: personel_manage.php");
    exit();
}

// ฟังก์ชันดึงข้อมูลฝ่ายและแผนกแบบจัดกลุ่ม
function getDepartmentGroups($selected_id = null) {
    global $mysqli3;
    $query = "SELECT d.id, d.department_name, 
                     CASE 
                         WHEN d.department_name LIKE 'ฝ่าย%' THEN 'ฝ่ายบริหาร'
                         WHEN d.department_name LIKE 'แผนก%' THEN 'แผนกวิชาการ'
                         ELSE 'อื่นๆ'
                     END as group_type
              FROM department d 
              ORDER BY 
                  CASE 
                      WHEN d.department_name LIKE 'ฝ่าย%' THEN 1
                      WHEN d.department_name LIKE 'แผนก%' THEN 2
                      ELSE 3
                  END, 
                  d.department_name";
    
    $result = $mysqli3->query($query);
    $groups = [
        'ฝ่ายบริหาร' => [],
        'แผนกวิชาการ' => [],
        'อื่นๆ' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $selected = ($row['id'] == $selected_id) ? 'selected' : '';
        $row['selected'] = $selected;
        $groups[$row['group_type']][] = $row;
    }
    return $groups;
}

// ฟังก์ชันดึงข้อมูลงานแบบจัดกลุ่มตามฝ่าย
function getWorkbranchGroups($selected_ids = []) {
    global $mysqli3;
    $query = "SELECT wb.id, wb.workbranch_name, d.department_name,
                     CASE 
                         WHEN d.department_name LIKE 'ฝ่าย%' THEN d.department_name
                         ELSE 'แผนกวิชาการ'
                     END as group_name
              FROM workbranch wb
              LEFT JOIN department d ON wb.department_id = d.id
              ORDER BY 
                  CASE 
                      WHEN d.department_name LIKE 'ฝ่าย%' THEN 1
                      ELSE 2
                  END,
                  d.department_name, wb.workbranch_name";
    
    $result = $mysqli3->query($query);
    $groups = [];
    
    while ($row = $result->fetch_assoc()) {
        $groupName = $row['group_name'];
        if (!isset($groups[$groupName])) {
            $groups[$groupName] = [];
        }
        $selected = in_array($row['id'], $selected_ids) ? 'selected' : '';
        $row['selected'] = $selected;
        $groups[$groupName][] = $row;
    }
    return $groups;
}

// ฟังก์ชันดึงข้อมูลทั่วไป
function getOptions($table, $id_field, $name_field, $selected_id = null) {
    global $mysqli3;
    $query = "SELECT $id_field, $name_field FROM $table ORDER BY $name_field";
    $result = $mysqli3->query($query);
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $selected = ($row[$id_field] == $selected_id) ? 'selected' : '';
        $options .= "<option value='{$row[$id_field]}' $selected>{$row[$name_field]}</option>";
    }
    return $options;
}

$departmentGroups = getDepartmentGroups($personel['department_id']);
$workbranchGroups = getWorkbranchGroups($workbranches);
?>

<div class="max-w-4xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white text-center py-4 rounded-xl mb-8">
        <h2 class="text-2xl font-bold"><i class="fas fa-user-edit mr-3"></i>แก้ไขข้อมูลบุคลากร</h2>
        <p class="text-blue-100 mt-2"><?= htmlspecialchars($personel['fullname']) ?></p>
    </div>

    <!-- แสดงรูปโปรไฟล์ปัจจุบัน -->
    <?php 
    if ($personel['profile_image']): 
        $img_path = $personel['profile_image'];
        // Check if path is relative and doesn't start with http/https
        if ($img_path && !preg_match("~^(?:f|ht)tps?://~i", $img_path)) {
            $img_path = "/" . ltrim($img_path, '/');
        }
    ?>
    <div class="text-center mb-6">
        <div class="inline-block bg-gray-100 p-4 rounded-lg">
            <p class="text-sm text-gray-600 mb-2">รูปโปรไฟล์ปัจจุบัน:</p>
            <img src="<?= htmlspecialchars($img_path) ?>" 
                 alt="<?= htmlspecialchars($personel['fullname']) ?>" 
                 class="w-32 h-32 rounded-full object-cover border-4 border-white shadow mx-auto">
        </div>
    </div>
    <?php endif; ?>

    <form action="personel_update.php" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="id" value="<?= $personel['id'] ?>">
        <input type="hidden" name="current_profile_image" value="<?= htmlspecialchars($personel['profile_image'] ?? '') ?>">
        <input type="hidden" name="profile_image_base64" id="profileBase64">

        <!-- ข้อมูลส่วนตัว -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-id-card text-blue-600 mr-2"></i>
                ข้อมูลส่วนตัว
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-address-card mr-1 text-blue-600"></i>
                        เลขบัตรประชาชน
                    </label>
                    <input type="text" name="thai_id" 
                           value="<?= htmlspecialchars($personel['thai_id']) ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-100 cursor-not-allowed" 
                           readonly
                           placeholder="เลขบัตรประชาชน">
                    <p class="text-xs text-gray-500 mt-1">ไม่สามารถแก้ไขเลขบัตรประชาชนได้</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-blue-600"></i>
                        ชื่อ-นามสกุล *
                    </label>
                    <input type="text" name="fullname" 
                           value="<?= htmlspecialchars($personel['fullname']) ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                           required 
                           placeholder="กรอกชื่อ-นามสกุลเต็ม">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-venus-mars mr-1 text-blue-600"></i>
                        เพศ
                    </label>
                    <select name="gender_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
                        <option value="-">- เลือกเพศ -</option>
                        <?= getOptions('gender', 'id', 'gender_name', $personel['gender_id']); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap mr-1 text-blue-600"></i>
                        ระดับการศึกษา
                    </label>
                    <select name="education_level_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
                        <option value="-">- เลือกระดับการศึกษา -</option>
                        <?= getOptions('education_level', 'id', 'education_name', $personel['education_level_id']); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-alt mr-1 text-blue-600"></i>
                        วุฒิการศึกษา (ระบุสาขา)
                    </label>
                    <input type="text" name="education_detail" 
                           value="<?= htmlspecialchars($personel['education_detail'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                           placeholder="เช่น วท.บ. วิทยาการคอมพิวเตอร์">
                </div>
            </div>
        </div>

        <!-- ข้อมูลติดต่อ -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-address-book text-blue-600 mr-2"></i>
                ข้อมูลติดต่อ
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone mr-1 text-blue-600"></i>
                        โทรศัพท์
                    </label>
                    <input type="text" name="Tel" 
                           value="<?= htmlspecialchars($personel['Tel'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                           placeholder="กรอกเบอร์โทรศัพท์">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1 text-blue-600"></i>
                        อีเมล
                    </label>
                    <input type="email" name="E_mail" 
                           value="<?= htmlspecialchars($personel['E_mail'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                           placeholder="กรอกอีเมล">
                </div>
            </div>
        </div>

        <!-- ข้อมูลตำแหน่ง -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-briefcase text-blue-600 mr-2"></i>
                ข้อมูลตำแหน่ง
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tie mr-1 text-blue-600"></i>
                        ตำแหน่ง
                    </label>
                    <select name="position_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
                        <option value="-">- เลือกตำแหน่ง -</option>
                        <?= getOptions('positions', 'id', 'position_name', $personel['position_id']); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-award mr-1 text-blue-600"></i>
                        ระดับวิทยฐานะ
                    </label>
                    <select name="position_level_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
                        <option value="-">- เลือกระดับวิทยฐานะ -</option>
                        <?= getOptions('position_level', 'id', 'level_name', $personel['position_level_id']); ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ข้อมูลสังกัด -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-building text-blue-600 mr-2"></i>
                ข้อมูลสังกัด
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sitemap mr-1 text-blue-600"></i>
                        แผนก/ฝ่าย
                    </label>
                    <select name="department_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
                        <option value="-">- เลือกแผนก/ฝ่าย -</option>
                        <?php foreach($departmentGroups as $groupName => $departments): ?>
                            <?php if(!empty($departments)): ?>
                                <optgroup label="🏢 <?= $groupName ?>">
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tasks mr-1 text-blue-600"></i>
                        สาขางาน (เลือกได้หลายรายการ)
                    </label>
                    <select name="workbranch_id[]" multiple class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors h-32">
                        <?php foreach($workbranchGroups as $groupName => $workbranches): ?>
                            <?php if(!empty($workbranches)): ?>
                                <optgroup label="📋 <?= $groupName ?>">
                                    <?php foreach($workbranches as $wb): ?>
                                        <option value="<?= $wb['id'] ?>" <?= $wb['selected'] ?>>
                                            <?= htmlspecialchars($wb['workbranch_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">กด Ctrl (หรือ Command บน Mac) เพื่อเลือกหลายรายการ</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-layer-group mr-1 text-blue-600"></i>
                        ระดับงาน (เลือกได้หลายรายการ)
                    </label>
                    <select name="worklevel_id[]" multiple class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors">
                        <?php 
                        // สร้าง options สำหรับ worklevel พร้อม selected
                        $worklevel_options = '';
                        $worklevel_query = "SELECT id, work_level_name FROM worklevel ORDER BY work_level_name";
                        $worklevel_result = $mysqli3->query($worklevel_query);
                        while ($wl = $worklevel_result->fetch_assoc()) {
                            $selected = in_array($wl['id'], $worklevels) ? 'selected' : '';
                            $worklevel_options .= "<option value='{$wl['id']}' $selected>{$wl['work_level_name']}</option>";
                        }
                        echo $worklevel_options;
                        ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">กด Ctrl (หรือ Command บน Mac) เพื่อเลือกหลายรายการ</p>
                </div>
            </div>
        </div>

        <!-- รูปโปรไฟล์ -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-camera text-blue-600 mr-2"></i>
                รูปโปรไฟล์
            </h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-image mr-1 text-blue-600"></i>
                    อัพโหลดรูปภาพใหม่
                </label>
                <input type="file" id="profileInput" accept="image/*" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-1">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</p>
                <?php if ($personel['profile_image']): ?>
                <p class="text-xs text-blue-600 mt-2">⚠️ หากอัพโหลดรูปใหม่ รูปเดิมจะถูกแทนที่</p>
                <?php endif; ?>
                
                <!-- Preview Area -->
                <div id="profilePreviewContainer" class="mt-3 hidden">
                    <p class="text-sm text-gray-600 mb-2">ตัวอย่างรูปภาพใหม่:</p>
                    <img id="profilePreview" class="max-w-xs rounded-lg shadow border" alt="Preview">
                </div>
            </div>
        </div>

        <!-- Crop Modal -->
        <div id="profileModal" class="fixed inset-0 hidden bg-black bg-opacity-70 items-center justify-center z-50">
            <div class="bg-white rounded-lg p-4 max-w-3xl w-full">
                <h3 class="text-lg font-semibold mb-4">ตัดรูปภาพโปรไฟล์</h3>
                <div class="max-h-[60vh] overflow-auto">
                    <img id="profileCropPreview" class="max-w-full"/>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" id="profileModal_cancel" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors">ยกเลิก</button>
                    <button type="button" id="profileModal_confirm" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">ยืนยัน</button>
                </div>
            </div>
        </div>

        <!-- ปุ่มดำเนินการ -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <a href="personel_manage.php" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                กลับสู่หน้าจัดการ
            </a>
            <div class="flex gap-3">
                <a href="personel_add_job.php?id=<?= $personel['id'] ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    เพิ่มงาน
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    บันทึกการแก้ไข
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

<script>
let cropper;

// ฟังก์ชันจัดการการ crop ภาพ
function initImageCrop(inputId, modalId, previewId, hiddenInputId, aspectRatio = 1/1, outputWidth = 300, outputHeight = 300) {
    const uploadInput = document.getElementById(inputId);
    const cropModal = document.getElementById(modalId);
    const imagePreview = document.getElementById(previewId);
    const hiddenInput = document.getElementById(hiddenInputId);
    const previewContainer = document.getElementById('profilePreviewContainer');
    const finalPreview = document.getElementById('profilePreview');

    uploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        // ตรวจสอบประเภทไฟล์
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF) เท่านั้น');
            uploadInput.value = '';
            return;
        }

        // ตรวจสอบขนาดไฟล์ (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('ขนาดไฟล์ต้องไม่เกิน 5MB');
            uploadInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            imagePreview.src = reader.result;
            cropModal.classList.remove('hidden');
            
            // เริ่มต้น cropper
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

    // ปุ่มยกเลิก
    document.getElementById(modalId + "_cancel").addEventListener('click', () => {
        if (cropper) {
            cropper.destroy();
        }
        cropModal.classList.add('hidden');
        uploadInput.value = '';
        previewContainer.classList.add('hidden');
    });

    // ปุ่มยืนยัน
    document.getElementById(modalId + "_confirm").addEventListener('click', () => {
        const canvas = cropper.getCroppedCanvas({ 
            width: outputWidth, 
            height: outputHeight 
        });
        
        // แสดงตัวอย่างภาพที่ตัดแล้ว
        finalPreview.src = canvas.toDataURL("image/jpeg", 0.9);
        previewContainer.classList.remove('hidden');
        
        // บันทึก base64 ลงใน hidden input
        hiddenInput.value = canvas.toDataURL("image/jpeg", 0.9);
        
        // ปิด modal และทำลาย cropper
        cropper.destroy();
        cropModal.classList.add('hidden');
    });
}

document.addEventListener("DOMContentLoaded", () => {
    // เริ่มต้นระบบ crop สำหรับรูปโปรไฟล์ (อัตราส่วน 3:4, ขนาด 300x400)
    initImageCrop("profileInput", "profileModal", "profileCropPreview", "profileBase64", 3/4, 300, 400);
});

// เพิ่มการแจ้งเตือนเมื่อเลือกหลายงาน
document.addEventListener('DOMContentLoaded', function() {
    const workbranchSelect = document.querySelector('select[name="workbranch_id[]"]');
    const worklevelSelect = document.querySelector('select[name="worklevel_id[]"]');
    
    workbranchSelect.addEventListener('change', function() {
        updateSelectionCount(this, 'งาน');
    });
    
    worklevelSelect.addEventListener('change', function() {
        updateSelectionCount(this, 'ระดับงาน');
    });
    
    // อัพเดทจำนวนที่เลือกเมื่อโหลดหน้า
    updateSelectionCount(workbranchSelect, 'งาน');
    updateSelectionCount(worklevelSelect, 'ระดับงาน');
    
    function updateSelectionCount(select, type) {
        const selectedCount = select.selectedOptions.length;
        let message = '';
        
        if (selectedCount > 0) {
            message = `เลือก ${type} แล้ว ${selectedCount} รายการ`;
        }
        
        // อัพเดทหรือสร้างข้อความแสดงผล
        let countElement = select.parentNode.querySelector('.selection-count');
        if (!countElement) {
            countElement = document.createElement('p');
            countElement.className = 'selection-count text-xs text-blue-600 mt-1 font-medium';
            select.parentNode.appendChild(countElement);
        }
        countElement.textContent = message;
    }
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>