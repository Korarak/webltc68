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

<nav class="fixed top-3 left-3 right-3 z-[9999] bg-white/90 backdrop-blur-md border border-white/50 shadow-xl rounded-2xl transition-all duration-300 sidebar-responsive">
  <div class="max-w-full mx-auto px-3 py-1.5 lg:px-5 flex items-center justify-between">

    <div class="flex items-center space-x-2.5 group cursor-pointer">
      <a href="/" class="flex items-center space-x-2.5">
        <div class="p-1 bg-white rounded-lg shadow-sm border border-emerald-100 group-hover:scale-105 transition duration-300">
             <img src="/svg/loeitech-logo.png" alt="Logo" class="h-7 w-7 lg:h-8 lg:w-8 object-contain" />
        </div>
        <div class="leading-tight">
          <span class="block font-extrabold tracking-wide text-emerald-800 text-xs lg:text-sm group-hover:text-emerald-600 transition">วิทยาลัยเทคนิคเลย</span>
          <span class="text-[9px] lg:text-[10px] text-gray-500 font-light tracking-wider uppercase">Loei Technical College</span>
        </div>
      </a>
    </div>

    <div class="hidden 2xl:flex justify-center flex-grow space-x-1" id="menu">
      <?php
      if ($mainMenusResult && $mainMenusResult->num_rows > 0) {
        $mainMenusResult->data_seek(0);
        while ($mainMenu = $mainMenusResult->fetch_assoc()) {
          $menuId = $mainMenu['menu_id'];
          $menuName = $mainMenu['menu_name'];
          $menuLink = $mainMenu['menu_link'];
          $isDropdown = $mainMenu['is_dropdown'];
          $target = ($mainMenu['target_blank'] == 1) ? 'target="_blank"' : '';

          $subMenuQuery = "SELECT * FROM sub_main_menus WHERE menu_id = $menuId AND visible = 1 AND position_type IN ('topnav', 'both') ORDER BY submenu_order ASC"; // Changed sort_order to submenu_order
          $subMenuResult = $mysqli4->query($subMenuQuery);

          if ($isDropdown && $subMenuResult && $subMenuResult->num_rows > 0) {
            echo '<div class="relative group">';
            echo '<button id="menu-btn-' . $menuId . '" onclick="toggleDropdown(\'dropdown-' . $menuId . '\', this)" class="flex items-center px-3 py-1.5 text-xs font-semibold text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 transition duration-200 group-hover:bg-emerald-50/50">';
            echo htmlspecialchars($menuName);
            echo '<i class="fas fa-chevron-down text-[9px] ml-1 transition-transform duration-300 group-[.active]:rotate-180 text-gray-400"></i>';
            echo '</button>';
            
            echo '<div id="dropdown-' . $menuId . '" class="absolute left-1/2 transform -translate-x-1/2 top-full mt-1 bg-white rounded-xl shadow-2xl hidden z-[10000] min-w-[12rem] overflow-hidden border border-emerald-100 ring-1 ring-black/5 p-1">';
            while ($subMenu = $subMenuResult->fetch_assoc()) {
              $subTarget = ($subMenu['target_blank'] == 1) ? 'target="_blank"' : '';
              echo '<a href="' . htmlspecialchars(normalize_url_nav($subMenu['submenu_link'])) . '" ' . $subTarget . ' class="flex items-center px-3 py-2 text-xs text-gray-600 hover:bg-emerald-50 hover:text-emerald-700 rounded-lg transition duration-150 whitespace-nowrap group/item">';
              echo '<span class="w-1 h-1 rounded-full bg-emerald-200 mr-2 group-hover/item:bg-emerald-500 transition-colors"></span>';
              echo htmlspecialchars($subMenu['submenu_name']);
              echo '</a>';
            }
            echo '</div></div>';
          } else {
            echo '<a href="' . htmlspecialchars(normalize_url_nav($menuLink)) . '" ' . $target . ' class="px-3 py-1.5 text-xs font-semibold text-gray-600 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 transition duration-200">' . htmlspecialchars($menuName) . '</a>';
          }
        }
      }
      ?>
    </div>

    <!-- Social Icons: ซ่อนบนมือถือเล็ก, แสดงกลางจอ (sm-xl), ชิดขวา (2xl+) -->
    <div class="hidden sm:flex absolute left-1/2 -translate-x-1/2 2xl:static 2xl:translate-x-0 items-center space-x-1.5 bg-gray-50 p-1 rounded-full border border-gray-100">
      <a href="https://www.loeitech.ac.th/mikrotik/?lang=en" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-blue-600 shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
        <img src="https://www.loeitech.ac.th/mikrotik/images/logo/Mikrotik--Streamline-Simple-Icons.svg" alt="Mikrotik" class="w-3.5 h-3.5">
      </a>
      <a href="https://facebook.com/www.loeitech.ac.th" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-blue-600 shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
        <i class="fab fa-facebook-f text-xs"></i>
      </a>
      <a href="https://www.youtube.com/@loeitechnicalcollege1556" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-red-600 shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
        <i class="fab fa-youtube text-xs"></i>
      </a>
      <a href="https://www.tiktok.com/@businessloeitech" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-full bg-white text-black shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
        <i class="fab fa-tiktok text-xs"></i>
      </a>
      <div class="w-px h-3 bg-gray-300 mx-0.5"></div>
      <a href="https://loeitech.appedr.com/edr/login.do" target="_blank" class="w-7 h-7 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 shadow-sm hover:bg-emerald-200 hover:scale-110 transition duration-300">
        <img src="/svg/EDR.png" alt="EDR" class="w-4 h-4 object-contain">
      </a>
    </div>

    <div class="flex items-center space-x-2">

      <button id="menu-toggle" class="2xl:hidden p-1.5 text-gray-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition duration-200 focus:outline-none">
        <i class="fas fa-bars text-lg"></i>
      </button>
    </div>
  </div>

  <div id="mobile-menu" class="hidden border-t border-gray-100 bg-white/95 backdrop-blur-xl rounded-b-2xl overflow-hidden shadow-lg">
    <div class="px-3 py-3 space-y-1 max-h-[70vh] overflow-y-auto custom-scrollbar">
    <?php
    if ($mobileMenusResult && $mobileMenusResult->num_rows > 0) {
      $mobileMenusResult->data_seek(0);
      while ($mainMenu = $mobileMenusResult->fetch_assoc()) {
        $menuId = $mainMenu['menu_id'];
        $menuName = $mainMenu['menu_name'];
        $menuLink = $mainMenu['menu_link'];
        $isDropdown = $mainMenu['is_dropdown'];
        $target = ($mainMenu['target_blank'] == 1) ? 'target="_blank"' : '';

        $subMenuQuery = "SELECT * FROM sub_main_menus WHERE menu_id = $menuId AND visible = 1 AND position_type IN ('topnav', 'both') ORDER BY submenu_order ASC"; // Changed sort_order to submenu_order
        $subMenuResult = $mysqli4->query($subMenuQuery);

        if ($isDropdown && $subMenuResult && $subMenuResult->num_rows > 0) {
          echo '<div class="bg-gray-50 rounded-lg overflow-hidden mb-1">';
          echo '<button onclick="toggleMobileDropdown(\'mobile-dropdown-' . $menuId . '\', this)" class="w-full text-left font-semibold text-sm text-gray-700 px-4 py-2 hover:bg-emerald-50 hover:text-emerald-700 flex justify-between items-center transition duration-200">';
          echo '<span>' . htmlspecialchars($menuName) . '</span>';
          echo '<i class="fas fa-chevron-down text-[10px] text-gray-400 transform transition-transform" id="mobile-arrow-' . $menuId . '"></i>';
          echo '</button>';
          
          echo '<div id="mobile-dropdown-' . $menuId . '" class="hidden bg-white border-t border-gray-100">';
          while ($subMenu = $subMenuResult->fetch_assoc()) {
            $subTarget = ($subMenu['target_blank'] == 1) ? 'target="_blank"' : '';
            echo '<a href="' . htmlspecialchars(normalize_url_nav($subMenu['submenu_link'])) . '" ' . $subTarget . ' class="flex items-center px-4 py-2 text-xs text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 pl-8 transition duration-150">';
            echo '<i class="fas fa-angle-right text-[10px] mr-2 opacity-50"></i>';
            echo htmlspecialchars($subMenu['submenu_name']);
            echo '</a>';
          }
          echo '</div></div>';
        } else {
          echo '<a href="' . htmlspecialchars(normalize_url_nav($menuLink)) . '" ' . $target . ' class="block font-semibold text-sm text-gray-700 px-4 py-2 rounded-lg hover:bg-emerald-50 hover:text-emerald-700 transition duration-200">' . htmlspecialchars($menuName) . '</a>';
        }
      }
    }
    ?>
    
    <div class="pt-3 mt-3 border-t border-gray-200">
      <!-- Social Icons: แสดงตลอดเป็นไอคอนกลมเหมือน navbar -->
      <div class="flex items-center justify-center space-x-1.5 bg-gray-50 p-1.5 rounded-full border border-gray-100">
        <a href="https://www.loeitech.ac.th/mikrotik/?lang=en" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-blue-600 shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
          <img src="https://www.loeitech.ac.th/mikrotik/images/logo/Mikrotik--Streamline-Simple-Icons.svg" alt="Mikrotik" class="w-4 h-4">
        </a>
        <a href="https://facebook.com/www.loeitech.ac.th" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-blue-600 shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
          <i class="fab fa-facebook-f text-sm"></i>
        </a>
        <a href="https://www.youtube.com/@loeitechnicalcollege1556" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-red-600 shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
          <i class="fab fa-youtube text-sm"></i>
        </a>
        <a href="https://www.tiktok.com/@businessloeitech" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-black shadow-sm hover:scale-110 hover:shadow-md transition duration-300 border border-gray-100">
          <i class="fab fa-tiktok text-sm"></i>
        </a>
        <div class="w-px h-4 bg-gray-300 mx-0.5"></div>
        <a href="https://loeitech.appedr.com/edr/login.do" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-full bg-emerald-100 text-emerald-700 shadow-sm hover:bg-emerald-200 hover:scale-110 transition duration-300">
          <img src="/svg/EDR.png" alt="EDR" class="w-4 h-4 object-contain">
        </a>
      </div>
    </div>
    </div>
  </div>
