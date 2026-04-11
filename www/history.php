<?php
$title = "ประวัติวิทยาลัย";

// ==========================================
// ตั้งค่าภาพประกอบหน้าประวัติวิทยาลัย
// (เปลี่ยนชื่อไลฟ์อัปเดตไฟล์ภาพที่นี่ เพื่อความสะดวก)
// ==========================================
$img_hero           = "http://loeitech.ac.th/uploads/loeitech-environment/history__4_.jpg";             // ภาพหน้าปกหลัก (Hero Image)
$img_history_old    = "http://loeitech.ac.th/uploads/loeitech-environment/history__8_.jpg";    // ภาพประกอบประวัติ (รูปโรงเรียนช่างไม้)
$img_gallery_tall   = "http://loeitech.ac.th/uploads/loeitech-environment/history__2_.jpg";    // ภาพแกลลอรี่แนวตั้ง (รูปใหญ่)
$img_gallery_items  = [                                      // ภาพแกลลอรี่ย่อย 4 ภาพ
    'http://loeitech.ac.th/uploads/loeitech-environment/history__5_.jpg', 
    'http://loeitech.ac.th/uploads/loeitech-environment/history__7_.jpg', 
    'http://loeitech.ac.th/uploads/loeitech-environment/history__3_.jpg', 
    'http://loeitech.ac.th/uploads/loeitech-environment/Gemini_Generated_Image_48ls6z48ls6z48ls_-_Copy.png'
];
// ==========================================

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
  <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-32 pb-16 lg:pt-40 lg:pb-20">
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
      
      <!-- Hero Image -->
      <div class="relative z-10 w-full animate-[fade-left_1s_ease-out]">
         <div class="rounded-2xl overflow-hidden shadow-2xl skew-y-1 hover:skew-y-0 transition-transform duration-700">
             <img src="<?= $img_hero ?>" alt="วิทยาลัยเทคนิคเลย" class="w-full h-auto object-cover transform hover:scale-105 transition duration-700">
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
         <div class="logo-circle group-hover:border-emerald-400 group-hover:scale-105 transition overflow-hidden bg-white cursor-pointer" onclick="openLightbox('images/loeitech%20logo.png')">
            <img src="images/loeitech%20logo.png" alt="ตราวิทยาลัยเทคนิคเลย" class="w-full h-full object-contain p-2">
         </div>
         <h4 class="font-bold text-slate-800">ตราวิทยาลัยเทคนิคเลย</h4>
         <a href="images/loeitech%20logo.png" download class="mt-4 px-4 py-2 bg-emerald-600 text-white text-sm rounded-lg hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">Download</a>
      </div>
      <div class="logo-card group">
         <div class="logo-circle group-hover:border-emerald-400 group-hover:scale-105 transition overflow-hidden bg-white cursor-pointer" onclick="openLightbox('images/loei-vec.png')">
            <img src="images/loei-vec.png" alt="สำนักงานอาชีวศึกษาจังหวัดเลย" class="w-full h-full object-contain p-2">
         </div>
         <h4 class="font-bold text-slate-800">สำนักงานอาชีวศึกษาจังหวัดเลย</h4>
         <a href="images/loei-vec.png" download class="mt-4 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm rounded-lg hover:bg-emerald-50 transition">Download</a>
      </div>
      <div class="logo-card group">
         <div class="logo-circle group-hover:border-emerald-400 group-hover:scale-105 transition overflow-hidden bg-white cursor-pointer" onclick="openLightbox('images/vec-logo.png')">
            <img src="images/vec-logo.png" alt="ตราสำนักงานคณะกรรมการการอาชีวศึกษา" class="w-full h-full object-contain p-2">
         </div>
         <h4 class="font-bold text-slate-800">ตราสำนักงานคณะกรรมการการอาชีวศึกษา</h4>
         <a href="images/vec-logo.png" download class="mt-4 px-4 py-2 border border-emerald-600 text-emerald-600 text-sm rounded-lg hover:bg-emerald-50 transition">Download</a>
      </div>
    </div>
  </section>

  <!-- ═══════ NARRATIVE (V1 Mixed Layout) ═══════ -->
  <section class="grid lg:grid-cols-12 gap-12 items-start mb-24">
    <!-- Side Image (Sticky) -->
    <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-6">
       <div class="rounded-2xl overflow-hidden shadow-2xl transform rotate-1 transition hover:rotate-0 cursor-pointer" onclick="openLightbox('<?= $img_history_old ?>')">
          <img src="<?= $img_history_old ?>" alt="ภาพโรงเรียนช่างไม้เลย (อดีต)" class="w-full h-auto object-cover hover:scale-105 transition duration-500">
          <div class="bg-gray-100 p-2 text-center border-t-4 border-white">
             <span class="text-gray-500 font-medium text-sm">ภาพโรงเรียนช่างไม้เลย (อดีต)</span>
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

  <!-- ═══════ CORE VALUES (Philosophy, Vision, Identity, Uniqueness) ═══════ -->
  <section class="mb-24">
    <div class="text-center mb-12">
      <span class="text-emerald-600 font-bold tracking-wider text-sm uppercase">Core Values</span>
      <h2 class="text-3xl font-extrabold text-slate-900 mt-2">ปรัชญา วิสัยทัศน์ และ เอกลักษณ์</h2>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
       <!-- Card 1: Philosophy -->
       <div class="bg-white rounded-2xl p-8 border border-emerald-100 shadow-[0_8px_30px_rgba(16,185,129,0.06)] hover:shadow-[0_8px_30px_rgba(16,185,129,0.15)] transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50/80 rounded-bl-[100px] -z-0 group-hover:scale-110 transition-transform duration-500"></div>
          <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 shadow-inner relative z-10 group-hover:bg-emerald-500 group-hover:text-white transition-colors duration-300">
             <i class="fas fa-book-open text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-3 relative z-10 group-hover:text-emerald-600 transition-colors">ปรัชญาวิทยาลัย</h3>
          <p class="text-slate-600 leading-relaxed font-medium relative z-10 group-hover:text-emerald-800 transition-colors">“วิชาดี ฝีมือเยี่ยม เปี่ยมคุณธรรม เป็นผู้นำทางอาชีพ”</p>
       </div>
       
       <!-- Card 2: Vision -->
       <div class="bg-white rounded-2xl p-8 border border-teal-100 shadow-[0_8px_30px_rgba(20,184,166,0.06)] hover:shadow-[0_8px_30px_rgba(20,184,166,0.15)] transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50/80 rounded-bl-[100px] -z-0 group-hover:scale-110 transition-transform duration-500"></div>
          <div class="w-14 h-14 rounded-2xl bg-teal-100 text-teal-600 flex items-center justify-center mb-6 shadow-inner relative z-10 group-hover:bg-teal-500 group-hover:text-white transition-colors duration-300">
             <i class="fas fa-eye text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-3 relative z-10 group-hover:text-teal-600 transition-colors">วิสัยทัศน์</h3>
          <p class="text-slate-600 leading-relaxed font-medium relative z-10 group-hover:text-teal-800 transition-colors">“พัฒนาผู้เรียนสู่มาตรฐานสากล บนพื้นฐานคุณธรรม ตามหลักปรัชญาเศรษฐกิจพอเพียง”</p>
       </div>

       <!-- Card 3: Identity -->
       <div class="bg-white rounded-2xl p-8 border border-blue-100 shadow-[0_8px_30px_rgba(59,130,246,0.06)] hover:shadow-[0_8px_30px_rgba(59,130,246,0.15)] transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50/80 rounded-bl-[100px] -z-0 group-hover:scale-110 transition-transform duration-500"></div>
          <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mb-6 shadow-inner relative z-10 group-hover:bg-blue-500 group-hover:text-white transition-colors duration-300">
             <i class="fas fa-fingerprint text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-3 relative z-10 group-hover:text-blue-600 transition-colors">อัตลักษณ์</h3>
          <p class="text-slate-600 leading-relaxed font-medium relative z-10 group-hover:text-blue-800 transition-colors">“ทักษะดี มีจิตอาสา”</p>
       </div>

       <!-- Card 4: Uniqueness -->
       <div class="bg-white rounded-2xl p-8 border border-indigo-100 shadow-[0_8px_30px_rgba(99,102,241,0.06)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.15)] transition-all duration-300 hover:-translate-y-1 relative overflow-hidden group">
          <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50/80 rounded-bl-[100px] -z-0 group-hover:scale-110 transition-transform duration-500"></div>
          <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6 shadow-inner relative z-10 group-hover:bg-indigo-500 group-hover:text-white transition-colors duration-300">
             <i class="fas fa-award text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-3 relative z-10 group-hover:text-indigo-600 transition-colors">เอกลักษณ์</h3>
          <p class="text-slate-600 leading-relaxed font-medium relative z-10 group-hover:text-indigo-800 transition-colors">“องค์กรแห่งการเรียนรู้ด้านวิชาชีพ”</p>
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
      <div class="g-tall bg-gray-100 rounded-2xl overflow-hidden relative group cursor-pointer zoomable-image" onclick="openLightbox('<?= $img_gallery_tall ?>')">
         <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition z-10"></div>
         <div class="absolute bottom-4 left-4 text-white z-20 opacity-0 group-hover:opacity-100 transition transform translate-y-4 group-hover:translate-y-0">
            <span class="font-bold"></span>
         </div>
         <img src="<?= $img_gallery_tall ?>" alt="" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
         <div class="absolute top-4 right-4 bg-black/50 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition backdrop-blur-sm">
            <span class="material-symbols-outlined text-sm">zoom_in</span>
         </div>
      </div>
      
      <!-- Standard Images -->
      <?php 
      foreach($img_gallery_items as $img): 
      ?>
      <div class="bg-gray-100 rounded-2xl overflow-hidden relative group min-h-[200px] cursor-pointer zoomable-image" onclick="openLightbox('<?= $img ?>')">
          <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition z-10"></div>
          <img src="<?= $img ?>" alt="History Gallery" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-500">
          <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-black/30 text-white p-3 rounded-full opacity-0 group-hover:opacity-100 transition backdrop-blur-md transform scale-50 group-hover:scale-100">
            <span class="material-symbols-outlined">zoom_in</span>
          </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

