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
    <title>Register - Camagru</title>
    <link rel="stylesheet" href="/public/assets/css/auth.css">
    <link rel="icon" href="data:," />

</head>
<body>
</div>
    <div class="shooting-stars-container">
    <div class="shooting-star" style="--shoot-top: 10%; --shoot-left: 5%; --shoot-duration:4s; --shoot-delay:1s;"></div>
    <div class="shooting-star" style="--shoot-top: 20%; --shoot-left: 15%; --shoot-duration:5s; --shoot-delay:2s;"></div>
    <div class="shooting-star" style="--shoot-top: 30%; --shoot-left: 2%; --shoot-duration:4.5s; --shoot-delay:3s;"></div>
    <div class="shooting-star" style="--shoot-top: 40%; --shoot-left: 10%; --shoot-duration:6s; --shoot-delay:4s;"></div>
    <div class="shooting-star" style="--shoot-top: 50%; --shoot-left: 1%; --shoot-duration:5s; --shoot-delay:5s;"></div>
</div>
    <div class="container">
        <h1>Create Account</h1>
        
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

        <form action="/auth/register" method="POST" class="form" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       required 
                       minlength="3" 
                       maxlength="50" 
                       pattern="^[a-zA-Z0-9_]{3,50}$"
                       class="form-control"
                       autocomplete="off">
                <small>Between 3 and 50 characters, only letters, numbers and underscores</small>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       class="form-control"
                       autocomplete="off">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
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

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="text-center">
            Already have an account? <a href="/login">Login here</a>
        </p>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password');
        const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        
        if (!passwordPattern.test(password.value)) {
            e.preventDefault();
            alert('Password must contain at least one uppercase letter, one lowercase letter and one number');
            return;
        }
        
        const username = document.getElementById('username');
        const usernamePattern = /^[a-zA-Z0-9_]{3,50}$/;
        if (!usernamePattern.test(username.value)) {
            e.preventDefault();
            alert('Username must contain only letters, numbers and underscores');
            return;
        }

        const email = document.getElementById('email');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email.value)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            return;
        }
    });
    </script>
    <?php require_once __DIR__ . '../partials/footer.php'; ?>
    </body>
</html>