</nav>

<script>
// (Scripts remain unchanged)
document.getElementById('menu-toggle').addEventListener('click', () => {
  const mobileMenu = document.getElementById('mobile-menu');
  const icon = document.querySelector('#menu-toggle i');
  
  if (mobileMenu.classList.contains('hidden')) {
    mobileMenu.classList.remove('hidden');
    icon.classList.remove('fa-bars');
    icon.classList.add('fa-times');
  } else {
    mobileMenu.classList.add('hidden');
    icon.classList.remove('fa-times');
    icon.classList.add('fa-bars');
  }
});

function toggleDropdown(id, button) {
  const dropdown = document.getElementById(id);
  const isActive = !dropdown.classList.contains("hidden");

  document.querySelectorAll("div[id^='dropdown-']").forEach(div => div.classList.add("hidden"));
  document.querySelectorAll("button[id^='menu-btn-']").forEach(btn => btn.classList.remove("active"));

  if (!isActive) {
    dropdown.classList.remove("hidden");
    button.classList.add("active");
  } else {
    button.classList.remove("active");
  }
}

function toggleMobileDropdown(id, button) {
  const dropdown = document.getElementById(id);
  const arrow = document.getElementById('mobile-arrow-' + id.split('-')[2]);
  const isCurrentlyOpen = !dropdown.classList.contains("hidden");

  dropdown.classList.toggle("hidden", isCurrentlyOpen);
  if (arrow) arrow.classList.toggle('rotate-180', !isCurrentlyOpen);
}

