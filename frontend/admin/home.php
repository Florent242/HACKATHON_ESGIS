<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/home.css">
    <link rel="stylesheet" href="/css/styles/admin/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script defer src="/js/admin/home.js"></script>
    <script src="/js/lucide.min.js"></script>
</head>
<body>
    <!-- Navigation -->
    <?php require_once '../includes/admin/header.php'; ?>

    <div class="page-header">
        <div>
            <h1 class="page-title"><i class="fas fa-gauge-high"></i> Panneau d'administration</h1>
            <p class="page-subtitle">Gérez les hackathons, challenges, utilisateurs et ressources de la plateforme.</p>
        </div>
    </div>

    <!-- Conteneur d'erreur pour les notifications -->
    <div id="error-container" class="error-notification hidden"></div>

    <!-- Spinner de chargement global -->
    <div id="global-loading-spinner" class="loading-spinner hidden">
        <div class="spinner"></div>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Hackathons</h3>
                <div id="hackathons-count" class="number">0</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-laptop-code"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Challenges</h3>
                <div id="challenges-count" class="number">0</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-trophy"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Utilisateurs</h3>
                <div id="users-count" class="number">0</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Équipes</h3>
                <div id="teams-count" class="number">0</div>
            </div>
            <div class="stat-icon blue">
                <i class="fas fa-user-friends"></i>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 30px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-history"></i> Activité récente</div>
            </div>
            
            <div id="activity-feed" class="activity-feed">
                <!-- Les activités seront chargées dynamiquement ici -->
            </div>
            
            <!-- État vide pour les activités -->
            <div id="no-recent-activity" class="empty-state" style="display: none;">
                <div class="empty-state-icon">
                    <i data-lucide="activity"></i>
                </div>
                <div class="empty-state-text">
                    <h3>Aucune activité récente</h3>
                    <p>Les activités récentes apparaîtront ici.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-calendar-alt"></i> Hackathons à venir</div>
            </div>
            
            <div id="upcoming-hackathons" style="padding: 15px;">
                <!-- Les hackathons à venir seront chargés dynamiquement ici -->
            </div>
            
            <!-- État vide pour les hackathons -->
            <div id="no-upcoming-hackathons" class="empty-state" style="display: none; padding: 15px;">
                <div class="empty-state-icon">
                    <i data-lucide="calendar"></i>
                </div>
                <div class="empty-state-text">
                    <h3>Aucun hackathon à venir</h3>
                    <p>Les prochains hackathons apparaîtront ici.</p>
                    <a href="hackathons.php?action=create" class="btn btn-primary">Créer un hackathon</a>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-trophy"></i> Défis populaires</div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Participants</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody id="popular-challenges">
                        <!-- Les défis populaires seront chargés dynamiquement ici -->
                    </tbody>
                </table>
                
                <!-- État vide pour les défis -->
                <div id="no-popular-challenges" class="empty-state" style="display: none;">
                    <div class="empty-state-icon">
                        <i data-lucide="trophy"></i>
                    </div>
                    <div class="empty-state-text">
                        <h3>Aucun défi populaire</h3>
                        <p>Les défis populaires apparaîtront ici.</p>
                        <a href="challenges.php?action=create" class="btn btn-primary">Créer un défi</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-user-group"></i> Équipes actives</div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Membres</th>
                            <th>Défis complétés</th>
                        </tr>
                    </thead>
                    <tbody id="active-teams">
                        <!-- Les équipes actives seront chargées dynamiquement ici -->
                    </tbody>
                </table>
                
                <!-- État vide pour les équipes -->
                <div id="no-active-teams" class="empty-state" style="display: none;">
                    <div class="empty-state-icon">
                        <i data-lucide="users"></i>
                    </div>
                    <div class="empty-state-text">
                        <h3>Aucune équipe active</h3>
                        <p>Les équipes actives apparaîtront ici.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-bolt"></i> Accès rapide</div>
        </div>

        <div class="quick-access">
            <a href="/admin/hackathons" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-laptop-code"></i>
                </div>
                <div>Hackathons</div>
            </a>
            
            <a href="/admin/challenges" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div>Challenges</div>
            </a>
            
            <a href="/admin/utilisateurs" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>Utilisateurs</div>
            </a>
            
            <a href="/admin/equipes" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div>Équipes</div>
            </a>
            
            <a href="/admin/logs" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div>Logs</div>
            </a>
            
            <a href="/admin/soumissions" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div>Soumissions</div>
            </a>
        </div>
    </div>

    <!-- Bouton de rafraîchissement -->
    <div class="refresh-button-container">
        <button id="refresh-dashboard" class="btn btn-primary">
            <i class="fas fa-sync-alt"></i> Rafraîchir
        </button>
    </div>

    <style>
    .page-header {
        margin-bottom: 2rem;
    }

    .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 1000;
    }

    .loading-spinner .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .hidden {
        display: none !important;
    }

    .error-notification {
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        text-align: center;
    }

    .empty-state-icon {
        font-size: 2rem;
        color: #6c757d;
        margin-bottom: 15px;
    }

    .empty-state-text h3 {
        margin-bottom: 10px;
    }

    .refresh-button-container {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
        
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
    </style>
</body>
</html>