<?php
class GalleryController {
    private $image;
    private $like;
    private $imagesPerPage = 5;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->image = new Image($db);
        $this->like = new Like($db); // Ajout du modèle Like
    }

    public function index() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $offset = ($page - 1) * $this->imagesPerPage;
            
            $images = $this->image->getAllImages($this->imagesPerPage, $offset);
            
            // Ajouter les informations de like
            foreach ($images as &$image) {
                $image['like_count'] = $this->like->getLikeCount($image['id']);
                $image['user_has_liked'] = isset($_SESSION['user']) ? 
                    $this->like->hasUserLiked($_SESSION['user']['id'], $image['id']) : false;
            }
            
            $totalImages = $this->image->getTotalImages();
            $totalPages = ceil($totalImages / $this->imagesPerPage);

            require_once __DIR__ . '/../views/gallery.php';
        } catch (Exception $e) {
            error_log("Error in gallery: " . $e->getMessage());
            header('Location: /404.php');
        }
    }
}