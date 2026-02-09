   <!-- 88year.php -->
   <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        anniversary: {
                            gold: '#FFD700',
                            silver: '#C0C0C0'
                        }
                    },
/*                     fontFamily: {
                        'thai': ['Noto Sans Thai', 'sans-serif'],
                    }, */
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'fadeIn': 'fadeIn 1s ease-in-out',
                        'slideIn': 'slideIn 1s ease-out',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'spin-slow': 'spin 8s linear infinite',
                        'bounce-gentle': 'bounce-gentle 2s ease-in-out infinite',
                        'confetti': 'confetti 5s ease-in-out forwards',
                        'number-count': 'number-count 2s ease-out forwards'
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-15px)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideIn: {
                            '0%': { transform: 'translateX(-100%)', opacity: '0' },
                            '100%': { transform: 'translateX(0)', opacity: '1' },
                        },
                        'bounce-gentle': {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-8px)' },
                        },
                        'confetti': {
                            '0%': { transform: 'translateY(-100vh) rotate(0deg)', opacity: '1' },
                            '100%': { transform: 'translateY(100vh) rotate(360deg)', opacity: '0' },
                        },
                        'number-count': {
                            '0%': { transform: 'scale(0.5)', opacity: '0' },
                            '70%': { transform: 'scale(1.1)', opacity: '1' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            /* font-family: 'Noto Sans Thai', sans-serif; */
            overflow-x: hidden;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%);
        }
        .logo-placeholder {
            background: linear-gradient(45deg, #16a34a, #22c55e, #4ade80);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .anniversary-badge {
            background: linear-gradient(135deg, #FFD700, #C0C0C0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: bold;
        }
        .floating-element {
            animation: float 6s ease-in-out infinite;
        }
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: #22c55e;
            opacity: 0.7;
            border-radius: 50%;
        }
        .number-88 {
            font-size: 8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #16a34a, #22c55e, #FFD700);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 30px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #22c55e;
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 20px;
            width: 2px;
            height: calc(100% + 10px);
            background: #22c55e;
        }
        .timeline-item:last-child::after {
            display: none;
        }
        .glow-card {
            transition: all 0.3s ease;
        }
        .glow-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(34, 197, 94, 0.3);
        }
        .counter {
            font-size: 3rem;
            font-weight: 700;
            color: #16a34a;
        }
    </style>
    
<!-- History Section -->
<section id="history" class="py-16 gradient-bg">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-primary-800 mb-4">เส้นทาง 88 ปี</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                เรื่องราวการก้าวเดินผ่านกาลเวลาของวิทยาลัยเทคนิคเลย 
                ตั้งแต่การก่อตั้งจนถึงปัจจุบัน
            </p>
        </div>
        
        <div class="max-w-4xl mx-auto">
            <div class="timeline-item animate-fadeIn">
                <h3 class="text-xl font-bold text-primary-800 mb-2">พ.ศ. 2481</h3>
                <p class="text-gray-600">กระทรวงศึกษาธิการได้ตั้ง "โรงเรียนช่างไม้เลย" ขึ้นและได้เปิดทำการสอน เมื่อวันที่ 17 กรกฎาคม พ.ศ.2481 ตั้งอยู่บริเวณริมฝั่งแม่น้ำเลย (หน้าศาลาเทศบาลเมืองเลย)</p>
            </div>
            
            <div class="timeline-item animate-fadeIn" style="animation-delay: 0.2s;">
                <h3 class="text-xl font-bold text-primary-800 mb-2">พ.ศ. 2502</h3>
                <p class="text-gray-600">วันที่ 15 กันยายน กรมอาชีวศึกษาได้สั่งการให้เปลี่ยนชื่อโรงเรียนจาก "โรงเรียนช่างไม้เลย" เป็น "โรงเรียนการช่างเลย" และถือเป็นวันสถาปนาโรงเรียน</p>
            </div>
            
            <div class="timeline-item animate-fadeIn" style="animation-delay: 0.4s;">
                <h3 class="text-xl font-bold text-primary-800 mb-2">พ.ศ. 2522</h3>
                <p class="text-gray-600">วันที่ 1 มกราคม ได้เปลี่ยนชื่อจาก "โรงเรียนการช่างเลย" เป็น "โรงเรียนเทคนิคเลย"</p>
            </div>
            
            <div class="timeline-item animate-fadeIn" style="animation-delay: 0.6s;">
                <h3 class="text-xl font-bold text-primary-800 mb-2">พ.ศ. 2524</h3>
                <p class="text-gray-600">ยกฐานะจาก "โรงเรียนเทคนิคเลย" มาเป็น "วิทยาลัยเทคนิคเลย"</p>
            </div>
            
            <div class="timeline-item animate-fadeIn" style="animation-delay: 0.8s;">
                <h3 class="text-xl font-bold text-primary-800 mb-2">พ.ศ. 2557-2564</h3>
                <p class="text-gray-600">เปิดสอนระดับปริญญาตรีหลักสูตรเทคโนโลยีบัณฑิต ในสาขาต่างๆ ได้แก่ เทคโนโลยีการก่อสร้าง (โยธา), เทคโนโลยีไฟฟ้า, เทคโนโลยียานยนต์ (เครื่องกล) และเทคโนโลยีการผลิต</p>
            </div>
            
            <div class="timeline-item animate-fadeIn" style="animation-delay: 1.0s;">
                <h3 class="text-xl font-bold text-primary-800 mb-2">พ.ศ. 2568</h3>
                <p class="text-gray-600">ครบรอบ 88 ปี</p>
            </div>
        </div>
    </div>