</main>

<!-- Lightbox Modal -->
<div id="lightbox" class="fixed inset-0 z-[10000] bg-black/95 hidden flex items-center justify-center opacity-0 transition-opacity duration-300" onclick="closeLightbox()">
    <button class="absolute top-6 right-6 text-white/50 hover:text-white transition transform hover:scale-110" onclick="closeLightbox()">
        <span class="material-symbols-outlined text-4xl">close</span>
    </button>
    <img id="lightbox-img" src="" alt="Fullsize" class="max-h-[90vh] max-w-[90vw] object-contain shadow-2xl rounded-lg transform scale-95 transition-transform duration-300" onclick="event.stopPropagation()">
    <div class="absolute bottom-6 left-0 right-0 text-center pointer-events-none">
        <p class="text-white/70 text-sm font-light tracking-widest uppercase">Loei Technical College</p>
    </div>
</div>

<script>
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');

    function openLightbox(src) {
        lightboxImg.src = src;
        lightbox.classList.remove('hidden');
        // Small delay to allow display:block to apply before opacity transition
        setTimeout(() => {
            lightbox.classList.remove('opacity-0');
            lightboxImg.classList.remove('scale-95');
            lightboxImg.classList.add('scale-100');
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.add('opacity-0');
        lightboxImg.classList.remove('scale-100');
        lightboxImg.classList.add('scale-95');
        setTimeout(() => {
            lightbox.classList.add('hidden');
            lightboxImg.src = '';
        }, 300);
        document.body.style.overflow = 'auto';
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
</script>

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