<?php
/**
 * batch_optimize.php — บีบอัดภาพเก่าที่มีอยู่ในระบบ
 * 
 * เรียกจาก admin เท่านั้น: /admin/batch_optimize.php
 * หรือ CLI: php admin/batch_optimize.php
 */
require_once __DIR__ . '/../includes/optimize_image.php';

// ตรวจสอบสิทธิ์ (ข้ามถ้าเรียกจาก CLI)
if (php_sapi_name() !== 'cli') {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        die('Unauthorized — กรุณาเข้าสู่ระบบก่อน');
    }
}

// กำหนด max width ตามโฟลเดอร์
$folders = [
    '../uploads/carousel'      => ['maxWidth' => 1600, 'quality' => 82],
    '../uploads/newsletter'    => ['maxWidth' => 800,  'quality' => 80],
    '../uploads/ltc_personal'  => ['maxWidth' => 600,  'quality' => 80],
    '../uploads/news'          => ['maxWidth' => 1200, 'quality' => 80],
    '../uploads/editor_images' => ['maxWidth' => 1200, 'quality' => 80],
    '../uploads/badges'        => ['maxWidth' => 400,  'quality' => 80],
    '../uploads/pages'         => ['maxWidth' => 1200, 'quality' => 80],
];

// ภาพใน root uploads (ไม่อยู่ใน subfolder)
$rootUploads = ['maxWidth' => 1200, 'quality' => 80];

$isCli = (php_sapi_name() === 'cli');
$nl = $isCli ? "\n" : "<br>";

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Batch Optimize</title>
    <style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0;}
    .success{color:#4ade80;} .skip{color:#facc15;} .error{color:#f87171;}
    h2{color:#60a5fa;border-bottom:1px solid #334;padding-bottom:8px;}
    .summary{background:#1e293b;padding:16px;border-radius:8px;margin-top:20px;}
    </style></head><body>";
    echo "<h1>🗜️ Batch Image Optimizer</h1>";
}

$totalSaved = 0;
$totalFiles = 0;
$totalOptimized = 0;
$totalSkipped = 0;
$totalErrors = 0;

$extensions = ['jpg', 'jpeg', 'png', 'gif'];

foreach ($folders as $folder => $config) {
    if (!is_dir($folder)) continue;
    
    echo "{$nl}<h2>📁 " . basename($folder) . "</h2>{$nl}";
    
    $files = glob($folder . '/*.{' . implode(',', $extensions) . '}', GLOB_BRACE | GLOB_NOSORT);
    // เพิ่ม uppercase extensions
    $files = array_merge($files, glob($folder . '/*.{' . strtoupper(implode(',', $extensions)) . '}', GLOB_BRACE | GLOB_NOSORT));
    $files = array_unique($files);
    
    foreach ($files as $file) {
        $totalFiles++;
        $originalSize = filesize($file);
        
        // ข้ามไฟล์เล็กกว่า 50KB (ถือว่า optimize แล้ว)
        if ($originalSize < 51200) {
            echo "<span class='skip'>⏭ SKIP</span> " . basename($file) . " (" . formatBytes($originalSize) . " — เล็กพอแล้ว){$nl}";
            $totalSkipped++;
            continue;
        }
        
        $result = optimizeImage($file, $config['maxWidth'], $config['quality']);
        
        if ($result['success'] && $result['saved'] > 0) {
            echo "<span class='success'>✅ OK</span> " . basename($file) 
                 . " — " . formatBytes($result['original_size']) 
                 . " → " . formatBytes($result['new_size'])
                 . " (ลด " . formatBytes($result['saved']) . ")"
                 . ($result['webp'] ? " + WebP" : "")
                 . $nl;
            $totalSaved += $result['saved'];
            $totalOptimized++;
        } elseif ($result['success']) {
            echo "<span class='skip'>⏭ SKIP</span> " . basename($file) . " — ไม่สามารถลดขนาดได้อีก{$nl}";
            $totalSkipped++;
        } else {
            echo "<span class='error'>❌ ERROR</span> " . basename($file) . " — " . $result['error'] . $nl;
            $totalErrors++;
        }
    }
}

// Root uploads
echo "{$nl}<h2>📁 uploads (root)</h2>{$nl}";
$rootFiles = glob('../uploads/*.{' . implode(',', $extensions) . '}', GLOB_BRACE | GLOB_NOSORT);
$rootFiles = array_merge($rootFiles, glob('../uploads/*.{' . strtoupper(implode(',', $extensions)) . '}', GLOB_BRACE | GLOB_NOSORT));
$rootFiles = array_unique($rootFiles);

foreach ($rootFiles as $file) {
    $totalFiles++;
    $originalSize = filesize($file);
    
    if ($originalSize < 51200) {
        echo "<span class='skip'>⏭ SKIP</span> " . basename($file) . " (" . formatBytes($originalSize) . "){$nl}";
        $totalSkipped++;
        continue;
    }
    
    $result = optimizeImage($file, $rootUploads['maxWidth'], $rootUploads['quality']);
    
    if ($result['success'] && $result['saved'] > 0) {
        echo "<span class='success'>✅ OK</span> " . basename($file) 
             . " — " . formatBytes($result['original_size']) 
             . " → " . formatBytes($result['new_size'])
             . " (ลด " . formatBytes($result['saved']) . ")" . $nl;
        $totalSaved += $result['saved'];
        $totalOptimized++;
    } elseif ($result['success']) {
        echo "<span class='skip'>⏭ SKIP</span> " . basename($file) . $nl;
        $totalSkipped++;
    } else {
        echo "<span class='error'>❌ ERROR</span> " . basename($file) . " — " . $result['error'] . $nl;
        $totalErrors++;
    }
}

// สรุปผล
echo "{$nl}<div class='summary'>";
echo "<h2>📊 สรุปผล</h2>";
echo "ไฟล์ทั้งหมด: <strong>{$totalFiles}</strong>{$nl}";
echo "Optimized: <span class='success'><strong>{$totalOptimized}</strong></span>{$nl}";
echo "Skipped: <span class='skip'><strong>{$totalSkipped}</strong></span>{$nl}";
echo "Errors: <span class='error'><strong>{$totalErrors}</strong></span>{$nl}";
echo "ประหยัดพื้นที่: <span class='success'><strong>" . formatBytes($totalSaved) . "</strong></span>{$nl}";
echo "</div>";

if (!$isCli) echo "</body></html>";
