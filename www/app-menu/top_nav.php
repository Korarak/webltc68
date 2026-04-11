<?php
//app-menu/top_nav.php
?>
<?php 
// เชื่อมต่อฐานข้อมูล
@include 'condb/condb.php'; 

// ฟังก์ชันสำหรับจัดการ URL
function normalize_url_nav($url) { 
  $url = trim($url ?? '');
  if ($url === '' || $url === '#') return '#';
  if (preg_match('~^https?://~i', $url)) return $url;
  return '/' . ltrim($url, '/');
}

// ตรวจสอบการเชื่อมต่อ
if (!isset($mysqli4) || !$mysqli4 instanceof mysqli) {
    $mainMenusResult = (object) ['num_rows' => 0]; 
} else {
    $mainMenusQuery = "SELECT * FROM main_menus WHERE visible = 1 AND position_type IN ('topnav', 'both') ORDER BY menu_order";
    $mainMenusResult = $mysqli4->query($mainMenusQuery);
}

// Mobile Menu Query
$mobileMenusResult = $mainMenusResult; 
if (isset($mysqli4) && $mysqli4 instanceof mysqli) {
    $mobileMenusQuery = "SELECT * FROM main_menus WHERE visible = 1 AND position_type IN ('topnav', 'both') ORDER BY menu_order";
    $mobileMenusResult = $mysqli4->query($mobileMenusQuery);
}
?>

