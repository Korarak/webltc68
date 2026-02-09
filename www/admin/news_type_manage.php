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

<div class="max-w-5xl mx-auto mt-10 px-4">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">จัดการประเภทข่าว</h2>

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
        <form action="news_type_manage.php?action=<?= $action ?><?= isset($edit_category) ? '&id=' . $edit_category['id'] : '' ?>" method="post"
              class="space-y-4 bg-white p-6 rounded shadow">
            <div>
                <label class="block mb-2 font-semibold">ชื่อประเภทข่าว</label>
                <input type="text" name="name" value="<?= htmlspecialchars($form_data['name']); ?>" required
                       class="w-full px-4 py-2 border border-gray-300 rounded focus:ring focus:border-blue-400">
            </div>

            <div>
                <label class="block mb-2 font-semibold">ลำดับการแสดงผล</label>
                <input type="number" name="sort_order" value="<?= htmlspecialchars($form_data['sort_order']); ?>"
                       class="w-full px-4 py-2 border border-gray-300 rounded focus:ring focus:border-blue-400">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="visible" value="1" <?= $form_data['visible'] ? 'checked' : '' ?>>
                <label class="font-semibold">แสดงผลหมวดนี้</label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    <?= $action === 'edit' ? 'อัพเดต' : 'บันทึก' ?>
                </button>
                <a href="news_type_manage.php" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">ยกเลิก</a>
            </div>
        </form>

    <?php else: ?>
        <!-- รายการทั้งหมด -->
        <a href="news_type_manage.php?action=add"
           class="inline-block mb-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
            ➕ เพิ่มประเภทข่าว
        </a>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="py-2 px-3 border-b">รหัส</th>
                        <th class="py-2 px-3 border-b">ชื่อประเภท</th>
                        <th class="py-2 px-3 border-b">ลำดับ</th>
                        <th class="py-2 px-3 border-b">สถานะ</th>
                        <th class="py-2 px-3 border-b">เครื่องมือ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3 border-b"><?= $row['id']; ?></td>
                                <td class="py-2 px-3 border-b"><?= htmlspecialchars($row['name']); ?></td>
                                <td class="py-2 px-3 border-b text-center"><?= $row['sort_order']; ?></td>
                                <td class="py-2 px-3 border-b text-center">
                                    <?= $row['visible'] ? '<span class="text-green-600">✔ แสดง</span>' : '<span class="text-gray-500">✘ ซ่อน</span>'; ?>
                                </td>
                                <td class="py-2 px-3 border-b space-x-1 text-center">
                                    <a href="news_type_manage.php?action=edit&id=<?= $row['id']; ?>"
                                       class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-xs">แก้ไข</a>
                                    <a href="news_type_manage.php?action=delete&id=<?= $row['id']; ?>"
                                       onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบประเภทข่าวนี้?');"
                                       class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">ลบ</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-4 text-center text-gray-500">ไม่พบข้อมูลประเภทข่าว</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-6">
            <nav class="inline-flex space-x-1">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="news_type_manage.php?page=<?= $i; ?>"
                       class="px-3 py-1 border rounded <?= ($i == $page) ? 'bg-blue-600 text-white' : 'bg-white text-gray-800 hover:bg-gray-100' ?>">
                        <?= $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
