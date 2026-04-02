<?php
include 'middleware.php';
file_put_contents('/tmp/debug_news_hit.log', date('Y-m-d H:i:s') . " - Page Accessed: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('db_news.php');

$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if(!$news_id) { header("Location: news_manage.php"); exit; }

// Fetch News
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ? AND is_deleted = 0");
$stmt->bind_param("i", $news_id);
$stmt->execute();
$news = $stmt->get_result()->fetch_assoc();
if(!$news) { header("Location: news_manage.php"); exit; }

// Fetch Attachments
$att_stmt = $conn->prepare("SELECT * FROM attachments WHERE news_id = ? ORDER BY sort_order ASC, id ASC");
$att_stmt->bind_param("i", $news_id);
$att_stmt->execute();
$attachments = $att_stmt->get_result();

$upload_success_msg = "";
$upload_error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // DEBUG: Log POST and FILES data
    file_put_contents('/tmp/debug_news.log', date('Y-m-d H:i:s') . " [EDIT] - POST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);
    // Check if delete attachment
    if(!empty($_POST['delete_at_id'])) {
        $del_id = intval($_POST['delete_at_id']);
        $del_path = $_POST['delete_at_path'];
        if(file_exists($del_path)) unlink($del_path);
        $conn->query("DELETE FROM attachments WHERE id=$del_id");
        // Refetch attachments
        header("Location: news_edit.php?id=$news_id"); // Reload clean
        exit;
    }

    // Update News
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    // $category = $_POST['category'] ?? 1; // Assuming default if no category sys yet in my logic (Code from manage has category)
    $category_id = isset($_POST['category']) ? intval($_POST['category']) : $news['category_id'];
    if ($category_id === 0) $category_id = null;

    $stmt = $conn->prepare("UPDATE news SET title=?, content=?, category_id=? WHERE id=?");
    $stmt->bind_param("ssii", $title, $content, $category_id, $news_id);
    
    if($stmt->execute()) {
        // Update Sort Order
        if(isset($_POST['attachment_order']) && !empty($_POST['attachment_order'])) {
            $order_ids = explode(',', $_POST['attachment_order']);
            foreach($order_ids as $index => $id) {
                $pos = $index + 1;
                $update_stmt = $conn->prepare("UPDATE attachments SET sort_order = ? WHERE id = ? AND news_id = ?");
                $update_stmt->bind_param("iii", $pos, $id, $news_id);
                $update_stmt->execute();
            }
        }

        // Upload New Attachments
        if (!empty($_FILES['attachments']['name'][0])) {
            // ... (rest of upload logic)
            // Structured: uploads/news/Y/m/
            $sub_path = "news/" . date('Y') . "/" . date('m') . "/";
            $target_dir = "../uploads/" . $sub_path; // Points to www/uploads
            
            if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

            foreach ($_FILES['attachments']['name'] as $key => $original_name) {
                if ($_FILES['attachments']['error'][$key] === 0) {
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $ext;
                    $target_file = $target_dir . $new_filename; 
                    
                    if(move_uploaded_file($_FILES['attachments']['tmp_name'][$key], $target_file)){
                         // บีบอัดเฉพาะไฟล์ภาพ
                         if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                             require_once __DIR__ . '/../includes/optimize_image.php';
                             optimizeImage($target_file, 1200, 80);
                         }

                         $f_type = mime_content_type($target_file);
                         $f_size = round($_FILES['attachments']['size'][$key] / 1024);
                         
                         $db_path = "uploads/" . $sub_path . $new_filename; // Standardize: uploads/...

                         
                         $conn->query("INSERT INTO attachments (news_id, file_name, file_path, file_type, file_size) VALUES ($news_id, '$original_name', '$db_path', '$f_type', $f_size)");
                    }
                }
            }
        }
        $upload_success_msg = "อัปเดตข้อมูลเรียบร้อยแล้ว";
        // Refresh data
        // $stmt->execute(); // Re-run update? No need.
        // Reload page to show checkmark
    } else {
        $upload_error_msg = "Database Error: " . $conn->error;
    }
}

