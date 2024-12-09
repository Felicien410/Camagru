<style>
    footer {
        background-color: #000000;
        padding: 20px 0;
        margin-top: auto;
        width: 100%;
    }

    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .footer-links {
        display: flex;
        gap: 20px;
    }

    .footer-links a {
        text-decoration: none;
        color: #ffffff;
    }

    .footer-links a:hover {
        color: #cccccc;
    }

    .footer-copyright {
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .footer-content {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }

        .footer-links {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>

<footer>
    <div class="footer-content">
        <div class="footer-links">
            <a href="/about">About</a>
            <a href="/contact">Contact</a>
            <a href="/privacy">Privacy Policy</a>
            <a href="/terms">Terms of Service</a>
        </div>
        <div class="footer-copyright">
            © <?php echo date('Y'); ?> Camagru. All rights reserved.
        </div>
    </div>
</footer>