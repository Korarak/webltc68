<?php
$title = "ผังโครงสร้างผู้บริหาร";
ob_start();
@require 'condb/condb.php';

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

// --- Query ฝ่าย/แผนกทั้งหมด ---
$deptQuery = "SELECT * FROM department ORDER BY id ASC";
$deptResult = $mysqli3->query($deptQuery);
$departments = [];
while ($d = $deptResult->fetch_assoc()) {
    $departments[$d['id']] = $d['department_name'];
}

// --- Query งานทั้งหมด ---
$branchQuery = "SELECT * FROM workbranch ORDER BY department_id ASC, id ASC";
$branchResult = $mysqli3->query($branchQuery);
$workbranches = [];
while ($b = $branchResult->fetch_assoc()) {
    $workbranches[$b['department_id']][$b['id']] = $b['workbranch_name'];
}

// --- Query หัวหน้างาน ---
$headQuery = "SELECT w.workbranch_id, p.id as personel_id, p.fullname 
              FROM work_detail w
              JOIN personel_data p ON w.personel_id = p.id
              WHERE p.is_deleted = 0 AND w.worklevel_id = 1";
$headResult = $mysqli3->query($headQuery);
$workHeads = [];
while ($h = $headResult->fetch_assoc()) {
    $workHeads[$h['workbranch_id']] = $h;
}
?>

