<script defer src="/HACKATHON_ESGIS/public/js/lucide.js"></script>
<script defer src="/HACKATHON_ESGIS/public/js/header.js"></script>
<header>
    <div class="header-container">
        <div class="logo-nav">
            <a href="/HACKATHON_ESGIS/public/" class="logo">
                <div class="logo-circle">E</div>
                <span>EsgisHub</span>
            </a>
            <nav class="header-nav">
                <!-- verifie et attribut la classe active au lien correspondant -->
                <a href="/HACKATHON_ESGIS/public/challenges" class="<?php echo $_SERVER['REQUEST_URI'] == '/HACKATHON_ESGIS/public/challenges' ? 'active' : ''; ?>">Challenges</a>
                <a href="/HACKATHON_ESGIS/public/hackathon" class="<?php echo $_SERVER['REQUEST_URI'] == '/HACKATHON_ESGIS/public/hackathon' ? 'active' : ''; ?>">Hackathons</a>
                <a href="/HACKATHON_ESGIS/public/leaderboard" class="<?php echo $_SERVER['REQUEST_URI'] == '/HACKATHON_ESGIS/public/leaderboard' ? 'active' : ''; ?>">Leaderboard</a>
                <a href="/HACKATHON_ESGIS/public/resources" class="<?php echo $_SERVER['REQUEST_URI'] == '/HACKATHON_ESGIS/public/resources' ? 'active' : ''; ?>">Resources</a>
            </nav>
        </div>
        <div class="header-actions">
            <button class="notification-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </button>
            <button class="start-challenge">Start Challenge</button>
        </div>
    </div>
</header>