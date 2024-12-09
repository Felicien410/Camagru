<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Camagru</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/gallery.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
        }

        .gallery-grid {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php
    function generateImageHtml($image) {
        return sprintf('
            <div class="gallery-item">
                <img src="%s" alt="Gallery image">
                <div class="image-info">
                    <div class="username">By %s</div>
                    <div class="image-date">%s</div>
                    <div class="like-section">
                        <button class="like-btn %s"
                                data-image-id="%s">
                            ❤️ <span class="like-count">%s</span>
                        </button>
                        <div class="comments-section">
                            <button class="comment-toggle-btn" onclick="toggleComments(%s)">
                                💬 <span class="comment-count">%s</span>
                            </button>
                            
                            <div id="comments-%s" class="comments-container" style="display: none;">
                                <div class="comments-list"></div>
                                <form class="comment-form" onsubmit="addComment(event, %s)">
                                    <input type="text" placeholder="Add a comment..." required>
                                    <button type="submit">Send</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ',
            htmlspecialchars($image['image_path']),
            htmlspecialchars($image['username']),
            htmlspecialchars($image['created_at_formatted']),
            $image['user_has_liked'] ? 'liked' : '',
            htmlspecialchars($image['id']),
            htmlspecialchars($image['like_count']),
            htmlspecialchars($image['id']),
            htmlspecialchars($image['comment_count']),
            htmlspecialchars($image['id']),
            htmlspecialchars($image['id'])
        );
    }
    ?>

    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <h1>Public Gallery</h1>
        
        <div class="gallery-grid" id="gallery-container">
            <?php foreach ($images as $image): ?>
                <?php echo generateImageHtml($image); ?>
            <?php endforeach; ?>
        </div>
        
        <div id="loading-spinner" style="display: none;" class="text-center p-4">
            Loading more images...
        </div>
    </div>

    <?php require_once __DIR__ . '/partials/footer.php'; ?>

    <script>
    // Variables globales pour le chargement infini
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let loadingDebounce = null;

    // Gestion des commentaires (définir avant l'utilisation dans le HTML)
    function toggleComments(imageId) {
        const container = document.getElementById(`comments-${imageId}`);
        if (container.style.display === 'none') {
            container.style.display = 'block';
            loadComments(imageId);
        } else {
            container.style.display = 'none';
        }
    }

    async function loadComments(imageId) {
        try {
            const response = await fetch(`/comments/get/${imageId}`);
            const data = await response.json();
            
            if (data.success) {
                const container = document.querySelector(`#comments-${imageId} .comments-list`);
                container.innerHTML = data.comments.map(comment => `
                    <div class="comment-item">
                        <strong>${comment.username}</strong>: ${comment.content}
                        <small>${new Date(comment.created_at).toLocaleString()}</small>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading comments:', error);
        }
    }

    async function addComment(event, imageId) {
        event.preventDefault();
        const form = event.target;
        const input = form.querySelector('input');
        const content = input.value.trim();

        try {
            const response = await fetch('/comments/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ imageId, content })
            });

            const data = await response.json();

            if (data.success) {
                input.value = '';
                await loadComments(imageId);
                const counter = form.closest('.comments-section')
                                   .querySelector('.comment-count');
                counter.textContent = parseInt(counter.textContent) + 1;
            } else {
                throw new Error(data.error || 'Failed to add comment');
            }
        } catch (error) {
            console.error('Error adding comment:', error);
            alert(error.message || 'Failed to add comment. Please try again.');
        }
    }

    // Gestion des likes
    async function handleLikeClick(event) {
        const button = event.currentTarget;
        const imageId = button.dataset.imageId;
        
        try {
            const response = await fetch('/like/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ imageId: imageId })
            });

            const data = await response.json();
            
            if (data.success) {
                button.classList.toggle('liked');
                const countSpan = button.querySelector('.like-count');
                countSpan.textContent = data.likeCount;
            } else {
                throw new Error(data.error || 'Failed to toggle like');
            }
        } catch (error) {
            console.error('Error toggling like:', error);
            alert('Failed to update like. Please try again.');
        }
    }

    function initializeLikeButtons() {
        document.querySelectorAll('.like-btn:not([data-initialized])').forEach(button => {
            button.setAttribute('data-initialized', 'true');
            button.addEventListener('click', handleLikeClick);
        });
    }

    // Gestion du chargement infini
    function generateImageHtml(image) {
        return `
            <div class="gallery-item">
                <img src="${image.image_path}" alt="Gallery image">
                <div class="image-info">
                    <div class="username">By ${image.username}</div>
                    <div class="image-date">${image.created_at_formatted}</div>
                    <div class="like-section">
                        <button class="like-btn ${image.user_has_liked ? 'liked' : ''}"
                                data-image-id="${image.id}">
                            ❤️ <span class="like-count">${image.like_count}</span>
                        </button>
                        <div class="comments-section">
                            <button class="comment-toggle-btn" onclick="toggleComments(${image.id})">
                                💬 <span class="comment-count">${image.comment_count}</span>
                            </button>
                            
                            <div id="comments-${image.id}" class="comments-container" style="display: none;">
                                <div class="comments-list"></div>
                                <form class="comment-form" onsubmit="addComment(event, ${image.id})">
                                    <input type="text" placeholder="Add a comment..." required>
                                    <button type="submit">Send</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async function loadMoreImages() {
        if (isLoading || !hasMore) {
            console.log('Skipping load - isLoading:', isLoading, 'hasMore:', hasMore);
            return;
        }
        
        try {
            isLoading = true;
            document.getElementById('loading-spinner').style.display = 'block';
            console.log('Loading more images, current page:', currentPage);
            
            const response = await fetch(`/gallery/load-more?page=${currentPage + 1}`);
            const data = await response.json();
            console.log('Received data:', data);
            
            if (data.success) {
                if (data.images && data.images.length > 0) {
                    const container = document.getElementById('gallery-container');
                    data.images.forEach(image => {
                        container.insertAdjacentHTML('beforeend', generateImageHtml(image));
                    });
                    
                    initializeLikeButtons();
                    currentPage++;
                    hasMore = data.hasMore;
                    
                    console.log('Updated state:', {
                        currentPage,
                        hasMore,
                        totalImages: data.totalImages,
                        loadedImagesCount: data.images.length
                    });
                } else {
                    hasMore = false;
                    console.log('No more images available');
                }
            }
        } catch (error) {
            console.error('Error loading more images:', error);
        } finally {
            isLoading = false;
            document.getElementById('loading-spinner').style.display = 'none';
        }
    }

    // Initialisation de l'Intersection Observer
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Initializing infinite scroll');
        
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isLoading) {
                    console.log('Loading triggered by intersection');
                    loadMoreImages();
                }
            });
        }, options);

        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            observer.observe(spinner);
            console.log('Observer attached to spinner');
        }

        // Backup scroll event
        window.addEventListener('scroll', () => {
            const scrollPosition = window.innerHeight + window.pageYOffset;
            const totalHeight = document.documentElement.scrollHeight;
            
            if (totalHeight - scrollPosition < 1000 && !isLoading && hasMore) {
                console.log('Loading triggered by scroll');
                loadMoreImages();
            }
        });

        // Initialize initial content
        initializeLikeButtons();
    });
    </script>
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    </body>
</html>