// Configuration de base
const API_BASE_URL = '/HACKATHON_ESGIS/public/api';
const DASHBOARD_ELEMENTS = {
    username: '.Username',
    email: '.Email',
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
        template: '.current-challenge-item' // Sélecteur du template existant dans le HTML
    },
    recentActivities: {
        container: '#recent-activities-container',
        template: '.recent-activity-item' 
    },
    nextEvent: {
        container: '#next-event-container',
        title: '.next-event-title',
        description: '.next-event-description',
        startDate: '.next-event-start-date',
        endDate: '.next-event-end-date',
        location: '.next-event-location',
        noEventMessage: '#no-next-event'
    }
};
/**
 * Met à jour la section des défis en cours
 * @param {Array} challenges - Liste des défis en cours
 */
function updateCurrentChallenges(challenges) {
    const container = document.querySelector(DASHBOARD_ELEMENTS.currentChallenges.container);

    // Cache le conteneur si aucun défi
    if (!challenges || challenges.length === 0) {
        container.style.display = 'none';
        return;
    }

    // Affiche le conteneur
    container.style.display = 'block';

    // Supprime tous les défis existants sauf le premier (qui sert de template)
    const items = container.querySelectorAll(DASHBOARD_ELEMENTS.currentChallenges.template);
    for (let i = 1; i < items.length; i++) {
        items[i].remove();
    }

    // Clone et remplit le template pour chaque défi (en commençant par l'index 1)
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
    element.querySelector('.challenge-title').textContent = challenge.title || 'Titre inconnu';
    element.querySelector('.challenge-description').textContent = challenge.description || 'Description non disponible';
    element.querySelector('.challenge-deadline').textContent = challenge.deadline ? `Date limite: ${challenge.deadline}` : 'Pas de date limite';
    element.querySelector('.challenge-progress').textContent = challenge.progress || 'Progression inconnue';
}

/**
 * Met à jour la section des activités récentes
 * @param {Array} activities - Liste des activités récentes
 */
