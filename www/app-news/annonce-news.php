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

// ฟังก์ชันเดิมที่ใช้เดือนเต็ม (เก็บไว้เผื่อใช้)
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

// ดึงหมวดหมู่ทั้งหมดเก็บไว้ใน Array ก่อน
$categories = [];
$categories_result = $mysqli1->query("SELECT * FROM categories WHERE visible = 1 ORDER BY sort_order ASC");
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row;
}

// หา sort_order สูงสุด (ลำดับสุดท้าย) เพื่อใช้ logic เดิม
$max_order_result = $mysqli1->query("SELECT MAX(sort_order) AS max_order FROM categories WHERE visible = 1");
$last_sort_order = $max_order_result->fetch_assoc()['max_order'];
?>

<div class="container mx-auto px-2 sm:px-4 py-8 max-w-7xl"> 
    
    <!-- Tab Navigation -->
    <div class="mb-8 relative group" data-aos="fade-down">
        <!-- Scroll Fade Effect & Arrow (Right) - Visible on mobile/tablet -->
        <div id="scroll-indicator-right" class="absolute inset-y-0 right-0 w-16 bg-gradient-to-l from-white via-white/80 to-transparent z-10 md:hidden flex items-center justify-end pr-1 transition-opacity duration-300">
            <button onclick="scrollTabs('right')" class="bg-white/80 backdrop-blur-sm rounded-full p-2 shadow-md hover:bg-white text-green-600 border border-green-100 animate-pulse">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        
        <!-- Scrollable Container -->
        <div class="flex overflow-x-auto pb-4 pt-2 px-1 gap-3 no-scrollbar scroll-smooth relative z-0" id="newsTabs" role="tablist" onscroll="checkScroll()">
            <?php foreach ($categories as $index => $cat): 
                $isActive = ($index === 0);
                $tabId = 'tab-btn-' . $cat['id'];
                $contentId = 'cat-content-' . $cat['id'];
            ?>
                <button 
                    onclick="switchTab('<?= $cat['id'] ?>')"
                    id="<?= $tabId ?>"
                    role="tab"
                    aria-controls="<?= $contentId ?>"
                    aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                    class="tab-button whitespace-nowrap px-5 py-2.5 rounded-full text-sm md:text-base font-semibold transition-all duration-300 border-2 flex-shrink-0
                    <?= $isActive 
                        ? 'bg-green-600 text-white border-green-600 shadow-md scale-100' 
                        : 'bg-white text-gray-600 border-gray-200 hover:border-green-400 hover:text-green-600' 
                    ?>"
                >
                    <?php if ($cat['sort_order'] == $last_sort_order): ?>
                        <i class="fas fa-bullhorn mr-2"></i>
                    <?php else: ?>
                        <i class="fas fa-newspaper mr-2"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($cat['name']) ?>
                </button>
            <?php endforeach; ?>
            <!-- Spacer to ensure last item isn't covered by fade -->
            <div class="w-8 flex-shrink-0 md:hidden"></div>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="relative min-h-[400px]">
        <?php foreach ($categories as $index => $cat): 
            $cat_id = $cat['id'];
            $cat_name = $cat['name'];
            $sort_order = $cat['sort_order'];
            $isActive = ($index === 0);
            
            // ดึงข่าวของแต่ละหมวด
            $stmt = $mysqli1->prepare("SELECT * FROM news WHERE category_id = ? ORDER BY upload_datetime DESC LIMIT 6");
            $stmt->bind_param("i", $cat_id);
            $stmt->execute();
            $result = $stmt->get_result();
        ?>
            
            <div id="cat-content-<?= $cat_id ?>" 
                 role="tabpanel" 
                 class="tab-content transition-opacity duration-300 <?= $isActive ? 'block opacity-100' : 'hidden opacity-0 absolute top-0 left-0 w-full' ?>">

                <!-- Logic แสดงผลเดิม -->
                <?php if ($sort_order == $last_sort_order): ?>
                    <!-- Layout แบบ Grid สำหรับหมวดสุดท้าย (ประชาสัมพันธ์ทั่วไป) -->
                    <div class="p-4 md:p-6 bg-white rounded-2xl shadow-xl border border-gray-100">
                        <h4 class="text-3xl font-extrabold border-b-4 border-green-500 pb-3 mb-8 flex items-center text-green-700 tracking-wide">
                            <i class="fas fa-camera-retro mr-3 text-3xl"></i>
                            <span class="truncate"><?= htmlspecialchars($cat_name) ?></span>
                        </h4>

                        <div class="max-w-7xl mx-auto">
                            <?php if ($result->num_rows > 0): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                                <?php while ($row = $result->fetch_assoc()):
                                    $news_title = strip_tags($row['title']);
                                    $content = strip_tags($row['content']);
                                    $title_display = mb_strlen($news_title, 'UTF-8') > 80 ? mb_substr($news_title, 0, 80, 'UTF-8') . '...' : $news_title;
                                    $content_display = mb_strlen($content, 'UTF-8') > 150 ? mb_substr($content, 0, 150, 'UTF-8') . '...' : $content;
                                    $detail_url = 'app-news/annonce_detail.php?id=' . $row['id'];
                                    $cover_image = !empty($row['cover_image']) ? '../admin/' . htmlspecialchars($row['cover_image']) : './images/OG-TAG-Website-Loeitech.jpg';
                                ?>
                                <article class="bg-white border border-gray-200 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col group transform hover:scale-[1.02]">
                                    <div class="relative overflow-hidden aspect-video">
                                        <img src="<?= $cover_image ?>" alt="<?= htmlspecialchars($news_title) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" loading="lazy">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                    </div>
                                    <div class="p-4 md:p-5 flex flex-col flex-grow">
                                        <h3 class="text-lg md:text-xl font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-green-600 transition duration-300">
                                            <?= htmlspecialchars($title_display) ?>
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-gray-500 mb-4">
                                            <span class="flex items-center font-semibold"><i class="fas fa-calendar-alt mr-2 text-green-500"></i><?= formatDateTime($row['upload_datetime']) ?></span>
                                            <span class="flex items-center"><i class="fas fa-user-circle mr-2 text-green-500"></i><?= htmlspecialchars($row['uploader']) ?></span>
                                        </div>
                                        <p class="text-sm text-gray-700 line-clamp-3 mb-4 flex-grow"><?= htmlspecialchars($content_display) ?></p>
                                        <div class="mt-auto text-center">
                                            <a href="<?= $detail_url ?>" class="inline-flex items-center text-sm md:text-base font-semibold text-green-700 border-2 border-green-700 px-4 py-2 rounded-full hover:bg-green-700 hover:text-white transition-all duration-300 shadow-md w-full justify-center">
                                                <i class="fas fa-angle-right mr-1.5"></i> ดูรายละเอียด
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
                                <a href="app-news/annonce_list.php?category_id=<?= $cat_id ?>" class="inline-flex items-center text-base md:text-lg font-bold bg-green-600 text-white px-8 py-3 rounded-full shadow-xl hover:bg-green-700 transition-all duration-300 transform hover:scale-[1.02]">
                                    <i class="fas fa-list-ul mr-3"></i> ดูข่าวทั้งหมดในหมวด <span class="hidden sm:inline"><?= htmlspecialchars($cat_name) ?></span>
                                </a>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Layout แบบ List สำหรับหมวดทั่วไป -->
                    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                        <div class="bg-gradient-to-r from-green-700 to-green-800 px-4 md:px-6 py-4 md:py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-bullhorn text-white text-2xl md:text-3xl mr-3"></i>
                                    <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-wide truncate"><?= htmlspecialchars($cat_name) ?></h2>
                                </div>
                                <a href="app-news/annonce_list.php?category_id=<?= $cat_id ?>" class="text-white text-sm hover:underline hidden sm:inline-block">
                                    ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>

                        <div class="p-4 md:p-6">
                            <div class="space-y-4">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()):
                                        $news_title = strip_tags($row['title']);
                                        $title_display = mb_strlen($news_title, 'UTF-8') > 100 ? mb_substr($news_title, 0, 100, 'UTF-8') . '...' : $news_title;
                                        $uploaddate = $row['upload_datetime'];
                                        $detail_url = 'app-news/annonce_detail.php?id=' . $row['id'];
                                    ?>
                                    <div class="bg-white border-b border-gray-200 p-3 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 hover:bg-green-50 transition duration-300 rounded-lg hover:shadow-md group">
                                        <div class="flex items-start flex-grow min-w-0">
                                            <div class="flex-shrink-0 mr-3 mt-1 hidden sm:block">
                                                <i class="fas fa-dot-circle text-green-600 text-lg group-hover:scale-110 transition-transform"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <a href="<?= $detail_url ?>" class="text-base md:text-lg font-semibold text-gray-800 group-hover:text-green-700 transition line-clamp-2">
                                                    <span class="sm:hidden"><i class="fas fa-dot-circle text-green-600 mr-2"></i></span>
                                                    <?= $title_display ?>
                                                </a>
                                                <div class="text-xs md:text-sm text-gray-500 mt-1 flex items-center">
                                                    <i class="fas fa-calendar-alt mr-1"></i> <?= formatDateTime($uploaddate) ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 w-full md:w-auto text-right md:text-left">
                                            <a href="<?= $detail_url ?>" class="inline-flex items-center text-sm font-medium bg-green-100 text-green-800 px-4 py-1.5 rounded-full hover:bg-green-600 hover:text-white transition shadow-sm w-full justify-center md:w-auto">
                                                <i class="fas fa-eye mr-2"></i> รายละเอียด
                                            </a>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-6 rounded-xl text-center">
                                        <i class="fas fa-folder-open text-3xl mb-2 opacity-50"></i>
                                        <p>ยังไม่มีข่าวในหมวดนี้</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-6 text-center sm:hidden">
                                <a href="app-news/annonce_list.php?category_id=<?= $cat_id ?>" class="inline-block text-green-700 font-bold border border-green-700 px-6 py-2 rounded-full hover:bg-green-700 hover:text-white transition">
                                    ดูทั้งหมด
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php $mysqli1->close(); ?>

