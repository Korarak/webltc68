<?php
include 'middleware.php';
include 'db_news.php'; 

// Fetch Stats from News DB
$news_count = $conn->query("SELECT count(*) as c FROM news")->fetch_assoc()['c'];
$files_in_db = 0;
// Check if attachments table exists in this DB (it should)
$chk_att = $conn->query("SHOW TABLES LIKE 'attachments'");
if($chk_att->num_rows > 0) {
    $files_in_db = $conn->query("SELECT count(*) as c FROM attachments")->fetch_assoc()['c'];
}

// Switch to Letter/Carousel DB
include 'db_letter.php'; // Overwrites $conn
$popup_chk = $conn->query("SELECT count(*) as c FROM carousel WHERE slide_show = 1")->fetch_assoc()['c'];
$popup_status = $popup_chk > 0 ? 'Active' : 'Inactive';
$popup_color = $popup_chk > 1 ? 'text-red-500' : ($popup_chk == 1 ? 'text-purple-500' : 'text-gray-400');
?>
<?php ob_start(); ?>

<div class="space-y-8 animate-fade-in-up">
    
    <!-- Welcome Header -->
    <div class="relative bg-gradient-to-r from-blue-700 to-indigo-800 rounded-3xl p-8 text-white shadow-xl overflow-hidden">
        <div class="absolute right-0 top-0 h-full w-1/2 bg-white/5 skew-x-12 transform translate-x-20"></div>
        <div class="relative z-10">
            <h1 class="text-4xl font-extrabold mb-2">ยินดีต้อนรับ, <?= htmlspecialchars($decoded->username) ?> 👋</h1>
            <p class="text-blue-100 text-lg opacity-90">ระบบจัดการเว็บไซต์วิทยาลัยเทคนิคเลย เวอร์ชัน 2.0</p>
            <div class="mt-6 flex gap-3">
                 <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-sm border border-white/10">
                     <i class="fas fa-wifi text-green-400 text-xs"></i> System Online
                 </span>
                 <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-sm border border-white/10">
                     <i class="fas fa-clock text-yellow-400 text-xs"></i> <?= date('d M Y') ?>
                 </span>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">ข่าวกิจกรรม</p>
                <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= number_format($news_count) ?></h3>
                <p class="text-xs text-green-600 mt-2 font-medium flex items-center gap-1"><i class="fas fa-arrow-up"></i> รายการทั้งหมด</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                 <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">ไฟล์ในระบบ</p>
                 <h3 class="text-3xl font-bold text-gray-800 mt-1"><?= number_format($files_in_db) ?>+</h3>
                 <p class="text-xs text-gray-400 mt-2">Attachments</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center text-2xl">
                <i class="fas fa-folder"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                 <p class="text-gray-500 text-sm font-medium uppercase tracking-wider">สถานะ Popup</p>
                 <h3 class="text-3xl font-bold <?= $popup_color ?> mt-1"><?= $popup_status ?></h3>
                 <?php if($popup_chk > 1): ?>
                    <p class="text-xs text-red-500 mt-2 font-bold"><i class="fas fa-exclamation-circle"></i> เปิดเกิน 1 รายการ</p>
                 <?php else: ?>
                    <p class="text-xs text-gray-400 mt-2">หน้าเว็บไซต์ปกติ</p>
                 <?php endif; ?>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-500 flex items-center justify-center text-2xl">
                <i class="fas fa-window-restore"></i>
            </div>
        </div>
    </div>

    <!-- Shortcuts Grid -->
    <div>
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-rocket text-red-500"></i> เมนูลัด (Shortcuts)
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="news_add.php" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl hover:border-blue-500 hover:shadow-lg transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <span class="font-bold text-gray-700 group-hover:text-blue-600">เขียนข่าวใหม่</span>
            </a>

            <a href="file_manage.php" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl hover:border-yellow-500 hover:shadow-lg transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-folder-plus"></i>
                </div>
                <span class="font-bold text-gray-700 group-hover:text-yellow-600">จัดการไฟล์</span>
            </a>

            <a href="carousel_manage.php" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl hover:border-purple-500 hover:shadow-lg transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-images"></i>
                </div>
                <span class="font-bold text-gray-700 group-hover:text-purple-600">ป้ายประชาสัมพันธ์</span>
            </a>
            
            <a href="building_manage.php" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl hover:border-cyan-500 hover:shadow-lg transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-building"></i>
                </div>
                <span class="font-bold text-gray-700 group-hover:text-cyan-600">ข้อมูลาคาร</span>
            </a>
            
             <a href="admin-manage.php" class="group flex flex-col items-center justify-center p-6 bg-white border border-gray-200 rounded-2xl hover:border-gray-500 hover:shadow-lg transition-all cursor-pointer">
                <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-users-cog"></i>
                </div>
                <span class="font-bold text-gray-700 group-hover:text-black">ผู้ดูแลระบบ</span>
            </a>
            
             <div class="group flex flex-col items-center justify-center p-6 bg-gray-50 border border-gray-200 border-dashed rounded-2xl hover:bg-white hover:border-green-500 hover:shadow-lg transition-all cursor-pointer" onclick="window.open('/', '_blank')">
                <div class="w-14 h-14 rounded-full bg-green-50 text-green-600 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition-transform">
                    <i class="fas fa-globe"></i>
                </div>
                <span class="font-bold text-gray-400 group-hover:text-green-600">ดูหน้าเว็บ</span>
            </div>
            
        </div>
    </div>

</div>

<style>
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
