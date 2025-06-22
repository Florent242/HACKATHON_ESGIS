// Variable globale pour stocker les équipes récupérées de l'API
let fetchedAllTeamsData = [];
let currentUserId = null; // Variable pour stocker l'ID de l'utilisateur actuel

// Navigation entre les onglets (Toutes les équipes / Mes équipes)
function initTabs() {
    const tabs = document.querySelectorAll('.nav-tab');
    const allTeamsSection = document.getElementById('allTeamsSection');
    const myTeamsSection = document.getElementById('myTeamsSection');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const tabType = tab.getAttribute('data-tab');

            if (tabType === 'all') {
                allTeamsSection.classList.remove('hidden');
                myTeamsSection.classList.add('hidden');
                applySearch();
            } else if (tabType === 'my') {
                allTeamsSection.classList.add('hidden');
                myTeamsSection.classList.remove('hidden');
                displayMyTeams();
            }
        });
    });

    if (document.querySelector('.nav-tab.active').getAttribute('data-tab') === 'all') {
        allTeamsSection.classList.remove('hidden');
        myTeamsSection.classList.add('hidden');
    }
}

// Fonction pour vérifier si l'utilisateur appartient à une équipe
function isUserInTeam(team, userId) {
    // Vérifier si l'utilisateur est le leader de l'équipe
    if (team.leader_id && team.leader_id == userId) {
        return { isMember: true, role: 'captain' };
    }

    // Vérifier si l'utilisateur est dans la liste des membres
    if (team.members && Array.isArray(team.members)) {
        const memberFound = team.members.find(member =>
            member.id == userId || member.user_id == userId
        );
        if (memberFound) {
            return { isMember: true, role: memberFound.role || 'member' };
        }
    }

    // Fallback sur la propriété isMember si elle existe déjà
    if (team.isMember === true) {
        return { isMember: true, role: team.role || 'member' };
    }

    return { isMember: false, role: null };
}

// Affiche "Mes équipes" à partir des données récupérées
async function displayMyTeams() {
    const myTeamsGrid = document.getElementById('myTeamsGrid');
    myTeamsGrid.innerHTML = '';
    const noTeamsMessage = document.getElementById('noTeams');

    // S'assurer qu'on a l'ID utilisateur
    if (!currentUserId) {
        try {
            currentUserId = await getUserId();
        } catch (error) {
            console.error('Erreur lors de la récupération de l\'ID utilisateur:', error);
            noTeamsMessage.classList.remove('hidden');
            myTeamsGrid.classList.add('hidden');
            return;
        }
    }

    // Filtrer les équipes où l'utilisateur est membre ou leader
    const myTeams = fetchedAllTeamsData.filter(team => {
        const userStatus = isUserInTeam(team, currentUserId);
        if (userStatus.isMember) {
            // Ajouter les informations de membership à l'équipe
            team.isMember = true;
            team.role = userStatus.role;
            return true;
        }
        return false;
    });

    if (myTeams.length === 0) {
        noTeamsMessage.classList.remove('hidden');
        myTeamsGrid.classList.add('hidden');
    } else {
        noTeamsMessage.classList.add('hidden');
        myTeamsGrid.classList.remove('hidden');
        myTeams.forEach(team => {
            const card = createTeamCard(team);
            myTeamsGrid.appendChild(card);
        });

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
        initTeamActions();
    }
}

// Fonction utilitaire pour créer une carte d'équipe
function createTeamCard(team) {
    const card = document.createElement('div');
    card.className = 'team-card';
    card.setAttribute('data-team-id', team.id);
    card.setAttribute('data-team-type', team.type);
    if (team.isMember !== undefined) card.setAttribute('data-is-member', team.isMember);
    if (team.role) card.setAttribute('data-role', team.role);

    // Améliorer l'affichage du rôle
    let teamRoleTag = '';
    if (team.role) {
        const roleText = team.role === 'captain' ? 'Capitaine' : 'Membre';
        const roleClass = team.role === 'captain' ? 'captain' : 'member';
        teamRoleTag = `<span class="team-role ${roleClass}">${roleText}</span>`;
    }

    // Afficher le nombre de membres si disponible
    let membersCount = '';
    if (team.members && Array.isArray(team.members)) {
        const totalMembers = team.members.length + (team.leader_id ? 1 : 0);
        membersCount = `<span class="team-members-count">${totalMembers} membre${totalMembers > 1 ? 's' : ''}</span>`;
    }

    card.innerHTML = `
        <div class="team-header">
            <div class="team-avatar">${team.avatar || team.name.substring(0, 2).toUpperCase()}</div>
            <div class="team-info">
                <h3 style="font-size: 18px;">${team.name}</h3>
                <div class="team-meta">
                    ${teamRoleTag}
                    ${membersCount}
                </div>
            </div>
        </div>
        <p class="team-description">${team.description || 'Description non disponible.'}</p>
        <div class="team-actions">
            <button class="btn btn-primary view-team-btn">
                <i data-lucide="square-arrow-out-up-right" class="w-4 h-4 align-middle"></i> Voir l'équipe
            </button>
        </div>
    `;

    return card;
}

