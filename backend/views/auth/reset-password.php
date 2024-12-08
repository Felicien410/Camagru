<?php
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Camagru</title>
    <link rel="stylesheet" href="/public/assets/css/auth.css">
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/auth/reset-password" method="POST" class="form" autocomplete="off">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       minlength="8" 
                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$" 
                       class="form-control"
                       autocomplete="new-password">
                <small>Minimum 8 characters, at least one uppercase letter, one lowercase letter and one number</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm New Password</label>
                <input type="password" 
                       id="password_confirm" 
                       name="password_confirm" 
                       required 
                       minlength="8" 
                       class="form-control"
                       autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary">Reset Password</button>
            <a href="/login" class="btn">Back to Login</a>
        </form>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirm');
        const pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        
        if (!pattern.test(password.value)) {
            e.preventDefault();
            alert('Password must contain at least one uppercase letter, one lowercase letter and one number');
            return;
        }
        
        if (password.value !== confirm.value) {
            e.preventDefault();
            alert('Passwords do not match');
            return;
        }
    });
    </script>
</body>
</html>