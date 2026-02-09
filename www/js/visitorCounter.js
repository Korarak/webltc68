// นับจำนวนผู้เยี่ยมชมและแสดงผล
function countVisitor() {
    // เรียกฟังก์ชันนับจำนวนผู้เยี่ยมชมทุกครั้งเมื่อหน้าเว็บโหลดใหม่
    let count = localStorage.getItem('visitorCount') || 0; // รับค่าจำนวนผู้เยี่ยมชมจาก local storage หรือเริ่มต้นที่ 0 ถ้าไม่มีค่าใน local storage
    count++; // เพิ่มจำนวนผู้เยี่ยมชมขึ้นทีละ 1 ทุกครั้งที่เว็บโหลดใหม่
    localStorage.setItem('visitorCount', count); // บันทึกค่าจำนวนผู้เยี่ยมชมล่าสุดใน local storage
    document.getElementById('visitorCount').textContent = count; // แสดงค่าจำนวนผู้เยี่ยมชมในหน้า HTML
}

// เรียกใช้ฟังก์ชัน countVisitor เมื่อหน้าเว็บโหลด
countVisitor();
