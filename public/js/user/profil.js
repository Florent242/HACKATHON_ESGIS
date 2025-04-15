const API_BASE_URL = '/HACKATHON_ESGIS/public/api';
const PROFILE_ELEMENTS = {
    username: '.Username',
    email: '.Email',
    fullName: '.fullName',
    special_comp: '.special_comp',
    university: '.university',
    skill: '.skill',
    languages: '.languages',
    study_level: '.study_level',
    number: '.number',
    loadingSpinner: '#global-loading-spinner',
    stats: {
        challengesSolved: '#number-challenges-solved',
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
 * Met à jour la section des activités récentes
 * @param {Array} activities - Liste des activités récentes
 */
function updateRecentActivities(activities) {
    const container = document.querySelector(PROFILE_ELEMENTS.recentActivities.container);
    const emptyState = document.querySelector(PROFILE_ELEMENTS.recentActivities.emptyState);

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
    const items = container.querySelectorAll(PROFILE_ELEMENTS.recentActivities.template);
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
 * Met à jour un élément de notification individuelle
*/
function updateActivityItem(element, activity) {
    const icon = element.querySelector('.activity-icon');
    const textElement = element.querySelector('.activity-text');
    const detailsElement = element.querySelector('.activity-details');
    const timeElement = element.querySelector('.activity-time');
    // console.log(activity);

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
        // icon.parentElement.classList.add(activityClass);
    }
    
    if (textElement) {
        // Filtre les messages d'erreur SQL
        let description = activity.description || activity.action || 'Activité inconnue';
        if (activity.level === 'error' && description.includes('SQLSTATE')) {
            description = "Une erreur système est survenue";
        }
        textElement.innerHTML = sanitizeText(description);
    }

    if (detailsElement) {
        detailsElement.textContent = activity.action || '';
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

/**
 * Nettoie le texte pour prévenir les attaques XSS
 */
function sanitizeText(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Fonction pour formater les dates
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Date invalide';
        
        const options = {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
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
async function loadUserInfo(userId) {
    try {
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }
        const data = await apiRequest(`/users/${userId}`);
        console.log(data);
        updateDOM({
            username: PROFILE_ELEMENTS.username,
            email: PROFILE_ELEMENTS.email,
            fullname: PROFILE_ELEMENTS.fullName,
            special_comp: PROFILE_ELEMENTS.special_comp,
            school: PROFILE_ELEMENTS.university,
            skill: PROFILE_ELEMENTS.skill,
            languages: PROFILE_ELEMENTS.languages,
            study_level: PROFILE_ELEMENTS.study_level,
            number: PROFILE_ELEMENTS.number
        }, data);
    } catch (error) {
        handleError('Erreur lors de la récupération des informations utilisateur', error, 'error');
    }
}

async function loadRecentActivity(userId) {
    try {
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
document.addEventListener("DOMContentLoaded", async () => {
    /* Tabs for main content */

    // tabs button link
    const tabs = document.querySelectorAll(".tab-link");
    // tabs content
    const contents = document.querySelectorAll(".tab-content");

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            const target = this.getAttribute("data-tab");

            // Supprime la classe active de tous les onglets et cache le contenu
            tabs.forEach(t => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"));
            contents.forEach(c => c.classList.add("hidden"));

            // Active le bon onglet et affiche le bon contenu
            this.classList.add("text-white", "border-blue-500", "bg-gray-900/75");
            document.getElementById(target).classList.remove("hidden");
        });
    });

    /* Tabs for challenges subcontent */

    // subtabs button link
    const subTabs = document.querySelectorAll(".sub-tab-link");
    // subtabs content
    const subContents = document.querySelectorAll(".sub-tab-content");

    subTabs.forEach(subTab => {
        subTab.addEventListener("click", function () {
            const target = this.getAttribute("data-sub-tab");

            // Supprime la classe active de tous les onglets et cache le contenu
            subTabs.forEach(t => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"));
            subContents.forEach(c => c.classList.add("hidden"));

            // Active le bon onglet et affiche le bon contenu
            this.classList.add("text-white", "border-blue-500", "bg-gray-900/75");
            document.getElementById(target).classList.remove("hidden");
        });
    });

    const userId = await getUserId();
    loadUserInfo(userId);
    loadRecentActivity(userId);
});