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
    <title>Edit Profile - Camagru</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="/profile/update" method="POST" class="form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($_SESSION['user']['username']); ?>" 
                       class="form-control">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control">
                <small>Leave blank to keep current email</small>
            </div>

            <div class="form-group">
            <label>
                <input type="checkbox" name="notifications_enabled" 
                    <?php echo $user['notifications_enabled'] ? 'checked' : ''; ?>>
                Receive email notifications for comments
            </label>
        </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="/dashboard" class="btn">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>