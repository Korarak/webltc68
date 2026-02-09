<?php
include 'middleware.php';
?>
<!DOCTYPE html>
<html lang="th" x-data="{ sidebarOpen: false }" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8">
  <title>แผงควบคุม</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- FontAwesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
      body { font-family: 'Sarabun', sans-serif; }
      .nav-item {
          transition: all 0.2s;
          border-left: 3px solid transparent;
      }
      .nav-item:hover {
          background-color: #f3f4f6;
          border-left-color: #3b82f6;
          padding-left: 1rem;
      }
      .nav-group-title {
          font-size: 0.75rem;
          text-transform: uppercase;
          color: #9ca3af;
          font-weight: 600;
          letter-spacing: 0.05em;
          margin-top: 1.5rem;
          margin-bottom: 0.5rem;
          padding-left: 0.75rem;
      }
  </style>
</head>
<body class="bg-gray-100 font-sans min-h-screen" x-data="{ sidebarOpen: false }">

  <!-- Overlay on mobile -->
  <div :class="sidebarOpen ? 'block' : 'hidden'" class="fixed inset-0 z-40 bg-black bg-opacity-50 backdrop-blur-sm md:hidden"
       @click="sidebarOpen = false" x-transition.opacity></div>

  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed z-50 top-0 left-0 w-72 h-full bg-white shadow-xl transform transition-transform duration-300 md:translate-x-0 md:static md:block flex flex-col">
      
      <!-- Brand -->
      <div class="p-6 border-b border-gray-100 flex items-center gap-3">
          <img src="/svg/loeitech-logo.ico" class="w-10 h-10" alt="Logo"> <!-- Placeholder for logo -->
          <div>
              <h2 class="font-bold text-gray-800 text-lg leading-tight">Loei Tech</h2>
              <p class="text-xs text-green-600 font-medium">Administration System</p>
          </div>
      </div>

      <nav class="flex-1 overflow-y-auto p-4 space-y-1 custom-scrollbar">
        
        <a href="/admin/admin-index.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 font-medium hover:text-blue-600">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-home"></i></div>
            Dashboard
        </a>

        <div class="nav-group-title">CONTENT MANAGEMENT</div>
        
        <a href="news_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-500 flex items-center justify-center"><i class="fas fa-newspaper"></i></div>
            จัดการข่าว/ประกาศ
        </a>
        <a href="carousel_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-500 flex items-center justify-center"><i class="fas fa-images"></i></div>
             จัดการป้ายประชาสัมพันธ์
        </a>
        <a href="file_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-500 flex items-center justify-center"><i class="fas fa-folder-open"></i></div>
             จัดการไฟล์ (File Manager)
        </a>
        <a href="letter_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-500 flex items-center justify-center"><i class="fas fa-envelope-open-text"></i></div>
             จัดการจดหมายข่าว
        </a>
        <a href="webpages_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-500 flex items-center justify-center"><i class="fas fa-globe"></i></div>
             จัดการเว็บเพจ
        </a>

        <div class="nav-group-title">DATA MANAGEMENT</div>

        <a href="personel_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-pink-50 text-pink-500 flex items-center justify-center"><i class="fas fa-users-cog"></i></div>
             จัดการข้อมูลบุคลากร
        </a>
        <a href="setting-personel.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center"><i class="fas fa-briefcase"></i></div>
             จัดการข้อมูลตำแหน่ง
        </a>
        <a href="badge_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-yellow-600/10 text-yellow-600 flex items-center justify-center"><i class="fas fa-medal"></i></div>
             จัดการ Badge
        </a>
        <a href="building_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-500 flex items-center justify-center"><i class="fas fa-building"></i></div>
             จัดการแผนผังอาคาร
        </a>
        <a href="map_editor.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center"><i class="fas fa-map-marked-alt"></i></div>
             เครื่องมือสร้างแผนผัง
        </a>

        <div class="nav-group-title">SYSTEM SETTINGS</div>

        <a href="news_type_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <i class="fas fa-tags w-8 text-center text-gray-400"></i> ประเภทข่าว
        </a>
        <a href="navbar_menu_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <i class="fas fa-bars w-8 text-center text-gray-400"></i> เมนู Navbar
        </a>
        <a href="footer_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <i class="fas fa-shoe-prints w-8 text-center text-gray-400"></i> จัดการ Footer
        </a>
        <a href="siteinfo_manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <i class="fas fa-cogs w-8 text-center text-gray-400"></i> ข้อมูลเว็บไซต์
        </a>
        <a href="admin-manage.php" class="nav-item flex items-center gap-3 p-3 rounded-lg text-gray-700 hover:text-blue-600">
             <i class="fas fa-user-shield w-8 text-center text-gray-400"></i> ผู้ดูแลระบบ
        </a>
        
        <div class="mt-8">
            <a href="../logout.php" class="flex items-center gap-3 p-3 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors font-medium">
                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
            </a>
        </div>
      </nav>
    </aside>

    <!-- Page content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Top Header -->
      <header class="bg-white px-6 py-4 flex justify-between items-center shadow-sm sticky top-0 z-30 border-b border-gray-100">
        <div class="flex items-center gap-4">
          <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-600 hover:text-blue-600 transition-colors">
            <i class="fas fa-bars text-xl"></i>
          </button>
          <a href="/" target="_blank" class="hidden md:flex items-center gap-2 text-gray-500 hover:text-green-600 transition-colors text-sm font-medium">
              <i class="fas fa-external-link-alt"></i> เปิดหน้าเว็บไซต์
          </a>
        </div>
        
        <div class="flex items-center gap-4">
             <div class="text-right hidden sm:block">
                 <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($decoded->username) ?></p>
                 <p class="text-xs text-gray-500">Administrator</p>
             </div>
             <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-green-500 to-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-green-200">
                 <?= strtoupper(substr($decoded->username, 0, 1)) ?>
             </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto">
             <?= $content ?? '<p>No content</p>' ?>
        </div>
      </main>
    </div>
  </div>

</body>
</html>