</section>

<!-- Events Section -->
<section id="events" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-primary-800 mb-4">กิจกรรมครบรอบ 88 ปี</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                ร่วมเฉลิมฉลองและย้อนรำลึกความทรงจำดีๆ ผ่านกิจกรรมต่างๆ 
                ตลอดปีแห่งการเฉลิมฉลอง
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gradient-to-br from-primary-50 to-white p-6 rounded-xl shadow-md border border-primary-100 glow-card">
                <div class="text-primary-600 mb-2">
                    <i class="fas fa-hand-holding-usd mr-2"></i> กิจกรรมระดมทุน
                </div>
                <h3 class="text-xl font-bold text-primary-800 mb-2">ระดมทุนเพื่อการศึกษา</h3>
                <p class="text-gray-600 mb-4">ร่วมบริจาคและสนับสนุนทุนการศึกษาเพื่อพัฒนาการศึกษาของนักเรียนนักศึกษา</p>
                <button class="text-primary-600 hover:text-primary-800 font-medium flex items-center">
                    วันที่ 6 ธันวาคม 2568 <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
            
            <div class="bg-gradient-to-br from-primary-50 to-white p-6 rounded-xl shadow-md border border-primary-100 glow-card">
                <div class="text-primary-600 mb-2">
                    <i class="fas fa-shopping-bag mr-2"></i> สินค้าที่ระลึก
                </div>
                <h3 class="text-xl font-bold text-primary-800 mb-2">สินค้าที่ระลึก 88 ปี</h3>
                <p class="text-gray-600 mb-4">สินค้าที่ระลึกครบรอบ 88 ปี วิทยาลัยเทคนิคเลย สำหรับนักเรียนและศิษย์เก่า</p>
                <button class="text-primary-600 hover:text-primary-800 font-medium flex items-center">
                    <a href="https://88y.loeitech.ac.th">ชมสินค้า</a> <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
            
            <div class="bg-gradient-to-br from-primary-50 to-white p-6 rounded-xl shadow-md border border-primary-100 glow-card">
                <div class="text-primary-600 mb-2">
                    <i class="fas fa-users mr-2"></i> คืนสู่เหย้า
                </div>
                <h3 class="text-xl font-bold text-primary-800 mb-2">คืนสู่เหย้าศิษย์เก่า</h3>
                <p class="text-gray-600 mb-4">พบปะสังสรรค์ศิษย์เก่าวิทยาลัยเทคนิคเลยทุกรุ่น ร่วมบันทึกประวัติศาสตร์ 88 ปี</p>
                <button class="text-primary-600 hover:text-primary-800 font-medium flex items-center">
                    <a href="https://www.loeitech.org/app-news/annonce_detail.php?id=63">อ่าน</a>  <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</section>



<script>
    // Confetti effect
    function launchConfetti() {
        const container = document.getElementById('confetti-container');
        const colors = ['#22c55e', '#16a34a', '#FFD700', '#C0C0C0', '#ffffff'];
        
        for (let i = 0; i < 100; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.animationDelay = Math.random() * 5 + 's';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.width = (Math.random() * 10 + 5) + 'px';
            confetti.style.height = (Math.random() * 10 + 5) + 'px';
            
            container.appendChild(confetti);
            
            setTimeout(() => {
                confetti.remove();
            }, 5000);
        }
    }
</script>