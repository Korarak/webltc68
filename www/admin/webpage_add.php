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
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-6xl mx-auto py-8 px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">สร้างเว็บเพจใหม่</h1>
                    <p class="text-gray-600 mt-1">เขียนบทความแบบ Notion-style</p>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="webpages_manage.php" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        ย้อนกลับ
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" class="space-y-6" id="pageForm">
            <!-- Sidebar / Meta (Top Section) -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อเพจ *</label>
                        <input type="text" name="title" required placeholder="Page Title" 
                               class="w-full text-xl font-bold px-3 py-2 border-b-2 border-gray-200 focus:border-blue-500 focus:outline-none transition bg-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Slug (URL) *</label>
                        <input type="text" name="slug" required placeholder="url-slug" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50 focus:bg-white transition">
                    </div>
                </div>
                
                 <!-- Collapsible Meta -->
                <div class="mt-6 border-t pt-4">
                    <details class="group">
                        <summary class="flex justify-between items-center font-medium cursor-pointer list-none text-gray-600 hover:text-blue-600">
                            <span> ตั้งค่าเพิ่มเติม/SEO</span>
                            <span class="transition group-open:rotate-180">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </summary>
                        <div class="text-gray-500 mt-4 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <label class="block text-xs font-medium mb-1">คำอธิบาย (Description)</label>
                                <textarea name="meta_description" rows="2" class="w-full border rounded text-sm px-2 py-1"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1">คีย์เวิร์ด</label>
                                <input type="text" name="meta_keywords" class="w-full border rounded text-sm px-2 py-1">
                            </div>
                             <div>
                                <label class="block text-xs font-medium mb-1">รูปภาพปก</label>
                                <input type="file" name="thumbnail" accept="image/*" class="text-sm">
                            </div>
                            <div>
                                 <label class="flex items-center space-x-2 mt-4">
                                    <input type="checkbox" name="visible" value="1" checked class="w-4 h-4 text-blue-500 rounded">
                                    <span class="text-sm">เผยแพร่ทันที</span>
                                </label>
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            <!-- Editor Area -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 min-h-[500px] p-8 cursor-text" onclick="document.getElementById('editorjs').focus()">
                <div id="editorjs"></div>
            </div>
            
            <input type="hidden" name="editor_json" id="editor_json">

            <!-- Sticky Save Bar -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 flex justify-end shadow-lg z-50">
                <button type="button" onclick="savePage()" 
                        class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-md transition transform hover:-translate-y-1">
                    <i class="fas fa-save mr-2"></i> บันทึกเพจ
                </button>
            </div>
            <div class="h-16"></div> <!-- Spacer -->
        </form>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                if (typeof EditorJS === 'undefined') {
                    throw new Error('EditorJS is not loaded');
                }

                const editor = new EditorJS({
                    holder: 'editorjs',
                    placeholder: 'เริ่มพิมพ์เนื้อหาที่นี่...',
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
                            class: EditorjsList, // Changed from List to EditorjsList
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
                        table: Table,
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
                    onChange: (api, event) => {
                        console.log('Now I know that Editor\'s content changed!', event);
                    }
                });

                // Expose save function globally
                window.savePage = function() {
                    editor.save().then((outputData) => {
                        document.getElementById('editor_json').value = JSON.stringify(outputData);
                        document.getElementById('pageForm').submit();
                    }).catch((error) => {
                        console.error('Saving failed: ', error);
                        alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' + error.message);
                    });
                };
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