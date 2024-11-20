<?php
class EditorController {
    private $image;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->image = new Image($db);
    }

    public function capture() {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
    
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            // Créer l'image depuis la webcam
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['image']));
            $sourceImage = imagecreatefromstring($imageData);
            
            if (!$sourceImage) {
                throw new Exception('Failed to create image from webcam data');
            }

            // Charger le sticker
            $stickerPath = __DIR__ . "/../public/assets/stickers/{$data['sticker']}.png";
            $sticker = imagecreatefrompng($stickerPath);
            
            if (!$sticker) {
                throw new Exception('Failed to load sticker');
            }

            // Activer la transparence
            imagealphablending($sourceImage, true);
            imagesavealpha($sourceImage, true);
            
            // Calculer position du sticker (centré)
            $stickerWidth = imagesx($sticker);
            $stickerHeight = imagesy($sticker);
            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            
            $destX = ($sourceWidth - $stickerWidth) / 2;
            $destY = ($sourceHeight - $stickerHeight) / 2;
            
            // Superposer le sticker avec sa taille originale et centré
            imagecopy(
                $sourceImage, // destination
                $sticker,    // source
                $destX,      // dest x
                $destY,      // dest y
                0,          // src x
                0,          // src y
                $stickerWidth,
                $stickerHeight
            );
            
            // Sauvegarder
            $fileName = uniqid() . '.png';
            $filePath = __DIR__ . '/../public/uploads/' . $fileName;
            
            if (!imagepng($sourceImage, $filePath)) {
                throw new Exception('Failed to save image');
            }
            
            // Nettoyer
            imagedestroy($sourceImage);
            imagedestroy($sticker);
            
            if ($this->image->create($_SESSION['user']['id'], '/public/uploads/' . $fileName)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to save to database');
            }
        } catch (Exception $e) {
            error_log("Error in capture: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function getPhotos() {
        try {
            if (!isset($_SESSION['user'])) {
                throw new Exception('Not authenticated');
            }
            $photos = $this->image->getUserImages($_SESSION['user']['id']);
            echo json_encode($photos);
        } catch (Exception $e) {
            error_log("Error getting photos: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}