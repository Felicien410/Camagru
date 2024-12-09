<?php
class Database {
   private $host;
   private $db_name;
   private $username;
   private $password;
   private $conn;

   public function __construct() {
       $this->host = getenv('DB_HOST');
       $this->db_name = getenv('DB_NAME');
       $this->username = getenv('DB_USER');
       $this->password = getenv('DB_PASSWORD');
   }

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