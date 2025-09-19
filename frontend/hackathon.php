<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    <?php require_once "../includes/head.php"; ?>
    <link rel="stylesheet" href="/css/styles/hackathons.css">
</head>
<body class="bg-background text-text">
    <?php require_once '../includes/header.php'; ?>

    <main class="min-h-screen">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container mx-auto px-6 text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-4">
                    Hackathons <span class="text-blue-light">EsgisHub</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-text-secondary">
                    Participez à des défis passionnants et repoussez vos limites
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <div class="stat-item">
                        <span class="stat-number" id="total-hackathons">0</span>
                        <span class="stat-label">Hackathons actifs</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="total-participants">0</span>
                        <span class="stat-label">Capacité estimée</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters Section -->
        <section class="py-8 bg-background border-b border-border">
            <div class="container mx-auto px-6">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="filter-group flex flex-wrap gap-2">
                        <button class="filter-btn active" data-filter="all">Tous</button>
                        <button class="filter-btn" data-filter="active">Actifs</button>
                        <button class="filter-btn" data-filter="upcoming">À venir</button>
                        <button class="filter-btn" data-filter="past">Passés</button>
                    </div>
                    <div class="search-group">
                        <input 
                            type="text" 
                            id="search-input" 
                            placeholder="Rechercher un hackathon..." 
                            class="search-input"
                        >
                    </div>
                </div>
            </div>
        </section>

        <!-- Hackathons Grid -->
        <section class="py-12">
            <div class="container mx-auto px-6">
                <!-- Loading State -->
                <div id="loading-state" class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
                    <p class="text-text-secondary mt-4">Chargement des hackathons...</p>
                </div>

                <!-- Error State -->
                <div id="error-state" class="text-center py-12 hidden">
                    <i data-lucide="alert-triangle" class="w-12 h-12 text-red-400 mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-text mb-2">Erreur de chargement</h3>
                    <p class="text-text-secondary mb-4">Impossible de charger les hackathons.</p>
                    <button onclick="loadHackathons()" class="btn-primary">Réessayer</button>
                </div>

                <!-- Empty State -->
                <div id="empty-state" class="text-center py-12 hidden">
                    <i data-lucide="calendar-x" class="w-12 h-12 text-text-muted mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-text mb-2">Aucun hackathon trouvé</h3>
                    <p class="text-text-secondary">Aucun hackathon ne correspond à vos critères de recherche.</p>
                </div>

                <!-- Hackathons Grid -->
                <div id="hackathons-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 hidden">
                    <!-- Cards will be populated by JavaScript -->
                </div>
            </div>
        </section>
    </main>
    
    <?php require_once '../includes/footer.php'; ?>
    <!-- Scripts -->
    <script defer src="js/hackathons.js"></script>
</body>
</html>