<?php
function formatDateTime($datetime) {
    $date = new DateTime($datetime);
    // ใช้เดือนย่อ (ม.ค. แทน มกราคม) เพื่อประหยัดพื้นที่บนการ์ดข่าว
    return $date->format('d') . ' ' . getThaiMonthShort($date->format('m')) . ' ' . ($date->format('Y') + 543) . ' เวลา ' . $date->format('H:i');
}

function getThaiMonthShort($monthNumber) {
    $thaiMonths = [
        '01' => 'ม.ค.', '02' => 'ก.พ.', '03' => 'มี.ค.', '04' => 'เม.ย.',
        '05' => 'พ.ค.', '06' => 'มิ.ย.', '07' => 'ก.ค.', '08' => 'ส.ค.',
        '09' => 'ก.ย.', '10' => 'ต.ค.', '11' => 'พ.ย.', '12' => 'ธ.ค.'
    ];
    return $thaiMonths[$monthNumber] ?? '';
}

// ฟังก์ชันเดิมที่ใช้เดือนเต็ม
function getThaiMonth($monthNumber) {
    $thaiMonths = [
        '01' => 'มกราคม', '02' => 'กุมภาพันธ์', '03' => 'มีนาคม',
        '04' => 'เมษายน', '05' => 'พฤษภาคม', '06' => 'มิถุนายน',
        '07' => 'กรกฎาคม', '08' => 'สิงหาคม', '09' => 'กันยายน',
        '10' => 'ตุลาคม', '11' => 'พฤศจิกายน', '12' => 'ธันวาคม'
    ];
    return $thaiMonths[$monthNumber] ?? '';
}

include('condb/condb.php');

// ดึงเฉพาะหมวดหมู่ที่เปิดแสดง (visible = 1) เรียงตามลำดับ
$categories_result = $mysqli1->query("SELECT * FROM categories WHERE visible = 1 ORDER BY sort_order ASC");

// หา sort_order สูงสุด (ลำดับสุดท้าย)
$max_order_result = $mysqli1->query("SELECT MAX(sort_order) AS max_order FROM categories WHERE visible = 1");
$last_sort_order = $max_order_result->fetch_assoc()['max_order'];
?>

