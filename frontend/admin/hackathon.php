<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/hackaton.css">
    <link rel="stylesheet" href="/css/styles/admin/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
</head>
<body>
    <?php require_once '../includes/admin/header.php'; ?>

    <main>
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-laptop-code"></i> Gestion des Hackathons</h1>
                <p class="page-subtitle">Créez et gérez les hackathons de votre plateforme</p>
            </div>
            <button class="btn btn-primary" data-modal="newHackathonModal">
                <i class="fas fa-plus btn-icon"></i> Nouveau Hackathon
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-list"></i> Liste des hackathons</div>
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Rechercher un hackathon..." data-table="hackathonsTable">
                </div>
            </div>
            
            <div class="table-container">
                <table id="hackathonsTable">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Date</th>
                            <th>Participants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ESGIS Hackathon 2024</td>
                            <td>15 Mars 2024</td>
                            <td>120</td>
                            <td><span class="badge badge-primary"><i class="fas fa-calendar-alt"></i> À venir</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="1"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="1"><i class="fas fa-eye"></i> Voir détails</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="1"><i class="fas fa-trash"></i> Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Web Security Challenge</td>
                            <td>22 Avril 2024</td>
                            <td>85</td>
                            <td><span class="badge badge-primary"><i class="fas fa-calendar-alt"></i> À venir</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="2"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="2"><i class="fas fa-eye"></i> Voir détails</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="2"><i class="fas fa-trash"></i> Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Mobile App Innovation</td>
                            <td>10 Février 2024</td>
                            <td>75</td>
                            <td><span class="badge badge-success"><i class="fas fa-check-circle"></i> Terminé</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="3"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="3"><i class="fas fa-eye"></i> Voir détails</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="3"><i class="fas fa-trash"></i> Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-history"></i> Activités récentes</div>
            </div>
            
            <div class="activity-feed">
                <div class="activity-item">
                    <div class="activity-icon" style="background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Admin</div>
                        <div class="activity-subtitle">A créé un nouveau hackathon : ESGIS Hackathon 2024</div>
                    </div>
                    <div class="activity-time">
                        <i class="fas fa-clock"></i> Il y a 2 heures
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background-color: rgba(16, 185, 129, 0.2); color: #10b981;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Équipe CodeMasters</div>
                        <div class="activity-subtitle">S'est inscrite au hackathon ESGIS Hackathon 2024</div>
                        <div class="activity-subtitle">5 membres</div>
                    </div>
                    <div class="activity-time">
                        <i class="fas fa-clock"></i> Il y a 3 heures
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon" style="background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Jean Durand</div>
                        <div class="activity-subtitle">S'est connecté à la plateforme</div>
                    </div>
                    <div class="activity-time">
                        <i class="fas fa-clock"></i> Il y a 4 heures
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal pour nouveau hackathon -->
        <div id="newHackathonModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-plus-circle"></i> Nouveau Hackathon</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="hackathonName">Nom</label>
                            <div class="input-with-icon">
                                <i class="fas fa-tag input-icon"></i>
                                <input type="text" id="hackathonName" class="form-control" placeholder="Nom du hackathon" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Image/Bannière</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <div class="file-upload-text">Cliquez pour télécharger ou glissez-déposez</div>
                                <div class="file-upload-info">PNG, JPG ou WEBP (max. 2Mo)</div>
                                <input type="file" id="hackathonImage" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="date-input-group">
                            <div class="form-group">
                                <label for="hackathonStartDate">Date de début</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="date" id="hackathonStartDate" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="hackathonEndDate">Date de fin</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="date" id="hackathonEndDate" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hackathonLocation">Lieu</label>
                            <div class="input-with-icon">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input type="text" id="hackathonLocation" class="form-control" placeholder="Lieu du hackathon (en ligne ou physique)">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hackathonMaxParticipants">Nombre maximum de participants</label>
                            <div class="input-with-icon">
                                <i class="fas fa-users input-icon"></i>
                                <input type="number" id="hackathonMaxParticipants" class="form-control" min="1" placeholder="Nombre maximum de participants">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hackathonTeamSize">Taille des équipes</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user-friends input-icon"></i>
                                <input type="number" id="hackathonTeamSize" class="form-control" min="1" placeholder="Nombre de personnes par équipe">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hackathonDuration">Durée (en heures)</label>
                            <div class="input-with-icon">
                                <i class="fas fa-clock input-icon"></i>
                                <input type="number" id="hackathonDuration" class="form-control" min="1" placeholder="Durée du hackathon en heures">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hackathonPrizes">Prix/Récompenses</label>
                            <div class="input-with-icon">
                                <i class="fas fa-award input-icon"></i>
                                <textarea id="hackathonPrizes" class="form-control" rows="3" placeholder="Description des prix et récompenses"></textarea>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="hackathonDescription">Description</label>
                            <textarea id="hackathonDescription" class="form-control" rows="4" placeholder="Description détaillée du hackathon"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary modal-close"><i class="fas fa-times"></i> Annuler</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .page-header .btn {
                width: 100%;
            }
        }
        </style>
    </main>
</body>
</html>