// Fetch Categories for Select
$cats = $conn->query("SELECT * FROM categories ORDER BY sort_order ASC");
?>

<div class="max-w-5xl mx-auto px-4 py-8">
    
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><i class="fas fa-edit text-yellow-500 mr-2"></i> แก้ไขข่าว</h2>
        <a href="news_manage.php" class="text-gray-500 hover:text-gray-700 font-medium"><i class="fas fa-arrow-left"></i> กลับไปหน้าจัดการ</a>
    </div>

    <?php if($upload_success_msg): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $upload_success_msg ?>', timer: 1500, showConfirmButton: false }).then(() => window.location = 'news_manage.php');
            });
        </script>
    <?php endif; ?>

    <?php if(!empty($upload_error_msg)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: '<?= htmlspecialchars($upload_error_msg, ENT_QUOTES, "UTF-8") ?>' });
            });
        </script>
    <?php endif; ?>

    <form id="newsForm" method="post" enctype="multipart/form-data" class="space-y-6">
        <!-- ... form fields ... -->
        <script>
        function submitForm() {
            console.log('Manual submission triggered');
            // Ensure Summernote content is synced
            var content = $('#summernote').summernote('code');
            console.log('Summernote Content Length: ' + content.length);
            
            // Log to check if function is called
            // Create a test image to confirm write access? No, console is enough.
            
            document.getElementById('newsForm').submit();
        }
        
        document.getElementById('newsForm').addEventListener('submit', function(e) {
            console.log('Form submitting event fired...');
        });
        </script>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                     <label class="block text-gray-700 font-semibold mb-2">หัวข้อข่าว <span class="text-red-500">*</span></label>
                     <input type="text" name="title" value="<?= htmlspecialchars($news['title']) ?>" required class="w-full text-lg border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all">
                </div>
                <div>
                     <label class="block text-gray-700 font-semibold mb-2">หมวดหมู่</label>
                     <select name="category" class="w-full text-lg border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all bg-white">
                         <option value="0" <?= $news['category_id'] == 0 ? 'selected' : '' ?>>ทั่วไป</option>
                         <?php while($c = $cats->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>" <?= $c['id'] == $news['category_id'] ? 'selected' : '' ?>><?= $c['name'] ?></option>
                         <?php endwhile; ?>
                     </select>
                </div>
            </div>

            <div class="mb-4">
                 <label class="block text-gray-700 font-semibold mb-2">เนื้อหาข่าว</label>
                 <textarea id="summernote" name="content"><?= $news['content'] ?></textarea>
            </div>
        </div>

        <!-- Attachments Management -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
             <h3 class="font-semibold text-gray-800 mb-4 flex items-center justify-between">
                 <span><i class="fas fa-paperclip text-gray-400 mr-2"></i> ไฟล์แนบ</span>
                 <span class="text-xs text-gray-400 font-normal">ไฟล์เดิมที่มีอยู่</span>
             </h3>

             <div id="attachment-list" class="space-y-2 mb-6">
                 <?php while($att = $attachments->fetch_assoc()): 
                    // Path Logic: Preserve subdirectories, ensure relative to www/
                    $clean_path = str_replace(['../', './', 'admin/'], '', $att['file_path']);
                    $clean_path = ltrim($clean_path, '/');
                    if (strpos($clean_path, 'uploads/') !== 0) $clean_path = 'uploads/' . $clean_path;
                    $view_url = "../" . $clean_path;
                 ?>
                    <div class="flex items-center p-3 bg-white rounded-xl border border-gray-200 shadow-sm attachment-item hover:shadow-md transition-shadow group" data-id="<?= $att['id'] ?>">
                            <!-- Drag Handle -->
                            <div class="mr-3 cursor-move p-2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-grip-vertical text-lg"></i>
                            </div>

                            <!-- Preview / Icon -->
                            <div class="mr-4 flex-shrink-0">
                                <?php if(preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $clean_path)): ?>
                                    <div class="w-20 h-20 rounded-lg overflow-hidden border border-gray-100 bg-gray-50 relative group-hover:scale-105 transition-transform">
                                        <img src="<?= $view_url ?>" alt="preview" class="w-full h-full object-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="w-20 h-20 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-500 text-3xl">
                                        <?php if(preg_match('/\.pdf$/i', $clean_path)): ?>
                                            <i class="fas fa-file-pdf text-red-500"></i>
                                        <?php elseif(preg_match('/\.(doc|docx)$/i', $clean_path)): ?>
                                            <i class="fas fa-file-word text-blue-600"></i>
                                        <?php elseif(preg_match('/\.(xls|xlsx)$/i', $clean_path)): ?>
                                            <i class="fas fa-file-excel text-green-600"></i>
                                        <?php else: ?>
                                            <i class="fas fa-file-alt"></i>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Info -->
                            <div class="flex-grow min-w-0 mr-4">
                                <p class="text-sm font-semibold text-gray-800 truncate" title="<?= htmlspecialchars($att['file_name']) ?>"><?= htmlspecialchars($att['file_name']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= round($att['file_size']) ?> KB • <span class="uppercase"><?= pathinfo($clean_path, PATHINFO_EXTENSION) ?></span></p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <a href="<?= $view_url ?>" target="_blank" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="ดาวน์โหลด/ดู">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <button type="button" onclick="if(confirm('ยืนยันการลบไฟล์นี้?')) { this.form.delete_at_id.value='<?= $att['id'] ?>'; this.form.delete_at_path.value='<?= $att['file_path'] ?>'; this.form.submit(); }" class="w-8 h-8 flex items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="ลบ">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </div>
                 <?php endwhile; ?>
                 <input type="hidden" name="delete_at_id" value="">
                 <input type="hidden" name="delete_at_path" value="">
                 <input type="hidden" name="attachment_order" id="attachment_order" value="">
             </div>

             <div class="border-t border-gray-100 pt-4">
                 <label class="block text-gray-700 font-semibold mb-2">เพิ่มไฟล์แนบใหม่</label>
                 <input type="file" name="attachments[]" multiple class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
             </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
             <a href="news_manage.php" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 font-semibold hover:bg-gray-50 transition-colors">ยกเลิก</a>
             <button type="button" onclick="submitForm()" class="px-8 py-3 rounded-xl bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-bold shadow-lg shadow-orange-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                 <i class="fas fa-save mr-2"></i> บันทึกการแก้ไข
             </button>
        </div>

    </form>
</div>

<!-- Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    $('#summernote').summernote({
        placeholder: 'เนื้อหาข่าว...',
        tabsize: 2,
        height: 400,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear', 'forecolor', 'backcolor']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video', 'hr']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        fontNames: ['Sarabun', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New']
    });

    // Initialize Sortable
    if (document.getElementById('attachment-list')) {
        const sortable = new Sortable(document.getElementById('attachment-list'), {
            animation: 150,
            handle: '.fa-grip-vertical',
            onEnd: function() {
                updateOrder();
            }
        });

        function updateOrder() {
            const items = document.querySelectorAll('.attachment-item');
            const ids = Array.from(items).map(item => item.getAttribute('data-id'));
            document.getElementById('attachment_order').value = ids.join(',');
        }
        
        // Initial order
        updateOrder();
    }
</script>

<style>
    .note-editor.note-frame {
        border-radius: 12px;
        border-color: #e5e7eb;
    }
    .note-toolbar {
        border-radius: 12px 12px 0 0;
        background-color: #f9fafb;
    }
    .note-statusbar {
        border-radius: 0 0 12px 12px;
    }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>