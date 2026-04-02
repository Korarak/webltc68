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
        }
    }
    return $html;
}

// ตรวจสอบว่า id ถูกส่งมาหรือไม่
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<p class='text-red-600 text-center mt-10'>ไม่พบรหัสเพจที่ต้องการแก้ไข</p>";
    exit;
}

$id = (int)$_GET['id'];

// ดึงข้อมูลเพจ
$stmt = $mysqli4->prepare("SELECT * FROM web_pages WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

if (!$page) {
    echo "<p class='text-red-600 text-center mt-10'>ไม่พบข้อมูลเพจ</p>";
    exit;
}

// อัปเดตข้อมูลเมื่อมีการ submit
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
    // Generate HTML
    $data = json_decode($editor_json, true);
    $content = editorJsToHtml($data);
    
    $updated_by = $_SESSION['username'] ?? 'admin';

    // อัปโหลดรูป thumbnail ถ้ามี
    $thumbnail = $page['thumbnail'];
    
    if (isset($_POST['delete_thumbnail']) && $_POST['delete_thumbnail'] == '1') {
        if ($page['thumbnail'] && file_exists("../" . $page['thumbnail'])) {
            unlink("../" . $page['thumbnail']);
        }
        $thumbnail = NULL;
    }

    if (!empty($_FILES['thumbnail']['name'])) {
        $target_dir = "../uploads/pages/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["thumbnail"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_file)) {
            if ($page['thumbnail'] && file_exists("../" . $page['thumbnail']) && !isset($_POST['delete_thumbnail'])) {
                unlink("../" . $page['thumbnail']);
            }
            $thumbnail = "uploads/pages/" . $file_name;
        }
    }

    // Auto-fix: Ensure editor_json column exists
    $check_col = $mysqli4->query("SHOW COLUMNS FROM web_pages LIKE 'editor_json'");
    if ($check_col->num_rows == 0) {
        $mysqli4->query("ALTER TABLE web_pages ADD COLUMN editor_json LONGTEXT");
    }

    $update = $mysqli4->prepare("UPDATE web_pages 
        SET title=?, slug=?, content=?, thumbnail=?, meta_description=?, meta_keywords=?, visible=?, created_by=?, updated_at=NOW(), editor_json=?
        WHERE id=?");
    $update->bind_param("ssssssissi", $title, $slug, $content, $thumbnail, $meta_description, $meta_keywords, $visible, $updated_by, $editor_json, $id);
    
    if ($update->execute()) {
        echo "<script>
            alert('บันทึกการแก้ไขสำเร็จ');
            window.location.href = 'webpage_edit.php?id=" . $id . "';
        </script>";
        exit;
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการบันทึก: " . $mysqli4->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขเว็บเพจ (Notion Editor) - ระบบจัดการเนื้อหา</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .codex-editor { max-width: 800px; margin: 0 auto; }
        .ce-block__content { max-width: 800px; }
        .ce-toolbar__content { max-width: 1000px; }
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
                        <i class="fas fa-edit"></i>
                    </span>
                    แก้ไขเว็บเพจ
                </h1>
                <p class="text-gray-500 mt-1 ml-12 italic">กำลังจัดการ: <span class="text-indigo-600 font-bold"><?= e($page['title']) ?></span></p>
             </div>
             
             <div class="flex gap-2">
                 <a href="../app-webpage/page.php?slug=<?= urlencode($page['slug']) ?>" target="_blank" class="px-4 py-2 text-sm bg-indigo-50 border border-indigo-100 rounded-xl text-indigo-600 hover:bg-indigo-100 flex items-center gap-2 shadow-sm transition-all hover:shadow-md font-bold">
                    <i class="fas fa-external-link-alt"></i> ดูหน้าเว็บจริง
                 </a>
                 <a href="webpages_manage.php" class="px-4 py-2 text-sm bg-white border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 flex items-center gap-2 shadow-sm transition-all hover:shadow-md">
                    <i class="fas fa-arrow-left"></i> ย้อนกลับ
                 </a>
             </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6" id="pageForm">
            <!-- TOP Properties Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Status & Creation Info -->
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black tracking-widest text-indigo-400 uppercase">PUBLISHING</h3>
                    <label class="flex items-center justify-between p-3 bg-gray-50/50 border border-gray-100 rounded-2xl cursor-pointer hover:bg-indigo-50/30 transition-colors">
                        <span class="text-xs font-bold text-gray-600">เผยแพร่</span>
                        <div class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="visible" value="1" <?= $page['visible'] ? 'checked' : '' ?> class="sr-only peer">
                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-500"></div>
                        </div>
                    </label>
                    <div class="px-1 space-y-1">
                        <p class="text-[10px] text-gray-400">สร้างโดย: <span class="text-gray-600"><?= e($page['created_by']) ?></span></p>
                        <p class="text-[10px] text-gray-400">ล่าสุด: <span class="text-gray-600"><?= date('d M Y H:i', strtotime($page['updated_at'])) ?></span></p>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="md:col-span-1 space-y-4">
                    <h3 class="text-[10px] font-black tracking-widest text-indigo-400 uppercase">THUMBNAIL</h3>
                    <div class="relative group aspect-video rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 flex items-center justify-center overflow-hidden hover:border-indigo-300 hover:bg-indigo-50/30 transition-all cursor-pointer" onclick="document.getElementById('thumbnailInput').click()">
                        <?php if ($page['thumbnail']): ?>
                            <img id="thumbnailPreview" src="../<?= e($page['thumbnail']) ?>" class="absolute inset-0 w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <i class="fas fa-camera text-white text-2xl"></i>
                            </div>
                        <?php else: ?>
                            <div class="text-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-image text-2xl text-gray-300 group-hover:text-indigo-400"></i>
                                <p class="text-[10px] text-gray-400 mt-1 font-bold italic">อัปโหลดรูปปก</p>
                            </div>
                            <img id="thumbnailPreview" class="absolute inset-0 w-full h-full object-cover hidden">
                        <?php endif; ?>
                        <input type="file" name="thumbnail" id="thumbnailInput" accept="image/*" class="hidden" onchange="previewThumbnail(this)">
                    </div>
                    <?php if ($page['thumbnail']): ?>
                        <button type="button" onclick="deleteExistingThumbnail()" class="w-full py-1 text-[10px] font-bold text-red-400 hover:text-red-500 transition-colors">
                            <i class="fas fa-trash-alt mr-1"></i> ลบรูปภาพปัจจุบัน
                        </button>
                        <input type="hidden" name="delete_thumbnail" id="deleteThumbnail" value="0">
                    <?php endif; ?>
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
                                    oninput="document.getElementById('descCount').textContent = this.value.length + '/160'; updateCountColor(this, 'descCount')"><?= e($page['meta_description']) ?></textarea>
                        </div>
                        <div class="relative">
                            <label class="block text-[10px] font-bold text-gray-500 px-1 mb-1">KEYWORDS</label>
                            <input type="text" name="meta_keywords" value="<?= e($page['meta_keywords']) ?>" placeholder="keyword1, keyword2..." 
                                    class="w-full bg-gray-50/50 border-gray-200 rounded-2xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-100 outline-none border transition-all shadow-inner">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Title & Editor -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 min-h-[800px] overflow-hidden flex flex-col items-center">
                <!-- Internal Title Section -->
                <div class="w-full max-w-[1100px] px-10 pt-16 pb-8">
                    <input type="text" name="title" id="pageTitle" value="<?= e($page['title']) ?>" required placeholder="หัวข้อเพจ..." 
                            class="w-full text-5xl font-extrabold focus:outline-none placeholder-gray-100 text-gray-900 bg-transparent border-none p-0 mb-6 transition-all tracking-tight leading-tight">
                    
                    <div class="flex items-center gap-2 py-2 px-4 bg-gray-50 rounded-2xl border border-gray-100 group w-fit transition-all hover:border-indigo-200">
                        <i class="fas fa-link text-xs text-gray-300 group-hover:text-indigo-400 transition-colors"></i>
                        <span class="text-[10px] font-black text-gray-400 tracking-wider">URL:</span>
                        <input type="text" name="slug" id="pageSlug" value="<?= e($page['slug']) ?>" required placeholder="url-slug" 
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
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> ข้อมูลล่าสุด (Saved)
                         </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="button" onclick="previewPage()" class="px-6 py-2.5 bg-gray-50 text-gray-600 font-bold rounded-xl hover:bg-gray-100 transition flex items-center gap-2">
                            <i class="fas fa-eye"></i> ดูตัวอย่าง
                        </button>
                        <button type="button" onclick="savePage()" class="px-8 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-200 transition transform active:scale-95 flex items-center gap-2">
                            <i class="fas fa-save"></i> อัปเดตข้อมูล
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Preview Modal (Full Screen) -->
    <div id="previewModal" class="fixed inset-0 z-[100] bg-black/60 hidden flex items-center justify-center p-4 md:p-10">
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
                        <h1 id="prevHeader" class="text-4xl md:text-5xl font-extrabold text-gray-900 leading-tight text-center"></h1>
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

        function deleteExistingThumbnail() {
            Swal.fire({
                title: 'ยืนยันการลบรูปปก?',
                text: "รูปภาพจะถูกลบหลังจากที่คุณบันทึกข้อมูล",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'ลบรูปภาพ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteThumbnail').value = '1';
                    document.getElementById('thumbnailPreview').classList.add('hidden');
                }
            });
        }

        function getSlug(text) {
             return text.toLowerCase().trim()
                .replace(/[^\u0E00-\u0E7Fa-zA-Z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        function syncSlug() {
            const title = document.getElementById('pageTitle').value;
            document.getElementById('pageSlug').value = getSlug(title);
        }

        document.addEventListener('DOMContentLoaded', function() {
            let hasUnsavedChanges = false;
            const titleInput = document.getElementById('pageTitle');
            const slugInput = document.getElementById('pageSlug');
            
            // Initialization for character counts
            const descArea = document.getElementById('meta_description');
            document.getElementById('descCount').textContent = descArea.value.length + '/160';
            updateCountColor(descArea, 'descCount');

            titleInput.addEventListener('input', () => { hasUnsavedChanges = true; updateSaveStatus(); });
            slugInput.addEventListener('input', () => { hasUnsavedChanges = true; updateSaveStatus(); });
            document.querySelectorAll('input, textarea, select').forEach(el => {
                el.addEventListener('change', () => { hasUnsavedChanges = true; updateSaveStatus(); });
            });

            function updateSaveStatus() {
                const status = document.getElementById('saveStatus');
                if(hasUnsavedChanges) {
                    status.innerHTML = '<span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span> มีการเปลี่ยนแปลงที่ยังไม่ได้บันทึก';
                    status.classList.replace('text-gray-400', 'text-orange-600');
                }
            }

            window.addEventListener('beforeunload', (e) => {
                if (hasUnsavedChanges) { e.preventDefault(); e.returnValue = ''; }
            });

            try {
                // Load existing data
                <?php
                $safe_json = '{}';
                if (!empty($page['editor_json']) && $page['editor_json'] !== 'undefined' && $page['editor_json'] !== 'null') {
                    $safe_json = $page['editor_json'];
                }
                ?>
                const initialData = <?= $safe_json ?>;

                if (typeof EditorJS === 'undefined') { throw new Error('EditorJS is not loaded'); }

                window.editor = new EditorJS({
                    holder: 'editorjs',
                    placeholder: 'เริ่มพิมพ์เนื้อหาที่นี่...',
                    tools: {
                        paragraph: { inlineToolbar: true, tunes: ['alignment'] },
                        header: {
                            class: Header, inlineToolbar: true,
                            config: { placeholder: 'หัวข้อ', levels: [2, 3, 4], defaultLevel: 2 },
                            tunes: ['alignment']
                        },
                        list: { class: EditorjsList, inlineToolbar: true, tunes: ['alignment'] },
                        image: {
                            class: ImageTool,
                            config: { endpoints: { byFile: 'upload_image.php', byUrl: 'upload_image.php?url=' } }
                        },
                        quote: Quote,
                        table: { class: Table, inlineToolbar: true },
                        delimiter: Delimiter,
                        linkTool: { class: LinkTool, config: { endpoint: 'fetch_url.php' } },
                        hyperlink: {
                            class: Hyperlink,
                            config: { shortcut: 'CMD+K', target: '_blank', rel: 'nofollow' }
                        },
                        columns: {
                            class: editorjsColumns,
                            config: {
                                tools: {
                                    header: Header,
                                    paragraph: { class: EditorJS.Paragraph, inlineToolbar: true },
                                    list: EditorjsList,
                                    image: ImageTool,
                                }
                            }
                        },
                        alignment: {
                            class: AlignmentBlockTune,
                            config: { default: "left" },
                        },
                        attaches: { class: AttachesTool, config: { endpoint: 'upload_file.php' } },
                        raw: RawTool,
                        googleDrive: GoogleDriveEmbed
                    },
                    data: initialData,
                    onReady: () => { document.getElementById('editorjs').style.minHeight = '300px'; },
                    onChange: () => { hasUnsavedChanges = true; updateSaveStatus(); }
                });

                window.savePage = function() {
                    window.editor.save().then((outputData) => {
                        document.getElementById('editor_json').value = JSON.stringify(outputData);
                        hasUnsavedChanges = false;
                        document.getElementById('pageForm').submit();
                    }).catch((error) => {
                        Swal.fire('Error', 'เกิดข้อผิดพลาด: ' + error.message, 'error');
                    });
                };

                window.previewPage = function() {
                    window.editor.save().then((data) => {
                        const modal = document.getElementById('previewModal');
                        document.getElementById('prevTitle').textContent = document.getElementById('pageTitle').value || 'Untitled';
                        document.getElementById('prevHeader').textContent = document.getElementById('pageTitle').value || 'Untitled';
                        
                        const thumbData = document.getElementById('thumbnailPreview').src;
                        const prevThumb = document.getElementById('prevThumb');
                        if(thumbData && !thumbData.includes('hidden')) {
                            prevThumb.src = thumbData;
                            prevThumb.classList.remove('hidden');
                        } else {
                            prevThumb.classList.add('hidden');
                        }

                        document.getElementById('previewContent').innerHTML = '<p class="text-center text-gray-400 italic py-10">โหมดดูตัวอย่าง (จำลองรูปแบบเนื้อหา)</p>';
                        modal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    });
                };
                
                window.closePreview = function() {
                    document.getElementById('previewModal').classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }

            } catch (e) {
                console.error("Editor init error:", e);
                document.getElementById('editorjs').innerHTML = '<div class="text-red-500">Editor failed to load: ' + e.message + '</div>';
            }
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>