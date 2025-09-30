<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles/admin/validation.css">
    <?php 
    $pageTitle = 'Validation des Projets';
    require_once '../includes/admin/head.php'; 
    ?>
</head>

<body>

    <?php
    require_once '../includes/admin/header.php';
    ?>
    
    <div id="global-loading-spinner" class="loading-spinner" style="display: none;">
        <div class="spinner"></div>
    </div>

    <h1 class="page-title">Validation et Évaluation des Projets</h1>

    <!-- Filtres et actions -->
    <div class="card-header flex justify-between align-center mb-4">
        <div class="filters-container flex flex-wrap gap-2">
            <div class="filter-group">
                <label for="statusFilter" class="form-label small mb-1">Statut</label>
                <select id="statusFilter" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    <option value="submitted">Soumis - À évaluer</option>
                    <option value="in_evaluation">En cours d'évaluation</option>
                    <option value="validated">Validé</option>
                    <option value="rejected">Rejeté</option>
                    <option value="needs_revision">Révision demandée</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="hackathonFilter" class="form-label small mb-1">Hackathon</label>
                <select id="hackathonFilter" class="form-select form-select-sm">
                    <option value="">Tous les hackathons</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="priorityFilter" class="form-label small mb-1">Priorité</label>
                <select id="priorityFilter" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">Haute</option>
                    <option value="medium">Moyenne</option>
                    <option value="low">Basse</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="searchInput" class="form-label small mb-1">Recherche</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" class="form-control" placeholder="Nom du projet ou équipe...">
                </div>
            </div>
        </div>
        <div class="actions">
            <button id="exportBtn" class="btn btn-primary btn-sm">
                <i class="fas fa-download me-1"></i> Exporter Évaluations
            </button>
            <button id="refreshBtn" class="btn btn-outline-secondary btn-sm ms-2" title="Rafraîchir">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Statistiques de validation -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Projets à Évaluer</h3>
                <div class="number" id="pendingEvaluations">0</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Score Moyen</h3>
                <div class="number" id="averageScore">0/100</div>
            </div>
            <div class="stat-icon blue">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Projets Validés</h3>
                <div class="number" id="validatedProjects">0</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Taux de Validation</h3>
                <div class="number" id="validationRate">0%</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-percentage"></i>
            </div>
        </div>
    </div>

    <!-- Liste des projets à évaluer -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-tasks me-2"></i>
                Projets Soumis pour Évaluation
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%;">Projet</th>
                        <th style="width: 15%;">Équipe</th>
                        <th style="width: 15%;">Challenge</th>
                        <th style="width: 12%;">Livrables</th>
                        <th style="width: 10%;">Score Actuel</th>
                        <th style="width: 10%;">Statut</th>
                        <th style="width: 10%;">Date Soumission</th>
                        <th style="width: 8%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="projectsTable">
                    <!-- Les projets seront chargés ici -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal d'Évaluation Avancée -->
    <div id="evaluationModal" class="modal" style="display: none;">
        <div class="modal-content evaluation-modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-star me-2"></i>Évaluation du Projet</h2>
                <button class="close-modal" onclick="closeEvaluationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <!-- Informations du projet -->
                <div class="project-info-section">
                    <div class="project-header">
                        <div class="project-title">
                            <h3 id="projectTitle">Nom du Projet</h3>
                            <span class="team-badge" id="teamName">Équipe</span>
                        </div>
                        <div class="project-links">
                            <a href="#" id="repositoryLink" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fab fa-github me-1"></i>Repository
                            </a>
                            <a href="#" id="demoLink" class="btn btn-outline-success btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>Démo
                            </a>
                            <a href="#" id="downloadLink" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-download me-1"></i>Télécharger ZIP
                            </a>
                        </div>
                    </div>
                    <div class="project-description">
                        <p id="projectDescription">Description du projet...</p>
                    </div>
                </div>

                <!-- Critères d'évaluation -->
                <div class="evaluation-criteria-section">
                    <h4><i class="fas fa-clipboard-check me-2"></i>Critères d'Évaluation</h4>
                    
                    <div class="criteria-grid">
                        <!-- Innovation & Créativité -->
                        <div class="criterion-card">
                            <div class="criterion-header">
                                <h5><i class="fas fa-lightbulb me-2"></i>Innovation & Créativité</h5>
                                <span class="weight-badge">Poids: 25%</span>
                            </div>
                            <div class="criterion-content">
                                <div class="score-input-group">
                                    <input type="range" id="innovation_score" min="0" max="25" value="0" 
                                           class="score-slider" data-criterion="innovation">
                                    <div class="score-display">
                                        <span class="score-value">0</span>/25
                                    </div>
                                </div>
                                <div class="criterion-description">
                                    Originalité de l'approche, créativité de la solution, innovation technique
                                </div>
                            </div>
                        </div>

                        <!-- Qualité Technique -->
                        <div class="criterion-card">
                            <div class="criterion-header">
                                <h5><i class="fas fa-code me-2"></i>Qualité Technique</h5>
                                <span class="weight-badge">Poids: 30%</span>
                            </div>
                            <div class="criterion-content">
                                <div class="score-input-group">
                                    <input type="range" id="technical_score" min="0" max="30" value="0" 
                                           class="score-slider" data-criterion="technical">
                                    <div class="score-display">
                                        <span class="score-value">0</span>/30
                                    </div>
                                </div>
                                <div class="criterion-description">
                                    Propreté du code, architecture, bonnes pratiques, sécurité
                                </div>
                            </div>
                        </div>

                        <!-- Fonctionnalités -->
                        <div class="criterion-card">
                            <div class="criterion-header">
                                <h5><i class="fas fa-cogs me-2"></i>Fonctionnalités</h5>
                                <span class="weight-badge">Poids: 25%</span>
                            </div>
                            <div class="criterion-content">
                                <div class="score-input-group">
                                    <input type="range" id="functionality_score" min="0" max="25" value="0" 
                                           class="score-slider" data-criterion="functionality">
                                    <div class="score-display">
                                        <span class="score-value">0</span>/25
                                    </div>
                                </div>
                                <div class="criterion-description">
                                    Complétude des fonctionnalités, stabilité, performance
                                </div>
                            </div>
                        </div>

                        <!-- UI/UX & Présentation -->
                        <div class="criterion-card">
                            <div class="criterion-header">
                                <h5><i class="fas fa-palette me-2"></i>UI/UX & Présentation</h5>
                                <span class="weight-badge">Poids: 20%</span>
                            </div>
                            <div class="criterion-content">
                                <div class="score-input-group">
                                    <input type="range" id="presentation_score" min="0" max="20" value="0" 
                                           class="score-slider" data-criterion="presentation">
                                    <div class="score-display">
                                        <span class="score-value">0</span>/20
                                    </div>
                                </div>
                                <div class="criterion-description">
                                    Design, expérience utilisateur, facilité d'utilisation, documentation
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score Total -->
                <div class="total-score-section">
                    <div class="score-summary">
                        <div class="total-score-display">
                            <h3>Score Total: <span id="totalScore">0</span>/100</h3>
                            <div class="score-bar">
                                <div class="score-fill" id="scoreFill" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="score-grade">
                            <span class="grade-badge" id="gradeBadge">-</span>
                            <span class="grade-text" id="gradeText">Non évalué</span>
                        </div>
                    </div>
                </div>

                <!-- Commentaires -->
                <div class="comments-section">
                    <h4><i class="fas fa-comments me-2"></i>Commentaires du Jury</h4>
                    <div class="comment-tabs">
                        <button class="tab-btn active" data-tab="strengths">
                            <i class="fas fa-thumbs-up me-1"></i>Points Forts
                        </button>
                        <button class="tab-btn" data-tab="improvements">
                            <i class="fas fa-exclamation-triangle me-1"></i>Améliorations
                        </button>
                        <button class="tab-btn" data-tab="general">
                            <i class="fas fa-comment me-1"></i>Commentaire Général
                        </button>
                    </div>
                    
                    <div class="comment-content">
                        <div class="tab-panel active" id="strengths-panel">
                            <textarea id="strengthsComment" rows="3" 
                                      placeholder="Décrivez les points forts du projet..."
                                      class="form-control"></textarea>
                        </div>
                        <div class="tab-panel" id="improvements-panel">
                            <textarea id="improvementsComment" rows="3" 
                                      placeholder="Suggérez des améliorations..."
                                      class="form-control"></textarea>
                        </div>
                        <div class="tab-panel" id="general-panel">
                            <textarea id="generalComment" rows="4" 
                                      placeholder="Commentaire général sur le projet..."
                                      class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions de validation -->
            <div class="modal-footer evaluation-footer">
                <div class="validation-actions">
                    <button id="validateProjectBtn" class="btn btn-success btn-lg">
                        <i class="fas fa-check me-2"></i>Valider le Projet
                    </button>
                    <button id="requestRevisionBtn" class="btn btn-warning btn-lg">
                        <i class="fas fa-edit me-2"></i>Demander une Révision
                    </button>
                    <button id="rejectProjectBtn" class="btn btn-danger btn-lg">
                        <i class="fas fa-times me-2"></i>Rejeter le Projet
                    </button>
                </div>
                <button id="closeEvaluationBtn" class="btn btn-secondary">Fermer sans sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div id="confirmationModal" class="modal" style="display: none;">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <h3 id="confirmationTitle">Confirmer l'action</h3>
                <button class="close-modal" onclick="closeConfirmationModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmationMessage">Êtes-vous sûr de vouloir effectuer cette action ?</p>
                <div id="confirmationDetails" class="confirmation-details"></div>
            </div>
            <div class="modal-footer">
                <button id="confirmActionBtn" class="btn btn-primary">Confirmer</button>
                <button onclick="closeConfirmationModal()" class="btn btn-secondary">Annuler</button>
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
    <script defer src="/js/admin/validation.js"></script>

</body>

</html>