// Configuration de base
const API_BASE_URL = '/api';
const ADMIN_DASHBOARD_ELEMENTS = {
    loadingSpinner: '#global-loading-spinner',
    stats: {
        hackathonsCount: '#hackathons-count',
        challengesCount: '#challenges-count',
        usersCount: '#users-count',
        teamsCount: '#teams-count'
    },
    activityFeed: {
        container: '#activity-feed',
        emptyState: '#no-recent-activity'
    },
    upcomingHackathons: {
        container: '#upcoming-hackathons',
        emptyState: '#no-upcoming-hackathons'
    },
    popularChallenges: {
        container: '#popular-challenges',
        emptyState: '#no-popular-challenges'
    },
    activeTeams: {
        container: '#active-teams',
        emptyState: '#no-active-teams'
    }
};

/**
 * Met à jour les statistiques du tableau de bord
 * @param {Object} stats - Statistiques à afficher
 */
function updateStats(stats) {
    if (!stats) return;
    
    // Mettre à jour les compteurs
    const elements = ADMIN_DASHBOARD_ELEMENTS.stats;
    Object.keys(elements).forEach(key => {
        const element = document.querySelector(elements[key]);
        if (element && stats[key] !== undefined) {
            element.textContent = stats[key];
        }
    });
}

/**
 * Met à jour le flux d'activité récente
 * @param {Array} activities - Liste des activités récentes
 */
function updateActivityFeed(activities) {
    const container = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.activityFeed.container);
    const emptyState = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.activityFeed.emptyState);
    
    if (!container) return;
    
    // Afficher l'état vide si aucune activité
    if (!activities || !activities.length) {
        if (emptyState) emptyState.style.display = 'flex';
        container.innerHTML = '';
        return;
    }
    
    // Cacher l'état vide
    if (emptyState) emptyState.style.display = 'none';
    
    // Vider le conteneur
    container.innerHTML = '';
    
    // Ajouter chaque activité
    activities.forEach(activity => {
        const activityElement = createActivityElement(activity);
        container.appendChild(activityElement);
    });
    
    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

/**
 * Crée un élément d'activité
 * @param {Object} activity - Données de l'activité
 * @returns {HTMLElement} - Élément DOM de l'activité
 */
function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'activity-item';
    
    // Déterminer l'icône et la classe en fonction du type d'activité
    let iconName = 'activity';
    let activityClass = '';
    
    // Adapter en fonction de la structure de données renvoyée par l'API
    const activityType = activity.type || activity.action_type || 'default';
    
    switch (activityType) {
        case 'hackathon':
        case 'create_hackathon':
        case 'update_hackathon':
            iconName = 'laptop-code';
            activityClass = 'activity-hackathon';
            break;
        case 'challenge':
        case 'create_challenge':
        case 'solve_challenge':
            iconName = 'trophy';
            activityClass = 'activity-challenge';
            break;
        case 'user':
        case 'register':
        case 'login':
            iconName = 'user';
            activityClass = 'activity-user';
            break;
        case 'team':
        case 'create_team':
        case 'join_team':
            iconName = 'users';
            activityClass = 'activity-team';
            break;
        case 'submission':
        case 'submit_solution':
            iconName = 'file-text';
            activityClass = 'activity-submission';
            break;
    }
    
    // Formater la date
    const date = activity.timestamp || activity.created_at ? formatDate(activity.timestamp || activity.created_at) : 'Récemment';
    
    // Déterminer le texte de l'activité
    const activityText = activity.description || activity.action || 'Activité inconnue';
    const username = activity.username || activity.user_name || '';
    
    div.innerHTML = `
        <div class="activity-icon ${activityClass}">
            <i data-lucide="${iconName}"></i>
        </div>
        <div class="activity-content">
            ${username ? `<div class="activity-title">${sanitizeText(username)}</div>` : ''}
            <p class="activity-text">${sanitizeText(activityText)}</p>
            <span class="activity-time">${date}</span>
        </div>
    `;
    
    return div;
}

/**
 * Met à jour la section des hackathons à venir
 * @param {Array} hackathons - Liste des hackathons à venir
 */
function updateUpcomingHackathons(hackathons) {
    const container = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.upcomingHackathons.container);
    const emptyState = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.upcomingHackathons.emptyState);
    
    if (!container) return;
    
    // Afficher l'état vide si aucun hackathon
    if (!hackathons || !hackathons.length) {
        if (emptyState) emptyState.style.display = 'flex';
        container.innerHTML = '';
        return;
    }
    
    // Cacher l'état vide
    if (emptyState) emptyState.style.display = 'none';
    
    // Vider le conteneur
    container.innerHTML = '';
    
    // Ajouter chaque hackathon
    hackathons.forEach(hackathon => {
        const hackathonElement = createHackathonElement(hackathon);
        container.appendChild(hackathonElement);
    });
}

