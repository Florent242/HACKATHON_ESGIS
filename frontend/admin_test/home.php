<?php
$pageTitle = 'Panneau d\'administration';
$currentPage = 'dashboard';
include_once 'admin_test/header.php';
?>

<div class="container">
    <h1>Panneau d'administration</h1>
    <p class="mb-3">Gérez les hackathons, challenges, utilisateurs et ressources de la plateforme.</p>
    
    <div class="card mb-3">
        <div class="card-header">
            <h3>Navigation rapide</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-4">
                <a href="<?= BASE_URL ?>/hackathons" class="card">
                    <div class="d-flex align-center gap-2">
                        <div class="stat-icon icon-primary">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div>
                            <h3>Hackathons</h3>
                            <p>Gérer les événements</p>
                        </div>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/challenges" class="card">
                    <div class="d-flex align-center gap-2">
                        <div class="stat-icon icon-info">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div>
                            <h3>Challenges</h3>
                            <p>Gérer les défis</p>
                        </div>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/utilisateurs" class="card">
                    <div class="d-flex align-center gap-2">
                        <div class="stat-icon icon-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3>Utilisateurs</h3>
                            <p>Gérer les comptes</p>
                        </div>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/ressources" class="card">
                    <div class="d-flex align-center gap-2">
                        <div class="stat-icon icon-warning">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h3>Ressources</h3>
                            <p>Gérer les documents</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <div class="grid grid-2 mb-3">
        <div class="card">
            <div class="card-header">
                <h3>Activités récentes</h3>
            </div>
            <div class="card-body">
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon icon-primary">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Lionel SISSO</div>
                            <div class="activity-subtitle">S'est connecté à la plateforme</div>
                        </div>
                        <div class="activity-time">Il y a 1 heure</div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon icon-success">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Lionel SISSO</div>
                            <div class="activity-subtitle">A soumis une solution pour le challenge 'API Security'</div>
                        </div>
                        <div class="activity-time">Il y a 2 heures</div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon icon-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Jean Martin</div>
                            <div class="activity-subtitle">A créé une nouvelle équipe 'CodeMasters'</div>
                        </div>
                        <div class="activity-time">Il y a 3 heures</div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon icon-warning">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Sophie Laurent</div>
                            <div class="activity-subtitle">S'est inscrite au hackathon 'ESGIS Hackathon 2024'</div>
                        </div>
                        <div class="activity-time">Il y a 1 jour</div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="<?= BASE_URL ?>/logs" class="btn btn-sm">Voir tous les logs</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Statistiques</h3>
            </div>
            <div class="card-body">
                <div class="stats-container">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Hackathons</h3>
                            <div class="number">5</div>
                        </div>
                        <div class="stat-icon icon-primary">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Challenges</h3>
                            <div class="number">12</div>
                        </div>
                        <div class="stat-icon icon-info">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Utilisateurs</h3>
                            <div class="number">87</div>
                        </div>
                        <div class="stat-icon icon-success">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h3>Équipes</h3>
                            <div class="number">15</div>
                        </div>
                        <div class="stat-icon icon-warning">
                            <i class="fas fa-user-friends"></i>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-between align-center mt-3">
                    <div>
                        <h4>Soumissions récentes</h4>
                    </div>
                    <a href="<?= BASE_URL ?>/soumissions" class="btn btn-sm">Voir tout</a>
                </div>
                
                <div class="table-container mt-2">
                    <table>
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Challenge</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Pierre Martin</td>
                                <td>API Security Challenge</td>
                                <td>11/05/2024</td>
                                <td><span class="badge badge-success">Approuvé</span></td>
                            </tr>
                            <tr>
                                <td>Marie Dupont</td>
                                <td>Front-end Performance</td>
                                <td>10/05/2024</td>
                                <td><span class="badge badge-warning">En attente</span></td>
                            </tr>
                            <tr>
                                <td>Jean Durand</td>
                                <td>Database Optimization</td>
                                <td>09/05/2024</td>
                                <td><span class="badge badge-danger">Rejeté</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3>Accès rapide</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-3">
                <a href="<?= BASE_URL ?>/equipes" class="card">
                    <div class="text-center">
                        <div class="mb-2">
                            <i class="fas fa-user-friends fa-3x text-muted"></i>
                        </div>
                        <h3>Équipes</h3>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/logs" class="card">
                    <div class="text-center">
                        <div class="mb-2">
                            <i class="fas fa-history fa-3x text-muted"></i>
                        </div>
                        <h3>Logs</h3>
                    </div>
                </a>
                <a href="<?= BASE_URL ?>/soumissions" class="card">
                    <div class="text-center">
                        <div class="mb-2">
                            <i class="fas fa-file-code fa-3x text-muted"></i>
                        </div>
                        <h3>Soumissions</h3>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once 'admin_test/footer.php'; ?>