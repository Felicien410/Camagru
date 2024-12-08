<?php
class AuthController {
    private $user;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->user = new User($db);
    }

    private function ensureCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    private function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public function register() {
        $this->ensureCsrfToken();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return $this->renderView('auth/register');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid request';
            header("Location: /register");
            exit();
        }

        $username = htmlspecialchars(strip_tags($_POST['username']));
        $email = htmlspecialchars(strip_tags($_POST['email']));
        $password = $_POST['password'];

        $result = $this->user->create($username, $email, $password);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
            header("Location: /register");
            exit();
        }

        $_SESSION['success'] = $result['message'];
        header("Location: /login");
        exit();
    }

    public function login() {
        $this->ensureCsrfToken();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return $this->renderView('auth/login');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid request';
            header("Location: /login");
            exit();
        }

        $email = htmlspecialchars(strip_tags($_POST['email']));
        $password = $_POST['password'];

        $result = $this->user->login($email, $password);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
            header("Location: /login");
            exit();
        }

        $_SESSION['user'] = $result['user'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Nouveau token après connexion
        header("Location: /dashboard");
        exit();
    }

    public function logout() {
        session_destroy();
        header("Location: /login");
        exit();
    }

    public function verify() {
        $token = htmlspecialchars(strip_tags($_GET['token']));
        
        if ($this->user->verify($token)) {
            $_SESSION['success'] = "Email verified successfully. You can now login.";
        } else {
            $_SESSION['error'] = "Invalid or expired verification token.";
        }
        
        header("Location: /login");
        exit();
    }

    public function forgotPassword() {
        $this->ensureCsrfToken();

        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return $this->renderView('auth/forgot-password');
        }

        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid request';
            header("Location: /forgot-password");
            exit();
        }
    
        try {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            error_log("Attempting password reset for email: " . $email);
    
            if (empty($email)) {
                $_SESSION['error'] = "Email is required";
                header("Location: /forgot-password");
                exit();
            }
    
            if (!$this->user) {
                $_SESSION['error'] = "Database connection error";
                header("Location: /forgot-password");
                exit();
            }
    
            $result = $this->user->initiatePasswordReset($email);
    
            if (isset($result['error'])) {
                $_SESSION['error'] = $result['error'];
                header("Location: /forgot-password");
                exit();
            }
    
            $_SESSION['success'] = $result['message'];
            header("Location: /login");
            exit();
    
        } catch (Exception $e) {
            error_log("Exception in forgotPassword: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred during password reset";
            header("Location: /forgot-password");
            exit();
        }
    }

    public function resetPassword() {
        $this->ensureCsrfToken();

        if ($_SERVER["REQUEST_METHOD"] === "GET") {
            $token = htmlspecialchars(strip_tags($_GET['token']));
            
            if (!$this->user->validateResetToken($token)) {
                $_SESSION['error'] = "Invalid or expired reset token";
                header("Location: /login");
                exit();
            }
            
            return $this->renderView('auth/reset-password', ['token' => $token]);
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Invalid request';
                header("Location: /reset-password?token=" . $_POST['token']);
                exit();
            }

            $token = htmlspecialchars(strip_tags($_POST['token']));
            $password = $_POST['password'];
            
            $result = $this->user->resetPassword($token, $password);

            if (isset($result['error'])) {
                $_SESSION['error'] = $result['error'];
                header("Location: /reset-password?token=" . $token);
                exit();
            }

            $_SESSION['success'] = $result['message'];
            header("Location: /login");
            exit();
        }
    }

    public function showProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        $this->ensureCsrfToken();
        $userData = $this->user->getUserById($_SESSION['user']['id']);
        
        return $this->renderView('profile', ['user' => $userData]);
    }

    public function updateProfile() {
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['error'] = 'Invalid request';
                header('Location: /profile');
                exit();
            }

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

    private function renderView($view, $data = []) {
        extract($data);
        require_once __DIR__ . "/../views/$view.php";
    }
}