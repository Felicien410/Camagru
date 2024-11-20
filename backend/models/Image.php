<?php
class Image {
    private $conn;
    private $table = "images";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create($userId, $imagePath, $description = '') {
        $query = "INSERT INTO " . $this->table . " (user_id, image_path, description) 
                 VALUES (:user_id, :image_path, :description)";
                 
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":image_path", $imagePath);
        $stmt->bindParam(":description", $description);
        
        return $stmt->execute();
    }
    
    public function getUserImages($userId) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC";
                 
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}