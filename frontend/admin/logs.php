<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Logs</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/log.css">
</head>
<body>
    <?php require_once '../includes/admin/header.php'; ?>

    <h1 class="page-title">Logs du système</h1>

    <div class="card-header" style="justify-content: flex-end; margin-bottom: 20px;">
        <button class="btn btn-primary" id="exportLogsBtn">
            <i class="fas fa-download btn-icon"></i> Exporter les logs
        </button>
    </div>

    <!-- Statistiques -->
    <div class="stats-container" id="statsContainer">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Logs</h3>
                <div class="number" id="totalLogs">0</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-history"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Connexions</h3>
                <div class="number" id="connections">0</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-sign-in-alt"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Actions Équipes</h3>
                <div class="number" id="teamActions">0</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Challenges</h3>
                <div class="number" id="challenges">0</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" id="searchInput" placeholder="Rechercher dans les logs...">
    </div>

    <!-- Filtres -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <div class="dropdown" style="flex: 1;">
            <button class="dropdown-toggle" id="actionFilter" style="width: 100%; justify-content: space-between;">
                <span id="actionFilterText">Toutes les actions</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="actionDropdown">
                <a href="#" class="dropdown-item" data-action="">Toutes les actions</a>
            </div>
        </div>

        <div class="dropdown" style="flex: 1;">
            <button class="dropdown-toggle" id="periodFilter" style="width: 100%; justify-content: space-between;">
                <span id="periodFilterText">Toutes les périodes</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="periodDropdown">
                <a href="#" class="dropdown-item" data-period="all">Toutes les périodes</a>
                <a href="#" class="dropdown-item" data-period="today">Aujourd'hui</a>
                <a href="#" class="dropdown-item" data-period="week">Cette semaine</a>
                <a href="#" class="dropdown-item" data-period="month">Ce mois</a>
            </div>
        </div>

        <div class="dropdown" style="flex: 1;">
            <button class="dropdown-toggle" id="levelFilter" style="width: 100%; justify-content: space-between;">
                <span id="levelFilterText">Tous les niveaux</span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="levelDropdown">
                <a href="#" class="dropdown-item" data-level="">Tous les niveaux</a>
                <a href="#" class="dropdown-item" data-level="info">Info</a>
                <a href="#" class="dropdown-item" data-level="warning">Warning</a>
                <a href="#" class="dropdown-item" data-level="error">Error</a>
            </div>
        </div>
    </div>

    <!-- Liste des logs -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Logs d'activité système</div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">
                Total: <span id="totalEntries">0</span> entrées
            </div>
        </div>

        <div class="activity-feed" id="logsContainer">
            <!-- Les logs seront chargés ici -->
        </div>

        <!-- Pagination -->
        <div id="pagination" style="display: flex; justify-content: center; align-items: center; gap: 10px; padding: 20px;">
            <!-- Sera généré dynamiquement -->
        </div>
    </div>
    <script src="/js/admin/logs.js"></script>
</body>
</html>