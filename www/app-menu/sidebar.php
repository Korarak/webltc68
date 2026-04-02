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

<button id="sidebarToggle" class="hidden 2xl:flex fixed left-4 top-[6.5rem] z-[60] bg-white/80 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 backdrop-blur-2xl border border-white/50 w-10 h-10 items-center justify-center rounded-2xl shadow-[0_8px_32px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_32px_rgba(16,185,129,0.1)] transition-all duration-500">
  <i class="fas fa-bars text-sm"></i>
</button>

<!-- Mobile Bottom Navigation -->
<div class="fixed bottom-0 left-0 right-0 bg-white/80 backdrop-blur-2xl border-t border-white/50 z-[9999] px-6 py-3 flex justify-between items-center 2xl:hidden shadow-[0_-8px_32px_rgba(0,0,0,0.05)] safe-area-pb">
  
  <a href="/" class="flex flex-col items-center gap-1 group w-16">
    <div class="w-10 h-10 rounded-xl bg-transparent text-slate-400 group-hover:bg-emerald-50/50 group-hover:text-emerald-600 flex items-center justify-center transition-all duration-300">
      <i class="fas fa-home text-lg"></i>
    </div>
    <span class="text-[10px] font-medium text-slate-400 group-hover:text-emerald-600 transition-colors">หน้าแรก</span>
  </a>

  <button onclick="document.getElementById('menu-toggle').click()" class="flex flex-col items-center gap-1 group -mt-8">
    <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-emerald-400 to-teal-400 text-white shadow-lg shadow-emerald-200/50 flex items-center justify-center transform group-active:scale-95 transition-all duration-300 border-4 border-white/80 backdrop-blur-sm relative overflow-hidden">
      <!-- Glow Effect inside button -->
      <div class="absolute inset-0 bg-emerald-300 mix-blend-overlay opacity-0 group-hover:opacity-50 blur transition duration-300"></div>
      <i class="fas fa-bars text-xl relative z-10"></i>
    </div>
    <span class="text-[10px] font-bold text-emerald-600 tracking-wide mt-0.5" style="text-shadow: 0 1px 1px rgba(255,255,255,0.8);">เมนูหลัก</span>
  </button>

  <button onclick="toggleSidebar()" class="flex flex-col items-center gap-1 group w-16">
    <div class="w-10 h-10 rounded-xl bg-transparent text-slate-400 group-hover:bg-emerald-50/50 group-hover:text-emerald-600 flex items-center justify-center transition-all duration-300">
      <i class="fas fa-th-large text-lg"></i>
    </div>
    <span class="text-[10px] font-medium text-slate-400 group-hover:text-emerald-600 transition-colors">เพิ่มเติม</span>
  </button>

</div>

<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-40 hidden transition-opacity duration-300"></div>