<div class="container mx-auto px-2 sm:px-4 py-8 space-y-12 max-w-7xl"> 
<?php while ($cat = $categories_result->fetch_assoc()): ?>
    <?php
    $cat_id = $cat['id'];
    $cat_name = $cat['name'];
    $sort_order = $cat['sort_order'];

    // ดึงข่าวของแต่ละหมวด
    $stmt = $mysqli1->prepare("SELECT * FROM news WHERE category_id = ? ORDER BY upload_datetime DESC LIMIT 6");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // ถ้าเป็นลำดับสุดท้าย → ใช้ธีม "ข่าวประชาสัมพันธ์ทั่วไป" (มีภาพหน้าปก)
    if ($sort_order == $last_sort_order):
    ?>
    <div class="my-6 p-4 md:p-6 bg-white rounded-2xl shadow-2xl border border-gray-100" data-aos="fade-up">
        <h4 class="text-3xl font-extrabold border-b-4 border-green-500 pb-3 mb-8 flex items-center text-green-700 tracking-wide">
            <i class="fas fa-camera-retro mr-3 text-3xl"></i>
            <span class="truncate"><?= htmlspecialchars($cat_name) ?></span>
        </h4>

        <div class="max-w-7xl mx-auto">
            <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                <?php while ($row = $result->fetch_assoc()):
                    $title = strip_tags($row['title']);
                    $content = strip_tags($row['content']);
                    $title_display = mb_strlen($title, 'UTF-8') > 80 ? mb_substr($title, 0, 80, 'UTF-8') . '...' : $title;
                    $content_display = mb_strlen($content, 'UTF-8') > 150 ? mb_substr($content, 0, 150, 'UTF-8') . '...' : $content;
                    $detail_url = 'app-news/annonce_detail.php?id=' . $row['id'];

                    // สมมติว่ามีฟิลด์ cover_image ในตาราง news
                    $cover_image = !empty($row['cover_image']) 
                        ? '../admin/' . htmlspecialchars($row['cover_image']) 
                        : './images/OG-TAG-Website-Loeitech.jpg'; // ใช้ Placeholder ที่ดีกว่าเดิม
                ?>
                <article class="bg-white border border-gray-200 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col group transform hover:scale-[1.02]">
                    <div class="relative overflow-hidden aspect-video">
                        <img 
                            src="<?= $cover_image ?>" 
                            alt="<?= htmlspecialchars($title) ?>"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                            loading="lazy"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    </div>

                    <div class="p-4 md:p-5 flex flex-col flex-grow">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-green-600 transition duration-300">
                            <?= htmlspecialchars($title_display) ?>
                        </h3>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 mb-4">
                            <span class="flex items-center font-semibold">
                                <i class="fas fa-calendar-alt mr-2 text-green-500"></i>
                                <span class="whitespace-nowrap"><?= formatDateTime($row['upload_datetime']) ?></span>
                            </span>
                            <span class="flex items-center">
                                <i class="fas fa-user-circle mr-2 text-green-500"></i>
                                <span class="truncate"><?= htmlspecialchars($row['uploader']) ?></span>
                            </span>
                        </div>

                        <p class="text-sm text-gray-700 line-clamp-3 mb-4 flex-grow">
                            <?= htmlspecialchars($content_display) ?>
                        </p>

                        <div class="mt-auto text-center">
                            <a href="<?= $detail_url ?>" 
                               class="inline-flex items-center text-sm md:text-base font-semibold text-green-700 border-2 border-green-700 px-4 py-2 rounded-full hover:bg-green-700 hover:text-white transition-all duration-300 shadow-md w-full justify-center">
                                <i class="fas fa-angle-right mr-1.5"></i>
                                ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                </article>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
                    <i class="fas fa-info-circle text-5xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600 text-lg">ไม่พบข้อมูลข่าวสารในขณะนี้</p>
                </div>
            <?php endif; ?>

            <div class="text-center mt-10">
                <a href="app-news/annonce_list.php?category_id=<?= $cat_id ?>" 
                   class="inline-flex items-center text-base md:text-lg font-bold bg-green-600 text-white px-8 py-3 rounded-full shadow-xl hover:bg-green-700 transition-all duration-300 transform hover:scale-[1.02]">
                    <i class="fas fa-list-ul mr-3"></i>
                    ดูข่าวทั้งหมดในหมวด <span class="hidden sm:inline"><?= htmlspecialchars($cat_name) ?></span>
                </a>
            </div>
        </div>
    </div>
     

    <?php else: ?>
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100" data-aos="fade-up">
        <div class="bg-gradient-to-r from-green-700 to-green-800 px-4 md:px-6 py-4 md:py-5">
            <div class="flex items-center">
                <i class="fas fa-bullhorn text-white text-2xl md:text-3xl mr-3"></i>
                <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-wide truncate"><?= htmlspecialchars($cat_name) ?></h2>
            </div>
        </div>

        <div class="p-4 md:p-6">
            <div class="space-y-4">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()):
                        $title = strip_tags($row['title']);
                        $title_display = mb_strlen($title, 'UTF-8') > 100 ? mb_substr($title, 0, 100, 'UTF-8') . '...' : $title;
                        $uploaddate = $row['upload_datetime'];
                        $detail_url = 'app-news/annonce_detail.php?id=' . $row['id'];
                    ?>
                    <div class="bg-white border-b border-gray-200 p-3 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 hover:bg-green-50 transition duration-300 rounded-lg hover:shadow-md">
                        
                        <div class="flex items-start flex-grow min-w-0">
                            <div class="flex-shrink-0 mr-3 mt-1 hidden sm:block">
                                <i class="fas fa-dot-circle text-green-600 text-lg"></i>
                            </div>
                            
                            <div class="min-w-0">
                                <a href="<?= $detail_url ?>" class="text-base md:text-lg font-semibold text-gray-800 hover:text-green-700 transition line-clamp-2">
                                    <span class="sm:hidden"><i class="fas fa-dot-circle text-green-600 mr-2"></i></span>
                                    <?= $title_display ?>
                                </a>
                                <div class="text-xs md:text-sm text-gray-500 mt-1">
                                    <i class="fas fa-calendar-alt mr-1"></i> <?= formatDateTime($uploaddate) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex-shrink-0 w-full md:w-auto text-right md:text-left">
                            <a href="<?= $detail_url ?>" class="inline-flex items-center text-sm font-medium bg-green-600 text-white px-4 py-2 rounded-full hover:bg-green-700 transition shadow-sm w-full justify-center md:w-auto">
                                <i class="fas fa-eye mr-2"></i>
                                ดูรายละเอียด
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 p-4 rounded-xl text-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>ยังไม่มีข่าวในหมวดนี้
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-8 text-center">
                <a href="app-news/annonce_list.php?category_id=<?= $cat_id ?>" class="inline-flex items-center text-base md:text-lg font-bold border-2 border-green-600 text-green-700 bg-white px-6 py-2.5 rounded-full hover:bg-green-600 hover:text-white transition shadow-lg">
                    <i class="fas fa-arrow-alt-circle-right mr-2"></i>
                    ดู<span class="hidden sm:inline"><?= htmlspecialchars($cat_name) ?></span>ทั้งหมด
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endwhile; ?>

<?php $mysqli1->close(); ?>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Custom utility for aspect ratio, often handled by Tailwind JIT or config, but safe to include for compatibility */
.aspect-video {
    aspect-ratio: 16 / 9;
}

/* Optional: Animation for better visual flow if AOS.css is included */
.hover-scale:hover {
    transform: translateY(-2px);
}
</style>

<script>
// ตรวจสอบว่ามี AOS และ Feather Icon Library ถูกโหลดแล้ว
if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 800, easing: 'ease-in-out', once: true });
} else {
    // console.warn("AOS library not loaded.");
}
if (typeof feather !== 'undefined' && typeof feather.replace === 'function') {
    feather.replace();
}
</script>