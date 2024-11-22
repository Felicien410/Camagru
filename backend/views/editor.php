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
            margin-bottom: 60px;
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
            bottom: -50px;
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

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover:not(:disabled) {
            background-color: #c82333;
        }

        #validatePhoto {
            background-color: #4CAF50;
        }

        #validatePhoto:hover:not(:disabled) {
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
                    <button id="validatePhoto" class="btn" disabled>Validate & Save</button>
                    <button id="cancelPhoto" class="btn btn-danger" disabled>Cancel</button>
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
        const validatePhotoButton = document.getElementById('validatePhoto');
        const cancelPhotoButton = document.getElementById('cancelPhoto');
        const imageUpload = document.getElementById('imageUpload');
        let selectedSticker = null;
        let animationFrame;

        // Fonction pour réinitialiser l'état
        function resetState() {
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
                video.srcObject = null;
            }
            if (animationFrame) {
                cancelAnimationFrame(animationFrame);
            }
            
            // Réinitialiser le canvas
            const ctx = previewCanvas.getContext('2d');
            ctx.clearRect(0, 0, previewCanvas.width, previewCanvas.height);
            
            // Réinitialiser les boutons
            startButton.disabled = false;
            captureButton.disabled = true;
            validatePhotoButton.disabled = true;
            cancelPhotoButton.disabled = true;
            imageUpload.value = '';
            
            // Enlever la sélection du sticker
            selectedSticker = null;
            document.querySelectorAll('.sticker').forEach(s => s.classList.remove('selected'));
        }

        function drawPreview() {
            if (!video.videoWidth) return;

            previewCanvas.width = video.videoWidth;
            previewCanvas.height = video.videoHeight;
            const ctx = previewCanvas.getContext('2d');

            ctx.drawImage(video, 0, 0);

            if (selectedSticker) {
                const sticker = document.querySelector(`[data-sticker="${selectedSticker}"]`);
                const x = (previewCanvas.width - sticker.width) / 2;
                const y = (previewCanvas.height - sticker.height) / 2;
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

        captureButton.addEventListener('click', () => {
            if (!selectedSticker) {
                alert('Please select a sticker first');
                return;
            }
            // Désactiver la capture et activer la validation
            captureButton.disabled = true;
            validatePhotoButton.disabled = false;
            cancelPhotoButton.disabled = false;
        });

        imageUpload.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                // Arrêter la webcam si active
                if (video.srcObject) {
                    video.srcObject.getTracks().forEach(track => track.stop());
                    video.srcObject = null;
                    cancelAnimationFrame(animationFrame);
                }
                
                captureButton.disabled = true;
                validatePhotoButton.disabled = false;
                cancelPhotoButton.disabled = false;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        previewCanvas.width = 640;
                        previewCanvas.height = 480;
                        const ctx = previewCanvas.getContext('2d');
                        
                        const scale = Math.min(
                            previewCanvas.width / img.width,
                            previewCanvas.height / img.height
                        );
                        
                        const x = (previewCanvas.width - img.width * scale) / 2;
                        const y = (previewCanvas.height - img.height * scale) / 2;
                        
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

        validatePhotoButton.addEventListener('click', () => {
            if (!selectedSticker) {
                alert('Please select a sticker first');
                return;
            }
            const imageData = previewCanvas.toDataURL('image/png');
            uploadImage(imageData).then(() => {
                resetState();
            });
        });

        cancelPhotoButton.addEventListener('click', resetState);

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
                    return true;
                } else {
                    alert('Error saving photo: ' + (data.error || 'Unknown error'));
                    return false;
                }
            } catch (err) {
                console.error('Error:', err);
                alert('Error saving photo');
                return false;
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
                <button class="btn btn-danger delete-photo" data-id="${photo.id}">Delete</button>
            </div>
        `).join('');

        // Ajouter les event listeners pour les boutons de suppression
        document.querySelectorAll('.delete-photo').forEach(button => {
            button.addEventListener('click', async () => {
                if (confirm('Are you sure you want to delete this photo?')) {
                    await deletePhoto(button.dataset.id);
                }
            });
        });
    } catch (err) {
        console.error('Error refreshing photos:', err);
    }
}

async function deletePhoto(imageId) {
    try {
        const response = await fetch('/editor/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ imageId })
        });
        
        const data = await response.json();
        if (data.success) {
            refreshPhotosList();
        } else {
            alert('Error deleting photo: ' + data.error);
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Error deleting photo');
    }
}

        refreshPhotosList();
    </script>
</body>
</html>