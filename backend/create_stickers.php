<?php
// Sticker 1 - Cadre bleu
$img1 = imagecreatetruecolor(200, 200);
$blue = imagecolorallocate($img1, 0, 0, 255);
$transparent = imagecolorallocatealpha($img1, 0, 0, 0, 127);
imagefill($img1, 0, 0, $transparent);
imagerectangle($img1, 0, 0, 199, 199, $blue);
imagepng($img1, __DIR__ . '/public/assets/stickers/frame1.png');

// Sticker 2 - Cercle rouge
$img2 = imagecreatetruecolor(200, 200);
$red = imagecolorallocate($img2, 255, 0, 0);
$transparent = imagecolorallocatealpha($img2, 0, 0, 0, 127);
imagefill($img2, 0, 0, $transparent);
imageellipse($img2, 100, 100, 150, 150, $red);
imagepng($img2, __DIR__ . '/public/assets/stickers/frame2.png');

// Activer la transparence
imagealphablending($img1, true);
imagesavealpha($img1, true);
imagealphablending($img2, true);
imagesavealpha($img2, true);
?>