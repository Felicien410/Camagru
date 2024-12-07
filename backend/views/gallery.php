<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - Camagru</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/partials/header.php'; ?>

    <div class="container">
        <h1>Public Gallery</h1>
        
        <div class="gallery-grid">
            <?php foreach ($images as $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="Gallery image">
                    <div class="image-info">
                        <div class="username">By <?php echo htmlspecialchars($image['username']); ?></div>
                        <div class="image-date"><?php echo date('F j, Y', strtotime($image['created_at'])); ?></div>
                        <div class="like-section">
                            <button class="like-btn <?php echo $image['user_has_liked'] ? 'liked' : ''; ?>"
                                    data-image-id="<?php echo $image['id']; ?>">
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

    console.log('Attempting to add comment:', {
        imageId,
        content
    });

    try {
        console.log('Sending request to /comments/add');
        const response = await fetch('/comments/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ imageId, content })
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);

        const responseText = await response.text();
        console.log('Raw response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Invalid JSON response: ' + responseText);
        }

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
        console.error('Detailed error:', error);
        alert(error.message || 'Failed to add comment. Please try again.');
    }
}
</script>
</body>
</html>