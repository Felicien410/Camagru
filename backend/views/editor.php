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
    <link rel="stylesheet" href="/public/assets/css/style.css">
    </head>
<body>
    <?php require_once __DIR__ . '/partials/header.php'; ?>
    
    <main class="container">
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
    </main>

    <script src="/public/assets/js/editor.js"></script>
    </body>
</html>