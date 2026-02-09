<?php
include 'middleware.php';
ob_start();
include '../condb/condb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($username)) {
        $errors[] = "กรุณากรอกชื่อผู้ใช้";
    }
    
    if (empty($password)) {
        $errors[] = "กรุณากรอกรหัสผ่าน";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "รหัสผ่านไม่ตรงกัน";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
    }
    
    // Check if username exists
    $check_stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_stmt = $mysqli->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $insert_stmt->bind_param("ss", $username, $hashed_password);
        
        if ($insert_stmt->execute()) {
            $_SESSION['message'] = "เพิ่มผู้ใช้สำเร็จ";
            $_SESSION['message_type'] = "success";
            header("Location: admin-manage.php");
            exit();
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการเพิ่มผู้ใช้";
        }
    }
}
?>

<div class="max-w-md mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg border border-gray-200">
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white text-center py-4 rounded-xl mb-6">
        <h2 class="text-xl font-bold flex items-center justify-center gap-2">
            <i class="fas fa-user-plus"></i>
            เพิ่มผู้ดูแลใหม่
        </h2>
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
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                   required 
                   placeholder="กรอกชื่อผู้ใช้">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-key mr-1 text-blue-600"></i>
                รหัสผ่าน *
            </label>
            <input type="password" name="password" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                   required 
                   placeholder="กรอกรหัสผ่าน (อย่างน้อย 6 ตัวอักษร)">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-key mr-1 text-blue-600"></i>
                ยืนยันรหัสผ่าน *
            </label>
            <input type="password" name="confirm_password" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition-colors" 
                   required 
                   placeholder="กรอกรหัสผ่านอีกครั้ง">
        </div>

        <div class="flex gap-3 pt-4">
            <a href="admin-manage.php" 
               class="flex-1 bg-gray-500 hover:bg-gray-600 text-white text-center py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                ย้อนกลับ
            </a>
            <button type="submit" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>
                บันทึก
            </button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
include 'dashboard.php';
?>