<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles/admin/soumissions.css">
    <?php require_once '../includes/admin/head.php'; ?>
</head>

<body>

    <?php
    require_once '../includes/admin/header.php';

    ?>
    <div id="global-loading-spinner" class="loading-spinner" style="display: none;">
        <div class="spinner"></div>
    </div>

    <h1 class="page-title">Gestion des Soumissions</h1>

    <div class="card-header flex justify-between align-center mb-4">
        <div class="filters-container flex flex-wrap gap-2">
            <div class="filter-group">
                <label for="statusFilter" class="form-label small mb-1">Statut</label>
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="completed">Complété</option>
                    <option value="approved">Validé</option>
                    <option value="rejected">Rejeté</option>
                    <option value="error">Erreur</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="hackathonFilter" class="form-label small mb-1">Hackathon</label>
                <select id="hackathonFilter" class="form-select form-select-sm">
                    <option value="">Tous les hackathons</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="difficultyFilter" class="form-label small mb-1">Difficulté</label>
                <select id="difficultyFilter" class="form-select form-select-sm">
                    <option value="">Tous niveaux</option>
                    <option value="facile">Facile</option>
                    <option value="moyen">Moyen</option>
                    <option value="difficile">Difficile</option>
                    <option value="expert">Expert</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchInput" class="form-label small mb-1">Recherche</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                </div>
            </div>
        </div>
        <div class="actions">
            <button id="exportBtn" class="btn btn-primary btn-sm">
                <i class="fas fa-download me-1"></i> Exporter
            </button>
            <button id="refreshBtn" class="btn btn-outline-secondary btn-sm ms-2" title="Rafraîchir">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Soumissions</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Points attribués</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-star"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>En attente</h3>
                <div class="number">0</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Taux d'approbation</h3>
                <div class="number">0%</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="Rechercher une soumission...">
    </div>

    <div class="filter-container" style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
        <select id="statusFilter" class="dropdown-toggle" style="width: auto;">
            <option value="all">Tous les statuts</option>
            <option value="approved">Approuvé</option>
            <option value="pending">En attente</option>
            <option value="rejected">Rejeté</option>
        </select>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 15%;">Utilisateur</th>
                        <th style="width: 20%;">Challenge</th>
                        <th style="width: 10%;">Difficulté</th>
                        <th style="width: 15%;">Résultats</th>
                        <th style="width: 12%;">Performance</th>
                        <th style="width: 10%;">Statut</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 8%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="submissionsTable">
                    <!-- Les soumissions seront chargées ici -->
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <div class="card-title">Détails des soumissions récentes</div>
        </div>

        <div id="recentSubmissions" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px;">
            <div class="submission-detail">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="empty-state-text">
                        <h3>Chargement...</h3>
                    </div>
                </div>
            </div>

            <div class="submission-detail">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="empty-state-text">
                        <h3>Chargement...</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les détails d'une soumission -->
    <div id="submissionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails de la soumission</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="submissionDetails">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div class="empty-state-text">
                            <h3>Chargement des détails...</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="approveBtn" class="btn btn-secondary" style="display: none;">Approuver</button>
                <button id="rejectBtn" class="btn btn-danger" style="display: none;">Rejeter</button>
                <button id="closeModalBtn" class="btn btn-primary">Fermer</button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification" class="notification" style="display: none;">
        <div class="notification-content">
            <span id="notificationMessage"></span>
            <button class="notification-close">&times;</button>
        </div>
    </div>

    <!-- Inclure le JavaScript -->
    <script defer src="/js/admin/soumissions.js"></script>

</body>

</html>