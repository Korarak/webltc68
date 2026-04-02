<?php
//person_detail.php
require '../condb/condb.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='text-red-600 text-center'>ไม่พบข้อมูล</p>";
    exit;
}

$id = (int)$_GET['id'];

// ดึงข้อมูลบุคลากรพร้อมตำแหน่ง วิทยฐานะ และแผนก
$sql = "SELECT 
            p.fullname, p.profile_image, p.Tel, p.E_mail, p.education_detail,
            pos.position_name, pl.level_name, d.department_name, el.education_name
        FROM personel_data p
        LEFT JOIN positions pos ON p.position_id = pos.id
        LEFT JOIN position_level pl ON p.position_level_id = pl.id
        LEFT JOIN department d ON p.department_id = d.id
        LEFT JOIN education_level el ON p.education_level_id = el.id
        WHERE p.id = ? AND p.is_deleted = 0";

$stmt = $mysqli3->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$row = $result->fetch_assoc()) {
    echo "<p class='text-center text-gray-500'>ไม่พบข้อมูลบุคลากรนี้</p>";
    exit;
}

// ดึง workbranch + worklevel จาก work_detail
$sql_work = "SELECT 
                wb.workbranch_name, wl.work_level_name
             FROM work_detail wd
             LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id
             LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id
             WHERE wd.personel_id = ?";
$stmt2 = $mysqli3->prepare($sql_work);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$result2 = $stmt2->get_result();

$work_items = [];
while ($w = $result2->fetch_assoc()) {
    $work_items[] = $w['workbranch_name'] . " (" . $w['work_level_name'] . ")";
}
$work_detail_text = !empty($work_items) ? implode("<br>", $work_items) : null;

// ตรวจสอบว่ามีข้อมูลในแต่ละฟิลด์หรือไม่
$has_position = !empty($row['position_name']) && $row['position_name'] != '-';
$has_level = !empty($row['level_name']) && $row['level_name'] != '-';
$has_department = !empty($row['department_name']) && $row['department_name'] != '-';
$has_tel = !empty($row['Tel']) && $row['Tel'] != '-';
$has_email = !empty($row['E_mail']) && $row['E_mail'] != '-';
$has_work = !empty($work_detail_text) && $work_detail_text != '-';
$has_education = (!empty($row['education_name']) || !empty($row['education_detail']));

?>

<div class="flex flex-col md:flex-row gap-6 p-4 md:p-0">
  <div class="w-full md:w-3/4 lg:w-1/4 mx-auto">
    <img src="/<?= htmlspecialchars($row['profile_image'] ?: 'uploads/default.png') ?>"
         alt="<?= htmlspecialchars($row['fullname']) ?>"
         class="w-full rounded-lg shadow-lg object-cover aspect-[3/4]" />
  </div>
  
  <div class="flex-1">
    <h2 class="text-2xl font-bold text-green-800 mb-4"><?= htmlspecialchars($row['fullname']) ?></h2>
    
    <div class="space-y-3 text-gray-700">
      <?php if ($has_position): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-user-tie text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">ตำแหน่ง:</strong>
          <span class="ml-2"><?= htmlspecialchars($row['position_name']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($has_level): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-award text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">วิทยฐานะ:</strong>
          <span class="ml-2"><?= htmlspecialchars($row['level_name']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($has_department): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-building text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">แผนก/ฝ่าย:</strong>
          <span class="ml-2"><?= htmlspecialchars($row['department_name']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($has_education): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-graduation-cap text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">วุฒิการศึกษา:</strong>
          <span class="ml-2">
            <?= htmlspecialchars($row['education_name'] ?? '') ?>
            <?= (!empty($row['education_name']) && !empty($row['education_detail'])) ? ' - ' : '' ?>
            <?= htmlspecialchars($row['education_detail'] ?? '') ?>
          </span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($has_tel): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-phone text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">โทรศัพท์:</strong>
          <div class="ml-2 inline-flex flex-wrap items-center gap-2">
              <a href="tel:<?= htmlspecialchars($row['Tel']) ?>" 
                 class="text-green-700 hover:text-green-900 font-medium underline underline-offset-2">
                 <?= htmlspecialchars($row['Tel']) ?>
              </a>
              <button onclick="copyText('<?= htmlspecialchars($row['Tel']) ?>', this)"
                      class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 px-2 py-1 rounded border border-gray-300 transition-colors"
                      title="คัดลอกเบอร์โทร">
                  <i class="far fa-copy"></i> คัดลอก
              </button>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($has_email): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-envelope text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">อีเมล:</strong>
          <span class="ml-2"><?= htmlspecialchars($row['E_mail']) ?></span>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($has_work): ?>
      <div class="flex items-start gap-3">
        <i class="fas fa-tasks text-green-600 mt-1 flex-shrink-0"></i>
        <div>
          <strong class="text-gray-800">หน้าที่งาน:</strong>
          <div class="ml-2 mt-1 space-y-1">
            <?php foreach($work_items as $work_item): ?>
            <div class="bg-green-50 text-green-800 px-3 py-1 rounded-lg text-sm inline-block mr-2 mb-1">
              <i class="fas fa-check-circle mr-1 text-green-600"></i>
              <?= htmlspecialchars($work_item) ?>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- แสดงข้อความถ้าไม่มีข้อมูลเลย -->
    <?php if (!$has_position && !$has_level && !$has_department && !$has_tel && !$has_email && !$has_work): ?>
    <div class="mt-6 p-4 bg-gray-50 rounded-lg text-center">
      <i class="fas fa-info-circle text-gray-400 text-2xl mb-2"></i>
      <p class="text-gray-500">ยังไม่มีข้อมูลเพิ่มเติม</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
.fade-in {
  animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // เพิ่ม animation ให้กับการแสดงผล
  const items = document.querySelectorAll('.flex.items-start');
  items.forEach((item, index) => {
    item.style.animationDelay = `${index * 0.1}s`;
    item.classList.add('fade-in');
  });
});
</script>