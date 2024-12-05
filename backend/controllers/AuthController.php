<?php
class AuthController {
    private $user;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->user = new User($db);
    }

    public function register() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return $this->renderView('auth/register');
        }

        $username = htmlspecialchars(strip_tags($_POST['username']));
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $password = $_POST['password'];

        $result = $this->user->create($username, $email, $password);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
            header("Location: /register.php");
            exit();
        }

        $_SESSION['success'] = $result['message'];
        header("Location: /login.php");
        exit();
    }

    public function login() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return $this->renderView('auth/login');
        }

        $email = htmlspecialchars(strip_tags($_POST['email']));
        $password = $_POST['password'];

        $result = $this->user->login($email, $password);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
            header("Location: /login.php");
            exit();
        }

        $_SESSION['user'] = $result['user'];
        header("Location: /dashboard.php");
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: /login.php");
        exit();
    }

    public function verify() {
        $token = htmlspecialchars(strip_tags($_GET['token']));
        
        if ($this->user->verify($token)) {
            $_SESSION['success'] = "Email verified successfully. You can now login.";
        } else {
            $_SESSION['error'] = "Invalid or expired verification token.";
        }
        
        header("Location: /login.php");
        exit();
    }

    private function renderView($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/$view.php";
    }

    public function updateProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'username' => htmlspecialchars(strip_tags($_POST['username'])),
                'email' => !empty($_POST['email']) ? htmlspecialchars(strip_tags($_POST['email'])) : null,
                'password' => !empty($_POST['password']) ? $_POST['password'] : null
            ];
    
            $result = $this->user->updateProfile($_SESSION['user']['id'], $data);
    
            if (isset($result['error'])) {
                $_SESSION['error'] = $result['error'];
            } else {
                $_SESSION['success'] = 'Profile updated successfully';
                $_SESSION['user']['username'] = $data['username'];
            }
        }
        
        header('Location: /profile');
        exit();
    }

    public function forgotPassword() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return $this->renderView('auth/forgot-password');
        }
    
        try {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            error_log("Tentative de réinitialisation de mot de passe pour l'email : " . $email);
    
            // Vérification de l'email
            if (empty($email)) {
                error_log("Email vide");
                $_SESSION['error'] = "L'email est requis";
                header("Location: /forgot-password.php");
                exit();
            }
    
            // Vérification de la connexion à la base de données
            if (!$this->user) {
                error_log("Pas de connexion à la base de données");
                $_SESSION['error'] = "Erreur de connexion à la base de données";
                header("Location: /forgot-password.php");
                exit();
            }
    
            $result = $this->user->initiatePasswordReset($email);
            error_log("Résultat de initiatePasswordReset : " . print_r($result, true));
    
            if (isset($result['error'])) {
                error_log("Erreur lors de la réinitialisation : " . $result['error']);
                $_SESSION['error'] = $result['error'];
                header("Location: /forgot-password.php");
                exit();
            }
    
            $_SESSION['success'] = $result['message'];
            header("Location: /login.php");
            exit();
    
        } catch (Exception $e) {
            error_log("Exception dans forgotPassword : " . $e->getMessage());
            error_log("Stack trace : " . $e->getTraceAsString());
            $_SESSION['error'] = "Une erreur est survenue lors de la réinitialisation du mot de passe";
            header("Location: /forgot-password.php");
            exit();
        }
    }

public function resetPassword() {
    if ($_SERVER["REQUEST_METHOD"] === "GET") {
        $token = htmlspecialchars(strip_tags($_GET['token']));
        
        if (!$this->user->validateResetToken($token)) {
            $_SESSION['error'] = "Invalid or expired reset token";
            header("Location: /login.php");
            exit();
        }
        
        return $this->renderView('auth/reset-password', ['token' => $token]);
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $token = htmlspecialchars(strip_tags($_POST['token']));
        $password = $_POST['password'];
        
        $result = $this->user->resetPassword($token, $password);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
            header("Location: /reset-password.php?token=" . $token);
            exit();
        }

        $_SESSION['success'] = $result['message'];
        header("Location: /login.php");
        exit();
    }
}

public function showProfile() {
    if (!isset($_SESSION['user'])) {
        header('Location: /login');
        exit();
    }

    // Récupérer les informations complètes de l'utilisateur
    $userData = $this->user->getUserById($_SESSION['user']['id']);
    
    return $this->renderView('profile', ['user' => $userData]);
}
}