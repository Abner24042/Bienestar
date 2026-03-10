<?php
// Script temporal para comprimir la imagen hero
$src = __DIR__ . '/public/assets/img/content/DSC07783.jpg';
$dst = __DIR__ . '/public/assets/img/content/DSC07783_optimized.jpg';

$info = getimagesize($src);
echo "Original: {$info[0]}x{$info[1]}, " . round(filesize($src)/1024) . "KB\n";

$img = imagecreatefromjpeg($src);

// Resize to max 1920px width (good for hero backgrounds)
$maxWidth = 1920;
$origW = imagesx($img);
$origH = imagesy($img);

if ($origW > $maxWidth) {
    $newW = $maxWidth;
    $newH = (int)($origH * ($maxWidth / $origW));
    $resized = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
    imagedestroy($img);
    $img = $resized;
}

// Save with quality 75 (good balance)
imagejpeg($img, $dst, 75);
imagedestroy($img);

$newInfo = getimagesize($dst);
echo "Optimized: {$newInfo[0]}x{$newInfo[1]}, " . round(filesize($dst)/1024) . "KB\n";

// Replace original
rename($dst, $src);
echo "Done! Original replaced.\n";
