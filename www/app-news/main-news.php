<?php
// เชื่อมฐานข้อมูลจดหมายข่าว (SQL คงเดิม)
// ต้องมั่นใจว่าไฟล์ 'admin/db_letter.php' มี $conn
include 'admin/db_letter.php';
$sql = "SELECT * FROM letters ORDER BY letter_createtime DESC LIMIT 4";
$result = $conn->query($sql);

// เชื่อมฐานข้อมูลบุคลากร (SQL คงเดิม)
// ต้องมั่นใจว่าไฟล์ 'condb/condb.php' มี $mysqli3
include 'condb/condb.php';
global $mysqli3;

// ดึงข้อมูลผู้บริหาร (SQL: เพิ่ม pd.id)
$sql_boss = "SELECT pd.id, pd.fullname, pd.profile_image, p.position_name, d.department_name
        FROM personel_data pd
        JOIN positions p ON pd.position_id = p.id
        LEFT JOIN department d ON pd.department_id = d.id
        WHERE p.position_name IN ('ผู้อำนวยการ', 'รองผู้อำนวยการ')
        ORDER BY FIELD(pd.position_id, 1, 2), pd.fullname";


$result_boss = $mysqli3->query($sql_boss);
$executives = [];
while ($row_boss = $result_boss->fetch_assoc()) {
    // ต้องมี ID สำหรับ Modal
    $executives[] = $row_boss; 
}
?>

<script>
  // 1. ส่งข้อมูลผู้บริหาร (รวม ID) ไปยัง Alpine.js
  window.executives = <?= json_encode($executives, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  
  // ฟังก์ชัน openPersonModal ต้องถูกกำหนดไว้ก่อนใน <script> ด้านล่าง
</script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="container mx-auto px-2 sm:px-4 py-8 max-w-7xl">
  <div class="flex flex-col lg:flex-row gap-8">
    
    <div x-data="{
        current: 0,
        items: window.executives,
        get currentItem() {
            return this.items[this.current] || {};
        },
        init() {
          // Auto-scroll removed by user request
        }
      }"
      class="w-full lg:w-96 mx-auto flex-shrink-0"
    >
      <div 
        class="bg-white shadow-2xl rounded-2xl overflow-hidden border border-gray-100 relative h-fit group cursor-pointer" 
        x-show="items.length > 0"
        x-bind:data-id="currentItem.id" 
        @click="openPersonModal(currentItem.id)"
        @keydown.enter.space.prevent="openPersonModal(currentItem.id)"
        tabindex="0"
      >
        
        <div class="overflow-hidden">
            <div class="relative overflow-hidden aspect-[4/5] bg-gray-200">
                <img
                  x-bind:src="currentItem.profile_image ? '/' + currentItem.profile_image : '/uploads/default.png'"
                  x-bind:alt="currentItem.fullname"
                  class="w-full h-full object-cover group-hover:scale-105"
                  loading="lazy"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 pointer-events-none"></div>
            </div>
            
            <div class="p-6 text-center bg-gray-50 border-t border-gray-100">
              <h5 class="text-xl font-bold text-gray-900 mb-1" x-text="currentItem.fullname"></h5>
              
              <p class="text-base font-semibold" 
                 x-bind:class="currentItem.position_name === 'ผู้อำนวยการ' ? 'text-green-700' : 'text-green-600'"
                 x-text="currentItem.position_name"></p>
              
              <template x-if="currentItem.position_name && currentItem.position_name.includes('รองผู้อำนวยการ')">
                <p class="text-sm text-gray-500 mt-1" x-text="currentItem.department_name"></p>
              </template>
            </div>
        </div>
      </div>
      
      <div x-show="items.length === 0" class="p-8 text-center bg-white shadow-2xl rounded-2xl border border-gray-100">
          <i class="fas fa-user-tie text-5xl text-gray-400 mb-3"></i>
          <p class="text-gray-500">ไม่พบข้อมูลผู้บริหาร</p>
      </div>

      <div class="flex justify-center gap-2 mt-4" x-show="items.length > 1">
        <template x-for="(person, index) in items" :key="'dot'+index">
          <button @click="current = index"
            :class="current === index ? 'bg-green-600 ring-4 ring-green-200' : 'bg-gray-300 hover:bg-gray-400'"
            class="w-3 h-3 rounded-full focus:outline-none"></button>
        </template>
      </div>
    </div>

    <div class="w-full lg:w-auto lg:flex-grow">
      <div class="bg-white rounded-2xl shadow-2xl p-4 md:p-6 border border-gray-100">
        <h4 class="text-2xl font-extrabold border-b-4 border-green-500 pb-3 mb-6 flex items-center text-green-700 tracking-wide">
            <i class="fas fa-envelope-open-text mr-3 text-2xl"></i>
            <span>จดหมายข่าว (Newsletter)</span>
        </h4>

        <?php if ($result->num_rows > 0): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
          <?php $i = 0; while($row = $result->fetch_assoc()): ?>
            <a href="app-news/letter_view.php?id=<?= $row['letter_id']; ?>"
               class="block group bg-gray-50 rounded-lg shadow-lg overflow-hidden hover:shadow-xl">
              
              <div class="aspect-[3/4] overflow-hidden">
                  <img src="/<?= htmlspecialchars($row['letter_attenmath']); ?>"
                       alt="จดหมายข่าว"
                       class="w-full h-full object-cover group-hover:scale-110">
              </div>

              <div class="p-2 text-center">
                  <p class="text-sm font-medium text-gray-800 line-clamp-1" title="<?= htmlspecialchars($row['letter_title']); ?>">
                      <?= htmlspecialchars($row['letter_title']); ?>
                  </p>
              </div>
            </a>
          <?php $i++; endwhile; ?>
        </div>
        <?php else: ?>
          <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
            <i class="fas fa-exclamation-triangle text-5xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 text-lg">ไม่พบข้อมูลจดหมายข่าว</p>
          </div>
        <?php endif; ?>

        <div class="text-center mt-8">
          <a href="app-news/letter_list.php"
             class="inline-flex items-center text-base font-bold bg-green-600 text-white px-6 py-3 rounded-full shadow-xl hover:bg-green-700">
            <i class="fas fa-list-ul mr-3"></i>
            ดูจดหมายข่าวทั้งหมด
          </a>
        </div>
      </div>
    </div>

  </div>
