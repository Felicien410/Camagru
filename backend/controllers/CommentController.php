<?php
class CommentController {
    private $comment;
    private $user;
    private $image;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->comment = new Comment($db);
        $this->user = new User($db);
        $this->image = new Image($db);
    }


    public function addComment() {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_SESSION['user'])) {
                error_log("User not authenticated");
                echo json_encode(['error' => 'Not authenticated']);
                exit;
            }
    
            $data = json_decode(file_get_contents('php://input'), true);
            error_log("Received data: " . json_encode($data));
    
            if (!isset($data['imageId']) || !isset($data['content']) || empty(trim($data['content']))) {
                error_log("Missing data in request");
                echo json_encode(['error' => 'Missing required data']);
                exit;
            }
    
            $result = $this->comment->create(
                $_SESSION['user']['id'],
                $data['imageId'],
                htmlspecialchars(trim($data['content']))
            );
    
            if ($result) {
                $comments = $this->comment->getImageComments($data['imageId']);
                echo json_encode([
                    'success' => true,
                    'comments' => $comments
                ]);
            } else {
                error_log("Failed to create comment");
                echo json_encode(['error' => 'Failed to add comment']);
            }
        } catch (Exception $e) {
            error_log("Error in addComment: " . $e->getMessage());
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }

    public function getComments($imageId) {
        header('Content-Type: application/json');
        try {
            $comments = $this->comment->getImageComments($imageId);
            echo json_encode([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (Exception $e) {
            error_log("Error in getComments: " . $e->getMessage());
            echo json_encode(['error' => 'Server error']);
        }
        exit;
    }
}