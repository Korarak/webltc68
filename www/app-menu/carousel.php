<?php
// PHP: ดึงข้อมูลสไลด์จากฐานข้อมูล
include('./condb/condb.php');

// ตรวจสอบว่ามีการเชื่อมต่อ $mysqli2 หรือไม่
if (isset($mysqli2) && $mysqli2 instanceof mysqli) {
    $sql = "SELECT * FROM carousel WHERE visible = 1 AND slide_show = 0 ORDER BY carousel_no ASC";
    $result = $mysqli2->query($sql);

    $slides = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $slides[] = $row;
        }
    }

    $mysqli2->close();
} else {
    // หากเชื่อมต่อฐานข้อมูลล้มเหลว หรือ $mysqli2 ไม่ได้ถูกกำหนด
    $slides = [];
    // ในกรณีนี้ เราจะปล่อยให้ $slides เป็น array ว่าง เพื่อไม่ให้แสดง Carousel
}

// **เงื่อนไขสำคัญ:** จะแสดงผล HTML และ JavaScript ทั้งหมด เมื่อมีข้อมูลสไลด์เท่านั้น
if (!empty($slides)):
?>

<div class="max-w-7xl mx-auto mt-[84px] mb-4 px-4 md:px-8 relative">

  <div id="tailwind-carousel" class="relative overflow-hidden rounded-lg bg-gray-900 shadow-lg">
    <div class="relative w-full bg-gray-900" style="aspect-ratio: auto;">
      <?php foreach ($slides as $index => $slide): ?>
        <div class="carousel-slide absolute inset-0 w-full h-full transition-opacity duration-700 ease-in-out <?= $index === 0 ? 'opacity-100 z-20' : 'opacity-0 z-10' ?>" data-index="<?= $index ?>">
          <div class="absolute inset-0 w-full h-full bg-gray-900 flex items-center justify-center overflow-hidden">
            <?php
                // Robust Path Logic
                $clean_path = str_replace(['../', './', 'admin/'], '', $slide['carousel_pic']);
                $clean_path = ltrim($clean_path, '/');
                if (strpos($clean_path, 'uploads/') !== 0) $clean_path = 'uploads/' . $clean_path;
                $img_src = "../" . $clean_path;
            ?>
            <img src="<?= htmlspecialchars($img_src) ?>"
                 class="carousel-image"
                 alt="Slide <?= $index + 1 ?>"
                 loading="lazy">
          </div>

          <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-black/50 to-transparent text-white text-center py-6 px-4">
            <h5 class="text-xl font-bold"><?= htmlspecialchars($slide['carousel_text1'] ?? '') ?></h5>
            <p class="text-sm mt-1"><?= htmlspecialchars($slide['carousel_text2'] ?? '') ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2 z-30">
      <?php foreach ($slides as $index => $slide): ?>
        <button class="w-3 h-3 rounded-full transition-all duration-300 <?= $index === 0 ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75' ?>"
                data-slide="<?= $index ?>"
                aria-label="Go to slide <?= $index + 1 ?>"></button>
      <?php endforeach; ?>
    </div>

    <button id="prev-slide" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white p-3 rounded-full z-30 transition group" aria-label="Previous slide">
      <svg class="w-6 h-6 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
    </button>
    <button id="next-slide" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-black/40 hover:bg-black/70 text-white p-3 rounded-full z-30 transition group" aria-label="Next slide">
      <svg class="w-6 h-6 group-hover:scale-110 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </button>
  </div>
</div>

