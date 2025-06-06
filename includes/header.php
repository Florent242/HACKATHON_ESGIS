<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<script defer src="/js/header.js"></script>
<header>
    <div class="header-container">
        <div class="logo-nav">
            <a href="/" class="logo">
                <div class="logo-circle">E</div>
                <span>EsgisHub</span>
            </a>
            <nav class="header-nav">
                <!-- verifie et attribut la classe active au lien correspondant -->
                <a href="/hackathon" class="<?php echo $_SERVER['REQUEST_URI'] == '/hackathon' ? 'active' : ''; ?>">Hackathons</a>
                <a href="/resources" class="<?php echo $_SERVER['REQUEST_URI'] == '/resources' ? 'active' : ''; ?>">Resources</a>
                <a href="/contact" class="<?php echo $_SERVER['REQUEST_URI'] == '/contact' ? 'active' : ''; ?>">Contact</a>
                <a href="/sponsors" class="<?php echo $_SERVER['REQUEST_URI'] == '/sponsors' ? 'active' : ''; ?>">Sponsors</a>
            </nav>
        </div>
        <div class="header-actions">
            <button class="start-challenge">Start Challenge</button>
        </div>
    </div>
</header>
<div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>