</div>

<div id="personModal"
     class="fixed inset-0 z-50 flex items-start justify-center pt-10 hidden"
     aria-hidden="true"
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-3xl mx-4 bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
         onclick="event.stopPropagation();">

        <button
            class="absolute top-4 right-4 text-gray-500 hover:text-red-600 z-10"
            aria-label="ปิด"
            onclick="closePersonModal()">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div id="personModalContent" class="p-6">
            <div class="flex justify-center items-center h-40">
                <svg class="animate-spin h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<script>
/* -------------------------------------------------------------------
 * MODAL FUNCTIONS
 * ------------------------------------------------------------------- */

function closePersonModal() {
    const modal = document.getElementById('personModal');
    if (!modal) return;
    
    // 1. ซ่อนด้วย Tailwind class
    modal.classList.add('hidden');
    // 2. ซ่อนด้วย CSS display (สำรอง, เพื่อความชัวร์)
    modal.style.display = 'none'; 
    modal.setAttribute('aria-hidden', 'true');
    
    // Reset content to spinner/loader
    document.getElementById('personModalContent').innerHTML = `
        <div class="flex justify-center items-center h-40">
            <svg class="animate-spin h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
        </div>`;
}

function openPersonModal(id) {
    if (!id) {
        console.error("Person ID is missing for modal.");
        return; 
    }

    const modal = document.getElementById('personModal');
    const content = document.getElementById('personModalContent');
    
    // 1. แสดง Modal (ลบคลาส hidden และกำหนด display: flex)
    modal.classList.remove('hidden');
    modal.style.display = 'flex'; 
    modal.setAttribute('aria-hidden', 'false');
    
    // แสดง Spinner ก่อนโหลด
    content.innerHTML = `
        <div class="flex justify-center items-center h-40">
            <svg class="animate-spin h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
        </div>`;
    
    // Scroll behavior removed by user request
    // modal.scrollIntoView({behavior: 'smooth', block: 'center'});

    // API Call
    fetch(`../api/person_detail.php?id=${id}`)
        .then(r => r.text())
        .then(html => content.innerHTML = html)
        .catch(() => content.innerHTML = `
            <p class="text-center text-red-600 font-medium">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
        `);
}


/* ---------- Event Listeners (ใช้DOMContentLoaded เพื่อความเสถียร) ---------- */
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('personModal');
    if (!modal) return;

    /* Close when clicking backdrop */
    modal.addEventListener('click', e => {
        // ตรวจสอบให้แน่ใจว่าคลิกที่ตัว Modal เอง ไม่ใช่เนื้อหาภายใน
        if (e.target.id === 'personModal') closePersonModal(); 
    });

    /* Close with ESC */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closePersonModal();
        }
    });

    /* ---------- Card Click → Load Detail (สำหรับ Card ที่ไม่ใช่ Alpine.js Slider) ---------- */
    document.querySelectorAll('.person-card').forEach(card => {
        // โค้ดนี้จะข้าม Executive Slider ไป 
        if (card.parentElement.hasAttribute('x-data')) return; 
        
        const openModalEvent = () => {
            const id = card.dataset.id;
            openPersonModal(id);
        };

        card.addEventListener('click', openModalEvent);
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModalEvent(); }
        });
    });
});

// เพราะ AlpineJS จะเรียก openPersonModal(currentItem.id) ได้โดยตรง

function copyText(text, btn){
  if (!navigator.clipboard) { 
       const textArea = document.createElement("textarea");
      textArea.value = text;
      document.body.appendChild(textArea);
      textArea.select();
      try {
          document.execCommand('copy');
          const original = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check"></i>';
          setTimeout(()=>{ btn.innerHTML = original; }, 1200);
      } catch (err) {
          console.error('Fallback: Oops, unable to copy', err);
      }
      document.body.removeChild(textArea);
      return;
  }
  const original = btn.innerHTML;
  navigator.clipboard.writeText(text).then(()=>{
    btn.innerHTML = '<i class="fas fa-check"></i> คัดลอกแล้ว';
    setTimeout(()=>{
      btn.innerHTML = original;
    }, 1200);
  });
}
</script>