<html lang='fr'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once '../includes/admin/head.php'; ?>
    <title>Gestion des Équipes</title>
    <link rel="stylesheet" href="/css/styles/admin/equipes.css">
    <script defer src="/js/admin/equipes.js"></script>
</head>

<body>
    <?php require_once '../includes/admin/header.php'; ?>

    <h1 class="page-title">Gestion des Équipes</h1>

    <div class="card-header" style="justify-content: flex-end; margin-bottom: 20px;">
        <button class="btn btn-primary">
            <i class="fas fa-users btn-icon"></i> Créer une équipe
        </button>
    </div>

    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Total Équipes</h3>
                <div class="number">4</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Membres</h3>
                <div class="number">18</div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-user"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Participations</h3>
                <div class="number">8</div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-trophy"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Défis réalisés</h3>
                <div class="number">33</div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" class="search-input" placeholder="Rechercher une équipe..." data-table="teamsTable">
    </div>

    <div class="card">
        <div class="table-container">
            <table id="teamsTable">
                <thead>
                    <tr>
                        <th>Nom de l'équipe</th>
                        <th>Membres</th>
                        <th>Hackathons</th>
                        <th>Challenges</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>DevOps Masters</td>
                        <td><span class="badge badge-primary">5</span></td>
                        <td>2</td>
                        <td>7</td>
                        <td><span class="badge badge-success">Actif</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="1">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="1">Voir détails</a>
                                    <a href="#" class="dropdown-item action-button" data-action="delete" data-id="1">Supprimer</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Frontend Wizards</td>
                        <td><span class="badge badge-primary">3</span></td>
                        <td>1</td>
                        <td>5</td>
                        <td><span class="badge badge-success">Actif</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="2">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="2">Voir détails</a>
                                    <a href="#" class="dropdown-item action-button" data-action="delete" data-id="2">Supprimer</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Data Science Squad</td>
                        <td><span class="badge badge-primary">4</span></td>
                        <td>3</td>
                        <td>12</td>
                        <td><span class="badge badge-warning">Suspendu</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="3">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="3">Voir détails</a>
                                    <a href="#" class="dropdown-item action-button" data-action="activate" data-id="3">Activer</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Cloud Innovators</td>
                        <td><span class="badge badge-primary">6</span></td>
                        <td>2</td>
                        <td>9</td>
                        <td><span class="badge badge-success">Actif</span></td>
                        <td>
                            <div class="dropdown">
                                <button class="dropdown-toggle">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="#" class="dropdown-item action-button" data-action="edit" data-id="4">Modifier</a>
                                    <a href="#" class="dropdown-item action-button" data-action="view" data-id="4">Voir détails</a>
                                    <a href="#" class="dropdown-item action-button" data-action="delete" data-id="4">Supprimer</a>
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
            <div class="card-title">Équipes en vedette</div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding: 20px;">
            <div class="team-card" style="background-color: var(--background-lighter); border-radius: 0.5rem; padding: 20px;">
                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                        <i class="fas fa-users" style="color: #6d28d9;"></i>
                    </div>
                    <h3>DevOps Masters</h3>
                </div>
                <p>5 membres</p>

                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
                        <div style="font-weight: bold;">2</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
                        <div style="font-weight: bold;">7</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
                        <div style="font-weight: bold; color: #10b981;">Actif</div>
                    </div>
                </div>
            </div>

            <div class="team-card" style="background-color: var(--background-lighter); border-radius: 0.5rem; padding: 20px;">
                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                        <i class="fas fa-users" style="color: #6d28d9;"></i>
                    </div>
                    <h3>Frontend Wizards</h3>
                </div>
                <p>3 membres</p>

                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
                        <div style="font-weight: bold;">1</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
                        <div style="font-weight: bold;">5</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
                        <div style="font-weight: bold; color: #10b981;">Actif</div>
                    </div>
                </div>
            </div>

            <div class="team-card" style="background-color: var(--background-lighter); border-radius: 0.5rem; padding: 20px;">
                <div style="display: flex; align-items: center; margin-bottom: 15px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                        <i class="fas fa-users" style="color: #6d28d9;"></i>
                    </div>
                    <h3>Data Science Squad</h3>
                </div>
                <p>4 membres</p>

                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
                        <div style="font-weight: bold;">3</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
                        <div style="font-weight: bold;">12</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
                        <div style="font-weight: bold; color: #f59e0b;">Suspendu</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>