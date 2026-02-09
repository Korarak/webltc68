<!doctype html>
<!-- <html lang="th" class="monochrome"> -->
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>วิทยาลัยเทคนิคเลย - Loei Technical College</title>
  <meta name="description" content="วิทยาลัยเทคนิคเลย สถาบันการศึกษาอาชีวศึกษาภาคตะวันออกเฉียงเหนือ 1">
  <meta name="author" content="Korarak Promjabok">
  <?php if (isset($og_title)): ?>
    <meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_description) ?>">
    <meta property="og:image" content="<?= $og_image ?>">
    <meta property="og:url" content="<?= $og_url ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="วิทยาลัยเทคนิคเลย">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($og_title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($og_description) ?>">
    <meta name="twitter:image" content="<?= $og_image ?>">
  <?php endif; ?>


  <link rel="icon" type="image/x-icon" href="/svg/loeitech-logo.ico">
  <link rel="shortcut icon" href="/svg/loeitech-logo.ico">

  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- IBM Plex Thai Font -->
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai&display=swap" rel="stylesheet">

  <!-- FontAwesome & Material Icons -->
  <!-- FontAwesome (Local removed: 404) -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>
    /* Monochrome Class */
    .monochrome {
      filter: grayscale(100%);
      -webkit-filter: grayscale(100%);
      -moz-filter: grayscale(100%);
      -o-filter: grayscale(100%);
      -ms-filter: grayscale(100%);
    }

    body {
      font-family: 'IBM Plex Sans Thai', sans-serif;
      background: linear-gradient(145deg, #f5f5f5, #ffffff);
    }

    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }

    .fade-in.visible {
      opacity: 1;
      transform: translateY(0);
    }
  </style>
  <style>
    .thai-font {
      font-family: 'Kanit', 'Sarabun', sans-serif;
    }

    .hover-scale {
      transition: transform 0.2s ease;
    }

    .hover-scale:hover {
      transform: translateY(-2px);
    }

    .black-ribbon {
      position: fixed;
      z-index: 9999;
      width: 70px;
    }

    @media only all and (min-width: 768px) {
      .black-ribbon {
        width: auto;
      }
    }

    .stick-left {
      left: 0;
    }

    .stick-right {
      right: 0;
    }

    .stick-top {
      top: 0;
    }

    .stick-bottom {
      bottom: 0;
    }
  </style>




</head>

<body>

  <img src="/images/black_ribbons/black_ribbon_bottom_left.png" class="black-ribbon stick-bottom stick-left"/>
  <!-- Popup -->
  <?php require_once "app-menu/pop-up.php"; ?>
  <!-- Sidebar -->
  <?php require_once "app-menu/sidebar.php"; ?>
  <!-- Navbar -->
  <?php require_once "app-menu/top_nav.php"; ?>
  <?php
  $current_page = basename($_SERVER['REQUEST_URI']);
  if ($current_page === '') {
    require_once "app-menu/carousel.php";
  }
  ?>
  <!-- Main content -->

  <?= $content ?? ''; ?>

  <!-- Footer -->
  <?php require_once "app-footer/footer.php"; ?>

  <?php require_once "floating-badges.php"; ?>

  <!-- Scroll to top button -->
  <button onclick="scrollToTop()" id="scrollBtn" title="Go to top"
    class="fixed bottom-5 right-8 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-full shadow-lg">
    <span class="material-symbols-outlined">keyboard_arrow_up</span>
  </button>

  <!-- Scripts -->
  <script src="/js/offcanvas.js"></script>
  <script src="/js/scroll.js"></script>
  <script src="/js/visitorCounter.js"></script>

  <!-- Fade-in on scroll -->
  <script>
    const faders = document.querySelectorAll('.fade-in');
    const appearOptions = { threshold: 0.01, rootMargin: "0px 0px -50px 0px" };

    const appearOnScroll = new IntersectionObserver(function (entries, observer) {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('visible');
        observer.unobserve(entry.target);
      });
    }, appearOptions);

    faders.forEach(fader => {
      appearOnScroll.observe(fader);
    });

    function scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  </script>
</body>

</html>