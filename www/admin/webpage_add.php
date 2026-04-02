<?php
include 'middleware.php';
ob_start();
include '../condb/condb.php';

// Helper function to convert Editor.js blocks to HTML
function editorJsToHtml($data) {
    if (!is_array($data) || empty($data['blocks'])) return '';
    
    $html = '';
    foreach ($data['blocks'] as $block) {
        switch ($block['type']) {
            case 'header':
                $level = isset($block['data']['level']) ? $block['data']['level'] : 2;
                 $alignClass = isset($block['data']['alignment']) ? 'text-' . $block['data']['alignment'] : 'text-left';
                if(isset($block['tunes']['alignment']['alignment'])) {
                     $alignClass = 'text-' . $block['tunes']['alignment']['alignment'];
                }
                $html .= "<h{$level} class='text-2xl font-bold my-4 {$alignClass}'>{$block['data']['text']}</h{$level}>";
                break;
            case 'paragraph':
                $alignClass = isset($block['data']['alignment']) ? 'text-' . $block['data']['alignment'] : 'text-left';
                if(isset($block['tunes']['alignment']['alignment'])) {
                     $alignClass = 'text-' . $block['tunes']['alignment']['alignment'];
                }
                $html .= "<p class='mb-4 text-gray-700 leading-relaxed {$alignClass}'>{$block['data']['text']}</p>";
                break;
            case 'list':
                $tag = $block['data']['style'] === 'ordered' ? 'ol' : 'ul';
                $listClass = $tag === 'ol' ? 'list-decimal' : 'list-disc';
                // Alignment
                $alignClass = isset($block['tunes']['anyTuneName']['alignment']) ? 'text-' . $block['tunes']['anyTuneName']['alignment'] : 'text-left';
                if(isset($block['data']['alignment'])){
                     $alignClass = 'text-' . $block['data']['alignment'];
                }

                $html .= "<{$tag} class='{$listClass} ml-6 mb-4 space-y-2 {$alignClass}'>";
                foreach ($block['data']['items'] as $item) {
                     $html .= "<li>{$item}</li>";
                }
                $html .= "</{$tag}>";
                break;
            case 'image':
                $url = isset($block['data']['file']['url']) ? $block['data']['file']['url'] : '';
                $caption = isset($block['data']['caption']) ? $block['data']['caption'] : '';
                $withBorder = isset($block['data']['withBorder']) && $block['data']['withBorder'] ? 'border border-gray-200 p-2' : '';
                $withBackground = isset($block['data']['withBackground']) && $block['data']['withBackground'] ? 'bg-gray-50 p-4 rounded' : '';
                $stretched = isset($block['data']['stretched']) && $block['data']['stretched'] ? 'w-full' : '';
                
                $html .= "<figure class='my-6 text-center {$withBackground} {$withBorder}'>";
                $html .= "<img src='{$url}' alt='{$caption}' class='rounded-lg max-h-[500px] object-cover mx-auto {$stretched}'>";
                if ($caption) {
                    $html .= "<figcaption class='text-sm text-gray-500 mt-2'>{$caption}</figcaption>";
                }
                $html .= "</figure>";
                break;
            case 'columns':
                 $cols = $block['data']['cols'];
                 $html .= "<div class='grid grid-cols-1 md:grid-cols-" . count($cols) . " gap-4 my-6'>";
                 foreach($cols as $col){
                     $html .= "<div class='prose max-w-none'>";
                     if (isset($col['blocks'])) {
                        // Create a dummy data structure for recursive call
                        $dummyData = ['blocks' => $col['blocks']];
                        $html .= editorJsToHtml($dummyData);
                     }
                     $html .= "</div>";
                 }
                 $html .= "</div>";
                 break;
            case 'quote':
                $html .= "<blockquote class='border-l-4 border-blue-500 pl-4 py-2 my-4 italic text-gray-600 bg-gray-50 rounded-r'>";
                $html .= "<p>{$block['data']['text']}</p>";
                if (!empty($block['data']['caption'])) {
                    $html .= "<footer class='text-sm text-gray-500 mt-2'>— {$block['data']['caption']}</footer>";
                }
                $html .= "</blockquote>";
                break;
            case 'table':
                $content = $block['data']['content'];
                $html .= "<div class='overflow-x-auto my-6'><table class='min-w-full border border-gray-200'>";
                $isHead = isset($block['data']['withHeadings']) && $block['data']['withHeadings'];
                
                foreach ($content as $i => $row) {
                    $html .= "<tr>";
                    foreach ($row as $cell) {
                        $tag = ($isHead && $i === 0) ? 'th' : 'td';
                        $cellClass = ($isHead && $i === 0) ? 'bg-gray-100 font-semibold' : '';
                        $html .= "<{$tag} class='border p-2 min-w-[100px] {$cellClass}'>{$cell}</{$tag}>";
                    }
                    $html .= "</tr>";
                }
                $html .= "</table></div>";
                break;
            case 'delimiter':
                $html .= "<hr class='my-8 border-t border-gray-200'>";
                break;
            case 'linkTool':
                $link = $block['data']['link'];
                $meta = $block['data']['meta'];
                $title = isset($meta['title']) ? $meta['title'] : $link;
                $desc = isset($meta['description']) ? $meta['description'] : '';
                $image = isset($meta['image']['url']) ? $meta['image']['url'] : '';
                $site = isset($meta['site_name']) ? $meta['site_name'] : '';
                
                $html .= "<a href='{$link}' target='_blank' rel='nofollow noopener' class='block my-4 border border-gray-200 rounded-lg hover:bg-gray-50 flex overflow-hidden no-underline'>";
                $html .= "<div class='p-4 flex-1'>";
                $html .= "<h3 class='font-bold text-gray-800 mb-1'>{$title}</h3>";
                if ($desc) $html .= "<p class='text-sm text-gray-600 mb-2 line-clamp-2'>{$desc}</p>";
                if ($site) $html .= "<span class='text-xs text-gray-400'>{$site}</span>";
                $html .= "</div>";
                if ($image) {
                    $html .= "<div class='w-32 bg-cover bg-center hidden sm:block' style='background-image: url(\"{$image}\")'></div>";
                }
                $html .= "</a>";
                break;
            case 'attaches':
                $file = $block['data']['file'];
                $url = isset($file['url']) ? $file['url'] : '';
                $name = isset($file['name']) ? $file['name'] : 'Download File';
                $size = isset($file['size']) ? round($file['size'] / 1024, 2) . ' KB' : '';
                $extension = isset($file['extension']) ? strtoupper($file['extension']) : '';
                $title = isset($block['data']['title']) ? $block['data']['title'] : $name;
                
                $icon = 'fa-file-alt text-gray-400';
                if ($extension === 'PDF') $icon = 'fa-file-pdf text-red-500';
                elseif (in_array($extension, ['DOC', 'DOCX'])) $icon = 'fa-file-word text-blue-600';
                elseif (in_array($extension, ['XLS', 'XLSX'])) $icon = 'fa-file-excel text-green-600';
                elseif (in_array($extension, ['ZIP', 'RAR'])) $icon = 'fa-file-archive text-yellow-600';
                elseif (in_array($extension, ['JPG', 'JPEG', 'PNG', 'GIF'])) $icon = 'fa-image text-purple-600';

                $html .= "<a href='{$url}' target='_blank' class='flex items-center gap-4 p-4 my-4 border border-gray-200 rounded-xl hover:bg-gray-50 hover:shadow-sm transition bg-white no-underline group w-full max-w-2xl'>";
                $html .= "<div class='w-12 h-12 flex items-center justify-center rounded-lg bg-gray-50 border border-gray-100 group-hover:bg-white transition-colors flex-shrink-0'><i class='fas {$icon} text-2xl'></i></div>";
                $html .= "<div class='flex-1 min-w-0 flex flex-col justify-center'>";
                $html .= "<p class='font-bold text-gray-800 text-base group-hover:text-blue-600 transition-colors truncate m-0 leading-tight'>{$title}</p>";
                $html .= "<p class='text-xs text-gray-500 m-0 mt-1 leading-tight'>{$extension} &bull; {$size}</p>";
                $html .= "</div>";
                $html .= "<div class='w-10 h-10 flex items-center justify-center rounded-full bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors flex-shrink-0'><i class='fas fa-download text-sm'></i></div>";
                $html .= "</a>";
                break;
            case 'raw':
                $html .= "<div class='my-6 w-full overflow-hidden'>" . $block['data']['html'] . "</div>";
                break;
            case 'googleDrive':
                $html .= "<div class='my-6 w-full rounded-lg overflow-hidden border border-gray-200 shadow-sm'>" . $block['data']['embedCode'] . "</div>";
                break;
             // Add more types as needed
        }
    }
    return $html;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $slug = $_POST['slug'];
    $meta_description = $_POST['meta_description'];
    $meta_keywords = $_POST['meta_keywords'];
    $visible = isset($_POST['visible']) ? 1 : 0;
    
    // Editor Data
    $editor_json = $_POST['editor_json'];
    if (empty($editor_json) || $editor_json === 'undefined' || $editor_json === 'null') {
        $editor_json = '{}';
    }
    
    // Convert JSON to HTML for 'content' column
    $data = json_decode($editor_json, true);
    $content = editorJsToHtml($data);
    
    $created_by = $_SESSION['username'] ?? 'admin';

    // อัปโหลดรูป thumbnail ถ้ามี
    $thumbnail = '';
    if (!empty($_FILES['thumbnail']['name'])) {
        $target_dir = "../uploads/pages/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["thumbnail"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
            $thumbnail = "uploads/pages/" . $file_name;
        }
    }

    // ตรวจสอบว่า slug ซ้ำหรือไม่
    $check_stmt = $mysqli4->prepare("SELECT id FROM web_pages WHERE slug = ?");
    $check_stmt->bind_param("s", $slug);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Slug นี้มีอยู่แล้ว กรุณาใช้ Slug อื่น');</script>";
    } else {
        // Insert
        // Auto-fix: Ensure editor_json column exists
        $check_col = $mysqli4->query("SHOW COLUMNS FROM web_pages LIKE 'editor_json'");
        if ($check_col->num_rows == 0) {
            $mysqli4->query("ALTER TABLE web_pages ADD COLUMN editor_json LONGTEXT");
        }

        // IMPORTANT: Ensure editor_json column exists in DB first
        $insert = $mysqli4->prepare("INSERT INTO web_pages (title, slug, content, thumbnail, meta_description, meta_keywords, visible, created_by, editor_json) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("ssssssiss", $title, $slug, $content, $thumbnail, $meta_description, $meta_keywords, $visible, $created_by, $editor_json);
        
        if ($insert->execute()) {
            $new_id = $mysqli4->insert_id;
            echo "<script>
                alert('สร้างเพจใหม่สำเร็จ');
                window.location.href = 'webpage_edit.php?id=" . $new_id . "';
            </script>";
            exit;
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการสร้างเพจ: " . $mysqli4->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างเว็บเพจใหม่ (Notion Editor) - ระบบจัดการเนื้อหา</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Editor.js Clean Styles */
        .codex-editor {
            max-width: 800px;
            margin: 0 auto;
        }
        .ce-block__content {
            max-width: 800px;
        }
        .ce-toolbar__content {
            max-width: 800px;
        }
        .ce-popover { z-index: 50; }
        
        /* Typography mimics Notion */
        .ce-header { font-weight: bold; margin-bottom: 0.5rem; }
        .cdx-list { margin: 0; padding-left: 20px; list-style: disc;} 
        .preview-active { overflow: hidden; }
        #previewModal { backdrop-filter: blur(4px); }

        /* Match visual width and alignment of the site */
        .codex-editor { max-width: 1100px; margin: 0 auto; }
        .ce-block__content { max-width: 1000px; }
        .ce-toolbar__content { max-width: 1000px; }
    </style>
</head>
<body class="bg-gray-50 pb-24">
    <div class="max-w-[1400px] mx-auto py-8 px-4">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
             <div>
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-indigo-600 text-white p-2 rounded-lg shadow-md">
                        <i class="fas fa-plus-circle"></i>
                    </span>
                    สร้างเว็บเพจใหม่
                </h1>
                <p class="text-gray-500 mt-1 ml-12 italic">เขียนเนื้อหาแบบ Notion-style พร้อมเครื่องมือช่วย SEO</p>
             </div>
             
             <div class="flex gap-2">
                 <a href="webpages_manage.php" class="px-4 py-2 text-sm bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all hover:shadow-md">
                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                 </a>
             </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6" id="pageForm">
            <!-- TOP Properties Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Status -->
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black tracking-widest text-indigo-400 uppercase">PUBLISHING</h3>
                    <label class="flex items-center justify-between p-3 bg-gray-50/50 border border-gray-100 rounded-2xl cursor-pointer hover:bg-indigo-50/30 transition-colors">
                        <span class="text-xs font-bold text-gray-600">เผยแพร่ทันที</span>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="visible" value="1" checked class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                        </div>
                    </label>
                </div>

                <!-- Thumbnail -->
                <div class="md:col-span-1 space-y-4">
                    <h3 class="text-[10px] font-black tracking-widest text-indigo-400 uppercase">THUMBNAIL</h3>
                    <div class="relative group aspect-video rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 flex items-center justify-center overflow-hidden hover:border-indigo-300 hover:bg-indigo-50/30 transition-all cursor-pointer" onclick="document.getElementById('thumbnailInput').click()">
                        <div class="text-center group-hover:scale-110 transition-transform">
                            <i class="fas fa-image text-2xl text-gray-300 group-hover:text-indigo-400"></i>
                            <p class="text-[10px] text-gray-400 mt-1 font-bold italic">อัปโหลดรูปปก</p>
                        </div>
                        <img id="thumbnailPreview" class="absolute inset-0 w-full h-full object-cover hidden">
                        <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" class="hidden" onchange="previewThumbnail(this)">
                    </div>
                </div>

                <!-- SEO Description -->
                <div class="md:col-span-2 space-y-4">
                    <h3 class="text-[10px] font-black tracking-widest text-indigo-400 uppercase">SEARCH OPTIMIZATION (SEO)</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="relative">
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-[10px] font-bold text-gray-500 px-1">DESCRIPTION</label>
                                <span id="descCount" class="text-[9px] font-mono text-gray-400">0/160</span>
                            </div>
                            <textarea name="meta_description" id="meta_description" rows="2" placeholder="สรุปเนื้อหาเบื้องต้นให้ Search Engine..."
                                    class="w-full bg-gray-50/50 border-gray-200 rounded-2xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-100 outline-none border transition-all resize-none shadow-inner"
                                    oninput="document.getElementById('descCount').textContent = this.value.length + '/160'; updateCountColor(this, 'descCount')"></textarea>
                        </div>
                        <div class="relative">
                            <label class="block text-[10px] font-bold text-gray-500 px-1 mb-1">KEYWORDS</label>
                            <input type="text" name="meta_keywords" placeholder="keyword1, keyword2..." 
                                    class="w-full bg-gray-50/50 border-gray-200 rounded-2xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-100 outline-none border transition-all shadow-inner">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Title & Editor -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 min-h-[800px] overflow-hidden flex flex-col items-center">
                <!-- Internal Title Section -->
                <div class="w-full max-w-[1100px] px-10 pt-16 pb-8 flex flex-col items-center">
                    <input type="text" name="title" id="pageTitle" required placeholder="หัวข้อเพจ..." 
                            class="w-full text-5xl font-extrabold focus:outline-none placeholder-gray-100 text-gray-900 bg-transparent border-none p-0 mb-6 transition-all tracking-tight leading-tight text-center">
                    
                    <div class="flex items-center gap-2 py-2 px-4 bg-gray-50 rounded-2xl border border-gray-100 group w-fit transition-all hover:border-indigo-200 mx-auto">
                        <i class="fas fa-link text-xs text-gray-300 group-hover:text-indigo-400 transition-colors"></i>
                        <span class="text-[10px] font-black text-gray-400 tracking-wider">URL:</span>
                        <input type="text" name="slug" id="pageSlug" required placeholder="url-slug" 
                                class="bg-transparent border-none focus:ring-0 text-xs text-indigo-600 font-bold p-0 min-w-[200px]">
                        <button type="button" onclick="syncSlug()" class="text-gray-300 hover:text-indigo-500 transition-all p-1" title="อัปเดต Slug จากหัวข้อ">
                            <i class="fas fa-sync-alt text-xs"></i>
                        </button>
                    </div>

                    <hr class="mt-12 border-gray-50">
                </div>

                <!-- Editor js Area -->
                <div class="w-full cursor-text pb-20" onclick="document.getElementById('editorjs').focus()">
                    <div id="editorjs"></div>
                </div>
            </div>
            
            <input type="hidden" name="editor_json" id="editor_json">

            <!-- Sticky Save Bar -->
            <div class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-4xl px-4 z-50">
                <div class="bg-white/80 backdrop-blur-md rounded-2xl shadow-[0_10px_40px_-5px_rgba(0,0,0,0.1)] border border-white/50 p-3 flex justify-between items-center gap-4">
                    <div class="flex items-center gap-4 ml-4">
                         <div id="saveStatus" class="flex items-center gap-2 text-gray-400 text-xs font-medium">
                            <span class="w-2 h-2 rounded-full bg-gray-300"></span> ยังไม่ได้บันทึก
                         </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="button" @click="previewPage()" class="px-6 py-2.5 bg-gray-50 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition flex items-center gap-2">
                            <i class="fas fa-eye"></i> ดูตัวอย่าง
                        </button>
                        <button type="button" onclick="savePage()" class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition transform active:scale-95 flex items-center gap-2">
                            <i class="fas fa-save"></i> บันทึกเพจ
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Modal (Full Screen) -->
    <div id="previewModal" class="fixed inset-0 z-[100] bg-black/60 hidden flex items-center justify-center p-4 md:p-10" x-cloak>
        <div class="bg-white w-full h-full rounded-3xl overflow-hidden flex flex-col shadow-2xl">
            <div class="p-4 border-b flex justify-between items-center bg-gray-50">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-600 rounded text-[10px] font-black tracking-widest">LIVE PREVIEW</span>
                    <h2 class="font-bold text-gray-800" id="prevTitle">Title Preview</h2>
                </div>
                <button onclick="closePreview()" class="w-10 h-10 rounded-full hover:bg-gray-200 transition-colors flex items-center justify-center text-gray-500">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto bg-white p-8 md:p-16">
                 <div class="max-w-4xl mx-auto prosenotnotion">
                    <header class="mb-12">
                        <img id="prevThumb" class="w-full h-[300px] object-cover rounded-3xl mb-8 hidden">
                        <h1 id="prevHeader" class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight"></h1>
                    </header>
                    <div id="previewContent" class="space-y-4"></div>
                 </div>
            </div>
        </div>
    </div>

    <!-- Editor.js & Plugins (Local) -->
    <script src="../assets/vendor/editorjs/editor.min.js"></script>
    <script src="../assets/vendor/editorjs/header.js"></script>
    <script src="../assets/vendor/editorjs/list.js"></script>
    <script src="../assets/vendor/editorjs/image.js"></script>
    <script src="../assets/vendor/editorjs/quote.js"></script>
    <script src="../assets/vendor/editorjs/table.js"></script>
    <script src="../assets/vendor/editorjs/delimiter.js"></script>
    <script src="../assets/vendor/editorjs/link.js"></script>
    <script src="../assets/vendor/editorjs/hyperlink.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@calumk/editorjs-columns@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/editorjs-text-alignment-blocktune@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/attaches@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@editorjs/raw@latest"></script>
    <script src="../assets/vendor/editorjs/google-drive-embed.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Global scripts for convenience
        function previewThumbnail(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('thumbnailPreview');
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function updateCountColor(area, spanId) {
            const count = area.value.length;
            const span = document.getElementById(spanId);
            if(count > 160) span.classList.add('text-red-500');
            else if(count > 120) span.classList.add('text-orange-500');
            else span.classList.remove('text-red-500', 'text-orange-500');
        }

        document.addEventListener('alpine:init', () => {
             // We can also wrap everything in Alpine if needed, but keeping existing logic
        });

        document.addEventListener('DOMContentLoaded', function() {
            let hasUnsavedChanges = false;
            const titleInput = document.getElementById('pageTitle');
            const slugInput = document.getElementById('pageSlug');

            // Auto-slug logic
            titleInput.addEventListener('input', function() {
                if(!slugInput.dataset.manual) {
                    const slug = this.value
                        .toLowerCase()
                        .trim()
                        .replace(/[^\u0E00-\u0E7Fa-zA-Z0-9\s-]/g, '') // Keep Thai, Eng, Numbers, Space, Dash
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-');
                    slugInput.value = slug;
                }
                hasUnsavedChanges = true;
                updateSaveStatus();
            });

            slugInput.addEventListener('change', () => slugInput.dataset.manual = "1");

            function updateSaveStatus() {
                const status = document.getElementById('saveStatus');
                if(hasUnsavedChanges) {
                    status.innerHTML = '<span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span> มีการเปลี่ยนแปลงที่ยังไม่ได้บันทึก';
                    status.classList.replace('text-gray-400', 'text-orange-600');
                }
            }

            // Before unload guard
            window.addEventListener('beforeunload', (e) => {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });

            try {
                if (typeof EditorJS === 'undefined') {
                    throw new Error('EditorJS is not loaded');
                }

                window.editor = new EditorJS({
                    holder: 'editorjs',
                    placeholder: 'เขียนอะไรบางอย่าง...',
                    tools: {
                        paragraph: {
                            inlineToolbar: true,
                            tunes: ['alignment']
                        },
                        header: {
                            class: Header,
                            inlineToolbar: true,
                            config: { placeholder: 'หัวข้อ', levels: [2, 3, 4], defaultLevel: 2 },
                            tunes: ['alignment']
                        },
                        list: {
                            class: EditorjsList,
                            inlineToolbar: true,
                            config: { defaultStyle: 'unordered' },
                            tunes: ['alignment']
                        },
                        image: {
                            class: ImageTool,
                            config: {
                                endpoints: {
                                    byFile: 'upload_image.php',
                                    byUrl: 'upload_image.php?url='
                                }
                            }
                        },
                        quote: Quote,
                        table: {
                             class: Table,
                             inlineToolbar: true,
                        },
                        delimiter: Delimiter,
                        linkTool: {
                            class: LinkTool,
                            config: { endpoint: 'fetch_url.php' }
                        },
                        hyperlink: {
                            class: Hyperlink,
                            config: {
                                shortcut: 'CMD+K',
                                target: '_blank',
                                rel: 'nofollow',
                                availableTargets: ['_blank', '_self'],
                                availableRels: ['author', 'noreferrer'],
                                validate: false,
                            }
                        },
                        columns: {
                            class: editorjsColumns,
                            config: {
                                tools: {
                                    header: Header,
                                    paragraph: {
                                        class:  EditorJS.Paragraph,
                                        inlineToolbar: true,
                                    },
                                    list: EditorjsList,
                                    image: ImageTool,
                                }
                            }
                        },
                        alignment: {
                            class:AlignmentBlockTune,
                            config:{
                                default: "left",
                                blocks: {
                                    header: 'center',
                                    list: 'right'
                                }
                            },
                        },
                        attaches: {
                            class: AttachesTool,
                            config: {
                                endpoint: 'upload_file.php'
                            }
                        },
                        raw: RawTool,
                        googleDrive: GoogleDriveEmbed
                    },
                    data: {},
                    onReady: () => {
                        console.log('Editor.js is ready to work!');
                        document.getElementById('editorjs').style.minHeight = '300px'; 
                    },
                    onChange: () => {
                        hasUnsavedChanges = true;
                        updateSaveStatus();
                    }
                });

                window.savePage = function() {
                    window.editor.save().then((outputData) => {
                        document.getElementById('editor_json').value = JSON.stringify(outputData);
                        hasUnsavedChanges = false; // Disable guard
                        document.getElementById('pageForm').submit();
                    }).catch((error) => {
                        console.error('Saving failed: ', error);
                        Swal.fire('Error', 'เกิดข้อผิดพลาดในการบันทึก: ' + error.message, 'error');
                    });
                };

                window.previewPage = function() {
                    window.editor.save().then((data) => {
                        const modal = document.getElementById('previewModal');
                        const content = document.getElementById('previewContent');
                        
                        document.getElementById('prevTitle').textContent = document.getElementById('pageTitle').value || 'Untitled';
                        document.getElementById('prevHeader').textContent = document.getElementById('pageTitle').value || 'Untitled';
                        
                        const thumbData = document.getElementById('thumbnailPreview').src;
                        const prevThumb = document.getElementById('prevThumb');
                        if(thumbData) {
                            prevThumb.src = thumbData;
                            prevThumb.classList.remove('hidden');
                        } else {
                            prevThumb.classList.add('hidden');
                        }

                        // Generate simple HTML for preview (Mental model of editorJsToHtml)
                        content.innerHTML = '<p class="text-center text-gray-400 italic py-10">กำลังจำลองรูปแบบเนื้อหา...</p>';
                        
                        // In a real app, we might call back to server for true rendering, 
                        // but for convenience, we can do a simplified client render or a POST to a preview endpoint.
                        // For now, let's just show the raw structure or a quick mock.
                        
                        modal.classList.remove('hidden');
                        document.body.classList.add('preview-active');
                    });
                };
                
                window.closePreview = function() {
                    document.getElementById('previewModal').classList.add('hidden');
                    document.body.classList.remove('preview-active');
                }

            } catch (e) {
                console.error("Editor init error:", e);
                const editorArea = document.getElementById('editorjs');
                editorArea.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">Editor.js failed to load. Please check your internet connection or try refreshing. (${e.message})</span>
                </div>`;
            }
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>