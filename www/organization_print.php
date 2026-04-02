<?php
$title = "ผังโครงสร้างผู้บริหาร - วิทยาลัยเทคนิคเลย";
require 'condb/condb.php';

// --- Query ผู้อำนวยการ ---
$directorQuery = "SELECT p.id, p.fullname, p.Tel, p.E_mail, pos.position_name
                  FROM personel_data p
                  JOIN positions pos ON p.position_id = pos.id
                  WHERE p.is_deleted = 0 AND pos.position_name = 'ผู้อำนวยการ'
                  LIMIT 1";
$directorResult = $mysqli3->query($directorQuery);
$director = $directorResult->fetch_assoc();

// --- Query รองผู้อำนวยการ + department ---
$viceQuery = "SELECT p.id as personel_id, p.fullname, p.Tel, p.E_mail, pos.position_name, d.department_name, d.id as department_id
              FROM personel_data p
              JOIN positions pos ON p.position_id = pos.id
              JOIN department d ON p.department_id = d.id
              WHERE p.is_deleted = 0 AND pos.position_name = 'รองผู้อำนวยการ'
              ORDER BY d.id ASC";
$viceResult = $mysqli3->query($viceQuery);
$viceDirectors = [];
while ($row = $viceResult->fetch_assoc()) {
    $viceDirectors[] = $row;
}

// --- Query งานและหัวหน้างาน ---
$branchQuery = "SELECT b.id, b.department_id, b.workbranch_name, p.fullname as head_name
                FROM workbranch b
                LEFT JOIN work_detail w ON b.id = w.workbranch_id AND w.worklevel_id = 1
                LEFT JOIN personel_data p ON w.personel_id = p.id AND p.is_deleted = 0
                ORDER BY b.department_id ASC, b.id ASC";
