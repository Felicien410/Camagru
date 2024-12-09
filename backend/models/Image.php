<?php
class Image {
    private $conn;
    private $table = "images";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    //Récupère une liste d'images avec des données enrichies 
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


    //Ajoute une nouvelle image associée à un utilisateur dans la base.
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
            //error_log("Database error in Image::create: " . $e->getMessage());
            return false;
        }
    }
    
    //Récupère toutes les images pour un utilisateur spécifique.
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
            //error_log("Database error in Image::getUserImages: " . $e->getMessage());
            return [];
        }
    }
    
    //Supprime une image et ses données associées (likes, commentaires).
    public function deleteImage($imageId, $userId) {
        try {
            $this->conn->beginTransaction();
            
            // Supprimer les likes associés
            $queryLikes = "DELETE FROM likes WHERE image_id = :image_id";
            $stmtLikes = $this->conn->prepare($queryLikes);
            $stmtLikes->bindParam(":image_id", $imageId);
            $stmtLikes->execute();
            
            // Supprimer les commentaires associés
            $queryComments = "DELETE FROM comments WHERE image_id = :image_id";
            $stmtComments = $this->conn->prepare($queryComments);
            $stmtComments->bindParam(":image_id", $imageId);
            $stmtComments->execute();
            
            // Supprimer l'image
            $queryImage = "DELETE FROM " . $this->table . " 
                          WHERE id = :id AND user_id = :user_id";
            $stmtImage = $this->conn->prepare($queryImage);
            $stmtImage->bindParam(":id", $imageId);
            $stmtImage->bindParam(":user_id", $userId);
            
            $result = $stmtImage->execute();
            
            $this->conn->commit();
            return $result;
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            //error_log("Database error in Image::deleteImage: " . $e->getMessage());
            return false;
        }
    }
    
    //Retourne le nombre total d'images dans la base.
    public function getTotalImages() {
        $query = "SELECT COUNT(*) FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    //Récupère les détails d'une image spécifique, incluant l'email de son propriétaire.
    public function getImageById($imageId) {
        try {
            //error_log("Getting image with ID: " . $imageId);
            $query = "SELECT i.*, u.email FROM " . $this->table . " i
                      JOIN users u ON i.user_id = u.id
                      WHERE i.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $imageId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            //error_log("Image info found: " . json_encode($result));
            return $result;
        } catch (PDOException $e) {
            //error_log("Error in getImageById: " . $e->getMessage());
            return null;
        }
    }

    //Récupère le chemin d'une image pour un utilisateur donné.
    public function getImagePath($imageId, $userId) {
        try {
            $query = "SELECT image_path FROM " . $this->table . " 
                     WHERE id = :id AND user_id = :user_id";
                     
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $imageId);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['image_path'] : null;
            
        } catch (PDOException $e) {
            //error_log("Database error in Image::getImagePath: " . $e->getMessage());
            return null;
        }
    }
}