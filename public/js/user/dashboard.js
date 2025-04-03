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
    }
};

// Fonction utilitaire pour gérer les erreurs
function handleError(title = 'Une erreur est survenue', error, type = 'error') {
    console.error(title, error);
    setFlashMessage(type,title, error);
    // Vous pouvez ajouter ici une gestion d'erreur plus élaborée (affichage d'une modale, etc.)
}

// Fonction utilitaire pour gérer les requêtes API
async function apiRequest(endpoint, options = {}) {
    try {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...options,
            headers: { ...headers, ...options.headers }
        });

        if (!response.ok) {
            throw new Error(`Erreur API: ${response.status} ${response.statusText}`);
        }

        const data = await response.json();
        return data;  // ✅ Retourne bien les données récupérées
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
            throw new Error('Non authentifié. Dashboard');
        }

        const data = await response.json();
        return data.data.id;  // Retourne bien l'ID utilisateur
    } catch (error) {
        handleError('Impossible de récupérer l\'ID utilisateur.', error, 'error');
        return null;
    }
}


// Fonction pour mettre à jour les éléments du DOM
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
            // loadNotifications()
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
    // Initialisation de Lucide
    lucide.createIcons();

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