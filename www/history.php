<?php
$title = "ประวัติวิทยาลัย";

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>วิทยาลัยเทคนิคเลย — แนะนำสถาบัน</title>
  <!-- Tailwind CSS CDN removed -->
  <meta name="color-scheme" content="light" />
  <style>
    /* ===== Custom animations & utilities ===== */
    @keyframes float-y {
      0% { transform: translateY(0) }
      50% { transform: translateY(-6px) }
      100% { transform: translateY(0) }
    }
    @keyframes sheen {
      0% { transform: translateX(-120%) skewX(-20deg); opacity: 0.0 }
      50% { opacity: 0.25 }
      100% { transform: translateX(120%) skewX(-20deg); opacity: 0.0 }
    }
    @keyframes pulse-glow {
      0%, 100% { box-shadow: 0 0 0 rgba(16, 185, 129, 0); }
      50% { box-shadow: 0 0 24px rgba(16, 185, 129, .35); }
    }
    .card-float:hover { animation: float-y 1.6s ease-in-out infinite; }
    .btn-sheen { position: relative; overflow: hidden; }
    .btn-sheen::after {
      content: ""; position: absolute; inset: 0; pointer-events: none;
      background: linear-gradient(120deg, transparent 45%, rgba(255,255,255,.45) 50%, transparent 55%);
      transform: translateX(-120%) skewX(-20deg);
    }
    .btn-sheen:hover::after { animation: sheen 950ms ease-in-out 1; }
    .glow { animation: pulse-glow 2.6s ease-in-out infinite; }
    .soft-shadow { box-shadow: 0 10px 30px -12px rgba(0,0,0,.25); }
    .ring-hover:hover { box-shadow: 0 0 0 6px rgba(16,185,129,.12); }
    .timeline-dot { box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.20); }
  </style>
