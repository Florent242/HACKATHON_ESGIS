/**
 * assets/js/leaderboard.js
 * Script principal pour la gestion du leaderboard des hackathons
 */

// Configuration globale
const CONFIG = {
    hacksec_id: 1,
    hackdev_id: 2,
    REFRESH_INTERVAL: 15000, // 15 secondes
    STORAGE_KEY: 'leaderboard_preferences',
    ENDPOINTS: {
        PHASES: '/scores/',
        LEADERBOARD: `/scores/`
    }
};

// État global de l'application
let AppState = {
    currentEvent: null,
    currentPhase: null,
    currentPhaseData: null, // Pour stocker les données complètes de la phase courante
    refreshTimer: null,
    countdownTimer: null, // Timer pour le compte à rebours
    isLoading: false,
    lastUpdate: null
};

// Éléments DOM
const DOM = {
    eventSelect: null,
    phaseSelect: null,
    leaderboardBody: null,
    selectorLoading: null,
    errorMessage: null,
    errorText: null,
    currentEvent: null,
    currentPhase: null,
    countdown: null,
    lastUpdate: null,
    qualifyTeamsBtn: null,
    freezePhaseBtn: null,
    unfreezePhaseBtn: null
};

/**
 * Initialisation de l'application
 */
document.addEventListener('DOMContentLoaded', function () {
    initializeDOM();
    initializeLucideIcons();
    setupEventListeners();
    updateTimestamp();
    startAutoRefresh();
    applySavedPreferences();
});

/**
 * Initialise les références aux éléments DOM
 */
function initializeDOM() {
    DOM.eventSelect = document.getElementById('event-select');
    DOM.phaseSelect = document.getElementById('phase-select');
    DOM.leaderboardBody = document.getElementById('leaderboard-body');
    DOM.selectorLoading = document.getElementById('selector-loading');
    DOM.errorMessage = document.getElementById('error-message');
    DOM.errorText = document.getElementById('error-text');
    DOM.currentEvent = document.getElementById('current-event');
    DOM.currentPhase = document.getElementById('current-phase');
    DOM.countdown = document.getElementById('countdown');
    DOM.lastUpdate = document.getElementById('last-update');
    DOM.qualifyTeamsBtn = document.getElementById('qualify-teams');
    DOM.freezePhaseBtn = document.getElementById('freeze-phase');
    DOM.unfreezePhaseBtn = document.getElementById('unfreeze-phase');
}

/**
 * Initialise les icônes Lucide
 */
function initializeLucideIcons() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Configure les écouteurs d'événements
 */
function setupEventListeners() {
    // Sélecteur d'événement
    if (DOM.eventSelect) {
        DOM.eventSelect.addEventListener('change', handleEventChange);
    }

    // Sélecteur de phase
    if (DOM.phaseSelect) {
        DOM.phaseSelect.addEventListener('change', handlePhaseChange);
    }

    // Gestion des boutons d'administration
    if (DOM.qualifyTeamsBtn) {
        DOM.qualifyTeamsBtn.addEventListener('click', handleQualifyTeams);
    }
    if (DOM.freezePhaseBtn) {
        DOM.freezePhaseBtn.addEventListener('click', handleFreezePhase);
    }
    if (DOM.unfreezePhaseBtn) {
        DOM.unfreezePhaseBtn.addEventListener('click', handleUnfreezePhase);
    }

    // Gestion de la visibilité de la page (pause/reprise du refresh)
    document.addEventListener('visibilitychange', handleVisibilityChange);

    // Gestion des erreurs globales
    window.addEventListener('error', handleGlobalError);
}

/**
 * Gère le clic sur le bouton "Qualifier les équipes"
 */
