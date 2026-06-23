<?php
// Script to trim/crop the logo PNG tightly using GD
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$srcPath = 'C:/Users/suher/.gemini/antigravity-ide/brain/69e57fb1-8ece-4117-9ecd-1874f0460f58/sobatanak_logo_transparent_1781236868946.png';
$dstPath = __DIR__ . '/public/assets/logo-sobat-anak.png';

// Load the image
$img = imagecreatefrompng($srcPath);
if (!$img) { die("Cannot load image\n"); }

$width = imagesx($img);
$height = imagesy($img);

// Find bounding box of non-transparent pixels
$minX = $width; $minY = $height; $maxX = 0; $maxY = 0;

for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $rgba = imagecolorat($img, $x, $y);
        $alpha = ($rgba >> 24) & 0x7F;
        if ($alpha < 100) { // pixel is not fully transparent
            if ($x < $minX) $minX = $x;
            if ($y < $minY) $minY = $y;
            if ($x > $maxX) $maxX = $x;
            if ($y > $maxY) $maxY = $y;
        }
    }
}

$padding = 8;
$minX = max(0, $minX - $padding);
$minY = max(0, $minY - $padding);
$maxX = min($width - 1, $maxX + $padding);
$maxY = min($height - 1, $maxY + $padding);

$cropW = $maxX - $minX + 1;
$cropH = $maxY - $minY + 1;

echo "Original: {$width}x{$height}\n";
echo "Cropped: {$cropW}x{$cropH} (from {$minX},{$minY})\n";

// Create cropped image
$cropped = imagecreatetruecolor($cropW, $cropH);
imagealphablending($cropped, false);
imagesavealpha($cropped, true);
$transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
imagefilledrectangle($cropped, 0, 0, $cropW, $cropH, $transparent);

imagecopy($cropped, $img, 0, 0, $minX, $minY, $cropW, $cropH);

imagepng($cropped, $dstPath, 9);
echo "Saved to: $dstPath\n";

imagedestroy($img);
imagedestroy($cropped);
