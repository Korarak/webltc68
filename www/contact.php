<?php
$title = "ติดต่อ";

// Start output buffering
ob_start();
?>
<!-- Contact Section -->
<section class="mt-[84px] px-2 sm:px-4 lg:px-6">
  <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-emerald-200/70 max-w-7xl mx-auto w-full">

    <!-- top glow bar -->
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600 animate-pulse"></div>

    <div class="p-4 sm:p-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-2xl font-extrabold tracking-tight text-emerald-700">
          ติดต่อวิทยาลัยเทคนิคเลย
        </h2>

        <!-- actions (desktop) -->
        <div class="hidden sm:flex items-center gap-2">
          <button type="button"
             onclick="copyText('0801502400', this)"
             class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white font-medium shadow hover:shadow-lg active:scale-95 btn-sheen">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1M8 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M8 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m0 0h2a2 2 0 0 1 2 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
            </svg>
            <span>คัดลอกเบอร์</span>
          </button>
          <a href="https://maps.google.com/?q=วิทยาลัยเทคนิคเลย 272 ถ.เจริญรัฐ ต.กุดป่อง อ.เมือง จ.เลย 42000"
             target="_blank" rel="noopener"
             class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-emerald-700 font-medium border border-emerald-200 shadow hover:bg-emerald-50 hover:shadow-lg active:scale-95 btn-sheen">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 21s-6-5.33-6-10a6 6 0 1 1 12 0c0 4.67-6 10-6 10z"/><circle cx="12" cy="11" r="2"/>
            </svg>
            <span>เปิดแผนที่</span>
          </a>
        </div>
      </div>

      <!-- details -->
      <div class="mt-5 grid gap-4">
        <!-- address -->
        <div class="group flex flex-wrap sm:flex-nowrap items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-emerald-50/40 hover:border-emerald-200 transition">
          <div class="shrink-0 mt-0.5">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 21s-6-5.33-6-10a6 6 0 1 1 12 0c0 4.67-6 10-6 10z"/><circle cx="12" cy="11" r="2"/>
              </svg>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-700">ที่อยู่</div>
            <p class="mt-0.5 text-slate-700 break-words">
              ตั้งอยู่เลขที่ 272 ถ.เจริญรัฐ ต.กุดป่อง อ.เมือง จ.เลย รหัสไปรษณีย์ 42000
            </p>
          </div>
          <button type="button"
                  class="ml-auto mt-2 sm:mt-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-white hover:shadow active:scale-95"
                  onclick="copyText('272 ถ.เจริญรัฐ ต.กุดป่อง อ.เมือง จ.เลย 42000', this)">
            คัดลอก
          </button>
        </div>

        <!-- phone -->
        <!-- phone -->
        <div class="group flex flex-wrap sm:flex-nowrap items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-emerald-50/40 hover:border-emerald-200 transition">
          <div class="shrink-0 mt-0.5">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.86 19.86 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.86 19.86 0 0 1 2.08 4.18 2 2 0 0 1 4.07 2h3a2 2 0 0 1 2 1.72c.12.86.3 1.7.54 2.5a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.58-1.07a2 2 0 0 1 2.11-.45c.8.24 1.64.42 2.5.54A2 2 0 0 1 22 16.92z"/>
              </svg>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-700">โทรศัพท์</div>
            <p class="mt-0.5 text-slate-700 break-words">080-1502400</p>
          </div>
          <button type="button"
                  class="ml-auto mt-2 sm:mt-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-white hover:shadow active:scale-95"
                  onclick="copyText('0801502400', this)">
            คัดลอก
          </button>
        </div>

        <!-- website -->
        <!-- website -->
        <div class="group flex flex-wrap sm:flex-nowrap items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-emerald-50/40 hover:border-emerald-200 transition">
          <div class="shrink-0 mt-0.5">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 4 6 4 9s-1.5 6.5-4 9c-2.5-2.5-4-6-4-9s1.5-6.5 4-9z"/>
              </svg>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-700">เว็บไซต์</div>
            <p class="mt-0.5 text-blue-700 underline underline-offset-2 break-words">www.loeitech.ac.th</p>
          </div>
          <button type="button"
                  class="ml-auto mt-2 sm:mt-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-white hover:shadow active:scale-95"
                  onclick="copyText('https://www.loeitech.ac.th', this)">
            คัดลอกลิงก์
          </button>
        </div>

        <!-- email -->
        <!-- email -->
        <div class="group flex flex-wrap sm:flex-nowrap items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-emerald-50/40 hover:border-emerald-200 transition">
          <div class="shrink-0 mt-0.5">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/>
              </svg>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-700">อีเมล</div>
            <p class="mt-0.5 text-blue-700 underline underline-offset-2 break-words">loei@loeitech.mail.go.th</p>
          </div>
          <button type="button"
                  class="ml-auto mt-2 sm:mt-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-white hover:shadow active:scale-95"
                  onclick="copyText('loei@loeitech.mail.go.th', this)">
            คัดลอกอีเมล
          </button>
        </div>

        <!-- facebook -->
        <!-- facebook -->
        <div class="group flex flex-wrap sm:flex-nowrap items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-emerald-50/40 hover:border-emerald-200 transition">
          <div class="shrink-0 mt-0.5">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-white shadow">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M22 12a10 10 0 1 0-11.6 9.9v-7h-2.2v-2.9h2.2V9.5c0-2.2 1.3-3.4 3.3-3.4.96 0 1.96.17 1.96.17v2.2h-1.1c-1.08 0-1.41.67-1.41 1.36v1.64h2.4l-.38 2.9h-2.02v7A10 10 0 0 0 22 12z"/>
              </svg>
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold text-slate-700">Facebook</div>
            <p class="mt-0.5 text-blue-700 underline underline-offset-2 break-words">facebook.com/www.loeitech.ac.th</p>
          </div>
          <button type="button"
                  class="ml-auto mt-2 sm:mt-0 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-white hover:shadow active:scale-95"
                  onclick="copyText('https://www.facebook.com/www.loeitech.ac.th', this)">
            คัดลอกลิงก์
          </button>
        </div>
      </div>

      <!-- map -->
      <div class="mt-6">
        <div class="relative w-full overflow-hidden rounded-xl shadow-md ring-1 ring-slate-200">
          <div class="relative pt-[56.25%]">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d5505.390275998267!2d101.72721397643262!3d17.473764500308633!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x312147bd5382be3b%3A0x8c4341dd412a885c!2z4Lin4Li04LiX4Lii4Liy4Lil4Lix4Lii4LmA4LiX4LiE4LiZ4Li04LiE4LmA4Lil4LiiIExvZWkgVGVjaG5pY2FsIENvbGxlZ2U!5e1!3m2!1sth!2sth!4v1753651918500!5m2!1sth!2sth"
              class="absolute inset-0 h-full w-full"
              style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>
        </div>
        <!-- actions (mobile) -->
        <div class="mt-4 flex sm:hidden gap-2">
          <button type="button"
             onclick="copyText('0801502400', this)"
             class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white font-medium shadow hover:shadow-lg active:scale-95 btn-sheen">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-1M8 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M8 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m0 0h2a2 2 0 0 1 2 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
            </svg>
            คัดลอกเบอร์
          </button>
          <a href="https://maps.google.com/?q=วิทยาลัยเทคนิคเลย 272 ถ.เจริญรัฐ ต.กุดป่อง อ.เมือง จ.เลย 42000"
             target="_blank" rel="noopener"
             class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2 text-emerald-700 font-medium border border-emerald-200 shadow hover:bg-emerald-50 hover:shadow-lg active:scale-95 btn-sheen">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 21s-6-5.33-6-10a6 6 0 1 1 12 0c0 4.67-6 10-6 10z"/><circle cx="12" cy="11" r="2"/>
            </svg>
            เปิดแผนที่
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- small utilities for sheen & copy -->
<style>
  .btn-sheen { position: relative; overflow: hidden; }
  .btn-sheen::after{
    content:""; position:absolute; inset:0; pointer-events:none;
    background: linear-gradient(120deg, transparent 45%, rgba(255,255,255,.45) 50%, transparent 55%);
    transform: translateX(-120%) skewX(-20deg);
  }
  .btn-sheen:hover::after{ animation: sheen 950ms ease-in-out 1; }
  @keyframes sheen{
    0%{ transform: translateX(-120%) skewX(-20deg); opacity:0 }
    50%{ opacity:.25 }
    100%{ transform: translateX(120%) skewX(-20deg); opacity:0 }
  }
</style>

<script>
  function copyText(text, btn){
    if (!navigator.clipboard) { return; }
    const original = btn.textContent;
    navigator.clipboard.writeText(text).then(()=>{
      btn.textContent = 'คัดลอกแล้ว ✓';
      btn.classList.add('bg-emerald-50','border-emerald-200','text-emerald-700');
      setTimeout(()=>{
        btn.textContent = original;
        btn.classList.remove('bg-emerald-50','border-emerald-200','text-emerald-700');
      }, 1200);
    });
  }
</script>

<?php
$content = ob_get_clean();
include 'base.php';
?>