/**
 * Crée un élément de hackathon
 * @param {Object} hackathon - Données du hackathon
 * @returns {HTMLElement} - Élément DOM du hackathon
 */
function createHackathonElement(hackathon) {
    const div = document.createElement('div');
    div.className = 'bg-background-lighter rounded-lg p-4 mb-4 transition transform hover:-translate-y-1 cursor-pointer';
    div.style.backgroundColor = 'var(--background-lighter)';
    div.style.borderRadius = '0.75rem';
    div.style.padding = '15px';
    div.style.marginBottom = '15px';
    div.style.transition = 'transform 0.2s ease';
    div.style.cursor = 'pointer';
    
    div.onmouseover = function() { this.style.transform = 'translateY(-3px)'; };
    div.onmouseout = function() { this.style.transform = 'translateY(0)'; };
    
    // Formater la date
    const date = hackathon.start_date ? formatDate(hackathon.start_date, true) : 'Date non définie';
    
    // Déterminer le nombre de participants
    const participants = hackathon.participants || hackathon.participants_count || 0;
    
    div.innerHTML = `
        <h3>${sanitizeText(hackathon.name || hackathon.title)}</h3>
        <p style="color: var(--text-muted); font-size: 0.875rem;"><i data-lucide="calendar"></i> ${date}</p>
        <div style="display: flex; justify-content: space-between; margin-top: 10px;">
            <span><i data-lucide="users"></i> ${participants} participants</span>
            <span class="badge badge-primary"><i data-lucide="hourglass-start"></i> À venir</span>
        </div>
    `;
    
    // Ajouter un gestionnaire d'événements pour la navigation
    div.addEventListener('click', () => {
        window.location.href = `/admin/hackathons/view.php?id=${hackathon.id}`;
    });
    
    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons({ parent: div });
    }
    
    return div;
}

/**
 * Met à jour la section des défis populaires
 * @param {Array} challenges - Liste des défis populaires
 */
function updatePopularChallenges(challenges) {
    const container = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.popularChallenges.container);
    const emptyState = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.popularChallenges.emptyState);
    
    if (!container) return;
    
    // Afficher l'état vide si aucun défi
    if (!challenges || !challenges.length) {
        if (emptyState) emptyState.style.display = 'flex';
        container.innerHTML = `
            <tr>
                <td colspan="3">
                    <div class="empty-state" style="display: flex;">
                        <div class="empty-state-icon">
                            <i data-lucide="trophy"></i>
                        </div>
                        <div class="empty-state-text">
                            <h3>Aucun défi populaire</h3>
                            <p>Les utilisateurs n'ont pas encore commencé de défis.</p>
                            <a href="challenges.php" class="btn btn-primary">Créer un défi</a>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    // Cacher l'état vide
    if (emptyState) emptyState.style.display = 'none';
    
    // Vider le conteneur
    container.innerHTML = '';
    
    // Ajouter chaque défi
    challenges.forEach(challenge => {
        const row = document.createElement('tr');
        
        // Déterminer le statut et l'icône
        let statusClass = 'badge-primary';
        let statusIcon = 'play-circle';
        let statusText = 'En cours';
        
        if (challenge.status === 'completed') {
            statusClass = 'badge-success';
            statusIcon = 'check-circle';
            statusText = 'Terminé';
        } else if (challenge.status === 'upcoming') {
            statusClass = 'badge-info';
            statusIcon = 'hourglass-start';
            statusText = 'À venir';
        }
        
        row.innerHTML = `
            <td>${sanitizeText(challenge.title)}</td>
            <td>${challenge.participants || challenge.participants_count || 0}</td>
            <td><span class="badge ${statusClass}"><i data-lucide="${statusIcon}"></i> ${statusText}</span></td>
        `;
        
        container.appendChild(row);
    });
    
    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

/**
 * Met à jour la section des équipes actives
 * @param {Array} teams - Liste des équipes actives
 */
function updateActiveTeams(teams) {
    const container = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.activeTeams.container);
    const emptyState = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.activeTeams.emptyState);
    
    if (!container) return;
    
    // Afficher l'état vide si aucune équipe
    if (!teams || !teams.length) {
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }
    
    // Cacher l'état vide
    if (emptyState) emptyState.style.display = 'none';
    
    // Vider le conteneur
    container.innerHTML = '';
    
    // Ajouter chaque équipe
    teams.forEach(team => {
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td>${sanitizeText(team.name)}</td>
            <td>${team.members_count || 0}</td>
            <td>${team.challenges_completed || 0}</td>
        `;
        
        container.appendChild(row);
    });
}

/**
 * Nettoie le texte pour prévenir les attaques XSS
 * @param {string} text - Texte à nettoyer
 * @returns {string} - Texte nettoyé
 */
