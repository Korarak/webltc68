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
    WHERE pos.position_name IN ('ผู้อำนวยการ', 'รองผู้อำนวยการ')
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
<main class="max-w-7xl mx-auto px-4 py-8 mt-[72px]">

    <!-- ===== Header ===== -->
    <header class="text-center mb-12">
        <h1 class="text-3xl md:text-4xl font-bold text-green-700">
            ผู้บริหารวิทยาลัยเทคนิคเลย
        </h1>
        <p class="mt-2 text-gray-600">ทีมผู้นำที่พร้อมขับเคลื่อนสถาบัน</p>
    </header>

    <!-- ===== ผู้อำนวยการ (Hero Card) ===== -->
    <?php if ($director): ?>
        <section class="flex justify-center mb-16">
            <article
                class="group relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-xl transition-all duration-300 hover:shadow-2xl cursor-pointer person-card"
                data-id="<?= $director['id'] ?>"
                tabindex="0"
                role="button"
                aria-label="ดูรายละเอียด <?= htmlspecialchars($director['fullname']) ?>"
            >
                <div class="aspect-w-3 aspect-h-4 overflow-hidden">
                    <img
                        src="../<?= htmlspecialchars($director['profile_image'] ?: 'uploads/default.png') ?>"
                        alt="<?= htmlspecialchars($director['fullname']) ?>"
                        class="w-full h-96 object-cover transition-transform duration-500 group-hover:scale-105"
                    >
                </div>

                <div class="p-6 text-center">
                    <h2 class="text-2xl font-bold text-gray-900">
                        <?= htmlspecialchars($director['fullname']) ?>
                    </h2>
                    <p class="mt-1 text-lg font-medium text-green-600">
                        <?= htmlspecialchars($director['position_name']) ?>
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        <?= htmlspecialchars($director['department_name']) ?>
                    </p>

                    <div class="mt-4 flex justify-center gap-4 text-sm text-gray-500">
                        <span>📞 <?= htmlspecialchars($director['Tel'] ?: '-') ?></span>
                        <span>✉️ <?= htmlspecialchars($director['E_mail'] ?: '-') ?></span>
                    </div>
                </div>

                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </article>
        </section>
    <?php endif; ?>

    <!-- ===== รองผู้อำนวยการ (Grid) ===== -->
    <?php if (!empty($vice_directors)): ?>
        <section>
            <h2 class="text-2xl font-semibold text-center mb-8 text-gray-800">
                รองผู้อำนวยการ
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($vice_directors as $row): ?>
                    <article
                        class="group relative overflow-hidden rounded-xl bg-white shadow-md transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer person-card"
                        data-id="<?= $row['id'] ?>"
                        tabindex="0"
                        role="button"
                        aria-label="ดูรายละเอียด <?= htmlspecialchars($row['fullname']) ?>"
                    >
                        <div class="aspect-w-1 aspect-h-1 overflow-hidden">
                            <img
                                src="../<?= htmlspecialchars($row['profile_image'] ?: 'uploads/default.png') ?>"
                                alt="<?= htmlspecialchars($row['fullname']) ?>"
                                class="w-full h-110 object-cover transition-transform duration-500 group-hover:scale-110"
                            >
                        </div>

                        <div class="p-4 text-center">
                            <h3 class="font-semibold text-gray-900 truncate">
                                <?= htmlspecialchars($row['fullname']) ?>
                            </h3>
                            <p class="mt-1 text-sm text-green-600">
                                <?= htmlspecialchars($row['position_name']) ?>
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                <?= htmlspecialchars($row['department_name']) ?>
                            </p>

                            <div class="mt-3 flex justify-center gap-3 text-xs text-gray-500">
                                <span><i class="fas fa-phone text-green-600 mt-1 flex-shrink-0"></i> <?= htmlspecialchars($row['Tel'] ?: '-') ?></span>
                                <span><i class="fas fa-envelope text-green-600 mt-1 flex-shrink-0"></i> <?= htmlspecialchars($row['E_mail'] ?: '-') ?></span>
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