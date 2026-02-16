<?php
//app-menu/sidebar.php
@include 'condb/condb.php'; 

if (!function_exists('normalize_url')) {
    function normalize_url($url) {
        $url = trim($url ?? '');
        if ($url === '' || $url === '#') return '#';
        if (preg_match('~^https?://~i', $url)) return $url;
        return '/' . ltrim($url, '/');
    }
}
?>

<button id="sidebarToggle" class="fixed left-4 top-20 z-[60] bg-white/80 hover:bg-emerald-600 text-emerald-700 hover:text-white backdrop-blur-sm border border-emerald-100 p-2 rounded-lg shadow-lg transition-all duration-300 group">
  <i class="fas fa-bars text-base"></i>
</button>

<div id="sidebarOverlay" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40 hidden transition-opacity duration-300"></div>

<div id="sidebar" class="fixed top-20 left-4 bottom-4 w-56 bg-white/95 backdrop-blur-md shadow-2xl z-50 transform -translate-x-[120%] transition-transform duration-300 overflow-hidden flex flex-col rounded-2xl border border-white/50 ring-1 ring-gray-900/5">
  
  <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-2 flex items-center justify-end shadow-md shrink-0">
    <button id="sidebarClose" class="text-black hover:text-emerald-100 hover:bg-white/20 p-1 rounded transition">
      <i class="fa fa-arrow-left text-base text-white"></i>
    </button>
  </div>

  <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">

    <?php
    $sidebarMenusQuery = "SELECT * FROM main_menus WHERE visible = 1 AND position_type IN ('sidebar', 'both') ORDER BY menu_order ASC";
    
    if (isset($mysqli4)) {
        $sidebarMenusResult = $mysqli4->query($sidebarMenusQuery);
        
        if ($sidebarMenusResult && $sidebarMenusResult->num_rows > 0) {
          echo '<div class="mb-3">';
          echo '<h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2 px-1">เมนูทั้งหมด</h3>';
          echo '<div class="space-y-0.5">';
          
          while ($menu = $sidebarMenusResult->fetch_assoc()) {
            $menuId = $menu['menu_id'];
            $menuName = $menu['menu_name'];
            $menuLink = $menu['menu_link'];
            $isDropdown = $menu['is_dropdown'];
            // ตรวจสอบ target_blank ของเมนูหลัก
            $target = ($menu['target_blank'] == 1) ? 'target="_blank"' : '';

            $iconHtml = '<div class="w-7 h-7 flex items-center justify-center rounded bg-gray-100 text-gray-500 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-colors"><i class="fas ' . ($isDropdown ? 'fa-folder' : 'fa-link') . ' text-[11px]"></i></div>';

            if ($isDropdown) {
              $subMenuQuery = "SELECT * FROM sub_main_menus WHERE menu_id = $menuId AND visible = 1 ORDER BY submenu_order ASC";
              $subMenuResult = $mysqli4->query($subMenuQuery);
              
              if ($subMenuResult && $subMenuResult->num_rows > 0) {
                // Dropdown Structure
                echo '<div class="dropdown-menu group">';
                echo '<button class="dropdown-toggle flex items-center justify-between w-full text-gray-600 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-50/80 transition-all duration-200 font-medium text-xs">';
                echo '<span class="flex items-center space-x-2">';
                echo $iconHtml;
                echo '<span class="truncate w-32 text-left">' . htmlspecialchars($menuName) . '</span>';
                echo '</span>';
                echo '<i class="fas fa-chevron-down text-[9px] text-gray-400 transition-transform duration-300 group-hover:text-emerald-500 transform"></i>';
                echo '</button>';
                
                // Submenu Items
                echo '<div class="dropdown-content hidden space-y-0.5 ml-3 mt-0.5 border-l-2 border-emerald-100 pl-1.5">';
                while ($subMenu = $subMenuResult->fetch_assoc()) {
                  // ตรวจสอบ target_blank ของเมนูย่อย
                  $subTarget = ($subMenu['target_blank'] == 1) ? 'target="_blank"' : '';
                  
                  echo '<a href="' . htmlspecialchars(normalize_url($subMenu['submenu_link'])) . '" ' . $subTarget . ' class="flex items-center space-x-2 text-gray-500 hover:text-emerald-600 py-1 px-2 rounded hover:bg-emerald-50 transition text-[11px]">';
                  echo '<div class="w-1 h-1 rounded-full bg-gray-300 group-hover:bg-emerald-400"></div>';
                  echo '<span class="truncate">' . htmlspecialchars($subMenu['submenu_name']) . '</span>';
                  
                  // ไอคอนลูกศรเล็กๆ ถ้าเป็น New Tab
                  if ($subTarget) {
                      echo '<i class="fas fa-external-link-alt text-[8px] opacity-50 ml-auto"></i>';
                  }
                  
                  echo '</a>';
                }
                echo '</div></div>';

              } else {
                 // Dropdown empty -> Fallback to Link
                 echo '<a href="' . htmlspecialchars(normalize_url($menuLink)) . '" ' . $target . ' class="flex items-center space-x-2 text-gray-600 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-50/80 transition-all duration-200 group text-xs">';
                 echo $iconHtml;
                 echo '<span class="font-medium truncate">' . htmlspecialchars($menuName) . '</span>';
                 if ($target) echo '<i class="fas fa-external-link-alt text-[9px] opacity-50 ml-auto mr-1"></i>';
                 echo '</a>';
              }
            } else {
              // Normal Link
              echo '<a href="' . htmlspecialchars(normalize_url($menuLink)) . '" ' . $target . ' class="flex items-center space-x-2 text-gray-600 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-50/80 transition-all duration-200 group text-xs">';
              echo $iconHtml;
              echo '<span class="font-medium truncate">' . htmlspecialchars($menuName) . '</span>';
              if ($target) echo '<i class="fas fa-external-link-alt text-[9px] opacity-50 ml-auto mr-1"></i>';
              echo '</a>';
            }
          }
          echo '</div></div>';
        }
    }
    ?>
  </div>
