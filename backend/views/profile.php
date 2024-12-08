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
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/partials/header.php'; ?>
    
    <div class="container">
        <h1>Edit Profile</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="/profile/update" method="POST" class="form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       value="<?php echo htmlspecialchars($_SESSION['user']['username']); ?>"
                       required 
                       minlength="3" 
                       maxlength="50" 
                       pattern="^[a-zA-Z0-9_]{3,50}$"
                       class="form-control">
                <small>Between 3 and 50 characters, only letters, numbers and underscores</small>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control"
                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                <small>Leave blank to keep current email</small>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" 
                           name="notifications_enabled" 
                           value="1" 
                           <?php echo (isset($user['notifications_enabled']) && $user['notifications_enabled']) ? 'checked' : ''; ?>>
                    Receive email notifications for comments
                </label>
            </div>

            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       minlength="8" 
                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$"
                       class="form-control">
                <small>Leave blank to keep current password. If changing, minimum 8 characters with at least one uppercase letter, one lowercase letter and one number</small>
            </div>

            <button type="submit" class="btn btn-primary">Update Profile</button>
            <a href="/dashboard" class="btn">Back to Dashboard</a>
        </form>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const username = document.getElementById('username');
        const usernamePattern = /^[a-zA-Z0-9_]{3,50}$/;
        if (!usernamePattern.test(username.value)) {
            e.preventDefault();
            alert('Username must contain only letters, numbers and underscores');
            return;
        }

        const password = document.getElementById('password');
        if (password.value) {
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
            if (!passwordPattern.test(password.value)) {
                e.preventDefault();
                alert('Password must contain at least one uppercase letter, one lowercase letter and one number');
                return;
            }
        }

        const email = document.getElementById('email');
        if (email.value) {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
        }
    });
    </script>
</body>
</html>