function updateRecentActivities(activities) {
    const container = document.querySelector(DASHBOARD_ELEMENTS.recentActivities.container);
    
    if (!container) {
        console.error('Conteneur des activités récentes non trouvé');
        return;
    }

    // Cache le conteneur si aucune activité
    if (!activities || activities.length === 0) {
        container.style.display = 'none';
        return;
    }

    // Affiche le conteneur
    container.style.display = 'block';

    // Supprime toutes les activités existantes sauf la première (qui sert de template)
    const items = container.querySelectorAll(DASHBOARD_ELEMENTS.recentActivities.template);
    for (let i = 1; i < items.length; i++) {
        items[i].remove();
    }

    // Clone et remplit le template pour chaque activité (en commençant par l'index 1)
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
 * Met à jour un élément d'activité individuelle
 */
function updateActivityItem(element, activity) {
    const icon = element.querySelector('.activity-icon');
    
    if (icon) {
        // Vous pouvez personnaliser l'icône en fonction du type d'activité
        const iconMap = {
            'trophy': 'trophy',
            'file': 'file-text',
            'code': 'code',
            'flag': 'flag'
        };
        const iconName = iconMap[activity.type] || 'activity';
        icon.setAttribute('data-lucide', iconName);
    }
    
    element.querySelector('.activity-text').textContent = activity.text || 'Activité inconnue';
    element.querySelector('.activity-time').textContent = activity.time || 'Récemment';
}
// Fonction utilitaire pour gérer les erreurs
function handleError(title = 'Une erreur est survenue', error = null, type = 'error') {
    console.error(title, error);
    setFlashMessage(type,title, error.message);
    // Vous pouvez ajouter ici une gestion d'erreur plus élaborée (affichage d'une modale, etc.)
}

// Fonction utilitaire pour gérer les requêtes API
async function apiRequest(endpoint, options = {}) {
    try {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'

        };

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...options,
            headers: { ...headers, ...options.headers }
        });

        if (!response.ok) {
            throw new Error(`Erreur API: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data;  // Retourne bien les données récupérées
    } catch (error) {
        handleError('Erreur lors de la requête API', error, 'error');
        throw error;
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
            throw new Error('Utilisateur non authentifié. Dashboard');
        }

        const data = await response.json();
        return data.data.id;  // Retourne bien l'ID utilisateur
    } catch (error) {
        handleError('Impossible de récupérer l\'ID utilisateur.', error, 'error');
        return null;
    }
}


// Fonction pour mettre à jour les éléments du DOM
/*
function updateDOM(elements, data) {
    Object.entries(elements).forEach(([key, selector]) => {
        const elements = document.querySelectorAll(selector);
        if (elements.length > 0) {
            elements.forEach(element => {
                element.textContent = data.data[key] || 'N/A';
            });
        }else{
            console.error('Éléments non trouvés', selector);
        }
    });
}*/
function updateDOM(elements, data) {
    Object.entries(elements).forEach(([key, selector]) => {
        const elements = document.querySelectorAll(selector);
        if (elements.length > 0) {
            elements.forEach(element => {
                let value = 'N/A';
                if (data && data.data) {
                    if (data.data.stats && data.data.stats[key]) {
                        value = data.data.stats[key];
                    } else if (data.data[key]) {
                        value = data.data[key];
                    }
                }
                element.textContent = value;
            });
        } else {
            console.error('Éléments non trouvés', selector);
        }
    });
}

// Fonction pour initialiser le dashboard
async function initializeDashboard() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }

        // Charger les données de base
        await Promise.all([
            loadUserInfo(userId),
            loadStatistics(),
            loadNotifications(),
            loadNextEvent()
        ]);

        // Mettre en place les écouteurs d'événements
        setupEventListeners();

    } catch (error) {
        handleError('Erreur lors de l\'initialisation du dashboard', error, 'error');
    }
}

// Fonction pour charger les informations de l\'utilisateur
async function loadUserInfo(userId) {
    try {
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
/*
async function loadStatistics() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }
        const data = await apiRequest(`/users/${userId}/stats`);
        console.log(data);
        updateDOM(DASHBOARD_ELEMENTS.stats, data);
    } catch (error) {
        handleError('Erreur lors de la récupération des statistiques', error, 'error');
    }
}*/

async function loadStatistics() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }

        const data = await apiRequest(`/users/${userId}/dashboard-data`); // Appel au nouvel endpoint

        if (data.success && data.data) {
            console.log('Dashboard Data:', data.data);

            // Mettre à jour les statistiques
            updateDOM({
                devChallenges: DASHBOARD_ELEMENTS.stats.devChallenges,
                hackingChallenges: DASHBOARD_ELEMENTS.stats.hackingChallenges,
                devChallengesOn: DASHBOARD_ELEMENTS.stats.devChallengesOn,
                hackingChallengesValidate: DASHBOARD_ELEMENTS.stats.hackingChallengesValidate,
                submittedProjects: DASHBOARD_ELEMENTS.stats.submittedProjects,
                totalPoints: DASHBOARD_ELEMENTS.stats.totalPoints,
                // Vous devrez peut-être ajouter des éléments HTML pour afficher ces nouvelles données
                // devStat: DASHBOARD_ELEMENTS.stats.devStat,
                hackingStat: DASHBOARD_ELEMENTS.stats.hackingStat,
                totalPointsStat: DASHBOARD_ELEMENTS.stats.totalPointsStat,
                validated_flags: '#validated-flags-count', // Exemple de sélecteur pour les flags validés
                points_change: '#points-change', // Exemple de sélecteur pour le changement de points
            }, { data: {
                'number-dev-challenges': data.data.dev_challenges,
                'number-hacking-challenges': data.data.hacking_challenges,
                'number-dev-challenges-on': data.data.ongoing_dev_challenges,
                'number-hacking-challenges-validate': 0, // Vous devrez peut-être calculer ça côté JS si ce n'est pas direct
                'number-submitted-projects': data.data.submitted_projects,
                'total-points': data.data.total_points,
                'validated_flags_count': data.data.validated_flags,
                'points_change': data.data.points_change,
                // Vous devrez peut-être calculer les pourcentages de stat côté JS avec les données brutes
                // 'dev-stat': ...,
                // 'hacking-stat': ...,
                // 'total-points-stat': ...,
            }});

            // Mettre à jour les activités récentes
            updateRecentActivities(data.data.recent_activity || []);

            // Vous n'avez plus besoin d'appels séparés pour les défis en cours et les activités récentes ici
            // car ils sont inclus dans la réponse de /dashboard-data
            // const challengesResponse = await apiRequest(`/users/${userId}/current-challenges`);
            // updateCurrentChallenges(challengesResponse.data || []);
            // const activitiesResponse = await apiRequest(`/users/${userId}/recent-activities`);
            // updateRecentActivities(activitiesResponse.data || []);

        } else {
            handleError('Erreur lors de la récupération des données du dashboard', data.error);
        }

    } catch (error) {
        handleError('Erreur lors de la récupération des données du dashboard', error);
    }
}

// Fonction pour charger les notifications
async function loadNotifications() {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }
        const data = await apiRequest(`/users/${userId}/notifications`);
        console.log(data);
        const notificationsContainer = document.querySelector('.notifications-list');
        if (notificationsContainer) {
            notificationsContainer.innerHTML = data.map(notification => `
                <div class="notification-item ${notification.read ? 'read' : ''}" data-id="${notification.id}">
                    <p>${notification.message}</p>
                    <span class="timestamp">${notification.timestamp}</span>
                </div>
            `).join('');

            // Ajouter les écouteurs pour les notifications
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', async (e) => {
                    const notificationId = item.dataset.id;
                    if (notificationId) {
                        await markNotificationAsRead(notificationId);
                        item.classList.add('read');
                    }
                });
            });
        }
    } catch (error) {
        handleError('Erreur lors de la récupération des notifications', error, 'error');
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
            // Afficher le conteneur et masquer le message d'absence d'événement
            if (nextEventContainer) nextEventContainer.style.display = 'block';
            if (noEventMessage) noEventMessage.style.display = 'none';

            // Mettre à jour les informations de l'événement
            document.querySelector(DASHBOARD_ELEMENTS.nextEvent.title).textContent = data.data.name || 'Événement inconnu';
            document.querySelector(DASHBOARD_ELEMENTS.nextEvent.description).textContent = data.data.description || 'Description non disponible';
            document.querySelector(DASHBOARD_ELEMENTS.nextEvent.startDate).textContent = data.data.start_date ? formatDate(data.data.start_date) : 'Date de début inconnue';
            document.querySelector(DASHBOARD_ELEMENTS.nextEvent.endDate).textContent = data.data.end_date ? formatDate(data.data.end_date) : 'Date de fin inconnue';
            document.querySelector(DASHBOARD_ELEMENTS.nextEvent.location).textContent = data.data.location || 'Lieu inconnu';

        } else {
            // Masquer le conteneur et afficher le message d'absence d'événement
            if (nextEventContainer) nextEventContainer.style.display = 'none';
            if (noEventMessage) noEventMessage.style.display = 'block';
        }

    } catch (error) {
        handleError('Erreur lors de la récupération du prochain événement', error, 'error');
        const nextEventContainer = document.querySelector(DASHBOARD_ELEMENTS.nextEvent.container);
        const noEventMessage = document.querySelector(DASHBOARD_ELEMENTS.nextEvent.noEventMessage);
        if (nextEventContainer) nextEventContainer.style.display = 'none';
        if (noEventMessage) noEventMessage.style.display = 'block';
    }
}


// Fonction pour marquer une notification comme lue
async function markNotificationAsRead(notificationId) {
    try {
        const userId = await getUserId();
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }
        await apiRequest(`/users/${userId}/notifications/${notificationId}/read`, { method: 'POST' });
    } catch (error) {
        handleError('Erreur lors de la mise à jour de la notification', error, 'error');
    }
}

// Fonction pour mettre en place les écouteurs d'événements
function setupEventListeners() {
    // Gestion des clics sur les notifications
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', async (e) => {
            const notificationId = item.dataset.id;
            if (notificationId) {
                await markNotificationAsRead(notificationId);
                item.classList.add('read');
            }
        });
    });

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

    // Gestion du bouton de déconnexion
    const logoutButton = document.querySelector('.logout-button');
    if (logoutButton) {
        logoutButton.addEventListener('click', () => {
            // Supprimer les cookies
            document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            document.cookie = 'long_term_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            
            // Rediriger vers la page de connexion
            window.location.href = '/HACKATHON_ESGIS/public/auth';
        });
    }

    // Gestion du bouton de mise à jour du profil
    const updateProfileButton = document.querySelector('.update-profile');
    if (updateProfileButton) {
        updateProfileButton.addEventListener('click', () => {
            const modal = document.querySelector('.profile-modal');
            if (modal) {
                modal.classList.add('active');
            }
        });
    }

    // Gestion de la fermeture des modals
    document.querySelectorAll('.modal-close').forEach(button => {
        button.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Gestion des formulaires de mise à jour
    const updateForms = document.querySelectorAll('form[data-action="update"]');
    updateForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const endpoint = form.dataset.endpoint;
            
            try {
                await apiRequest(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                // Mettre à jour l'interface
                const modal = form.closest('.modal');
                if (modal) {
                    modal.classList.remove('active');
                }
                
                // Recharger les données
                await Promise.all([
                    loadUserInfo(await getUserId()),
                    loadStatistics()
                ]);
            } catch (error) {
                handleError('Erreur lors de la mise à jour', error, 'error');
            }
        });
    });
}

// Fonction pour mettre en place les écouteurs d'événements
document.addEventListener('DOMContentLoaded', () => {


    // Ajout du comportement de défilement en douceur
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Initialisation du dashboard
    initializeDashboard();
});