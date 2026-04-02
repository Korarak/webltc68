<?php
// personel_result.php
include 'middleware.php';
ob_start();
include '../condb/condb.php';
?>

<div class="container mx-auto p-4">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 text-white text-center py-8 rounded-xl shadow-lg mb-8">
        <h1 class="text-3xl font-bold mb-2"><i class="fas fa-chart-bar mr-3"></i>สรุปข้อมูลบุคลากร</h1>
        <p class="text-blue-100 text-lg">ภาพรวมข้อมูลบุคลากรทั้งหมดในระบบ</p>
    </div>

    <!-- สรุปข้อมูลเป็นตัวเลข -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php
        // นับจำนวนบุคลากรทั้งหมด
        $sql_total = "SELECT COUNT(*) as total FROM personel_data WHERE is_deleted = 0";
        $result_total = $mysqli3->query($sql_total);
        $total_personnel = $result_total->fetch_assoc()['total'];

        // นับจำนวนบุคลากรที่มีข้อมูลครบ (ปรับปรุงตามแนวทางใหม่)
        $sql_complete = "SELECT COUNT(DISTINCT p.id) as complete 
                        FROM personel_data p
                        LEFT JOIN work_detail wd ON p.id = wd.personel_id
                        WHERE p.is_deleted = 0
                        AND p.position_id IS NOT NULL 
                        AND p.department_id IS NOT NULL 
                        AND p.Tel IS NOT NULL 
                        AND p.E_mail IS NOT NULL
                        AND wd.id IS NOT NULL";
        $result_complete = $mysqli3->query($sql_complete);
        $complete_personnel = $result_complete->fetch_assoc()['complete'];

        // นับจำนวนแผนกที่มีบุคลากร (รวมทั้งสังกัดโดยตรงและผ่านงาน)
        $sql_dept = "SELECT COUNT(DISTINCT d.id) as dept_count 
                    FROM department d
                    WHERE EXISTS (
                        SELECT 1 FROM personel_data p WHERE p.department_id = d.id AND p.is_deleted = 0
                    ) OR EXISTS (
                        SELECT 1 FROM workbranch wb 
                        JOIN work_detail wd ON wb.id = wd.workbranch_id 
                        WHERE wb.department_id = d.id
                    )";
        $result_dept = $mysqli3->query($sql_dept);
        $dept_count = $result_dept->fetch_assoc()['dept_count'];

        // นับจำนวนตำแหน่งงานทั้งหมด
        $sql_pos = "SELECT COUNT(DISTINCT position_id) as pos_count FROM personel_data WHERE is_deleted = 0 AND position_id IS NOT NULL";
        $result_pos = $mysqli3->query($sql_pos);
        $pos_count = $result_pos->fetch_assoc()['pos_count'];

        // นับจำนวนงานย่อยทั้งหมด
        $sql_work = "SELECT COUNT(DISTINCT workbranch_id) as work_count FROM work_detail";
        $result_work = $mysqli3->query($sql_work);
        $work_count = $result_work->fetch_assoc()['work_count'];
        ?>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 text-center shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="text-4xl font-bold mb-2"><?= number_format($total_personnel) ?></div>
            <div class="text-blue-100 font-semibold">บุคลากรทั้งหมด</div>
            <i class="fas fa-users text-2xl mt-3 text-blue-200"></i>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-xl p-6 text-center shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="text-4xl font-bold mb-2"><?= number_format($complete_personnel) ?></div>
            <div class="text-green-100 font-semibold">ข้อมูลครบถ้วน</div>
            <div class="text-green-200 text-sm mt-1"><?= $total_personnel > 0 ? round(($complete_personnel/$total_personnel)*100, 1) : 0 ?>%</div>
            <i class="fas fa-check-circle text-2xl mt-3 text-green-200"></i>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl p-6 text-center shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="text-4xl font-bold mb-2"><?= number_format($dept_count) ?></div>
            <div class="text-purple-100 font-semibold">แผนก/ฝ่าย</div>
            <i class="fas fa-building text-2xl mt-3 text-purple-200"></i>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white rounded-xl p-6 text-center shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="text-4xl font-bold mb-2"><?= number_format($work_count) ?></div>
            <div class="text-orange-100 font-semibold">งานย่อย</div>
            <i class="fas fa-tasks text-2xl mt-3 text-orange-200"></i>
        </div>
    </div>

    <!-- Grid Layout สำหรับกราฟต่างๆ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- สรุปตามเพศ -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-venus-mars text-pink-500 mr-3"></i>
                จำนวนบุคลากรตามเพศ
            </h3>
            <div class="space-y-4">
                <?php
                // คำนวณเพศจากคำนำหน้าชื่อ
                $sql_gender_count = "SELECT 
                    SUM(CASE WHEN fullname LIKE 'นาย%' THEN 1 ELSE 0 END) as male,
                    SUM(CASE WHEN fullname LIKE 'นาง%' OR fullname LIKE 'นางสาว%' THEN 1 ELSE 0 END) as female,
                    SUM(CASE WHEN fullname NOT LIKE 'นาย%' AND fullname NOT LIKE 'นาง%' AND fullname NOT LIKE 'นางสาว%' THEN 1 ELSE 0 END) as unknown
                    FROM personel_data WHERE is_deleted = 0";
                $result_gender = $mysqli3->query($sql_gender_count);
                $gender_data = $result_gender->fetch_assoc();
                
                $genders = [
                    ['name' => 'ชาย', 'count' => $gender_data['male'], 'color' => 'blue', 'icon' => 'mars'],
                    ['name' => 'หญิง', 'count' => $gender_data['female'], 'color' => 'pink', 'icon' => 'venus'],
                    ['name' => 'ไม่ระบุ', 'count' => $gender_data['unknown'], 'color' => 'gray', 'icon' => 'question']
                ];
                
                foreach ($genders as $gender) {
                    $percentage = $total_personnel > 0 ? round(($gender['count'] / $total_personnel) * 100, 1) : 0;
                    $color_class = "bg-{$gender['color']}-500";
                    
                    echo "
                    <div class='flex items-center justify-between'>
                        <div class='flex items-center gap-3'>
                            <i class='fas fa-{$gender['icon']} text-{$gender['color']}-500 text-lg'></i>
                            <span class='font-medium text-gray-700'>{$gender['name']}</span>
                        </div>
                        <div class='flex items-center gap-3'>
                            <span class='font-bold text-gray-800'>{$gender['count']}</span>
                            <span class='text-sm text-gray-500 w-12 text-right'>{$percentage}%</span>
                        </div>
                    </div>
                    <div class='w-full bg-gray-200 rounded-full h-3'>
                        <div class='{$color_class} h-3 rounded-full transition-all duration-1000' style='width: {$percentage}%'></div>
                    </div>
                    ";
                }
                ?>
            </div>
        </div>

        <!-- สรุปตามตำแหน่ง -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-briefcase text-blue-500 mr-3"></i>
                จำนวนบุคลากรตามตำแหน่ง
            </h3>
            <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
                <?php
                $sql_position = "SELECT pos.position_name, COUNT(p.id) as count 
                               FROM personel_data p 
                               LEFT JOIN positions pos ON p.position_id = pos.id 
                               WHERE p.is_deleted = 0
                               GROUP BY p.position_id 
                               ORDER BY count DESC 
                               LIMIT 10";
                $result_position = $mysqli3->query($sql_position);
                
                while ($row = $result_position->fetch_assoc()) {
                    $position_name = $row['position_name'] ?: 'ไม่ระบุตำแหน่ง';
                    $count = $row['count'];
                    $percentage = $total_personnel > 0 ? round(($count / $total_personnel) * 100, 1) : 0;
                    
                    echo "
                    <div class='flex items-center justify-between'>
                        <span class='text-sm font-medium text-gray-700 truncate flex-1' title='{$position_name}'>{$position_name}</span>
                        <div class='flex items-center gap-3 ml-2'>
                            <span class='font-bold text-gray-800'>{$count}</span>
                            <span class='text-sm text-gray-500 w-12 text-right'>{$percentage}%</span>
                        </div>
                    </div>
                    <div class='w-full bg-gray-200 rounded-full h-2 mb-3'>
                        <div class='bg-gradient-to-r from-blue-400 to-blue-600 h-2 rounded-full transition-all duration-1000' style='width: {$percentage}%'></div>
                    </div>
                    ";
                }
                ?>
            </div>
        </div>

        <!-- สรุปตามฝ่ายหลัก -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-sitemap text-green-500 mr-3"></i>
                จำนวนบุคลากรตามฝ่ายหลัก
            </h3>
            <div class="space-y-4">
                <?php
                $sql_main_dept = "SELECT d.department_name, COUNT(DISTINCT p.id) as count 
                                FROM personel_data p 
                                LEFT JOIN department d ON p.department_id = d.id 
                                WHERE p.is_deleted = 0 AND d.department_name LIKE 'ฝ่าย%'
                                GROUP BY p.department_id 
                                ORDER BY count DESC";
                $result_main_dept = $mysqli3->query($sql_main_dept);
                
                while ($row = $result_main_dept->fetch_assoc()) {
                    $dept_name = $row['department_name'] ?: 'ไม่ระบุฝ่าย';
                    $count = $row['count'];
                    $percentage = $total_personnel > 0 ? round(($count / $total_personnel) * 100, 1) : 0;
                    
                    echo "
                    <div class='flex items-center justify-between'>
                        <span class='text-sm font-medium text-gray-700'>{$dept_name}</span>
                        <div class='flex items-center gap-3'>
                            <span class='font-bold text-gray-800'>{$count}</span>
                            <span class='text-sm text-gray-500 w-12 text-right'>{$percentage}%</span>
                        </div>
                    </div>
                    <div class='w-full bg-gray-200 rounded-full h-2 mb-3'>
                        <div class='bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full transition-all duration-1000' style='width: {$percentage}%'></div>
                    </div>
                    ";
                }
                ?>
            </div>
        </div>

        <!-- สรุปตามแผนกวิชา -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-graduation-cap text-purple-500 mr-3"></i>
                จำนวนบุคลากรตามแผนกวิชา
            </h3>
            <div class="space-y-4 max-h-80 overflow-y-auto pr-2">
                <?php
                $sql_academic_dept = "SELECT d.department_name, COUNT(DISTINCT p.id) as count 
                                    FROM personel_data p 
                                    LEFT JOIN department d ON p.department_id = d.id 
                                    WHERE p.is_deleted = 0 AND d.department_name LIKE 'แผนกวิชา%'
                                    GROUP BY p.department_id 
                                    ORDER BY count DESC";
                $result_academic_dept = $mysqli3->query($sql_academic_dept);
                
                while ($row = $result_academic_dept->fetch_assoc()) {
                    $dept_name = $row['department_name'] ?: 'ไม่ระบุแผนก';
                    $count = $row['count'];
                    $percentage = $total_personnel > 0 ? round(($count / $total_personnel) * 100, 1) : 0;
                    
                    echo "
                    <div class='flex items-center justify-between'>
                        <span class='text-sm font-medium text-gray-700 truncate flex-1' title='{$dept_name}'>{$dept_name}</span>
                        <div class='flex items-center gap-3 ml-2'>
                            <span class='font-bold text-gray-800'>{$count}</span>
                            <span class='text-sm text-gray-500 w-12 text-right'>{$percentage}%</span>
                        </div>
                    </div>
                    <div class='w-full bg-gray-200 rounded-full h-2 mb-3'>
                        <div class='bg-gradient-to-r from-purple-400 to-purple-600 h-2 rounded-full transition-all duration-1000' style='width: {$percentage}%'></div>
                    </div>
                    ";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- ข้อมูลงานย่อยและระดับงาน -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- สรุปตามงานย่อย -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-tasks text-orange-500 mr-3"></i>
                จำนวนบุคลากรตามงานย่อย
            </h3>
            <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                <?php
                $sql_workbranch = "SELECT 
                    wb.workbranch_name,
                    d.department_name,
                    COUNT(DISTINCT wd.personel_id) as count 
                    FROM work_detail wd 
                    LEFT JOIN workbranch wb ON wd.workbranch_id = wb.id 
                    LEFT JOIN department d ON wb.department_id = d.id
                    GROUP BY wd.workbranch_id 
                    ORDER BY count DESC 
                    LIMIT 15";
                $result_workbranch = $mysqli3->query($sql_workbranch);
                
                while ($row = $result_workbranch->fetch_assoc()) {
                    $workbranch_name = $row['workbranch_name'] ?: 'ไม่ระบุงาน';
                    $department_name = $row['department_name'] ?: 'ไม่ระบุฝ่าย';
                    $count = $row['count'];
                    
                    echo "
                    <div class='border-l-4 border-orange-500 pl-4 py-3 hover:bg-orange-50 rounded-r transition-colors'>
                        <div class='flex justify-between items-start mb-1'>
                            <span class='font-medium text-gray-800 text-sm'>{$workbranch_name}</span>
                            <span class='bg-orange-100 text-orange-800 text-xs px-2 py-1 rounded-full font-bold'>{$count}</span>
                        </div>
                        <div class='text-xs text-gray-600 flex items-center gap-1'>
                            <i class='fas fa-building'></i>
                            {$department_name}
                        </div>
                    </div>
                    ";
                }
                ?>
            </div>
        </div>

        <!-- สรุปตามระดับงาน -->
        <div class="bg-white shadow-xl rounded-xl p-6 border border-gray-100">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 flex items-center">
                <i class="fas fa-layer-group text-indigo-500 mr-3"></i>
                จำนวนบุคลากรตามระดับงาน
            </h3>
            <div class="space-y-4">
                <?php
                $sql_worklevel = "SELECT 
                    wl.work_level_name,
                    COUNT(DISTINCT wd.personel_id) as count 
                    FROM work_detail wd 
                    LEFT JOIN worklevel wl ON wd.worklevel_id = wl.id 
                    GROUP BY wd.worklevel_id 
                    ORDER BY count DESC";
                $result_worklevel = $mysqli3->query($sql_worklevel);
                
                while ($row = $result_worklevel->fetch_assoc()) {
                    $worklevel_name = $row['work_level_name'] ?: 'ไม่ระบุระดับ';
                    $count = $row['count'];
                    $percentage = $total_personnel > 0 ? round(($count / $total_personnel) * 100, 1) : 0;
                    
                    echo "
                    <div class='flex items-center justify-between'>
                        <span class='text-sm font-medium text-gray-700'>{$worklevel_name}</span>
                        <div class='flex items-center gap-3'>
                            <span class='font-bold text-gray-800'>{$count}</span>
                            <span class='text-sm text-gray-500 w-12 text-right'>{$percentage}%</span>
                        </div>
                    </div>
                    <div class='w-full bg-gray-200 rounded-full h-2 mb-3'>
                        <div class='bg-gradient-to-r from-indigo-400 to-indigo-600 h-2 rounded-full transition-all duration-1000' style='width: {$percentage}%'></div>
                    </div>
                    ";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- ข้อมูลที่ยังขาด -->
    <div class="bg-white shadow-xl rounded-xl p-6 border border-red-100 mb-8">
        <h3 class="text-xl font-semibold mb-6 text-gray-800 flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
            ข้อมูลที่ยังขาด
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            // นับข้อมูลที่ขาด (ปรับปรุงตามแนวทางใหม่)
            $sql_missing_position = "SELECT COUNT(*) as count FROM personel_data WHERE is_deleted = 0 AND position_id IS NULL";
            $result_missing_position = $mysqli3->query($sql_missing_position);
            $missing_position = $result_missing_position->fetch_assoc()['count'];

            $sql_missing_dept = "SELECT COUNT(*) as count FROM personel_data WHERE is_deleted = 0 AND department_id IS NULL";
            $result_missing_dept = $mysqli3->query($sql_missing_dept);
            $missing_dept = $result_missing_dept->fetch_assoc()['count'];

            $sql_missing_contact = "SELECT COUNT(*) as count FROM personel_data WHERE is_deleted = 0 AND (Tel IS NULL OR E_mail IS NULL)";
            $result_missing_contact = $mysqli3->query($sql_missing_contact);
            $missing_contact = $result_missing_contact->fetch_assoc()['count'];

            $sql_missing_work = "SELECT COUNT(*) as count FROM personel_data p 
                               LEFT JOIN work_detail wd ON p.id = wd.personel_id 
                               WHERE p.is_deleted = 0 AND wd.id IS NULL";
            $result_missing_work = $mysqli3->query($sql_missing_work);
            $missing_work = $result_missing_work->fetch_assoc()['count'];

            $missing_data = [
                ['name' => 'ตำแหน่งงาน', 'count' => $missing_position, 'color' => 'red', 'icon' => 'briefcase'],
                ['name' => 'แผนก/ฝ่าย', 'count' => $missing_dept, 'color' => 'orange', 'icon' => 'building'],
                ['name' => 'ข้อมูลติดต่อ', 'count' => $missing_contact, 'color' => 'yellow', 'icon' => 'phone'],
                ['name' => 'งานที่รับผิดชอบ', 'count' => $missing_work, 'color' => 'blue', 'icon' => 'tasks']
            ];

            foreach ($missing_data as $data) {
                $percentage = $total_personnel > 0 ? round(($data['count'] / $total_personnel) * 100, 1) : 0;
                
                echo "
                <div class='text-center p-4 border-2 border-{$data['color']}-200 rounded-xl bg-{$data['color']}-50 hover:bg-{$data['color']}-100 transition-colors'>
                    <i class='fas fa-{$data['icon']} text-{$data['color']}-500 text-2xl mb-2'></i>
                    <div class='text-2xl font-bold text-{$data['color']}-600'>{$data['count']}</div>
                    <div class='text-sm font-semibold text-gray-700 mb-1'>{$data['name']}</div>
                    <div class='text-xs text-gray-500'>{$percentage}% ของทั้งหมด</div>
                </div>
                ";
            }
            ?>
        </div>
    </div>

    <!-- ปุ่มดำเนินการ -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
        <a href="personel_manage.php" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all duration-300 transform hover:scale-105 flex items-center gap-2">
            <i class="fas fa-cog"></i>
            จัดการข้อมูลบุคลากร
        </a>
        <a href="personel_add.php" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all duration-300 transform hover:scale-105 flex items-center gap-2">
            <i class="fas fa-user-plus"></i>
            เพิ่มบุคลากรใหม่
        </a>
        <button onclick="window.print()" class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all duration-300 transform hover:scale-105 flex items-center gap-2">
            <i class="fas fa-print"></i>
            พิมพ์รายงาน
        </button>
    </div>
</div>

<script>
// Animation for progress bars
document.addEventListener('DOMContentLoaded', function() {
    const progressBars = document.querySelectorAll('[style*="width"]');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 100);
    });
});

// Add some interactive features
document.querySelectorAll('.hover\\:scale-105').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'scale(1.05)';
    });
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'scale(1)';
    });
});
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    .bg-gradient-to-r {
        background: #3b82f6 !important;
        color: white !important;
    }
    
    .shadow-lg, .shadow-xl {
        box-shadow: none !important;
    }
}
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>