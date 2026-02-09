<?php
// carousel_manage.php
include 'middleware.php';
ob_start();
include 'db_letter.php';    
include 'includes/upload_helper.php';

// Handle Add/Edit POST requests (Standard Form Submit)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    $carousel_no = $_POST['carousel_no']; // Will be auto-calculated normally, but if manual
    $carousel_text1 = $_POST['carousel_text1'];
    $carousel_text2 = $_POST['carousel_text2'];

    // Use physical path ../uploads/carousel/
    $physical_path = uploadBase64Image($_POST['carousel_pic_base64'], "../uploads/carousel/", "carousel_");

    if ($physical_path) {
        // DB stores uploads/carousel/ (remove ../)
        $db_path = str_replace("../", "", $physical_path);
        
        // Auto assign visible=1, slideshow=0
        $sql = "INSERT INTO carousel (carousel_no, carousel_pic, carousel_text1, carousel_text2, visible, slide_show) 
                VALUES ('$carousel_no', '$db_path', '$carousel_text1', '$carousel_text2', 1, 0)";
        if($conn->query($sql)){
             $success_msg = "เพิ่มป้ายประชาสัมพันธ์เรียบร้อยแล้ว";
        } else {
             $error_msg = "Error: " . $conn->error;
        }
    } else {
        $error_msg = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
    }
}

// Fetch Data
$sql = "SELECT * FROM carousel ORDER BY visible DESC, carousel_no ASC";
$result = $conn->query($sql);

$total_slides = 0;
$active_slides = 0;
$popup_slides = 0;
$slides = [];
while($row = $result->fetch_assoc()) {
    $slides[] = $row;
    $total_slides++;
    if($row['visible']) $active_slides++;
    if($row['slide_show']) $popup_slides++;
}

// Auto-calculate next order number
$next_order = $total_slides + 1;
?>

