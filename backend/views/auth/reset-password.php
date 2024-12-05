<!-- views/auth/reset-password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Camagru</title>
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/auth/reset-password" method="POST" class="form">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" 
                       required minlength="8" class="form-control">
                <small>Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm New Password</label>
                <input type="password" id="password_confirm" name="password_confirm" 
                       required minlength="8" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match');
        }
    });
    </script>
</body>
</html>