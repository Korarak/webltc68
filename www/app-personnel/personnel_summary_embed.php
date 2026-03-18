<?php
// personnel_summary_embed.php
header('Content-Type: text/html; charset=utf-8');
require '../condb/condb.php';

// ฟังก์ชันคำนวณเปอร์เซ็นต์
function calculatePercentage($part, $total) {
    if ($total == 0) return 0;
    return round(($part / $total) * 100, 1);
}

// สถิติทั้งหมด
$sql_total = "SELECT COUNT(*) as total FROM personel_data";
$result_total = $mysqli3->query($sql_total);
$total_personnel = $result_total->fetch_assoc()['total'];

// สถิติตามเพศจากตาราง gender
$sql_gender = "SELECT 
    g.gender_name,
    COUNT(p.id) as count
    FROM personel_data p
    LEFT JOIN gender g ON p.gender_id = g.id
    GROUP BY p.gender_id, g.gender_name
    ORDER BY count DESC";
$result_gender = $mysqli3->query($sql_gender);
$gender_stats = [];
while ($row = $result_gender->fetch_assoc()) {
    $gender_stats[] = $row;
}

// สถิติตามตำแหน่ง (Top 6)
$sql_positions = "SELECT 
    pos.position_name,
    COUNT(p.id) as count
    FROM personel_data p
    LEFT JOIN positions pos ON p.position_id = pos.id
    WHERE p.position_id IS NOT NULL
    GROUP BY p.position_id, pos.position_name
    ORDER BY count DESC
    LIMIT 6";
$result_positions = $mysqli3->query($sql_positions);
$position_stats = [];
while ($row = $result_positions->fetch_assoc()) {
    $position_stats[] = $row;
}

// สถิติฝ่ายบริหาร (เฉพาะฝ่าย)
$sql_admin_dept = "SELECT 
    d.department_name,
    COUNT(p.id) as count
    FROM personel_data p
    LEFT JOIN department d ON p.department_id = d.id
    WHERE d.department_name LIKE 'ฝ่าย%'
    GROUP BY p.department_id, d.department_name
    ORDER BY count DESC";
$result_admin_dept = $mysqli3->query($sql_admin_dept);
$admin_dept_stats = [];
while ($row = $result_admin_dept->fetch_assoc()) {
    $admin_dept_stats[] = $row;
}

// สถิติแผนกวิชาการ (เฉพาะแผนก) - Top 6
$sql_academic_dept = "SELECT 
    d.department_name,
    COUNT(p.id) as count
    FROM personel_data p
    LEFT JOIN department d ON p.department_id = d.id
    WHERE d.department_name LIKE 'แผนกวิชา%'
    GROUP BY p.department_id, d.department_name
    ORDER BY count DESC
    LIMIT 6";
$result_academic_dept = $mysqli3->query($sql_academic_dept);
$academic_dept_stats = [];
while ($row = $result_academic_dept->fetch_assoc()) {
    $academic_dept_stats[] = $row;
}

// สถิติงานย่อย (Top 10)
$sql_workbranch = "SELECT 
    wb.workbranch_name,
    d.department_name,
    COUNT(DISTINCT wd.personel_id) as count
    FROM work_detail wd
    JOIN workbranch wb ON wd.workbranch_id = wb.id
    JOIN department d ON wb.department_id = d.id
    GROUP BY wd.workbranch_id, wb.workbranch_name, d.department_name
    ORDER BY count DESC
    LIMIT 10";
$result_workbranch = $mysqli3->query($sql_workbranch);
$workbranch_stats = [];
while ($row = $result_workbranch->fetch_assoc()) {
    $workbranch_stats[] = $row;
}

// สถิติระดับงาน
$sql_worklevel = "SELECT 
    wl.work_level_name,
    COUNT(DISTINCT wd.personel_id) as count
    FROM work_detail wd
    JOIN worklevel wl ON wd.worklevel_id = wl.id
    GROUP BY wd.worklevel_id, wl.work_level_name
    ORDER BY count DESC";