function sanitizeText(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Formate une date
 * @param {string} dateString - Chaîne de date à formater
 * @param {boolean} shortFormat - Format court (jour mois année)
 * @returns {string} - Date formatée
 */
function formatDate(dateString, shortFormat = false) {
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';
        
        if (shortFormat) {
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }
        
        // Calculer la différence de temps
        const now = new Date();
        const diffMs = now - date;
        const diffSec = Math.floor(diffMs / 1000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHour = Math.floor(diffMin / 60);
        const diffDay = Math.floor(diffHour / 24);
        
        // Afficher un format relatif si c'est récent
        if (diffDay < 1) {
            if (diffHour < 1) {
                if (diffMin < 1) {
                    return 'À l\'instant';
                }
                return `Il y a ${diffMin} minute${diffMin > 1 ? 's' : ''}`;
            }
            return `Il y a ${diffHour} heure${diffHour > 1 ? 's' : ''}`;
        } else if (diffDay < 7) {
            return `Il y a ${diffDay} jour${diffDay > 1 ? 's' : ''}`;
        }
        
        // Sinon, afficher la date complète
        return date.toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        console.error('Erreur de formatage de date', e);
        return 'Date inconnue';
    }
}

/**
 * Affiche le spinner de chargement
 */
function showLoading() {
    const spinner = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.loadingSpinner);
    if (spinner) {
        spinner.classList.remove('hidden');
    }
}

/**
 * Cache le spinner de chargement
 */
function hideLoading() {
    const spinner = document.querySelector(ADMIN_DASHBOARD_ELEMENTS.loadingSpinner);
    if (spinner) {
        spinner.classList.add('hidden');
    }
}

/**
 * Gère les erreurs
 * @param {string} message - Message d'erreur
 * @param {Error} error - Objet d'erreur
 */
function handleError(message, error) {
    console.error(message, error);
    // Afficher une notification d'erreur si nécessaire
    const errorContainer = document.querySelector('#error-container');
    if (errorContainer) {
        errorContainer.textContent = `${message}: ${error.message || 'Erreur inconnue'}`;
        errorContainer.classList.remove('hidden');
        
        // Masquer après 5 secondes
        setTimeout(() => {
            errorContainer.classList.add('hidden');
        }, 5000);
    }
}

/**
 * Charge les statistiques du tableau de bord
 */
async function loadAdminStats() {
    try {
        // Utiliser le bon endpoint défini dans AdminController.php
        const response = await apiRequest('/admin/stats');
        
        if (response.success && response.data) {
            updateStats({
                hackathonsCount: response.data.hackathons_count || 0,
                challengesCount: response.data.challenges_count || 0,
                usersCount: response.data.users_count || 0,
                teamsCount: response.data.teams_count || 0
            });
        }
    } catch (error) {
        handleError('Erreur lors du chargement des statistiques', error);
    }
}

/**
 * Charge les activités récentes
 */
async function loadRecentActivity() {
    try {
        // Utiliser le bon endpoint défini dans AdminController.php
        const response = await apiRequest('/admin/activity');
        
        if (response.success && response.data) {
            updateActivityFeed(response.data);
        }
    } catch (error) {
        handleError('Erreur lors du chargement des activités récentes', error);
    }
}

/**
 * Charge les hackathons à venir
 */
async function loadUpcomingHackathons() {
    try {
        // Utiliser le bon endpoint défini dans AdminController.php
        const response = await apiRequest('/admin/upcoming-hackathons');
        
        if (response.success && response.data) {
            updateUpcomingHackathons(response.data);
        }
    } catch (error) {
        handleError('Erreur lors du chargement des hackathons à venir', error);
    }
}

/**
 * Charge les défis populaires
 */
async function loadPopularChallenges() {
    try {
        // Utiliser le bon endpoint défini dans AdminController.php
        const response = await apiRequest('/admin/popular-challenges');
        
        if (response.success && response.data) {
            updatePopularChallenges(response.data);
        }
    } catch (error) {
        handleError('Erreur lors du chargement des défis populaires', error);
    }
}

/**
 * Charge les équipes actives
 */
async function loadActiveTeams() {
    try {
        // Utiliser le bon endpoint défini dans AdminController.php
        const response = await apiRequest('/admin/teams');
        
        if (response.success && response.data) {
            // Filtrer pour obtenir uniquement les équipes actives
            const activeTeams = response.data.filter(team => {
                // Logique pour déterminer si une équipe est active
                // Par exemple, si elle a des membres et des défis récents
                return team.members_count > 0;
            }).slice(0, 5); // Limiter à 5 équipes
            
            updateActiveTeams(activeTeams);
        }
    } catch (error) {
        handleError('Erreur lors du chargement des équipes actives', error);
    }
}

/**
 * Charge les notifications administrateur
 */
