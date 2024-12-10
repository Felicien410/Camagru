// Elements DOM
const video = document.getElementById('webcam');
const canvas = document.getElementById('canvas');
const previewCanvas = document.getElementById('previewCanvas');
const startButton = document.getElementById('startCamera');
const captureButton = document.getElementById('capture');
const validatePhotoButton = document.getElementById('validatePhoto');
const cancelPhotoButton = document.getElementById('cancelPhoto');
const imageUpload = document.getElementById('imageUpload');
const uploadLabel = document.querySelector('label[for="imageUpload"]');

const CANVAS_WIDTH = 800;
const CANVAS_HEIGHT = 600;

let selectedSticker = null;
let animationFrame;
let isUsingCamera = false;
let currentImage = null;
let capturedImage = null;

// Initialiser les dimensions du canvas
previewCanvas.width = CANVAS_WIDTH;
previewCanvas.height = CANVAS_HEIGHT;

function calculateImagePosition(srcWidth, srcHeight) {
    const srcRatio = srcWidth / srcHeight;
    const canvasRatio = CANVAS_WIDTH / CANVAS_HEIGHT;
    let drawWidth, drawHeight, x, y;

    if (srcRatio > canvasRatio) {
        drawWidth = CANVAS_WIDTH;
        drawHeight = CANVAS_WIDTH / srcRatio;
        x = 0;
        y = (CANVAS_HEIGHT - drawHeight) / 2;
    } else {
        drawHeight = CANVAS_HEIGHT;
        drawWidth = CANVAS_HEIGHT * srcRatio;
        x = (CANVAS_WIDTH - drawWidth) / 2;
        y = 0;
    }

    return { x, y, width: drawWidth, height: drawHeight };
}

function updateCanvas() {
    const ctx = previewCanvas.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);

    if (isUsingCamera && video.videoWidth) {
        const { x, y, width, height } = calculateImagePosition(video.videoWidth, video.videoHeight);
        ctx.drawImage(video, x, y, width, height);
    } else if (currentImage) {
        const { x, y, width, height } = calculateImagePosition(currentImage.width, currentImage.height);
        ctx.drawImage(currentImage, x, y, width, height);
    }

    if (selectedSticker) {
        const sticker = document.querySelector(`[data-sticker="${selectedSticker}"]`);
        if (sticker) {
            if (selectedSticker === 'logo') {
                const logoWidth = CANVAS_WIDTH * 0.10;
                const logoHeight = (sticker.height * logoWidth) / sticker.width;
                const logoX = CANVAS_WIDTH * 0.75;
                const logoY = CANVAS_HEIGHT - logoHeight - (CANVAS_HEIGHT * 0.08);
                ctx.drawImage(sticker, logoX, logoY, logoWidth, logoHeight);
            } else {
                const stickerWidth = CANVAS_WIDTH * 0.3;
                const stickerHeight = (sticker.height * stickerWidth) / sticker.width;
                const stickerX = (CANVAS_WIDTH - stickerWidth) / 2;
                const stickerY = (CANVAS_HEIGHT - stickerHeight) / 2;
                ctx.drawImage(sticker, stickerX, stickerY, stickerWidth, stickerHeight);
            }
        }
    }

    // Appeler l'update en continu, même sans caméra
    animationFrame = requestAnimationFrame(updateCanvas);
}


function resetState() {
    stopCamera();
    currentImage = null;
    capturedImage = null;
    
    const ctx = previewCanvas.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_WIDTH, CANVAS_HEIGHT);
    
    startButton.disabled = false;
    captureButton.style.display = 'none'; // Cacher par défaut
    captureButton.disabled = true;
    validatePhotoButton.disabled = true;
    cancelPhotoButton.disabled = true;
    imageUpload.value = '';
    uploadLabel.classList.remove('disabled');
    
    selectedSticker = null;
    document.querySelectorAll('.sticker').forEach(s => s.classList.remove('selected'));
}

function stopCamera() {
    if (video.srcObject) {
        video.srcObject.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    if (animationFrame) {
        cancelAnimationFrame(animationFrame);
    }
    isUsingCamera = false;
}

function updateButtonStates() {
    // Gérer l'état des boutons en fonction du mode (caméra ou upload)
    if (isUsingCamera) {
        captureButton.style.display = ''; // Afficher
        captureButton.disabled = !selectedSticker;
        validatePhotoButton.disabled = true;
    } else if (currentImage) {
        captureButton.style.display = 'none'; // Cacher
        validatePhotoButton.disabled = !selectedSticker;
    }
    cancelPhotoButton.disabled = !(currentImage || isUsingCamera);
}

// Event Listeners
startButton.addEventListener('click', async () => {
    try {
        stopCamera();
        currentImage = null;
        const stream = await navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 1280 },
                height: { ideal: 720 }
            } 
        });
        video.srcObject = stream;
        await video.play();
        isUsingCamera = true;
        startButton.disabled = true;
        updateButtonStates();
        updateCanvas();
    } catch (err) {
        alert('Could not access camera: ' + err.message);
        resetState();
    }
});

document.querySelectorAll('.sticker').forEach(sticker => {
    sticker.addEventListener('click', () => {
        document.querySelectorAll('.sticker').forEach(s => s.classList.remove('selected'));
        sticker.classList.add('selected');
        selectedSticker = sticker.dataset.sticker;
        updateButtonStates();
        updateCanvas();
    });
});

captureButton.addEventListener('click', () => {
    if (!selectedSticker) {
        alert('Please select a sticker first');
        return;
    }
    
    capturedImage = new Image();
    capturedImage.onload = () => {
        stopCamera();
        currentImage = capturedImage;
        updateButtonStates();
        updateCanvas();
    };
    
    capturedImage.src = previewCanvas.toDataURL('image/png');
});

imageUpload.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        stopCamera();
        startButton.disabled = false;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                currentImage = img;
                updateButtonStates();
                updateCanvas();
                uploadLabel.classList.add('disabled');
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
        alert('Error saving photo');
        return false;
    }
}


async function refreshPhotosList() {
    try {
        const response = await fetch('/editor/photos');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new TypeError("Expected JSON response, got " + contentType);
        }

        const photos = await response.json();
        
        if (!Array.isArray(photos)) {
            throw new TypeError("Expected array of photos, got " + typeof photos);
        }

        const photosList = document.getElementById('photosList');
        photosList.innerHTML = photos.map(photo => `
            <div class="photo-item">
                <img src="${photo.image_path}" alt="Photo">
                <div class="photo-actions">
                    <button class="btn btn-danger delete-photo" data-id="${photo.id}">Delete</button>
                </div>
            </div>
        `).join('');

        // Event listeners pour la suppression
        document.querySelectorAll('.delete-photo').forEach(button => {
            button.addEventListener('click', async () => {
                if (confirm('Are you sure you want to delete this photo?')) {
                    await deletePhoto(button.dataset.id);
                }
            });
        });
    } catch (err) {
        const photosList = document.getElementById('photosList');
        photosList.innerHTML = '<div class="error">Error loading photos</div>';
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
        alert('Error deleting photo');
    }
}

// Appel initial
refreshPhotosList();
