<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathon Details</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/hackathon-details.css">
    <script src="/js/admin/hackathon-details.js"></script>
</head>

<body>
    <div class="container mx-auto px-4! py-8!">
        <!-- Header -->
        <div class="mb-8 bg-gradient-to-r from-primary/10 to-primary/5 p-6 rounded-xl border border-border border-violet-500/20">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-3xl font-bold text-white" id="hackathonTitle">Chargement...</h1>
                        <span class="status-badge text-sm px-3 py-1" id="hackathonStatus"></span>
                    </div>
                    <p class="text-text-muted text-lg" id="hackathonTheme"></p>
                    <div class="flex items-center gap-4 mt-3 text-sm text-text-muted">
                        <span class="flex items-center gap-1">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                            <span id="infoDateRange"></span>
                        </span>
                        <span class="flex items-center gap-1">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            <span id="infoLocation"></span>
                        </span>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button class="btn-secondary" id="editInfoBtn">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                        <span>Modifier</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-container mb-6">
            <div class="flex border-b border-border overflow-x-auto">
                <button class="tab-btn active" data-tab="infos">
                    <i data-lucide="info"></i>
                    <span>Informations</span>
                </button>
                <button class="tab-btn" data-tab="phases">
                    <i data-lucide="calendar"></i>
                    <span>Phases</span>
                </button>
                <button class="tab-btn" data-tab="teams">
                    <i data-lucide="users"></i>
                    <span>Équipes</span>
                </button>
                <button class="tab-btn" data-tab="participants">
                    <i data-lucide="user-check"></i>
                    <span>Participants</span>
                </button>
                <button class="tab-btn" data-tab="challenges">
                    <i data-lucide="flag"></i>
                    <span>Challenges</span>
                </button>
                <button class="tab-btn" data-tab="leaderboard">
                    <i data-lucide="trophy"></i>
                    <span>Classement</span>
                </button>
                <button class="tab-btn" data-tab="registrations">
                    <i data-lucide="user-plus"></i>
                    <span>Inscriptions</span>
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="tab-content active" id="infos-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Informations générales</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Type</span>
                            <span class="info-value" id="infoType"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date de début</span>
                            <span class="info-value" id="infoStartDate"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date de fin</span>
                            <span class="info-value" id="infoEndDate"></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Localisation</span>
                            <span class="info-value" id="infoLocation"></span>
                        </div>
                        <div class="info-item full-width">
                            <span class="info-label">Description</span>
                            <p class="info-value" id="infoDescription"></p>
                        </div>
                        <div class="info-item full-width">
                            <span class="info-label">Règles</span>
                            <p class="info-value" id="infoRules"></p>
                        </div>
                        <div class="info-item full-width">
                            <span class="info-label">Prix</span>
                            <p class="info-value" id="infoPrizes"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="phases-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Gestion des phases</h2>
                    <button class="btn-primary" id="addPhaseBtn">
                        <i data-lucide="plus"></i>
                        <span>Ajouter une phase</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Ordre</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="phasesTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="teams-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Équipes inscrites</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom équipe</th>
                                    <th>Leader</th>
                                    <th>Membres</th>
                                    <th>Date inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teamsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="participants-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Participants</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>École</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="participantsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="challenges-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Challenges</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Type</th>
                                    <th>Points</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="challengesTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="leaderboard-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Classement</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Rang</th>
                                    <th>Équipe</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody id="leaderboardTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content" id="registrations-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-xl font-semibold text-white">Demandes d'inscription</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Participant</th>
                                    <th>Email</th>
                                    <th>Date demande</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="registrationsTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Info -->
    <div class="modal" id="editInfoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text-xl font-semibold text-white">Modifier les informations</h3>
                <button class="modal-close" data-modal="editInfoModal">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editInfoForm">
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="3"></textarea>
                    </div>
                    <!-- Règles du hackathon -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-gray-300 font-medium">Règles</label>
                            <button type="button" onclick="addRule()" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une règle
                            </button>
                        </div>
                        <div id="rules-container" class="space-y-3">
                            <!-- Les règles seront ajoutées ici dynamiquement -->
                        </div>
                        <input type="hidden" id="hackathonRules" name="rules">
                    </div>

                    <!-- Critères d'éligibilité -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-gray-300 font-medium">Critères d'éligibilité</label>
                            <button type="button" onclick="addEligibilityCriterion()" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                <i data-lucide="plus" class="w-4 h-4"></i> Ajouter un critère
                            </button>
                        </div>
                        <div id="eligibility-container" class="space-y-3">
                            <!-- Les critères seront ajoutés ici dynamiquement -->
                        </div>
                        <input type="hidden" id="hackathonEligibility" name="eligibility_criteria">
                    </div>

                    <!-- Récompenses -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-gray-300 font-medium">Récompenses</label>
                            <button type="button" onclick="addPrize()" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une récompense
                            </button>
                        </div>
                        <div id="prizes-container" class="space-y-3">
                            <!-- Les récompenses seront ajoutées ici dynamiquement -->
                        </div>
                        <input type="hidden" id="hackathonPrizes" name="prizes">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" data-modal="editInfoModal">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Add/Edit Phase -->
    <div class="modal" id="phaseModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text-xl font-semibold text-white" id="phaseModalTitle">Ajouter une phase</h3>
                <button class="modal-close" data-modal="phaseModal">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="phaseForm">
                    <input type="hidden" name="phase_id">
                    <input type="hidden" name="hackathon_id">
                    <div class="form-group">
                        <label>Nom de la phase</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" required>
                            <option value="open">Ouverte</option>
                            <option value="qualified">Qualifiée</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date de début</label>
                        <input type="datetime-local" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label>Date de fin</label>
                        <input type="datetime-local" name="end_date" required>
                    </div>
                    <div class="form-group">
                        <label>Ordre</label>
                        <input type="number" name="order" min="1" required>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" data-modal="phaseModal">Annuler</button>
                        <button type="submit" class="btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>