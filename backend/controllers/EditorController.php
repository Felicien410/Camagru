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
            // Créer l'image depuis les données reçues
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['image']));
            
            // Sauvegarder directement l'image
            $fileName = uniqid() . '.png';
            $filePath = __DIR__ . '/../public/uploads/' . $fileName;
            
            if (!file_put_contents($filePath, $imageData)) {
                throw new Exception('Failed to save image');
            }
            
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
            $limit = 12; // Limite à 12 photos
            $photos = $this->image->getUserImages($_SESSION['user']['id'], $limit);
            echo json_encode($photos);
        } catch (Exception $e) {
            error_log("Error getting photos: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    public function deletePhoto() {
        if (!isset($_SESSION['user'])) {
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }
    
        $data = json_decode(file_get_contents('php://input'), true);
    
        if (!isset($data['imageId'])) {
            echo json_encode(['error' => 'Image ID required']);
            exit;
        }
    
        if ($this->image->deleteImage($data['imageId'], $_SESSION['user']['id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to delete image']);
        }
    }
}