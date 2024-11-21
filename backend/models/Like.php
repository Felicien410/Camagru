<?php
class Like {
    private $conn;
    private $table = "likes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function toggleLike($userId, $imageId) {
        // Vérifier si le like existe déjà
        $query = "SELECT id FROM " . $this->table . 
                " WHERE user_id = :user_id AND image_id = :image_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":image_id", $imageId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Le like existe, on le supprime
            $query = "DELETE FROM " . $this->table . 
                    " WHERE user_id = :user_id AND image_id = :image_id";
            $status = 'unliked';
        } else {
            // Le like n'existe pas, on l'ajoute
            $query = "INSERT INTO " . $this->table . 
                    " (user_id, image_id) VALUES (:user_id, :image_id)";
            $status = 'liked';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":image_id", $imageId);
        
        return $stmt->execute() ? ['status' => $status] : false;
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