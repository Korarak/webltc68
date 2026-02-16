<?php
include 'middleware.php';
ob_start();
include('db_news.php');

$upload_success_msg = "";
$upload_error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    // Fix: Handle content images (Base64 -> File) if needed (Summernote does Base64 by default which is heavy for DB)
    // For "Powerful" system, strictly, we should extract Base64 images and save them. 
    // BUT for simplicity and speed of "Convenience", modern DBs handle medium text size fine, or we can leave Base64.
    // Recommendation: Keep Base64 for now unless requested, or user complains about performance. 
    // Actually, let's stick to standard Summernote behavior (Base64).
    
    $uploader = htmlspecialchars($decoded->username ?? 'Unknown'); 

    if (empty($title)) {
        $upload_error_msg = "กรุณากรอกหัวข้อข่าว";
    } else {
        $stmt = $conn->prepare("INSERT INTO news (title, content, uploader) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $content, $uploader);

        if ($stmt->execute()) {
            $news_id = $stmt->insert_id;
            
            if (!empty($_FILES['attachments']['name'][0])) {
                // Structured Upload: uploads/news/YYYY/MM/
                $sub_path = "news/" . date('Y') . "/" . date('m') . "/";
                $target_dir = "../uploads/" . $sub_path; 
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                foreach ($_FILES['attachments']['name'] as $key => $original_name) {
                    if ($_FILES['attachments']['error'][$key] === 0) {
                        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '.' . $ext;
                        $target_file = $target_dir . $new_filename;
                        
                        // DB Path: uploads/news/YYYY/MM/filename
                        // File Move: ../uploads/news/YYYY/MM/filename
                        
                        if (move_uploaded_file($_FILES['attachments']['tmp_name'][$key], $target_file)) {
                            $f_type = mime_content_type($target_file);
                            $f_size = round($_FILES['attachments']['size'][$key] / 1024);
                            
                            $db_path = "uploads/" . $sub_path . $new_filename;
                            
                            $stmt_a = $conn->prepare("INSERT INTO attachments (news_id, file_name, file_path, file_type, file_size, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                            $sort_order = $key + 1;
                            $stmt_a->bind_param("isssii", $news_id, $original_name, $db_path, $f_type, $f_size, $sort_order);
                            $stmt_a->execute();
                        }
                    }
                }
            }
            $upload_success_msg = "บันทึกข่าวเรียบร้อยแล้ว";
        } else {
            $upload_error_msg = "Database Error: " . $conn->error;
        }
    }
}
?>

<div class="max-w-5xl mx-auto px-4 py-8">
    
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><i class="fas fa-edit text-blue-600 mr-2"></i> เขียนข่าวใหม่</h2>
        <a href="news_manage.php" class="text-gray-500 hover:text-gray-700 font-medium"><i class="fas fa-times"></i> ยกเลิก</a>
    </div>

    <!-- Alerts -->
    <?php if($upload_success_msg): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                Swal.fire({ icon: 'success', title: 'สำเร็จ', text: '<?= $upload_success_msg ?>', timer: 1500, showConfirmButton: false }).then(() => window.location = 'news_manage.php');
            });
        </script>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="space-y-6">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">หัวข้อข่าว <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="w-full text-lg border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all" placeholder="ระบุชื่อเรื่อง...">
            </div>

            <div class="mb-4">
                 <label class="block text-gray-700 font-semibold mb-2">เนื้อหาข่าว</label>
                 <textarea id="summernote" name="content"></textarea>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
             <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-paperclip text-gray-400 mr-2"></i> ไฟล์แนบ</h3>
             
             <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center bg-gray-50 hover:bg-white transition-colors relative cursor-pointer group">
                 <input type="file" id="fileInput" name="attachments[]" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewFiles()">
                 <div class="pointer-events-none">
                     <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 group-hover:text-blue-500 transition-colors mb-3"></i>
                     <p class="text-gray-600 font-medium">คลิกหรือลากไฟล์มาวางที่นี่</p>
                     <p class="text-xs text-gray-400 mt-1">รองรับ PDF, Word, Excel, Images (Max 10MB)</p>
                 </div>
             </div>
             
             <!-- Preview Area -->
             <div id="previewContainer" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6"></div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
             <a href="news_manage.php" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 font-semibold hover:bg-gray-50 transition-colors">ยกเลิก</a>
             <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold shadow-lg shadow-blue-200 hover:shadow-xl hover:scale-[1.02] transition-all">
                 <i class="fas fa-save mr-2"></i> เผยแพร่ข่าว
             </button>
        </div>

    </form>
</div>

<!-- Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Summernote -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    $('#summernote').summernote({
        placeholder: 'เขียนเนื้อหาข่าวของคุณที่นี่...',
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
        fontNames: ['Sarabun', 'Arial', 'Arial Black', 'Comic Sans MS', 'Courier New'],
        callbacks: {
            // Default behavior handles Base64 insert
        }
    });

    function previewFiles() {
        const preview = document.getElementById('previewContainer');
        const fileInput = document.getElementById('fileInput');
        const files = fileInput.files;

        preview.innerHTML = '';

        if (files) {
            [].forEach.call(files, readAndPreview);
        }

        function readAndPreview(file) {
            // Check file size (Max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                alert(file.name + " มีขนาดใหญ่เกิน 10MB");
                return;
            }

            const reader = new FileReader();
            
            // Container for each file
            const div = document.createElement('div');
            div.className = 'relative group bg-gray-50 rounded-lg p-2 border border-gray-200 shadow-sm hover:shadow-md transition-all';

            reader.addEventListener("load", function() {
                let content = '';
                
                // If extension is image
                if (/\.(jpe?g|png|gif)$/i.test(file.name)) {
                    content = `<img src="${this.result}" class="w-full h-32 object-contain rounded-md mb-2 bg-white border border-gray-100">`;
                } else if (/\.pdf$/i.test(file.name)) {
                     content = `<div class="w-full h-32 flex items-center justify-center bg-red-50 rounded-md text-red-500 mb-2"><i class="fas fa-file-pdf text-4xl"></i></div>`;
                } else if (/\.(doc|docx)$/i.test(file.name)) {
                     content = `<div class="w-full h-32 flex items-center justify-center bg-blue-50 rounded-md text-blue-500 mb-2"><i class="fas fa-file-word text-4xl"></i></div>`;
                } else if (/\.(xls|xlsx)$/i.test(file.name)) {
                     content = `<div class="w-full h-32 flex items-center justify-center bg-green-50 rounded-md text-green-500 mb-2"><i class="fas fa-file-excel text-4xl"></i></div>`;
                } else {
                     content = `<div class="w-full h-32 flex items-center justify-center bg-gray-100 rounded-md text-gray-500 mb-2"><i class="fas fa-file text-4xl"></i></div>`;
                }

                div.innerHTML = `
                    ${content}
                    <div class="text-center">
                        <p class="text-xs text-gray-700 font-medium truncate w-full" title="${file.name}">${file.name}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">${(file.size/1024/1024).toFixed(2)} MB</p>
                    </div>
                `;
                
                preview.appendChild(div);
            });
            
            reader.readAsDataURL(file);
        }
    }
</script>

<style>
    /* Custom Summernote Look */
    .note-editor.note-frame {
        border-radius: 12px;
        border-color: #e5e7eb;
        box-shadow: none;
    }
    .note-toolbar {
        border-radius: 12px 12px 0 0;
        background-color: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .note-statusbar {
        border-radius: 0 0 12px 12px;
    }
    /* Font prompt integration if needed, but standard sans works */
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>