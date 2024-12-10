<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camagru</title>
    <style>
        .logo img {
            height: 70px;
            width: auto;
            vertical-align: middle;
            max-width: 100%;
        }

        .logo a {
            display: inline-block;
            text-decoration: none;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .left-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-logout {
            background-color: #ff4444;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav>
            <div class="left-section">
                <div class="logo">
                    <link rel="icon" href="data:," />

                    <a href="/"><img src="/public/images/logo.jpg" alt="Camagru Logo"></a>        
                </div>
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/logout" class="btn btn-logout">Logout</a>
                <?php endif; ?>
            </div>
        
            <button class="hamburger" aria-label="Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-links">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="/gallery">Gallery</a>
                    <a href="/editor">Editor</a>
                    <a href="/profile">Profile</a>
                <?php else: ?>
                    <a href="/gallery">Gallery</a>
                    <a href="/login">Login</a>
                    <a href="/register">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('active');
        }
    });
});
</script>
</body>
</html>