<!-- Navbar Container -->
<nav class="fixed top-4 left-4 right-4 z-[9999] bg-white/80 backdrop-blur-2xl border border-white/50 shadow-[0_8px_32px_rgba(0,0,0,0.05)] rounded-2xl transition-all duration-500 sidebar-responsive hover:shadow-[0_8px_32px_rgba(16,185,129,0.1)]">
  <div class="max-w-full mx-auto px-2 py-2 lg:px-3 xl:px-4 2xl:px-6 flex items-center justify-between flex-nowrap">

    <!-- Logo Section -->
    <div class="flex items-center space-x-3 group cursor-pointer relative z-[10001] shrink-0">
      <a href="/" class="flex items-center space-x-2 lg:space-x-3 group/logo">
        <div class="relative p-1 bg-gradient-to-br from-white to-emerald-50 rounded-xl shadow-inner border border-white/60 group-hover/logo:scale-105 transition duration-500 overflow-hidden shrink-0">
             <!-- Glow Effect -->
             <div class="absolute inset-0 bg-emerald-400 opacity-0 group-hover/logo:opacity-20 blur-md transition duration-500"></div>
             <img src="/svg/loeitech-logo.png" alt="Logo" class="relative z-10 h-7 w-7 lg:h-9 lg:w-9 object-contain drop-shadow-sm" />
        </div>
        <div class="leading-tight flex flex-col min-w-0">
          <span class="block font-black tracking-wide text-emerald-900 text-[12px] lg:text-[clamp(11px,1vw,16px)] group-hover/logo:text-emerald-600 transition duration-300 truncate" style="text-shadow: 0 1px 2px rgba(255,255,255,0.8);">
            วิทยาลัยเทคนิคเลย
          </span>
          <span class="text-[9px] lg:text-[clamp(10px,0.8vw,11px)] text-slate-500 font-medium tracking-[0.1em] lg:tracking-[0.15em] uppercase group-hover/logo:tracking-[0.2em] transition-all duration-500 truncate">
            Loei Technical College
          </span>
        </div>
      </a>
    </div>

    <!-- Desktop Menu (Squeezed to prevent pushing social icons out) -->
    <div class="hidden xl:flex justify-center flex-grow mx-1 2xl:mx-4 items-center gap-x-0 2xl:gap-x-1" id="menu">
      <?php
      if ($mainMenusResult && $mainMenusResult->num_rows > 0) {
        $mainMenusResult->data_seek(0);
        while ($mainMenu = $mainMenusResult->fetch_assoc()) {
          $menuId = $mainMenu['menu_id'];
          $menuName = $mainMenu['menu_name'];
          $menuLink = $mainMenu['menu_link'];
          $isDropdown = $mainMenu['is_dropdown'];
          $target = ($mainMenu['target_blank'] == 1) ? 'target="_blank"' : '';

          $subMenuQuery = "SELECT * FROM sub_main_menus WHERE menu_id = $menuId AND visible = 1 AND position_type IN ('topnav', 'both') ORDER BY submenu_order ASC"; 
          $subMenuResult = $mysqli4->query($subMenuQuery);

          if ($isDropdown && $subMenuResult && $subMenuResult->num_rows > 0) {
            // Dropdown Menu Item
            echo '<div class="relative group/menu shrink-0">';
            echo '<button id="menu-btn-' . $menuId . '" class="flex items-center px-1 py-2 lg:px-1.5 2xl:px-4 text-[clamp(10px,0.75vw,13px)] whitespace-nowrap font-bold text-slate-600 rounded-xl hover:text-emerald-700 transition duration-300 relative overflow-hidden group-hover/menu:bg-white/50 shrink-0">';
            echo '<span class="relative z-10">' . htmlspecialchars($menuName) . '</span>';
            echo '<i class="fas fa-chevron-down text-[9px] ml-1 transition-transform duration-300 group-hover/menu:rotate-180 text-slate-400 group-hover/menu:text-emerald-500 relative z-10"></i>';
            // Hover Underline
            echo '<span class="absolute bottom-1 left-2 right-2 h-0.5 bg-gradient-to-r from-emerald-400 to-teal-500 transform scale-x-0 group-hover/menu:scale-x-100 transition-transform duration-300 origin-center"></span>';
            echo '</button>';
            
            // Dropdown Content
            echo '<div id="dropdown-' . $menuId . '" class="absolute left-1/2 transform -translate-x-1/2 top-full mt-2 bg-white/90 backdrop-blur-xl rounded-2xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.1)] border border-white/60 p-2 min-w-[220px] 
                         opacity-0 invisible scale-95 origin-top 
                         group-hover/menu:opacity-100 group-hover/menu:visible group-hover/menu:scale-100 transition-all duration-300 z-[10000]">';
            
            // Arrow
            echo '<div class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-3 h-3 bg-white rotate-45 border-l border-t border-white/60 clip-path-polygon"></div>';

            while ($subMenu = $subMenuResult->fetch_assoc()) {
              $subTarget = ($subMenu['target_blank'] == 1) ? 'target="_blank"' : '';
              echo '<a href="' . htmlspecialchars(normalize_url_nav($subMenu['submenu_link'])) . '" ' . $subTarget . ' class="group/item flex items-center px-4 py-2.5 text-sm text-slate-600 hover:text-emerald-800 hover:bg-emerald-50/50 rounded-xl transition duration-200 whitespace-nowrap">';
              // Icon dot
              echo '<span class="flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 text-emerald-400 mr-3 shrink-0 group-hover/item:bg-emerald-100 group-hover/item:text-emerald-600 transition-colors duration-200 shadow-sm">';
              echo '<i class="fas fa-arrow-right text-[10px] transform -translate-x-1 opacity-0 group-hover/item:opacity-100 group-hover/item:translate-x-0 transition-all duration-300"></i>';
              echo '</span>';
              
              echo '<span class="font-medium whitespace-nowrap">' . htmlspecialchars($subMenu['submenu_name']) . '</span>';
              echo '</a>';
            }
            echo '</div></div>';
          } else {
            // Single Menu Item
            echo '<a href="' . htmlspecialchars(normalize_url_nav($menuLink)) . '" ' . $target . ' class="relative group/link px-1 py-2 lg:px-1.5 2xl:px-4 text-[clamp(10px,0.75vw,13px)] whitespace-nowrap font-bold text-slate-600 rounded-xl hover:text-emerald-700 transition duration-300 overflow-hidden hover:bg-white/50 shrink-0">';
            echo '<span class="relative z-10">' . htmlspecialchars($menuName) . '</span>';
            echo '<span class="absolute bottom-1 left-2 right-2 h-0.5 bg-gradient-to-r from-emerald-400 to-teal-500 transform scale-x-0 group-hover/link:scale-x-100 transition-transform duration-300 origin-center"></span>';
            echo '</a>';
          }
        }
      }
      ?>
    </div>

    <!-- Social Icons: Floating Pills -->
    <div class="hidden lg:flex absolute left-1/2 -translate-x-1/2 xl:static xl:translate-x-0 items-center gap-0.5 bg-white/60 backdrop-blur-md px-1 py-1 rounded-full border border-white/50 shadow-inner shrink-0">
      <a href="https://www.loeitech.ac.th/mikrotik/?lang=en" target="_blank" class="w-6 h-6 2xl:w-7 2xl:h-7 flex items-center justify-center rounded-full bg-white text-blue-600 shadow-sm hover:scale-110 hover:-translate-y-1 transition duration-300 border border-slate-100 group relative">
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-800 text-white text-[10px] py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap pointer-events-none">Mikrotik</span>
        <img src="https://www.loeitech.ac.th/mikrotik/images/logo/Mikrotik--Streamline-Simple-Icons.svg" alt="Mikrotik" class="w-2.5 h-2.5 2xl:w-3 2xl:h-3 opacity-80 group-hover:opacity-100">
      </a>
      <a href="https://facebook.com/www.loeitech.ac.th" target="_blank" class="w-6 h-6 2xl:w-7 2xl:h-7 flex items-center justify-center rounded-full bg-white text-blue-600 shadow-sm hover:scale-110 hover:-translate-y-1 transition duration-300 border border-slate-100">
        <i class="fab fa-facebook-f text-[9px] 2xl:text-[11px]"></i>
      </a>
      <a href="https://www.youtube.com/@loeitechnicalcollege1556" target="_blank" class="w-6 h-6 2xl:w-7 2xl:h-7 flex items-center justify-center rounded-full bg-white text-red-600 shadow-sm hover:scale-110 hover:-translate-y-1 transition duration-300 border border-slate-100">
        <i class="fab fa-youtube text-[9px] 2xl:text-[11px]"></i>
      </a>
      <a href="https://www.tiktok.com/@businessloeitech" target="_blank" class="w-6 h-6 2xl:w-7 2xl:h-7 flex items-center justify-center rounded-full bg-white text-black shadow-sm hover:scale-110 hover:-translate-y-1 transition duration-300 border border-slate-100">
        <i class="fab fa-tiktok text-[9px] 2xl:text-[11px]"></i>
      </a>
      <div class="w-px h-3 bg-slate-200 mx-0.5"></div>
      <a href="https://loeitech.appedr.com/edr/login.do" target="_blank" class="w-6 h-6 2xl:w-7 2xl:h-7 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 shadow-sm hover:bg-emerald-500 hover:text-white hover:scale-110 hover:-translate-y-1 transition duration-300">
        <img src="/svg/EDR.png" alt="EDR" class="w-2.5 h-2.5 2xl:w-3 2xl:h-3 object-contain">
      </a>
    </div>

    <!-- Mobile Toggle -->
    <div class="flex items-center space-x-2">
      <button id="menu-toggle" class="xl:hidden p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition duration-300 focus:outline-none active:scale-95">
        <div class="w-6 h-5 flex flex-col justify-between relative transform transition-all duration-300">
            <span class="w-full h-0.5 bg-current rounded-full origin-left transition-all duration-300" id="bar1"></span>
            <span class="w-full h-0.5 bg-current rounded-full transition-all duration-300 opacity-100" id="bar2"></span>
            <span class="w-full h-0.5 bg-current rounded-full origin-left transition-all duration-300" id="bar3"></span>
        </div>
      </button>
    </div>
  </div>

  <!-- Mobile Menu Container -->
  <div id="mobile-menu" class="hidden border-t border-white/20 bg-white/95 backdrop-blur-xl rounded-b-2xl overflow-hidden shadow-2xl transition-all duration-500 origin-top transform">
    <div class="px-4 py-4 space-y-2 max-h-[75vh] overflow-y-auto custom-scrollbar">
    <?php
    if ($mobileMenusResult && $mobileMenusResult->num_rows > 0) {
      $mobileMenusResult->data_seek(0);
      while ($mainMenu = $mobileMenusResult->fetch_assoc()) {
        $menuId = $mainMenu['menu_id'];
        $menuName = $mainMenu['menu_name'];
        $menuLink = $mainMenu['menu_link'];
        $isDropdown = $mainMenu['is_dropdown'];
        $target = ($mainMenu['target_blank'] == 1) ? 'target="_blank"' : '';

        $subMenuQuery = "SELECT * FROM sub_main_menus WHERE menu_id = $menuId AND visible = 1 AND position_type IN ('topnav', 'both') ORDER BY submenu_order ASC"; 
        $subMenuResult = $mysqli4->query($subMenuQuery);

        if ($isDropdown && $subMenuResult && $subMenuResult->num_rows > 0) {
          echo '<div class="bg-slate-50/50 rounded-xl overflow-hidden border border-slate-100">';
          echo '<button onclick="toggleMobileDropdown(\'mobile-dropdown-' . $menuId . '\', this)" class="w-full text-left font-bold text-base text-slate-700 px-5 py-3 hover:bg-white hover:text-emerald-700 flex justify-between items-center transition duration-200 group">';
          echo '<span>' . htmlspecialchars($menuName) . '</span>';
          echo '<div class="w-6 h-6 rounded-full bg-white flex items-center justify-center shadow-sm group-hover:bg-emerald-100 transition-colors">';
          echo '<i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-emerald-600 transform transition-transform duration-300" id="mobile-arrow-' . $menuId . '"></i>';
          echo '</div>';
          echo '</button>';
          
          echo '<div id="mobile-dropdown-' . $menuId . '" class="hidden bg-white border-t border-slate-100">';
          while ($subMenu = $subMenuResult->fetch_assoc()) {
            $subTarget = ($subMenu['target_blank'] == 1) ? 'target="_blank"' : '';
            echo '<a href="' . htmlspecialchars(normalize_url_nav($subMenu['submenu_link'])) . '" ' . $subTarget . ' class="flex items-center px-5 py-3 text-sm text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 pl-8 transition duration-200 border-l-2 border-transparent hover:border-emerald-400">';
            echo '<span class="w-1.5 h-1.5 rounded-full bg-emerald-200 mr-3"></span>';
            echo htmlspecialchars($subMenu['submenu_name']);
            echo '</a>';
          }
          echo '</div></div>';
        } else {
          echo '<a href="' . htmlspecialchars(normalize_url_nav($menuLink)) . '" ' . $target . ' class="block font-bold text-base text-slate-700 px-5 py-3 rounded-xl hover:bg-emerald-50 hover:text-emerald-700 hover:pl-7 transition-all duration-300">' . htmlspecialchars($menuName) . '</a>';
        }
      }
    }
    ?>
    
    <div class="pt-6 mt-4 border-t border-slate-100">
      <div class="flex items-center justify-center space-x-3 bg-slate-50 p-2 rounded-2xl border border-slate-100">
        <!-- Optimized Social Icons for Mobile -->
        <a href="https://www.loeitech.ac.th/mikrotik/?lang=en" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white text-blue-600 shadow-sm border border-slate-200 active:scale-95 transition">
          <img src="https://www.loeitech.ac.th/mikrotik/images/logo/Mikrotik--Streamline-Simple-Icons.svg" alt="Mikrotik" class="w-5 h-5">
        </a>
        <a href="https://facebook.com/www.loeitech.ac.th" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-200 active:scale-95 transition">
          <i class="fab fa-facebook-f text-base"></i>
        </a>
        <a href="https://www.youtube.com/@loeitechnicalcollege1556" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-600 text-white shadow-md shadow-red-200 active:scale-95 transition">
          <i class="fab fa-youtube text-base"></i>
        </a>
        <a href="https://www.tiktok.com/@businessloeitech" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-black text-white shadow-md shadow-gray-400 active:scale-95 transition">
          <i class="fab fa-tiktok text-base"></i>
        </a>
        <div class="w-px h-6 bg-slate-300 mx-1"></div>
        <a href="https://loeitech.appedr.com/edr/login.do" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-100 text-emerald-800 shadow-sm border border-emerald-200 active:scale-95 transition">
          <img src="/svg/EDR.png" alt="EDR" class="w-5 h-5 object-contain">
        </a>
      </div>
    </div>
    </div>
  </div>
