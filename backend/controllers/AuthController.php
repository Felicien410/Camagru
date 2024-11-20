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
}