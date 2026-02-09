<?php
include 'middleware.php';
ob_start();
include '../condb/condb.php';

// ฟังก์ชันดึงข้อมูลฝ่ายและแผนกแบบจัดกลุ่ม
function getDepartmentGroups() {
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
        $groups[$row['group_type']][] = $row;
    }
    return $groups;
}

// ฟังก์ชันดึงข้อมูลงานแบบจัดกลุ่มตามฝ่าย
function getWorkbranchGroups() {
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
        $groups[$groupName][] = $row;
    }
    return $groups;
}

// ฟังก์ชันดึงข้อมูลทั่วไป
function getOptions($table, $id_column, $name_column) {
    global $mysqli3;
    $stmt = $mysqli3->prepare("SELECT $id_column, $name_column FROM $table ORDER BY $name_column");
    $stmt->execute();
    $result = $stmt->get_result();
    $options = "";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='{$row[$id_column]}'>{$row[$name_column]}</option>";
    }
    $stmt->close();
    return $options;
}

// ฟังก์ชันตรวจสอบเลขบัตรประชาชนซ้ำ
function checkDuplicateThaiID($thai_id) {
    global $mysqli3;
    $stmt = $mysqli3->prepare("SELECT id, fullname FROM personel_data WHERE thai_id = ?");
    $stmt->bind_param("s", $thai_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    $stmt->close();
    return $existing;
}

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

// ตัวแปรเก็บค่าฟอร์มและข้อความผิดพลาด
$form_data = [];
$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // เก็บค่าจากฟอร์ม
    $form_data = [
        'thai_id' => $_POST['thai_id'],
        'fullname' => $_POST['fullname'],
        'Tel' => $_POST['Tel'] ?? null,
        'E_mail' => $_POST['E_mail'] ?? null,
        'gender_id' => $_POST['gender_id'] !== '-' ? $_POST['gender_id'] : null,
        'education_level_id' => $_POST['education_level_id'] !== '-' ? $_POST['education_level_id'] : null,
        'education_detail' => $_POST['education_detail'] ?? null,
        'position_id' => $_POST['position_id'] !== '-' ? $_POST['position_id'] : null,
        'position_level_id' => $_POST['position_level_id'] !== '-' ? $_POST['position_level_id'] : null,
        'department_id' => $_POST['department_id'] !== '-' ? $_POST['department_id'] : null,
        'workbranch_ids' => $_POST['workbranch_id'] ?? [],
        'worklevel_ids' => $_POST['worklevel_id'] ?? []
    ];

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($form_data['thai_id'])) {
        $errors[] = "กรุณากรอกเลขบัตรประชาชน";
    } elseif (strlen($form_data['thai_id']) != 13 || !is_numeric($form_data['thai_id'])) {
        $errors[] = "เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก";
    } else {
        // ตรวจสอบเลขบัตรประชาชนซ้ำ
        $existing_person = checkDuplicateThaiID($form_data['thai_id']);
        if ($existing_person) {
            $errors[] = "เลขบัตรประชาชนนี้มีอยู่ในระบบแล้ว (ชื่อ: {$existing_person['fullname']})";
        }
    }

    if (empty($form_data['fullname'])) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุล";
    }

    // อัพโหลดรูปโปรไฟล์จาก base64
    $profile_img = null;
    if (!empty($_POST['profile_img_base64'])) {
        // Fix: Upload to central directory
        $physical_path = uploadBase64Image($_POST['profile_img_base64'], "../uploads/ltc_personal/", "profile_");
        if ($physical_path) {
             // Fix: DB should store path relative to root (uploads/...) not ../uploads/...
             $profile_img = str_replace('../', '', $physical_path);
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัพโหลดรูปภาพ";
        }
    }

    // ถ้าไม่มีข้อผิดพลาด ให้บันทึกข้อมูล
    if (empty($errors)) {
        try {
            $mysqli3->begin_transaction();

            // เพิ่มข้อมูลบุคลากร
            $stmt = $mysqli3->prepare("INSERT INTO personel_data (thai_id, fullname, Tel, E_mail, gender_id, education_level_id, education_detail, position_id, position_level_id, department_id, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiissiis", 
                $form_data['thai_id'], 
                $form_data['fullname'], 
                $form_data['Tel'], 
                $form_data['E_mail'], 
                $form_data['gender_id'], 
                $form_data['education_level_id'], 
                $form_data['education_detail'],
                $form_data['position_id'], 
                $form_data['position_level_id'], 
                $form_data['department_id'], 
                $profile_img
            );

            if ($stmt->execute()) {
                $personel_id = $stmt->insert_id;
                $stmt->close();

                // เพิ่มข้อมูลงาน
                if (!empty($form_data['workbranch_ids']) && !empty($form_data['worklevel_ids'])) {
                    $stmt_detail = $mysqli3->prepare("INSERT INTO work_detail (personel_id, workbranch_id, worklevel_id) VALUES (?, ?, ?)");
                    foreach ($form_data['workbranch_ids'] as $workbranch_id) {
                        foreach ($form_data['worklevel_ids'] as $worklevel_id) {
                            $stmt_detail->bind_param("iii", $personel_id, $workbranch_id, $worklevel_id);
                            $stmt_detail->execute();
                        }
                    }
                    $stmt_detail->close();
                }

                $mysqli3->commit();
                
                $_SESSION['message'] = "✅ เพิ่มข้อมูลบุคลากรสำเร็จ";
                $_SESSION['message_type'] = "success";
                header("Location: personel_manage.php");
                exit();
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $mysqli3->rollback();
            $errors[] = "❌ เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }
    }
}

$departmentGroups = getDepartmentGroups();
$workbranchGroups = getWorkbranchGroups();
?>

<div class="max-w-4xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white text-center py-4 rounded-xl mb-8">
        <h2 class="text-2xl font-bold"><i class="fas fa-user-plus mr-3"></i>เพิ่มบุคลากรใหม่</h2>
    </div>

    <!-- แสดงข้อความผิดพลาด -->
    <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
        <div class="flex items-center gap-2 text-red-700 font-semibold mb-2">
            <i class="fas fa-exclamation-triangle"></i>
            เกิดข้อผิดพลาด
        </div>
        <ul class="list-disc list-inside space-y-1 text-red-600">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <!-- ข้อมูลส่วนตัว -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-id-card text-green-600 mr-2"></i>
                ข้อมูลส่วนตัว
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-address-card mr-1 text-green-600"></i>
                        เลขบัตรประชาชน *
                    </label>
                    <input type="text" name="thai_id" 
                           value="<?= htmlspecialchars($form_data['thai_id'] ?? '') ?>" 
                           class="w-full border <?= isset($errors) && in_array('เลขบัตรประชาชน', array_map(function($e) { return substr($e, 0, 15); }, $errors)) ? 'border-red-300' : 'border-gray-300' ?> rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors" 
                           required 
                           placeholder="กรอกเลขบัตรประชาชน 13 หลัก"
                           maxlength="13"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <p class="text-xs text-gray-500 mt-1">ต้องเป็นตัวเลข 13 หลักเท่านั้น</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-1 text-green-600"></i>
                        ชื่อ-นามสกุล *
                    </label>
                    <input type="text" name="fullname" 
                           value="<?= htmlspecialchars($form_data['fullname'] ?? '') ?>" 
                           class="w-full border <?= isset($errors) && in_array('ชื่อ-นามสกุล', array_map(function($e) { return substr($e, 0, 15); }, $errors)) ? 'border-red-300' : 'border-gray-300' ?> rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors" 
                           required 
                           placeholder="กรอกชื่อ-นามสกุลเต็ม">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-venus-mars mr-1 text-green-600"></i>
                        เพศ
                    </label>
                    <select name="gender_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors">
                        <option value="-">- เลือกเพศ -</option>
                        <?= getOptions('gender', 'id', 'gender_name'); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap mr-1 text-green-600"></i>
                        ระดับการศึกษา
                    </label>
                    <select name="education_level_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors">
                        <option value="-">- เลือกระดับการศึกษา -</option>
                        <?= getOptions('education_level', 'id', 'education_name'); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-alt mr-1 text-green-600"></i>
                        วุฒิการศึกษา (ระบุสาขา)
                    </label>
                    <input type="text" name="education_detail" 
                           value="<?= htmlspecialchars($form_data['education_detail'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors" 
                           placeholder="เช่น วท.บ. วิทยาการคอมพิวเตอร์">
                </div>
            </div>
        </div>

        <!-- ข้อมูลติดต่อ -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-address-book text-green-600 mr-2"></i>
                ข้อมูลติดต่อ
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-phone mr-1 text-green-600"></i>
                        โทรศัพท์
                    </label>
                    <input type="text" name="Tel" 
                           value="<?= htmlspecialchars($form_data['Tel'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors" 
                           placeholder="กรอกเบอร์โทรศัพท์">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-1 text-green-600"></i>
                        อีเมล
                    </label>
                    <input type="email" name="E_mail" 
                           value="<?= htmlspecialchars($form_data['E_mail'] ?? '') ?>" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors" 
                           placeholder="กรอกอีเมล">
                </div>
            </div>
        </div>

        <!-- ข้อมูลตำแหน่ง -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-briefcase text-green-600 mr-2"></i>
                ข้อมูลตำแหน่ง
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tie mr-1 text-green-600"></i>
                        ตำแหน่ง
                    </label>
                    <select name="position_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors">
                        <option value="-">- เลือกตำแหน่ง -</option>
                        <?= getOptions('positions', 'id', 'position_name'); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-award mr-1 text-green-600"></i>
                        ระดับวิทยฐานะ
                    </label>
                    <select name="position_level_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors">
                        <option value="-">- เลือกระดับวิทยฐานะ -</option>
                        <?= getOptions('position_level', 'id', 'level_name'); ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ข้อมูลสังกัด -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-building text-green-600 mr-2"></i>
                ข้อมูลสังกัด
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sitemap mr-1 text-green-600"></i>
                        แผนก/ฝ่าย
                    </label>
                    <select name="department_id" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors">
                        <option value="-">- เลือกแผนก/ฝ่าย -</option>
                        <?php foreach($departmentGroups as $groupName => $departments): ?>
                            <?php if(!empty($departments)): ?>
                                <optgroup label="🏢 <?= $groupName ?>">
                                    <?php foreach($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= (isset($form_data['department_id']) && $form_data['department_id'] == $dept['id']) ? 'selected' : '' ?>>
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
                        <i class="fas fa-tasks mr-1 text-green-600"></i>
                        สาขางาน (เลือกได้หลายรายการ)
                    </label>
                    <select name="workbranch_id[]" multiple class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors h-32">
                        <?php foreach($workbranchGroups as $groupName => $workbranches): ?>
                            <?php if(!empty($workbranches)): ?>
                                <optgroup label="📋 <?= $groupName ?>">
                                    <?php foreach($workbranches as $wb): ?>
                                        <option value="<?= $wb['id'] ?>" <?= (isset($form_data['workbranch_ids']) && in_array($wb['id'], $form_data['workbranch_ids'])) ? 'selected' : '' ?>>
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
                        <i class="fas fa-layer-group mr-1 text-green-600"></i>
                        ระดับงาน (เลือกได้หลายรายการ)
                    </label>
                    <select name="worklevel_id[]" multiple class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors">
                        <?= getOptions('worklevel', 'id', 'work_level_name'); ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">กด Ctrl (หรือ Command บน Mac) เพื่อเลือกหลายรายการ</p>
                </div>
            </div>
        </div>

        <!-- รูปโปรไฟล์ -->
        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-camera text-green-600 mr-2"></i>
                รูปโปรไฟล์
            </h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-image mr-1 text-green-600"></i>
                    อัพโหลดรูปภาพ
                </label>
                <input type="file" id="profileInput" accept="image/*" 
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-green-400 focus:border-green-400 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <input type="hidden" name="profile_img_base64" id="profileBase64">
                <p class="text-xs text-gray-500 mt-1">รองรับไฟล์รูปภาพ (JPG, PNG, GIF) ขนาดไม่เกิน 5MB</p>
                
                <!-- Preview Area -->
                <div id="profilePreviewContainer" class="mt-3 hidden">
                    <p class="text-sm text-gray-600 mb-2">ตัวอย่างรูปภาพ:</p>
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
                    <button type="button" id="profileModal_confirm" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">ยืนยัน</button>
                </div>
            </div>
        </div>

        <!-- ปุ่มดำเนินการ -->
        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
            <a href="personel_manage.php" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                กลับสู่หน้าจัดการ
            </a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i>
                บันทึกข้อมูล
            </button>
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
    // เริ่มต้นระบบ crop สำหรับรูปโปรไฟล์ (อัตราส่วน 3:4, ขนาด 600x800)
    initImageCrop("profileInput", "profileModal", "profileCropPreview", "profileBase64", 3/4, 600, 800);
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
            countElement.className = 'selection-count text-xs text-green-600 mt-1 font-medium';
            select.parentNode.appendChild(countElement);
        }
        countElement.textContent = message;
    }
});

// ตรวจสอบเลขบัตรประชาชนแบบ real-time
document.querySelector('input[name="thai_id"]').addEventListener('blur', function() {
    const thaiId = this.value;
    if (thaiId.length === 13 && /^\d+$/.test(thaiId)) {
        this.classList.remove('border-red-300');
        this.classList.add('border-green-300');
    } else if (thaiId.length > 0) {
        this.classList.add('border-red-300');
        this.classList.remove('border-green-300');
    }
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>