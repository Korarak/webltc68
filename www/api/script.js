document.addEventListener("DOMContentLoaded", function () {
    let thaiId = ""; // เก็บค่า thai_id
    
    // 🔧 CONFIGURATION (ตั้งค่าระบบ)
    const API_PHP = "https://www.loeitech.ac.th/api";     // ฝั่ง PHP ตรวจสอบบุคลากร
    const API_FLASK = "https://hotspot.loeitech.org";     // 👈 อัปเดต URL Flask เรียบร้อยครับ
    const HOTSPOT_URL = "http://10.1.1.1";                // หน้า Login Hotspot
    const API_KEY = "MY_SECRET_KEY";                      // ⚠️ อย่าลืมแก้ให้ตรงกับ .env ใน Server Flask นะครับ

    // เริ่มต้นทำงาน
    checkIPAndInitialize();

    function checkIPAndInitialize() {
        // (ส่วนเช็ค IP)
        // ถ้าต้องการเปิดใช้เช็ค IP ให้ย้าย initializeApplication() เข้าไปใน logic ของ fetch IP
        initializeApplication(); 
    }

    function initializeApplication() {
        setupEventListeners();
    }

    function setupEventListeners() {
        // 1. ดักจับการกดปุ่ม "ตรวจสอบ" (Step 1)
        const checkForm = document.getElementById("checkThaiIdForm");
        if (checkForm) {
            checkForm.addEventListener("submit", function (event) {
                event.preventDefault();
                thaiId = document.getElementById("thai_id").value.trim();

                if (!/^\d{13}$/.test(thaiId)) {
                    document.getElementById("error-message").innerText = "เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก!";
                    return;
                }

                document.getElementById("error-message").innerText = "กำลังตรวจสอบ...";

                // เรียก PHP เพื่อเช็คว่าเป็นบุคลากรจริงไหม
                fetch(`${API_PHP}/check_thai_id.php`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ thai_id: thaiId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        // ✅ เป็นบุคลากรจริง -> ไปเช็คต่อว่ามี ID ใน MikroTik หรือยัง
                        checkExistingUser(thaiId);
                    } else {
                        document.getElementById("error-message").innerText = "ไม่พบข้อมูลบุคลากรในระบบ!";
                    }
                })
                .catch(error => {
                    console.error("PHP Error:", error);
                    document.getElementById("error-message").innerText = "เชื่อมต่อฐานข้อมูลบุคลากรไม่ได้";
                });
            });
        }

        // 2. ดักจับการกดปุ่ม "สมัครใช้งาน" (Step 2)
        const registerForm = document.getElementById("registerForm");
        if (registerForm) {
            registerForm.addEventListener("submit", function (event) {
                event.preventDefault();
                registerUser();
            });
        }

        // 3. ปุ่มปิด/ย้อนกลับ
        document.querySelectorAll(".closeWindow").forEach(btn => {
            btn.addEventListener("click", () => {
                const step2 = document.getElementById("step2");
                if(step2 && step2.style.display === "block"){
                    // ถ้าย้อนกลับจากหน้าสมัคร ให้กลับไปหน้าตรวจสอบ
                    step2.style.display = "none";
                    document.getElementById("step1").style.display = "block";
                    // รีเซ็ตค่า UI เดิมกลับมา
                    location.reload(); 
                } else {
                    if (window.history.length > 1) {
                        window.history.back();
                    } else {
                        alert("ไม่สามารถย้อนกลับได้");
                    }
                }
            });
        });
    }

    // 🔍 ฟังก์ชันเช็คว่ามี User ใน MikroTik หรือยัง
    function checkExistingUser(id) {
        // ใช้ API_FLASK ที่อัปเดตแล้ว
        fetch(`${API_FLASK}/get_user_by_thai_id`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ thai_id: id })
        })
        .then(response => response.json())
        .then(data => {
            console.log("Flask Response:", data);

            if (data.status === "success") {
                // ✅ กรณี 1: มีบัญชีอยู่แล้ว -> โชว์ Username/Password เลย
                const userData = data.data;
                showAccountInfo(userData.username, userData.password);
            } else {
                // ❌ กรณี 2: ยังไม่มีบัญชี -> เด้งไปหน้าสมัคร (Step 2)
                document.getElementById("step1").style.display = "none";
                document.getElementById("step2").style.display = "block";
                
                // เคลียร์ค่า error เก่า
                document.getElementById("register-error-message").innerText = "";
                document.getElementById("username").value = "";
                document.getElementById("password").value = "";
                document.getElementById("confirm_password").value = "";
            }
        })
        .catch(error => {
            console.error("Flask Error:", error);
            // แจ้งเตือน user ให้ชัดเจนขึ้น
            document.getElementById("error-message").innerText = "ติดต่อ Server ไม่ได้ (ตรวจสอบ Flask/Internet)";
        });
    }

    // 🖼️ ฟังก์ชันแสดงข้อมูลบัญชี (Inject HTML เข้าไปแทนฟอร์มตรวจสอบ)
    function showAccountInfo(username, password) {
        const html = `
            <div class="alert alert-success">
                <h4 class="alert-heading">✅ ท่านมีบัญชีอยู่แล้ว</h4>
                <hr>
                <p><strong>ชื่อผู้ใช้ (Username):</strong> ${username}</p>
                <div class="mb-3">
                    <strong>รหัสผ่าน (Password):</strong>
                    <div class="input-group">
                        <input type="password" id="showPassField" class="form-control" value="${password}" readonly>
                        <button class="btn btn-outline-secondary" type="button" id="btnTogglePass">ดู</button>
                    </div>
                </div>
                <hr>
                <div class="d-grid gap-2">
                    <a href="${HOTSPOT_URL}" class="btn btn-primary">เข้าสู่ระบบ Hotspot</a>
                    <button id="btnDeleteUser" class="btn btn-danger">ลบบัญชีนี้ (เพื่อสมัครใหม่)</button>
                </div>
            </div>
        `;
        document.getElementById("step1").innerHTML = html;

        // ผูก Event ให้ปุ่มที่สร้างขึ้นใหม่
        document.getElementById("btnTogglePass").addEventListener("click", function() {
            const input = document.getElementById("showPassField");
            if (input.type === "password") {
                input.type = "text";
                this.innerText = "ซ่อน";
            } else {
                input.type = "password";
                this.innerText = "ดู";
            }
        });

        document.getElementById("btnDeleteUser").addEventListener("click", function() {
            if(confirm("คุณต้องการลบบัญชีนี้จริงหรือไม่? ข้อมูลอินเทอร์เน็ตเดิมจะหายไป")) {
                deleteUser(thaiId);
            }
        });
    }

    // 🗑️ ฟังก์ชันลบผู้ใช้
    function deleteUser(id) {
        fetch(`${API_FLASK}/delete_user`, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "Authorization": `Bearer ${API_KEY}` // ส่ง Key
            },
            body: JSON.stringify({ thai_id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("ลบบัญชีเรียบร้อยแล้ว");
                location.reload(); 
            } else {
                alert("เกิดข้อผิดพลาด: " + data.message);
            }
        })
        .catch(error => alert("เชื่อมต่อ Server ไม่ได้"));
    }

    // 📝 ฟังก์ชันสมัครสมาชิก
    function registerUser() {
        const username = document.getElementById("username").value.trim();
        const password = document.getElementById("password").value;
        const confirmPass = document.getElementById("confirm_password").value;

        // Validation
        if (!/^[A-Za-z0-9]{4,}$/.test(username)) {
            document.getElementById("register-error-message").innerText = "Username ต้องเป็นภาษาอังกฤษ/ตัวเลข 4 ตัวขึ้นไป";
            return;
        }
        if (password.length < 4) {
            document.getElementById("register-error-message").innerText = "Password ต้องมี 4 ตัวขึ้นไป";
            return;
        }
        if (password !== confirmPass) {
            document.getElementById("register-error-message").innerText = "รหัสผ่านไม่ตรงกัน";
            return;
        }

        fetch(`${API_FLASK}/add_user`, {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "Authorization": `Bearer ${API_KEY}` // ส่ง Key
            },
            body: JSON.stringify({ 
                thai_id: thaiId, 
                username: username, 
                password: password 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                alert("สมัครสมาชิกสำเร็จ! 🎉");
                // กลับไปหน้าแรกเพื่อโชว์ข้อมูล
                document.getElementById("step2").style.display = "none";
                document.getElementById("step1").style.display = "block";
                checkExistingUser(thaiId); 
            } else {
                document.getElementById("register-error-message").innerText = data.message;
            }
        })
        .catch(error => {
            console.error(error);
            document.getElementById("register-error-message").innerText = "เกิดข้อผิดพลาดในการเชื่อมต่อ";
        });
    }
});