async function loadAdminNotifications() {
    try {
        const response = await apiRequest('/admin/notifications');
        
        if (response.success && response.data) {
            // Mettre à jour le compteur de notifications
            const notifCounter = document.querySelector('#notifications-counter');
            if (notifCounter) {
                const unreadCount = response.data.filter(notif => !notif.read_at).length;
                notifCounter.textContent = unreadCount;
                notifCounter.style.display = unreadCount > 0 ? 'flex' : 'none';
            }
            
            // Mettre à jour la liste des notifications
            const notifList = document.querySelector('#notifications-list');
            if (notifList) {
                notifList.innerHTML = '';
                
                if (response.data.length === 0) {
                    notifList.innerHTML = '<div class="empty-notification">Aucune notification</div>';
                } else {
                    response.data.slice(0, 5).forEach(notif => {
                        const notifItem = document.createElement('div');
                        notifItem.className = `notification-item ${!notif.read_at ? 'unread' : ''}`;
                        
                        notifItem.innerHTML = `
                            <div class="notification-icon">
                                <i data-lucide="${getNotificationIcon(notif.type)}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">${sanitizeText(notif.title)}</div>
                                <div class="notification-text">${sanitizeText(notif.message)}</div>
                                <div class="notification-time">${formatDate(notif.created_at)}</div>
                            </div>
                        `;
                        
                        notifList.appendChild(notifItem);
                    });
                    
                    // Ajouter un lien "Voir tout"
                    const viewAllLink = document.createElement('a');
                    viewAllLink.href = '/admin/notifications.php';
                    viewAllLink.className = 'view-all-notifications';
                    viewAllLink.textContent = 'Voir toutes les notifications';
                    notifList.appendChild(viewAllLink);
                }
                
                // Actualiser les icônes Lucide
                if (window.lucide) {
                    window.lucide.createIcons({ parent: notifList });
                }
            }
        }
    } catch (error) {
        handleError('Erreur lors du chargement des notifications', error);
    }
}

/**
 * Obtient l'icône appropriée pour un type de notification
 * @param {string} type - Type de notification
 * @returns {string} - Nom de l'icône Lucide
 */
function getNotificationIcon(type) {
    switch (type) {
        case 'warning':
            return 'alert-triangle';
        case 'error':
            return 'alert-octagon';
        case 'success':
            return 'check-circle';
        case 'info':
        default:
            return 'info';
    }
}

/**
 * Initialise le tableau de bord administrateur
 */
async function initializeAdminDashboard() {
    try {
        showLoading();
        
        // Charger toutes les données en parallèle
        await Promise.all([
            loadAdminStats(),
            loadRecentActivity(),
            loadUpcomingHackathons(),
            loadPopularChallenges(),
            loadActiveTeams(),
            loadAdminNotifications()
        ]);
        
        // Configurer le rafraîchissement automatique
        setupAutoRefresh();
        
    } catch (error) {
        handleError('Erreur lors de l\'initialisation du tableau de bord', error);
    } finally {
        hideLoading();
    }
}

/**
 * Configure le rafraîchissement automatique des données
 */
function setupAutoRefresh() {
    // Rafraîchir les données toutes les 5 minutes
    setInterval(() => {
        loadAdminStats();
        loadRecentActivity();
        loadAdminNotifications();
    }, 5 * 60 * 1000);
    
    // Rafraîchir les notifications plus fréquemment
    setInterval(() => {
        loadAdminNotifications();
    }, 60 * 1000);
}

// Initialiser le tableau de bord lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
    
    // Initialiser le tableau de bord
    initializeAdminDashboard();
    
    // Ajouter des gestionnaires d'événements pour les interactions utilisateur
    setupEventListeners();
});

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
    // Exemple: gestionnaire pour le bouton de rafraîchissement
    const refreshButton = document.querySelector('#refresh-dashboard');
    if (refreshButton) {
        refreshButton.addEventListener('click', () => {
            initializeAdminDashboard();
        });
    }
    
    // Exemple: gestionnaire pour le menu de notifications
    const notifToggle = document.querySelector('#notifications-toggle');
    const notifDropdown = document.querySelector('#notifications-dropdown');
    if (notifToggle && notifDropdown) {
        notifToggle.addEventListener('click', (e) => {
            e.preventDefault();
            notifDropdown.classList.toggle('show');
        });
        
        // Fermer le dropdown quand on clique ailleurs
        document.addEventListener('click', (e) => {
            if (!notifToggle.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.remove('show');
            }
        });
    }
}

// Exporter les fonctions pour les tests ou l'utilisation dans d'autres fichiers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        updateStats,
        updateActivityFeed,
        updateUpcomingHackathons,
        updatePopularChallenges,
        apiRequest,
        formatDate,
        sanitizeText
    };
}

// Pour tester le code
console.log("Admin Dashboard JavaScript chargé");