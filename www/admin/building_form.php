<?php
include 'middleware.php';
require_once '../config.php';

$id = $_GET['id'] ?? '';
$data = ['name'=>'', 'description'=>'', 'capacity'=>'', 'equipment'=>'', 'responsible'=>'', 'color'=>'#cccccc'];

if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM buildings WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $data = $row;
    }
}
?>
<?php ob_start(); ?>

<div class="animate-fade-in-up max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="building_manage.php" class="w-10 h-10 rounded-full bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-blue-600 hover:border-blue-500 transition-all shadow-sm">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $id ? 'แก้ไขข้อมูลอาคาร' : 'เพิ่มอาคารใหม่'; ?></h1>
            <p class="text-gray-500 text-sm">กรอกข้อมูลรายละเอียดของอาคาร</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="building_save.php" method="post">
            <input type="hidden" name="id" value="<?= $id; ?>">
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่ออาคาร / สถานที่ <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" value="<?= htmlspecialchars($data['name']); ?>" required placeholder="ระบุชื่ออาคาร">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ผู้ดูแลรับผิดชอบ</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400"><i class="fas fa-user-tie"></i></span>
                        <input type="text" name="responsible" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" value="<?= htmlspecialchars($data['responsible']); ?>" placeholder="เช่น ฝ่ายอาคารสถานที่">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ความจุ (คน)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400"><i class="fas fa-users"></i></span>
                        <input type="text" name="capacity" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" value="<?= htmlspecialchars($data['capacity']); ?>" placeholder="เช่น 100 คน">
                    </div>
                </div>
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">อุปกรณ์ภายใน</label>
                <div class="relative">
                    <span class="absolute left-3 top-2.5 text-gray-400"><i class="fas fa-tools"></i></span>
                    <input type="text" name="equipment" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" value="<?= htmlspecialchars($data['equipment']); ?>" placeholder="เช่น โปรเจคเตอร์, ไมโครโฟน">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด / คำอธิบายเพิ่มเติม</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" placeholder="รายละเอียดอื่นๆ..."><?= htmlspecialchars($data['description']); ?></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="building_manage.php" class="px-5 py-2 rounded-lg text-gray-600 bg-gray-100 hover:bg-gray-200 font-medium transition-colors">ยกเลิก</a>
                <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-md shadow-blue-200 transition-all">
                    <i class="fas fa-save mr-1"></i> บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-fade-in-up { animation: fadeInUp 0.5s ease-out; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
