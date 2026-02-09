<?php
include 'middleware.php';
?>
<?php
ob_start();
include '../condb/condb.php';

// ตรวจสอบการส่งข้อมูลฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ดึงค่าจากฟอร์ม
    $personel_id = $_GET['id'];
    $worklevel_id = $_POST['worklevel_id'];
    $workbranch_id = $_POST['workbranch_id'];

    // ตรวจสอบว่ามีการส่งค่าผ่านฟอร์มหรือไม่
    if (empty($worklevel_id) || empty($workbranch_id)) {
        $_SESSION['message'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        $_SESSION['message_type'] = "danger";
        header("Location: personel_add_job.php?id=$personel_id");
        exit();
    }

    // ตรวจสอบว่ามีงานซ้ำหรือไม่
    $check_query = "SELECT id FROM work_detail 
                   WHERE personel_id = ? AND worklevel_id = ? AND workbranch_id = ?";
    $check_stmt = $mysqli3->prepare($check_query);
    $check_stmt->bind_param("iii", $personel_id, $worklevel_id, $workbranch_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $_SESSION['message'] = "⚠️ มีงานนี้อยู่แล้วในระบบ";
        $_SESSION['message_type'] = "warning";
        header("Location: personel_add_job.php?id=$personel_id");
        exit();
    }

    // สร้างคำสั่ง SQL สำหรับการเพิ่มข้อมูล
    $insert_query = "INSERT INTO work_detail (personel_id, worklevel_id, workbranch_id) VALUES (?, ?, ?)";
    $stmt = $mysqli3->prepare($insert_query);
    $stmt->bind_param("iii", $personel_id, $worklevel_id, $workbranch_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "✅ เพิ่มข้อมูลงานเรียบร้อยแล้ว";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
        $_SESSION['message_type'] = "danger";
    }

    $stmt->close();
    header("Location: personel_add_job.php?id=$personel_id");
    exit();
}

// ลบงานออกจากข้อมูล
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM work_detail WHERE id = ?";
    $stmt = $mysqli3->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "🗑️ ลบงานเรียบร้อยแล้ว";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ เกิดข้อผิดพลาดในการลบข้อมูล";
        $_SESSION['message_type'] = "danger";
    }
    
    $stmt->close();
    header("Location: personel_add_job.php?id={$_GET['id']}");
    exit();
}

// ดึงข้อมูลบุคลากร
$personel_id = $_GET['id'];
$query_personel = "SELECT fullname FROM personel_data WHERE id = ?";
$stmt_personel = $mysqli3->prepare($query_personel);
$stmt_personel->bind_param("i", $personel_id);
$stmt_personel->execute();
$stmt_personel->bind_result($fullname);
$stmt_personel->fetch();
$stmt_personel->close();

// ดึงข้อมูลงานของบุคลากร
$query_jobs = "SELECT wd.id, wl.work_level_name, wb.workbranch_name, d.department_name
               FROM work_detail wd
               LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id
               LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
               LEFT JOIN department d ON wb.department_id = d.id
               WHERE wd.personel_id = ?
               ORDER BY d.department_name, wb.workbranch_name";

$stmt_jobs = $mysqli3->prepare($query_jobs);
$stmt_jobs->bind_param("i", $personel_id);
$stmt_jobs->execute();
$result_jobs = $stmt_jobs->get_result();

// จัดกลุ่มงานตามแผนก
$jobs_by_department = [];
while ($job = $result_jobs->fetch_assoc()) {
    $dept_name = $job['department_name'] ?: 'ไม่มีสังกัด';
    if (!isset($jobs_by_department[$dept_name])) {
        $jobs_by_department[$dept_name] = [];
    }
    $jobs_by_department[$dept_name][] = $job;
}
?>