</nav>

<!-- Minimal Mobile Bottom Navigation (Portrait Only) -->
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[95%] max-w-[420px] bg-white/95 backdrop-blur-2xl border border-gray-100 z-[9999] px-2 py-2 shadow-[0_15px_35px_rgba(0,0,0,0.1)] rounded-[32px] flex justify-between items-center md:hidden landscape:hidden safe-area-pb">
  
  <!-- Left Side: Home & Personnel -->
  <div class="flex items-center justify-evenly flex-1">
    <a href="/" class="flex flex-col items-center gap-1 p-1 text-gray-400 hover:text-emerald-500 transition-colors active:scale-95 w-14" title="หน้าแรก">
      <i class="fas fa-home text-[18px]"></i>
      <span class="text-[9px] font-medium whitespace-nowrap hidden sm:block min-[350px]:block">หน้าแรก</span>
    </a>
    <a href="/organization.php" class="flex flex-col items-center gap-1 p-1 text-gray-400 hover:text-emerald-500 transition-colors active:scale-95 w-14" title="บุคลากร">
      <i class="fas fa-users text-[18px]"></i>
      <span class="text-[9px] font-medium whitespace-nowrap hidden sm:block min-[350px]:block">บุคลากร</span>
    </a>
  </div>

  <!-- Prominent Center Menu -->
  <button onclick="event.stopPropagation(); document.getElementById('menu-toggle').click()" class="relative -mt-10 flex flex-col items-center justify-center group outline-none shrink-0 z-10 z-[10000]">
    <div class="w-[66px] h-[66px] rounded-full bg-emerald-500 text-white shadow-[0_10px_25px_rgba(16,185,129,0.4)] flex items-center justify-center transform group-active:scale-90 transition-all duration-300 border-[6px] border-[#fbfdfc] group-hover:border-white">
      <i class="fas fa-bars text-2xl group-hover:rotate-180 transition-transform duration-500" style="text-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
    </div>
    <span class="text-[9px] font-bold text-emerald-600 tracking-wide mt-1 whitespace-nowrap bg-white/80 px-2.5 py-0.5 rounded-full shadow-sm backdrop-blur-md absolute -bottom-4 hidden min-[350px]:block">เมนูหลัก</span>
  </button>

  <!-- Right Side: Contact & Sidebar -->
  <div class="flex items-center justify-evenly flex-1">
    <a href="/contact.php" class="flex flex-col items-center gap-1 p-1 text-gray-400 hover:text-emerald-500 transition-colors active:scale-95 w-14" title="ติดต่อเรา">
      <i class="fas fa-phone-alt text-[18px]"></i>
      <span class="text-[9px] font-medium whitespace-nowrap hidden sm:block min-[350px]:block">ติดต่อ</span>
    </a>
    <button onclick="toggleSidebar()" class="flex flex-col items-center gap-1 p-1 text-gray-400 hover:text-emerald-500 transition-colors active:scale-95 w-14 outline-none" title="เมนูเพิ่มเติม">
      <i class="fas fa-th-large text-[18px]"></i>
      <span class="text-[9px] font-medium whitespace-nowrap hidden sm:block min-[350px]:block">เพิ่มเติม</span>
    </button>
  </div>

