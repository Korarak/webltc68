<?php
include 'middleware.php';
ob_start();
include '../condb/condb.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin-manage.php");
    exit();
}

$user_id = $_GET['id'];

// ดึงข้อมูลผู้ใช้
$stmt = $mysqli->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['message'] = "ไม่พบผู้ใช้";
    $_SESSION['message_type'] = "danger";
    header("Location: admin-manage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $change_password = !empty($_POST['password']);
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "กรุณากรอกชื่อผู้ใช้";
    }
    
    // Check if username exists (excluding current user)
    $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $check_stmt->bind_param("si", $username, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว";
    }
    
    if ($change_password) {
        if (strlen($_POST['password']) < 6) {
            $errors[] = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
        }
        
        if ($_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "รหัสผ่านไม่ตรงกัน";
        }
    }
    
    if (empty($errors)) {
        if ($change_password) {
            $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $update_stmt = $mysqli->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $username, $hashed_password, $user_id);
        } else {
            $update_stmt = $mysqli->prepare("UPDATE users SET username = ? WHERE id = ?");
            $update_stmt->bind_param("si", $username, $user_id);
        }
        
        if ($update_stmt->execute()) {
            $_SESSION['message'] = "อัพเดทข้อมูลผู้ใช้สำเร็จ";
            $_SESSION['message_type'] = "success";
            header("Location: admin-manage.php");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัพเดทข้อมูล";
        }
    }
}
?>

<div class="max-w-md mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg border border-gray-200">
    <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 text-white text-center py-4 rounded-xl mb-6">
        <h2 class="text-xl font-bold flex items-center justify-center gap-2">
            <i class="fas fa-user-edit"></i>
            แก้ไขข้อมูลผู้ใช้
        </h2>
        <p class="text-yellow-100 text-sm mt-1"><?= htmlspecialchars($user['username']) ?></p>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                <div>
                    <h4 class="text-red-800 font-semibold">พบข้อผิดพลาด</h4>
                    <ul class="text-red-700 text-sm mt-1 list-disc list-inside">
                        <?php foreach($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-user mr-1 text-blue-600"></i>
                ชื่อผู้ใช้ *
            </label>
            <input type="text" name="username" 
                   value="<?= htmlspecialchars($user['username']) ?>" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                   required>
        </div>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-key mr-2 text-blue-600"></i>
                เปลี่ยนรหัสผ่าน (ไม่บังคับ)
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">รหัสผ่านใหม่</label>
                    <input type="password" name="password" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                           placeholder="เว้นว่างหากไม่ต้องการเปลี่ยน">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="confirm_password" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                           placeholder="ยืนยันรหัสผ่านใหม่">
                </div>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <a href="admin-manage.php" 
               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white text-center py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                ย้อนกลับ
            </a>
            <button type="submit" 
                    class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                อัพเดท
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>