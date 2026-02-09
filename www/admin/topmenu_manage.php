<?php
ob_start();
include 'middleware.php';
include '../condb/condb.php';

// Add Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_menu'])) {
    $menu_name = $_POST['menu_name'];
    $menu_link = $_POST['menu_link'] ? $_POST['menu_link'] : NULL;
    $is_dropdown = isset($_POST['is_dropdown']) ? 1 : 0;

    $stmt = $mysqli4->prepare("INSERT INTO menus (menu_name, menu_link, is_dropdown) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $menu_name, $menu_link, $is_dropdown);
    $stmt->execute();
    $stmt->close();
}

// Edit Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_menu'])) {
    $menu_id = $_POST['menu_id'];
    $menu_name = $_POST['menu_name'];
    $menu_link = $_POST['menu_link'];
    $is_dropdown = isset($_POST['is_dropdown']) ? 1 : 0;

    $stmt = $mysqli4->prepare("UPDATE menus SET menu_name = ?, menu_link = ?, is_dropdown = ? WHERE menu_id = ?");
    $stmt->bind_param("ssii", $menu_name, $menu_link, $is_dropdown, $menu_id);
    $stmt->execute();
    $stmt->close();
}

// Add Sub-Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_submenu'])) {
    $menu_id = $_POST['menu_id'];
    $submenu_name = $_POST['submenu_name'];
    $submenu_link = $_POST['submenu_link'];

    $stmt = $mysqli4->prepare("INSERT INTO sub_menus (menu_id, submenu_name, submenu_link) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $menu_id, $submenu_name, $submenu_link);
    $stmt->execute();
    $stmt->close();
}

