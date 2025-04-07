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
    currentHackathons: {
        container: '#current-hackathons-container',
        template: '.current-hackathon-item' // Sélecteur du template existant dans le HTML
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
    },
    upcomingHackathons: {
        container: '#upcoming-hackathons-container',
        item: '.upcoming-hackathon-item',
        title: '.hackathon-title',
        description: '.hackathon-description',
        startDate: '.hackathon-start-date',
        endDate: '.hackathon-end-date',
        noHackathonsMessage: '#no-upcoming-hackathons'
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
/**
 * Met à jour la section des hackathons en cours
 * @param {Array} hackathons - Liste des hackathons en cours
 */
function updateCurrentHackathons(hackathons) {
    const container = document.querySelector(DASHBOARD_ELEMENTS.upcomingHackathons.container);
    const noHackathonsMessage = document.querySelector(DASHBOARD_ELEMENTS.upcomingHackathons.noHackathonsMessage);

    if (!container || !noHackathonsMessage) {
        console.error('Éléments du DOM pour les hackathons introuvables');
        return;
    }

    // Cache le message si des hackathons existent
    noHackathonsMessage.style.display = hackathons.length === 0 ? 'block' : 'none';

    // Vide le conteneur
    container.innerHTML = '';

    // Ajoute chaque hackathon
    hackathons.forEach(hackathon => {
        const item = document.createElement('div');
        item.className = 'upcoming-hackathon-item flex flex-col gap-2 justify-between bg-(--card-bg) p-4 rounded-xl border border-gray-700 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 mb-3';
        
        item.innerHTML = `
            <div>
                <h4 class="text-md font-semibold hackathon-title">${hackathon.name || 'Hackathon sans nom'}</h4>
                <p class="text-gray-400 text-sm hackathon-description">${hackathon.description || 'Aucune description disponible'}</p>
            </div>
            <div class="flex flex-row gap-4 mt-2">
                <p class="flex items-center text-gray-500 text-sm">
                    <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                    <span class="hackathon-start-date">${hackathon.start_date ? formatDate(hackathon.start_date) : 'Date inconnue'}</span>
                </p>
                <p class="flex items-center text-gray-500 text-sm">
                    <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                    <span class="hackathon-end-date">${hackathon.end_date ? formatDate(hackathon.end_date) : 'Date inconnue'}</span>
                </p>
            </div>
            <div class="flex justify-end mt-2">
                <a href="/HACKATHON_ESGIS/public/user/hackathon/details/${hackathon.id}" class="text-blue-500 hover:underline text-sm">Voir les détails</a>
            </div>
        `;
        
        container.appendChild(item);
    });

    // Rafraîchit les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}
/**
 * Met à jour un élément de hackathon individuel
 */
function updateHackathonItem(element, hackathon) {
    if (!hackathon) {
        console.error('Hackathon invalide:', hackathon);
        return;
    }

    const titleElement = element.querySelector('.hackathon-title');
    const descriptionElement = element.querySelector('.hackathon-description');
    const startDateElement = element.querySelector('.hackathon-start-date');
    const endDateElement = element.querySelector('.hackathon-end-date');

    if (titleElement) {
        titleElement.textContent = hackathon.title || 'Hackathon inconnu';
    } else {
        console.error('Élément de titre introuvable');
    }

    if (descriptionElement) {
        descriptionElement.textContent = hackathon.description || 'Description non disponible';
    } else {
        console.error('Élément de description introuvable');
    }

    if (startDateElement) {
        startDateElement.textContent = hackathon.start_date ? `Début: ${hackathon.start_date}` : 'Pas de date de début';
    } else {
        console.error('Élément de date de début introuvable');
    }

    if (endDateElement) {
        endDateElement.textContent = hackathon.end_date ? `Fin: ${hackathon.end_date}` : 'Pas de date de fin';
    } else {
        console.error('Élément de date de fin introuvable');
    }
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
            loadNextEvent(),
            loadCurrentChallenges(userId),
            loadCurrentHackathons(userId)
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
        
        // Charge les statistiques
        const statsResponse = await apiRequest(`/users/${userId}/stats`);
        console.log('Stats:', statsResponse);
        updateDOM(DASHBOARD_ELEMENTS.stats, statsResponse);
        
        // Charge les défis en cours
        const challengesResponse = await apiRequest(`/users/${userId}/current-challenges`);
        console.log('Défis en cours:', challengesResponse);
        updateCurrentChallenges(challengesResponse.data || []);
        
        // Charge les activités récentes
        const activitiesResponse = await apiRequest(`/users/${userId}/recent-activities`);
        console.log('Activités récentes:', activitiesResponse);
        updateRecentActivities(activitiesResponse.data || []);
        
    } catch (error) {
        handleError('Erreur lors de la récupération des données du dashboard', error, 'error');
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
async function loadCurrentChallenges(userId) {
    try {
        const data = await apiRequest(`/users/${userId}/current-challenges`);
        console.log('Défis en cours:', data);
        updateCurrentChallenges(data.data || []);
    } catch (error) {
        handleError('Erreur lors de la récupération des défis en cours', error, 'error');
    }
}
async function loadCurrentHackathons(userId) {
    try {
        const data = await apiRequest(`/users/${userId}/current-hackathons`);
        console.log('Hackathons en cours:', data);
        updateCurrentHackathons(data.data || []);
    } catch (error) {
        handleError('Erreur lors de la récupération des hackathons en cours', error, 'error');
        // Affiche le message "Aucun hackathon" en cas d'erreur
        const noHackathonsMessage = document.querySelector(DASHBOARD_ELEMENTS.upcomingHackathons.noHackathonsMessage);
        if (noHackathonsMessage) noHackathonsMessage.style.display = 'block';
    }
}
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('fr-FR', options);
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