// Fonction pour créer une équipe via l'API (VERSION CORRIGÉE)
async function createTeamViaAPI(teamData) {
    try {
        const userId = await getUserId();
        if (!userId) {
            throw new Error('Utilisateur non authentifié');
        }
        console.log("Création d'équipe pour l'utilisateur :", userId);

        const formElement = document.querySelector('#createTeamForm');
        const formData = new FormData(formElement);
        formData.append('leader_id', userId);
        formData.append('nom', teamData.name);
        formData.append('type', teamData.type);
        if (teamData.description) formData.append('description', teamData.description);
        if (teamData.hackathon_id) formData.append('hackathon_id', teamData.hackathon_id);

        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        if (!csrfToken) {
            throw new Error('Token CSRF manquant');
        }
        formData.append('csrf_token', csrfToken);

        // Débogage : Vérifier les cookies disponibles
        console.log('Cookies envoyés:', document.cookie);

        const response = await fetch('http://51.77.218.188:8081/api/teams', {
            method: 'POST',
            credentials: 'include', // Inclut les cookies dans la requête
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        console.log('Réponse API status:', response.status);
        const data = await response.json().catch(() => ({}));
        console.log('Données reçues de l\'API:', data);

        if (!response.ok) {
            if (response.status === 401) {
                showNotification('Session expirée', 'Veuillez vous reconnecter', 'error');
                window.location.href = '/login';
            }
            throw new Error(`Erreur HTTP ${response.status}: ${data.error || 'Erreur inconnue'}`);
        }

        showNotification('Équipe créée avec succès', 'success');
        return data;
    } catch (error) {
        console.error('Erreur dans createTeamViaAPI:', error.message);
        showNotification('Erreur lors de la création de l\'équipe', error.message, 'error');
        return { success: false, error: error.message };
    }
}


// Fonction pour rejoindre une équipe via code d'invitation (VERSION CORRIGÉE)
async function joinTeamViaCode(invitationCode) {
    try {
        const element = document.getElementById('inviteCodeForm');
        const formData = new FormData(element);
        if (formData.get('invitation_code') === null) {
            formData.append('invitation_code', invitationCode);
        }

        const response = await fetch('/api/teams/join', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            showNotification('Erreur lors de la tentative de rejoindre l\'équipe', errorText.error, 'error');
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            // Rafraîchir les données des équipes
            await fetchAndDisplayAllTeams();
            return { success: true, message: data.message };
        } else {
            return { success: false, error: data.error || 'Code d\'invitation invalide' };
        }
    } catch (error) {
        console.error('Erreur dans joinTeamViaCode:', error.error);
        showNotification('Erreur lors de la tentative de rejoindre l\'équipe', error.error, 'error');
        return { success: false, error: error.error || 'Erreur de réseau' };
    }
}

// Fonction pour envoyer une demande pour rejoindre une équipe (VERSION CORRIGÉE)
async function sendJoinRequest(teamName) {
    try {
        const element = document.getElementById('sendRequestForm');
        const formData = new FormData(element);
        if (formData.get('team_name') === null) {
            formData.append('team_name', teamName);
        }

        const response = await fetch('/api/teams/request', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            showNotification('Erreur lors de l\'envoi de la demande', errorText.error, 'error');
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            return { success: true, message: data.message };
        } else {
            return { success: false, error: data.error || 'Erreur lors de l\'envoi de la demande' };
        }
    } catch (error) {
        console.error('Erreur dans sendJoinRequest:', error.error);
        showNotification('Erreur lors de l\'envoi de la demande', error.error, 'error');
        return { success: false, error: error.error || 'Erreur de réseau' };
    }
}

// Fonction pour gérer les boutons d'action principaux
function initActionButtons() {
    const createTeamBtn = document.getElementById('createTeamBtn');
    const joinTeamBtn = document.getElementById('joinTeamBtn');

    const createTeamModal = document.getElementById('createTeamModal');
    const closeCreateTeamModalBtn = document.getElementById('closeCreateTeamModal');
    const cancelCreateTeamBtn = document.getElementById('cancelCreateTeam');
    const createTeamForm = document.getElementById('createTeamForm');
    const createTeamModalContent = document.getElementById('createTeamModalContent');

    const joinTeamModal = document.getElementById('joinTeamModal');
    const closeJoinTeamModalBtn = document.getElementById('closeJoinTeamModal');
    const cancelInviteCodeBtn = document.getElementById('cancelInviteCode');
    const cancelSendRequestBtn = document.getElementById('cancelSendRequest');
    const joinTeamModalContent = document.getElementById('joinTeamModalContent');

    const joinModalTabButtons = document.querySelectorAll('.join-team-modal .modal-tab-btn');
    const inviteCodeForm = document.getElementById('inviteCodeForm');
    const sendRequestForm = document.getElementById('sendRequestForm');

    // Modale "Créer une équipe"
    if (createTeamBtn) {
        createTeamBtn.addEventListener('click', () => {
            createTeamModal.classList.remove('hidden');
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        });
    }

    if (closeCreateTeamModalBtn) closeCreateTeamModalBtn.addEventListener('click', () => {
        createTeamModal.classList.add('hidden');
    });
    if (cancelCreateTeamBtn) cancelCreateTeamBtn.addEventListener('click', () => {
        createTeamModal.classList.add('hidden');
    });

    if (createTeamForm) {
        createTeamForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const teamName = document.getElementById('teamNameInput').value;
            const teamType = document.getElementById('teamTypeSelect').value;
            const teamDescription = document.getElementById('teamDescriptionInput').value;

            // Validation côté client
            if (!teamName.trim()) {
                showNotification('Le nom de l\'équipe est requis', 'warning');
                return;
            }
            if (!teamType) {
                showNotification('Le type d\'équipe est requis', 'warning');
                return;
            }

            const newTeamData = {
                name: teamName.trim(),
                type: teamType,
                description: teamDescription.trim(),
                hackathon_id: 1,
                leader_id: 1
            };

            const submitButton = createTeamForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Création en cours...';
            }

            try {
                const result = await createTeamViaAPI(newTeamData);

                if (result.success) {
                    showNotification(`L'équipe "${teamName}" a été créée avec succès !`, 'success');
                    createTeamModal.classList.add('hidden');
                    createTeamForm.reset();
                } else {
                    showNotification(`Erreur lors de la création de l'équipe : ${result.error}`, 'error');
                    console.log("7 erreur de creation de la team");
                }
            } catch (error) {
                showNotification('Une erreur est survenue lors de la création de l\'équipe', 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Créer';
                }
            }
        });
    }

    if (createTeamModalContent) createTeamModalContent.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    if (createTeamModal) createTeamModal.addEventListener('click', () => {
        createTeamModal.classList.add('hidden');
    });

    // Modale "Rejoindre une équipe"
    if (joinTeamBtn) {
        joinTeamBtn.addEventListener('click', () => {
            joinTeamModal.classList.remove('hidden');
            if (joinModalTabButtons.length > 0) {
                joinModalTabButtons[0].click();
            }
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        });
    }

    if (closeJoinTeamModalBtn) closeJoinTeamModalBtn.addEventListener('click', () => {
        joinTeamModal.classList.add('hidden');
    });
    if (cancelInviteCodeBtn) cancelInviteCodeBtn.addEventListener('click', () => {
        joinTeamModal.classList.add('hidden');
    });
    if (cancelSendRequestBtn) cancelSendRequestBtn.addEventListener('click', () => {
        joinTeamModal.classList.add('hidden');
    });

    joinModalTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            joinModalTabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const tab = button.getAttribute('data-join-tab');
            if (tab === 'code') {
                if (inviteCodeForm) inviteCodeForm.classList.remove('hidden');
                if (sendRequestForm) sendRequestForm.classList.add('hidden');
            } else if (tab === 'request') {
                if (inviteCodeForm) inviteCodeForm.classList.add('hidden');
                if (sendRequestForm) sendRequestForm.classList.remove('hidden');
            }
        });
    });

    if (inviteCodeForm) {
        inviteCodeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const invitationCode = document.getElementById('invitationCode').value.trim();

            if (!invitationCode) {
                showNotification('Veuillez entrer un code d\'invitation', 'warning');
                return;
            }

            const submitButton = inviteCodeForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Rejoindre en cours...';
            }

            try {
                const result = await joinTeamViaCode(invitationCode);

                if (result.success) {
                    showNotification('Vous avez rejoint l\'équipe avec succès !', 'success');
                    joinTeamModal.classList.add('hidden');
                    inviteCodeForm.reset();
                } else {
                    showNotification(`Erreur : ${result.error}`, 'error');
                }
            } catch (error) {
                showNotification('Une erreur est survenue lors de la tentative de rejoindre l\'équipe', 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Rejoindre';
                }
            }
        });
    }

    if (sendRequestForm) {
        sendRequestForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const requestTeamName = document.getElementById('requestTeamName').value.trim();

            if (!requestTeamName) {
                showNotification('Veuillez entrer le nom de l\'équipe', 'warning');
                return;
            }

            const submitButton = sendRequestForm.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Envoi en cours...';
            }

            try {
                const result = await sendJoinRequest(requestTeamName);

                if (result.success) {
                    showNotification('Votre demande a été envoyée avec succès !', 'success');
                    joinTeamModal.classList.add('hidden');
                    sendRequestForm.reset();
                } else {
                    showNotification(`Erreur : ${result.error}`, 'error');
                }
            } catch (error) {
                showNotification('Une erreur est survenue lors de l\'envoi de la demande', 'error');
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Envoyer';
                }
            }
        });
    }

    if (joinTeamModalContent) joinTeamModalContent.addEventListener('click', (e) => {
        e.stopPropagation();
    });

    if (joinTeamModal) joinTeamModal.addEventListener('click', () => {
        joinTeamModal.classList.add('hidden');
    });
}

