<?php
session_start();

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default
}

$lang_file = 'lang_' . $_SESSION['lang'] . '.php';
if (file_exists($lang_file)) {
    include $lang_file;
} else {
    include 'lang_en.php';
}
?>
<!DOCTYPE html>
<html lang="<?= $_SESSION['lang'] ?>" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $lang['title'] ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="images/logo/Mikrotik--Streamline-Simple-Icons.svg">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                        display: ['Kanit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb', // Brand Blue
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                        secondary: '#eb008b', // Pink from reference
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #ffffff;
            color: #333333;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Kanit', sans-serif;
        }
        .nav-link {
            position: relative;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: #2563eb;
            transition: width 0.3s;
        }
        .nav-link:hover::after {
            width: 100%;
        }
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }
        .card-shadow:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="antialiased">

    <!-- Top Bar -->
    <div class="bg-gray-900 text-white py-2 text-sm hidden md:block">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <div class="flex gap-4">
                <span><i class="fas fa-phone-alt mr-2"></i>080-1502400</span>
                <span><i class="fas fa-envelope mr-2"></i>loei@loeitech.mail.go.th</span>
            </div>
            <div class="flex gap-3">
          <a href="https://facebook.com/www.loeitech.ac.th" target="_blank" class="flex items-center space-x-2 p-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
            <i class="fab fa-facebook-f text-xs"></i>
            <span class="text-xs font-medium">Facebook</span>
          </a>
          <a href="https://www.youtube.com/@loeitechnicalcollege1556" target="_blank" class="flex items-center space-x-2 p-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition">
            <i class="fab fa-youtube text-xs"></i>
            <span class="text-xs font-medium">YouTube</span>
          </a>
          <a href="https://www.tiktok.com/@businessloeitech" target="_blank" class="flex items-center space-x-2 p-2 bg-gray-100 text-black rounded-lg hover:bg-gray-200 transition">
            <i class="fab fa-tiktok text-xs"></i>
            <span class="text-xs font-medium">TikTok</span>
          </a>
          <a href="https://loeitech.appedr.com/edr/login.do" target="_blank" class="flex items-center space-x-2 p-2 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition">
            <img src="/svg/EDR.png" alt="EDR" class="w-3 h-3">
            <span class="text-xs font-medium">ระบบ EDR</span>
          </a>
            </div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <img src="images/logo/Mikrotik--Streamline-Simple-Icons.svg" alt="MikroTik Logo" class="h-12 w-auto">
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-gray-800 leading-tight">MikroTik</span>
                        <span class="text-sm text-gray-500">Loei Technical College</span>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8">
                    <a href="#home" class="nav-link text-gray-600 hover:text-primary-600 font-medium py-2"><?= $lang['nav_home'] ?></a>
                    <a href="#courses" class="nav-link text-gray-600 hover:text-primary-600 font-medium py-2"><?= $lang['nav_course'] ?></a>
                    <a href="#usage" class="nav-link text-gray-600 hover:text-primary-600 font-medium py-2"><?= $lang['nav_usage'] ?></a>
                    <a href="#activity" class="nav-link text-gray-600 hover:text-primary-600 font-medium py-2"><?= $lang['nav_activity'] ?></a>
                    <a href="#consultants" class="nav-link text-gray-600 hover:text-primary-600 font-medium py-2"><?= $lang['nav_consultant'] ?></a>
                    <a href="#contact" class="nav-link text-gray-600 hover:text-primary-600 font-medium py-2"><?= $lang['nav_contact'] ?></a>
                    <div class="border-l pl-4 ml-2 flex items-center gap-2">
                        <a href="?lang=th" class="font-medium hover:text-primary-600 <?= $_SESSION['lang'] == 'th' ? 'text-primary-600' : 'text-gray-400' ?>">TH</a>
                        <span class="text-gray-300">|</span>
                        <a href="?lang=en" class="font-medium hover:text-primary-600 <?= $_SESSION['lang'] == 'en' ? 'text-primary-600' : 'text-gray-400' ?>">EN</a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" onclick="toggleMobileMenu()" class="md:hidden text-gray-600 hover:text-primary-600 focus:outline-none">
                    <span id="menu-icon" class="material-symbols-outlined text-3xl transition-transform duration-300">menu</span>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="md:hidden max-h-0 overflow-hidden transition-all duration-300 ease-in-out">
                <div class="pb-4 pt-2 space-y-1 border-t border-gray-100">
                    <a href="#home" onclick="closeMobileMenu()" class="block px-4 py-2.5 text-gray-600 hover:bg-primary-50 hover:text-primary-600 rounded-lg font-medium transition"><?= $lang['nav_home'] ?></a>
                    <a href="#courses" onclick="closeMobileMenu()" class="block px-4 py-2.5 text-gray-600 hover:bg-primary-50 hover:text-primary-600 rounded-lg font-medium transition"><?= $lang['nav_course'] ?></a>
                    <a href="#usage" onclick="closeMobileMenu()" class="block px-4 py-2.5 text-gray-600 hover:bg-primary-50 hover:text-primary-600 rounded-lg font-medium transition"><?= $lang['nav_usage'] ?></a>
                    <a href="#activity" onclick="closeMobileMenu()" class="block px-4 py-2.5 text-gray-600 hover:bg-primary-50 hover:text-primary-600 rounded-lg font-medium transition"><?= $lang['nav_activity'] ?></a>
                    <a href="#consultants" onclick="closeMobileMenu()" class="block px-4 py-2.5 text-gray-600 hover:bg-primary-50 hover:text-primary-600 rounded-lg font-medium transition"><?= $lang['nav_consultant'] ?></a>
                    <a href="#contact" onclick="closeMobileMenu()" class="block px-4 py-2.5 text-gray-600 hover:bg-primary-50 hover:text-primary-600 rounded-lg font-medium transition"><?= $lang['nav_contact'] ?></a>
                    <div class="flex items-center gap-3 px-4 pt-2 border-t border-gray-100 mt-2">
                        <a href="?lang=th" class="font-medium hover:text-primary-600 <?= $_SESSION['lang'] == 'th' ? 'text-primary-600' : 'text-gray-400' ?>">TH</a>
                        <span class="text-gray-300">|</span>
                        <a href="?lang=en" class="font-medium hover:text-primary-600 <?= $_SESSION['lang'] == 'en' ? 'text-primary-600' : 'text-gray-400' ?>">EN</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero / Slider -->
    <?php
    $hero_dir = __DIR__ . '/images/gallery';
    $hero_images = glob($hero_dir . "/*.{webp,jpg,jpeg,png,gif}", GLOB_BRACE);
    if ($hero_images) {
        // Prefer WebP if available for the same image (glob sorted order might help, but let's be explicit)
        $selected_image = $hero_images[array_rand($hero_images)];
        $hero_bg = 'images/gallery/' . basename($selected_image);
    } else {
        $hero_bg = 'https://images.unsplash.com/photo-1544197150-b99a580bbcbf?q=80&w=2071&auto=format&fit=crop';
    }
    ?>
    <header id="home" class="relative bg-gray-100 h-[500px] flex items-center justify-center overflow-hidden">
        <img src="<?= $hero_bg ?>" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 to-gray-900/40"></div>
        <div class="relative z-10 text-center text-white max-w-4xl px-4">
            <h1 class="text-4xl md:text-6xl font-bold mb-4"><?= $lang['hero_title'] ?></h1>
            <p class="text-xl md:text-2xl text-gray-200 font-light mb-8"><?= $lang['hero_subtitle'] ?></p>
            <p class="max-w-2xl mx-auto text-gray-300 mb-8 leading-relaxed">
                <?= $lang['hero_desc'] ?>
            </p>
            <div>
                 <a href="#courses" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-3 rounded-full font-medium transition shadow-lg inline-flex items-center gap-2">
                    <?= $lang['hero_btn'] ?> <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Intro & Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        
        <!-- Intro -->
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-gray-800 mb-4 border-b-2 border-primary-600 inline-block pb-2"><?= $lang['intro_title'] ?></h2>
            <p class="text-gray-600 max-w-3xl mx-auto mt-6 text-lg">
                <?= $lang['intro_desc'] ?>
            </p>
        </div>

        <!-- Courses Section -->
        <section id="courses" class="mb-24 scroll-mt-24">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-8 bg-primary-600 rounded"></div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800"><?= $lang['course_title'] ?></h2>
            </div>

            <div class="grid md:grid-cols-2 gap-12 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8 md:p-12">
                    <h3 class="text-2xl font-bold text-primary-600 mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined">school</span> <?= $lang['course_mtcna_title'] ?>
                    </h3>
                    <p class="text-lg font-medium text-gray-800 mb-2"><?= $lang['course_mtcna_subtitle'] ?></p>
                    <p class="text-gray-600 mb-6"><?= $lang['course_mtcna_desc'] ?></p>
                    
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 border-l-4 border-primary-600 pl-3"><?= $lang['course_topics_title'] ?></h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <?php for ($i = 1; $i <= 9; $i++): ?>
                            <div class="flex items-center gap-3 bg-gray-50 rounded-lg px-3 py-2.5 hover:bg-primary-50 transition">
                                <span class="w-6 h-6 bg-primary-600 text-white rounded-full flex items-center justify-center text-xs font-bold shrink-0"><?= $i ?></span>
                                <span class="text-sm text-gray-700"><?= $lang['course_topic_' . $i] ?></span>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 flex items-center justify-center p-8">
                    <!-- YouTube Video -->
                    <div class="w-full aspect-video rounded-xl shadow-inner overflow-hidden">
                        <iframe 
                            src="https://www.youtube.com/embed/gVDubJTegV4" 
                            title="MikroTik MTCNA" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen 
                            class="w-full h-full rounded-xl"
                        ></iframe>
                    </div>
                </div>
            </div>
        </section>

        <!-- Usage Section -->
        <section id="usage" class="mb-24 scroll-mt-24">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-8 bg-emerald-600 rounded"></div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800"><?= $lang['usage_title'] ?></h2>
            </div>

            <!-- Curriculum Integration -->
            <div class="mb-16">
                <h3 class="text-xl font-bold text-gray-700 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">auto_stories</span> <?= $lang['usage_curriculum_title'] ?>
                </h3>
                <div class="grid md:grid-cols-3 gap-6">
                    <!-- Subject 1 -->
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition card-shadow">
                        <div class="text-emerald-600 font-bold mb-1"><?= $lang['usage_subject_1_title'] ?></div>
                        <h4 class="font-bold text-gray-800 mb-3"><?= $lang['usage_subject_1_name'] ?></h4>
                        <div class="text-sm font-semibold text-gray-600 mb-3 bg-emerald-50 p-2 rounded"><?= $lang['usage_subject_1_topic'] ?></div>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_1_content_1'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_1_content_2'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_1_content_3'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_1_content_4'] ?></li>
                        </ul>
                    </div>
                    <!-- Subject 2 -->
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition card-shadow">
                        <div class="text-emerald-600 font-bold mb-1"><?= $lang['usage_subject_2_title'] ?></div>
                        <h4 class="font-bold text-gray-800 mb-3"><?= $lang['usage_subject_2_name'] ?></h4>
                        <div class="text-sm font-semibold text-gray-600 mb-3 bg-emerald-50 p-2 rounded"><?= $lang['usage_subject_2_topic'] ?></div>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_2_content_1'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_2_content_2'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_2_content_3'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_2_content_4'] ?></li>
                        </ul>
                    </div>
                    <!-- Subject 3 -->
                    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition card-shadow">
                        <div class="text-emerald-600 font-bold mb-1"><?= $lang['usage_subject_3_title'] ?></div>
                        <h4 class="font-bold text-gray-800 mb-3"><?= $lang['usage_subject_3_name'] ?></h4>
                        <div class="text-sm font-semibold text-gray-600 mb-3 bg-emerald-50 p-2 rounded"><?= $lang['usage_subject_3_topic'] ?></div>
                        <ul class="text-sm text-gray-600 space-y-2">
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_3_content_1'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_3_content_2'] ?></li>
                            <li class="flex gap-2"><i class="fas fa-check text-emerald-500 mt-1"></i> <?= $lang['usage_subject_3_content_3'] ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Infrastructure Integration -->
            <div class="bg-emerald-50 rounded-2xl p-8 md:p-12 border border-emerald-100">
                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">settings_input_component</span> <?= $lang['usage_infra_title'] ?>
                </h3>
                <p class="text-gray-600 mb-8"><?= $lang['usage_infra_arch_title'] ?>: <?= $lang['usage_infra_arch_desc'] ?></p>

                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0">
                                <span class="material-symbols-outlined text-emerald-600">router</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800"><?= $lang['usage_infra_core_title'] ?></h4>
                                <p class="text-sm text-gray-600 mt-1"><?= $lang['usage_infra_core_desc'] ?></p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 bg-white rounded-lg flex items-center justify-center shadow-sm shrink-0 p-1.5">
                                <img src="images/logo/proxmox.png" alt="Proxmox" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800"><?= $lang['usage_infra_vfw_title'] ?></h4>
                                <p class="text-sm text-gray-600 mt-1"><?= $lang['usage_infra_vfw_desc'] ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-emerald-100">
                        <h4 class="font-bold text-emerald-600 mb-4 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">hub</span> <?= $lang['usage_infra_services_title'] ?>
                        </h4>
                        <ul class="space-y-4 text-sm text-gray-600">
                            <li class="flex items-start gap-3">
                                <span class="w-6 h-6 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600 shrink-0 text-xs font-bold">1</span>
                                <span><?= $lang['usage_infra_svc_1'] ?></span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-6 h-6 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600 shrink-0 text-xs font-bold">2</span>
                                <span><?= $lang['usage_infra_svc_2'] ?></span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-6 h-6 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600 shrink-0 text-xs font-bold">3</span>
                                <span><?= $lang['usage_infra_svc_3'] ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Powered by logos -->
                <div class="mt-8 pt-6 border-t border-emerald-200 flex flex-wrap items-center justify-center gap-8">
                    <span class="text-xs text-gray-400 uppercase tracking-widest font-semibold">Powered by</span>
                    <img src="images/logo/MikroTik_Logo_(2022).svg" alt="MikroTik" class="h-8 opacity-60 hover:opacity-100 transition">
                    <img src="images/logo/proxmox.png" alt="Proxmox VE" class="h-8 opacity-60 hover:opacity-100 transition">
                </div>
            </div>
        </section>

        <!-- Activities Section -->
        <section id="activity" class="mb-24 scroll-mt-24">
           <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-8 bg-secondary rounded"></div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800"><?= $lang['activity_title'] ?></h2>
            </div>
            
            <!-- Dynamic Gallery Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php
                $gallery_dir = __DIR__ . '/images/gallery';
                $gallery_url = 'images/gallery';
                
                // Scan for images (jpg, jpeg, png, gif, webp)
                $images = glob($gallery_dir . "/*.{webp,jpg,jpeg,png,gif}", GLOB_BRACE);
                
                if ($images) {
                    shuffle($images); // สุ่มลำดับภาพ
                    foreach ($images as $image) {
                        $filename = basename($image);
                        $file_url = $gallery_url . '/' . $filename;
                        ?>
                        <div class="aspect-[4/3] rounded-lg overflow-hidden group relative card-shadow cursor-pointer transition-all duration-300 hover:z-10" onclick="openLightbox('<?= $file_url ?>')">
                            <img src="<?= $file_url ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" loading="lazy">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center pointer-events-none">
                                <span class="material-symbols-outlined text-white text-3xl">zoom_in</span>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    // Fallback placeholders if no images found
                    for ($i = 1; $i <= 8; $i++) {
                        ?>
                        <div class="aspect-[4/3] rounded-lg overflow-hidden group relative card-shadow">
                            <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-110 transition duration-500">
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition"></div>
                            <div class="absolute bottom-2 left-2 bg-black/50 text-white text-xs px-2 py-1 rounded backdrop-blur-sm">
                                ตัวอย่างภาพกิจกรรม (Upload ภาพลง images/gallery)
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
            
            <div class="text-center mt-8">
                <button class="px-6 py-2 border border-gray-300 rounded-full text-gray-600 hover:bg-gray-50 transition"><?= $lang['activity_view_all'] ?></button>
            </div>
        </section>

        <!-- Consultants Section -->
        <section id="consultants" class="mb-16 scroll-mt-24">
             <div class="flex items-center gap-3 mb-8">
                <div class="w-1.5 h-8 bg-gray-600 rounded"></div>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-800"><?= $lang['consultant_title'] ?></h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $trainers = [
                    [
                        'file' => 'mr-korarak-mtcna-mtctce.png',
                        'name' => $lang['consultant_1_name'],
                        'role' => $lang['consultant_role_trainer_admin'],
                        'certs' => ['MTCNA', 'MTCTCE']
                    ],
                    [
                        'file' => 'miss-chanidapha-mtcna-mtctce.png',
                        // Corrected filename from content.md to match actual file
                        'name' => $lang['consultant_2_name'],
                        'role' => $lang['consultant_role_trainer'],
                        'certs' => ['MTCNA', 'MTCTCE']
                    ],
                    [
                        'file' => 'mr-chaiyut-mtcna-mtctce.png',
                        'name' => $lang['consultant_3_name'],
                        'role' => $lang['consultant_role_trainer'],
                        'certs' => ['MTCNA', 'MTCTCE']
                    ],
                    /* [
                        'file' => 'miss-sawarin-mtcna.png',
                        // Corrected filename from content.md to match actual file
                        'name' => $lang['consultant_4_name'],
                        'role' => $lang['consultant_role_assistant'],
                        'certs' => ['MTCNA']
                    ] */
                ];

                foreach ($trainers as $trainer) {
                ?>
                <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden p-6 flex flex-col items-center text-center card-shadow hover:bg-gray-50 transition min-h-[400px]">
                    <div class="w-48 h-48 mb-6 relative"> <!-- Increased size from w-24 h-24 to w-48 h-48 -->
                        <?php 
                        $image_path = "images/trainer/" . $trainer['file'];
                        $webp_path = str_replace(['.png', '.jpg', '.jpeg'], '.webp', $image_path);
                        $display_path = file_exists(__DIR__ . '/' . $webp_path) ? $webp_path : $image_path;
                        ?>
                        <img src="<?= $display_path ?>" class="w-full h-full rounded-full object-cover shadow-md border-4 border-white ring-2 ring-gray-100">
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-lg mb-2"><?= $trainer['name'] ?></h4>
                        <p class="text-sm text-gray-500 mb-4 uppercase tracking-wide font-semibold"><?= $trainer['role'] ?></p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <?php foreach ($trainer['certs'] as $cert): 
                                $bg_color = ($cert == 'MTCNA') ? 'bg-blue-100 text-blue-800' : 'bg-indigo-100 text-indigo-800';
                            ?>
                            <span class="inline-block <?= $bg_color ?> text-xs px-3 py-1 rounded-full font-bold border border-white shadow-sm hover:scale-105 transition"><?= $cert ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-800 text-gray-300 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
             <div class="grid md:grid-cols-3 gap-12 mb-12">
                 <!-- Contact Info -->
                 <div>
                    <h5 class="text-white font-bold text-lg mb-6 border-l-4 border-secondary pl-3"><?= $lang['footer_contact_title'] ?></h5>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary mt-1">location_on</span>
                            <span><?= $lang['footer_address'] ?></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-secondary">call</span>
                            <span>042-811-591</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-secondary">mail</span>
                            <span>info@loeitech.ac.th</span>
                        </li>
                    </ul>
                 </div>

                 <!-- Links -->
                 <div>
                     <h5 class="text-white font-bold text-lg mb-6 border-l-4 border-secondary pl-3"><?= $lang['footer_dept_title'] ?></h5>
                     <ul class="space-y-2 text-sm">
                         <li><a href="https://loeitech.ac.th" class="hover:text-white transition"><?= $lang['footer_dept_home'] ?></a></li>
                         <li><a href="#" class="hover:text-white transition"><?= $lang['footer_dept_reg'] ?></a></li>
                         <li><a href="#" class="hover:text-white transition"><?= $lang['footer_dept_data'] ?></a></li>
                         <li><a href="#" class="hover:text-white transition"><?= $lang['footer_dept_it'] ?></a></li>
                     </ul>
                 </div>

                 <!-- Social -->
                 <div>
                    <h5 class="text-white font-bold text-lg mb-6 border-l-4 border-secondary pl-3"><?= $lang['footer_social_title'] ?></h5>
                    <div class="flex items-center space-x-2 bg-gray-700/50 p-2 rounded-full">
                        <a href="https://facebook.com/www.loeitech.ac.th" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-600 text-white shadow-sm hover:bg-blue-600 hover:scale-110 hover:shadow-md transition duration-300">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.youtube.com/@loeitechnicalcollege1556" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-600 text-white shadow-sm hover:bg-red-600 hover:scale-110 hover:shadow-md transition duration-300">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.tiktok.com/@businessloeitech" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-600 text-white shadow-sm hover:bg-black hover:scale-110 hover:shadow-md transition duration-300">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <div class="w-px h-5 bg-gray-500 mx-1"></div>
                        <a href="https://loeitech.appedr.com/edr/login.do" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-600 text-white shadow-sm hover:bg-emerald-500 hover:scale-110 hover:shadow-md transition duration-300">
                            <img src="/svg/EDR.png" alt="EDR" class="w-4 h-4 object-contain">
                        </a>
                    </div>
                    <div class="mt-8">
                        <img src="/svg/loeitech-logo.png" class="h-12 opacity-80 grayscale hover:grayscale-0 transition">
                    </div>
                 </div>
             </div>

             <div class="border-t border-gray-700 pt-8 text-center text-sm">
                 <p><?= $lang['footer_copyright'] ?></p>
             </div>
        </div>
    </footer>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="fixed inset-0 z-[9999] bg-black/90 hidden items-center justify-center cursor-pointer" onclick="closeLightbox()">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 transition z-10">&times;</button>
        <img id="lightbox-img" src="" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl" onclick="event.stopPropagation()">
    </div>

    <script>
    // Lightbox
    function openLightbox(src) {
        const lb = document.getElementById('lightbox');
        document.getElementById('lightbox-img').src = src;
        lb.classList.remove('hidden');
        lb.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        const lb = document.getElementById('lightbox');
        lb.classList.add('hidden');
        lb.classList.remove('flex');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

    // Mobile Menu Toggle
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('menu-icon');
        if (menu.style.maxHeight && menu.style.maxHeight !== '0px') {
            closeMobileMenu();
        } else {
            menu.style.maxHeight = menu.scrollHeight + 'px';
            icon.textContent = 'close';
            icon.style.transform = 'rotate(90deg)';
        }
    }
    function closeMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        const icon = document.getElementById('menu-icon');
        menu.style.maxHeight = '0px';
        icon.textContent = 'menu';
        icon.style.transform = 'rotate(0deg)';
    }
    </script>

</body>
</html>
