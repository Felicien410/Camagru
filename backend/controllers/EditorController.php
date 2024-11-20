<?php
class EditorController {
    private $image;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->image = new Image($db);
    }

    public function capture() {
        error_log("Capture endpoint called");
        
        if (!isset($_SESSION['user'])) {
            error_log("User not authenticated");
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Received data: " . json_encode($data));
        
        if (!isset($data['image']) || !isset($data['sticker'])) {
            error_log("Missing data");
            echo json_encode(['error' => 'Missing data']);
            exit;
        }

        try {
            $uploadsDir = __DIR__ . '/../public/uploads';
            if (!file_exists($uploadsDir)) {
                error_log("Creating uploads directory");
                mkdir($uploadsDir, 0777, true);
            }

            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $data['image']));
            $fileName = uniqid() . '.png';
            $filePath = $uploadsDir . '/' . $fileName;
            
            error_log("Saving to: " . $filePath);
            if (file_put_contents($filePath, $imageData)) {
                error_log("File saved successfully");
            } else {
                error_log("Failed to save file");
            }

            if ($this->image->create($_SESSION['user']['id'], '/uploads/' . $fileName)) {
                error_log("Database record created");
                echo json_encode(['success' => true]);
            } else {
                error_log("Database error");
                echo json_encode(['error' => 'Database error']);
            }
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getPhotos() {
        try {
            if (!isset($_SESSION['user'])) {
                throw new Exception('Not authenticated');
            }
            $photos = $this->image->getUserImages($_SESSION['user']['id']);
            error_log("Retrieved photos: " . json_encode($photos));
            echo json_encode($photos);
        } catch (Exception $e) {
            error_log("Error getting photos: " . $e->getMessage());
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}