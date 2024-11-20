<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/controllers/AuthController.php';

$request = $_SERVER['REQUEST_URI'];
$controller = new AuthController();

switch ($request) {
    case '/':
        require __DIR__ . '/views/home.php';
        break;
        
    case '/register':
    case '/register.php':
    case '/auth/register':
        $controller->register();
        break;
        
    case '/login':
    case '/login.php':
    case '/auth/login':
        $controller->login();
        break;
        
    case '/logout':
        $controller->logout();
        break;

    case '/dashboard':
        case '/dashboard.php':
            require __DIR__ . '/views/dashboard.php';
            break;
        
    case '/profile':
        require __DIR__ . '/views/profile.php';
        break;
    
    case '/profile/update':
        $controller->updateProfile();
        break;

    case '/editor':
        require __DIR__ . '/views/editor.php';
        break;

    case '/editor/capture':
        $editorController = new EditorController();
        $editorController->capture();
        break;
    
    case '/editor/photos':
        $editorController = new EditorController();
        $editorController->getPhotos();
        break;

    case (preg_match('/^\/verify\.php\?token=/', $request) ? true : false):
        $controller->verify();
        break;

        
    default:
        http_response_code(404);
        require __DIR__ . '/views/404.php';
        break;
}