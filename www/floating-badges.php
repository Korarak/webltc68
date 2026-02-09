<?php
include 'condb/condb.php';

// ดึงข้อมูล badges ที่มี status visible = 1 จากฐานข้อมูล
$badges_sql = "SELECT * FROM badges WHERE visible = 1 ORDER BY sort_order";
$badges_result = $mysqli4->query($badges_sql);
$badges = [];

if ($badges_result && $badges_result->num_rows > 0) {
    while ($badge = $badges_result->fetch_assoc()) {
        $badges[] = $badge;
    }
}
?>

<div id="floating-badges-container" style="display: none;">
    <?php 
    $bottom_position = 80; // เริ่มต้นจากระดับความสูง 80px
    // คำนวณระยะห่างคงที่:
    // - Image height: h-16 = 64px
    // - Padding Y: py-3 = 6px (รวมบนล่าง)
    // - ขนาดปุ่มทั้งหมด: 64 + 6 = 70px
    // - ระยะห่างระหว่าง badge: 15px
    // - รวมทั้งสิ้น: 70 + 15 = 85px
    $badge_spacing = 100;
    
    foreach ($badges as $index => $badge) : 
        $badge_id = 'badge-' . htmlspecialchars($badge['id']);
        $has_image = !empty($badge['badge_image']);
        $icon_class = htmlspecialchars($badge['badge_icon']);
        $badge_color = htmlspecialchars($badge['badge_color']);
        $badge_url = htmlspecialchars($badge['badge_url'] ?? '#');
        $badge_name = htmlspecialchars($badge['badge_name']);
        $badge_description = htmlspecialchars($badge['badge_description'] ?? '');
        $animation_delay = ($index * 0.5) . 's';
    ?>
    
    <div class="fixed z-50" id="<?= $badge_id ?>" style="bottom: <?= $bottom_position ?>px; right: 24px;">
        <a href="<?= $badge_url ?>" 
           target="_blank"
           rel="noopener noreferrer"
           class="flex items-center gap-2 text-white rounded-md shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl no-underline group floating-badge-link <?= $has_image ? 'px-3 py-3' : 'px-2 pr-6 py-2' ?>"
           style="background-color: <?= $badge_color ?>;"
           data-badge-id="<?= $badge['id'] ?>"
           title="<?= $badge_name ?>">
            
            <?php if ($has_image) : ?>
                <!-- Image Badge Mode - Show ONLY Image (no shape) -->
                <img src="<?= htmlspecialchars($badge['badge_image']) ?>" 
                     alt="<?= $badge_name ?>"
                     class="h-16 object-cover shadow-lg rounded-md">
            <?php else : ?>
                <!-- Icon Badge Mode - Icon + Text -->
                <div class="bg-white bg-opacity-20 h-16 w-16 flex items-center justify-center rounded-full backdrop-blur-sm shrink-0">
                    <i class="fas <?= $icon_class ?> text-xl"></i>
                </div>
                
                <div class="flex flex-col items-start">
                    <span class="font-bold text-sm whitespace-nowrap"><?= $badge_name ?></span>
                    <?php if (!empty($badge_description)) : ?>
                        <span class="text-[10px] opacity-90 whitespace-normal"><?= $badge_description ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </a>
    </div>

    <?php 
    $bottom_position += $badge_spacing; // เพิ่มระยะห่างคงที่สำหรับปุ่มถัดไป
    endforeach; 
    ?>
</div>

<style>
/* Animation for floating badges */
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

.floating-badge-link {
    animation: float 3s ease-in-out infinite;
}

<?php 
// สร้าง animation delay สำหรับแต่ละ badge
foreach ($badges as $index => $badge) {
    $badge_id = $badge['id'];
    $delay = ($index * 0.5) . 's';
    echo "#badge-{$badge_id} a { animation-delay: {$delay}; }\n";
}
?>

.floating-badge-link:hover {
    animation: none;
}

/* Image badge styling */
.floating-badge-link img {
    transition: transform 0.3s ease;
}

.floating-badge-link:hover img {
    transform: scale(1.1);
}

/* Icon badge styling */
.floating-badge-link i {
    transition: transform 0.3s ease;
}

.floating-badge-link:hover i {
    transform: scale(1.2);
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .floating-badge-link {
        padding: 10px !important;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ตรวจสอบว่าควรแสดง floating badges หรือไม่
    const path = window.location.pathname;
    
    // แสดงเฉพาะบนหน้าแรก (/ หรือ /index.php)
    if (path === '/' || path.endsWith('/index.php')) {
        const container = document.getElementById('floating-badges-container');
        if (container) {
            container.style.display = 'block';
            
            // เพิ่ม event listener สำหรับแต่ละ badge link
            document.querySelectorAll('.floating-badge-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    const badgeId = this.getAttribute('data-badge-id');
                    // บันทึก click ลง localStorage เพื่อหลีกเลี่ยงแจ้งเตือนซ้ำ (optional)
                    localStorage.setItem('badge_clicked_' + badgeId, 'true');
                });
            });
            
            // ซ่อน/แสดง badges เมื่อ scroll
            let lastScrollPosition = 0;
            window.addEventListener('scroll', function() {
                const currentScrollPosition = window.scrollY;
                
                // ถ้า scroll down ให้ซ่อน, scroll up ให้แสดง
                if (currentScrollPosition > lastScrollPosition) {
                    // Scrolling down
                    container.style.opacity = '0';
                    container.style.pointerEvents = 'none';
                } else {
                    // Scrolling up
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                }
                
                lastScrollPosition = currentScrollPosition;
            });
            
            // เพิ่ม transition เพื่อให้การซ่อนแสดงราบรื่น
            container.style.transition = 'opacity 0.3s ease';
        }
    }
    
    // Optional: ลบการแจ้งเตือน (badge notification) หลังจาก 7 วัน
    const today = new Date().toDateString();
    const lastReset = localStorage.getItem('badgeNotificationLastReset');
    
    if (lastReset !== today) {
        localStorage.removeItem('badgeNotifications');
        localStorage.setItem('badgeNotificationLastReset', today);
    }
});
</script>