<div class="p-6 max-w-7xl mx-auto" x-data="{ showUploadModal: false }">
    
    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">ป้ายทั้งหมด</p>
                    <h3 class="text-4xl font-bold mt-2"><?= $total_slides ?></h3>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="fas fa-images text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-blue-100 text-xs">ภาพสไลด์ทั้งหมดในระบบ</div>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-6 text-white shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider">กำลังแสดงผล</p>
                    <h3 class="text-4xl font-bold mt-2"><?= $active_slides ?></h3>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="fas fa-eye text-2xl"></i>
                </div>
            </div>
             <div class="mt-4 text-emerald-100 text-xs">ป้ายที่เปิดใช้งานอยู่</div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg transform hover:scale-105 transition-transform duration-300">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium uppercase tracking-wider">Pop-up</p>
                    <h3 class="text-4xl font-bold mt-2"><?= $popup_slides ?></h3>
                </div>
                <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                    <i class="fas fa-window-restore text-2xl"></i>
                </div>
            </div>
             <div class="mt-4 text-purple-100 text-xs text-red-100 flex items-center gap-1">
                 <?php if($popup_slides > 1): ?>
                    <i class="fas fa-exclamation-triangle"></i> ควรเปิดเพียง 1 รายการ
                 <?php else: ?>
                    <i class="fas fa-check-circle"></i> สถานะปกติ
                 <?php endif; ?>
             </div>
        </div>
    </div>

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="bg-blue-100 p-2 rounded-lg text-blue-600"><i class="fas fa-layer-group"></i></span>
                จัดการป้ายประชาสัมพันธ์
            </h1>
            <p class="text-gray-500 text-sm mt-1">ลากวางเพื่อเปลี่ยนลำดับ • กด Toggle เพื่อเปิด/ปิดการแสดงผล</p>
        </div>
        <button @click="showUploadModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl shadow-lg hover:shadow-blue-200 transition-all duration-300 flex items-center gap-2 font-medium">
            <i class="fas fa-plus"></i> เพิ่มป้ายใหม่
        </button>
    </div>

    <!-- Sortable List -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-gray-100 text-gray-600 text-xs uppercase tracking-wider">
                        <th class="p-4 w-12 text-center">#</th>
                        <th class="p-4 w-24">ภาพตัวอย่าง</th>
                        <th class="p-4">รายละเอียด</th>
                        <th class="p-4 w-32 text-center">สถานะ</th>
                        <th class="p-4 w-32 text-center">Pop-up</th>
                        <th class="p-4 w-32 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="sortable-list" class="divide-y divide-gray-100">
                    <?php foreach($slides as $slide): 
                        // Path Logic: Prepend ../ if simple path from DB
                        $view_path = $slide['carousel_pic'];
                        if (strpos($view_path, 'http') !== 0 && strpos($view_path, '/') !== 0) {
                            $view_path = "../" . $view_path;
                        }
                    ?>
                    <tr class="group hover:bg-blue-50/20 transition-colors" data-id="<?= $slide['carousel_id'] ?>">
                        <td class="p-4 text-center cursor-move text-gray-400 hover:text-blue-500 handle">
                            <i class="fas fa-grip-vertical"></i>
                        </td>
                        <td class="p-4">
                             <div class="w-24 h-14 rounded-lg overflow-hidden shadow-sm border border-gray-200 relative group-hover:shadow-md transition-shadow">
                                <img src="<?= htmlspecialchars($view_path) ?>" class="w-full h-full object-cover">
                             </div>
                        </td>
                        <td class="p-4">
                            <div class="font-medium text-gray-800"><?= $slide['carousel_text1'] ?: '<span class="text-gray-400 italic">ไม่มีข้อความบน</span>' ?></div>
                            <div class="text-sm text-gray-500"><?= $slide['carousel_text2'] ?: '<span class="text-gray-400 italic">ไม่มีข้อความล่าง</span>' ?></div>
                        </td>
                        <td class="p-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer toggle-visible" 
                                       data-id="<?= $slide['carousel_id'] ?>" 
                                       <?= $slide['visible'] ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </td>
                        <td class="p-4 text-center">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer toggle-popup" 
                                       data-id="<?= $slide['carousel_id'] ?>" 
                                       <?= $slide['slide_show'] ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                <a href="carousel_edit.php?carousel_id=<?= $slide['carousel_id'] ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 transition-colors shadow-sm" title="แก้ไข">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button onclick="deleteSlide(<?= $slide['carousel_id'] ?>)" class="p-2 bg-white border border-gray-200 rounded-lg text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors shadow-sm" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if(empty($slides)): ?>
                <div class="p-12 text-center text-gray-400">
                    <i class="fas fa-images text-4xl mb-3 opacity-50"></i>
                    <p>ยังไม่มีป้ายประชาสัมพันธ์</p>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <!-- Upload Modal -->
    <div x-show="showUploadModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="showUploadModal = false"></div>
        
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden animate-fade-in-up">
            <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">เพิ่มป้ายประชาสัมพันธ์ใหม่</h3>
                <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="post" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับ</label>
                        <input type="number" name="carousel_no" value="<?= $next_order ?>" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รูปภาพ</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer relative" id="drop-zone">
                        <input type="file" id="carouselInput" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer text-sm" required>
                        <div class="space-y-2">
                             <input type="hidden" name="carousel_pic_base64" id="carouselBase64">
                             <div id="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                <p class="text-sm text-gray-500 font-medium">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่</p>
                                <p class="text-xs text-gray-400">แนะนำขนาด 1920x1080px (16:9)</p>
                             </div>
                             <div id="preview-container" class="hidden">
                                 <img id="preview-thumb" class="max-h-32 mx-auto rounded-lg shadow-sm">
                                 <p class="text-xs text-green-600 mt-2"><i class="fas fa-check-circle"></i> เลือกไฟล์เรียบร้อยเลี้ยว</p>
                             </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ข้อความบรรทัดบน</label>
                    <input type="text" name="carousel_text1" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all" placeholder="เช่น ยินดีต้อนรับสู่...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ข้อความบรรทัดล่าง</label>
                     <input type="text" name="carousel_text2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all" placeholder="เช่น วิทยาลัยเทคนิคเลย...">
                </div>

                <div class="pt-2">
                    <button type="submit" name="add" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-blue-200 transition-all active:scale-95">
                        <i class="fas fa-save mr-2"></i> บันทึกข้อมูล
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Crop Modal (Keep logic but style update) -->
    <div id="carouselModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl w-full max-w-4xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
               <h3 class="font-bold text-gray-800"><i class="fas fa-crop-alt mr-2"></i> ปรับแต่งรูปภาพ</h3>
               <div class="flex items-center gap-2">
                 <select id="aspectRatioSelect" class="bg-white border border-gray-300 text-sm rounded-lg px-3 py-1.5 outline-none focus:border-blue-500">
                    <option value="16/9">16:9 (Standard)</option>
                    <option value="4/3">4:3</option>
                    <option value="1">1:1 (Square)</option>
                    <option value="NaN">Free</option>
                 </select>
               </div>
            </div>
            
            <div class="flex-1 bg-gray-900 overflow-hidden relative flex items-center justify-center p-4">
                 <img id="carouselPreview" class="max-w-full max-h-full">
            </div>

            <div class="p-4 bg-white border-t border-gray-100 flex justify-end gap-3">
                <button type="button" id="carouselModal_cancel" class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    ยกเลิก
                </button>
                <button type="button" id="carouselModal_confirm" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-medium shadow-lg shadow-blue-200 transition-all">
                    <i class="fas fa-check mr-2"></i> ยืนยันการตัดภาพ
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Helper Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<script>
    // --- SortableJS Logic ---
    const list = document.getElementById('sortable-list');
    new Sortable(list, {
        animation: 150,
        handle: '.handle',
        ghostClass: 'bg-blue-50',
        onEnd: function() {
            let order = [];
            document.querySelectorAll('#sortable-list tr').forEach((row) => {
                order.push(row.getAttribute('data-id'));
            });
            
            // Send new order to server
            fetch('carousel_update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'reorder', order: order })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const Toast = Swal.mixin({
                      toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true
                    })
                    Toast.fire({ icon: 'success', title: 'จัดเรียงลำดับใหม่แล้ว' })
                }
            });
        }
    });

    // --- Toggle Logic ---
    document.querySelectorAll('.toggle-visible').forEach(item => {
        item.addEventListener('change', e => {
            const id = e.target.getAttribute('data-id');
            const status = e.target.checked ? 0 : 1; // Current status before change (toggle logic handles flipping)
            // Correction: Checkbox checked means it IS visible. If I uncheck, checked becomes false.
            // Let's pass current UI state? No, verify with backend.
            // Simplified: pass the intended action or just ID.
            
            // Backend logic: $current_status -> flip it.
            // If checking (true), status was false (0). So current is 0.
             const currentStatusToSend = e.target.checked ? 0 : 1;

            updateStatus('toggle_visible', id, currentStatusToSend);
        });
    });

    document.querySelectorAll('.toggle-popup').forEach(item => {
        item.addEventListener('change', e => {
            const id = e.target.getAttribute('data-id');
            const currentStatusToSend = e.target.checked ? 0 : 1;
            updateStatus('toggle_popup', id, currentStatusToSend, e.target);
        });
    });

    function updateStatus(action, id, currentStatus, element = null) {
        fetch('carousel_update.php', {
            method: 'POST',
             headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action, id: id, status: currentStatus })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                  const Toast = Swal.mixin({
                      toast: true, position: 'top-end', showConfirmButton: false, timer: 1500
                    })
                    Toast.fire({ icon: 'success', title: 'บันทึกสถานะเรียบร้อย' })
            } else {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message });
                // Revert toggle if failed
                if(element) element.checked = !element.checked; 
            }
        })
        .catch(err => {
             if(element) element.checked = !element.checked;
        });
    }

    // --- Delete Logic ---
    window.deleteSlide = function(id) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "คุณต้องการลบป้ายประชาสัมพันธ์นี้ใช่หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'ลบข้อมูล',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('carousel_update.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id: id })
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Deleted!', 'ลบข้อมูลเรียบร้อยแล้ว.', 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                });
            }
        })
    }

    // --- Cropper Logic (Ported & Improved) ---
    // Similar to old logic but integrated with new modal IDs
    let cropper = null;
    let currentRatio = 16/9;
    
    const uploadInput = document.getElementById('carouselInput');
    const cropModal = document.getElementById('carouselModal');
    const imagePreview = document.getElementById('carouselPreview');
    const hiddenInput = document.getElementById('carouselBase64');
    const aspectSelect = document.getElementById('aspectRatioSelect');
    const previewContainer = document.getElementById('preview-container');
    const uploadPlaceholder = document.getElementById('upload-placeholder');
    const previewThumb = document.getElementById('preview-thumb');

    uploadInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        if (!file.type.match('image.*')) {
            Swal.fire('Format Error', 'กรุณาเลือกไฟล์รูปภาพเท่านั้น', 'error');
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
             imagePreview.src = reader.result;
             cropModal.classList.remove('hidden');
             
             // Wait for image load
             imagePreview.onload = () => {
                if (cropper) cropper.destroy();
                const ratio = aspectSelect.value === 'NaN' ? NaN : parseFloat(aspectSelect.value);
                
                cropper = new Cropper(imagePreview, {
                  aspectRatio: ratio,
                  viewMode: 1,
                  autoCropArea: 1,
                  responsive: true,
                  ready() {
                      // ready
                  }
                });
             }
        };
        reader.readAsDataURL(file);
    });
    
    aspectSelect.addEventListener('change', () => {
        if (!cropper) return;
        const ratio = aspectSelect.value === 'NaN' ? NaN : parseFloat(aspectSelect.value);
        cropper.setAspectRatio(ratio);
    });

    document.getElementById('carouselModal_cancel').addEventListener('click', () => {
        if (cropper) cropper.destroy();
        cropModal.classList.add('hidden');
        uploadInput.value = ''; // Reset
    });

    document.getElementById('carouselModal_confirm').addEventListener('click', () => {
        if (!cropper) return;
        
        const canvas = cropper.getCroppedCanvas({
          width: 1280,
          imageSmoothingEnabled: true,
          imageSmoothingQuality: 'high'
        });
        
        hiddenInput.value = canvas.toDataURL('image/jpeg', 0.9);
        
        // Show preview in form
        previewThumb.src = hiddenInput.value;
        previewContainer.classList.remove('hidden');
        uploadPlaceholder.classList.add('hidden');

        cropper.destroy();
        cropModal.classList.add('hidden');
    });

</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
<style>
    [x-cloak] { display: none !important; }
    .animate-fade-in-up { animation: fadeInUp 0.4s ease-out; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
</style>