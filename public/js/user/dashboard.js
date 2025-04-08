import { createEle } from '/HACKATHON_ESGIS/public/js/dom.js';
// Configuration de base
const API_BASE_URL = '/HACKATHON_ESGIS/public/api';
const DASHBOARD_ELEMENTS = {
    username: '.Username',
    email: '.Email',
    loadingSpinner: '#global-loading-spinner',
    stats: {
        devChallenges: '#number-dev-challenges',
        hackingChallenges: '#number-hacking-challenges',
        devChallengesOn: '#number-dev-challenges-on',
        hackingChallengesValidate: '#number-hacking-challenges-validate',
        submittedProjects: '#number-submitted-projects',
        totalPoints: '#total-points',
        devStat: '#dev-stat',
        hackingStat: '#hacking-stat',
        totalPointsStat: '#total-points-stat'
    },
    currentChallenges: {
        container: '#current-challenges-container',
        template: '.current-challenge-item',
        emptyState: '#no-current-challenges'
    },
    recentActivities: {
        container: '#recent-activities-container',
        template: '.recent-activity-item',
        emptyState: '#no-recent-activities'
    },
    nextEvent: {
        container: '#next-event-container',
        title: '.next-event-title',
        description: '.next-event-description',
        startDate: '.next-event-start-date',
        endDate: '.next-event-end-date',
        location: '.next-event-location',
        noEventMessage: '#no-next-event'
    },
};

/**
 * Met à jour la section des défis en cours
 * @param {Array} challenges - Liste des défis en cours
 */
function updateCurrentChallenges(challenges) {
    const container = document.querySelector(DASHBOARD_ELEMENTS.currentChallenges.container);
    const emptyState = document.querySelector(DASHBOARD_ELEMENTS.currentChallenges.emptyState);

    if (!container) {
        console.error('Conteneur des défis en cours non trouvé');
        return;
    }

    // Cache le conteneur si aucun défi
    if (!challenges || challenges.length === 0) {
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }

    // Affiche le conteneur et cache l'empty state
    if (emptyState) emptyState.style.display = 'none';

    // Supprime tous les défis existants sauf le premier (qui sert de template)
    const items = container.querySelectorAll(DASHBOARD_ELEMENTS.currentChallenges.template);
    for (let i = 1; i < items.length; i++) {
        items[i].remove();
    }

    // Clone et remplit le template pour chaque défi
    challenges.forEach((challenge, index) => {
        if (index === 0) {
            // Met à jour le premier élément (template)
            updateChallengeItem(items[0], challenge);
        } else {
            // Clone et ajoute pour les autres défis
            const clone = items[0].cloneNode(true);
            updateChallengeItem(clone, challenge);
            container.appendChild(clone);
        }
    });
}

/**
 * Met à jour un élément de défi individuel
 */
function updateChallengeItem(element, challenge) {
    element.querySelector('.challenge-title').textContent = challenge.title || challenge.name || 'Titre inconnu';
    element.querySelector('.challenge-description').textContent = challenge.description || 'Description non disponible';

    if (challenge.deadline || challenge.end_date) {
        const deadline = challenge.deadline || challenge.end_date;
        const date = new Date(deadline);
        element.querySelector('.challenge-deadline').textContent = `Date limite: ${date.toLocaleDateString()}`;
    } else {
        element.querySelector('.challenge-deadline').textContent = 'Pas de date limite';
    }

    // Mise à jour de la barre de progression
    // const progress = challenge.progress || 0;
    // element.querySelector('.challenge-progress-text').textContent = `Progression: ${progress}%`;
    // const progressBar = element.querySelector('.challenge-progress-bar');
    // if (progressBar) {
    //     progressBar.style.width = `${progress}%`;
    //     progressBar.setAttribute('aria-valuenow', progress);
    // }
}

/**
 * Met à jour la section des activités récentes
 * @param {Array} activities - Liste des activités récentes
 */
