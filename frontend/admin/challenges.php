<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Gestion des Challenges</title>
    <link rel="stylesheet" href="/css/styles/admin/challenges.css">
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/paraiso-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/python/python.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/clike/clike.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/xml/xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/ruby/ruby.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/shell/shell.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/pascal/pascal.min.js"></script>

    <script defer src="/js/admin/challenges.js"></script>
</head>

<body>
    <!-- Header -->
    <?php require_once '../includes/admin/header.php'; ?>

    <!-- Loading Spinner -->
    <div id="global-loading-spinner" class="loading-spinner" style="display: none;">
        <div class="spinner"></div>
        <p>Chargement...</p>
    </div>

    <div class="container">
        <!-- En-tête de la page -->
        <div class="page-header">
            <div class="page-title-section">
                <h1 class="page-title">
                    <i class="fas fa-trophy"></i>
                    Gestion des Challenges
                </h1>
                <p class="page-subtitle">Créez et gérez tous vos challenges de hackathon</p>
            </div>
            <div class="page-actions">
                <button class="btn btn-primary" data-modal="newChallengeModal">
                    <i class="fas fa-plus"></i>
                    Nouveau Challenge
                </button>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="totalChallenges">0</div>
                    <div class="stat-label">Total Challenges</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="activeChallenges">0</div>
                    <div class="stat-label">Challenges Actifs</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="totalParticipants">0</div>
                    <div class="stat-label">Participants</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number" id="totalPoints">0</div>
                    <div class="stat-label">Points Distribués</div>
                </div>
            </div>
        </div>

        <!-- Filtres et recherche -->
        <div class="filters-section">
            <div class="filters-row">
                <div class="search-box">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="search-input" placeholder="Rechercher un challenge...">
                </div>
                <div class="filters-group">
                    <select class="filter-select" id="typeFilter">
                        <option value="">Tous les types</option>
                        <option value="ctf">CTF</option>
                        <option value="dev">Développement</option>
                        <option value="project">Projet</option>
                        <option value="finale">Finale</option>
                    </select>
                    <select class="filter-select" id="difficultyFilter">
                        <option value="">Toutes difficultés</option>
                        <option value="easy">Facile</option>
                        <option value="medium">Moyen</option>
                        <option value="hard">Difficile</option>
                    </select>
                    <select class="filter-select" id="statusFilter">
                        <option value="">Tous les statuts</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Tableau des challenges -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Liste des Challenges</h3>
                <div class="card-actions">
                    <button class="btn btn-secondary" id="exportBtn">
                        <i class="fas fa-download"></i>
                        Exporter
                    </button>
                </div>
            </div>
            <div class="table-container">
                <table id="challengesTable" class="data-table">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" class="checkbox-input">
                            </th>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Difficulté</th>
                            <th>Points</th>
                            <th>Participants</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="challengesTableBody">
                        <!-- Les données seront chargées dynamiquement -->
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <div class="pagination-info">
                    <span id="paginationInfo">Affichage de 0 à 0 sur 0 résultats</span>
                </div>
                <div class="pagination-controls">
                    <button class="btn btn-sm btn-secondary" id="prevPage" disabled>
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="currentPage">1</span> / <span id="totalPages">1</span>
                    <button class="btn btn-sm btn-secondary" id="nextPage">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de création/édition de challenge -->
    <div id="challengeModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2 id="modalTitle">
                    <i class="fas fa-trophy"></i>
                    <span id="modalTitleText">Nouveau Challenge</span>
                </h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="challengeForm" class="challenge-form" novalidate>
                    <input type="hidden" id="challengeId" name="id">
                    <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <!-- Onglets -->
                    <div class="form-tabs">
                        <button type="button" class="tab-button active" data-tab="general">
                            <i class="fas fa-info-circle"></i>
                            Général
                        </button>
                        <button type="button" class="tab-button" data-tab="content">
                            <i class="fas fa-file-alt"></i>
                            Contenu
                        </button>
                        <button type="button" class="tab-button" data-tab="configuration">
                            <i class="fas fa-cogs"></i>
                            Configuration
                        </button>
                        <button type="button" class="tab-button" data-tab="flags" style="display: none;">
                            <i class="fas fa-flag"></i>
                            Flags
                        </button>
                        <button type="button" class="tab-button" data-tab="code" style="display: none;">
                            <i class="fas fa-code"></i>
                            Code & Tests
                        </button>
                        <button type="button" class="tab-button" data-tab="technologies" style="display: none;">
                            <i class="fas fa-tools"></i>
                            Technologies
                        </button>
                    </div>

                    <!-- Onglet Général -->
                    <div class="tab-content active" id="generalTab">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Titre *</label>
                                <input type="text" id="title" name="title" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="type">Type *</label>
                                <select id="type" name="type" class="form-control" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="ctf">CTF (Capture The Flag)</option>
                                    <option value="dev">Développement</option>
                                    <option value="finale">Finale</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Catégorie</label>
                                <select id="category" name="category" class="form-control">
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="project">Projet</option>
                                    <option value="web">Web</option>
                                    <option value="crypto">Cryptographie</option>
                                    <option value="forensics">Forensics</option>
                                    <option value="reverse">Reverse Engineering</option>
                                    <option value="osint">OSINT</option>
                                    <option value="stegano">Stéganographie</option>
                                    <option value="pwn">Pwn</option>
                                    <option value="algo">Algorithmique</option>
                                    <option value="mobile">Mobile</option>
                                    <option value="ai">Intelligence Artificielle</option>
                                    <option value="data">Science des Données</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="difficulty">Difficulté *</label>
                                <select id="difficulty" name="difficulty" class="form-control" required>
                                    <option value="">Sélectionner une difficulté</option>
                                    <option value="easy">Facile</option>
                                    <option value="medium">Moyen</option>
                                    <option value="hard">Difficile</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="hackathon_id">Hackathon *</label>
                                <select id="hackathon_id" name="hackathon_id" class="form-control" required>
                                    <option value="">Sélectionner un hackathon</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="phase_id">Phase *</label>
                                <select id="phase_id" name="phase_id" class="form-control" required>
                                    <option value="">Sélectionner une phase</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="points">Points *</label>
                                <input type="number" id="points" name="points" class="form-control" min="1" max="1000" required>
                            </div>
                            <div class="form-group">
                                <label for="is_active">Statut</label>
                                <select id="is_active" name="is_active" class="form-control">
                                    <option value="1">Actif</option>
                                    <option value="0">Inactif</option>
                                </select>
                            </div>
                        </div>

                        <!-- autheur -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="created_by">Auteur *</label>
                                <input type="text" id="created_by" name="created_by" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Contenu -->
                    <div class="tab-content" id="contentTab">
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="form-control" rows="6" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="instructions">Instructions (Dev uniquement)</label>
                            <textarea id="instructions" name="instructions" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="url_path">URL du challenge (CTF uniquement)</label>
                                <input type="text" id="url_path" name="url_path" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="resource_link">Lien de ressource (CTF uniquement)</label>
                                <input type="text" id="resource_link" name="resource_link" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="hint">Indices (JSON)</label>
                            <textarea id="hint" name="hint" class="form-control" rows="3" placeholder='["Indice 1", "Indice 2"]'></textarea>
                        </div>
                    </div>

                    <!-- Onglet Configuration -->
                    <div class="tab-content" id="configurationTab">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="is_dynamic">Scoring dynamique (CTF uniquement)</label>
                                <select id="is_dynamic" name="is_dynamic" class="form-control">
                                    <option value="0">Non</option>
                                    <option value="1">Oui</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="algo_config">Configuration Algo (JSON) (Algo uniquement)</label>
                                <textarea id="algo_config" name="algo_config" class="form-control" rows="4" placeholder='{"time_limit": 2000, "memory_limit": 128, "allowed_languages": ["python", "java", "cpp"]}'></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Flags (CTF uniquement) -->
                    <div class="tab-content" id="flagsTab">
                        <div class="flags-section">
                            <div class="flags-header">
                                <h4>Flags du Challenge</h4>
                                <button type="button" class="btn btn-sm btn-primary" id="addFlagBtn">
                                    <i class="fas fa-plus"></i>
                                    Ajouter un flag
                                </button>
                            </div>
                            <div id="flagsContainer">
                                <!-- Les flags seront ajoutés dynamiquement -->
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Code & Tests (Algo uniquement) -->
                    <div class="tab-content" id="codeTab">
                        <div class="code-section">
                            <div class="snippets-section">
                                <h4>Templates de Code</h4>
                                <div class="snippets-tabs">
                                    <button type="button" class="snippet-tab active" data-language="python">Python</button>
                                    <button type="button" class="snippet-tab" data-language="bash">Bash</button>
                                    <button type="button" class="snippet-tab" data-language="javascript">JavaScript</button>
                                    <button type="button" class="snippet-tab" data-language="cpp">C++</button>
                                    <button type="button" class="snippet-tab" data-language="c">C</button>
                                    <button type="button" class="snippet-tab" data-language="csharp">C#</button>
                                    <button type="button" class="snippet-tab" data-language="php">PHP</button>
                                    <button type="button" class="snippet-tab" data-language="ruby">Ruby</button>
                                    <button type="button" class="snippet-tab" data-language="pascal">Pascal</button>
                                    <button type="button" class="snippet-tab" data-language="typescript">TypeScript</button>
                                </div>
                                <div class="snippets-content">
                                    <textarea id="python_snippet" name="snippets[python]" class="code-editor" data-language="python"></textarea>
                                    <textarea id="bash_snippet" name="snippets[bash]" class="code-editor" data-language="bash" style="display: none;"></textarea>
                                    <textarea id="javascript_snippet" name="snippets[javascript]" class="code-editor" data-language="javascript" style="display: none;"></textarea>
                                    <textarea id="cpp_snippet" name="snippets[cpp]" class="code-editor" data-language="cpp" style="display: none;"></textarea>
                                    <textarea id="c_snippet" name="snippets[c]" class="code-editor" data-language="c" style="display: none;"></textarea>
                                    <textarea id="csharp_snippet" name="snippets[csharp]" class="code-editor" data-language="csharp" style="display: none;"></textarea>
                                    <textarea id="php_snippet" name="snippets[php]" class="code-editor" data-language="php" style="display: none;"></textarea>
                                    <textarea id="ruby_snippet" name="snippets[ruby]" class="code-editor" data-language="ruby" style="display: none;"></textarea>
                                    <textarea id="pascal_snippet" name="snippets[pascal]" class="code-editor" data-language="pascal" style="display: none;"></textarea>
                                    <textarea id="typescript_snippet" name="snippets[typescript]" class="code-editor" data-language="typescript" style="display: none;"></textarea>
                                </div>
                            </div>
                            <div class="tests-section">
                                <h4>Cas de Test</h4>
                                <button type="button" class="btn btn-sm btn-primary" id="addTestBtn">
                                    <i class="fas fa-plus"></i>
                                    Ajouter un test
                                </button>
                                <div id="testsContainer">
                                    <!-- Les tests seront ajoutés dynamiquement -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Technologies (Dev uniquement) -->
                    <div class="tab-content" id="technologiesTab">
                        <div class="technologies-section">
                            <h4>Technologies Requises</h4>
                            <div id="technologiesContainer">
                                <!-- Les technologies seront ajoutées dynamiquement -->
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" id="addTechnologyBtn">
                                <i class="fas fa-plus"></i>
                                Ajouter une technologie
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="display:flex; gap:8px; justify-content:space-between; align-items:center;">
                <button type="button" class="btn btn-secondary modal-closed">
                    <i class="fas fa-times"></i>
                    Annuler
                </button>
                <div class="wizard-actions" style="display:flex; gap:8px; align-items:center;">
                    <button type="button" class="btn btn-secondary" id="wizardPrev" disabled>
                        <i class="fas fa-chevron-left"></i>
                        Précédent
                    </button>
                    <div class="flex flex-row gap-2">
                        <button type="button" class="btn btn-primary" id="wizardNext">
                            Suivant
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <button type="submit" form="challengeForm" class="btn btn-success" id="wizardSubmit" style="display:none;">
                            <i class="fas fa-save"></i>
                            <span id="saveButtonText">Créer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirmation</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer ce challenge ?</p>
                <p class="text-warning">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-close">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i>
                    Supprimer
                </button>
            </div>
        </div>
    </div>

    <!-- Template pour les flags -->
    <template id="flagTemplate">
        <div class="flag-item">
            <div class="flag-header">
                <h5>Flag #<span class="flag-number"></span></h5>
                <button type="button" class="btn btn-sm btn-danger remove-flag">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="flags[][name]" class="form-control" placeholder="Nom du flag">
                </div>
                <div class="form-group">
                    <label>Valeur (SHA256) *</label>
                    <input type="text" name="flags[][value]" class="form-control" placeholder="Hash SHA256 du flag" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="flags[][points]" class="form-control" min="1" value="100">
                </div>
                <div class="form-group">
                    <label>Points minimum</label>
                    <input type="number" name="flags[][min_points]" class="form-control" min="1" value="50">
                </div>
                <div class="form-group">
                    <label>Décroissance (%)</label>
                    <input type="number" name="flags[][decay]" class="form-control" min="0" max="100" value="10">
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="flags[][is_dynamic]" value="1">
                    Scoring dynamique
                </label>
            </div>
        </div>
    </template>

    <!-- Template pour les tests -->
    <template id="testTemplate">
        <div class="test-item">
            <div class="test-header">
                <h5>Test #<span class="test-number"></span></h5>
                <button type="button" class="btn btn-sm btn-danger remove-test">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Données d'entrée</label>
                    <textarea name="tests[][input_data]" class="form-control" rows="3" placeholder="Données d'entrée du test"></textarea>
                </div>
                <div class="form-group">
                    <label>Sortie attendue</label>
                    <textarea name="tests[][expected_output]" class="form-control" rows="3" placeholder="Sortie attendue"></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Poids</label>
                    <input type="number" name="tests[][weight]" class="form-control" min="1" value="1">
                </div>
                <div class="form-group">
                    <label>Timeout (secondes)</label>
                    <input type="number" name="tests[][timeout_seconds]" class="form-control" min="1" value="2">
                </div>
                <div class="form-group">
                    <label>Mémoire (MB)</label>
                    <input type="number" name="tests[][memory_limit_mb]" class="form-control" min="1" value="128">
                </div>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="tests[][is_public]" value="1" checked>
                    Test public (visible par les participants)
                </label>
            </div>
        </div>
    </template>

    <!-- Template pour les technologies -->
    <template id="technologyTemplate">
        <div class="technology-item">
            <div class="technology-header">
                <h5>Technologie #<span class="technology-number"></span></h5>
                <button type="button" class="btn btn-sm btn-danger remove-technology">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="form-group">
                <label>Technologie</label>
                <select name="technologies[]" class="form-control">
                    <option value="">Sélectionner une technologie</option>
                </select>
            </div>
        </div>
    </template>
</body>

</html>