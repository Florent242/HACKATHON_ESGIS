<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/home.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/home.js"></script>
    <!-- Lucide Icons -->
    <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
</head>
<body>
    <!-- Navigation -->
    <?php require_once '../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1 class="hero-title fade-in">
                Challenge Yourself with
                <span class="highlight">EsgisHub</span>
            </h1>
            <p class="hero-subtitle fade-in">
                Join our community of developers and security enthusiasts in building amazing projects,
                mastering new technologies, and discovering cybersecurity challenges.
            </p>
            <div class="hero-buttons">
                <button class="btn btn-primary fade-in">
                    Start Your Journey
                    <i data-lucide="arrow-right"></i>
                </button>
                <button class="btn btn-secondary fade-in">
                    Explore Challenges
                </button>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section fade-in">
        <div class="stats-container">
            <div class="stat-item">
                <i data-lucide="users"></i>
                <h2><span>1200+</span></h2>
                <p>Active Members</p>
            </div>
            <div class="stat-item">
                <i data-lucide="calendar"></i>
                <h2><span>50+</span></h2>
                <p>Weekly Challenges</p>
            </div>
            <div class="stat-item">
                <i data-lucide="trophy"></i>
                <h2>$<span>50</span>K</h2>
                <p>In Prizes</p>
            </div>
            <div class="stat-item">
                <i data-lucide="swords"></i>
                <h2><span>2</span></h2>
                <p>Challenge Types</p>
            </div>
        </div>
    </section>

    <!-- Featured Challenges Section -->
    <section class="arguments-section">
        <div class="arguments-header">
            <h2>Pourquoi Choisir EsgisHub ?</h2>
            <p>Découvrez ce qui rend notre plateforme unique.</p>
        </div>
        <div class="arguments-container">
            <div class="arguments-list fade-in">
                <i data-lucide="chevrons-left-right"></i>
                <h3>Défis de Développement</h3>
                <p>Participez à des défis de programmation stimulants et améliorez vos compétences</p>
            </div>
            <div class="arguments-list fade-in">
                <i data-lucide="shield"></i>
                <h3>Défis de Sécurité</h3>
                <p>Testez vos compétences en cybersécurité avec des challenges réalistes</p>
            </div>
            <div class="arguments-list fade-in">
                <i data-lucide="users"></i>
                <h3>Communauté Active</h3>
                <p>Rejoignez une communauté dynamique de développeurs et d'experts en sécurité</p>
            </div>
            <div class="arguments-list fade-in">
                <i data-lucide="trophy"></i>
                <h3>Competitions</h3>
                <p>Participez à des hackathons et gagnez des prix exceptionnels</p>
            </div>
        </div>
    </section>

    <!-- events section -->
    <section class="events-section">
        <div class="events-header">
            <h2>Événements à Venir</h2>
            <p>Ne manquez pas nos prochains hackathons</p>
        </div>

        <div class="events-container">
                <div class="events-item fade-in-left">
                    <h3>ESGIS Hackathon 2024</h3>
                    <div>
                        <span>
                            <i data-lucide="calendar"></i>
                        15-17 Mars 2024
                        </span>
                        <span>
                            <i data-lucide="users"></i>200 participants
                        </span>
                        <span>
                            <i data-lucide="trophy"></i>Prix: 2,000,000 FCFA
                        </span>
                    </div>
                    <button class="event-info">En savoir plus <i data-lucide="arrow-right"></i>
                    </button>
                </div>
                <div class="events-item  fade-in-right">
                    <h3>Security Challenge Week</h3>
                    <div>
                        <span>
                            <i data-lucide="calendar"></i>
                            15-17 Mars 2024
                        </span>
                        <span>
                            <i data-lucide="users"></i>200 participants
                        </span>
                        <span>
                            <i data-lucide="trophy"></i>Prix: 2,000,000 FCFA
                        </span>
                    </div>
                    <button class="event-info">En savoir plus <i data-lucide="arrow-right"></i>
                    </button>
                </div>

    </section>
    <!-- footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>