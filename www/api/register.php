<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครใช้งานอินเตอร์เน็ต</title>
    
    <!-- Bootstrap 5 -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="img/LTC.png" type="image/x-icon"> <!-- Favicon link -->
    <script src="js/bootstrap.bundle.min.js"></script>
    
    <!-- Google Font -->
    <!-- <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;600&display=swap" rel="stylesheet"> -->
    
    <style>
        @font-face {
            font-family: 'CHULALONGKORNBold';
            src: url('../font/CHULALONGKORNBold.otf') format('truetype');
            }
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 500px;
            margin-top: 50px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            width: 100%;
            border-radius: 8px;
        }
        .btn-secondary {
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }
        .btn-warning {
            width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }
        .error-message {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2 class="text-center text-primary">ระบบสร้างและตรวจสอบ <br>ข้อมูลผู้ใช้งานอินเตอร์เน็ต</h2>
        <p class="text-center text-muted">สำหรับบุคลากรวิทยาลัยเทคนิคเลย</p>
        <!-- Form ตรวจสอบเลขบัตร -->
        <div id="step1">
            <h5 class="mb-3">ท่านสามารถกรอกข้อมูลในหน้าต่างนี้ <br>เพื่อตรวจสอบ username / password ของท่าน</h5>
            <form id="checkThaiIdForm">
                <div class="mb-3">
                    <label class="form-label">เลขบัตรประชาชน 13 หลัก:</label>
                    <input type="text" id="thai_id" class="form-control" maxlength="13" required>
                </div>
                <button type="submit" class="btn btn-primary">ตรวจสอบ</button>
            </form>
            <p id="error-message" class="error-message mt-2"></p>
        </div>

        <!-- Form สมัครใช้งาน -->
        <div id="step2" style="display: none;">
            <h4 class="mb-3">สร้างบัญชี Hotspot</h4>
            <form id="registerForm">
                <div class="mb-3">
                    <label class="form-label">ชื่อผู้ใช้ :</label>
                    <label class="form-label">(เฉพาะตัวอักษรภาษาอังกฤษและตัวเลขเท่านั้น):</label>
                    <input type="text" id="username" class="form-control" pattern="[A-Za-z0-9]+" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">รหัสผ่าน :</label>
                    <label class="form-label">(ตัวอักษรภาษาอังกฤษหรือตัวเลขหรืออักขระพิเศษ)</label>
                    <input type="password" id="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">ยืนยันรหัสผ่าน:</label>
                    <input type="password" id="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">สมัครใช้งาน</button>
                <button type="button" class="btn btn-secondary closeWindow">ย้อนกลับ</button>
            </form>
            <p id="register-error-message" class="error-message mt-2"></p>
        </div>
        <button type="button" class="btn btn-warning closeWindow">ปิด</button>
    </div>

    <script src="script.js" defer></script>

</body>
</html>
