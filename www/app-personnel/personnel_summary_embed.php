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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .summary-embed {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 100%;
            background: white;
            color: #333;
            padding: 1.5rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .embed-header {
            text-align: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .embed-header h2 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: #1e40af;
        }
        
        .embed-header p {
            color: #64748b;
            font-size: 0.9rem;
        }
        
        /* สถิติหลัก */
        .main-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.2rem;
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card.total { border-top: 4px solid #3b82f6; }
        .stat-card.complete { border-top: 4px solid #10b981; }
        .stat-card.departments { border-top: 4px solid #8b5cf6; }
        .stat-card.works { border-top: 4px solid #f59e0b; }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }
        
        .stat-percentage {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 0.3rem;
        }
        
        /* กริดสถิติ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.2rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e2e8f0;
            color: #1e293b;
        }
        
        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .section-icon {
            font-size: 1.1rem;
        }
        
        .stats-list {
            list-style: none;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .stat-item:last-child {
            border-bottom: none;
        }
        
        .stat-name {
            flex: 1;
            font-size: 0.85rem;
            color: #374151;
        }
        
        .stat-count {
            font-weight: bold;
            color: #1e40af;
            min-width: 40px;
            text-align: right;
        }
        
        .stat-percent-bar {
            width: 60px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin-left: 0.5rem;
        }
        
        .stat-percent-fill {
            height: 100%;
            background: #3b82f6;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        
        /* ข้อมูลที่ขาด */
        .missing-data {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 1.2rem;
            margin-bottom: 1.5rem;
        }
        
        .missing-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: #dc2626;
            font-weight: 600;
        }
        
        .missing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .missing-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #fecaca;
        }
        
        .missing-count {
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 0.3rem;
        }
        
        .missing-label {
            font-size: 0.8rem;
            color: #7f1d1d;
        }
        
        .missing-percentage {
            font-size: 0.7rem;
            color: #b91c1c;
            margin-top: 0.2rem;
        }
        
        /* Progress bars */
        .progress-container {
            margin-top: 0.5rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        .progress-male { background: #3b82f6; }
        .progress-female { background: #ec4899; }
        .progress-unknown { background: #6b7280; }
        
        .empty-state {
            text-align: center;
            color: #9ca3af;
            font-style: italic;
            padding: 1rem;
            font-size: 0.85rem;
        }
        
        .update-time {
            text-align: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .data-source {
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .main-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .missing-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .summary-embed {
                padding: 1rem;
            }
            
            .stat-section {
                padding: 1rem;
            }
            
            .main-stats {
                grid-template-columns: 1fr;
            }
            
            .missing-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="summary-embed">
        <div class="embed-header">
            <h2>📊 สรุปข้อมูลบุคลากร - ภาพรวมระบบ</h2>
            <p>สถิติและข้อมูลสรุปบุคลากรทั้งหมดในระบบ</p>
        </div>
        
        <!-- สถิติหลัก -->
        <div class="main-stats">
            <div class="stat-card total">
                <div class="stat-number"><?= number_format($total_personnel) ?></div>
                <div class="stat-label">บุคลากรทั้งหมด</div>
            </div>
            
            <div class="stat-card complete">
                <div class="stat-number"><?= number_format($complete_count) ?></div>
                <div class="stat-label">ข้อมูลครบถ้วน</div>
                <div class="stat-percentage"><?= calculatePercentage($complete_count, $total_personnel) ?>%</div>
            </div>
            
            <div class="stat-card departments">
                <div class="stat-number"><?= number_format($dept_count) ?></div>
                <div class="stat-label">แผนก/ฝ่าย</div>
            </div>
            
            <div class="stat-card works">
                <div class="stat-number"><?= number_format($work_count) ?></div>
                <div class="stat-label">งานย่อย</div>
            </div>
        </div>
        
        <div class="stats-grid">
            <!-- เพศ -->
            <div class="stat-section">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-icon">👥</span>
                        <span>จำนวนบุคลากรตามเพศ</span>
                    </div>
                </div>
                <?php if (!empty($gender_stats)): ?>
                <ul class="stats-list">
                    <?php foreach($gender_stats as $gender): ?>
                    <li class="stat-item">
                        <div class="stat-name"><?= @htmlspecialchars($gender['gender_name']) ?></div>
                        <div class="stat-count"><?= @number_format($gender['count']) ?></div>
                        <div class="stat-percent-bar">
                            <div class="stat-percent-fill" style="width: <?= calculatePercentage($gender['count'], $total_personnel) ?>%"></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">ไม่มีข้อมูลเพศ</div>
                <?php endif; ?>
            </div>
            
            <!-- ตำแหน่ง (Top 6) -->
            <div class="stat-section">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-icon">💼</span>
                        <span>ตำแหน่งงาน</span>
                    </div>
                </div>
                <?php if (!empty($position_stats)): ?>
                <ul class="stats-list">
                    <?php foreach($position_stats as $position): ?>
                    <li class="stat-item">
                        <div class="stat-name"><?= htmlspecialchars($position['position_name']) ?></div>
                        <div class="stat-count"><?= number_format($position['count']) ?></div>
                        <div class="stat-percent-bar">
                            <div class="stat-percent-fill" style="width: <?= calculatePercentage($position['count'], $total_personnel) ?>%"></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">ไม่มีข้อมูลตำแหน่ง</div>
                <?php endif; ?>
            </div>
            
            <!-- ฝ่ายบริหาร -->
            <div class="stat-section">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-icon">🏢</span>
                        <span>ฝ่ายบริหาร</span>
                    </div>
                </div>
                <?php if (!empty($admin_dept_stats)): ?>
                <ul class="stats-list">
                    <?php foreach($admin_dept_stats as $dept): ?>
                    <li class="stat-item">
                        <div class="stat-name"><?= htmlspecialchars($dept['department_name']) ?></div>
                        <div class="stat-count"><?= number_format($dept['count']) ?></div>
                        <div class="stat-percent-bar">
                            <div class="stat-percent-fill" style="width: <?= calculatePercentage($dept['count'], $total_personnel) ?>%"></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">ไม่มีข้อมูลฝ่ายบริหาร</div>
                <?php endif; ?>
            </div>
            
            <!-- แผนกวิชาการ (Top 6) -->
            <div class="stat-section">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-icon">📚</span>
                        <span>แผนกวิชาการ</span>
                    </div>
                </div>
                <?php if (!empty($academic_dept_stats)): ?>
                <ul class="stats-list">
                    <?php foreach($academic_dept_stats as $dept): ?>
                    <li class="stat-item">
                        <div class="stat-name"><?= htmlspecialchars($dept['department_name']) ?></div>
                        <div class="stat-count"><?= number_format($dept['count']) ?></div>
                        <div class="stat-percent-bar">
                            <div class="stat-percent-fill" style="width: <?= calculatePercentage($dept['count'], $total_personnel) ?>%"></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">ไม่มีข้อมูลแผนกวิชาการ</div>
                <?php endif; ?>
            </div>
            
            <!-- งานย่อย (Top 10) -->
            <div class="stat-section">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-icon">📋</span>
                        <span>งานย่อย</span>
                    </div>
                </div>
                <?php if (!empty($workbranch_stats)): ?>
                <ul class="stats-list">
                    <?php foreach($workbranch_stats as $work): ?>
                    <li class="stat-item">
                        <div class="stat-name" title="<?= htmlspecialchars($work['workbranch_name']) ?>">
                            <?= htmlspecialchars(mb_strlen($work['workbranch_name']) > 20 ? mb_substr($work['workbranch_name'], 0, 20).'...' : $work['workbranch_name']) ?>
                            <div style="font-size: 0.7rem; color: #64748b;"><?= htmlspecialchars($work['department_name']) ?></div>
                        </div>
                        <div class="stat-count"><?= number_format($work['count']) ?></div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">ไม่มีข้อมูลงานย่อย</div>
                <?php endif; ?>
            </div>
            
            <!-- ระดับงาน -->
            <div class="stat-section">
                <div class="section-header">
                    <div class="section-title">
                        <span class="section-icon">📊</span>
                        <span>ระดับงาน</span>
                    </div>
                </div>
                <?php if (!empty($worklevel_stats)): ?>
                <ul class="stats-list">
                    <?php foreach($worklevel_stats as $level): ?>
                    <li class="stat-item">
                        <div class="stat-name"><?= htmlspecialchars($level['work_level_name']) ?></div>
                        <div class="stat-count"><?= number_format($level['count']) ?></div>
                        <div class="stat-percent-bar">
                            <div class="stat-percent-fill" style="width: <?= calculatePercentage($level['count'], $total_personnel) ?>%"></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="empty-state">ไม่มีข้อมูลระดับงาน</div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- ข้อมูลที่ยังขาด -->
        <div class="missing-data">
            <div class="missing-header">
                <span>⚠️</span>
                <span>ข้อมูลที่ยังขาด</span>
            </div>
            <div class="missing-grid">
                <div class="missing-item">
                    <div class="missing-count"><?= number_format($missing_position) ?></div>
                    <div class="missing-label">ตำแหน่งงาน</div>
                    <div class="missing-percentage"><?= calculatePercentage($missing_position, $total_personnel) ?>%</div>
                </div>
                <div class="missing-item">
                    <div class="missing-count"><?= number_format($missing_dept) ?></div>
                    <div class="missing-label">แผนก/ฝ่าย</div>
                    <div class="missing-percentage"><?= calculatePercentage($missing_dept, $total_personnel) ?>%</div>
                </div>
                <div class="missing-item">
                    <div class="missing-count"><?= number_format($missing_contact) ?></div>
                    <div class="missing-label">ข้อมูลติดต่อ</div>
                    <div class="missing-percentage"><?= calculatePercentage($missing_contact, $total_personnel) ?>%</div>
                </div>
                <div class="missing-item">
                    <div class="missing-count"><?= number_format($missing_work) ?></div>
                    <div class="missing-label">งานที่รับผิดชอบ</div>
                    <div class="missing-percentage"><?= calculatePercentage($missing_work, $total_personnel) ?>%</div>
                </div>
            </div>
        </div>
        
        <div class="data-source">
            📝 ข้อมูลอ้างอิงจากตาราง personel_data และตารางที่เกี่ยวข้อง
        </div>
        
        <div class="update-time">
            อัปเดต: <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <script>
        // Add hover effects
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            const statSections = document.querySelectorAll('.stat-section');
            
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            statSections.forEach(section => {
                section.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                section.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Animate progress bars
            const progressBars = document.querySelectorAll('.stat-percent-fill');
            progressBars.forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100);
            });
        });
        
        // Auto-refresh every 10 minutes
        setTimeout(() => {
            window.location.reload();
        }, 600000);
    </script>
</body>
</html>