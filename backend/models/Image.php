<?php
class Image {
    private $conn;
    private $table = "images";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAllImages($limit = 5, $offset = 0) {
        $query = "SELECT i.*, u.username,
                  (SELECT COUNT(*) FROM comments WHERE image_id = i.id) as comment_count,
                  (SELECT COUNT(*) FROM likes WHERE image_id = i.id) as like_count
                  FROM " . $this->table . " i
                  JOIN users u ON i.user_id = u.id
                  ORDER BY i.created_at DESC
                  LIMIT :limit OFFSET :offset";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }


    
    public function create($userId, $imagePath, $description = '') {
        try {
            $query = "INSERT INTO " . $this->table . " 
                    (user_id, image_path, description) 
                    VALUES (:user_id, :image_path, :description)";
                    
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":image_path", $imagePath);
            $stmt->bindParam(":description", $description);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in Image::create: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserImages($userId, $limit = 12) {
        try {
            $query = "SELECT * FROM " . $this->table . " 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC 
                    LIMIT :limit";
                    
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database error in Image::getUserImages: " . $e->getMessage());
            return [];
        }
    }
    
    public function deleteImage($imageId, $userId) {
        try {
            // Vérifier que l'utilisateur est propriétaire de l'image
            $query = "DELETE FROM " . $this->table . " 
                    WHERE id = :id AND user_id = :user_id";
                    
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $imageId);
            $stmt->bindParam(":user_id", $userId);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Database error in Image::deleteImage: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTotalImages() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
}