<?php
class Database {
   private $host = "db";
   private $db_name = "camagru";
   private $username = "root";
   private $password = "root"; 
   private $conn;

   public function getConnection() {
       $this->conn = null;
       $retries = 5;
       $delay = 2;

       while ($retries > 0) {
           try {
               $this->conn = new PDO(
                   "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                   $this->username,
                   $this->password
               );
               $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
               $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
               $this->conn->exec("set names utf8mb4");
               return $this->conn;
           } catch(PDOException $e) {
               $retries--;
               if ($retries <= 0) {
                   error_log("Connection error: " . $e->getMessage());
                   echo "Connection error: " . $e->getMessage();
                   return null;
               }
               error_log("Failed to connect, retrying in {$delay} seconds...");
               sleep($delay);
           }
       }
   }
}