</div>

<script>
const sidebar = document.getElementById('sidebar');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarClose = document.getElementById('sidebarClose');
const sidebarOverlay = document.getElementById('sidebarOverlay');
const body = document.body;

const BREAKPOINT = 1536; 

function openSidebar() {
  sidebar.classList.remove('-translate-x-[120%]');
  body.classList.add('sidebar-open');
  
  // Hide toggle button when sidebar is open
  sidebarToggle.classList.add('hidden');
  
  // Show overlay only on smaller screens
  if (window.innerWidth < BREAKPOINT) {
      sidebarOverlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; 
  }
}

function closeSidebar() {
  sidebar.classList.add('-translate-x-[120%]');
  sidebarOverlay.classList.add('hidden');
  body.classList.remove('sidebar-open');
  document.body.style.overflow = 'auto';

  // Show toggle button when sidebar is closed
  sidebarToggle.classList.remove('hidden');
}

sidebarToggle.addEventListener('click', () => {
  if (sidebar.classList.contains('-translate-x-[120%]')) {
    openSidebar();
  } else {
    closeSidebar();
  }
});

sidebarClose.addEventListener('click', closeSidebar);
sidebarOverlay.addEventListener('click', closeSidebar);

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeSidebar();
  }
});

document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
  toggle.addEventListener('click', function(e) {
    e.preventDefault();
    const dropdownMenu = this.closest('.dropdown-menu');
    const content = dropdownMenu.querySelector('.dropdown-content');
    const icon = this.querySelector('.fa-chevron-down');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
    } else {
        content.classList.add('hidden');
        icon.style.transform = 'rotate(0deg)';
    }
  });
});

function handleResize() {
  if (window.innerWidth >= BREAKPOINT) {
     // On desktop, ensure overlay is hidden even if sidebar is open
     sidebarOverlay.classList.add('hidden');
     document.body.style.overflow = 'auto';
  } else {
     // Switch back to mobile: if open, show overlay
     if (!sidebar.classList.contains('-translate-x-[120%]')) {
         sidebarOverlay.classList.remove('hidden');
         document.body.style.overflow = 'hidden';
     }
  }
}

window.addEventListener('resize', handleResize);
document.addEventListener('DOMContentLoaded', () => {
  // Default to open on large screens
  if (window.innerWidth >= BREAKPOINT) {
      openSidebar();
  }
});
</script>

<style>
main {
  margin-left: 0;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@media (min-width: 1536px) {
  body.sidebar-open main.shiftable-content {
    /* ปรับ margin-left ให้พอดีกับ w-56 (14rem) + gap */
    margin-left: 15rem; 
  }
  
  #sidebar {
    top: 5.5rem;
  }
}

#sidebarToggle {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>