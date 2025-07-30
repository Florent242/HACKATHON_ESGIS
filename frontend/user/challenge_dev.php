<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="hackathon-id" content="2">
  <meta name="phase-id" content="2">
  <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
  <title>EsgisHub - Phase 1 : Challenges Algorithmiques</title>
  <link rel="stylesheet" href="/css/styles/user/challenge_dev.css">
  <?php require_once '../includes/user/head.php'; ?>
  <script defer src="/js/user/challenge_dev.js"></script>
</head>

<body class="min-h-screen bg-gradient-dark">
  <?php require_once '../includes/user/header.php'; ?>

  <main class="main-container">
    <div class="flex flex-col lg:flex-row gap-6 max-w-7xl mx-auto px-4 py-6">
      <!-- Sidebar -->
      <aside class="w-full lg:w-80 flex-shrink-0">
        <!-- Performance Card -->
        <div class="dashboard-card performance-card mb-6">
          <div class="card-header">
            <div class="header-icon">
              <i data-lucide="trophy" class="w-5 h-5"></i>
            </div>
            <h3 class="card-title">Vos Performances</h3>
          </div>
          <div class="performance-content">
            <div class="main-score">
              <span class="score-value">0</span>
              <span class="score-label">points obtenus</span>
            </div>
            <div class="performance-grid">
              <div class="perf-item">
                <div class="perf-value solved">0</div>
                <div class="perf-label">Résolus</div>
              </div>
              <div class="perf-item">
                <div class="perf-value rank">#0</div>
                <div class="perf-label">Rang</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Rules Card -->
        <div class="dashboard-card rules-card">
          <div class="card-header">
            <div class="header-icon">
              <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <h3 class="card-title">Règles Importantes</h3>
          </div>
          <div class="rules-content">
            <div class="rule-item">
              <div class="rule-icon">
                <i data-lucide="code" class="w-4 h-4"></i>
              </div>
              <div class="rule-text">
                <div class="rule-title">Langages autorisés</div>
                <div class="rule-desc">Python, Java, C++, JavaScript</div>
              </div>
            </div>
            <div class="rule-item">
              <div class="rule-icon">
                <i data-lucide="clock" class="w-4 h-4"></i>
              </div>
              <div class="rule-text">
                <div class="rule-title">Temps d'exécution max</div>
                <div class="rule-desc">2 secondes par test</div>
              </div>
            </div>
          </div>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="flex-1 min-w-0">
        <div class="search-filter-section">
          <div class="search-container">
            <div class="search-input-wrapper">
              <i data-lucide="search" class="search-icon"></i>
              <input
                type="text"
                id="search-input"
                placeholder="Rechercher un challenge par titre ou description..."
                class="search-input" />
            </div>
          </div>

          <div class="filter-controls">
            <div class="filter-group">
              <button class="filter-btn active" data-filter="all">
                <i data-lucide="grid-3x3" class="w-4 h-4"></i>
                Tous
              </button>
              <button class="filter-btn" data-filter="easy">
                <i data-lucide="circle" class="w-4 h-4"></i>
                Facile
              </button>
              <button class="filter-btn" data-filter="medium">
                <i data-lucide="minus-circle" class="w-4 h-4"></i>
                Moyen
              </button>
              <button class="filter-btn" data-filter="hard">
                <i data-lucide="alert-circle" class="w-4 h-4"></i>
                Difficile
              </button>
            </div>

            <div class="sort-group">
              <button class="sort-btn" data-sort="points-desc">
                <i data-lucide="trending-down" class="w-4 h-4"></i>
                Points ↓
              </button>
              <button class="sort-btn" data-sort="difficulty-asc">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                Difficulté ↑
              </button>
            </div>
          </div>
        </div>

        <div class="results-counter">
          <div class="results-info">
            <span class="results-text">
              <span id="filtered-count">0</span> challenge(s) trouvé(s)
            </span>
            <span class="active-filters" id="active-filters-text"></span>
          </div>
        </div>

        <div class="challenges-section">
          <div id="challenges-grid" class="challenges-grid">
            <div class="loading-state">
              <div class="loading-spinner">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin"></i>
              </div>
              <p class="loading-text">Chargement des challenges...</p>
            </div>

            <!-- Empty state -->
            <div id="challenges-empty-state" class="w-full py-12 hidden items-center justify-center flex-col animate-fade-in">
              <div class="relative mx-auto flex items-center justify-center">
                <div class="absolute inset-0 rounded-full bg-blue-500/10 blur-xl animate-pulse-slow"></div>
                <div class="relative z-10 flex items-center justify-center h-20 w-20 rounded-2xl bg-gradient-to-br from-blue-600/20 to-blue-800/30 backdrop-blur-sm border border-blue-500/20 shadow-lg shadow-blue-500/10 mb-6 animate-pulse-slow">
                  <i id="empty-icon" data-lucide="shield-question" class="w-10 h-10 text-blue-400"></i>
                </div>
              </div>

              <h3 id="empty-title" class="text-2xl font-bold text-white mb-2 text-center">Aucun challenge disponible</h3>
              <p id="empty-message" class="text-gray-400 text-center max-w-md mb-6 leading-relaxed">
                Il n'y a pas encore de challenge de sécurité disponible pour le moment. Revenez bientôt pour découvrir de nouveaux défis passionnants !
              </p>

              <div class="flex flex-col sm:flex-row gap-3">
                <a href="/user/hackathon"
                  class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2 group">
                  <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-0.5"></i>
                  Voir les hackathons
                </a>
                <button onclick="window.location.reload()"
                  class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-gray-200 font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2 group">
                  <i data-lucide="refresh-ccw" class="w-4 h-4 transition-transform group-hover:rotate-180"></i>
                  Actualiser
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <div id="challenge-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title">Détails du Challenge</h3>
        <button class="modal-close" id="close-modal">
          <i data-lucide="x" class="w-5 h-5"></i>
        </button>
      </div>
      <div class="modal-body" id="modal-body">
      </div>
    </div>
  </div>

</body>

</html>