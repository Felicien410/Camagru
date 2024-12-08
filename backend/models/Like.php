<?php
class Like {
    private $conn;
    private $table = "likes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function toggleLike($userId, $imageId) {
        try {
            // Validation des entrées
            $userId = filter_var($userId, FILTER_VALIDATE_INT);
            $imageId = filter_var($imageId, FILTER_VALIDATE_INT);
    
            if (!$userId || !$imageId) {
                error_log("Invalid user_id or image_id in toggleLike");
                return false;
            }
    
            $this->conn->beginTransaction();
    
            try {
                // Vérifier si l'image existe
                $checkImage = "SELECT id FROM images WHERE id = :image_id";
                $stmt = $this->conn->prepare($checkImage);
                $stmt->bindParam(":image_id", $imageId, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Image not found");
                }
    
                // Vérifier si le like existe déjà
                $query = "SELECT id FROM " . $this->table . 
                        " WHERE user_id = :user_id AND image_id = :image_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->bindParam(":image_id", $imageId, PDO::PARAM_INT);
                $stmt->execute();
    
                if ($stmt->rowCount() > 0) {
                    $query = "DELETE FROM " . $this->table . 
                            " WHERE user_id = :user_id AND image_id = :image_id";
                    $status = 'unliked';
                } else {
                    $query = "INSERT INTO " . $this->table . 
                            " (user_id, image_id) VALUES (:user_id, :image_id)";
                    $status = 'liked';
                }
    
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $userId, PDO::PARAM_INT);
                $stmt->bindParam(":image_id", $imageId, PDO::PARAM_INT);
                
                $result = $stmt->execute();
                $this->conn->commit();
                
                return $result ? ['status' => $status] : false;
    
            } catch (Exception $e) {
                $this->conn->rollBack();
                error_log("Error in transaction: " . $e->getMessage());
                return false;
            }
    
        } catch (PDOException $e) {
            error_log("Database error in toggleLike: " . $e->getMessage());
            return false;
        }
    }

    public function getLikeCount($imageId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . 
                " WHERE image_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$imageId]);
        
        return $stmt->fetch()['count'];
    }

    public function hasUserLiked($userId, $imageId) {
        $query = "SELECT id FROM " . $this->table . 
                " WHERE user_id = ? AND image_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId, $imageId]);
        
        return $stmt->rowCount() > 0;
    }
}