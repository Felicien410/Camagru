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
    <title>Forgot Password - Camagru</title>
    <link rel="stylesheet" href="/public/assets/css/auth.css">

</head>
<body>
    
    <div class="container">
        <h1>Forgot Password</h1>
        
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

        <form action="/auth/forgot-password" method="POST" class="form" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       class="form-control"
                       autocomplete="off">
                <small>Enter your registered email address</small>
            </div>

            <button type="submit" class="btn btn-primary">Send Reset Link</button>
            <a href="/login" class="btn">Back to Login</a>
        </form>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.getElementById('email');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            return;
        }
    });
    </script>
</body>
</html>