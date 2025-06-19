<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <link rel="stylesheet" href="/css/styles/home.css">
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script defer src="/js/home.js"></script>
    <script src="/js/user/test.js"></script> <!-- Inclure le script ici -->

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
                Challengez-vous avec
                <span class="highlight">EsgisHub</span>
            </h1>
            <p class="hero-subtitle fade-in">
                Rejoignez notre communauté de développeurs et de passionnés de sécurité pour construire des projets exceptionnels,
                maîtriser de nouvelles technologies et découvrir des défis de cybersécurité.
            </p>
            <div class="hero-buttons">
                <button class="btn-primary btn-startchallenge fade-in">
                    Commencer votre voyage
                    <i data-lucide="arrow-right"></i>
                </button>
                <button class="btn-primary btn-standard fade-in">
                    Découvrir les défis
                </button>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section id="stats" class="stats-section fade-in">
        <div class="stats-container">
            <div class="stat-item">
                <i data-lucide="users"></i>
                <h2><span>200+</span></h2>
                <p>Membres actifs</p>
            </div>
            <div class="stat-item">
                <i data-lucide="calendar"></i>
                <h2><span>50+</span></h2>
                <p>Défis à venir</p>
            </div>
            <div class="stat-item">
                <i data-lucide="trophy"></i>
                <h2>$<span>50</span>K</h2>
                <p>En jeu</p>
            </div>
            <div class="stat-item">
                <i data-lucide="swords"></i>
                <h2><span>2</span></h2>
                <p>Types de challenge</p>
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
                        Arrive bientôt
                        </span>
                        <span>
                            <i data-lucide="users"></i>200 participants attendus
                        </span>
                    </div>
                    <button class="btn-primary btn-ghost">En savoir plus <i data-lucide="arrow-right"></i>
                    </button>
                </div>
                <div class="events-item  fade-in-right">
                    <h3>Défis de Sécurité</h3>
                    <div>
                        <span>
                            <i data-lucide="calendar"></i>
                            Arrive bientôt
                        </span>
                        <span>
                            <i data-lucide="users"></i>200 participants attendus
                        </span>
                    </div>
                    <button class="btn-primary btn-ghost">En savoir plus <i data-lucide="arrow-right"></i>
                    </button>
                </div>

    </section>
    <!-- footer -->
    <?php include '../includes/footer.php'; ?>
</body>
</html>