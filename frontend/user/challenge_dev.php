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
                        <h1>Défis de développement</h1>
                    </div>
                    <div class="header-actions">
                        <a href="/HACKATHON_ESGIS/public/user" class="back-link">
                            <i data-lucide="arrow-left"></i>
                            Retour au Dashboard
                        </a>
                        <a href="/HACKATHON_ESGIS/public/user/leaderboard">
                            <button class="ranking-button">
                                <i data-lucide="trophy"></i>
                                Classement
                            </button>
                        </a>
                    </div>
                </div>
                <p>Choisissez parmi notre liste de défis de développement et de sécurité</p>
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
                                <span>9</span>
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
                                <span>12</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="search-container">
                <div class="search-bar-container">
                    <i data-lucide="search"></i>
                    <input type="text" class="search-bar" id="searchInput" placeholder="Rechercher un défis...">
                </div>
                <div class="filters-btns">
                    <button class="filters" onclick="toggleFilters()" id="filterButton">
                        <i data-lucide="sliders"></i>
                        <span id="filterText">Afficher les filtres</span>
                        <i data-lucide="chevron-down" class="chevron-down" id="filterIcon"></i>
                    </button>

                    <button class="reset-filters" onclick="resetFilters()">
                        <i data-lucide="x-circle"></i>
                        <span>Supprimer filtre</span>
                    </button>
                </div>
            </div>

            <div class="filters-container" id="filterTags">
                <div class="filter-section categories">
                    <h3>Catégorie</h3>
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

                <div class="filter-section difficulty-levels">
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
                <div class="col-span-full flex flex-col items-center justify-center text-center py-16 px-4 gap-4">
                    <i data-lucide="search-x" class="w-16 h-16 text-blue-500"></i>

                    <h2 class="text-xl text-white font-semibold">
                        Aucun défi trouvé
                    </h2>

                    <p class="text-sm text-zinc-400 max-w-md">
                        Aucun challenge ne correspond à vos critères actuels.<br />
                        Essayez de modifier vos filtres ou votre recherche.
                    </p>

                    <button
                        onclick="resetFilters()"
                        class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow transition">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        Réinitialiser les filtres
                    </button>
                </div>

            </div>
        </div>
    </main>

</body>

</html>