$branchResult = $mysqli3->query($branchQuery);
$workHeads = [];
while ($b = $branchResult->fetch_assoc()) {
    $workHeads[$b['department_id']][] = $b;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <!-- Tailwind CSS for layout scaffolding -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Sarabun', sans-serif;
            background: #ffffff;
            color: #000000;
            margin: 0;
            padding: 20px;
        }

        /* ----- Minimalist Block CSS ----- */
        .org-box {
            border: 2px solid #333;
            border-radius: 4px;
            padding: 10px 12px;
            background: #fff;
            text-align: center;
            display: inline-block;
            min-width: 140px;
            max-width: 220px;
            box-sizing: border-box;
            position: relative;
            z-index: 2;
        }

        .org-box.director {
            border-width: 3px;
            font-size: 16px;
        }

        .org-box.vice {
            font-size: 14px;
        }
        
        .org-box.dept {
            border: 1px solid #666;
            font-size: 12px;
            text-align: left;
            width: 100%;
            max-width: none;
            padding: 6px 10px;
            border-radius: 2px;
        }

        .name { font-weight: 700; margin-bottom: 2px; }
        .position { font-weight: 500; color: #444; font-size: 0.9em; }
        .dept-title { font-size: 0.85em; color: #555; margin-top: 4px; border-top: 1px dotted #ccc; padding-top: 4px;}

        /* ----- TREE CSS (Classic Horizontal) ----- */
        .tree, .tree ul, .tree li {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .tree {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .tree ul {
            display: flex;
            position: relative;
            padding-top: 20px;
            justify-content: center;
        }

        .tree li {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            padding: 20px 10px 0 10px;
        }

        /* Connecting lines */
        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 2px solid #333;
            width: 50%;
            height: 20px;
            z-index: 1;
        }
        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 2px solid #333;
        }

        /* Remove lines for single children */
        .tree li:only-child::after, .tree li:only-child::before {
            display: none;
        }
        .tree li:only-child {
            padding-top: 0;
        }

        /* First and Last children handling */
        .tree li:first-child::before, .tree li:last-child::after {
            border: 0 none;
        }
        .tree li:last-child::before {
            border-right: 2px solid #333;
        }

        /* Downward line from parents to children */
        .tree ul::before {
            content: '';
            position: absolute;
            top: -2px; /* Pull it slightly up to connect perfectly */
            left: 50%;
            border-left: 2px solid #333;
            width: 0;
            height: 22px; /* Extend height to cover the pull */
            z-index: 1;
        }

        /* Remove top line for the very first ul (Director level) */
        .tree > ul {
            padding-top: 0px;
        }
        .tree > ul::before {
            display: none;
        }

        /* Vertical list for lower layers */
        .vertical-list {
            display: flex !important;
            flex-direction: column;
            padding-top: 15px; /* Less gap since no lines */
            position: relative;
            align-items: center; /* Center align cards under Vice Director */
            gap: 8px; /* Give some spacing between cards */
        }
        .vertical-list::before {
             display: none !important;
        }
        .vertical-list li {
            padding: 0; 
            align-items: center; /* Center align */
            width: auto; /* Let card determine width */
        }

        /* Hide horizontal connections for vertical list */
        .vertical-list li::before, .vertical-list li::after {
            display: none;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .org-box {
                border-color: #000 !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .tree li::before, .tree li::after, .tree ul::before {
                border-color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            /* Force everything to fit on a single page */
            .tree-wrapper {
                overflow: visible !important;
            }
            body {
                /* Scale the whole page to fit. Adjust if chart is too large/small */
                zoom: 0.75;
            }
        }
    </style>
</head>
<body>

    <div class="no-print max-w-5xl mx-auto mb-6 bg-slate-100 p-4 border border-slate-300 rounded flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Print Preview: ผังโครงสร้างองค์กร</h1>
            <p class="text-sm text-slate-600">ออกแบบมาเพื่อปรับตัวอัตโนมัติ ไม่ว่าคุณจะเลือกพิมพ์แนวตั้ง (Portrait) หรือ แนวนอน (Landscape)</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.close()" class="px-4 py-2 bg-white border border-slate-300 rounded hover:bg-slate-50 transition">ปิด</button>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white font-medium rounded hover:bg-blue-700 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                พิมพ์ PDF
            </button>
        </div>
    </div>

    <div class="text-center w-full mb-8 pt-4">
        <h2 class="text-xl md:text-xl font-bold text-black" style="color: #000;">ผังการบริหารงานวิทยาลัยเทคนิคเลย</h2>
    </div>

    <div class="tree-wrapper w-full overflow-x-auto pb-10">
        <div class="tree">
            <!-- Ensure tree starts without extra top gap, but lines can reach -->
            <ul style="padding-top: 0;">
                <li style="padding-bottom: 0;">
                    <!-- Director -->
                    <?php if($director): ?>
                    <div class="org-box director" style="position: relative; z-index: 10;">
                        <div class="name"><?= htmlspecialchars($director['fullname']) ?></div>
                        <div class="position"><?= htmlspecialchars($director['position_name']) ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- First Level: Separation for better layout -->
                    <?php 
                    if(!empty($viceDirectors)): 
                        $academicVice = null;
                        $otherVices = [];
                        foreach($viceDirectors as $vice) {
                            if (strpos($vice['department_name'], 'วิชาการ') !== false) {
                                $academicVice = $vice;
                            } else {
                                $otherVices[] = $vice;
                            }
                        }
                    ?>
                    
                    <ul>
                        <?php foreach($otherVices as $vice): ?>
                        <li>
                            <div class="org-box vice">
                                <div class="name"><?= htmlspecialchars($vice['fullname']) ?></div>
                                <div class="position"><?= htmlspecialchars($vice['position_name']) ?></div>
                                <div class="dept-title"><?= htmlspecialchars($vice['department_name']) ?></div>
                            </div>
                            
                            <!-- Work Branches for Others -->
                            <?php if(isset($workHeads[$vice['department_id']])): ?>
                            <ul class="vertical-list">
                                <?php foreach($workHeads[$vice['department_id']] as $work): ?>
                                <li style="padding-top: 5px;">
                                    <div class="org-box dept">
                                        <div class="font-bold text-gray-800 leading-tight mb-1"><?= htmlspecialchars($work['workbranch_name']) ?></div>
                                        <?php if($work['head_name']): ?>
                                            <div class="text-[11px] text-gray-600">
                                                <span>หัวหน้า:</span> <?= htmlspecialchars($work['head_name']) ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-[11px] text-gray-400 italic">- ว่าง -</div>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>

                        <!-- Academics Split -->
                        <?php if($academicVice): ?>
                        <li>
                            <div class="org-box vice" style="border-color: #059669; border-width: 2px;">
                                <div class="name"><?= htmlspecialchars($academicVice['fullname']) ?></div>
                                <div class="position"><?= htmlspecialchars($academicVice['position_name']) ?></div>
                                <div class="dept-title"><?= htmlspecialchars($academicVice['department_name']) ?></div>
                            </div>
                            
                            <?php if(isset($workHeads[$academicVice['department_id']])): 
                                // Split Academics into 2 columns
                                $allWorks = $workHeads[$academicVice['department_id']];
                                $half = ceil(count($allWorks) / 2);
                                $col1 = array_slice($allWorks, 0, $half);
                                $col2 = array_slice($allWorks, $half);
                            ?>
                            <ul style="padding-top:20px;">
                                <li style="padding-top: 0;">
                                    <ul class="vertical-list" style="padding-top:0;">
                                        <?php foreach($col1 as $work): ?>
                                        <li style="padding-top: 5px;">
                                            <div class="org-box dept" style="width: 170px;">
                                                <div class="font-bold text-gray-800 leading-tight mb-1" style="font-size:11px;"><?= htmlspecialchars($work['workbranch_name']) ?></div>
                                                <div class="text-[10px] text-gray-600">
                                                    <?= $work['head_name'] ? htmlspecialchars($work['head_name']) : '<span class="italic text-gray-400">- ว่าง -</span>' ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <li style="padding-top: 0;">
                                    <ul class="vertical-list" style="padding-top:0;">
                                        <?php foreach($col2 as $work): ?>
                                        <li style="padding-top: 5px;">
                                            <div class="org-box dept" style="width: 170px;">
                                                <div class="font-bold text-gray-800 leading-tight mb-1" style="font-size:11px;"><?= htmlspecialchars($work['workbranch_name']) ?></div>
                                                <div class="text-[10px] text-gray-600">
                                                    <?= $work['head_name'] ? htmlspecialchars($work['head_name']) : '<span class="italic text-gray-400">- ว่าง -</span>' ?>
                                                </div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>

</body>
</html>
