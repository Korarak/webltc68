// เมื่อผู้ใช้เลื่อนลง 20px จากด้านบนของเอกสารให้แสดงปุ่ม
window.onscroll = function() {scrollFunction()};

function scrollFunction() {
    const scrollBtn = document.getElementById("scrollBtn");
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        scrollBtn.style.display = "block";
    } else {
        scrollBtn.style.display = "none";
    }
}

// เมื่อผู้ใช้คลิกที่ปุ่มเลื่อนขึ้นไปด้านบนของเอกสาร
function scrollToTop() {
    document.body.scrollTop = 0; // สำหรับ Safari
    document.documentElement.scrollTop = 0; // สำหรับ Chrome, Firefox, IE และ Opera
}
