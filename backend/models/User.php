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

    //Vérifier les règles de validation pour les entrées utilisateur.
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
        try {
            $to = $email;
            $subject = "Verify your Camagru account";
            $url = "http://localhost:8080/verify.php?token=" . $token;
            
            $message = "Hello,\n\n";
            $message .= "Thank you for registering at Camagru. Please click the link below to verify your account:\n\n";
            $message .= $url . "\n\n";
            $message .= "If you didn't create this account, please ignore this email.";
            
            $headers = array(
                'From' => 'noreply@camagru.com',
                'Content-Type' => 'text/plain; charset=utf-8'
            );
    
            if (mail($to, $subject, $message, $headers)) {
                //error_log("Verification email sent successfully to: " . $to);
                return true;
            } else {
                //error_log("Failed to send verification email to: " . $to);
                return false;
            }
        } catch (Exception $e) {
            //error_log("Error sending verification email: " . $e->getMessage());
            return false;
        }
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

public function updateNotificationSettings($userId, $notify) {
    try {
        $query = "UPDATE " . $this->table . " 
                SET notifications_enabled = :notify 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":notify", $notify, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $userId);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        //error_log("Error updating notification settings: " . $e->getMessage());
        return false;
    }
}


public function getUserById($userId) {
    try {
        $query = "SELECT * FROM " . $this->table . " 
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        //error_log("Error in getUserById: " . $e->getMessage());
        return null;
    }
}

public function sendCommentNotification($email, $imagePath) {
    try {
        //error_log("Attempting to send notification");
        //error_log("Email: " . $email);
        //error_log("Image path: " . $imagePath);
        
        $to = $email;
        $subject = "New Comment on Your Camagru Photo";
        
        $message = "Hello,\n\n";
        $message .= "Someone commented on your photo.\n";
        $message .= "View the photo here: http://localhost:8080" . $imagePath . "\n\n";
        $message .= "Best regards,\nCamagru Team";
        
        //error_log("Full message: " . $message);
        
        $headers = array(
            'From' => 'noreply@camagru.com',
            'Content-Type' => 'text/plain; charset=utf-8'
        );

        $mailResult = mail($to, $subject, $message, $headers);
        //error_log("Mail function returned: " . ($mailResult ? "true" : "false"));
        return $mailResult;
    } catch (Exception $e) {
        //error_log("Error sending comment notification: " . $e->getMessage());
        return false;
    }
}

//Initie une demande de réinitialisation de mot de passe.
public function initiatePasswordReset($email) {
    try {
        //error_log("Début de initiatePasswordReset pour : " . $email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            //error_log("Email invalide");
            return ['error' => 'Adresse email invalide'];
        }

        $query = "SELECT id FROM " . $this->table . " WHERE email = :email AND is_verified = true";
        //error_log("Requête SQL : " . $query);
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        //error_log("Nombre de résultats : " . $stmt->rowCount());

        if ($stmt->rowCount() === 0) {
            //error_log("Aucun compte vérifié trouvé pour cet email");
            return ['error' => 'Aucun compte vérifié trouvé avec cette adresse email'];
        }

        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store reset token in database
        $query = "UPDATE " . $this->table . " 
                SET reset_token = :token, reset_token_expiry = :expiry 
                WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $reset_token);
        $stmt->bindParam(':expiry', $token_expiry);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            $this->sendPasswordResetEmail($email, $reset_token);
            return [
                'success' => true,
                'message' => 'Password reset instructions have been sent to your email'
            ];
        }

        return ['error' => 'Unable to process password reset request'];
    } catch (Exception $e) {
        //error_log("Password reset initiation error: " . $e->getMessage());
        return ['error' => 'An error occurred during password reset initiation'];
    }
}

private function sendPasswordResetEmail($email, $token) {
    try {
        $to = $email;
        $subject = "Reset Your Camagru Password";
        $url = "http://localhost:8080/reset-password.php?token=" . $token;
        
        $message = "Hello,\n\n";
        $message .= "You have requested to reset your password for your Camagru account.\n\n";
        $message .= "Please click the link below to reset your password:\n\n";
        $message .= $url . "\n\n";
        $message .= "This link will expire in 1 hour.\n\n";
        $message .= "If you didn't request this password reset, please ignore this email.";
        
        $headers = array(
            'From' => 'noreply@camagru.com',
            'Content-Type' => 'text/plain; charset=utf-8'
        );

        if (mail($to, $subject, $message, $headers)) {
            //error_log("Password reset email sent successfully to: " . $to);
            return true;
        } else {
            //error_log("Failed to send password reset email to: " . $to);
            return false;
        }
    } catch (Exception $e) {
        //error_log("Error sending password reset email: " . $e->getMessage());
        return false;
    }
}

public function validateResetToken($token) {
    try {
        $query = "SELECT id FROM " . $this->table . " 
                WHERE reset_token = :token 
                AND reset_token_expiry > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        //error_log("Token validation error: " . $e->getMessage());
        return false;
    }
}

public function resetPassword($token, $new_password) {
    try {
        if (strlen($new_password) < 8) {
            return ['error' => 'Password must be at least 8 characters long'];
        }

        $query = "SELECT id FROM " . $this->table . " 
                WHERE reset_token = :token 
                AND reset_token_expiry > NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return ['error' => 'Invalid or expired reset token'];
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE " . $this->table . " 
                SET password = :password, 
                    reset_token = NULL, 
                    reset_token_expiry = NULL 
                WHERE reset_token = :token";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':token', $token);
        
        if ($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Your password has been successfully reset'
            ];
        }

        return ['error' => 'Unable to reset password'];
    } catch (Exception $e) {
        //error_log("Password reset error: " . $e->getMessage());
        return ['error' => 'An error occurred while resetting password'];
    }
}
}

