<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <div id="evaluationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4" style="display: none; z-index: 9999;">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[85vh] overflow-hidden border border-gray-200">
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center" style="position: sticky; top: 0; z-index: 10;">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-clipboard-check mr-3 text-blue-600"></i>
                    Évaluation du Projet
                </h2>
                <button id="closeModalBtn" class="text-gray-500 hover:text-gray-700 transition-colors text-xl font-bold hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center" onclick="closeEvaluationModal()">
                    &times;
                </button>
            </div>
            <div class="overflow-y-auto" style="max-height: calc(85vh - 140px);">
                <!-- Informations du projet -->
                <div class="bg-gray-50 p-6 border-b border-gray-200">
                    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start space-y-4 lg:space-y-0">
                        <div class="flex-1">
                            <h3 id="projectTitle" class="text-xl font-bold text-gray-900 mb-3">Nom du Projet</h3>
                            <span id="teamName" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-md text-sm font-medium">
                                <i class="fas fa-users mr-2"></i>Équipe
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="#" id="repositoryLink" class="inline-flex items-center px-3 py-1 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700 transition-colors" target="_blank" style="display: none;">
                                <i class="fab fa-github mr-2"></i><span>Repository</span>
                            </a>
                            <a href="#" id="demoLink" class="inline-flex items-center px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700 transition-colors" target="_blank" style="display: none;">
                                <i class="fas fa-external-link-alt mr-2"></i><span>Voir la Démo</span>
                            </a>
                            <a href="#" id="downloadLink" class="inline-flex items-center px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition-colors" style="display: none;">
                                <i class="fas fa-download mr-2"></i><span>Télécharger ZIP</span>
                            </a>
                            <div id="noLinksMessage" class="inline-flex items-center px-3 py-1 bg-gray-200 text-gray-600 rounded-md text-sm" style="display: none;">
                                <i class="fas fa-info-circle mr-2"></i><span>Aucun lien disponible</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 p-4 bg-white rounded-lg border border-gray-300">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Description:</h4>
                        <p id="projectDescription" class="text-gray-600 text-sm leading-relaxed">Description du projet...</p>
                    </div>
                </div>

                <!-- Critères d'évaluation -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center border-b border-gray-200 pb-3">
                        <i class="fas fa-star mr-2 text-blue-600"></i>
                        Critères d'Évaluation
                    </h4>
                    
                    <div class="grid gap-4 md:grid-cols-2">
                        <!-- Innovation & Créativité -->
                        <div class="bg-white border border-gray-200 rounded-lg p-5 hover:shadow-sm transition-shadow">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-base font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-lightbulb mr-2 text-amber-500"></i>
                                    Innovation & Créativité
                                </h5>
                                <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded text-xs font-medium">
                                    25 pts
                                </span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <input type="range" id="innovation_score" min="0" max="25" value="0" 
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer mr-3 focus:outline-none focus:ring-2 focus:ring-amber-500 score-slider" 
                                           data-criterion="innovation"
                                           style="background: linear-gradient(to right, #f59e0b 0%, #f59e0b 0%, #e5e7eb 0%, #e5e7eb 100%);">
                                    <div class="bg-amber-50 border border-amber-200 rounded px-2 py-1 font-semibold text-amber-700 text-sm min-w-[50px] text-center">
                                        <span class="score-value">0</span>/25
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                    Originalité, créativité de la solution, innovation technique
                                </p>
                            </div>
                        </div>

                        <!-- Qualité Technique -->
                        <div class="bg-white border border-gray-200 rounded-lg p-5 hover:shadow-sm transition-shadow">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-base font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-code mr-2 text-blue-600"></i>
                                    Qualité Technique
                                </h5>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                    30 pts
                                </span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <input type="range" id="technical_score" min="0" max="30" value="0" 
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer mr-3 focus:outline-none focus:ring-2 focus:ring-blue-500 score-slider" 
                                           data-criterion="technical"
                                           style="background: linear-gradient(to right, #3b82f6 0%, #3b82f6 0%, #e5e7eb 0%, #e5e7eb 100%);">
                                    <div class="bg-blue-50 border border-blue-200 rounded px-2 py-1 font-semibold text-blue-700 text-sm min-w-[50px] text-center">
                                        <span class="score-value">0</span>/30
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                    Propreté du code, architecture, bonnes pratiques, sécurité
                                </p>
                            </div>
                        </div>

                        <!-- Fonctionnalités -->
                        <div class="bg-white border border-gray-200 rounded-lg p-5 hover:shadow-sm transition-shadow">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-base font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-cogs mr-2 text-green-600"></i>
                                    Fonctionnalités
                                </h5>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                                    25 pts
                                </span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <input type="range" id="functionality_score" min="0" max="25" value="0" 
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer mr-3 focus:outline-none focus:ring-2 focus:ring-green-500 score-slider" 
                                           data-criterion="functionality"
                                           style="background: linear-gradient(to right, #10b981 0%, #10b981 0%, #e5e7eb 0%, #e5e7eb 100%);">
                                    <div class="bg-green-50 border border-green-200 rounded px-2 py-1 font-semibold text-green-700 text-sm min-w-[50px] text-center">
                                        <span class="score-value">0</span>/25
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                    Complétude des fonctionnalités, stabilité, performance
                                </p>
                            </div>
                        </div>

                        <!-- UI/UX & Présentation -->
                        <div class="bg-white border border-gray-200 rounded-lg p-5 hover:shadow-sm transition-shadow">
                            <div class="flex justify-between items-center mb-4">
                                <h5 class="text-base font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-palette mr-2 text-purple-600"></i>
                                    UI/UX & Présentation
                                </h5>
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs font-medium">
                                    20 pts
                                </span>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <input type="range" id="presentation_score" min="0" max="20" value="0" 
                                           class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer mr-3 focus:outline-none focus:ring-2 focus:ring-purple-500 score-slider" 
                                           data-criterion="presentation"
                                           style="background: linear-gradient(to right, #8b5cf6 0%, #8b5cf6 0%, #e5e7eb 0%, #e5e7eb 100%);">
                                    <div class="bg-purple-50 border border-purple-200 rounded px-2 py-1 font-semibold text-purple-700 text-sm min-w-[50px] text-center">
                                        <span class="score-value">0</span>/20
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 bg-gray-50 p-2 rounded">
                                    Design, expérience utilisateur, facilité d'utilisation, documentation
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score Total -->
                <div class="bg-gray-50 border-t border-b border-gray-200 px-6 py-6">
                    <div class="max-w-xl mx-auto">
                        <div class="text-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">
                                Score Total: <span id="totalScore" class="text-blue-600 font-bold">0</span><span class="text-gray-600">/100</span>
                            </h3>
                            <div class="relative h-3 bg-gray-200 rounded-full overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-red-500 via-yellow-500 via-green-500 to-blue-600 rounded-full transition-all duration-300" 
                                     id="scoreFill" style="width: 0%;"></div>
                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="text-center">
                                <span class="inline-flex items-center justify-center w-12 h-12 rounded-full text-lg font-bold text-white bg-gray-400" id="gradeBadge">-</span>
                                <p class="text-xs font-medium text-gray-600 mt-1" id="gradeText">Non évalué</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commentaires -->
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                        <i class="fas fa-comments mr-2 text-blue-600"></i>Commentaires du Jury
                    </h4>
                    <div class="flex border-b border-gray-200 mb-4">
                        <button class="tab-btn px-4 py-2 text-sm font-medium text-green-600 border-b-2 border-green-600 bg-green-50" data-tab="strengths">
                            <i class="fas fa-thumbs-up mr-1"></i>Points Forts
                        </button>
                        <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-orange-600 hover:bg-orange-50" data-tab="improvements">
                            <i class="fas fa-exclamation-triangle mr-1"></i>Améliorations
                        </button>
                        <button class="tab-btn px-4 py-2 text-sm font-medium text-gray-600 hover:text-blue-600 hover:bg-blue-50" data-tab="general">
                            <i class="fas fa-comment mr-1"></i>Commentaire Général
                        </button>
                    </div>
                    
                    <div class="comment-content">
                        <div class="tab-panel block" id="strengths-panel">
                            <textarea id="strengthsComment" rows="3" 
                                      placeholder="Décrivez les points forts du projet..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"></textarea>
                        </div>
                        <div class="tab-panel hidden" id="improvements-panel">
                            <textarea id="improvementsComment" rows="3" 
                                      placeholder="Suggérez des améliorations..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"></textarea>
                        </div>
                        <div class="tab-panel hidden" id="general-panel">
                            <textarea id="generalComment" rows="4" 
                                      placeholder="Commentaire général sur le projet..."
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions de validation -->
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4 flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0" style="position: sticky; bottom: 0; z-index: 10;">
                <div class="flex flex-wrap gap-2">
                    <button id="validateProjectBtn" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                        <i class="fas fa-check mr-2"></i>Valider le Projet
                    </button>
                    <button id="requestRevisionBtn" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-md hover:bg-orange-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i>Demander une Révision
                    </button>
                    <button id="rejectProjectBtn" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-times mr-2"></i>Rejeter le Projet
                    </button>
                </div>
                <button id="closeEvaluationBtn" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 transition-colors">
                    Fermer sans sauvegarder
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4" style="display: none; z-index: 10000;">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-md border border-gray-200">
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center rounded-t-lg">
                <h3 id="confirmationTitle" class="text-lg font-semibold text-gray-800">Confirmer l'action</h3>
                <button class="text-gray-500 hover:text-gray-700 transition-colors text-xl font-bold hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center" onclick="closeConfirmationModal()">
                    &times;
                </button>
            </div>
            <div class="px-6 py-4">
                <p id="confirmationMessage" class="text-gray-700 mb-4">Êtes-vous sûr de vouloir effectuer cette action ?</p>
                <div id="confirmationDetails" class="bg-gray-50 p-3 rounded-md text-sm text-gray-600"></div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 rounded-b-lg">
                <button onclick="closeConfirmationModal()" class="px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-400 transition-colors">
                    Annuler
                </button>
                <button id="confirmActionBtn" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                    Confirmer
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notification" class="fixed top-4 right-4 max-w-sm rounded-lg shadow-lg border border-gray-200 bg-white" style="display: none; z-index: 10001;">
        <div class="flex items-center justify-between p-4">
            <span id="notificationMessage" class="text-sm font-medium text-gray-800"></span>
            <button class="notification-close text-gray-400 hover:text-gray-600 ml-4 text-lg font-bold">
                &times;
            </button>
        </div>
    </div>

    <!-- Inclure le JavaScript -->
    <script defer src="/js/admin/validation.js"></script>

</body>

</html>