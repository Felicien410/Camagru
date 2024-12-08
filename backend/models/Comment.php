<?php
class Comment {
    private $conn;
    private $table = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $imageId, $content) {
        try {
            // Validation et nettoyage des entrées
            $userId = filter_var($userId, FILTER_VALIDATE_INT);
            $imageId = filter_var($imageId, FILTER_VALIDATE_INT);
            $content = htmlspecialchars(strip_tags($content), ENT_QUOTES, 'UTF-8');
    
            if (!$userId || !$imageId || empty($content)) {
                error_log("Invalid input parameters");
                return false;
            }
    
            // Vérifier la longueur du contenu
            if (strlen($content) > 500) { // ajustez la limite selon vos besoins
                error_log("Comment content too long");
                return false;
            }
    
            error_log("Attempting to create comment with userId: $userId, imageId: $imageId");
    
            $this->conn->beginTransaction();
            
            try {
                // Vérifier si l'image existe
                $checkImage = "SELECT id FROM images WHERE id = :image_id";
                $stmt = $this->conn->prepare($checkImage);
                $stmt->bindParam(":image_id", $imageId, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Image not found: $imageId");
                }
    
                // Vérifier si l'utilisateur existe
                $checkUser = "SELECT id FROM users WHERE id = :user_id";
                $stmt = $this->conn->prepare($checkUser);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() === 0) {
                    throw new Exception("User not found: $userId");
                }
    
                $query = "INSERT INTO " . $this->table . " 
                         (user_id, image_id, content) 
                         VALUES (:user_id, :image_id, :content)";
    
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->bindParam(":image_id", $imageId, PDO::PARAM_INT);
                $stmt->bindParam(":content", $content, PDO::PARAM_STR);
    
                $result = $stmt->execute();
                $this->conn->commit();
                
                error_log("Comment creation result: " . ($result ? "success" : "failed"));
                return $result;
                
            } catch (Exception $e) {
                $this->conn->rollBack();
                error_log("Error in transaction: " . $e->getMessage());
                return false;
            }
    
        } catch (PDOException $e) {
            error_log("Error creating comment: " . $e->getMessage());
            return false;
        }
    }
    public function getImageComments($imageId) {
        try {
            error_log("Getting comments for image: $imageId");
            
            $query = "SELECT c.*, u.username 
                     FROM " . $this->table . " c
                     JOIN users u ON c.user_id = u.id
                     WHERE c.image_id = :image_id
                     ORDER BY c.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":image_id", $imageId);
            $stmt->execute();

            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Found " . count($comments) . " comments");
            return $comments;

        } catch (PDOException $e) {
            error_log("Error getting comments: " . $e->getMessage());
            return [];
        }
    }
}