<!-- views/auth/forgot-password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Camagru</title>
</head>
<body>
    <div class="container">
        <h1>Forgot Password</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form action="/auth/forgot-password" method="POST" class="form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required class="form-control">
                <small>Enter your registered email address</small>
            </div>

            <button type="submit" class="btn btn-primary">Send Reset Link</button>
            <a href="/login.php" class="btn">Back to Login</a>
        </form>
    </div>
</body>
</html>