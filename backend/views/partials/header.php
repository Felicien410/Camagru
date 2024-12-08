<header class="header">
    <nav>
        <div class="logo">
            <a href="/">Camagru</a>
        </div>
        
        <button class="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    // Fermer le menu en cliquant ailleurs
    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('active');
        }
    });
});
</script>