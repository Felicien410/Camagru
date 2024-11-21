<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Camagru</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .gallery-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .gallery-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .image-info {
            padding: 15px;
            border-top: 1px solid #eee;
        }

        .image-info .username {
            color: #666;
            font-size: 0.9em;
        }

        .image-date {
            color: #999;
            font-size: 0.8em;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .pagination a:hover:not(.active) {
            background-color: #f5f5f5;
        }
        .like-section {
        margin-top: 10px;
        }
        
        .like-btn {
        background: none;
        border: 2px solid #ff4757;
        cursor: pointer;
        padding: 8px 15px;
        border-radius: 20px;
        transition: all 0.2s;
        font-size: 16px;
    }

    .like-btn:hover {
        transform: scale(1.1);
        background-color: #ffe0e3;
    }

    .like-btn.liked {
        background-color: #ff4757;
        color: white;
    }

    .like-count {
        margin-left: 5px;
        font-weight: bold;
    }
    .comments-section {
    margin-top: 10px;
    border-top: 1px solid #eee;
    padding-top: 10px;
}

.comment-toggle-btn {
    background: none;
    border: 2px solid #3498db;
    padding: 5px 10px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s;
}

.comment-toggle-btn:hover {
    background-color: #3498db;
    color: white;
}

.comments-container {
    margin-top: 10px;
}

.comments-list {
    max-height: 200px;
    overflow-y: auto;
    margin-bottom: 10px;
}

.comment-item {
    padding: 8px;
    border-bottom: 1px solid #eee;
}

.comment-form {
    display: flex;
    gap: 10px;
}

.comment-form input {
    flex: 1;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.comment-form button {
    padding: 8px 16px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
    </style>
</head>
<body>
    <div class="gallery-container">
        <h1>Public Gallery</h1>
        
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery image">
                    <div class="image-info">
                    <div class="username">By <?php echo htmlspecialchars($image['username']); ?></div>
                    <div class="image-date"><?php echo date('F j, Y', strtotime($image['created_at'])); ?></div>
                    <div class="like-section">
                        <button 
                            class="like-btn <?php echo $image['user_has_liked'] ? 'liked' : ''; ?>"
                            data-image-id="<?php echo $image['id']; ?>"
                        >
                            ❤️ <span class="like-count"><?php echo $image['like_count']; ?></span>
                        </button>
                        <div class="comments-section">
                    <button class="comment-toggle-btn" onclick="toggleComments(<?php echo $image['id']; ?>)">
                        💬 <span class="comment-count"><?php echo $image['comment_count']; ?></span>
                    </button>
                    
                    <div id="comments-<?php echo $image['id']; ?>" class="comments-container" style="display: none;">
                        <div class="comments-list"></div>
                        <form class="comment-form" onsubmit="addComment(event, <?php echo $image['id']; ?>)">
                            <input type="text" placeholder="Add a comment..." required>
                            <button type="submit">Send</button>
                        </form>
                    </div>
                </div>
                    </div>
                </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="<?php echo $page === $i ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <script>
document.querySelectorAll('.like-btn').forEach(button => {
    button.addEventListener('click', async () => {
        console.log('Like button clicked');
        const imageId = button.dataset.imageId;
        console.log('Image ID:', imageId);
        
        try {
            console.log('Sending request to /like/toggle');
            const response = await fetch('/like/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ imageId: imageId })
            });

            const data = await response.json();
            console.log('Response:', data);
            
            if (data.success) {
                console.log('Like successful');
                button.classList.toggle('liked');
                const countSpan = button.querySelector('.like-count');
                countSpan.textContent = data.likeCount;
            } else {
                console.error('Error:', data.error);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});
</script>
<script>
async function toggleComments(imageId) {
    const container = document.getElementById(`comments-${imageId}`);
    if (container.style.display === 'none') {
        container.style.display = 'block';
        await loadComments(imageId);
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
            // Mettre à jour le compteur de commentaires
            const counter = form.closest('.comments-section')
                               .querySelector('.comment-count');
            counter.textContent = parseInt(counter.textContent) + 1;
        } else {
            alert(data.error || 'Failed to add comment');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
    }
}
</script>
</body>
</html>