<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Resources</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/challenge_dev.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="/HACKATHON_ESGIS/public/js/user/challenge_dev.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>
    <?php require_once '../includes/user/header.php'; ?>

    <main>
        <div class="resources-container">
            <div class="resources-header">
                <div class="header-content">
                    <div class="header-title">
                        <i data-lucide="code" style="color: var(--blue); font-size: 1.5rem;"></i>
                        <h1 style="font-size: 1.5rem; margin: 0;">Défis de développement</h1>
                    </div>
                    <p style="color: var(--text-secondary); margin: 0;">Choisissez parmi notre liste de défis de développement et de sécurité</p>
                </div>
                <div class="header-actions">
                    <a href="/HACKATHON_ESGIS/public/user" class="back-link" style="color: var(--blue); text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
                        <i data-lucide="arrow-left" style="color: var(--blue);"></i>
                        Retour au Dashboard
                    </a>
                    <a href="/HACKATHON_ESGIS/public/user/leaderboard">
                        <button class="ranking-button" style="background-color: var(--blue); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i data-lucide="trophy" style="color: white;"></i>
                            Classement
                        </button>
                    </a>
                </div>
            </div>


            <!-- Stats Section -->
            <div class="stats-section">
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div class="stat-card-header">
                                <i data-lucide="users" class="icon-blue"></i>
                                <h4>Total des participants</h4>
                            </div>
                            <div class="stat-card-value">
                                <h3>9</h3>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-content">
                            <div class="stat-card-header">
                                <i data-lucide="award" class="icon-blue"></i>
                                <h4>Défis actifs</h4>
                            </div>
                            <div class="stat-card-value">
                                <h3>1234</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-container">
                <input type="text" class="search-bar" id="searchInput" placeholder="Search resources...">
                <button class="filters" onclick="toggleFilters()" id="filterButton">
                    <i data-lucide="sliders" style="font-size: 1.2rem;"></i>
                    Afficher les filtres
                    <i data-lucide="chevron-down" id="filterIcon" style="font-size: 1.2rem;"></i>
                </button>

                <button class="reset-filters" onclick="resetFilters()">
                    <i data-lucide="x-circle" style="font-size: 1.2rem;"></i>
                    Supprimer filtre
                </button>
            </div>

            <div class="filters-container" id="filterTags">
                <div class="categories">
                    <h3>Catégories</h3>
                    <div class="category-list">
                        <span class="filter-tag" data-category="web">Web</span>
                        <span class="filter-tag" data-category="mobile">Mobile</span>
                        <span class="filter-tag" data-category="ia">IA</span>
                        <span class="filter-tag" data-category="backend">Backend</span>
                        <span class="filter-tag" data-category="frontend">Frontend</span>
                        <span class="filter-tag" data-category="blockchain">Blockchain</span>
                        <span class="filter-tag" data-category="database">Base de données</span>
                        <span class="filter-tag" data-category="devops">DevOps</span>
                    </div>
                </div>

                <div class="difficulty-levels">
                    <h3>Niveau de difficulté</h3>
                    <div class="difficulty-buttons">
                        <span class="filter-tag" data-difficulty="all">All Levels</span>
                        <span class="filter-tag" data-difficulty="facile">Facile</span>
                        <span class="filter-tag" data-difficulty="intermediaire">Intermédiaire</span>
                        <span class="filter-tag" data-difficulty="avancé">Avancé</span>
                    </div>
                </div>
            </div>

            <hr>

            <div class="resources-grid" id="resourcesGrid">
                <!-- Carte 1 -->
                <div class="resource-card"
                    data-title="API REST - Module d’authentification"
                    data-description="Créez une API REST sécurisée avec un système d’authentification JWT complet."
                    data-difficulty="intermédiaire"
                    data-category="backend"
                    data-date="29 mai 2025"
                    data-status="En cours"
                    data-tags="NodeJS, Express, MongoDB">
                    <div class="card-header">
                        <span class="resource-category">Backend</span>
                        <span class="resource-difficulty">Intermédiaire</span>
                    </div>
                    <h3 class="resource-title">API REST - Module d’authentification</h3>
                    <p class="resource-description">
                        Créez une API REST sécurisée avec un système d’authentification JWT complet.
                    </p>
                    <div class="resource-tags">
                        <span class="tag">NodeJS</span>
                        <span class="tag">Express</span>
                        <span class="tag">MongoDB</span>
                    </div>
                    <span class="resource-date">29 mai 2025</span>
                    <div class="card-footer">
                        <span class="resource-status">
                            <i data-lucide="clock"></i>
                            En cours
                        </span>
                        <button class="submit-button" onclick="openSubmitModal(this)">
                            <i data-lucide="upload-cloud"></i>
                            Soumettre
                        </button>
                        <span class="resource-details">
                            <i data-lucide="info"></i>
                            Détails
                        </span>
                    </div>
                </div>
                <!-- Carte 2 -->
                <div class="resource-card"
                    data-title="Application Mobile React Native"
                    data-description="Développez une application mobile cross-platform avec React Native, Redux et Firebase."
                    data-difficulty="avancé"
                    data-category="mobile"
                    data-date="29 mai 2025"
                    data-status="Soumis"
                    data-tags="React Native, Redux, Firebase">
                    <div class="card-header">
                        <span class="resource-category">Mobile</span>
                        <span class="resource-difficulty">Avancé</span>
                    </div>
                    <h3 class="resource-title">Application Mobile React Native</h3>
                    <p class="resource-description">
                        Développez une application mobile cross-platform avec React Native, Redux et Firebase.
                    </p>
                    <div class="resource-tags">
                        <span class="tag">React Native</span>
                        <span class="tag">Redux</span>
                        <span class="tag">Firebase</span>
                    </div>
                    <span class="resource-date">29 mai 2025</span>
                    <div class="card-footer">
                        <span class="resource-status">
                            <i data-lucide="clock"></i>
                            Soumis
                        </span>
                        <span class="resource-details">
                            <i data-lucide="info"></i>
                            Détails
                        </span>
                    </div>
                </div>
                <!-- Carte 3 -->
                <div class="resource-card"
                    data-title="Architecture Microservices"
                    data-description="Développez une architecture microservices Docker et Kubernetes."
                    data-difficulty="avancé"
                    data-category="devops"
                    data-date="30 juin 2025"
                    data-status="En cours"
                    data-tags="Docker, Kubernetes, NodeJS">
                    <div class="card-header">
                        <span class="resource-category">DevOps</span>
                        <span class="resource-difficulty">Avancé</span>
                    </div>
                    <h3 class="resource-title">Architecture Microservices</h3>
                    <p class="resource-description">
                        Développez une architecture microservices Docker et Kubernetes.
                    </p>
                    <div class="resource-tags">
                        <span class="tag">Docker</span>
                        <span class="tag">Kubernetes</span>
                        <span class="tag">NodeJS</span>
                    </div>
                    <span class="resource-date">30 juin 2025</span>
                    <div class="card-footer">
                        <span class="resource-status">
                            <i data-lucide="clock"></i>
                            En cours
                        </span>
                        <button class="submit-button" onclick="openSubmitModal(this)">
                            <i data-lucide="upload-cloud"></i>
                            Soumettre
                        </button>
                        <span class="resource-details">
                            <i data-lucide="info"></i>
                            Détails
                        </span>
                    </div>
                </div>
                <!-- Carte 4 -->
                <div class="resource-card"
                    data-title="Application Web Frontend avec React"
                    data-description="Créez une interface utilisateur moderne pour un tableau de bord d'analytique."
                    data-difficulty="intermédiaire"
                    data-category="frontend"
                    data-date="30 juin 2025"
                    data-status="Disponible"
                    data-tags="React, TypeScript, MongoDB, Recharts">
                    <div class="card-header">
                        <span class="resource-category">Frontend</span>
                        <span class="resource-difficulty">Intermédiaire</span>
                    </div>
                    <h3 class="resource-title">Application Web Frontend avec React</h3>
                    <p class="resource-description">
                        Créez une interface utilisateur moderne pour un tableau de bord d'analytique.
                    </p>
                    <div class="resource-tags">
                        <span class="tag">React</span>
                        <span class="tag">TypeScript</span>
                        <span class="tag">MongoDB</span>
                        <span class="tag">Recharts</span>
                    </div>
                    <span class="resource-date">30 juin 2025</span>
                    <div class="card-footer">
                        <span class="resource-status">
                            <i data-lucide="clock"></i>
                            Disponible
                        </span>
                        <span class="resource-details">
                            <i data-lucide="info"></i>
                            Détails
                        </span>
                    </div>
                </div>
            </div>





    </main>



</body>

</html>