</div>

<script>

// Hamburger Animation Script
const menuToggle = document.getElementById('menu-toggle');
const mobileMenu = document.getElementById('mobile-menu');
const bar1 = document.getElementById('bar1');
const bar2 = document.getElementById('bar2');
const bar3 = document.getElementById('bar3');

// Close mobile menu when clicking outside
document.addEventListener('click', (event) => {
  if (!mobileMenu.classList.contains('hidden')) {
    if (!mobileMenu.contains(event.target) && !menuToggle.contains(event.target)) {
      // Trigger toggle logic to close menu and reset hamburger icon
      menuToggle.click();
    }
  }
});

menuToggle.addEventListener('click', () => {
  const isClosed = mobileMenu.classList.contains('hidden');
  
  if (isClosed) {
    mobileMenu.classList.remove('hidden');
    // Animate Icon to X
    bar1.classList.add('rotate-45', 'translate-y-[9px]'); // Adjust based on height
    bar2.classList.add('opacity-0');
    bar3.classList.add('-rotate-45', '-translate-y-[9px]'); // Adjust based on height
    // Because flex-col justify-between with h-5 (20px), bars are ~7-8px apart.
    // Let's refine style manually if needed, but rotation is good for now.
    // Tailwind classes might need precise values, let's use style for exact cross.
    bar1.style.transform = 'rotate(45deg) translate(5px, 6px)';
    bar3.style.transform = 'rotate(-45deg) translate(5px, -6px)';

  } else {
    mobileMenu.classList.add('hidden');
    // Reset Icon
    bar1.style.transform = '';
    bar3.style.transform = '';
    bar1.classList.remove('rotate-45', 'translate-y-[9px]');
    bar2.classList.remove('opacity-0');
    bar3.classList.remove('-rotate-45', '-translate-y-[9px]');
  }
});

