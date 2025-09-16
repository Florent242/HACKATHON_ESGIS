<?php 
// Définir le titre de la page avant d'inclure le header
$pageTitle = 'Panneau d\'administration - Plateforme de Hackathon';

// Inclure le header qui contient la navigation
require_once '../includes/admin/header.php'; 
?>

<!-- Ajouter les styles CSS spécifiques à cette page -->
<link rel="stylesheet" href="/css/styles/admin/dashboard.css">

<div class="content-wrapper">
    <h1 class="page-title">Panneau d'administration</h1>
    <p class="page-subtitle">Gérez les hackathons, challenges, utilisateurs et ressources de la plateforme.</p>

    <h2 class="page-title">Gestion des Utilisateurs</h2>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Utilisateurs</h3>
                <div class="number">4</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <h3>Administrateurs</h3>
                <div class="number">1</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <h3>Utilisateurs Actifs</h3>
                <div class="number">3</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <h3>Utilisateurs Suspendus</h3>
                <div class="number">1</div>
            </div>
            <div class="stat-icon red">
                <i class="fas fa-user-slash"></i>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
        <div class="search-container" style="flex: 1; margin-right: 10px;">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Rechercher un utilisateur par nom ou email..." data-table="usersTable">
        </div>
        
        <button class="btn btn-primary">
            <i class="fas fa-envelope btn-icon"></i> Notification globale
        </button>
        
        <button class="btn btn-primary" style="margin-left: 10px;">
            <i class="fas fa-user-plus btn-icon"></i> Ajouter utilisateur
        </button>
    </div>

    <div class="card">
        <div class="table-container">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Lionel SISSO</td>
                        <td>sisso.lionel@esgis.bj</td>
                        <td><span class="badge badge-primary">Admin</span></td>
                        <td><span class="badge badge-success">Actif</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="1">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="1">Voir profil</a>
                                    <a href="#" class="dropdown-item action-button" data-action="suspend" data-id="1">Suspendre</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Marie Dupont</td>
                        <td>marie.dupont@example.com</td>
                        <td><span class="badge badge-info">Utilisateur</span></td>
                        <td><span class="badge badge-success">Actif</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="2">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="2">Voir profil</a>
                                    <a href="#" class="dropdown-item action-button" data-action="suspend" data-id="2">Suspendre</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Jean Martin</td>
                        <td>jean.martin@example.com</td>
                        <td><span class="badge badge-info">Utilisateur</span></td>
                        <td><span class="badge badge-warning">Suspendu</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="3">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="3">Voir profil</a>
                                    <a href="#" class="dropdown-item action-button" data-action="activate" data-id="3">Activer</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Sophie Laurent</td>
                        <td>sophie.laurent@example.com</td>
                        <td><span class="badge badge-info">Modérateur</span></td>
                        <td><span class="badge badge-success">Actif</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="4">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="4">Voir profil</a>
                                    <a href="#" class="dropdown-item action-button" data-action="suspend" data-id="4">Suspendre</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <div class="card-title">Activités récentes</div>
        </div>
        
        <div class="activity-feed">
            <div class="activity-item">
                <div class="activity-icon" style="background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                    <i class="fas fa-user"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">Lionel SISSO</div>
                    <div class="activity-subtitle">S'est connecté à la plateforme</div>
                </div>
                <div class="activity-time">
                    Il y a 1 heure
                </div>
            </div>
            
            <div class="activity-item">
                <div class="activity-icon" style="background-color: rgba(16, 185, 129, 0.2); color: #10b981;">
                    <i class="fas fa-file-code"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">Lionel SISSO</div>
                    <div class="activity-subtitle">A soumis une solution pour le challenge 'API Security'</div>
                </div>
                <div class="activity-time">
                    Il y a 2 heures
                </div>
            </div>
            
            <div class="activity-item">
                <div class="activity-icon" style="background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">Lionel SISSO</div>
                    <div class="activity-subtitle">S'est inscrit au hackathon ESGIS Hackathon 2024</div>
                </div>
                <div class="activity-time">
                    Il y a 1 jour
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <div class="card-title">Accès rapide</div>
        </div>
        
        <div class="quick-access">
            <a href="/admin/equipes" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>Équipes</div>
            </a>
            
            <a href="/admin/logs" class="quick-access-item">
                <div class="quick-access-icon">
                    <i class="fas fa-history"></i>
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
</div>

<!-- Scripts spécifiques à cette page -->
<script defer src="/js/admin/dashboard.js"></script>

<?php
// Nettoyer la notification après affichage si elle existe
if (isset($_SESSION['notification'])) {
    unset($_SESSION['notification']);
}
?>

</body>
</html>