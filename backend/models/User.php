<?php
class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($username, $email, $password) {
        if (!$this->validateInput($username, $email, $password)) {
            return ['error' => 'Invalid input data'];
        }

        if ($this->emailExists($email)) {
            return ['error' => 'Email already exists'];
        }

        if ($this->usernameExists($username)) {
            return ['error' => 'Username already exists'];
        }

        try {
            $verification_token = bin2hex(random_bytes(32));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO " . $this->table . " 
                    (username, email, password, verification_token) 
                    VALUES (:username, :email, :password, :token)";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":token", $verification_token);
            
            if($stmt->execute()) {
                $this->sendVerificationEmail($email, $verification_token);
                return [
                    'success' => true,
                    'message' => 'Account created successfully. Please check your email for verification.'
                ];
            }
        } catch(PDOException $e) {
            return ['error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function verify($token) {
        $query = "UPDATE " . $this->table . " 
                SET is_verified = true, verification_token = NULL 
                WHERE verification_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $query = "SELECT id, username, password, is_verified 
                FROM " . $this->table . " 
                WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($password, $row['password'])) {
                if (!$row['is_verified']) {
                    return ['error' => 'Please verify your email first'];
                }
                return [
                    'success' => true,
                    'user' => [
                        'id' => $row['id'],
                        'username' => $row['username']
                    ]
                ];
            }
        }
        return ['error' => 'Invalid credentials'];
    }

    private function validateInput($username, $email, $password) {
        if (strlen($username) < 3 || strlen($username) > 50) {
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (strlen($password) < 8) {
            return false;
        }
        return true;
    }

    private function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    private function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        return $stmt->rowCount() > 0;
    }

    private function sendVerificationEmail($email, $token) {
        $to = $email;
        $subject = "Verify your Camagru account";
        $url = "http://localhost:8080/verify.php?token=" . $token;
        
        $message = "Hello,\n\n";
        $message .= "Thank you for registering at Camagru. Please click the link below to verify your account:\n\n";
        $message .= $url . "\n\n";
        $message .= "If you didn't create this account, please ignore this email.";
        
        $headers = "From: noreply@camagru.com";
        
        mail($to, $subject, $message, $headers);
    }

    public function updateProfile($userId, $data) {
        try {
            $query = "UPDATE " . $this->table . " SET ";
            $params = [];
            
            if (!empty($data['username'])) {
                if ($this->usernameExists($data['username']) && 
                    !$this->isCurrentUsername($userId, $data['username'])) {
                    return ['error' => 'Username already exists'];
                }
                $query .= "username = :username, ";
                $params[':username'] = $data['username'];
            }
            
            if (!empty($data['email'])) {
                if ($this->emailExists($data['email']) && 
                    !$this->isCurrentEmail($userId, $data['email'])) {
                    return ['error' => 'Email already exists'];
                }
                $query .= "email = :email, ";
                $params[':email'] = $data['email'];
            }
            
            if (!empty($data['password'])) {
                $query .= "password = :password, ";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $query = rtrim($query, ", ");
            $query .= " WHERE id = :id";
            $params[':id'] = $userId;
            
            $stmt = $this->conn->prepare($query);
            if ($stmt->execute($params)) {
                return ['success' => true];
            }
        } catch(PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function isCurrentUsername($userId, $username) {
        $query = "SELECT username FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row['username'] === $username;
    }

    private function isCurrentEmail($userId, $email) {
        $query = "SELECT email FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row['email'] === $email;
}
}