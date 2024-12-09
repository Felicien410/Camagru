<?php
// Création du sticker avec le logo
function createLogoSticker($logoPath) {
    // Vérifier que le fichier existe
    if (!file_exists($logoPath)) {
        throw new Exception('Logo file not found');
    }

    // Charger le logo
    $logo = imagecreatefromjpeg($logoPath);
    if (!$logo) {
        throw new Exception('Failed to load logo');
    }

    // Obtenir les dimensions originales
    $originalWidth = imagesx($logo);
    $originalHeight = imagesy($logo);

    // Définir la taille souhaitée pour le sticker (petit)
    $newWidth = 100; // Ajustez selon vos besoins
    $newHeight = ($originalHeight * $newWidth) / $originalWidth;

    // Créer une nouvelle image avec transparence
    $sticker = imagecreatetruecolor($newWidth, $newHeight);
    
    // Activer la transparence
    imagealphablending($sticker, true);
    imagesavealpha($sticker, true);
    
    // Allouer la transparence
    $transparent = imagecolorallocatealpha($sticker, 0, 0, 0, 127);
    imagefill($sticker, 0, 0, $transparent);

    // Redimensionner le logo
    imagecopyresampled(
        $sticker, $logo,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $originalWidth, $originalHeight
    );

    // Définir le chemin de sortie
    $outputPath = __DIR__ . '/public/assets/stickers/logo_sticker.png';
    
    // Sauvegarder le sticker
    imagepng($sticker, $outputPath);
    
    // Libérer la mémoire
    imagedestroy($logo);
    imagedestroy($sticker);
    
    return $outputPath;
}

try {
    $logoPath = '/home/fcatteau/Documents/Camagru/backend/public/images/logo.jpg';
    $stickerPath = createLogoSticker($logoPath);
    echo "Sticker créé avec succès : " . $stickerPath;
} catch (Exception $e) {
    echo "Erreur lors de la création du sticker : " . $e->getMessage();
}
?>