<script>
  const slides = document.querySelectorAll(".carousel-slide");
  const indicators = document.querySelectorAll("[data-slide]");
  const carousel = document.getElementById('tailwind-carousel');
  const slidesContainer = carousel?.querySelector('.relative'); // ใช้ Optional Chaining เผื่อ carousel เป็น null

  let currentIndex = 0;
  let autoSlideInterval;

  function updateHeight() {
    // ปรับความสูงตามรูปแรก
    if (slides.length > 0 && slidesContainer) {
      const firstImg = slides[0].querySelector('img');
      if (firstImg && firstImg.complete) {
        const aspectRatio = firstImg.naturalWidth / firstImg.naturalHeight;
        // ใช้ paddingBottom เพื่อกำหนดความสูงตาม Aspect Ratio ของรูปภาพ
        slidesContainer.style.paddingBottom = `${(1 / aspectRatio) * 100}%`; 
      }
    }
  }

  function showSlide(index) {
    slides.forEach((slide, i) => {
      if (i === index) {
        slide.classList.remove("opacity-0", "z-10");
        slide.classList.add("opacity-100", "z-20");
      } else {
        slide.classList.remove("opacity-100", "z-20");
        slide.classList.add("opacity-0", "z-10");
      }
    });

    // อัปเดต indicators
    indicators.forEach((indicator, i) => {
      if (i === index) {
        indicator.classList.remove("w-3", "bg-white/50", "hover:bg-white/75");
        indicator.classList.add("w-8", "bg-white");
      } else {
        indicator.classList.remove("w-8", "bg-white");
        indicator.classList.add("w-3", "bg-white/50", "hover:bg-white/75");
      }
    });

    currentIndex = index;
  }

  function nextSlide() {
    if (slides.length > 1) {
      let index = (currentIndex + 1) % slides.length;
      showSlide(index);
    }
  }

  function prevSlide() {
    if (slides.length > 1) {
      let index = (currentIndex - 1 + slides.length) % slides.length;
      showSlide(index);
    }
  }

  document.getElementById("prev-slide")?.addEventListener("click", () => {
    prevSlide();
    resetAutoSlide();
  });

  document.getElementById("next-slide")?.addEventListener("click", () => {
    nextSlide();
    resetAutoSlide();
  });

  indicators.forEach(btn => {
    btn.addEventListener("click", () => {
      showSlide(parseInt(btn.dataset.slide));
      resetAutoSlide();
    });
  });

  // Auto slide every 5 seconds
  function startAutoSlide() {
    if (slides.length > 1) {
      autoSlideInterval = setInterval(() => {
        nextSlide();
      }, 5000);
    }
  }

  function resetAutoSlide() {
    clearInterval(autoSlideInterval);
    startAutoSlide();
  }

  // Start auto slide on page load
  document.addEventListener('DOMContentLoaded', () => {
    updateHeight();
    startAutoSlide();
  });

  // Update height on window resize
  window.addEventListener('resize', updateHeight);

  // Stop auto slide when carousel is hovered
  carousel?.addEventListener('mouseenter', () => {
    clearInterval(autoSlideInterval);
  });

  carousel?.addEventListener('mouseleave', startAutoSlide);

  // Update height when images load
  slides.forEach(slide => {
    const img = slide.querySelector('img');
    if (img) {
      // ตรวจสอบว่ารูปโหลดเสร็จแล้วหรือไม่ ก่อนเรียก updateHeight
      if (img.complete) {
        updateHeight();
      } else {
        img.addEventListener('load', updateHeight);
      }
    }
  });
</script>

<style>
  /* ไม่ crop - แสดงรูปเต็ม */
  .carousel-image {
    object-fit: contain; /* เปลี่ยนจาก cover เป็น contain เพื่อไม่ให้รูปภาพถูก crop */
    object-position: center;
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
  }

  #tailwind-carousel {
    width: 100%;
  }

  .carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    #prev-slide, #next-slide {
      padding: 0.625rem;
    }

    #prev-slide svg, #next-slide svg {
      width: 1.5rem;
      height: 1.5rem;
    }
  }

  /* Smooth transitions */
  [data-slide] {
    transition: all 0.3s ease;
  }

  #prev-slide, #next-slide {
    transition: all 0.3s ease;
  }
</style>

<?php 
// สิ้นสุดเงื่อนไข if (!empty($slides))
endif; 
?>