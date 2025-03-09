<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/admin/home.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/admin/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/admin/home.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Hackathons</h3>
                    <div class="number">3</div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-laptop-code"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Challenges</h3>
                    <div class="number">12</div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Utilisateurs</h3>
                    <div class="number">45</div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Équipes</h3>
                    <div class="number">8</div>
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
                            <i class="fas fa-clock"></i> Il y a 1 heure
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background-color: rgba(16, 185, 129, 0.2); color: #10b981;">
                            <i class="fas fa-file-code"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Marie Dupont</div>
                            <div class="activity-subtitle">A soumis une solution pour le challenge 'API Security'</div>
                        </div>
                        <div class="activity-time">
                            <i class="fas fa-clock"></i> Il y a 2 heures
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background-color: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Jean Martin</div>
                            <div class="activity-subtitle">A créé une nouvelle équipe 'CodeMasters'</div>
                        </div>
                        <div class="activity-time">
                            <i class="fas fa-clock"></i> Il y a 3 heures
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon" style="background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">Sophie Laurent</div>
                            <div class="activity-subtitle">S'est inscrite au hackathon 'ESGIS Hackathon 2024'</div>
                        </div>
                        <div class="activity-time">
                            <i class="fas fa-clock"></i> Il y a 1 jour
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i class="fas fa-calendar-alt"></i> Hackathons à venir</div>
                </div>
                
                <div style="padding: 15px;">
                    <div style="background-color: var(--background-lighter); border-radius: 0.75rem; padding: 15px; margin-bottom: 15px; transition: transform 0.2s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        <h3>ESGIS Hackathon 2024</h3>
                        <p style="color: var(--text-muted); font-size: 0.875rem;"><i class="fas fa-calendar"></i> 15 Mars 2024</p>
                        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                            <span><i class="fas fa-users"></i> 120 participants</span>
                            <span class="badge badge-primary"><i class="fas fa-hourglass-start"></i> À venir</span>
                        </div>
                    </div>
                    
                    <div style="background-color: var(--background-lighter); border-radius: 0.75rem; padding: 15px; transition: transform 0.2s ease; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                        <h3>Web Security Challenge</h3>
                        <p style="color: var(--text-muted); font-size: 0.875rem;"><i class="fas fa-calendar"></i> 22 Avril 2024</p>
                        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                            <span><i class="fas fa-users"></i> 85 participants</span>
                            <span class="badge badge-primary"><i class="fas fa-hourglass-start"></i> À venir</span>
                        </div>
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
                        <tbody>
                            <tr>
                                <td>API Security Challenge</td>
                                <td>45</td>
                                <td><span class="badge badge-primary"><i class="fas fa-play-circle"></i> En cours</span></td>
                            </tr>
                            <tr>
                                <td>Front-end Performance</td>
                                <td>38</td>
                                <td><span class="badge badge-primary"><i class="fas fa-play-circle"></i> En cours</span></td>
                            </tr>
                            <tr>
                                <td>Database Optimization</td>
                                <td>27</td>
                                <td><span class="badge badge-success"><i class="fas fa-check-circle"></i> Terminé</span></td>
                            </tr>
                        </tbody>
                    </table>
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
                        <tbody>
                            <tr>
                                <td>DevOps Masters</td>
                                <td>5</td>
                                <td>7</td>
                            </tr>
                            <tr>
                                <td>Frontend Wizards</td>
                                <td>3</td>
                                <td>5</td>
                            </tr>
                            <tr>
                                <td>Cloud Innovators</td>
                                <td>6</td>
                                <td>9</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-bolt"></i> Accès rapide</div>
            </div>

            <div class="quick-access">
                <a href="hackathons.php" class="quick-access-item">
                    <div class="quick-access-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <div>Hackathons</div>
                </a>
                
                <a href="challenges.php" class="quick-access-item">
                    <div class="quick-access-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div>Challenges</div>
                </a>
                
                <a href="utilisateurs.php" class="quick-access-item">
                    <div class="quick-access-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>Utilisateurs</div>
                </a>
                
                <a href="equipes.php" class="quick-access-item">
                    <div class="quick-access-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div>Équipes</div>
                </a>
                
                <a href="ressources.php" class="quick-access-item">
                    <div class="quick-access-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>Ressources</div>
                </a>
                
                <a href="soumissions.php" class="quick-access-item">
                    <div class="quick-access-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>Soumissions</div>
                </a>
            </div>
        </div>

        <style>
        .page-header {
            margin-bottom: 2rem;
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