$result_worklevel = $mysqli3->query($sql_worklevel);
$worklevel_stats = [];
while ($row = $result_worklevel->fetch_assoc()) {
    $worklevel_stats[] = $row;
}

// ข้อมูลที่ยังขาด
$sql_missing_position = "SELECT COUNT(*) as count FROM personel_data WHERE position_id IS NULL";
$result_missing_position = $mysqli3->query($sql_missing_position);
$missing_position = $result_missing_position->fetch_assoc()['count'];

$sql_missing_dept = "SELECT COUNT(*) as count FROM personel_data WHERE department_id IS NULL";
$result_missing_dept = $mysqli3->query($sql_missing_dept);
$missing_dept = $result_missing_dept->fetch_assoc()['count'];

$sql_missing_contact = "SELECT COUNT(*) as count FROM personel_data WHERE Tel IS NULL OR E_mail IS NULL";
$result_missing_contact = $mysqli3->query($sql_missing_contact);
$missing_contact = $result_missing_contact->fetch_assoc()['count'];

$sql_missing_work = "SELECT COUNT(*) as count FROM personel_data p 
                   LEFT JOIN work_detail wd ON p.id = wd.personel_id 
                   WHERE wd.id IS NULL";
$result_missing_work = $mysqli3->query($sql_missing_work);
$missing_work = $result_missing_work->fetch_assoc()['count'];

// นับจำนวนแผนก
$sql_dept_count = "SELECT COUNT(DISTINCT d.id) as count 
                  FROM department d
                  WHERE EXISTS (
                      SELECT 1 FROM personel_data p WHERE p.department_id = d.id
                  ) OR EXISTS (
                      SELECT 1 FROM workbranch wb 
                      JOIN work_detail wd ON wb.id = wd.workbranch_id 
                      WHERE wb.department_id = d.id
                  )";
$result_dept_count = $mysqli3->query($sql_dept_count);
$dept_count = $result_dept_count->fetch_assoc()['count'];

// นับจำนวนงานย่อย
$sql_work_count = "SELECT COUNT(DISTINCT workbranch_id) as count FROM work_detail";
$result_work_count = $mysqli3->query($sql_work_count);
$work_count = $result_work_count->fetch_assoc()['count'];

// นับข้อมูลครบถ้วน (ตามแนวทางใหม่)
$sql_complete = "SELECT COUNT(DISTINCT p.id) as count 
                FROM personel_data p
                LEFT JOIN work_detail wd ON p.id = wd.personel_id
                WHERE p.position_id IS NOT NULL 
                AND p.department_id IS NOT NULL 
                AND p.Tel IS NOT NULL 
                AND p.E_mail IS NOT NULL
                AND wd.id IS NOT NULL";