// Fonction pour gérer les actions des cartes d'équipe
function initTeamActions() {
    const viewTeamButtons = document.querySelectorAll('.team-card .view-team-btn');

    viewTeamButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const teamCard = e.target.closest('.team-card');
            const teamId = teamCard.getAttribute('data-team-id');
            
            // Redirection vers la page de détail de l'équipe
            window.location.href = `/user/teams/overview/${teamId}`;
        });
    });
}

// Fonction pour appliquer la recherche
function applySearch() {
    const searchTerm = document.getElementById('teamSearch')?.value.toLowerCase() || '';
    const allTeamsGrid = document.getElementById('allTeamsGrid');

    if (!allTeamsGrid) return;

    if (fetchedAllTeamsData.length === 0) {
        allTeamsGrid.innerHTML = '<p class="text-center text-gray-400">Chargement des équipes...</p>';
        return;
    }

    const filteredTeams = fetchedAllTeamsData.filter(team => {
        if (!searchTerm) return true;

        return (
            team.name.toLowerCase().includes(searchTerm) ||
            (team.description && team.description.toLowerCase().includes(searchTerm)) ||
            (team.type && team.type.toLowerCase().includes(searchTerm))
        );
    });

    if (filteredTeams.length === 0) {
        const message = searchTerm
            ? `Aucune équipe trouvée pour "${searchTerm}".`
            : 'Aucune équipe disponible.';
        allTeamsGrid.innerHTML = `<p class="text-center text-gray-400">${message}</p>`;
    } else {
        allTeamsGrid.innerHTML = '';
        filteredTeams.forEach(team => {
            // Pour l'affichage dans "Toutes les équipes", déterminer si l'utilisateur en fait partie
            if (currentUserId) {
                const userStatus = isUserInTeam(team, currentUserId);
                team.isMember = userStatus.isMember;
                team.role = userStatus.role;
            }

            const card = createTeamCard(team);
            allTeamsGrid.appendChild(card);
        });

        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons();
        }
        initTeamActions();
    }
}

