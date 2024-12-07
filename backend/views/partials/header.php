<header class="header">
    <nav>
        <div class="logo">
            <a href="/">Camagru</a>
        </div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user'])): ?>
                <a href="/dashboard">Dashboard</a>
                <a href="/gallery">Gallery</a>
                <a href="/editor">Editor</a>
                <a href="/profile">Profile</a>
                <a href="/logout" class="btn btn-logout">Logout</a>
            <?php else: ?>
                <a href="/gallery">Gallery</a>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>