$result_complete = $mysqli3->query($sql_complete);
$complete_count = $result_complete->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปข้อมูลบุคลากร - ภาพรวมระบบ</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Kanit', 'sans-serif'],
                    },
                    colors: {
                        primary: '#1e40af',
                        secondary: '#3b82f6',
                        accent: '#8b5cf6',
                        success: '#10b981',
                        warning: '#f59e0b',
                        danger: '#ef4444',
                        surface: '#ffffff',
                        background: '#f8fafc',
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: transparent; /* For iframe embedding */
        }
        
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border-radius: 1rem;
        }
        
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
        }
        
        .card-total::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .card-complete::before { background: linear-gradient(90deg, #10b981, #34d399); }
        .card-dept::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
        .card-work::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        
        .gradient-text {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .progress-bar {
            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .list-container {
            max-height: 280px;
            overflow-y: auto;
        }

        .list-container::-webkit-scrollbar {
            width: 4px;
        }
        .list-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .list-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(10px);
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="p-2 sm:p-4 text-slate-800 antialiased">
    
    <div class="glass-panel p-4 sm:p-6 md:p-8 animate-fade-in">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 border-b border-slate-200 pb-4">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h2 class="text-2xl md:text-3xl font-bold gradient-text flex items-center justify-center md:justify-start gap-3">
                    <i class="fa-solid fa-chart-pie"></i>
                    ภาพรวมข้อมูลบุคลากร
                </h2>
                <p class="text-slate-500 mt-1 text-sm md:text-base">ระบบสารสนเทศเพื่อการบริหารจัดการ</p>
            </div>
            <div class="bg-slate-50 px-4 py-2 rounded-lg border border-slate-100 flex items-center gap-2 shadow-sm">
                <i class="fa-regular fa-clock text-slate-400"></i>
                <div class="text-xs text-slate-600">
                    <span class="block text-slate-400 uppercase tracking-wider" style="font-size: 0.65rem;">อัปเดตล่าสุด</span>
                    <span class="font-medium"><?= date('d/m/Y H:i') ?></span>
                </div>
            </div>
        </div>
        
        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
            <!-- Total Personnel -->
            <div class="glass-panel stat-card card-total p-5 flex items-center gap-4" style="animation-delay: 0.1s;">
                <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">บุคลากรทั้งหมด</h3>
                    <div class="text-2xl font-bold text-slate-800"><?= number_format($total_personnel) ?> <span class="text-sm font-normal text-slate-400">คน</span></div>
                </div>
            </div>
            
            <!-- Complete Data -->
            <div class="glass-panel stat-card card-complete p-5 flex items-center gap-4" style="animation-delay: 0.2s;">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-shield-check"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">ข้อมูลครบถ้วน</h3>
                    <div class="flex items-baseline gap-2">
                        <div class="text-2xl font-bold text-slate-800"><?= number_format($complete_count) ?></div>
                        <div class="text-xs font-medium text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <?= calculatePercentage($complete_count, $total_personnel) ?>%
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Departments -->
            <div class="glass-panel stat-card card-dept p-5 flex items-center gap-4" style="animation-delay: 0.3s;">
                <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-sitemap"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">แผนก/ฝ่ายบริหาร</h3>
                    <div class="text-2xl font-bold text-slate-800"><?= number_format($dept_count) ?> <span class="text-sm font-normal text-slate-400">หน่วย</span></div>
                </div>
            </div>
            
            <!-- Work Branches -->
            <div class="glass-panel stat-card card-work p-5 flex items-center gap-4" style="animation-delay: 0.4s;">
                <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-slate-500">งานย่อยในระบบ</h3>
                    <div class="text-2xl font-bold text-slate-800"><?= number_format($work_count) ?> <span class="text-sm font-normal text-slate-400">งาน</span></div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Grids -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Column 1: Gender & Needs Action -->
            <div class="flex flex-col gap-6 lg:col-span-1">
                
                <!-- Gender Stats -->
                <div class="glass-panel p-5 animate-fade-in" style="animation-delay: 0.5s;">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                        <div class="w-8 h-8 rounded bg-indigo-50 text-indigo-500 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-venus-mars"></i>
                        </div>
                        สัดส่วนเพศ
                    </h3>
                    
                    <?php if (!empty($gender_stats)): ?>
                    <div class="space-y-4">
                        <?php 
                        $colors = ['text-blue-500 bg-blue-500', 'text-pink-500 bg-pink-500', 'text-slate-500 bg-slate-500'];
                        foreach($gender_stats as $key => $gender): 
                            $percent = calculatePercentage($gender['count'], $total_personnel);
                            $colorClass = $colors[$key % count($colors)] ?? 'text-indigo-500 bg-indigo-500';
                            $textClass = explode(' ', $colorClass)[0];
                            $bgClass = explode(' ', $colorClass)[1];
                        ?>
                        <div class="group">
                            <div class="flex justify-between items-end mb-1">
                                <span class="text-sm font-medium text-slate-700"><?= htmlspecialchars($gender['gender_name'] ?? 'ไม่ระบุ') ?></span>
                                <div class="text-right">
                                    <span class="text-sm font-bold text-slate-800"><?= number_format($gender['count']) ?></span>
                                    <span class="text-xs text-slate-500 ml-1">(<?= $percent ?>%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="progress-bar <?= $bgClass ?> h-full rounded-full" style="width: 0%" data-width="<?= $percent ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Chart optional -->
                    <div class="mt-6 flex justify-center">
                         <div class="relative w-32 h-32">
                             <canvas id="genderChart"></canvas>
                         </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-6 text-slate-400 text-sm">ไม่มีข้อมูล</div>
                    <?php endif; ?>
                </div>

                <!-- Missing Data Warning -->
                <div class="bg-red-50 border border-red-100 rounded-xl p-5 animate-fade-in" style="animation-delay: 0.6s;">
                    <h3 class="text-md font-semibold text-red-700 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        ข้อมูลที่ต้องติดตาม (ไม่ครบถ้วน)
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white p-3 rounded-lg border border-red-50 text-center shadow-sm">
                            <div class="text-xl font-bold text-red-600 mb-1"><?= number_format($missing_position) ?></div>
                            <div class="text-xs text-slate-600">ขาดตำแหน่ง</div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-red-50 text-center shadow-sm">
                            <div class="text-xl font-bold text-red-600 mb-1"><?= number_format($missing_dept) ?></div>
                            <div class="text-xs text-slate-600">ขาดแผนก/ฝ่าย</div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-red-50 text-center shadow-sm">
                            <div class="text-xl font-bold text-red-600 mb-1"><?= number_format($missing_contact) ?></div>
                            <div class="text-xs text-slate-600">เบอร์/อีเมล</div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-red-50 text-center shadow-sm">
                            <div class="text-xl font-bold text-red-600 mb-1"><?= number_format($missing_work) ?></div>
                            <div class="text-xs text-slate-600">ขาดภาระงาน</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Column 2: Positions & Work Levels -->
            <div class="flex flex-col gap-6 lg:col-span-1">
                
                <!-- Positions -->
                <div class="glass-panel p-5 h-full animate-fade-in" style="animation-delay: 0.7s;">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded bg-sky-50 text-sky-500 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-id-badge"></i>
                            </div>
                            ตำแหน่งงาน (สูงสุด 6 อันดับ)
                        </h3>
                    </div>
                    
                    <?php if (!empty($position_stats)): ?>
                    <div class="list-container pr-2 space-y-3">
                        <?php foreach($position_stats as $position): 
                            $percent = calculatePercentage($position['count'], $total_personnel);
                        ?>
                        <div class="bg-slate-50 border border-slate-100 p-3 rounded-lg hover:bg-sky-50 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-medium text-sm text-slate-700 line-clamp-1 flex-1 pr-2" title="<?= htmlspecialchars($position['position_name']) ?>">
                                    <?= htmlspecialchars($position['position_name']) ?>
                                </div>
                                <div class="bg-sky-100 text-sky-700 px-2 py-0.5 rounded text-xs font-bold">
                                    <?= number_format($position['count']) ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                    <div class="progress-bar bg-sky-500 h-full rounded-full" style="width: 0%" data-width="<?= $percent ?>%"></div>
                                </div>
                                <span class="text-[0.65rem] text-slate-500 font-medium w-8 text-right"><?= $percent ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-10 text-slate-400 text-sm">ไม่มีข้อมูลตำแหน่ง</div>
                    <?php endif; ?>
                </div>

                <!-- Work Levels -->
                <div class="glass-panel p-5 animate-fade-in" style="animation-delay: 0.8s;">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded bg-teal-50 text-teal-500 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            ระดับงาน
                        </h3>
                    </div>
                    
                    <?php if (!empty($worklevel_stats)): ?>
                    <div class="space-y-3">
                        <?php foreach($worklevel_stats as $level): 
                            $percent = calculatePercentage($level['count'], $total_personnel);
                        ?>
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-teal-400"></div>
                                <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors"><?= htmlspecialchars($level['work_level_name']) ?></span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-slate-800"><?= number_format($level['count']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-6 text-slate-400 text-sm">ไม่มีข้อมูลระดับงาน</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Column 3: Departments & Work Branches -->
            <div class="flex flex-col gap-6 lg:col-span-1">
                
                <!-- Academic Departments -->
                <div class="glass-panel p-5 animate-fade-in" style="animation-delay: 0.9s;">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded bg-fuchsia-50 text-fuchsia-500 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            หมวด/แผนกวิชา
                        </h3>
                    </div>
                    
                    <?php if (!empty($academic_dept_stats)): ?>
                    <div class="space-y-3">
                        <?php foreach($academic_dept_stats as $dept): ?>
                        <div class="flex items-start gap-3">
                            <div class="mt-1 w-1.5 h-1.5 rounded-full bg-fuchsia-400 shrink-0"></div>
                            <div class="flex-1">
                                <div class="flex justify-between items-baseline">
                                    <div class="text-sm text-slate-700 font-medium line-clamp-1" title="<?= htmlspecialchars($dept['department_name']) ?>">
                                        <?= htmlspecialchars($dept['department_name']) ?>
                                    </div>
                                    <div class="text-sm font-bold text-fuchsia-600 ml-2 py-0.5 px-2 bg-fuchsia-50 rounded">
                                        <?= number_format($dept['count']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-6 text-slate-400 text-sm">ไม่มีข้อมูลแผนกวิชาการ</div>
                    <?php endif; ?>
                </div>

                <!-- Work Branches (Top 5) -->
                <div class="glass-panel p-5 flex-1 animate-fade-in" style="animation-delay: 1.0s;">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-2">
                        <h3 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                            <div class="w-8 h-8 rounded bg-orange-50 text-orange-500 flex items-center justify-center text-sm">
                                <i class="fa-solid fa-clipboard-list"></i>
                            </div>
                            งานย่อยที่มีบุคลากรมากสุด
                        </h3>
                    </div>
                    
                    <?php if (!empty($workbranch_stats)): ?>
                    <div class="space-y-3 list-container pr-2">
                        <?php foreach(array_slice($workbranch_stats, 0, 5) as $work): ?>
                        <div class="border-l-2 border-orange-400 pl-3 py-1 hover:bg-orange-50 rounded-r transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-sm font-medium text-slate-800 line-clamp-1">
                                        <?= htmlspecialchars($work['workbranch_name']) ?>
                                    </div>
                                    <div class="text-[0.7rem] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i class="fa-regular fa-building text-[0.6rem]"></i>
                                        <span class="line-clamp-1"><?= htmlspecialchars($work['department_name']) ?></span>
                                    </div>
                                </div>
                                <div class="bg-white border border-slate-200 px-2 py-1 rounded text-xs font-bold text-orange-600 ml-2 shadow-sm shrink-0">
                                    <?= number_format($work['count']) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-10 text-slate-400 text-sm">ไม่มีข้อมูลงานย่อย</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate progress bars
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(bar => {
                    bar.style.width = bar.getAttribute('data-width');
                });
            }, 300);

            // Chart.js Gender Donut Chart (if data exists)
            <?php if (!empty($gender_stats)): ?>
            const genderCtx = document.getElementById('genderChart');
            if (genderCtx) {
                const data = {
                    labels: [
                        <?php foreach($gender_stats as $g) echo "'" . htmlspecialchars($g['gender_name'] ?? 'ไม่ระบุ') . "', "; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach($gender_stats as $g) echo $g['count'] . ", "; ?>
                        ],
                        backgroundColor: [
                            '#3b82f6', // blue
                            '#ec4899', // pink
                            '#64748b'  // slate
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                };
                
                new Chart(genderCtx, {
                    type: 'doughnut',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                titleFont: { family: 'Kanit' },
                                bodyFont: { family: 'Kanit' },
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += context.parsed + ' คน';
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            <?php endif; ?>
        });
        
        // Auto-refresh every 10 minutes
        setTimeout(() => {
            window.location.reload();
        }, 600000);
    </script>
</body>
</html>