// Fonction pour récupérer toutes les équipes de l'API
let isFetchingTeams = false;
async function fetchAndDisplayAllTeams() {
    if (isFetchingTeams) return;
    isFetchingTeams = true;

    const allTeamsGrid = document.getElementById('allTeamsGrid');
    if (allTeamsGrid) {
        allTeamsGrid.innerHTML = '<p class="text-center text-gray-400">Chargement des équipes...</p>';
    }

    try {
        // S'assurer qu'on a l'ID utilisateur
        if (!currentUserId) {
            try {
                currentUserId = await getUserId();
            } catch (error) {
                console.warn('Impossible de récupérer l\'ID utilisateur:', error);
            }
        }

        const response = await fetch('/api/teams', {
            method: 'GET',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const data = await response.json();

        if (data.success && Array.isArray(data.data)) {
            fetchedAllTeamsData = data.data;
            applySearch();
        } else {
            if (allTeamsGrid) {
                allTeamsGrid.innerHTML = '<p class="text-center text-red-400">Erreur lors du chargement des équipes.</p>';
            }
        }
    } catch (error) {
        console.error('Erreur lors du chargement des équipes:', error);
        if (allTeamsGrid) {
            allTeamsGrid.innerHTML = '<p class="text-center text-red-400">Impossible de joindre l\'API.</p>';
        }
    } finally {
        isFetchingTeams = false;
    }
}

// Fonction de débounce pour optimiser la recherche
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', async () => {
    initTabs();
    initActionButtons();
    initTeamActions();

    await fetchAndDisplayAllTeams();

    const teamSearchInput = document.getElementById('teamSearch');
    if (teamSearchInput) {
        const debouncedSearch = debounce(applySearch, 300);
        teamSearchInput.addEventListener('input', debouncedSearch);

        teamSearchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                teamSearchInput.value = '';
                applySearch();
            }
        });
    }
});