<div class="max-w-6xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg border border-gray-100">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white text-center py-4 rounded-xl mb-8">
        <h2 class="text-2xl font-bold"><i class="fas fa-tasks mr-3"></i>จัดการงานบุคลากร</h2>
        <p class="text-purple-100 mt-2"><?= htmlspecialchars($fullname ?? '') ?></p>
    </div>

    <!-- ฟอร์มเพิ่มงานใหม่ -->
    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-plus-circle text-purple-600 mr-2"></i>
            เพิ่มงานใหม่
        </h3>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- ระดับงาน -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-layer-group mr-1 text-purple-600"></i>
                    ระดับงาน *
                </label>
                <select name="worklevel_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-colors">
                    <option value="">- เลือกระดับงาน -</option>
                    <?php
                    $query_worklevel = "SELECT id, work_level_name FROM worklevel ORDER BY work_level_name";
                    $result_worklevel = $mysqli3->query($query_worklevel);
                    while ($row = $result_worklevel->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['work_level_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- สาขางาน -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-briefcase mr-1 text-purple-600"></i>
                    สาขางาน *
                </label>
                <select name="workbranch_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 transition-colors">
                    <option value="">- เลือกสาขางาน -</option>
                    <?php
                    $query_workbranch = "SELECT wb.id, wb.workbranch_name, d.department_name 
                                        FROM workbranch wb 
                                        LEFT JOIN department d ON wb.department_id = d.id 
                                        ORDER BY d.department_name, wb.workbranch_name";
                    $result_workbranch = $mysqli3->query($query_workbranch);
                    while ($row = $result_workbranch->fetch_assoc()) {
                        $display_name = $row['workbranch_name'];
                        if ($row['department_name']) {
                            $display_name .= " ({$row['department_name']})";
                        }
                        echo "<option value='{$row['id']}'>{$display_name}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- ปุ่มส่ง -->
            <div class="flex items-end">
                <button type="submit"
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors flex items-center justify-center gap-2">
                    <i class="fas fa-plus"></i>
                    เพิ่มงาน
                </button>
            </div>
        </form>
    </div>

    <!-- แสดงงานที่มีอยู่ -->
    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-list-check text-purple-600 mr-2"></i>
            งานที่มีอยู่
            <span class="ml-2 bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm font-medium">
                <?= $result_jobs->num_rows ?> งาน
            </span>
        </h3>

        <?php if (empty($jobs_by_department)): ?>
            <div class="text-center py-8 bg-white rounded-lg border-2 border-dashed border-gray-300">
                <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-500">ยังไม่มีงานที่ได้รับมอบหมาย</p>
                <p class="text-sm text-gray-400 mt-1">เพิ่มงานแรกโดยใช้ฟอร์มด้านบน</p>
            </div>
        <?php else: ?>
            <!-- แสดงงานแบบ Tag/Group -->
            <div class="space-y-6">
                <?php foreach($jobs_by_department as $dept_name => $jobs): ?>
                <div class="bg-white rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                        <i class="fas fa-building text-purple-600"></i>
                        <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($dept_name) ?></h4>
                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded-full text-xs">
                            <?= count($jobs) ?> งาน
                        </span>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <?php foreach($jobs as $job): ?>
                        <div class="group relative">
                            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-2 rounded-full flex items-center gap-2 shadow-sm hover:shadow-md transition-all duration-200">
                                <span class="text-sm font-medium"><?= htmlspecialchars($job['workbranch_name']) ?></span>
                                <span class="bg-purple-700 px-2 py-1 rounded-full text-xs">
                                    <?= htmlspecialchars($job['work_level_name']) ?>
                                </span>
                                <a href="personel_add_job.php?id=<?= $personel_id ?>&delete_id=<?= $job['id'] ?>" 
                                   onclick="return confirm('ยืนยันการลบงาน \"<?= htmlspecialchars($job['workbranch_name']) ?>\" หรือไม่?')"
                                   class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 ml-1 hover:bg-purple-800 rounded-full p-1">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ปุ่มดำเนินการ -->
    <div class="flex justify-between items-center pt-6 border-t border-gray-200 mt-8">
        <a href="personel_manage.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            กลับสู่หน้าจัดการ
        </a>
        <a href="personel_edit.php?id=<?= $personel_id ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors flex items-center gap-2">
            <i class="fas fa-edit"></i>
            แก้ไขข้อมูลส่วนตัว
        </a>
    </div>
</div>

<style>
.tag-hover {
    transition: all 0.3s ease;
}

.tag-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(128, 90, 213, 0.3);
}

.fade-in {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// เพิ่ม animation ให้กับ tag ใหม่
document.addEventListener('DOMContentLoaded', function() {
    const tags = document.querySelectorAll('.group');
    tags.forEach((tag, index) => {
        tag.style.animationDelay = `${index * 0.1}s`;
        tag.classList.add('fade-in');
    });
});

// แสดงตัวอย่างเมื่อเลือกสาขางาน
document.querySelector('select[name="workbranch_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        this.classList.add('border-green-400');
    } else {
        this.classList.remove('border-green-400');
    }
});

// แสดงตัวอย่างเมื่อเลือกระดับงาน
document.querySelector('select[name="worklevel_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        this.classList.add('border-green-400');
    } else {
        this.classList.remove('border-green-400');
    }
});
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>