<?php
session_start();
require 'config.php';
use Firebase\JWT\JWT;

// Generate CSRF Token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // Verify CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $error = "Session Expired or Invalid Request (CSRF). Please reload the page.";
    } else {
        // Authenticate User
        $stmt = $mysqli->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Regenerate Session ID to prevent fixation
                session_regenerate_id(true);
                
                $payload = [
                    "iss" => $issuer,
                    "iat" => time(),
                    "exp" => time() + 3600,
                    "uid" => $user['id'],
                    "username" => $user['username']
                ];
                $jwt = JWT::encode($payload, $secret_key, 'HS256');

                setcookie("jwt_token", $jwt, [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);

                header("Location: admin/admin-index.php");
                exit();
            }
        }
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ - วิทยาลัยเทคนิคเลย</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'IBM Plex Sans Thai', sans-serif; }
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">

  <!-- Background Decoration -->
  <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10">
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-emerald-200/30 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-200/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
  </div>

  <div class="w-full max-w-md bg-white/80 backdrop-blur-xl p-8 rounded-2xl shadow-2xl border border-white/50 animate-fade-in-up m-4">
    
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-emerald-100 shadow-sm transform transition duration-500 hover:rotate-12">
             <img src="/svg/loeitech-logo.png" alt="Logo" class="w-12 h-12 object-contain">
        </div>
        <h2 class="text-2xl font-bold text-gray-800">ยินดีต้อนรับ</h2>
        <p class="text-sm text-gray-500 mt-1">เข้าสู่ระบบจัดการเว็บไซต์</p>
    </div>

    <?php if ($error): ?>
      <div class="mb-4 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r shadow-sm flex items-start animate-fade-in-up" role="alert">
          <i class="fas fa-exclamation-circle mt-1 mr-2 text-lg"></i>
          <div>
            <p class="font-bold">ข้อผิดพลาด</p>
            <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
          </div>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-6">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      
      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ใช้</label>
        <div class="relative group">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                <i class="fas fa-user-circle"></i>
            </span>
            <input type="text" name="username" id="username" required 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all bg-white/50 placeholder-gray-400 text-gray-900 shadow-sm"
                placeholder="ระบุชื่อผู้ใช้" autocomplate="off">
        </div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
        <div class="relative group">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 group-focus-within:text-emerald-600 transition-colors">
                <i class="fas fa-lock"></i>
            </span>
            <input type="password" name="password" id="password" required 
                class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all bg-white/50 placeholder-gray-400 text-gray-900 shadow-sm"
                placeholder="ระบุรหัสผ่าน">
            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-emerald-600 focus:outline-none cursor-pointer transition-colors" title="แสดง/ซ่อน รหัสผ่าน">
                <i class="fas fa-eye" id="toggleIcon"></i>
            </button>
        </div>
      </div>

      <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-semibold py-2.5 rounded-lg shadow-lg hover:shadow-xl hover:scale-[1.02] transform transition-all duration-200">
        <i class="fas fa-sign-in-alt mr-2"></i> เข้าสู่ระบบ
      </button>
    </form>
    
    <div class="mt-8 text-center border-t border-gray-100 pt-6">
        <a href="/" class="text-sm text-gray-400 hover:text-emerald-600 transition flex items-center justify-center group">
            <i class="fas fa-arrow-left mr-2 text-xs transform group-hover:-translate-x-1 transition-transform"></i> กลับหน้าหลักเว็บไซต์
        </a>
    </div>

  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const toggleIcon = document.getElementById('toggleIcon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }
  </script>
</body>
</html>
