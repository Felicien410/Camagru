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
    <title>Dashboard - Camagru</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome <?php echo htmlspecialchars($_SESSION['user']['username']); ?></h1>
        
        <div class="dashboard-menu">
            <a href="/editor" class="btn">Create New Photo</a>
            <a href="/gallery" class="btn">View Gallery</a>
            <a href="/profile" class="btn">Edit Profile</a>
            <a href="/logout" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>