<div id="sidebar" class="fixed top-[6.5rem] left-4 bottom-4 w-64 bg-white/90 backdrop-blur-2xl border border-white/60 shadow-[0_8px_32px_rgba(0,0,0,0.05)] z-50 transform -translate-x-[120%] transition-transform duration-500 overflow-hidden flex flex-col rounded-2xl">
  
  <!-- Sidebar Header -->
  <div class="px-5 py-4 border-b border-white/50 flex items-center justify-between shrink-0 bg-white/40">
    <div class="flex items-center gap-3">
      <div class="relative p-1.5 bg-gradient-to-br from-white to-emerald-50 rounded-xl shadow-inner border border-white/60 flex items-center justify-center w-9 h-9 overflow-hidden">
        <div class="absolute inset-0 bg-emerald-400 opacity-20 blur-md rounded-xl"></div>
        <i class="fas fa-th-large text-emerald-600 text-sm relative z-10"></i>
      </div>
      <span class="font-extrabold tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-emerald-700 to-teal-600 text-[15px] pt-0.5" style="text-shadow: 0 1px 1px rgba(255,255,255,0.3);">
        เมนูเพิ่มเติม
      </span>
    </div>
    <button id="sidebarClose" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-50 hover:bg-red-50 text-slate-400 hover:text-red-500 border border-slate-100/50 transition-all duration-300 shadow-sm active:scale-95">
      <i class="fa fa-times text-[10px]"></i>
    </button>
  </div>

  <!-- Scrollable Menu Content -->
  <div class="p-3 overflow-y-auto flex-1 custom-scrollbar">

    <?php
    $sidebarMenusQuery = "SELECT * FROM main_menus WHERE visible = 1 AND position_type IN ('sidebar', 'both') ORDER BY menu_order ASC";
    
    if (isset($mysqli4)) {
        $sidebarMenusResult = $mysqli4->query($sidebarMenusQuery);
        
        if ($sidebarMenusResult && $sidebarMenusResult->num_rows > 0) {
          echo '<div class="space-y-1">';
          
          while ($menu = $sidebarMenusResult->fetch_assoc()) {
            $menuId = $menu['menu_id'];
            $menuName = $menu['menu_name'];
            $menuLink = $menu['menu_link'];
            $isDropdown = $menu['is_dropdown'];
            // ตรวจสอบ target_blank ของเมนูหลัก
            $target = ($menu['target_blank'] == 1) ? 'target="_blank"' : '';

            $iconClass = $isDropdown ? 'fa-folder-open' : 'fa-link';
            $iconHtml = '<div class="w-8 h-8 flex items-center justify-center rounded-xl bg-emerald-50/50 text-emerald-500 group-hover:bg-emerald-100 group-hover:text-emerald-600 transition-all duration-300 shrink-0 shadow-sm border border-emerald-100/50 relative overflow-hidden"><div class="absolute inset-0 bg-emerald-200/20 opacity-0 group-hover:opacity-100 transition duration-300 blur-sm"></div><i class="fas ' . $iconClass . ' text-xs relative z-10"></i></div>';

            if ($isDropdown) {
              $subMenuQuery = "SELECT * FROM sub_main_menus WHERE menu_id = $menuId AND visible = 1 ORDER BY submenu_order ASC";
              $subMenuResult = $mysqli4->query($subMenuQuery);
              
              if ($subMenuResult && $subMenuResult->num_rows > 0) {
                // Dropdown Structure
                echo '<div class="dropdown-menu">';
                echo '<button class="dropdown-toggle flex items-center justify-between w-full text-slate-600 hover:text-emerald-700 px-2.5 py-2.5 rounded-xl hover:bg-white/60 transition-all duration-300 group">';
                echo '<span class="flex items-center gap-3">';
                echo $iconHtml;
                echo '<span class="font-semibold text-[13px] truncate max-w-[140px] text-left">' . htmlspecialchars($menuName) . '</span>';
                echo '</span>';
                echo '<i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform duration-300 group-hover:text-emerald-500 ml-1"></i>';
                echo '</button>';
                
                // Submenu Items
                echo '<div class="dropdown-content hidden ml-[1.35rem] mt-1 mb-1 space-y-1 border-l-2 border-emerald-100/50 pl-4 py-1">';
                while ($subMenu = $subMenuResult->fetch_assoc()) {
                  // ตรวจสอบ target_blank ของเมนูย่อย
                  $subTarget = ($subMenu['target_blank'] == 1) ? 'target="_blank"' : '';
                  
                  echo '<a href="' . htmlspecialchars(normalize_url($subMenu['submenu_link'])) . '" ' . $subTarget . ' class="flex items-center gap-3 text-slate-500 hover:text-emerald-600 py-2.5 px-3 rounded-xl hover:bg-emerald-50/50 transition-all duration-300 group text-[12px]">';
                  echo '<div class="w-1.5 h-1.5 rounded-full bg-slate-300 group-hover:bg-emerald-400 shrink-0 transition-colors shadow-sm relative"><div class="absolute inset-0 bg-emerald-400 blur-sm opacity-0 group-hover:opacity-60 transition duration-300"></div></div>';
                  echo '<span class="truncate font-semibold tracking-wide drop-shadow-sm">' . htmlspecialchars($subMenu['submenu_name']) . '</span>';
                  
                  // ไอคอนลูกศรเล็กๆ ถ้าเป็น New Tab
                  if ($subTarget) {
                      echo '<i class="fas fa-external-link-alt text-[9px] opacity-40 ml-auto"></i>';
                  }
                  
                  echo '</a>';
                }
                echo '</div></div>';

              } else {
                 // Dropdown empty -> Fallback to Link
                 echo '<a href="' . htmlspecialchars(normalize_url($menuLink)) . '" ' . $target . ' class="flex items-center gap-3 text-slate-600 hover:text-emerald-700 px-2.5 py-2.5 rounded-xl hover:bg-white/60 transition-all duration-300 group">';
                 echo $iconHtml;
                 echo '<span class="font-semibold text-[13px] truncate">' . htmlspecialchars($menuName) . '</span>';
                 if ($target) echo '<i class="fas fa-external-link-alt text-[9px] opacity-40 ml-auto mr-1"></i>';
                 echo '</a>';
              }
            } else {
              // Normal Link
              echo '<a href="' . htmlspecialchars(normalize_url($menuLink)) . '" ' . $target . ' class="flex items-center gap-3 text-slate-600 hover:text-emerald-700 px-2.5 py-2.5 rounded-xl hover:bg-white/60 transition-all duration-300 group">';
              echo $iconHtml;
              echo '<span class="font-semibold text-[13px] truncate">' . htmlspecialchars($menuName) . '</span>';
              if ($target) echo '<i class="fas fa-external-link-alt text-[9px] opacity-40 ml-auto mr-1"></i>';
              echo '</a>';
            }
          }
          echo '</div>';
        }
    }
    ?>
  </div>

  <!-- Sidebar Footer -->
  <div class="px-5 py-4 border-t border-white/50 shrink-0 bg-gradient-to-t from-slate-50/50 to-transparent">
    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium tracking-wide">
      <i class="fas fa-info-circle text-emerald-400/70"></i>
      <span>วิทยาลัยเทคนิคเลย</span>
    </div>
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
  sidebarToggle.classList.remove('2xl:flex');
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
  sidebarToggle.classList.add('2xl:flex');
}

function toggleSidebar() {
  if (sidebar.classList.contains('-translate-x-[120%]')) {
    openSidebar();
  } else {
    closeSidebar();
  }
}

sidebarToggle.addEventListener('click', toggleSidebar);

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
    margin-left: 17.5rem; 
  }
  
  #sidebar {
    top: 6.5rem;
  }
}

#sidebarToggle {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* Dropdown animation */
.dropdown-content {
  animation: slideDown 0.2s ease-out;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}

.safe-area-pb {
  padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>