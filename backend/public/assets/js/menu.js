document.addEventListener('DOMContentLoaded', () => {
    // Ajout du fond étoilé
    const starsDiv = document.createElement('div');
    starsDiv.className = 'stars';
    document.body.appendChild(starsDiv);

    // Gestion du menu hamburger
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        
        // Animation du hamburger
        const spans = hamburger.querySelectorAll('span');
        spans.forEach(span => span.classList.toggle('active'));
    });

    // Fermer le menu en cliquant en dehors
    document.addEventListener('click', (e) => {
        if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
            navLinks.classList.remove('active');
        }
    });
});