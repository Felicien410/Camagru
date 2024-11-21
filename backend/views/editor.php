<?php
if (!isset($_SESSION['user'])) {
    header('Location: /login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Editor - Camagru</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
    .webcam-container {
        width: 640px;
        height: 480px;
        margin: 0 auto;
        margin-bottom: 60px; /* Espace pour les boutons */
        position: relative;
        background: #f0f0f0;
        border: 1px solid #ccc;
    }
    
    #webcam, #previewCanvas {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    #canvas {
        display: none;
    }

    #previewCanvas {
        position: absolute;
        top: 0;
        left: 0;
    }

    .editor-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
    }

    .button-group {
        position: absolute;
        bottom: -50px; /* Position sous le conteneur */
        left: 0;
        right: 0;
        display: flex;
        gap: 10px;
        padding: 10px 0;
        background: white;
    }

    .sticker.selected {
        border: 2px solid blue;
    }

    .sticker {
        cursor: pointer;
        transition: transform 0.2s;
    }

    .sticker:hover {
        transform: scale(1.1);
    }

    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
        background-color: #007bff;
        color: white;
    }

    .btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    #validateUpload {
        background-color: #4CAF50;
    }

    #validateUpload:hover:not(:disabled) {
        background-color: #45a049;
    }

    .preview-container {
        margin-top: 20px;
    }

    .photo-item {
        margin: 10px 0;
    }

    .photo-item img {
        max-width: 200px;
        max-height: 200px;
        object-fit: contain;
    }

    /* Style pour l'input file */
    input[type="file"] {
        padding: 6px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>
</head>
<body>
    <div class="container">
        <h1>Photo Editor</h1>
        
        <div class="editor-container">
            <div class="webcam-container">
                <video id="webcam" autoplay playsinline></video>
                <canvas id="previewCanvas"></canvas>
                <canvas id="canvas" style="display: none;"></canvas>
                <div class="button-group">
                    <button id="startCamera" class="btn">Start Camera</button>
                    <button id="capture" class="btn" disabled>Take Photo</button>
                    <input type="file" id="imageUpload" accept="image/*" class="btn">
                    <button id="validateUpload" class="btn" disabled>Validate & Upload</button>
                </div>
            </div>
            
            <div class="stickers-container">
                <h3>Select Sticker</h3>
                <div class="sticker-grid">
                    <img src="/public/assets/stickers/frame1.png" class="sticker" data-sticker="frame1" width="100">
                    <img src="/public/assets/stickers/frame2.png" class="sticker" data-sticker="frame2" width="100">
                </div>
            </div>
        </div>
        
        <div class="preview-container">
            <h3>Previous Photos</h3>
            <div id="photosList"></div>
        </div>
    </div>

    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const previewCanvas = document.getElementById('previewCanvas');
        const startButton = document.getElementById('startCamera');
        const captureButton = document.getElementById('capture');
        const validateButton = document.getElementById('validateUpload');
        const imageUpload = document.getElementById('imageUpload');
        let selectedSticker = null;
        let animationFrame;

        const maxDisplayWidth = 320;
        const maxDisplayHeight = 240;

        function drawPreview() {
            if (!video.videoWidth) return;

            const width = Math.min(video.videoWidth, maxDisplayWidth);
            const height = Math.min(video.videoHeight, maxDisplayHeight);

            previewCanvas.width = width;
            previewCanvas.height = height;
            const ctx = previewCanvas.getContext('2d');

            // Dessiner la vidéo avec les bonnes dimensions
            ctx.drawImage(video, 0, 0, width, height);

            if (selectedSticker) {
                const sticker = document.querySelector(`[data-sticker="${selectedSticker}"]`);
                const x = (width - sticker.width) / 2;
                const y = (height - sticker.height) / 2;
                ctx.drawImage(sticker, x, y);
            }

            animationFrame = requestAnimationFrame(drawPreview);
        }

        startButton.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: maxDisplayWidth },
                        height: { ideal: maxDisplayHeight }
                    } 
                });
                video.srcObject = stream;
                await video.play();
                startButton.disabled = true;
                captureButton.disabled = false;
                drawPreview();
            } catch (err) {
                console.error('Error:', err);
                alert('Could not access camera: ' + err.message);
            }
        });

        document.querySelectorAll('.sticker').forEach(sticker => {
            sticker.addEventListener('click', () => {
                document.querySelectorAll('.sticker').forEach(s => s.classList.remove('selected'));
                sticker.classList.add('selected');
                selectedSticker = sticker.dataset.sticker;
            });
        });

        imageUpload.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        validateButton.disabled = false;
        captureButton.disabled = true;

        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
            cancelAnimationFrame(animationFrame);
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                // Dimensions fixes pour le canvas
                previewCanvas.width = 640;
                previewCanvas.height = 480;
                const ctx = previewCanvas.getContext('2d');
                
                // Calculer les dimensions pour centrer l'image
                const scale = Math.min(
                    previewCanvas.width / img.width,
                    previewCanvas.height / img.height
                );
                
                const x = (previewCanvas.width - img.width * scale) / 2;
                const y = (previewCanvas.height - img.height * scale) / 2;
                
                // Effacer le canvas
                ctx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
                
                // Dessiner l'image centrée et mise à l'échelle
                ctx.drawImage(
                    img, 
                    x, y, 
                    img.width * scale,
                    img.height * scale
                );

                function updatePreview() {
                    ctx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
                    ctx.drawImage(
                        img,
                        x, y,
                        img.width * scale,
                        img.height * scale
                    );
                    if (selectedSticker) {
                        const sticker = document.querySelector(`[data-sticker="${selectedSticker}"]`);
                        const stickerX = (previewCanvas.width - sticker.width) / 2;
                        const stickerY = (previewCanvas.height - sticker.height) / 2;
                        ctx.drawImage(sticker, stickerX, stickerY);
                    }
                }

                updatePreview();
                document.querySelectorAll('.sticker').forEach(sticker => {
                    sticker.addEventListener('click', updatePreview);
                });
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

        captureButton.addEventListener('click', async () => {
            if (!selectedSticker) {
                alert('Please select a sticker first');
                return;
            }
            const imageData = previewCanvas.toDataURL('image/png');
            uploadImage(imageData);
        });

        validateButton.addEventListener('click', () => {
            if (!selectedSticker) {
                alert('Please select a sticker first');
                return;
            }
            const imageData = previewCanvas.toDataURL('image/png');
            uploadImage(imageData);
        });

        async function uploadImage(imageData) {
            try {
                const response = await fetch('/editor/capture', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image: imageData,
                        sticker: selectedSticker
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    refreshPhotosList();
                    imageUpload.value = '';
                    validateButton.disabled = true;
                } else {
                    alert('Error saving photo: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error saving photo');
            }
        }

        async function refreshPhotosList() {
            try {
                const response = await fetch('/editor/photos');
                const photos = await response.json();
                const photosList = document.getElementById('photosList');
                photosList.innerHTML = photos.map(photo => `
                    <div class="photo-item">
                        <img src="${photo.image_path}" alt="Photo">
                    </div>
                `).join('');
            } catch (err) {
                console.error('Error refreshing photos:', err);
            }
        }

        window.addEventListener('beforeunload', () => {
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
            }
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
        });

        refreshPhotosList();
    </script>
</body>
</html>