<?php
class LikeController {
    private $like;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->like = new Like($db);
    }

    public function toggle() {
        if (!isset($_SESSION['user'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['imageId'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Image ID required']);
            exit;
        }

        $result = $this->like->toggleLike($_SESSION['user']['id'], $data['imageId']);
        
        header('Content-Type: application/json');
        if ($result) {
            $likeCount = $this->like->getLikeCount($data['imageId']);
            echo json_encode([
                'success' => true,
                'status' => $result['status'],
                'likeCount' => $likeCount
            ]);
        } else {
            echo json_encode(['error' => 'Failed to toggle like']);
        }
        exit;
    }
}