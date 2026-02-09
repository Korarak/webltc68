<?php
include 'middleware.php';
require_once '../config.php';

// Fetch all buildings
$sql = "SELECT * FROM buildings ORDER BY id ASC";
$result = $mysqli->query($sql);
?>
<?php ob_start(); ?>

<div class="animate-fade-in-up">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">จัดการแผนผังอาคาร</h1>
            <p class="text-gray-500 text-sm">จัดการข้อมูลอาคารในแผนที่โรงเรียน</p>
        </div>
        <a href="building_form.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors flex items-center gap-2">
            <i class="fas fa-plus"></i> เพิ่มอาคารใหม่
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                    <tr>
                        <th class="p-4 border-b">ID</th>
                        <th class="p-4 border-b">ชื่ออาคาร</th>
                        <th class="p-4 border-b">ผู้ดูแล</th>
                        <th class="p-4 border-b">ความจุ</th>
                        <th class="p-4 border-b text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4 text-gray-500 font-mono text-sm"><?= $row['id']; ?></td>
                                <td class="p-4 font-medium text-gray-800"><?= htmlspecialchars($row['name']); ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($row['responsible']); ?></td>
                                <td class="p-4 text-gray-600"><?= htmlspecialchars($row['capacity']); ?></td>
                                <td class="p-4 text-right space-x-2">
                                    <a href="building_form.php?id=<?= $row['id']; ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="building_delete.php?id=<?= $row['id']; ?>" onclick="return confirm('ยืนยันการลบข้อมูลนี้?');" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="ลบ">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400">
                                <i class="fas fa-folder-open text-4xl mb-3"></i>
                                <p>ยังไม่มีข้อมูลอาคาร</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