function updateRecentActivities(activities) {
    const container = document.querySelector(DASHBOARD_ELEMENTS.recentActivities.container);
    const emptyState = document.querySelector(DASHBOARD_ELEMENTS.recentActivities.emptyState);

    if (!container) {
        console.error('Conteneur des activités récentes non trouvé');
        return;
    }

    // Cache le conteneur si aucune activité
    if (!activities || activities.length === 0) {
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }

    // Affiche le conteneur et cache l'empty state
    container.style.display = 'flex';
    if (emptyState) emptyState.style.display = 'none';

    // Supprime toutes les activités existantes sauf la première (qui sert de template)
    const items = container.querySelectorAll(DASHBOARD_ELEMENTS.recentActivities.template);
    for (let i = 1; i < items.length; i++) {
        items[i].remove();
    }

    // Clone et remplit le template pour chaque activité
    activities.forEach((activity, index) => {
        if (index === 0) {
            // Met à jour le premier élément (template)
            updateActivityItem(items[0], activity);
        } else {
            // Clone et ajoute pour les autres activités
            const clone = items[0].cloneNode(true);
            updateActivityItem(clone, activity);
            container.appendChild(clone);
        }
    });
}

/**
 * Nettoie le texte pour prévenir les attaques XSS
 */
function sanitizeText(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Met à jour un élément d'activité individuelle
 */
function updateActivityItem(element, activity) {
    const icon = element.querySelector('.activity-icon');
    const textElement = element.querySelector('.activity-text');
    const timeElement = element.querySelector('.activity-time');

    // Détermine la classe CSS en fonction du niveau d'activité
    const activityClass = {
        'info': 'activity-info',
        'error': 'activity-error',
        'success': 'activity-success',
        'warning': 'activity-warning',
        'register_error': 'activity-error'
    }[activity.level] || 'activity-default';

    element.classList.add(activityClass);

    if (icon) {
        // Détermine l'icône en fonction du type ou niveau d'activité
        const iconMap = {
            'info': 'info',
            'error': 'alert-circle',
            'success': 'check-circle',
            'warning': 'alert-triangle',
            'register_error': 'alert-circle',
            'default': 'activity'
        };

        const iconName = iconMap[activity.level] ||
            iconMap[activity.action] ||
            iconMap['default'];
        icon.setAttribute('data-lucide', iconName);
    }

    if (textElement) {
        // Filtre les messages d'erreur SQL
        let description = activity.description || activity.action || 'Activité inconnue';
        if (activity.level === 'error' && description.includes('SQLSTATE')) {
            description = "Une erreur système est survenue";
        }
        textElement.innerHTML = sanitizeText(description);
    }

    if (timeElement) {
        timeElement.textContent = activity.created_at ?
            formatDate(activity.created_at) :
            'Récemment';
    }

    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// Fonction pour formater les dates
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';

        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        };
        return date.toLocaleDateString('fr-FR', options);
    } catch (e) {
        console.error('Erreur de formatage de date', e);
        return 'Date inconnue';
    }
}

