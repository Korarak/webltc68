<?php
include 'middleware.php';
ob_start();
require 'db_news.php';

$action = $_GET['action'] ?? 'list';

// เพิ่มข้อมูล
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $sort_order = (int)$_POST['sort_order'];
    $visible = isset($_POST['visible']) ? 1 : 0;

    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name, sort_order, visible) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $name, $sort_order, $visible);
        $stmt->execute();
        header("Location: news_type_manage.php?msg=เพิ่มประเภทข่าวสำเร็จ");
        exit;
    }
}

// แก้ไขข้อมูล
if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $sort_order = (int)$_POST['sort_order'];
        $visible = isset($_POST['visible']) ? 1 : 0;

        if ($name !== '') {
            $stmt = $conn->prepare("UPDATE categories SET name=?, sort_order=?, visible=? WHERE id=?");
            $stmt->bind_param("siii", $name, $sort_order, $visible, $id);
            $stmt->execute();
            header("Location: news_type_manage.php?msg=แก้ไขประเภทข่าวสำเร็จ");
            exit;
        }
    } else {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $edit_category = $stmt->get_result()->fetch_assoc();
    }
}

// ลบข้อมูล
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: news_type_manage.php?msg=ลบประเภทข่าวสำเร็จ");
    exit;
}

// ดึงรายการ
$records_per_page = 10;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $records_per_page;

$stmt = $conn->prepare("SELECT * FROM categories ORDER BY sort_order ASC, id ASC LIMIT ?, ?");
$stmt->bind_param("ii", $start, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

$total = $conn->query("SELECT COUNT(*) AS total FROM categories")->fetch_assoc()['total'];
$total_pages = ceil($total / $records_per_page);
?>

<div class="min-h-screen bg-gray-50/50 pb-12">
    <div class="max-w-6xl mx-auto mt-8 px-4">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                    <span class="bg-blue-600 text-white p-2 rounded-lg shadow-md shadow-blue-200">
                        <i class="fas fa-tags"></i>
                    </span>
                    จัดการประเภทข่าว
                </h2>
                <p class="text-gray-500 mt-1 ml-12">เพิ่ม ลบ หรือแก้ไขหมวดหมู่สำหรับจัดเรียงข่าวสาร</p>
            </div>
            
            <div class="flex gap-3">
                 <a href="news_manage.php" class="bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 px-5 py-2.5 rounded-xl shadow-sm hover:shadow transition-all flex items-center gap-2 transform hover:-translate-y-0.5">
                    <i class="fas fa-arrow-left"></i> <span class="font-medium">กลับหน้าข่าวสาร</span>
                 </a>
            </div>
        </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="mb-4 p-2 bg-green-100 text-green-800 rounded">
            <?= htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if ($action === 'add' || ($action === 'edit' && isset($edit_category))): ?>
        <?php
        $form_data = [
            'name' => $edit_category['name'] ?? '',
            'sort_order' => $edit_category['sort_order'] ?? 0,
            'visible' => $edit_category['visible'] ?? 1
        ];
        ?>

        <!-- ฟอร์ม เพิ่ม / แก้ไข -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">
                <i class="fas <?= $action === 'edit' ? 'fa-edit text-yellow-500' : 'fa-plus-circle text-green-500' ?> mr-2"></i>
                <?= $action === 'edit' ? 'แก้ไขประเภทข่าว' : 'เพิ่มประเภทข่าวใหม่' ?>
            </h3>
            <form action="news_type_manage.php?action=<?= $action ?><?= isset($edit_category) ? '&id=' . $edit_category['id'] : '' ?>" method="post"
                  class="space-y-6">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ชื่อประเภทข่าว <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="<?= htmlspecialchars($form_data['name']); ?>" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all" placeholder="เช่น กิจกรรมนักศึกษา...">
                    </div>

                    <div>
                        <label class="block mb-2 font-semibold text-gray-700">ลำดับการแสดงผล</label>
                        <input type="number" name="sort_order" value="<?= htmlspecialchars($form_data['sort_order']); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all">
                        <p class="text-xs text-gray-400 mt-1">ตัวเลขน้อยจะแสดงก่อนในรายการ</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-xl border border-gray-100 w-fit">
                    <input type="checkbox" name="visible" value="1" id="visible_chk" <?= $form_data['visible'] ? 'checked' : '' ?> class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                    <label for="visible_chk" class="font-semibold text-gray-700 cursor-pointer">แสดงผลหมวดหมู่นี้ให้ผู้ใช้เห็น</label>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <a href="news_type_manage.php" class="px-6 py-2.5 bg-white border border-gray-300 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">ยกเลิก</a>
                    <button type="submit" class="px-8 py-2.5 bg-gradient-to-r <?= $action === 'edit' ? 'from-yellow-500 to-orange-500 shadow-orange-200' : 'from-green-500 to-emerald-600 shadow-green-200' ?> text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> <?= $action === 'edit' ? 'บันทึกการแก้ไข' : 'บันทึกข้อมูล' ?>
                    </button>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- รายการทั้งหมด -->
        <div class="mb-4">
            <a href="news_type_manage.php?action=add"
               class="inline-flex items-center gap-2 px-6 py-2.5 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-medium rounded-xl shadow-lg shadow-green-200 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fas fa-plus"></i> เพิ่มหมวดหมู่ใหม่
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full whitespace-nowrap text-left text-sm">
                    <thead class="bg-gray-50/50 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider">
                        <tr>
                            <th class="py-4 px-6 w-20 text-center">ID</th>
                            <th class="py-4 px-6">ชื่อประเภทข่าว</th>
                            <th class="py-4 px-6 w-32 text-center">ลำดับ</th>
                            <th class="py-4 px-6 w-32 text-center">การแสดงผล</th>
                            <th class="py-4 px-6 w-40 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="py-4 px-6 text-center text-gray-500 font-medium">#<?= $row['id']; ?></td>
                                    <td class="py-4 px-6 font-bold text-gray-800"><?= htmlspecialchars($row['name']); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg font-medium border border-gray-200"><?= $row['sort_order']; ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <?php if($row['visible']): ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-600 border border-green-200">
                                                <i class="fas fa-circle text-[8px]"></i> แสดงผล
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200">
                                                <i class="fas fa-eye-slash text-[10px]"></i> ซ่อนไว้
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2 opacity-60 group-hover:opacity-100 transition-all">
                                            <a href="news_type_manage.php?action=edit&id=<?= $row['id']; ?>" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-yellow-600 hover:bg-yellow-50 transition-colors bg-white shadow-sm" title="แก้ไข">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <a href="news_type_manage.php?action=delete&id=<?= $row['id']; ?>" onclick="return confirm('ยืนยันระบบลบประเภทข่าวนี้หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้');" class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors bg-white shadow-sm" title="ลบ">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400">
                                    <i class="fas fa-inbox text-4xl mb-3 block opacity-20"></i>
                                    ไม่พบข้อมูลประเภทข่าว เริ่มต้นสร้างหมวดหมู่แรกของคุณ
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex gap-2" aria-label="Pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="news_type_manage.php?page=<?= $i; ?>"
                       class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium transition-colors <?= ($i == $page) ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
