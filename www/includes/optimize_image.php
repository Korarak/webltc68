<?php
/**
 * optimize_image.php — ฟังก์ชันบีบอัดและปรับขนาดภาพ
 * 
 * ใช้งาน:
 *   require_once __DIR__ . '/../includes/optimize_image.php';
 *   optimizeImage('/path/to/image.jpg', 1600, 80);
 */

/**
 * บีบอัดและ resize ภาพ + สร้าง WebP คู่กัน (ถ้า GD รองรับ)
 *
 * @param string $filepath    เส้นทางไฟล์ภาพจริง
 * @param int    $maxWidth    ความกว้างสูงสุด (px), 0 = ไม่ resize
 * @param int    $quality     คุณภาพ JPEG (1-100), default 82
 * @return array              ['success'=>bool, 'saved'=>bytes ที่ลดได้, 'webp'=>path หรือ null]
 */
function optimizeImage(string $filepath, int $maxWidth = 1600, int $quality = 82): array {
    $result = ['success' => false, 'saved' => 0, 'webp' => null, 'error' => null];

    if (!file_exists($filepath)) {
        $result['error'] = 'File not found';
        return $result;
    }

    if (!function_exists('imagecreatefromjpeg')) {
        $result['error'] = 'GD extension not available';
        return $result;
    }

    $originalSize = filesize($filepath);
    $info = @getimagesize($filepath);
    if (!$info) {
        $result['error'] = 'Not a valid image';
        return $result;
    }

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    // สร้าง GD image จากไฟล์ต้นฉบับ
    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($filepath);
            break;
        default:
            $result['error'] = "Unsupported format: $mime";
            return $result;
    }

    if (!$image) {
        $result['error'] = 'Failed to create image from file';
        return $result;
    }

    // Resize ถ้าภาพกว้างเกินกำหนด
    $needsResize = ($maxWidth > 0 && $width > $maxWidth);
    if ($needsResize) {
        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = (int) round($height * $ratio);

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // รักษา transparency สำหรับ PNG
        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    // เลือก resampled, บันทึกกลับไปที่ไฟล์เดิม
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($image, $filepath, $quality);
            break;
        case 'image/png':
            // PNG compression level: 0 (no compression) - 9 (max)
            // quality 82 → compression level ~6
            $pngQuality = max(0, min(9, (int) round((100 - $quality) / 11)));
            imagepng($image, $filepath, $pngQuality);
            break;
        case 'image/gif':
            imagegif($image, $filepath);
            break;
        case 'image/webp':
            imagewebp($image, $filepath, $quality);
            break;
    }

    // สร้าง WebP version (ถ้า GD รองรับ)
    $webpPath = null;
    if (function_exists('imagewebp') && $mime !== 'image/webp') {
        $pathInfo = pathinfo($filepath);
        $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
        imagewebp($image, $webpPath, $quality);
        $result['webp'] = $webpPath;
    }

    imagedestroy($image);

    // คำนวณขนาดที่ลดได้
    clearstatcache(true, $filepath);
    $newSize = filesize($filepath);
    $result['success'] = true;
    $result['saved'] = $originalSize - $newSize;
    $result['original_size'] = $originalSize;
    $result['new_size'] = $newSize;

    return $result;
}

/**
 * ช่วย format ขนาดไฟล์ให้อ่านง่าย
 */
function formatBytes(int $bytes, int $precision = 1): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * ตรวจสอบว่าไฟล์ WebP มีอยู่คู่กับไฟล์ต้นฉบับหรือไม่
 */
function getWebpPath(string $originalPath): ?string {
    $pathInfo = pathinfo($originalPath);
    $webpPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.webp';
    return file_exists($webpPath) ? $webpPath : null;
}
