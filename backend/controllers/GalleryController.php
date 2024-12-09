<?php

class GalleryController {
    private $image;
    private $like;
    private $imagesPerPage = 5;

    public function __construct() {
        $database = new Database();
        $db = $database->getConnection();
        $this->image = new Image($db);
        $this->like = new Like($db);
    }

    public function index() {
        try {
            $page = 1;
            $images = $this->loadImages($page);
            $totalImages = $this->image->getTotalImages();
            $hasMore = $totalImages > $this->imagesPerPage;
            require_once __DIR__ . '/../views/gallery.php';
        } catch (Exception $e) {
            error_log("Error in gallery: " . $e->getMessage());
            header('Location: /404.php');
        }
    }

    public function loadMoreImages() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            error_log("Loading page: " . $page);
            
            $images = $this->loadImages($page);
            $totalImages = $this->image->getTotalImages();
            $hasMore = ($page * $this->imagesPerPage) < $totalImages;
            
            error_log("Total images: " . $totalImages);
            error_log("Current offset: " . (($page - 1) * $this->imagesPerPage));
            error_log("Has more: " . ($hasMore ? "true" : "false"));
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'images' => $images,
                'hasMore' => $hasMore,
                'totalImages' => $totalImages,
                'currentPage' => $page,
                'debug' => [
                    'page' => $page,
                    'offset' => ($page - 1) * $this->imagesPerPage,
                    'limit' => $this->imagesPerPage,
                    'loadedCount' => count($images)
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in loadMoreImages: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function loadImages($page) {
        $offset = ($page - 1) * $this->imagesPerPage;
        error_log("Loading images with offset: " . $offset . " and limit: " . $this->imagesPerPage);
        
        $images = $this->image->getAllImages($this->imagesPerPage, $offset);
        error_log("Loaded " . count($images) . " images");
        
        foreach ($images as &$image) {
            $image['like_count'] = $this->like->getLikeCount($image['id']);
            $image['user_has_liked'] = isset($_SESSION['user']) ? 
                $this->like->hasUserLiked($_SESSION['user']['id'], $image['id']) : false;
            $image['created_at_formatted'] = date('F j, Y', strtotime($image['created_at']));
            $image['comment_count'] = isset($image['comment_count']) ? $image['comment_count'] : 0;
        }
        
        return $images;
    }
}