// Fonction utilitaire pour gérer les erreurs
function handleError(title = 'Une erreur est survenue', error = null, type = 'error') {
    console.error(title, error);

    // Affiche une notification à l'utilisateur
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i data-lucide="${type === 'error' ? 'alert-circle' : 'info'}"></i>
        <span>${sanitizeText(title)}</span>
    `;

    const notificationContainer = document.querySelector('.notifications-container') || document.body;
    notificationContainer.appendChild(notification);

    // Supprime la notification après 5 secondes
    setTimeout(() => {
        notification.remove();
    }, 5000);

    // Actualise les icônes
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

// Fonction utilitaire pour gérer les requêtes API
async function apiRequest(endpoint, options = {}) {
    showLoading();

    try {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...options,
            headers: { ...headers, ...options.headers },
            credentials: 'include'
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(
                errorData.message ||
                `Erreur API: ${response.status} ${response.statusText}`
            );
        }

        return await response.json();
    } catch (error) {
        handleError('Erreur lors de la requête API', error, 'error');
        throw error;
    } finally {
        setTimeout(() => {
            hideLoading();
        }, 1000);
    }
}

// Afficher le spinner de chargement
function showLoading() {
    const spinner = document.querySelector(DASHBOARD_ELEMENTS.loadingSpinner);
    if (spinner) {
        spinner.classList.remove('opacity-0', 'pointer-events-none');
        spinner.classList.add('opacity-100');
    }
}

// Cacher le spinner de chargement
function hideLoading() {
    const spinner = document.querySelector(DASHBOARD_ELEMENTS.loadingSpinner);
    if (spinner) {
        spinner.classList.remove('opacity-100');
        spinner.classList.add('opacity-0', 'pointer-events-none');
    }
}

// Fonction pour récupérer l'ID utilisateur
async function getUserId() {
    try {
        const response = await fetch('/HACKATHON_ESGIS/public/api/users/me', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Utilisateur non authentifié');
        }

        const data = await response.json();
        return data.data?.id;
    } catch (error) {
        handleError('Impossible de récupérer l\'ID utilisateur', error, 'error');
        return null;
    }
}

// Fonction pour mettre à jour les éléments du DOM
function updateDOM(elements, data) {
    Object.entries(elements).forEach(([key, selector]) => {
        const element = document.querySelectorAll(selector);
        if (element) {
            element.forEach(element => {

                let value = data.data?.stats?.[key] ||
                    data.data?.[key] ||
                    data[key] ||
                    'N/A';

                if (typeof value === 'number') {
                    value = value.toString();
                }

                element.textContent = value;

                // Ajoute des classes pour les états vides
                if (value === '0' || value === 0) {
                    element.classList.add('empty-stat');
                } else if (value === 'N/A' || !value) {
                    element.classList.add('na-stat');
                }
            });
        }
    });
}

// Fonction pour charger les informations de l'utilisateur
async function loadUserInfo(userId) {
    try {
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }
        const data = await apiRequest(`/users/${userId}`);
        updateDOM({
            username: DASHBOARD_ELEMENTS.username,
            email: DASHBOARD_ELEMENTS.email
        }, data);
    } catch (error) {
        handleError('Erreur lors de la récupération des informations utilisateur', error, 'error');
    }
}

// Fonction pour charger les statistiques
async function loadStatistics() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }

        // Charge les statistiques
        const statsResponse = await apiRequest(`/users/${userId}/stats`);
        console.log('Stats:', statsResponse);

        if (statsResponse.success && statsResponse.data) {
            updateDOM(DASHBOARD_ELEMENTS.stats, statsResponse);

            // Met à jour la barre de progression globale
            const totalPoints = statsResponse.data.stats?.['total-points'] || 0;
            const maxPoints = 56; // Valeur fixe selon votre dashboard
            const progressPercent = Math.round((totalPoints / maxPoints) * 100);

            const progressBar = document.querySelector('.global-progress-bar');
            if (progressBar) {
                progressBar.style.width = `${progressPercent}%`;
                progressBar.setAttribute('aria-valuenow', progressPercent);
                progressBar.textContent = `${progressPercent}%`;
            }
        }

        // Charge les activités récentes
        const activitiesResponse = await apiRequest(`/users/${userId}/recent-activities`);
        console.log('Activités récentes:', activitiesResponse);

        if (activitiesResponse.success) {
            updateRecentActivities(activitiesResponse.data || []);
        }

    } catch (error) {
        handleError('Erreur lors de la récupération des données du dashboard', error, 'error');
    }
}

async function loadCurrentChalenge() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }

        // Charge les défis en cours
        const challengesResponse = await apiRequest(`/users/${userId}/current-challenges`);
        console.log('Défis en cours:', challengesResponse);

        if (challengesResponse.success) {
            updateCurrentChallenges(challengesResponse.data || []);
        }
    } catch (error) {
        handleError('Erreur lors de la récupération des défis en cours', error, 'error');
    }
}

async function loadRecentActivity() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }

        // Charge les activités récentes
        const activitiesResponse = await apiRequest(`/users/${userId}/recent-activities`);
        console.log('Activités récentes:', activitiesResponse);

        if (activitiesResponse.success) {
            updateRecentActivities(activitiesResponse.data || []);
        }
    } catch (error) {
        handleError('Erreur lors de la récupération des activités récentes', error, 'error');
    }
}

// Fonction pour charger le prochain événement
async function loadNextEvent() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }

        const data = await apiRequest(`/users/${userId}/next-event`);
        const nextEventContainer = document.querySelector(DASHBOARD_ELEMENTS.nextEvent.container);
        const noEventMessage = document.querySelector(DASHBOARD_ELEMENTS.nextEvent.noEventMessage);

        if (data.success && data.data) {
            noEventMessage.style.display = 'none';
            console.log('Prochain événement:', data.data);
            const eventDiv = createEle('div', {
                class: 'flex flex-col gap-2 justify-between bg-(--card-bg) p-4 rounded-xl border border-gray-700 transition delay-150 duration-300 ease-in-out hover:-translate-y-1'
            })
            eventDiv.innerHTML = `
                    <p class="text-md font-semibold">${data.data.name || 'Événement inconnu'}</p>
                    <p class="text-gray-500 text-sm">${data.data.start_date ? formatDate(data.data.start_date) : 'Date de début inconnue'}</p>
                    <span class="text-green-400 bg-emerald-950 flex self-start text-xs p-2 rounded-xl">Bientôt</span>
            `;
            if (nextEventContainer) nextEventContainer.appendChild(eventDiv);
        } else {
            if (nextEventContainer) nextEventContainer.style.display = 'none';
            if (noEventMessage) noEventMessage.style.display = 'flex';
        }

    } catch (error) {
        handleError('Erreur lors de la récupération du prochain événement', error, 'error');
        const nextEventContainer = document.querySelector(DASHBOARD_ELEMENTS.nextEvent.container);
        const noEventMessage = document.querySelector(DASHBOARD_ELEMENTS.nextEvent.noEventMessage);
        if (nextEventContainer) nextEventContainer.style.display = 'none';
        if (noEventMessage) noEventMessage.style.display = 'flex';
    }
}

// Fonction pour initialiser le dashboard
async function initializeDashboard() {
    try {
        showLoading();

        const userId = await getUserId();
        // Charger les données de base
        await Promise.all([
            loadUserInfo(userId),
            loadStatistics(),
            loadCurrentChalenge(),
            // loadRecentActivity(),
            loadNextEvent()
        ]);

        // Mettre en place les écouteurs d'événements
        setupEventListeners();

        // Configurer le rafraîchissement automatique
        setupAutoRefresh();

    } catch (error) {
        handleError('Erreur lors de l\'initialisation du dashboard', error, 'error');
    } finally {
        setTimeout(() => {
            hideLoading();
        }, 1000);
    }
}

// Configuration du rafraîchissement automatique
function setupAutoRefresh() {
    // Rafraîchir toutes les 5 minutes
    setInterval(loadStatistics, 300000);

    // Rafraîchir lors du retour en ligne
    window.addEventListener('online', () => {
        loadStatistics();
    });
}

// Fonction pour mettre en place les écouteurs d'événements
function setupEventListeners() {
    // Gestion du bouton de mise à jour des statistiques
    const refreshStatsButton = document.querySelector('.refresh-stats');
    if (refreshStatsButton) {
        refreshStatsButton.addEventListener('click', async () => {
            try {
                await loadStatistics();
            } catch (error) {
                handleError('Erreur lors de la mise à jour des statistiques', error, 'error');
            }
        });
    }

    // Gestion des notifications
    const notificationButtons = document.querySelectorAll('.notification-dismiss');
    notificationButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.target.closest('.notification').remove();
        });
    });
}

// Initialisation lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    // Initialisation des icônes Lucide si disponible
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Initialisation du dashboard
    initializeDashboard().then(() => {
        console.log('Dashboard initialisé avec succès');
    }).catch(error => {
        console.error('Erreur lors de l\'initialisation du dashboard:', error);
    });
});