// Edit Sub-Menu
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_submenu'])) {
    $submenu_id = $_POST['submenu_id'];
    $submenu_name = $_POST['submenu_name'];
    $submenu_link = $_POST['submenu_link'];

    $stmt = $mysqli4->prepare("UPDATE sub_menus SET submenu_name = ?, submenu_link = ? WHERE submenu_id = ?");
    $stmt->bind_param("ssi", $submenu_name, $submenu_link, $submenu_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch Menus and Sub-Menus
$menus_sql = "SELECT * FROM menus";
$menus_result = $mysqli4->query($menus_sql);
?>

<div class="max-w-5xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Top Menu Management</h2>

    <!-- Add Menu Form -->
    <form action="topmenu_manage.php" method="post" class="bg-white p-6 rounded shadow mb-10">
        <h3 class="text-lg font-semibold mb-4">Add Menu</h3>
        <div class="mb-4">
            <label for="menu_name" class="block font-medium">Menu Name</label>
            <input type="text" id="menu_name" name="menu_name" required class="mt-1 w-full border border-gray-300 rounded p-2">
        </div>
        <div class="mb-4">
            <label for="menu_link" class="block font-medium">Menu Link</label>
            <input type="text" id="menu_link" name="menu_link" class="mt-1 w-full border border-gray-300 rounded p-2">
        </div>
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" id="is_dropdown" name="is_dropdown" class="form-checkbox">
                <span class="ml-2">Has Dropdown</span>
            </label>
        </div>
        <button type="submit" name="add_menu" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Menu</button>
    </form>

    <!-- Add Sub-Menu Form -->
    <form action="topmenu_manage.php" method="post" class="bg-white p-6 rounded shadow mb-10">
        <h3 class="text-lg font-semibold mb-4">Add Sub-Menu</h3>
        <div class="mb-4">
            <label for="menu_id" class="block font-medium">Select Menu</label>
            <select id="menu_id" name="menu_id" required class="mt-1 w-full border border-gray-300 rounded p-2">
                <?php $menus_result->data_seek(0); while ($menu = $menus_result->fetch_assoc()): ?>
                    <option value="<?= $menu['menu_id']; ?>"><?= $menu['menu_name']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-4">
            <label for="submenu_name" class="block font-medium">Sub-Menu Name</label>
            <input type="text" id="submenu_name" name="submenu_name" required class="mt-1 w-full border border-gray-300 rounded p-2">
        </div>
        <div class="mb-4">
            <label for="submenu_link" class="block font-medium">Sub-Menu Link</label>
            <input type="text" id="submenu_link" name="submenu_link" class="mt-1 w-full border border-gray-300 rounded p-2">
        </div>
        <button type="submit" name="add_submenu" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Sub-Menu</button>
    </form>

    <!-- Display Menus and Sub-Menus -->
    <div class="space-y-6">
        <?php $menus_result->data_seek(0); while ($menu = $menus_result->fetch_assoc()):
            $submenu_sql = "SELECT * FROM sub_menus WHERE menu_id = " . $menu['menu_id'];
            $submenu_result = $mysqli4->query($submenu_sql);
        ?>
            <div class="bg-gray-100 p-4 rounded shadow">
                <div class="flex justify-between items-center">
                    <h4 class="text-lg font-semibold"><?= $menu['menu_name']; ?></h4>
                    <div class="space-x-2">
                        <a href="topmenu_delete.php?id=<?= $menu['menu_id']; ?>" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Delete</a>
                        <button onclick="document.getElementById('editMenuModal<?= $menu['menu_id']; ?>').classList.remove('hidden')" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Edit</button>
                    </div>
                </div>
                <?php if ($submenu_result->num_rows > 0): ?>
                    <ul class="list-disc pl-6 mt-3">
                        <?php while ($submenu = $submenu_result->fetch_assoc()): ?>
                            <li class="flex justify-between items-center">
                                <span><?= $submenu['submenu_name']; ?></span>
                                <div class="space-x-2">
                                    <a href="topsubmenu_delete.php?id=<?= $submenu['submenu_id']; ?>" class="text-red-600 hover:underline">Delete</a>
                                    <button onclick="document.getElementById('editSubmenuModal<?= $submenu['submenu_id']; ?>').classList.remove('hidden')" class="text-yellow-600 hover:underline">Edit</button>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-600 mt-2">No sub-menus</p>
                <?php endif; ?>
            </div>

            <!-- Edit Menu Modal -->
            <div id="editMenuModal<?= $menu['menu_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                <div class="bg-white p-6 rounded shadow max-w-md w-full">
                    <form action="topmenu_manage.php" method="post">
                        <input type="hidden" name="menu_id" value="<?= $menu['menu_id']; ?>">
                        <h3 class="text-lg font-semibold mb-4">Edit Menu</h3>
                        <div class="mb-4">
                            <label class="block font-medium">Menu Name</label>
                            <input type="text" name="menu_name" value="<?= $menu['menu_name']; ?>" required class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block font-medium">Menu Link</label>
                            <input type="text" name="menu_link" value="<?= $menu['menu_link']; ?>" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="mb-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_dropdown" <?= $menu['is_dropdown'] ? 'checked' : ''; ?> class="form-checkbox">
                                <span class="ml-2">Has Dropdown</span>
                            </label>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                            <button type="submit" name="edit_menu" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Submenu Modal -->
            <?php $submenu_result->data_seek(0); while ($submenu = $submenu_result->fetch_assoc()): ?>
                <div id="editSubmenuModal<?= $submenu['submenu_id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                    <div class="bg-white p-6 rounded shadow max-w-md w-full">
                        <form action="topmenu_manage.php" method="post">
                            <input type="hidden" name="submenu_id" value="<?= $submenu['submenu_id']; ?>">
                            <h3 class="text-lg font-semibold mb-4">Edit Sub-Menu</h3>
                            <div class="mb-4">
                                <label class="block font-medium">Sub-Menu Name</label>
                                <input type="text" name="submenu_name" value="<?= $submenu['submenu_name']; ?>" required class="w-full border border-gray-300 rounded p-2">
                            </div>
                            <div class="mb-4">
                                <label class="block font-medium">Sub-Menu Link</label>
                                <input type="text" name="submenu_link" value="<?= $submenu['submenu_link']; ?>" class="w-full border border-gray-300 rounded p-2">
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="this.closest('.fixed').classList.add('hidden')" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                                <button type="submit" name="edit_submenu" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endwhile; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>