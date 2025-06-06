<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>EsgisHub - Challenges</title>
        <link rel="stylesheet" href="/css/styles/admin/challenge.css">
        <link rel="stylesheet" href="/css/styles/admin/header.css">
        <link rel="stylesheet" href="/css/dist/output.css">
        <!-- Lucide Icons -->
        <script defer src="/js/admin/challenge.js"></script>
        <script src="https://unpkg.com/lucide@latest"></script>
    </head>
    <body>
        <!-- Header -->
        <?php require_once '../includes/admin/header.php'; ?>


        <h1 class="page-title">Gestion des Challenges</h1>

        <div class="card-header" style="justify-content: flex-end; margin-bottom: 20px;">
            <button class="btn btn-primary" data-modal="newChallengeModal">
                <i class="fas fa-plus btn-icon"></i> Nouveau Challenge
            </button>
        </div>

        <div class="card">
            <div class="table-container">
                <table id="challengesTable">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Difficulté</th>
                            <th>Participants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>API Security Challenge</td>
                            <td><span class="badge badge-danger"><i class="fas fa-fire"></i> Avancé</span></td>
                            <td>45</td>
                            <td><span class="badge badge-primary"><i class="fas fa-play-circle"></i> En cours</span></td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="1"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="1"><i class="fas fa-eye"></i> Voir détails</a>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="1"><i class="fas fa-trash"></i> Supprimer</a>
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
                <div class="card-title">Points des défis soumis</div>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Défi</th>
                            <th>Points</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pierre Martin</td>
                            <td>API Security Challenge</td>
                            <td><span class="badge badge-warning"><i class="fas fa-star"></i> 150</span></td>
                            <td>11/05/2024</td>
                        </tr>
                        <tr>
                            <td>Marie Dupont</td>
                            <td>Front-end Performance</td>
                            <td><span class="badge badge-warning"><i class="fas fa-star"></i> 100</span></td>
                            <td>10/05/2024</td>
                        </tr>
                        <tr>
                            <td>Jean Durand</td>
                            <td>Database Optimization</td>
                            <td><span class="badge badge-warning"><i class="fas fa-star"></i> 120</span></td>
                            <td>09/05/2024</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal pour nouveau challenge -->
        <div id="newChallengeModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-trophy"></i> Nouveau Challenge</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="challengeTitle">Titre</label>
                            <input type="text" id="challengeTitle" class="form-control" placeholder="Titre du challenge" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Image</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <div class="file-upload-text">Cliquez pour télécharger ou glissez-déposez</div>
                                <div class="file-upload-info">PNG, JPG ou WEBP (max. 2Mo)</div>
                                <input type="file" id="challengeImage" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="challengeCategory">Catégorie</label>
                            <div class="input-with-icon">
                                <i class="fas fa-tag input-icon"></i>
                                <select id="challengeCategory" class="form-control">
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="web">Développement Web</option>
                                    <option value="mobile">Développement Mobile</option>
                                    <option value="security">Sécurité</option>
                                    <option value="data">Science des Données</option>
                                    <option value="ai">Intelligence Artificielle</option>
                                    <option value="devops">DevOps</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="challengeDifficulty">Difficulté</label>
                            <div class="input-with-icon">
                                <i class="fas fa-signal input-icon"></i>
                                <select id="challengeDifficulty" class="form-control">
                                    <option value="">Sélectionner une difficulté</option>
                                    <option value="easy">Débutant</option>
                                    <option value="medium">Intermédiaire</option>
                                    <option value="hard">Avancé</option>
                                    <option value="expert">Expert</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="date-input-group">
                            <div class="form-group">
                                <label for="challengePoints">Points à gagner</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-star input-icon"></i>
                                    <input type="number" id="challengePoints" class="form-control" min="1" placeholder="Points à gagner">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="challengeDuration">Durée estimée (heures)</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-clock input-icon"></i>
                                    <input type="number" id="challengeDuration" class="form-control" min="1" placeholder="Durée estimée">
                                </div>
                            </div>
                        </div>
                        
                        <div class="date-input-group">
                            <div class="form-group">
                                <label for="challengeStartDate">Date de début</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="date" id="challengeStartDate" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="challengeEndDate">Date limite</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-calendar input-icon"></i>
                                    <input type="date" id="challengeEndDate" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="challengeSkills">Compétences requises</label>
                            <div class="input-with-icon">
                                <i class="fas fa-code input-icon"></i>
                                <input type="text" id="challengeSkills" class="form-control" placeholder="ex: React, Node.js, GraphQL (séparés par des virgules)">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="challengeDescription">Description</label>
                            <textarea id="challengeDescription" class="form-control" rows="4" placeholder="Description détaillée du challenge"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="challengeCriteria">Critères de validation</label>
                            <textarea id="challengeCriteria" class="form-control" rows="3" placeholder="Critères pour valider la soumission"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary modal-close"><i class="fas fa-times"></i> Annuler</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Créer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</body>
</html>