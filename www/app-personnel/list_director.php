<?php
$title = "รายชื่อผู้อำนวยการ";
ob_start();
@require '../condb/condb.php';

/* -------------------------------------------------
   ดึงข้อมูลบุคลากร (ผู้อำนวยการ + รองผู้อำนวยการ)
   ------------------------------------------------- */
$query = "
    SELECT p.id, p.fullname, p.profile_image, p.Tel, p.E_mail,
           pos.position_name, d.department_name
    FROM personel_data p
    JOIN positions pos ON p.position_id = pos.id
    JOIN department d   ON p.department_id = d.id
    WHERE p.is_deleted = 0 AND pos.position_name IN ('ผู้อำนวยการ', 'รองผู้อำนวยการ')
    ORDER BY FIELD(pos.position_name, 'ผู้อำนวยการ', 'รองผู้อำนวยการ')
";
$result = $mysqli3->query($query);

$director      = null;
$vice_directors = [];

while ($row = $result->fetch_assoc()) {
    $row['position_name'] === 'ผู้อำนวยการ'
        ? $director = $row
        : $vice_directors[] = $row;
}
?>
<style>
/* Premium Gold/Executive Styles */
.text-gold-gradient {
    background: linear-gradient(to right, #bf953f, #fcf6ba, #b38728, #fbf5b7, #aa771c);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-size: 200% auto;
    animation: shine-text 5s linear infinite;
}
@keyframes shine-text {
    to { background-position: 200% center; }
}

.bg-gold-gradient {
    background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 50%, #b38728 100%);
    background-size: 200% auto;
    animation: gold-flow 5s ease infinite;
}
@keyframes gold-flow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.shine-effect {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 10;
}
.shine-effect::before {
    content: '';
    position: absolute;
    top: 0; left: -150%;
    width: 50%; height: 100%;
    background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
    transform: skewX(-25deg);
    transition: all 0.7s ease;
}
.group:hover .shine-effect::before {
    left: 200%;
    transition: all 0.7s ease;
}
</style>

<main class="max-w-7xl mx-auto px-4 py-8 mt-[72px]">

    <!-- ===== Header ===== -->
    <header class="text-center mb-16 mt-8">
        <div class="inline-block mb-4">
            <span class="bg-gold-gradient text-gray-900 px-4 py-1.5 rounded-full text-xs md:text-sm font-bold tracking-widest shadow-lg uppercase">Executive Board</span>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight leading-tight mb-4 drop-shadow-sm">
            คณะผู้บริหาร<br>
            <span class="text-gold-gradient text-5xl md:text-6xl drop-shadow-md mt-2 inline-block">วิทยาลัยเทคนิคเลย</span>
        </h1>
        <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto font-medium">ทีมผู้นำที่พร้อมขับเคลื่อนองค์กรด้วยวิสัยทัศน์ สู่ความเป็นเลิศทางอาชีวศึกษา</p>
    </header>

    <!-- ===== ผู้อำนวยการ (Hero Card) ===== -->
    <?php if ($director): ?>
        <section class="flex justify-center mb-20 relative px-2">
            <!-- Background Glow -->
            <div class="absolute inset-0 bg-yellow-400/20 blur-3xl max-w-3xl mx-auto rounded-full h-full -z-10 animate-pulse" style="animation-duration: 4s;"></div>

            <article
                class="group relative w-full max-w-4xl overflow-hidden rounded-[2rem] bg-white shadow-2xl transition-all duration-500 hover:shadow-[0_25px_60px_rgba(191,149,63,0.3)] cursor-pointer person-card border border-yellow-200"
                data-id="<?= $director['id'] ?>"
                tabindex="0"
                role="button"
                aria-label="ดูรายละเอียด <?= htmlspecialchars($director['fullname']) ?>"
            >
                <div class="flex flex-col md:flex-row h-full">
                    <!-- Image -->
                    <div class="w-full md:w-2/5 relative overflow-hidden bg-gray-100">
                        <img
                            src="../<?= htmlspecialchars($director['profile_image'] ?: 'uploads/default.png') ?>"
                            alt="<?= htmlspecialchars($director['fullname']) ?>"
                            class="w-full h-[400px] md:h-full object-cover object-top transition-transform duration-700 group-hover:scale-105"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/10 to-transparent md:bg-gradient-to-r md:from-transparent md:to-white/90 pointer-events-none"></div>
                        
                        <!-- Mobile Badge -->
                        <div class="absolute bottom-4 left-4 md:hidden">
                            <span class="bg-gold-gradient text-gray-900 px-4 py-1.5 rounded-full text-sm font-bold shadow-lg">ผู้อำนวยการวิทยาลัย</span>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="w-full md:w-3/5 p-8 md:p-12 flex flex-col justify-center relative bg-white md:bg-gradient-to-l md:from-white md:to-gray-50/80 backdrop-blur-sm">
                        <!-- Shine Effect -->
                        <div class="shine-effect rounded-r-[2rem]"></div>
                        
                        <div class="hidden md:inline-block mb-6 self-start">
                             <div class="flex items-center gap-2 px-5 py-2 rounded-full bg-yellow-50 border border-yellow-200 shadow-sm">
                                <i class="fas fa-crown text-yellow-500"></i>
                                <span class="text-sm font-extrabold text-yellow-700 tracking-wider">ผู้อำนวยการวิทยาลัย</span>
                             </div>
                        </div>

                        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2 group-hover:text-yellow-600 transition-colors duration-300">
                            <?= htmlspecialchars($director['fullname']) ?>
                        </h2>
                        <p class="text-xl md:text-2xl font-bold bg-clip-text text-transparent bg-gold-gradient mb-3 drop-shadow-sm">
                            <?= htmlspecialchars($director['position_name']) ?>
                        </p>
                        <p class="text-gray-500 mb-8 font-semibold">
                            <?= htmlspecialchars($director['department_name']) ?>
                        </p>

                        <div class="space-y-4 relative z-20">
                            <div class="flex items-center gap-4 text-gray-600 p-4 rounded-2xl bg-gray-50 border border-gray-100 group-hover:border-yellow-200 group-hover:bg-yellow-50/50 transition-all duration-300 shadow-sm">
                                <div class="w-12 h-12 rounded-full bg-gold-gradient flex items-center justify-center text-white shadow-md flex-shrink-0 text-lg">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <span class="font-bold text-gray-700 text-[15px]"><?= htmlspecialchars($director['Tel'] ?: '-') ?></span>
                            </div>
                            <div class="flex items-center gap-4 text-gray-600 p-4 rounded-2xl bg-gray-50 border border-gray-100 group-hover:border-yellow-200 group-hover:bg-yellow-50/50 transition-all duration-300 shadow-sm">
                                <div class="w-12 h-12 rounded-full bg-gold-gradient flex items-center justify-center text-white shadow-md flex-shrink-0 text-lg">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <span class="font-bold text-gray-700 text-[15px] truncate"><?= htmlspecialchars($director['E_mail'] ?: '-') ?></span>
                            </div>
                        </div>
                        
                        <!-- Hover Action Hint -->
                        <div class="absolute bottom-6 right-8 opacity-0 translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-500 flex items-center gap-2 text-yellow-600 font-bold text-sm bg-yellow-50 px-4 py-2 rounded-full border border-yellow-100 shadow-sm z-20">
                            ดูรายละเอียด <i class="fas fa-chevron-right ml-1 text-xs"></i>
                        </div>
                    </div>
                </div>
            </article>
        </section>
    <?php endif; ?>

    <!-- ===== รองผู้อำนวยการ (Grid) ===== -->
    <?php if (!empty($vice_directors)): ?>
        <section class="relative mb-12">
            <div class="flex items-center justify-center gap-4 mb-12">
                <div class="h-px bg-gradient-to-r from-transparent to-yellow-400 w-16 md:w-32"></div>
                <h2 class="text-3xl md:text-3xl font-extrabold text-gray-800 tracking-wide flex items-center gap-3 text-center drop-shadow-sm">
                    <i class="fas fa-star text-gold-gradient text-xl md:text-2xl"></i>
                    รองผู้อำนวยการ
                    <i class="fas fa-star text-gold-gradient text-xl md:text-2xl"></i>
                </h2>
                <div class="h-px bg-gradient-to-l from-transparent to-yellow-400 w-16 md:w-32"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($vice_directors as $row): ?>
                    <article
                        class="group relative overflow-hidden rounded-2xl bg-gray-900 shadow-xl border border-gray-200/50 transition-all duration-500 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(191,149,63,0.25)] hover:border-yellow-400/80 cursor-pointer person-card"
                        data-id="<?= $row['id'] ?>"
                        tabindex="0"
                        role="button"
                        aria-label="ดูรายละเอียด <?= htmlspecialchars($row['fullname']) ?>"
                    >
                        <div class="w-full relative overflow-hidden aspect-[3/4]">
                            <img
                                src="../<?= htmlspecialchars($row['profile_image'] ?: 'uploads/default.png') ?>"
                                alt="<?= htmlspecialchars($row['fullname']) ?>"
                                class="w-full h-full object-cover object-top transition-transform duration-700 group-hover:scale-110 opacity-90 group-hover:opacity-100"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-80 group-hover:opacity-90 transition-opacity duration-300"></div>
                            <div class="shine-effect"></div>
                        </div>

                        <!-- Card Content -->
                        <div class="absolute bottom-0 w-full p-6 flex flex-col justify-end translate-y-12 group-hover:-translate-y-4 transition-transform duration-500 ease-out z-20">
                             <div>
                                <p class="text-gold-gradient text-xs font-bold tracking-widest uppercase mb-1">
                                    <?= htmlspecialchars($row['position_name']) ?>
                                </p>
                                <h3 class="text-xl font-bold text-white drop-shadow-md line-clamp-2 group-hover:text-yellow-300 transition-colors duration-300">
                                    <?= htmlspecialchars($row['fullname']) ?>
                                </h3>
                                <p class="text-gray-300 text-sm mt-1 mb-2 font-medium drop-shadow-sm">
                                    <?= htmlspecialchars($row['department_name']) ?>
                                </p>
                             </div>

                             <div class="opacity-0 h-0 group-hover:opacity-100 group-hover:h-[80px] overflow-hidden transition-all duration-500 pt-3 border-t border-white/20 mt-2">
                                <div class="flex items-center gap-3 text-sm text-gray-200 mb-2 mt-1">
                                    <div class="w-7 h-7 flex items-center justify-center bg-white/20 rounded-full text-yellow-400 backdrop-blur-md"><i class="fas fa-phone-alt text-[10px]"></i></div>
                                    <span class="font-medium"><?= htmlspecialchars($row['Tel'] ?: '-') ?></span>
                                </div>
                                <div class="flex items-center gap-3 text-sm text-gray-200 mt-2">
                                    <div class="w-7 h-7 flex items-center justify-center bg-white/20 rounded-full text-yellow-400 backdrop-blur-md"><i class="fas fa-envelope text-[10px]"></i></div>
                                    <span class="truncate font-medium"><?= htmlspecialchars($row['E_mail'] ?: '-') ?></span>
                                </div>
                             </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<!-- ==================== MODAL ==================== -->
<div id="personModal"
     class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 backdrop-blur-sm"
     aria-hidden="true"
     role="dialog"
     aria-modal="true">
    <div class="relative w-full max-w-3xl mx-4 bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
         onclick="event.stopPropagation();">

        <button
            class="absolute top-4 right-4 text-gray-500 hover:text-red-600 transition"
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
/* ---------- Modal Helpers ---------- */
function closePersonModal() {
    const modal = document.getElementById('personModal');
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.getElementById('personModalContent').innerHTML = `
        <div class="flex justify-center items-center h-40">
            <svg class="animate-spin h-10 w-10 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
        </div>`;
}

/* Close when clicking backdrop */
document.getElementById('personModal').addEventListener('click', e => {
    if (e.target.id === 'personModal') closePersonModal();
});

/* Close with ESC */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !document.getElementById('personModal').classList.contains('hidden')) {
        closePersonModal();
    }
});

/* ---------- Card Click → Load Detail ---------- */
document.querySelectorAll('.person-card').forEach(card => {
    const openModal = () => {
        const id = card.dataset.id;
        const modal = document.getElementById('personModal');
        const content = document.getElementById('personModalContent');

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        modal.scrollIntoView({behavior: 'smooth', block: 'center'});

        fetch(`../api/person_detail.php?id=${id}`)
            .then(r => r.text())
            .then(html => content.innerHTML = html)
            .catch(() => content.innerHTML = `
                <p class="text-center text-red-600 font-medium">เกิดข้อผิดพลาดในการโหลดข้อมูล</p>
            `);
    };

    card.addEventListener('click', openModal);
    card.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openModal(); }
    });
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../base.php';
?>