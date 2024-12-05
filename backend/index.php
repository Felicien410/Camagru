<?php
session_start();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Image.php';
require_once __DIR__ . '/models/Like.php';
require_once __DIR__ . '/models/Comment.php';  // Ajout du model Comment
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/GalleryController.php';
require_once __DIR__ . '/controllers/LikeController.php';
require_once __DIR__ . '/controllers/CommentController.php';
require_once __DIR__ . '/controllers/EditorController.php';

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$controller = new AuthController();
$galleryController = new GalleryController();

switch ($request) {
    case '/':
        require __DIR__ . '/views/home.php';
        break;
        
    case '/forgot-password':
    case '/forgot-password.php':
    case '/auth/forgot-password':
        $controller->forgotPassword();
        break;
            
    case '/reset-password.php':
    case '/auth/reset-password':
        $controller->resetPassword();
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
        $controller->showProfile();  // Au lieu de require __DIR__ . '/views/profile.php'
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

    case '/gallery':
        $galleryController->index();
        break;

    case (preg_match('/^\/verify\.php\?token=/', $request) ? true : false):
        $controller->verify();
        break;
    
    case '/like/toggle':
        $likeController = new LikeController();
        header('Content-Type: application/json');
        $likeController->toggle();
        exit;
        break;

    case '/comments/add':
        $commentController = new CommentController();
        $commentController->addComment();
        break;

    case (preg_match('#^/comments/get/(\d+)$#', $request, $matches) ? true : false):
        $commentController = new CommentController();
        $commentController->getComments($matches[1]);
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/views/404.php';
        break;

}