async function handleQualifyTeams() {
    if (!AppState.currentPhase) return;

    if (!await showConfirmDialog('Êtes-vous sûr de vouloir qualifier les équipes pour la phase suivante ? Cette action est irréversible.')) {
        return;
    }

    try {
        toggleLoadingState(DOM.qualifyTeamsBtn, true);

        const response = await apiRequest(`${CONFIG.ENDPOINTS.PHASES}${AppState.currentEvent}/qualify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                phase_id: AppState.currentPhase
            })
        });

        if (response.success) {
            showNotification('Les équipes ont été qualifiées avec succès !',null, 'success');
            // Recharger les données
            await loadLeaderboard(AppState.currentEvent, AppState.currentPhase);
        } else {
            throw new Error(response.message || 'Erreur lors de la qualification des équipes');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError(error.message || 'Une erreur est survenue lors de la qualification des équipes');
    } finally {
        toggleLoadingState(DOM.qualifyTeamsBtn, false);
    }
}

/**
 * Gère le clic sur le bouton "Geler la phase"
 */
async function handleFreezePhase() {
    if (!AppState.currentPhase) return;

    if (!await showConfirmDialog('Êtes-vous sûr de vouloir geler cette phase ? Le classement ne sera plus mis à jour.')) {
        return;
    }

    try {
        toggleLoadingState(DOM.freezePhaseBtn, true);

        const response = await apiRequest(`${CONFIG.ENDPOINTS.PHASES}${AppState.currentPhase}/freeze`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                phase_id: AppState.currentPhase
            })
        });

        if (response.success) {
            showNotification('La phase a été gelée avec succès !',null, 'success');
            AppState.currentPhaseData.frozen_leaderboard = 1;
            AppState.currentPhaseData.status = 'frozen';
            // Mettre à jour l'interface
            updatePhaseStatus('frozen');
        } else {
            throw new Error(response.message || 'Erreur lors du gel de la phase');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError(error.message || 'Une erreur est survenue lors du gel de la phase');
    }
}

/**
 * Gère le clic sur le bouton "Dégeler la phase"
 */
async function handleUnfreezePhase() {
    if (!AppState.currentPhase) return;

    if (!await showConfirmDialog('Êtes-vous sûr de vouloir dégeler cette phase ? Le classement sera à nouveau mis à jour.')) {
        return;
    }

    try {
        toggleLoadingState(DOM.unfreezePhaseBtn, true);

        const response = await apiRequest(`${CONFIG.ENDPOINTS.PHASES}${AppState.currentPhase}/unfreeze`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                phase_id: AppState.currentPhase
            })
        });

        if (response.success) {
            showNotification('La phase a été dégélée avec succès !',null, 'success');
            AppState.currentPhaseData.frozen_leaderboard = 0;
            AppState.currentPhaseData.status = 'active';
            // Mettre à jour l'interface
            updatePhaseStatus('active');
        } else {
            throw new Error(response.message || 'Erreur lors du dégel de la phase');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError(error.message || 'Une erreur est survenue lors du dégel de la phase');
    }
}

/**
 * Met à jour l'état de chargement d'un bouton
 */
function toggleLoadingState(button, isLoading) {
    if (!button) return;
    
    button.disabled = isLoading;
    const icon = button.querySelector('i');
    if (icon) {
        if (isLoading) {
            icon.setAttribute('data-lucide', 'loader');
            icon.classList.add('animate-spin');
        } else {
            const originalIcon = button === DOM.qualifyTeamsBtn ? 'check-circle' : 'snowflake';
            icon.setAttribute('data-lucide', originalIcon);
            icon.classList.remove('animate-spin');
        }
        lucide.createIcons(); // Mettre à jour l'icône
    }
}

/**
 * Met à jour l'état des boutons en fonction de la phase sélectionnée
 */
function updateAdminButtons(phaseData) {
    if (!phaseData || !DOM.qualifyTeamsBtn || !DOM.freezePhaseBtn) return;

    const canQualify = phaseData.status === 'ended';
    const canFreeze = phaseData.status === 'active' && phaseData?.frozen_leaderboard === 0;
    const canUnfreeze = phaseData.status === 'frozen' && phaseData?.frozen_leaderboard === 1;

    DOM.qualifyTeamsBtn.disabled = !canQualify;
    DOM.freezePhaseBtn.disabled = !canFreeze;
    DOM.unfreezePhaseBtn.disabled = !canUnfreeze;
}

/**
 * Sauvegarde les préférences dans le localStorage
 */
function savePreferences() {
    const preferences = {
        eventId: AppState.currentEvent,
        phaseId: AppState.currentPhase
    };
    localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(preferences));
}

/**
 * Charge les préférences depuis le localStorage
 * @returns {Object|null} Les préférences sauvegardées ou null si non trouvées
 */
function loadPreferences() {
    const saved = localStorage.getItem(CONFIG.STORAGE_KEY);
    return saved ? JSON.parse(saved) : null;
}

/**
 * Applique les préférences sauvegardées
 */
function applySavedPreferences() {
    const prefs = loadPreferences();
    if (prefs && prefs.eventId) {
        // On sélectionne l'événement sauvegardé
        DOM.eventSelect.value = prefs.eventId;
        // On déclenche le changement d'événement
        const event = new Event('change');
        DOM.eventSelect.dispatchEvent(event);
        
        // Une fois que les phases sont chargées, on sélectionne la phase sauvegardée
        setTimeout(() => {
            if (prefs.phaseId) {
                DOM.phaseSelect.value = prefs.phaseId;
                const phaseEvent = new Event('change');
                DOM.phaseSelect.dispatchEvent(phaseEvent);
            }
        }, 500);
    }
}

/**
 * Gère le changement d'événement
 */
async function handleEventChange(event) {
    const eventId = event.target.value;

    if (!eventId) {
        resetPhaseSelector();
        resetLeaderboard();
        return;
    }

    AppState.currentEvent = eventId;
    savePreferences();
    updateCurrentEventDisplay();

    try {
        showSelectorLoading(true);
        await loadPhases(eventId);
        hideError();
    } catch (error) {
        console.error('Error loading phases:', error);
        showError('Impossible de charger les phases pour cet événement');
        resetPhaseSelector();
    } finally {
        showSelectorLoading(false);
    }
}

/**
 * Gère le changement de phase
 */
async function handlePhaseChange(event) {
    const phaseId = event.target.value;

    if (!phaseId) {
        resetLeaderboard();
        return;
    }

    AppState.currentPhase = phaseId;
    savePreferences();
    updateCurrentPhaseDisplay();

    // Récupérer les données complètes de la phase sélectionnée
    const selectedOption = event.target.options[event.target.selectedIndex];
    if (selectedOption && selectedOption.dataset.phaseData) {
        AppState.currentPhaseData = JSON.parse(selectedOption.dataset.phaseData);
        updateCountdown(); // Mettre à jour le compte à rebours
    }

    // Mettre à jour les boutons d'administration
    if (selectedOption && selectedOption.dataset.phaseData) {
        const phaseData = JSON.parse(selectedOption.dataset.phaseData);
        AppState.currentPhaseData = phaseData;
        updateAdminButtons(phaseData);
        updateCountdown();
    }

    try {
        await loadLeaderboard(AppState.currentEvent, phaseId);
        hideError();
    } catch (error) {
        console.error('Error loading leaderboard:', error);
        showError('Impossible de charger le classement pour cette phase');
        showLeaderboardError();
    }
}

/**
 * Met à jour l'état de la phase dans l'interface
 */
function updatePhaseStatus(status) {
    if (!AppState.currentPhaseData) return;
    
    // Mettre à jour les données de la phase
    AppState.currentPhaseData.status = status;
    if (status === 'frozen') {
        AppState.currentPhaseData.is_frozen = true;
    }
    
    // Mettre à jour le sélecteur de phase
    const phaseSelect = document.getElementById('phase-select');
    if (phaseSelect) {
        const option = phaseSelect.options[phaseSelect.selectedIndex];
        if (option) {
            option.dataset.phaseData = JSON.stringify(AppState.currentPhaseData);
        }
    }
    
    // Mettre à jour les boutons
    updateAdminButtons(AppState.currentPhaseData);
}

/**
 * Charge les phases pour un événement donné
 */
async function loadPhases(eventId) {
    const url = `${CONFIG.ENDPOINTS.PHASES}${eventId}/phases`;

    try {
        const response = await apiRequest(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const data = await response;

        if (data.success && Array.isArray(data.phases)) {
            populatePhaseSelector(data.phases);
        } else {
            throw new Error(data.message || 'Format de réponse invalide');
        }
    } catch (error) {
        console.error('Failed to load phases:', error);
        throw error;
    }
}

/**
 * Charge le leaderboard pour un événement et une phase
 */
async function loadLeaderboard(eventId, phaseId) {
    if (AppState.isLoading) {
        return;
    }

    AppState.isLoading = true;
    showLeaderboardLoading();

    try {
        const response = await apiRequest(`${CONFIG.ENDPOINTS.LEADERBOARD}${eventId}/leaderboard/${phaseId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        const data = await response;

        if (data.success) {
            populateLeaderboard(data.leaderboard || []);
            updateTimestamp();
        } else {
            throw new Error(data.message || 'Erreur lors du chargement du classement');
        }
    } catch (error) {
        console.error('Failed to load leaderboard:', error);
        throw error;
    } finally {
        AppState.isLoading = false;
    }
}

/**
 * Peuple le sélecteur de phases
 */
function populatePhaseSelector(phases) {
    if (!DOM.phaseSelect) {console.error('Phase select element not found'); return;}

    // Vider le sélecteur
    DOM.phaseSelect.innerHTML = '<option disabled selected class="bg-blue-950 text-white hover:bg-blue-800 transition-all duration-150">Choisir une phase</option>';

    if (phases.length === 0) {
        DOM.phaseSelect.innerHTML = '<option disabled class="bg-blue-950 text-white hover:bg-blue-800 transition-all duration-150">Aucune phase disponible</option>';
        DOM.phaseSelect.disabled = true;
        return;
    }

    // Trier les phases par date de début (les plus récentes en premier)
    phases.sort((a, b) => new Date(b.start) - new Date(a.start));

    // Ajouter les phases
    phases.forEach(phase => {
        const option = document.createElement('option');
        option.value = phase.id;
        option.textContent = phase.name;
        option.className = 'bg-blue-950 text-white hover:bg-blue-800 transition-all duration-150';
        option.dataset.phaseData = JSON.stringify(phase); // Stocker les données complètes de la phase
        DOM.phaseSelect.appendChild(option);
    });

    DOM.phaseSelect.disabled = false;
    DOM.phaseSelect.classList.add('fadeIn');
}

/**
 * Peuple le tableau du leaderboard
 */
function populateLeaderboard(teams) {
    if (!DOM.leaderboardBody) {console.error('Leaderboard body element not found'); return;}

    // Vider le tableau
    DOM.leaderboardBody.innerHTML = '';

    if (teams.length === 0) {
        showLeaderboardNoData();
        return;
    }

    // Créer les lignes du tableau
    teams.forEach((team, index) => {
        const row = createLeaderboardRow(team, index + 1);
        DOM.leaderboardBody.appendChild(row);
    });

    // Animer l'apparition
    DOM.leaderboardBody.classList.add('fadeIn');

    // Réinitialiser les icônes Lucide
    setTimeout(() => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 100);
}

/**
 * Crée une ligne du tableau leaderboard
 */
function createLeaderboardRow(team, rank) {
    const row = document.createElement('tr');
    row.className = 'hover:bg-slate-700 transition-colors duration-200';

    // Ajout d'une classe spéciale pour l'équipe de l'utilisateur (à implémenter)
    // if (team.isMember) {
    //     row.className += ' bg-slate-600 border-l-4 border-blue-500';
    // }

    // Colonne rang avec médaille
    const rankCell = document.createElement('td');
    rankCell.className = 'px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900';
    rankCell.innerHTML = createRankDisplay(rank);

    // Colonne équipe
    const teamCell = document.createElement('td');
    teamCell.className = 'px-6 py-4 whitespace-nowrap';
    teamCell.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-pink-500/90 to-violet-500/90 bg-clip flex items-center justify-center">
                    <i data-lucide="users" class="w-5 h-5 text-white"></i>
                </div>
            </div>
            <div class="ml-4">
                <div class="text-base ${rank === 1 ? 'bg-gradient-to-r from-blue-500 to-violet-500 bg-clip-text text-transparent font-extrabold' : 'text-white font-normal'}">${team.name}</div>
                <div class="text-sm text-gray-400">${team.members ? team.members + ' membres' : ''}</div>
            </div>
        </div>
    `;

    // Colonne points
    const pointsCell = document.createElement('td');
    pointsCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-900';
    pointsCell.innerHTML = `
        <div class="flex items-center">
            <span class="text-2xl font-bold text-blue-600">${team.points}</span>
            <span class="ml-1 text-sm text-gray-500">pts</span>
        </div>
    `;

    // Colonne dernière soumission
    const lastSubmissionCell = document.createElement('td');
    lastSubmissionCell.className = 'px-6 py-4 whitespace-nowrap text-sm text-gray-500';
    lastSubmissionCell.innerHTML = `
        <div class="flex items-center">
            <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
            <span>${formatDateTime(team.lastSubmission)}</span>
        </div>
    `;

    row.appendChild(rankCell);
    row.appendChild(teamCell);
    row.appendChild(pointsCell);
    row.appendChild(lastSubmissionCell);

    return row;
}

/**
 * Crée l'affichage du rang avec médaille
 */
function createRankDisplay(rank) {
    const medals = {
        1: '🥇',
        2: '🥈',
        3: '🥉'
    };

    if (rank <= 3) {
        return `
            <div class="flex items-center">
                <span class="text-2xl mr-2">${medals[rank]}</span>
                <span class="font-bold text-white">${rank}</span>
            </div>
        `;
    }

    return `<span class="font-semibold text-white">${rank}</span>`;
}

/**
 * Affiche l'état de chargement du leaderboard
 */
function showLeaderboardLoading() {
    if (!DOM.leaderboardBody) return;

    const template = document.getElementById('loading-template');
    if (template) {
        DOM.leaderboardBody.innerHTML = template.innerHTML;
    }
}

/**
 * Affiche l'état "aucune donnée" du leaderboard
 */
function showLeaderboardNoData() {
    if (!DOM.leaderboardBody) return;

    const template = document.getElementById('no-data-template');
    if (template) {
        DOM.leaderboardBody.innerHTML = template.innerHTML;
    }

    // Réinitialiser les icônes
    setTimeout(() => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 100);
}

/**
 * Affiche l'état d'erreur du leaderboard
 */
function showLeaderboardError() {
    if (!DOM.leaderboardBody) return;

    const template = document.getElementById('error-template');
    if (template) {
        DOM.leaderboardBody.innerHTML = template.innerHTML;
    }

    // Réinitialiser les icônes
    setTimeout(() => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 100);
}

/**
 * Affiche/masque le chargement des sélecteurs
 */
function showSelectorLoading(show) {
    if (!DOM.selectorLoading) return;

    if (show) {
        DOM.selectorLoading.classList.remove('hidden');
        DOM.phaseSelect.disabled = true;
    } else {
        DOM.selectorLoading.classList.add('hidden');
    }
}

/**
 * Affiche un message d'erreur
 */
function showError(message) {
    if (!DOM.errorMessage || !DOM.errorText) return;

    DOM.errorText.textContent = message;
    DOM.errorMessage.classList.remove('hidden');
    DOM.errorMessage.classList.add('slide-in-top');

    // Masquer automatiquement après 5 secondes
    setTimeout(() => {
        hideError();
    }, 5000);
}

/**
 * Masque le message d'erreur
 */
function hideError() {
    if (!DOM.errorMessage) return;

    DOM.errorMessage.classList.add('hidden');
    DOM.errorMessage.classList.remove('slide-in-top');
}

/**
 * Remet à zéro le sélecteur de phases
 */
function resetPhaseSelector() {
    if (!DOM.phaseSelect) return;

    DOM.phaseSelect.innerHTML = '<option disabled selected>Choisir une phase</option>';
    DOM.phaseSelect.disabled = true;
    AppState.currentPhase = null;
    updateCurrentPhaseDisplay();
}

/**
 * Remet à zéro le leaderboard
 */
function resetLeaderboard() {
    if (!DOM.leaderboardBody) return;

    DOM.leaderboardBody.innerHTML = `
        <tr id="waiting-state">
            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center gap-4">
                    <i data-lucide="search" class="w-12 h-12 text-gray-400"></i>
                    <div>
                        <p class="text-lg font-medium">Sélectionnez un hackathon et une phase</p>
                        <p class="text-sm">pour afficher le classement</p>
                    </div>
                </div>
            </td>
        </tr>
    `;

    // Réinitialiser les icônes
    setTimeout(() => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }, 100);
}

/**
 * Met à jour l'affichage de l'événement actuel
 */
function updateCurrentEventDisplay() {
    if (!DOM.currentEvent) return;

    const eventSelect = DOM.eventSelect;
    const selectedOption = eventSelect.options[eventSelect.selectedIndex];

    if (selectedOption && selectedOption.value) {
        DOM.currentEvent.textContent = selectedOption.textContent;
    } else {
        DOM.currentEvent.textContent = 'Sélectionner un hackathon';
    }
}

/**
 * Met à jour l'affichage de la phase actuelle
 */
function updateCurrentPhaseDisplay() {
    if (!DOM.currentPhase) return;

    const phaseSelect = DOM.phaseSelect;
    const selectedOption = phaseSelect.options[phaseSelect.selectedIndex];

    if (selectedOption && selectedOption.value) {
        DOM.currentPhase.textContent = selectedOption.textContent;
    } else {
        DOM.currentPhase.textContent = 'Aucune phase';
    }
}

/**
 * Met à jour le compte à rebours
 */
function updateCountdown() {
    if (!AppState.currentPhaseData || !DOM.countdown) return;

    // Arrêter le timer précédent s'il existe
    if (AppState.countdownTimer) {
        clearInterval(AppState.countdownTimer);
    }

    const startDate = new Date(AppState.currentPhaseData.start);
    const endDate = new Date(AppState.currentPhaseData.end);
    const now = new Date();

    // Fonction pour formater le temps restant
    const updateDisplay = () => {
        const now = new Date();
        let targetDate, message, isActive = false;

        if (now < startDate) {
            // La phase n'a pas encore commencé
            targetDate = startDate;
            message = 'Début dans ';
        } else if (now < endDate) {
            // La phase est en cours
            targetDate = endDate;
            message = 'Fin dans ';
            isActive = true;
        } else {
            // La phase est terminée
            DOM.countdown.innerHTML = `
                <div class="flex items-center text-sm">
                    <i data-lucide="flag" class="w-4 h-4 mr-2 text-red-500"></i>
                    <span class="text-red-400">Terminé</span>
                </div>
            `;
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            return;
        }

        // Calculer la différence
        const diff = targetDate - now;
        
        // Calculer les unités de temps
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        // Formater l'affichage
        let displayText = '';
        if (days > 0) {
            displayText = `${days}j ${hours}h`;
        } else if (hours > 0) {
            displayText = `${hours}h ${minutes}m`;
        } else {
            displayText = `${minutes}m ${seconds}s`;
        }

        // Mettre à jour l'affichage
        DOM.countdown.innerHTML = `
            <div class="flex items-center text-sm">
                <i data-lucide="${isActive ? 'clock' : 'alarm-clock'}" class="w-4 h-4 mr-2 ${isActive ? 'text-green-500' : 'text-yellow-500'}"></i>
                <span class="${isActive ? 'text-green-400' : 'text-yellow-400'}">${message}${displayText}</span>
            </div>
        `;

        // Mettre à jour les icônes Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    };

    // Mettre à jour immédiatement
    updateDisplay();

    // Mettre à jour toutes les secondes
    AppState.countdownTimer = setInterval(updateDisplay, 1000);

    // Stoper le timer si la phase est terminée
    if (now >= endDate) {
        clearInterval(AppState.countdownTimer);
    }
}

/**
 * Met à jour le timestamp de dernière mise à jour
 */
function updateTimestamp() {
    if (!DOM.lastUpdate) return;

    const now = new Date();
    const timeString = now.toLocaleTimeString('fr-FR');
    DOM.lastUpdate.textContent = timeString;
    AppState.lastUpdate = now;
}

/**
 * Démarre le rafraîchissement automatique
 */
function startAutoRefresh() {
    if (AppState.refreshTimer) {
        clearInterval(AppState.refreshTimer);
    }

    AppState.refreshTimer = setInterval(() => {
        if (AppState.currentEvent && AppState.currentPhase && !AppState.isLoading) {
            loadLeaderboard(AppState.currentEvent, AppState.currentPhase)
                .catch(error => {
                    console.error('Auto-refresh failed:', error);
                });
        }
    }, CONFIG.REFRESH_INTERVAL);
}

/**
 * Arrête le rafraîchissement automatique
 */
function stopAutoRefresh() {
    if (AppState.refreshTimer) {
        clearInterval(AppState.refreshTimer);
        AppState.refreshTimer = null;
    }
}

/**
 * Gère les changements de visibilité de la page
 */
function handleVisibilityChange() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
        // Rafraîchir immédiatement si la page redevient visible
        if (AppState.currentEvent && AppState.currentPhase) {
            loadLeaderboard(AppState.currentEvent, AppState.currentPhase)
                .catch(error => {
                    console.error('Refresh on visibility change failed:', error);
                });
        }
    }
}

/**
 * Gère les erreurs globales
 */
function handleGlobalError(event) {
    console.error('Global error:', event.error);
    showError('Une erreur inattendue s\'est produite');
}

/**
 * Formate une date/heure
 */
function formatDateTime(dateString) {
    if (!dateString) return 'Non renseigné';

    try {
        // Convertir le format "YYYY-MM-DD HH:MM:SS" en ISO
        const [datePart, timePart] = dateString.split(' ');
        const [year, month, day] = datePart.split('-');
        const [hours, minutes, seconds] = timePart.split(':');
        const isoString = `${year}-${month}-${day}T${hours}:${minutes}:${seconds}`;
        
        const date = new Date(isoString);
        const now = new Date();
        const diff = now - date;

        // Moins d'une minute
        switch (true) {
            case diff < 60000:
                return 'À l\'instant';
            case diff < 3600000:
                const minutes = Math.floor(diff / 60000);
                return `Il y a ${minutes} min`;
            case diff < 86400000:
                const hours = Math.floor(diff / 3600000);
                return `Il y a ${hours}h`;
            default:
                return date.toLocaleDateString('fr-FR', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
        }
    } catch (error) {
        return 'Date invalide';
    }
}

/**
 * Échappe les caractères HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Retourne l'icône appropriée pour le type de notification
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'alert-circle',
        warning: 'alert-triangle',
        info: 'info'
    };

    return icons[type] || 'info';
}

/**
 * Utilitaire pour déboguer l'état de l'application
 */
function debugState() {
    console.log('Current Application State:', {
        currentEvent: AppState.currentEvent,
        currentPhase: AppState.currentPhase,
        isLoading: AppState.isLoading,
        lastUpdate: AppState.lastUpdate,
        refreshTimer: AppState.refreshTimer ? 'Active' : 'Inactive'
    });
}

// Exposition des fonctions utiles pour le développement
// window.LeaderboardApp = {
//     debugState,
//     showNotification,
//     loadLeaderboard: (eventId, phaseId) => loadLeaderboard(eventId, phaseId),
//     updateTimestamp
// };