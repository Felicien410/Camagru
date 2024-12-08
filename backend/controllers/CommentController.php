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
        // Vérification d'authentification
        if (!isset($_SESSION['user'])) {
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        // Récupération et validation des données
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("Received data: " . json_encode($data));

        if (!isset($data['imageId']) || !isset($data['content']) || empty(trim($data['content']))) {
            echo json_encode(['error' => 'Missing required data']);
            exit;
        }

        // Récupération des infos de l'image
        $imageInfo = $this->image->getImageById($data['imageId']);
        if (!$imageInfo) {
            echo json_encode(['error' => 'Image not found']);
            exit;
        }

        // Création du commentaire
        $result = $this->comment->create(
            $_SESSION['user']['id'],
            $data['imageId'],
            htmlspecialchars(trim($data['content']))
        );

        if ($result) {
            // Gestion des notifications
            if ($imageInfo['user_id'] != $_SESSION['user']['id']) {
                error_log("Different users - checking notifications");
                $imageOwner = $this->user->getUserById($imageInfo['user_id']);
                error_log("Image owner: " . json_encode($imageOwner));

                if ($imageOwner && isset($imageOwner['notifications_enabled'])) {
                    error_log("Notifications status: " . ($imageOwner['notifications_enabled'] ? 'enabled' : 'disabled'));
                    
                    if ($imageOwner['notifications_enabled']) {
                        try {
                            error_log("Attempting to send notification to " . $imageOwner['email']);
                            error_log("For image path: " . $imageInfo['image_path']);
                            
                            $notificationResult = $this->user->sendCommentNotification(
                                $imageOwner['email'],
                                $imageInfo['image_path']
                            );
                            error_log("Notification send result: " . ($notificationResult ? 'success' : 'failed'));
                        } catch (Exception $e) {
                            error_log("Notification error: " . $e->getMessage());
                        }
                    } else {
                        error_log("User has notifications disabled");
                    }
                } else {
                    error_log("User preferences not found");
                }
            } else {
                error_log("Self-comment - no notification needed");
            }

            // Retour des commentaires mis à jour
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
        echo json_encode(['error' => 'Server error']);
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