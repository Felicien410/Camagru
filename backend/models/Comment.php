<?php
class Comment {
    private $conn;
    private $table = "comments";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $imageId, $content) {
        try {
            error_log("Attempting to create comment with userId: $userId, imageId: $imageId");
            
            // Vérifier si l'image existe
            $checkImage = "SELECT id FROM images WHERE id = :image_id";
            $stmt = $this->conn->prepare($checkImage);
            $stmt->bindParam(":image_id", $imageId);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                error_log("Image not found: $imageId");
                return false;
            }

            // Vérifier si l'utilisateur existe
            $checkUser = "SELECT id FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($checkUser);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                error_log("User not found: $userId");
                return false;
            }

            $query = "INSERT INTO " . $this->table . " 
                     (user_id, image_id, content) 
                     VALUES (:user_id, :image_id, :content)";

            error_log("Executing query: " . $query);

            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":image_id", $imageId);
            $stmt->bindParam(":content", $content);

            $result = $stmt->execute();
            error_log("Comment creation result: " . ($result ? "success" : "failed"));
            return $result;

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