<!-- Styles for Hide Scrollbar but keep functionality -->
<style>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
/* Animation Classes */
.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function switchTab(catId) {
    // 1. Reset all buttons state
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('bg-green-600', 'text-white', 'border-green-600', 'shadow-lg', 'scale-105');
        btn.classList.add('bg-white', 'text-gray-600', 'border-gray-200', 'hover:border-green-400', 'hover:text-green-600');
        btn.setAttribute('aria-selected', 'false');
    });

    // 2. Hide all contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden', 'opacity-0', 'absolute');
        content.classList.remove('block', 'opacity-100');
    });

    // 3. Activate clicked button
    const activeBtn = document.getElementById('tab-btn-' + catId);
    if(activeBtn) {
        activeBtn.classList.remove('bg-white', 'text-gray-600', 'border-gray-200', 'hover:border-green-400', 'hover:text-green-600');
        activeBtn.classList.add('bg-green-600', 'text-white', 'border-green-600', 'shadow-lg', 'scale-105');
        activeBtn.setAttribute('aria-selected', 'true');
        
        // Scroll button into view if needed (helpful on mobile)
        activeBtn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    // 4. Show corresponding content
    const activeContent = document.getElementById('cat-content-' + catId);
    if(activeContent) {
        activeContent.classList.remove('hidden', 'absolute');
        // Small delay to allow display:block to apply before opacity transition
        setTimeout(() => {
            activeContent.classList.remove('opacity-0');
            activeContent.classList.add('block', 'opacity-100');
        }, 10);
    }
}


function scrollTabs(direction) {
    const container = document.getElementById('newsTabs');
    const scrollAmount = 200; // Adjust scroll distance as needed
    if (direction === 'right') {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
}

function checkScroll() {
    const container = document.getElementById('newsTabs');
    const indicatorRight = document.getElementById('scroll-indicator-right');
    
    // Check if scrolled to the end (allow 5px buffer)
    if (container.scrollLeft + container.clientWidth >= container.scrollWidth - 5) {
        indicatorRight.classList.add('opacity-0', 'pointer-events-none');
    } else {
        indicatorRight.classList.remove('opacity-0', 'pointer-events-none');
    }
}

// Initial check on load
document.addEventListener('DOMContentLoaded', checkScroll);

// Restore original AOS initialization and utility checks
if (typeof AOS !== 'undefined') {
    AOS.init({ duration: 800, easing: 'ease-in-out', once: true });
}
if (typeof feather !== 'undefined' && typeof feather.replace === 'function') {
    feather.replace();
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Custom utility for aspect ratio compatibility */
.aspect-video {
    aspect-ratio: 16 / 9;
}
</style>