</head>
<body class="bg-neutral-50 text-slate-800 antialiased selection:bg-emerald-200/60 selection:text-emerald-900">
  <!-- Header / Hero (no images) -->
  <header class="relative isolate overflow-hidden">
    <div class="absolute inset-0 opacity-30 bg-gradient-to-br from-emerald-200 via-emerald-100 to-white"></div>
    <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center rounded-full bg-emerald-600/90 px-3 py-1 text-xs font-semibold text-white shadow-md">Loei Technical College</span>
        <span class="text-xs text-emerald-800/80">— since 2481 (1938)</span>
      </div>
      <h1 class="mt-4 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-700 via-emerald-600 to-teal-600 bg-clip-text text-transparent">
        วิทยาลัยเทคนิคเลย (Loei Technical College)
      </h1>
      <p class="mt-3 max-w-3xl text-base sm:text-lg text-slate-700">
        สถาบันการศึกษาด้านอาชีวศึกษาในจังหวัดเลย มุ่งสร้างสมปัญญา เกียรติศักดิ์ และความซื่อสัตย์ในวิชาชีพ — พร้อมปลูกฝังค่านิยม <span class="font-semibold">สีประจำวิทยาลัย: เขียว–ขาว</span> ให้หยั่งรากในทุกผลงานของผู้เรียนและผู้สอน
      </p>
      <div class="mt-6 flex flex-wrap items-center gap-3">
        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-300/80 bg-white px-3 py-2 text-sm font-medium text-emerald-700 soft-shadow ring-hover">
          <span class="inline-block h-3 w-3 rounded-full bg-emerald-600"></span>
          เขียว
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl border border-emerald-300/80 bg-white px-3 py-2 text-sm font-medium text-emerald-700 soft-shadow ring-hover">
          <span class="inline-block h-3 w-3 rounded-full bg-white ring-1 ring-slate-200"></span>
          ขาว
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl border border-yellow-300/80 bg-white px-3 py-2 text-sm font-medium text-yellow-700 soft-shadow ring-hover">
          <span class="inline-block h-3 w-3 rounded-full bg-yellow-400"></span>
          เหลือง: ความสว่าง • ปัญญา
        </span>
        <span class="inline-flex items-center gap-2 rounded-xl border border-blue-300/80 bg-white px-3 py-2 text-sm font-medium text-blue-700 soft-shadow ring-hover">
          <span class="inline-block h-3 w-3 rounded-full bg-blue-600"></span>
          น้ำเงิน: ความสูงศักดิ์ (Noble Rank)
        </span>
      </div>
    </div>
  </header>

  <main class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 pb-20">
    <!-- สีประจำวิทยาลัย & ความหมาย -->
    <section id="colors" class="relative mt-4">
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Green from Yellow + Blue -->
        <article class="group rounded-2xl bg-white p-5 soft-shadow hover:shadow-xl transition-all duration-300 card-float ring-hover">
          <h3 class="text-lg font-semibold text-emerald-700 flex items-center gap-2">
            สีเขียว
            <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">ผสม</span>
          </h3>
          <p class="mt-2 text-sm leading-6 text-slate-600">
            มาจาก <span class="font-medium text-yellow-600">สีเหลือง</span> กับ <span class="font-medium text-blue-600">สีน้ำเงิน</span> ผสมกัน
          </p>
          <div class="mt-4 h-2 w-full rounded-full bg-gradient-to-r from-yellow-400 via-emerald-500 to-blue-600"></div>
        </article>

        <article class="group rounded-2xl bg-white p-5 soft-shadow hover:shadow-xl transition-all duration-300 card-float ring-hover">
          <h3 class="text-lg font-semibold text-yellow-700">สีเหลือง</h3>
          <p class="mt-2 text-sm leading-6 text-slate-600">หมายถึง ความสว่าง • ปัญญา</p>
          <div class="mt-4 h-10 rounded-xl bg-gradient-to-r from-yellow-300 to-amber-400 group-hover:scale-[1.02] transition"></div>
        </article>

        <article class="group rounded-2xl bg-white p-5 soft-shadow hover:shadow-xl transition-all duration-300 card-float ring-hover">
          <h3 class="text-lg font-semibold text-blue-700">สีน้ำเงิน</h3>
          <p class="mt-2 text-sm leading-6 text-slate-600">หมายถึง ความสูงศักดิ์ <span class="text-xs text-slate-500">(Noble Rank)</span></p>
          <div class="mt-4 h-10 rounded-xl bg-gradient-to-r from-blue-400 to-blue-700 group-hover:scale-[1.02] transition"></div>
        </article>

        <article class="group rounded-2xl bg-white p-5 soft-shadow hover:shadow-xl transition-all duration-300 card-float ring-hover">
          <h3 class="text-lg font-semibold text-slate-800">สีขาว</h3>
          <p class="mt-2 text-sm leading-6 text-slate-600">หมายถึง ความซื่อสัตย์ • บริสุทธิ์</p>
          <div class="mt-4 h-10 rounded-xl bg-gradient-to-r from-slate-50 via-white to-slate-100 group-hover:scale-[1.02] transition"></div>
        </article>
      </div>

      <div class="mt-8 rounded-2xl border border-emerald-200 bg-white p-6 soft-shadow">
        <blockquote class="text-lg sm:text-xl leading-8 text-emerald-900">
          <span class="block text-sm font-semibold text-emerald-600">ความหมายโดยสรุป</span>
          <span class="mt-1 block font-bold">“สถาบันสร้างสมปัญญา เกียรติศักดิ์ ความซื่อสัตย์แห่งตน และวิชาชีพ”</span>
        </blockquote>
      </div>
    </section>

    <!-- โลโก้ & ดาวน์โหลด (placeholder; ไม่ใส่รูปตามคำขอ) -->
    <section id="logos" class="mt-16">
      <div class="flex items-center gap-3">
        <h2 class="text-2xl font-bold text-slate-900">ตราวิทยาลัยเทคนิคเลย & โลโก้</h2>
        <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">ไม่มีรูป (placeholder)</span>
      </div>
      <p class="mt-2 text-slate-600">ส่วนนี้เตรียมพื้นที่สำหรับไฟล์โลโก้ (เช่น ตราวิทยาลัย, Loeitech, อาชีวศึกษา) — ขณะนี้เป็นปุ่มตัวอย่างเพื่อจัดวางและทดสอบการโต้ตอบ</p>

      <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="#" class="btn-sheen glow group rounded-2xl bg-emerald-600 px-5 py-4 text-white font-semibold flex items-center justify-between soft-shadow active:scale-[.98] transition">
          <span>Download โลโก้ — ตราวิทยาลัย</span>
          <svg class="h-5 w-5 opacity-90 group-hover:translate-y-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </a>
        <a href="#" class="btn-sheen group rounded-2xl bg-white px-5 py-4 text-emerald-700 font-semibold border border-emerald-200 soft-shadow hover:bg-emerald-50 active:scale-[.98] transition">
          <div class="flex items-center justify-between">
            <span>Download โลโก้ — Loeitech</span>
            <svg class="h-5 w-5 opacity-80 group-hover:translate-y-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          </div>
        </a>
        <a href="#" class="btn-sheen group rounded-2xl bg-white px-5 py-4 text-emerald-700 font-semibold border border-emerald-200 soft-shadow hover:bg-emerald-50 active:scale-[.98] transition">
          <div class="flex items-center justify-between">
            <span>Download โลโก้ — อาชีวศึกษา</span>
            <svg class="h-5 w-5 opacity-80 group-hover:translate-y-0.5 transition" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          </div>
        </a>
      </div>
    </section>

    <!-- ประวัติความเป็นมา -->
    <section id="history" class="mt-16">
      <h2 class="text-2xl font-bold text-slate-900">ประวัติความเป็นมา</h2>

      <!-- Intro narrative card -->
      <article class="mt-4 rounded-2xl bg-white p-6 soft-shadow border border-slate-200/70">
        <p class="text-slate-700 leading-8">
          กระทรวงศึกษาธิการได้ตั้ง “โรงเรียนช่างไม้เลย” ขึ้นและได้เปิดทำการสอน เมื่อ วันที่ 17 กรกฎาคม พ.ศ. 2481 ตั้งอยู่บริเวณริมฝั่งแม่น้ำเลย (หน้าศาลาเทศบาลเมืองเลย) ต่อมาถูกน้ำท่วมกัดเซาะพังไปหมดจึงได้ย้ายมาตั้งที่ใหม่ คือ บริเวณบ้านติ้ว (ซึ่งเป็นสถานที่ตั้งวิทยาลัยเทคนิคเลยในปัจจุบัน) เมื่อเริ่มเปิดสอนครั้งแรกนั้น มีครู 2 คน และมีนักเรียนเพียง 16 คน โดยมี นายเจริญ หัตถกรรม เป็นครูใหญ่ ซึ่งได้เปิดรับนักเรียนที่จบชั้นประถม 4 เข้าเรียน มีหลักสูตร 3 ปี ซึ่งเรียกว่า “อาชีวศึกษาตอนต้น” ทำการสอนวิชาช่างไม้ เมื่อจบการศึกษาเทียบเท่าชั้นมัธยมศึกษาปีที่ 3 ต่อมาเมื่อปี พ.ศ. 2496 ทางกรมอาชีวศึกษา กระทรวงศึกษาธิการ ได้ขยายหลักสูตรการสอนเพิ่มขึ้นอีก 3 ปี เรียกว่า “อาชีวศึกษาตอนปลาย” โดยรับนักศึกษาที่เรียนจบ เมื่อจบเทียบเท่าชั้นมัธยมศึกษาปีที่ 6
        </p>
      </article>

      <!-- Vertical timeline -->
      <div class="mt-10 grid gap-8 lg:grid-cols-[22rem,1fr]">
        <div class="lg:sticky lg:top-6 h-max">
          <div class="rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-700 p-6 text-white soft-shadow">
            <h3 class="text-xl font-bold">เหตุการณ์สำคัญ</h3>
            <p class="mt-1 text-emerald-100/90 text-sm">ไทม์ไลน์การเปลี่ยนแปลงชื่อ หน่วยงาน หลักสูตร และการเปิดแผนกวิชา</p>
            <div class="mt-4 flex flex-wrap gap-2 text-[11px]">
              <span class="rounded-full bg-white/10 px-2 py-1">เริ่มสอน 2481</span>
              <span class="rounded-full bg-white/10 px-2 py-1">สถาปนา 15 ก.ย. 2502</span>
              <span class="rounded-full bg-white/10 px-2 py-1">ยกฐานะเป็นวิทยาลัย 2524</span>
              <span class="rounded-full bg-white/10 px-2 py-1">ขยายระดับ ปวส./ทล.บ.</span>
            </div>
          </div>
        </div>

        <ol class="relative border-s-2 border-emerald-200 pl-6">
          <!-- Each item -->
          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2500</h4>
              <p class="mt-1 text-slate-700 text-sm">ขยายหลักสูตรเป็น “ประโยคอาชีวศึกษาชั้นสูง” รับนักเรียน ม.6 และผู้สำเร็จอาชีวศึกษาตอนปลาย สอนช่างไม้และก่อสร้าง (ตัดอาชีวศึกษาตอนปลายออก)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">26 ส.ค. 2502 → 15 ก.ย. 2502</h4>
              <p class="mt-1 text-slate-700 text-sm">เปลี่ยนชื่อจาก “โรงเรียนช่างไม้เลย” เป็น “โรงเรียนการช่างเลย” และทำพิธีเปิดป้าย 15 กันยายน (นับเป็นวันสถาปนา)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2515–2518</h4>
              <p class="mt-1 text-slate-700 text-sm">เปิดสอนแผนกวิชาช่างยนต์ (ปวช.) และช่างเชื่อมโลหะแผ่น (ปวช.) พร้อมเปิดรอบเช้า–บ่าย 07.30–20.00 น. (เว้นอาทิตย์/วันหยุดราชการ)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2521–2524</h4>
              <p class="mt-1 text-slate-700 text-sm">เปิดสอนแผนกช่างไฟฟ้า (ปวช.), เปลี่ยนชื่อเป็น “โรงเรียนเทคนิคเลย” (2522) และยกฐานะเป็น “วิทยาลัยเทคนิคเลย” (2524)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2525–2531</h4>
              <p class="mt-1 text-slate-700 text-sm">เปิดแผนกเทคนิควิศวกรรมโยธา (ปวท.), ช่างไฟฟ้า (ปวส.), ช่างอิเล็กทรอนิกส์ (ปวส.), ช่างยนต์ (ปวส.)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2533–2539</h4>
              <p class="mt-1 text-slate-700 text-sm">เพิ่มแผนกช่างกลโรงงาน (ปวช. 2536), ช่างก่อสร้าง และเทคนิคโลหะ (ปวส. 2538), เทคนิคการผลิต (ปวส. 2539)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2549–2553</h4>
              <p class="mt-1 text-slate-700 text-sm">เปิดสาขาสถาปัตยกรรม (ปวช. 2549), เทคโนโลยีสารสนเทศ (ปวส. 2550), ตั้ง/ยุบแผนก: สถาปัตยกรรม, โยธา, เทคโนโลยีโทรคมนาคม, พาณิชยการ</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2555–2559</h4>
              <p class="mt-1 text-slate-700 text-sm">ช่างยนต์ (ปวส. ทวิภาคี 2555), เทคโนโลยีสารสนเทศ (ปวช. 2556), เปิดปริญญาตรี ทล.บ.: ก่อสร้าง/โยธา (2557/2564), ไฟฟ้า (2558), ยานยนต์→เครื่องกล (2559/2564)</p>
            </div>
          </li>

          <li class="relative pb-8">
            <span class="absolute -left-[11px] top-1.5 h-3.5 w-3.5 rounded-full bg-emerald-600 timeline-dot"></span>
            <div class="rounded-xl bg-white p-5 border border-slate-200/70 soft-shadow hover:shadow-lg transition">
              <h4 class="font-semibold text-emerald-700">พ.ศ. 2561–2565</h4>
              <p class="mt-1 text-slate-700 text-sm">เปิดเทคโนโลยีคอมพิวเตอร์ (ปวส. 2561), ปวช. อิเล็กทรอนิกส์/แมคคาทอนิกส์ & IT–คอมพิวเตอร์แอนิเมชันและเกมส์ (2562), ก่อตั้งแผนกเทคโนโลยีคอมพิวเตอร์ (2563), IT–นักพัฒนาซอฟต์แวร์ (ปวส. 2563), ปริญญาตรี ทล.บ. เทคโนโลยีการผลิต (2564), ปวส. เทคนิคซ่อมตัวถังและสีรถยนต์ (2564), ปวส. สถาปัตยกรรม (2564), แยกแผนกสถาปัตยกรรมออกจากก่อสร้าง–โยธา (2565)</p>
            </div>
          </li>
        </ol>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="border-t border-slate-200/70 bg-white/80 backdrop-blur">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 text-sm text-slate-600 flex flex-wrap items-center justify-between gap-4">
      <p>© <span id="y"></span> Loei Technical College</p>
      <div class="flex items-center gap-2 text-xs">
        <span class="inline-flex h-3 w-3 rounded-full bg-emerald-600"></span>
        <span>สีประจำวิทยาลัย: เขียว–ขาว</span>
      </div>
    </div>
  </footer>

  <script>
    // ปีปัจจุบัน
    document.getElementById('y').textContent = new Date().getFullYear().toString();
  </script>
</body>
</html>
<?php
// Capture the output and store it in $content
$content = ob_get_clean();

// Include the base template
include 'base.php';
?>