<style>
  /* --- Global & Utilities --- */
  .org-title {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 10px 30px rgba(5, 150, 105, 0.2);
  }

  /* --- Director Card --- */
  .card-director {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 20px 50px rgba(5, 150, 105, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
  }

  /* --- Vice Director Card --- */
  .card-vice {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    /* overflow: hidden; เอาออกเพื่อให้ Tooltip หรือเงาทำงานได้เต็มที่ */
  }

  .card-vice:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    border-color: rgba(16, 185, 129, 0.4);
    z-index: 50;
  }

  /* ส่วนหัวของการ์ดรอง (รูป+ชื่อ) */
  .vice-header {
    cursor: pointer;
    background: linear-gradient(to bottom, #ffffff, #f9fafb);
    transition: background 0.3s;
    border-radius: 1rem 1rem 0 0; /* โค้งเฉพาะด้านบน */
  }
  
  .vice-header:hover {
    background: #f0fdf4;
  }

  /* รูป Avatar */
  .avatar-circle {
    background: linear-gradient(135deg, #34d399 0%, #059669 100%);
    box-shadow: 0 4px 6px -1px rgba(5, 150, 105, 0.2);
  }

  /* --- Department List Area --- */
  .dept-section {
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    border-radius: 0 0 1rem 1rem; /* โค้งเฉพาะด้านล่าง */
    flex-grow: 1; /* ยืดให้เต็มพื้นที่ที่เหลือ (ถ้าการ์ดเพื่อนข้างๆ สูงกว่า) */
  }

  /* รายการงาน (Work Item) */
  .work-item {
    background: white;
    border: 1px solid #f1f5f9;
    transition: all 0.2s;
  }
  .work-item:hover {
    border-color: #d1fae5;
    background: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
  }

  /* ปุ่มหัวหน้างาน (Capsule) */
  .head-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background-color: #ecfdf5;
    color: #059669;
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid #d1fae5;
    transition: all 0.2s ease;
    margin-top: 6px;
  }

  .head-badge:hover {
    background-color: #059669;
    color: white;
    border-color: #059669;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
  }

  .divider-line {
    background: linear-gradient(180deg, rgba(5, 150, 105, 0.5) 0%, rgba(5, 150, 105, 0.1) 100%);
  }

  /* --- Print Styles --- */
  @media print {
    body {
      background-color: white !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    
    .no-print, header, footer, .btn-print, #personModal {
      display: none !important;
    }

    main {
      margin-top: 0 !important;
      padding-top: 0 !important;
      max-width: 100% !important;
    }

    .org-title {
      background: #059669 !important;
      box-shadow: none !important;
      color: white !important;
      padding: 16px 0 !important;
      margin-bottom: 20px !important;
      border-radius: 8px !important;
    }

    .card-director {
      background: #059669 !important;
      box-shadow: none !important;
      border-radius: 8px !important;
      border: 1px solid #10b981 !important;
      page-break-inside: avoid;
      padding: 16px !important;
    }
    
    .card-vice {
      box-shadow: none !important;
      border: 1px solid #ccc !important;
      border-radius: 8px !important;
      page-break-inside: avoid;
    }

    .vice-header {
      background: #f8fafc !important;
      border-bottom: 1px solid #ccc !important;
      padding: 12px !important;
    }

    .dept-section {
      background: white !important;
      border-top: none !important;
      padding: 12px !important;
    }

    .work-item {
      border: 1px solid #eee !important;
      page-break-inside: avoid;
      padding: 8px !important;
    }

    .head-badge {
      border: 1px solid #059669 !important;
      color: #059669 !important;
      background: #ecfdf5 !important;
    }

    .grid {
      display: grid !important;
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      gap: 16px !important;
    }
    
    .divider-line {
       display: none !important;
    }

    .text-emerald-100 { color: #d1fae5 !important; }
    .text-emerald-50 { color: #ecfdf5 !important; }
    .bg-emerald-800\/30 { background-color: rgba(6, 78, 59, 0.3) !important; }
    
    /* Make Director card smaller for print */
    .card-director h2 { font-size: 1.5rem !important; margin-bottom: 4px !important; }
    .card-director .material-symbols-outlined { font-size: 2rem !important; }
    .card-director .bg-white\/20 { padding: 8px !important; }
  }
</style>

<main class="max-w-7xl mx-auto px-4 py-8 mt-[72px]">
  
  <div class="flex justify-end mb-4 btn-print no-print">
    <a href="organization_print.php" target="_blank" class="flex items-center gap-2 bg-slate-800 text-white px-5 py-2.5 rounded-xl hover:bg-slate-700 transition shadow-lg border border-slate-600 font-medium">
      <span class="material-symbols-outlined">print</span> พิมพ์โครงสร้างองค์กร (PDF)
    </a>
  </div>

  <div class="org-title text-white text-center py-8 px-6 rounded-2xl mb-12 shadow-xl">
    <div class="flex justify-center mb-3">
      <span class="material-symbols-outlined text-5xl drop-shadow-md">account_balance</span>
    </div>
    <h1 class="text-3xl md:text-4xl font-bold drop-shadow-sm">โครงสร้างองค์กร</h1>
    <p class="text-emerald-100 text-lg mt-2 font-light">วิทยาลัยเทคนิคเลย</p>
  </div>

  <?php if ($director): ?>
    <div class="flex flex-col items-center">
      <div class="card-director text-white rounded-2xl p-8 text-center w-full max-w-md mb-8 cursor-pointer group transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl" onclick="openPersonModal(<?= $director['id'] ?>)">
        <div class="flex justify-center mb-4">
          <div class="bg-white/20 p-4 rounded-full backdrop-blur-sm group-hover:scale-110 transition-transform duration-300">
             <span class="material-symbols-outlined text-5xl">person_4</span>
          </div>
        </div>
        <h2 class="text-2xl font-bold mb-2 tracking-wide"><?= htmlspecialchars($director['fullname']) ?></h2>
        <p class="text-emerald-50 text-lg font-medium mb-6 bg-emerald-800/30 inline-block px-4 py-1 rounded-full"><?= htmlspecialchars($director['position_name']) ?></p>
        
        <div class="flex justify-center gap-4 text-sm opacity-90">
          <?php if($director['Tel']): ?>
             <div class="flex items-center gap-1"><span class="material-symbols-outlined text-base">phone</span> <?= $director['Tel'] ?></div>
          <?php endif; ?>
          <?php if($director['E_mail']): ?>
             <div class="flex items-center gap-1"><span class="material-symbols-outlined text-base">mail</span> Email</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="divider-line w-[2px] h-16 mb-8"></div>
    </div>
  <?php endif; ?>

  <?php if (!empty($viceDirectors)): ?>
    <div class="w-full mb-12">
      <div class="relative flex py-5 items-center mb-8">
          <div class="flex-grow border-t border-gray-200"></div>
          <span class="flex-shrink-0 mx-4 text-gray-600 font-bold text-xl flex items-center gap-2">
             <span class="material-symbols-outlined text-emerald-600">manage_accounts</span> รองผู้อำนวยการ
          </span>
          <div class="flex-grow border-t border-gray-200"></div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative"> 
        <?php foreach ($viceDirectors as $vice): ?>
          <div class="card-vice h-full"> <div class="p-6 vice-header text-center" onclick="openPersonModal(<?= $vice['personel_id'] ?>)">
                <div class="flex justify-center mb-4">
                  <div class="avatar-circle rounded-full p-4 text-white transform transition-transform duration-300 group-hover:scale-110">
                    <span class="material-symbols-outlined text-4xl">person</span>
                  </div>
                </div>

                <h4 class="font-bold text-lg text-gray-800 mb-1 leading-tight"><?= htmlspecialchars($vice['fullname']) ?></h4>
                <p class="text-emerald-600 text-sm font-bold mb-3"><?= htmlspecialchars($vice['position_name']) ?></p>
                
                <div class="inline-block bg-emerald-50 text-emerald-800 px-3 py-1 rounded-md text-xs font-semibold border border-emerald-100 mb-4">
                  <?= htmlspecialchars($vice['department_name']) ?>
                </div>

                <div class="flex justify-center gap-3 text-gray-400">
                   <?php if($vice['Tel']): ?>
                    <a href="tel:<?= $vice['Tel'] ?>" onclick="event.stopPropagation()" class="hover:text-emerald-600 hover:bg-emerald-50 p-2 rounded-full transition-colors" title="โทร"><span class="material-symbols-outlined text-lg">phone</span></a>
                   <?php endif; ?>
                   <?php if($vice['E_mail']): ?>
                    <a href="mailto:<?= $vice['E_mail'] ?>" onclick="event.stopPropagation()" class="hover:text-emerald-600 hover:bg-emerald-50 p-2 rounded-full transition-colors" title="อีเมล"><span class="material-symbols-outlined text-lg">mail</span></a>
                   <?php endif; ?>
                </div>
            </div>

            <div class="dept-section p-4">
              <h5 class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-1 pl-1">
                <span class="material-symbols-outlined text-[14px]">folder_open</span>
                งานในสังกัด
              </h5>
              
              <div class="space-y-2"> <?php
                $deptId = $vice['department_id'];
                if (isset($workbranches[$deptId])) {
                    foreach ($workbranches[$deptId] as $branchId => $work) {
                ?>
                        <div class="work-item rounded-lg p-3">
                            <div class="text-xs font-medium text-gray-700 mb-1"><?= htmlspecialchars($work) ?></div>
                            <?php if (isset($workHeads[$branchId])): ?>
                                <div class="head-badge" 
                                     onclick="event.stopPropagation(); openPersonModal(<?= $workHeads[$branchId]['personel_id'] ?>)">
                                  <span class="material-symbols-outlined text-[14px]">account_circle</span>
                                  <span><?= htmlspecialchars($workHeads[$branchId]['fullname']) ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-300 text-[10px] italic pl-1">- ว่าง -</span>
                            <?php endif; ?>
                        </div>
                <?php
                    }
                }
                ?>
              </div>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</main>

<div id="personModal" class="fixed inset-0 bg-black bg-opacity-60 hidden z-[100] flex items-center justify-center p-4 backdrop-blur-sm" onclick="closePersonModalOutside(event)">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-auto p-0 relative max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation();">
    <button class="absolute top-4 right-4 bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-500 rounded-full p-2 transition-colors z-10" onclick="closePersonModal()">
      <span class="material-symbols-outlined text-xl">close</span>
    </button>
    
    <div id="personModalContent" class="overflow-y-auto p-6">
      </div>
  </div>
</div>

<script>
function openPersonModal(id) {
  const modal = document.getElementById('personModal');
  const modalContent = document.getElementById('personModalContent');
  
  modal.classList.remove('hidden');
  modalContent.innerHTML = `
      <div class="flex flex-col items-center justify-center py-12">
          <span class="material-symbols-outlined text-4xl text-emerald-500 animate-spin">refresh</span>
          <p class="text-gray-400 mt-4">กำลังโหลดข้อมูล...</p>
      </div>
  `;
  
  fetch(`api/person_detail.php?id=${id}`)
    .then(res => res.text())
    .then(html => modalContent.innerHTML = html)
    .catch(() => {
        modalContent.innerHTML = `
            <div class="text-center py-12">
                <span class="material-symbols-outlined text-4xl text-red-400 mb-2">error</span>
                <p class="text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            </div>
        `;
    });
}

function closePersonModal() {
  document.getElementById('personModal').classList.add('hidden');
}

function closePersonModalOutside(event) {
  if (event.target.id === 'personModal') closePersonModal();
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closePersonModal();
});
</script>

<?php
$content = ob_get_clean();
include 'base.php';
?>