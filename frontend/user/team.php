<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hack & Stack - Équipes</title>
    <link rel="stylesheet" href="/css/styles/user/teams.css">
    <?php require_once '../includes/user/head.php'; ?>
</head>

<body>

    <?php require_once '../includes/user/header.php'; ?>
    <br>
    <br>
    <br>
    <div class="container" id="mainTeamsContainer">
        <div class="header">
            <div class="header-content">
                <div class="header-icon"><i data-lucide="users" class="w-5 h-5 align-middle"></i></div>
                <h1 style="font-size: 25px;">Équipes</h1>
            </div>
            <p class="subtitle">Rejoignez ou créez des équipes pour participer aux hackathons et challenges</p>
        </div>

        <div class="nav-tabs">
            <button class="nav-tab active" data-tab="all">Toutes les équipes</button>
            <button class="nav-tab" data-tab="my">Mon équipe</button>
        </div>

        <div id="allTeamsSection">
            <div class="search-filter-controls" id="searchFilterControls">
                <input type="text" id="teamSearch" placeholder="Rechercher une équipe...">
            </div>

            <div class="teams-content" id="allTeamsContent">
                <div class="teams-grid all-teams" id="allTeamsGrid">
                </div>
            </div>
        </div>

        <div id="myTeamsSection" class="hidden">
            <div class="action-buttons" id="actionButtons">
                <button style="font-size: 0.9rem;" class="btn btn-primary" id="createTeamBtn">
                    <span><i data-lucide="plus" class="w-4 h-4 align-middle"></i></span> Créer une équipe
                </button>
                <button class="btn btn-secondary" id="joinTeamBtn">
                    <span><i data-lucide="link" class="w-4 h-4 align-middle"></i></span> Rejoindre une équipe
                </button>
            </div>

            <div class="teams-content" id="myTeamsContent">
                <div class="teams-grid" id="myTeamsGrid">
                </div>
            </div>

            <div class="no-teams" id="noTeams">
                <h3>Aucune équipe trouvée</h3>
                <p>Vous ne faites partie d'aucune équipe.</p>
            </div>
        </div>
    </div>

    <div class="modal-overlay hidden" id="createTeamModal">
        <div class="modal-content" id="createTeamModalContent">
            <div class="modal-header">
                <h2>Créer une équipe</h2>
                <button class="modal-close-btn" id="closeCreateTeamModal">&times;</button>
            </div>
            <form id="createTeamForm">
                <input type="hidden" name="csrf_token" id="csrfCreateTeamForm" value="<?php echo $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="teamNameInput">Nom de l'équipe</label>
                    <input type="text" name="nom" id="teamNameInput" placeholder="Nom de l'équipe">
                </div>
                <div class="form-group">
                    <label for="teamTypeSelect">Type d'équipe</label>
                    <select name="type" id="teamTypeSelect" required>
                        <option value="">Sélectionnez le type</option>
                        <option value="dev">Dev</option>
                        <option value="hack">CTF</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="teamDescriptionInput">Description</label>
                    <textarea name="description" id="teamDescriptionInput" placeholder="Décrivez votre équipe..." rows="4"></textarea>
                </div>

                <div style="
                background: #fef3c7;
                border: 1px solid #f59e0b;
                border-radius: 8px;
                padding: 10px;
                margin-bottom: 15px;
            ">
                <p style="margin: 0; color: #92400e; font-size: 14px;">
                    <i data-lucide="info" style="width: 16px; height: 16px; display: inline; margin-right: 8px;"></i>
                    Vous ne pouvez former qu'une seule equipe et etre membre que d'une seule equipe.
                </p>
            </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelCreateTeam">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay hidden" id="joinTeamModal">
        <div class="modal-content join-team-modal" id="joinTeamModalContent">
            <div class="modal-header">
                <h2>Rejoindre une équipe</h2>
                <button class="modal-close-btn" id="closeJoinTeamModal">&times;</button>
            </div>
            <div class="modal-tabs">
                <button class="modal-tab-btn active" data-join-tab="code">Code d'invitation</button>
                <button class="modal-tab-btn" data-join-tab="request">Envoyer une requête</button>
            </div>

            <form id="inviteCodeForm" class="join-tab-content">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="invitationCode">Code d'invitation</label>
                    <input type="text" id="invitationCode" placeholder="Entrez le code"
                        name="invitation_code" required>
                    <p class="text-sm text-gray-500 mt-1">Code fourni par le capitaine.</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelInviteCode">Annuler</button>
                    <button type="submit" class="btn btn-primary">Rejoindre</button>
                </div>
            </form>

            <form id="sendRequestForm" class="join-tab-content hidden">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>">
                <div class="form-group">
                    <label for="requestTeamName">Nom de l'équipe</label>
                    <input type="text" id="requestTeamName" placeholder="Nom de l'équipe"
                        name="team_name" required>
                    <p class="text-sm text-gray-500 mt-1">Demande envoyée au capitaine.</p>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelSendRequest">Annuler</button>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/js/user/team.js" defer></script>
</body>

</html>