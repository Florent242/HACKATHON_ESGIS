<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Resources</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/ressources.css">
    <link rel="stylesheet" href="/css/styles/admin/header.css">
</head>
<body>
<?php require_once '../includes/admin/header.php'; ?>

    <main>
        <div class="page-header">
            <div>
                <h1 class="page-title"><i class="fas fa-book"></i> Gestion des Ressources</h1>
                <p class="page-subtitle">Gérez les ressources pédagogiques et documentations</p>
            </div>
            <button class="btn btn-primary" data-modal="newResourceModal">
                <i class="fas fa-plus btn-icon"></i> Nouvelle Ressource
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-list"></i> Liste des ressources</div>
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Rechercher une ressource..." data-table="resourcesTable">
                </div>
            </div>
            
            <div class="table-container">
                <table id="resourcesTable">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Auteur</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Introduction à React</td>
                            <td><span class="badge badge-info"><i class="fas fa-chalkboard-teacher"></i> Workshop</span></td>
                            <td>John Doe</td>
                            <td>01/03/2024</td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="1"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="1"><i class="fas fa-eye"></i> Voir détails</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item action-button" data-action="download" data-id="1"><i class="fas fa-download"></i> Télécharger</a>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="1"><i class="fas fa-trash"></i> Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Guide de sécurité API</td>
                            <td><span class="badge badge-primary"><i class="fas fa-file-pdf"></i> Document</span></td>
                            <td>Marie Dupont</td>
                            <td>15/02/2024</td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="2"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="2"><i class="fas fa-eye"></i> Voir détails</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item action-button" data-action="download" data-id="2"><i class="fas fa-download"></i> Télécharger</a>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="2"><i class="fas fa-trash"></i> Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>Tutoriel Docker</td>
                            <td><span class="badge badge-success"><i class="fas fa-video"></i> Vidéo</span></td>
                            <td>Pierre Martin</td>
                            <td>20/01/2024</td>
                            <td>
                                <div class="dropdown">
                                    <button class="dropdown-toggle">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a href="#" class="dropdown-item action-button" data-action="edit" data-id="3"><i class="fas fa-edit"></i> Modifier</a>
                                        <a href="#" class="dropdown-item action-button" data-action="view" data-id="3"><i class="fas fa-eye"></i> Voir détails</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="#" class="dropdown-item action-button" data-action="download" data-id="3"><i class="fas fa-download"></i> Télécharger</a>
                                        <a href="#" class="dropdown-item action-button" data-action="delete" data-id="3"><i class="fas fa-trash"></i> Supprimer</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal pour nouvelle ressource -->
        <div id="newResourceModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2><i class="fas fa-book"></i> Nouvelle Ressource</h2>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="resourceTitle">Titre</label>
                            <div class="input-with-icon">
                                <i class="fas fa-heading input-icon"></i>
                                <input type="text" id="resourceTitle" class="form-control" placeholder="Nom de la ressource" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="resourceType">Type</label>
                            <div class="input-with-icon">
                                <i class="fas fa-file-alt input-icon"></i>
                                <select id="resourceType" class="form-control">
                                    <option value="">Sélectionner un type</option>
                                    <option value="document">Document</option>
                                    <option value="video">Vidéo</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="tutorial">Tutoriel</option>
                                    <option value="tool">Outil</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Image</label>
                            <div class="file-upload">
                                <i class="fas fa-cloud-upload-alt file-upload-icon"></i>
                                <div class="file-upload-text">Cliquez pour télécharger ou glissez-déposez</div>
                                <div class="file-upload-info">PNG, JPG ou WEBP (max. 2Mo)</div>
                                <input type="file" id="resourceImage" accept="image/*">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="resourceDescription">Description</label>
                            <textarea id="resourceDescription" class="form-control" rows="4" placeholder="Description détaillée de la ressource"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="resourceUrl">Lien URL</label>
                            <div class="input-with-icon">
                                <i class="fas fa-link input-icon"></i>
                                <input type="url" id="resourceUrl" class="form-control" placeholder="https://exemple.com/ressource">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Fichier associé</label>
                            <div class="file-upload">
                                <i class="fas fa-file-upload file-upload-icon"></i>
                                <div class="file-upload-text">Choisir un fichier</div>
                                <div class="file-upload-info">PDF, DOCX, PPTX, etc. (max. 10Mo)</div>
                                <input type="file" id="resourceFile">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="resourcePublishDate">Date de publication</label>
                            <div class="input-with-icon">
                                <i class="fas fa-calendar input-icon"></i>
                                <input type="date" id="resourcePublishDate" class="form-control">
                            </div>
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