// Function for Contact Toggle
function toggleContact() {
  const content = document.getElementById('contact-content');
  const arrow = document.getElementById('contact-arrow');
  const isHidden = content.classList.contains('hidden');
  
  if (isHidden) {
    content.classList.remove('hidden');
    arrow.classList.add('rotate-180');
  } else {
    content.classList.add('hidden');
    arrow.classList.remove('rotate-180');
  }
}

window.addEventListener('click', function(e) {
  const isDropdownButton = e.target.closest('button[id^="menu-btn-"]');
  const isInsideDropdown = e.target.closest('div[id^="dropdown-"]');
  
  if (!isDropdownButton && !isInsideDropdown) {
    document.querySelectorAll("div[id^='dropdown-']").forEach(el => el.classList.add('hidden'));
    document.querySelectorAll("button[id^='menu-btn-']").forEach(el => el.classList.remove('active'));
  }
});

window.addEventListener('resize', function() {
  if (window.innerWidth >= 1536) { 
    document.getElementById('mobile-menu').classList.add('hidden');
    document.querySelector('#menu-toggle i').classList.remove('fa-times');
    document.querySelector('#menu-toggle i').classList.add('fa-bars');
  }
});
</script>

<style>
.rotate-180 { transform: rotate(180deg); }

.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

nav { -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px); }
</style>