// Dropdown Logic (Updated for visual classes)
function toggleMobileDropdown(id, button) {
  const dropdown = document.getElementById(id);
  const arrow = document.getElementById('mobile-arrow-' + id.split('-')[2]);
  const isCurrentlyOpen = !dropdown.classList.contains("hidden");

  if (isCurrentlyOpen) {
    dropdown.classList.add("hidden");
    arrow.classList.remove('rotate-180');
    button.classList.remove('text-emerald-700', 'bg-white');
  } else {
    dropdown.classList.remove("hidden");
    arrow.classList.add('rotate-180');
    button.classList.add('text-emerald-700', 'bg-white');
  }
}

// Window Resize Reset
window.addEventListener('resize', function() {
  if (window.innerWidth >= 1280) { 
    if (!mobileMenu.classList.contains('hidden')) {
        menuToggle.click(); // Trigger close
    }
  }
});
</script>

<style>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Clip path for arrow maybe? */

/* Mobile Bottom Navigation Visibility (Portrait Only) */
.mobile-bottom-nav {
  display: flex;
}

/* Hide on Desktop and Tablets (> 1024px) */
@media screen and (min-width: 1025px) {
  .mobile-bottom-nav {
    display: none !important;
  }
}

/* Hide when in Landscape Orientation */
@media screen and (orientation: landscape) {
  .mobile-bottom-nav {
    display: none !important;
  }
}
</style>
