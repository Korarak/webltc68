<?php
$title = "ประวัติวิทยาลัย";
ob_start();
?>
<style>
  @keyframes float-y { 0%{transform:translateY(0)} 50%{transform:translateY(-6px)} 100%{transform:translateY(0)} }
  @keyframes shmr { 0%{background-position:-1000px 0} 100%{background-position:1000px 0} }

  /* ═══════ V1 PREMIUM STYLES ═══════ */
  .history-hero { position:relative; overflow:hidden; background:linear-gradient(135deg,#064e3b 0%,#065f46 40%,#047857 100%); min-height:500px; }
  .history-hero::before { content:''; position:absolute; inset:0; background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat; }

  .hero-img-placeholder {
    border:3px dashed rgba(255,255,255,.3); border-radius:1.5rem;
    background:linear-gradient(to right, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 100%);
    background-size: 1000px 100%; animation: shmr 3s infinite linear;
    display:flex; align-items:center; justify-content:center; flex-direction:column;
    color:rgba(255,255,255,.6); transition:all .3s; min-height:300px; backdrop-filter:blur(10px);
  }
  .hero-img-placeholder:hover { border-color:rgba(255,255,255,.6); background:rgba(255,255,255,.1); transform:scale(1.01); }

  .stat-card { background:white; border-radius:1rem; padding:1.5rem; text-align:center; box-shadow:0 4px 6px -1px rgba(0,0,0,.05); border:1px solid #e5e7eb; transition:all .3s; position:relative; overflow:hidden; }
  .stat-card:hover { transform:translateY(-5px); box-shadow:0 20px 25px -5px rgba(0,0,0,.1); border-color:#10b981; }
  .stat-number { font-size:2.5rem; font-weight:800; background:linear-gradient(135deg,#059669,#0d9488); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

  .logo-card { background:white; border-radius:1.5rem; padding:2rem; border:1px solid #e5e7eb; transition:all .3s; text-align:center; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; }
  .logo-card:hover { transform:translateY(-5px); box-shadow:0 20px 40px -5px rgba(0,0,0,.1); border-color:#10b981; }
  .logo-circle { width:100px; height:100px; background:#f8fafc; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; border:1px dashed #cbd5e1; }

  .gallery-grid { display:grid; gap:1rem; grid-template-columns:repeat(3,1fr); grid-template-rows:200px 200px; }
  .g-tall { grid-row:span 2; }
  @media(max-width:768px) { .gallery-grid { grid-template-columns:1fr; grid-template-rows:auto; } .g-tall { grid-row:span 1; min-height:300px; } }

  .timeline-item { position:relative; padding-left:2.5rem; padding-bottom:3rem; border-left:2px solid #e5e7eb; }
  .timeline-item:last-child { border-left-color:transparent; }
  .timeline-dot { position:absolute; left:-9px; top:0; width:16px; height:16px; border-radius:50%; background:#10b981; border:3px solid white; box-shadow:0 0 0 3px #d1fae5; }
  .timeline-dot.highlight { background:#f59e0b; box-shadow:0 0 0 3px #fef3c7; }
</style>

<!-- ═══════ HERO ═══════ -->
<header class="history-hero">
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
      <!-- Text -->
      <div class="relative z-10 animate-[fade-up_1s_ease-out]">
        <div class="flex flex-wrap items-center gap-3 mb-6">
          <span class="inline-flex items-center rounded-full bg-white/10 backdrop-blur-md px-4 py-1.5 text-xs font-semibold text-white border border-white/20 shadow-lg">
            <span class="w-2 h-2 rounded-full bg-emerald-400 mr-2 animate-pulse"></span> Loeitech
          </span>
          <span class="text-emerald-100/80 text-xs font-mono tracking-wide">EST. 1938</span>
        </div>
        
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight">
          ประวัติ<br>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-200 to-teal-50" style="filter: drop-shadow(0 2px 10px rgba(0,0,0,0.2));">
            วิทยาลัยเทคนิคเลย
          </span>
        </h1>
        
        <!-- Contact Info (Added from Content Brief) -->
        <div class="mt-8 space-y-3 bg-white/5 backdrop-blur-sm p-4 rounded-xl border border-white/10 max-w-md hover:bg-white/10 transition">
           <div class="flex items-start gap-3 text-emerald-50/90 text-sm">
              <svg class="w-5 h-5 mt-0.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span>272 ถ.เจริญรัฐ ต.กุดป่อง อ.เมือง จ.เลย 42000</span>
           </div>
           <div class="flex items-center gap-3 text-emerald-50/90 text-sm">
              <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
              <span>042-811591, 080-1502400</span>
           </div>
        </div>

        <div class="mt-8 flex gap-3">
          <a href="#timeline" class="px-6 py-3 rounded-full bg-white text-emerald-800 font-bold shadow-lg hover:shadow-xl hover:bg-emerald-50 transition transform hover:-translate-y-1">
             เส้นทางประวัติศาสตร์
          </a>
          <a href="#gallery" class="px-6 py-3 rounded-full bg-emerald-900/30 text-white font-medium border border-white/20 hover:bg-emerald-900/50 transition">
             เยี่ยมชมสถานที่
          </a>
        </div>
      </div>
      
      <!-- Hero Image Placeholder (Premium V1) -->
      <div class="hero-img-placeholder group relative z-10 w-full">
         <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center mb-4 group-hover:scale-110 transition duration-500">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
         </div>
         <span class="font-bold text-lg text-white">พื้นที่สำหรับรูปภาพพระวิษณุกรรม</span>
         <span class="text-sm font-light text-emerald-200 mt-1">ขนาดแนะนำ 1200 x 600 px</span>
         <div class="mt-4 px-3 py-1 rounded border border-white/30 text-xs text-white/50 bg-black/20 font-mono">
            images/history/hero.jpg
         </div>
      </div>
    </div>
  </div>
</header>

<main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pb-20 -mt-16 relative z-20">

  <!-- ═══════ PREMIUM STATS (V1 Style) ═══════ -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-20">
    <div class="stat-card">
       <div class="stat-number"><?= date('Y') + 543 - 2481 ?></div>
       <div class="text-slate-500 font-semibold text-sm mt-2 uppercase tracking-wide">ปีแห่งประสบการณ์</div>
    </div>
    <div class="stat-card">
       <div class="stat-number">10+</div>
       <div class="text-slate-500 font-semibold text-sm mt-2 uppercase tracking-wide">แผนกวิชา</div>
    </div>
    <div class="stat-card">
       <div class="stat-number">3</div>
       <div class="text-slate-500 font-semibold text-sm mt-2 uppercase tracking-wide">ระดับการศึกษา</div>
    </div>
    <div class="stat-card">
       <div class="stat-number">2481</div>
       <div class="text-slate-500 font-semibold text-sm mt-2 uppercase tracking-wide">ปีก่อตั้ง (พ.ศ.)</div>
    </div>
  </div>

  <!-- ═══════ DOWNLOADS (Moved Up as Requested) ═══════ -->
  <section class="mb-24">
    <div class="flex items-center justify-between mb-8">
       <h2 class="text-2xl font-bold text-slate-900">ดาวน์โหลดตราสัญลักษณ์</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-6">
      <div class="logo-card group">
         <div class="logo-circle group-hover:border-emerald-400 group-hover:scale-105 transition">
            <span class="text-xs text-slate-400">Logo</span>
         </div>
         <h4 class="font-bold text-slate-800">ตราวิทยาลัย</h4>
         <a href="#" class="mt-4 px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">Download</a>
      </div>
      <div class="logo-card group">
         <div class="logo-circle group-hover:border-emerald-400 group-hover:scale-105 transition">
            <span class="text-xs text-slate-400">Loeitech</span>
         </div>
         <h4 class="font-bold text-slate-800">โลโก้ Loeitech</h4>
         <a href="#" class="mt-4 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm rounded-lg hover:bg-emerald-50 transition">Download</a>
      </div>
      <div class="logo-card group">
         <div class="logo-circle group-hover:border-emerald-400 group-hover:scale-105 transition">
            <span class="text-xs text-slate-400">VEC</span>
         </div>
         <h4 class="font-bold text-slate-800">ตราอาชีวศึกษา</h4>
         <a href="#" class="mt-4 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm rounded-lg hover:bg-emerald-50 transition">Download</a>
      </div>
    </div>
  </section>

  <!-- ═══════ NARRATIVE (V1 Mixed Layout) ═══════ -->
  <section class="grid lg:grid-cols-12 gap-12 items-start mb-24">
    <!-- Side Image (Sticky) -->
    <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-6">
       <div class="rounded-2xl overflow-hidden shadow-2xl transform rotate-1 transition hover:rotate-0">
          <div class="bg-gray-200 h-64 w-full flex flex-col items-center justify-center p-4 text-center border-4 border-white">
             <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
             <span class="text-gray-500 font-medium">ภาพโรงเรียนช่างไม้เลย (อดีต)</span>
             <span class="text-xs text-gray-400 mt-1 font-mono">images/history/old_school.jpg</span>
          </div>
       </div>
       <div class="p-6 bg-emerald-50 rounded-2xl border border-emerald-100">
          <h4 class="font-bold text-emerald-800 mb-2">วันสถาปนา</h4>
          <p class="text-emerald-700 text-sm leading-relaxed">
             วันที่ 15 กันยายน พ.ศ. 2502 เป็นวันเปิดป้าย "โรงเรียนการช่างเลย" จึงถือวันนี้เป็นวันคล้ายวันสถาปนาสืบมา
          </p>
       </div>
    </div>

    <!-- Text Content -->
    <div class="lg:col-span-7">
      <span class="text-emerald-600 font-bold tracking-wider text-sm uppercase mb-2 block">Our Story</span>
      <h2 class="text-3xl font-extrabold text-slate-900 mb-6">จุดเริ่มต้นแห่งตำนานช่างฝีมือ</h2>
      
      <div class="prose prose-lg text-slate-600 space-y-6">
        <p>
          กระทรวงศึกษาธิการได้ตั้ง <span class="font-bold text-emerald-700">"โรงเรียนช่างไม้เลย"</span> ขึ้นและได้เปิดทำการสอน เมื่อ <span class="bg-yellow-100 px-1 rounded font-semibold text-slate-800">วันที่ 17 กรกฎาคม พ.ศ. 2481</span> ตั้งอยู่บริเวณริมฝั่งแม่น้ำเลย (หน้าศาลาเทศบาลเมืองเลย)
        </p>
        <p>
          ต่อมาถูกน้ำท่วมกัดเซาะพังไปหมดจึงได้ย้ายมาตั้งที่ใหม่ คือ บริเวณบ้านติ้ว (ซึ่งเป็นสถานที่ตั้งวิทยาลัยเทคนิคเลยในปัจจุบัน) เมื่อเริ่มเปิดสอนครั้งแรกนั้น มีครู 2 คน และมีนักเรียนเพียง 16 คน โดยมี <strong>นายเจริญ หัตถกรรม</strong> เป็นครูใหญ่ เปิดรับนักเรียนจบชั้นประถม 4 หลักสูตร 3 ปี เรียกว่า "อาชีวศึกษาตอนต้น"
        </p>
        <p>
          ปี <strong>พ.ศ. 2496</strong> ขยายหลักสูตรเพิ่มขึ้นอีก 3 ปี เรียกว่า "อาชีวศึกษาตอนปลาย" รับผู้จบ ม.3 เดิม
        </p>
        <blockquote class="border-l-4 border-emerald-500 pl-4 py-2 my-6 bg-slate-50 italic text-slate-700">
           "สถาบันสร้างสมปัญญา เกียรติศักดิ์ ความซื่อสัตย์แห่งตน และวิชาชีพ"
        </blockquote>
      </div>
    </div>
  </section>

  <!-- ═══════ IDENTITY CARD (V1 Style) ═══════ -->
  <section class="mb-24">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-extrabold text-slate-900">อัตลักษณ์ & สีประจำวิทยาลัย</h2>
      <p class="text-slate-500 mt-2">ความหมายอันลึกซึ้งของสี เขียว – ขาว</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Green -->
      <div class="group relative bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-xl transition-all duration-300">
         <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
         <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition">G</div>
            <h3 class="text-xl font-bold text-emerald-800">สีเขียว</h3>
            <p class="text-sm text-slate-600 mt-2">เกิดจาก <span class="font-bold text-yellow-500">สีเหลือง</span> ผสม <span class="font-bold text-blue-600">สีน้ำเงิน</span></p>
         </div>
      </div>
      <!-- Yellow -->
      <div class="group relative bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-xl transition-all duration-300">
         <div class="absolute inset-0 bg-gradient-to-br from-yellow-50 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
         <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-yellow-400 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition">Y</div>
            <h3 class="text-xl font-bold text-yellow-700">สีเหลือง</h3>
            <p class="text-sm text-slate-600 mt-2">ความสว่าง <span class="font-semibold">& ปัญญา</span> (Wisdom)</p>
         </div>
      </div>
      <!-- Blue -->
      <div class="group relative bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-xl transition-all duration-300">
         <div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
         <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-blue-600 text-white flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 transition">B</div>
            <h3 class="text-xl font-bold text-blue-800">สีน้ำเงิน</h3>
            <p class="text-sm text-slate-600 mt-2">ความสูงศักดิ์ <span class="font-semibold">(Noble Rank)</span></p>
         </div>
      </div>
      <!-- White -->
      <div class="group relative bg-white overflow-hidden rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-xl transition-all duration-300">
         <div class="absolute inset-0 bg-gradient-to-br from-gray-50 to-transparent opacity-0 group-hover:opacity-100 transition"></div>
         <div class="relative z-10">
            <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center mb-4 shadow-inner border border-slate-200 group-hover:scale-110 transition">W</div>
            <h3 class="text-xl font-bold text-slate-800">สีขาว</h3>
            <p class="text-sm text-slate-600 mt-2">ความซื่อสัตย์ <span class="font-semibold">& บริสุทธิ์</span></p>
         </div>
      </div>
    </div>
  </section>

  <!-- ═══════ DETAILED TIMELINE (Content V2, Style V1) ═══════ -->
  <section id="timeline" class="mb-24">
    <div class="flex items-end justify-between mb-12 border-b border-slate-200 pb-4">
      <div>
         <span class="text-emerald-600 font-bold tracking-wider text-sm uppercase">Timeline</span>
         <h2 class="text-3xl font-extrabold text-slate-900 mt-1">เส้นทางแห่งการพัฒนา</h2>
      </div>
    </div>

    <div class="max-w-4xl mx-auto">
      <?php
      $history_events = [
          ['year' => '2500', 'desc' => 'ขยายหลักสูตรเป็น "ประโยคอาชีวศึกษาชั้นสูง" รับ ม.6'],
          ['year' => '2502', 'date' => '26 ส.ค.', 'desc' => 'เปลี่ยนชื่อเป็น "โรงเรียนการช่างเลย"'],
          ['year' => '2502', 'date' => '15 ก.ย.', 'desc' => 'วันสถาปนาและพิธีเปิดป้ายโรงเรียนอย่างเป็นทางการ', 'highlight' => true],
          ['year' => '2515', 'desc' => 'เปิดแผนกวิชาช่างยนต์ (ปวช.)'],
          ['year' => '2516', 'desc' => 'ปรับปรุงโรงเรียนและรื้อถอนอาคารเก่า'],
          ['year' => '2517', 'desc' => 'ซื้อที่ดินเพิ่ม (รวมปัจจุบัน 25 ไร่ 2 งาน)'],
          ['year' => '2518', 'desc' => 'เปิดช่างเชื่อม (ปวช.) และสอนรอบเช้า-บ่าย'],
          ['year' => '2521', 'desc' => 'เปิดช่างไฟฟ้า (ปวช.)'],
          ['year' => '2522', 'date' => '1 ม.ค.', 'desc' => 'เปลี่ยนชื่อเป็น "โรงเรียนเทคนิคเลย"'],
          ['year' => '2522', 'desc' => 'เปิดช่างอิเล็กทรอนิกส์ (ปวช.)'],
          ['year' => '2524', 'desc' => 'ยกฐานะเป็น "วิทยาลัยเทคนิคเลย"', 'highlight' => true],
          ['year' => '2525-2539', 'desc' => 'เปิด ปวท.โยธา, ปวส.ไฟฟ้า, อิเล็กฯ, ยนต์, กลโรงงาน(ปวช.), ก่อสร้าง, เทคนิคโลหะ, เทคนิคการผลิต'],
          ['year' => '2549-2556', 'desc' => 'เปิดสถาปัตย์(ปวช.), IT(ปวส./ปวช.), ช่างยนต์ทวิภาคี'],
          ['year' => '2557', 'desc' => 'เริ่มเปิดระดับ ปริญญาตรี (ทล.บ.) เทคโนโลยีการก่อสร้าง (โยธา)', 'highlight' => true],
          ['year' => '2558-2561', 'desc' => 'เปิด ป.ตรี ไฟฟ้า, เครื่องกล / ปวส. เทคโนโลยีคอมพิวเตอร์'],
          ['year' => '2562-ปัจจุบัน', 'desc' => 'เปิดแมคคาทรอนิกส์, แอนิเมชัน, นักพัฒนาซอฟต์แวร์, ป.ตรี การผลิต, ตัวถังและสี'],
      ];

      foreach ($history_events as $event):
          $isHigh = isset($event['highlight']);
      ?>
      <div class="timeline-item group">
         <div class="timeline-dot <?= $isHigh ? 'highlight' : '' ?> group-hover:scale-125 transition"></div>
         <div class="flex flex-col sm:flex-row gap-4 items-baseline group-hover:translate-x-2 transition duration-300">
            <div class="sm:w-32 flex-shrink-0">
               <span class="inline-block px-3 py-1 rounded-full text-sm font-bold shadow-sm <?= $isHigh ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                 พ.ศ. <?= $event['year'] ?>
               </span>
               <?php if(isset($event['date'])): ?>
                 <span class="block text-xs text-slate-400 mt-1 pl-2"><?= $event['date'] ?></span>
               <?php endif; ?>
            </div>
            <div class="flex-grow">
               <p class="text-slate-700 leading-relaxed <?= $isHigh ? 'font-medium' : '' ?>"><?= $event['desc'] ?></p>
            </div>
         </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ═══════ MASONRY OVERVIEW (V1 Style) ═══════ -->
  <section id="gallery" class="mb-24">
    <div class="text-center mb-10">
      <h2 class="text-3xl font-extrabold text-slate-900">ภาพบรรยากาศ</h2>
      <p class="text-slate-500 mt-2">มุมมองต่างๆ ภายในวิทยาลัย</p>
    </div>

    <div class="gallery-grid">
      <!-- Tall Image -->
      <div class="g-tall bg-gray-100 rounded-2xl overflow-hidden relative group">
         <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition z-10"></div>
         <div class="absolute bottom-4 left-4 text-white z-20 opacity-0 group-hover:opacity-100 transition transform translate-y-4 group-hover:translate-y-0">
            <span class="font-bold">อาคารเฉลิมพระเกียรติ</span>
         </div>
         <div class="w-full h-full flex items-center justify-center bg-emerald-50 text-emerald-300">
            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
         </div>
         <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-xs text-emerald-400">images/history/building_tall.jpg</span>
      </div>
      
      <!-- Standard Images -->
      <?php for($i=1; $i<=4; $i++): ?>
      <div class="bg-gray-100 rounded-2xl overflow-hidden relative group min-h-[200px]">
          <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition z-10"></div>
          <div class="w-full h-full flex items-center justify-center bg-slate-50 text-slate-300">
            <span class="text-xs">images/history/gallery_<?= $i ?>.jpg</span>
          </div>
      </div>
      <?php endfor; ?>
    </div>
  </section>

</main>

<style>
  /* Custom Scrollbar */
  ::-webkit-scrollbar { width: 10px; }
  ::-webkit-scrollbar-track { background: #f1f1f1; }
  ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 5px; }
  ::-webkit-scrollbar-thumb:hover { background: #059669; }
</style>

<?php
$content = ob_get_clean();
include 'base.php';
?>