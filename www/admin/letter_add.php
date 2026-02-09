<?php
include 'middleware.php';
$title = "เพิ่มจดหมายข่าว";
ob_start();
?>

<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="letter_manage.php" class="bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-200 p-2.5 rounded-xl shadow-sm transition-all">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800">เพิ่มจดหมายข่าวใหม่</h1>
            <p class="text-gray-500 text-sm">กรอกข้อมูลเพื่อสร้างจดหมายข่าว</p>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8">
            <form action="letter_create.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                
                <!-- Title Input -->
                <div class="space-y-2">
                    <label for="title" class="text-sm font-semibold text-gray-700">หัวข้อจดหมายข่าว <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-heading text-gray-400"></i>
                        </div>
                        <input type="text" id="title" name="title" required maxlength="255"
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400 outline-none transition-all placeholder-gray-400"
                               placeholder="ระบุหัวข้อจดหมายข่าว...">
                    </div>
                </div>

                <!-- File Upload -->
                <div class="space-y-2">
                    <label class="text-sm font-semibold text-gray-700">แนบไฟล์เอกสาร/รูปภาพ <span class="text-red-500">*</span></label>
                    <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:bg-gray-50 transition-colors cursor-pointer relative group"
                         onclick="document.getElementById('file').click()">
                        <div class="space-y-1 text-center">
                            <div class="w-12 h-12 mx-auto bg-blue-50 text-blue-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-cloud-upload-alt text-xl"></i>
                            </div>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="file" class="relative cursor-pointer bg-transparent rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                    <span>อัพโหลดไฟล์</span>
                                    <input id="file" name="file" type="file" class="sr-only" required onchange="showFileName(this)">
                                </label>
                                <p class="pl-1">หรือลากไฟล์มาวาง</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF, JPG, PNG, GIF up to 10MB</p>
                            <p id="file-name" class="text-sm font-medium text-blue-600 mt-2 hidden"></p>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="user" value="<?= htmlspecialchars($decoded->username)?>">

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-2.5 rounded-xl shadow-lg shadow-blue-200 hover:shadow-xl transition-all font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> บันทึกข้อมูล
                    </button>
                    <a href="letter_manage.php" class="px-6 py-2.5 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors font-medium">
                        ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showFileName(input) {
    const name = input.files[0] ? input.files[0].name : '';
    const display = document.getElementById('file-name');
    if(name) {
        display.textContent = 'ไฟล์ที่เลือก: ' + name;
        display.classList.remove('hidden');
    } else {
        display.classList.add('hidden');
    }
}
</script>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
