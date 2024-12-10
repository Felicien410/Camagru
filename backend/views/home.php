<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camagru - Home</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
    <link rel="stylesheet" href="/public/assets/css/home.css">
    <link rel="icon" href="data:," />
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
        
        <div class="explanation">
            <p>Camagru est une application web dédiée à la création et au partage de photos personnalisées. 
               Vous pouvez y ajouter des filtres, des stickers et bien d'autres effets artistiques. 
            </p>
        </div>
        
    </div>
    <?php require_once __DIR__ . '/partials/footer.php'; ?>
    </body>
</html>
