<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camagru - Home</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/home.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Camagru</h1>
        
        <?php if(isset($_SESSION['user'])): ?>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user']['username']); ?>!</p>
            <a href="/gallery" class="btn">View Gallery</a>
            <a href="/editor" class="btn">Create Photo</a>
            <a href="/logout" class="btn">Logout</a>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="/login" class="btn">Login</a>
                <a href="/register" class="btn">Register</a>
            </div>
        <?php endif; ?>
        
        <!-- Section d'explications sur Camagru -->
        <div class="explanation">
            <p>Camagru est une application web dédiée à la création et au partage de photos personnalisées. 
               Vous pouvez y ajouter des filtres, des stickers et bien d'autres effets artistiques. 
               L'idée est de donner un espace aux utilisateurs pour exprimer leur créativité, 
               partager leurs œuvres, et interagir avec une communauté de passionnés d'image.</p>
            <p>Que vous soyez un photographe amateur, un passionné du selfie ou simplement à la recherche 
               d'un outil ludique pour customiser vos images, Camagru est fait pour vous.</p>
        </div>
        
    </div>
</body>
</html>
