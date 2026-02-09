<?php
ob_start();
include 'middleware.php';
include '../condb/condb.php';

// ====================== FOOTER INFO ======================
// Update Footer Info
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $college_name = $_POST['college_name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $facebook = $_POST['facebook'];
    $website = $_POST['website'];

    $stmt = $mysqli4->prepare("UPDATE footer_info SET college_name=?, address=?, phone=?, email=?, facebook=?, website=? WHERE id=1");
    $stmt->bind_param("ssssss", $college_name, $address, $phone, $email, $facebook, $website);
    $stmt->execute();
    $stmt->close();
}

// Fetch Footer Info
$info_sql = "SELECT * FROM footer_info LIMIT 1";
$info_result = $mysqli4->query($info_sql);
$info = $info_result->fetch_assoc();

// ====================== FOOTER LINKS ======================
// Add Link
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_link'])) {
    $title = $_POST['title'];
    $url = $_POST['url'];
    $category = $_POST['category'];
    $icon = $_POST['icon'];
    $position = $_POST['position'];

    $stmt = $mysqli4->prepare("INSERT INTO footer_links (title, url, category, icon, position, visible) VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssssi", $title, $url, $category, $icon, $position);
    $stmt->execute();
    $stmt->close();
}

// Edit Link
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_link'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $url = $_POST['url'];
    $category = $_POST['category'];
    $icon = $_POST['icon'];
    $position = $_POST['position'];
    $visible = isset($_POST['visible']) ? 1 : 0;

    $stmt = $mysqli4->prepare("UPDATE footer_links SET title=?, url=?, category=?, icon=?, position=?, visible=? WHERE id=?");
    $stmt->bind_param("ssssiii", $title, $url, $category, $icon, $position, $visible, $id);
    $stmt->execute();
    $stmt->close();
}

// Fetch Links
$links_sql = "SELECT * FROM footer_links ORDER BY category, position ASC";
$links_result = $mysqli4->query($links_sql);
?>

<div class="max-w-5xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Footer Management</h2>

    <!-- Footer Info Form -->
    <form action="footer_manage.php" method="post" class="bg-white p-6 rounded shadow mb-10">
        <h3 class="text-lg font-semibold mb-4">Footer Info</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">College Name</label>
                <input type="text" name="college_name" value="<?= htmlspecialchars($info['college_name']) ?>" class="w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block font-medium">Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($info['phone']) ?>" class="w-full border border-gray-300 rounded p-2">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-medium">Address</label>
                <textarea name="address" rows="2" class="w-full border border-gray-300 rounded p-2"><?= htmlspecialchars($info['address']) ?></textarea>
            </div>
            <div>
                <label class="block font-medium">Email</label>
                <input type="text" name="email" value="<?= htmlspecialchars($info['email']) ?>" class="w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block font-medium">Facebook</label>
                <input type="text" name="facebook" value="<?= htmlspecialchars($info['facebook']) ?>" class="w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block font-medium">Website</label>
                <input type="text" name="website" value="<?= htmlspecialchars($info['website']) ?>" class="w-full border border-gray-300 rounded p-2">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" name="update_info" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save Info</button>
        </div>
    </form>

    <!-- Add Link Form -->
    <form action="footer_manage.php" method="post" class="bg-white p-6 rounded shadow mb-10">
        <h3 class="text-lg font-semibold mb-4">Add Footer Link</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium">Title</label>
                <input type="text" name="title" required class="w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block font-medium">URL</label>
                <input type="text" name="url" required class="w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block font-medium">Category</label>
                <select name="category" class="w-full border border-gray-300 rounded p-2">
                    <option value="menu">Menu</option>
                    <option value="resource">Resource</option>
                    <option value="social">Social</option>
                </select>
            </div>
            <div>
                <label class="block font-medium">Icon (FontAwesome)</label>
                <input type="text" name="icon" class="w-full border border-gray-300 rounded p-2">
            </div>
            <div>
                <label class="block font-medium">Position</label>
                <input type="number" name="position" value="0" class="w-full border border-gray-300 rounded p-2">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" name="add_link" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add Link</button>
        </div>
    </form>

    <!-- Display Links -->
    <div class="space-y-6">
        <?php while ($link = $links_result->fetch_assoc()): ?>
            <div class="bg-gray-100 p-4 rounded shadow">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-semibold"><?= htmlspecialchars($link['title']) ?> <span class="text-xs text-gray-500">(<?= $link['category'] ?>)</span></h4>
                    <div class="space-x-2">
                        <button onclick="document.getElementById('editLinkModal<?= $link['id']; ?>').classList.remove('hidden')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                    </div>
                </div>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($link['url']) ?></p>
            </div>

            <!-- Edit Link Modal -->
            <div id="editLinkModal<?= $link['id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white p-6 rounded shadow max-w-md w-full">
                    <form action="footer_manage.php" method="post">
                        <input type="hidden" name="id" value="<?= $link['id']; ?>">
                        <h3 class="text-lg font-semibold mb-4">Edit Link</h3>
                        <div class="mb-4">
                            <label class="block font-medium">Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($link['title']) ?>" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block font-medium">URL</label>
                            <input type="text" name="url" value="<?= htmlspecialchars($link['url']) ?>" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block font-medium">Category</label>
                            <select name="category" class="w-full border border-gray-300 rounded p-2">
                                <option value="menu" <?= $link['category']=='menu'?'selected':'' ?>>Menu</option>
                                <option value="resource" <?= $link['category']=='resource'?'selected':'' ?>>Resource</option>
                                <option value="social" <?= $link['category']=='social'?'selected':'' ?>>Social</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block font-medium">Icon</label>
                            <input type="text" name="icon" value="<?= htmlspecialchars($link['icon']) ?>" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block font-medium">Position</label>
                            <input type="number" name="position" value="<?= htmlspecialchars($link['position']) ?>" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="mb-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="visible" <?= $link['visible']?'checked':'' ?> class="form-checkbox">
                                <span class="ml-2">Visible</span>
                            </label>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                            <button type="submit" name="edit_link" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>
