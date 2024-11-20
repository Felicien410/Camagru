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
            max-width: 640px;
            margin: 0 auto;
            position: relative;
        }
        #webcam, #canvas, #previewCanvas {
            width: 100%;
            max-width: 640px;
            margin-bottom: 1rem;
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
        let selectedSticker = null;
        let animationFrame;

        function drawPreview() {
            if (!video.videoWidth) return;

            previewCanvas.width = video.videoWidth;
            previewCanvas.height = video.videoHeight;
            const ctx = previewCanvas.getContext('2d');

            // Dessiner la vidéo
            ctx.drawImage(video, 0, 0);

            // Dessiner le sticker si sélectionné
            if (selectedSticker) {
                const sticker = document.querySelector(`[data-sticker="${selectedSticker}"]`);
                // Position centrée du sticker
                const x = (video.videoWidth - sticker.width) / 2;
                const y = (video.videoHeight - sticker.height) / 2;
                ctx.drawImage(sticker, x, y);
            }

            animationFrame = requestAnimationFrame(drawPreview);
        }

        startButton.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 640 },
                        height: { ideal: 480 }
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

        captureButton.addEventListener('click', async () => {
            if (!selectedSticker) {
                alert('Please select a sticker first');
                return;
            }

            // Utiliser le canvas de prévisualisation pour la capture
            const imageData = previewCanvas.toDataURL('image/png');
            
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
                } else {
                    alert('Error saving photo: ' + (data.error || 'Unknown error'));
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error saving photo');
            }
        });

        async function refreshPhotosList() {
            try {
                const response = await fetch('/editor/photos');
                const photos = await response.json();
                const photosList = document.getElementById('photosList');
                photosList.innerHTML = photos.map(photo => `
                    <div class="photo-item">
                        <img src="${photo.image_path}" alt="Photo" style="max-width: 200px;">
                    </div>
                `).join('');
            } catch (err) {
                console.error('Error refreshing photos:', err);
            }
        }

        // Nettoyage lors de la fermeture
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