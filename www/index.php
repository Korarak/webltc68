<?php
$title = "หน้าแรก";

// Start output buffering
ob_start();
?>
<?php
@include "app-news/88years.php"
?>
<?php
include "app-news/section-register/pr2569.html"
?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-grow">
<div class="max-w-7xl mx-auto p-4 fade-in">
<?php
include "app-news/main-news.php"
?>
<?php
include "app-news/annonce-news.php"
?>
</div>
</main>
<?php
// Capture the output and store it in $content
$content = ob_get_clean();
// Include the base template
include 'base.php';
?>