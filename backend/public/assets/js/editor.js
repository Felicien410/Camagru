//editor 
// Elements DOM
const video = document.getElementById('webcam');
const canvas = document.getElementById('canvas');
const previewCanvas = document.getElementById('previewCanvas');
const startButton = document.getElementById('startCamera');
const captureButton = document.getElementById('capture');
const validatePhotoButton = document.getElementById('validatePhoto');
const cancelPhotoButton = document.getElementById('cancelPhoto');
const imageUpload = document.getElementById('imageUpload');

// Variables globales
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

// Event Listeners
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

// API Functions
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

// Initial load
refreshPhotosList();