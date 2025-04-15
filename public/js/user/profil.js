const API_BASE_URL = "/HACKATHON_ESGIS/public/api"
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
        pointsChangePercent: '#points-change-percent',
        totalPoints: '#total-points',
        hackingChallengesValidate: '#number-hacking-challenges-validate',
        numberRanking: '#number-ranking'
    },
    recentActivities: {
        overview: '#overview-activities-container',
        container: '#recent-activities-container',
        template: '.recent-activity-item',
        emptyState: '#no-recent-activities'
    },
    challenges: {
        inProgress: {
            container: '#in-progress-challenges-container',
            template: '.in-progress-challenge-item',
            emptyState: '#no-in-progress-challenges'
        },
        completed: {
            container: '#completed-challenges-container',
            template: '.completed-challenge-item',
            emptyState: '#no-completed-challenges'
        },
        allChallenges: {
            container: '#all-challenges-container',
            template: '.all-challenge-item',
            emptyState: '#no-all-challenges'
        }
    }
};

function updateUserStats(stats) {
    // Préparer les données formatées
    const formattedStats = {
        'devChallenges': stats['number-dev-challenges'] || 0,
        'devChallengesOn': stats['number-dev-challenges-on'] || 0,
        'hackingChallenges': stats['number-hacking-challenges'] || 0,
        'hackingChallengesValidate': stats['number-hacking-challenges-validate'] || 0,
        'submittedProjects': stats['number-submitted-projects'] || 0,
        'submittedProjectsWait': stats['number-submitted-projects'] || 0,
        'totalPoints': stats['total-points'] || 0,
        'devStat': stats['number-dev-challenges'] || 0,
        'hackingStat': stats['hacking-stat'] || 0,
        'totalPointsStat': stats['total-points-stat'] || 0,
        'pointsChange': stats['points-change'] || 0,
        'pointsChangePercent': stats['points-change-percent'] || 0
    };

    // Mettre à jour toutes les statistiques
    document.querySelector(PROFILE_ELEMENTS.stats.challengesSolved).textContent = formattedStats.hackingChallengesValidate;
    document.querySelector(PROFILE_ELEMENTS.stats.hackingChallenges).textContent = formattedStats.hackingChallenges;
    document.querySelector(PROFILE_ELEMENTS.stats.pointsChangePercent).textContent = formattedStats.pointsChangePercent;
    document.querySelector(PROFILE_ELEMENTS.stats.totalPoints).textContent = formattedStats.totalPoints;
    // document.querySelector(PROFILE_ELEMENTS.stats.hackingChallengesValidate).textContent = formattedStats.hackingChallengesValidate;
    // document.querySelector(PROFILE_ELEMENTS.stats.numberRanking).textContent = formattedStats.numberRanking;
}

/**
 * Met à jour la section des activités récentes
 * @param {Array} activities - Liste des activités récentes
 */
function updateRecentActivities(activities) {
    const container = document.querySelector(PROFILE_ELEMENTS.recentActivities.overview);
    const emptyState = document.querySelector(PROFILE_ELEMENTS.recentActivities.emptyState);
    const overviewContainer = document.querySelector(PROFILE_ELEMENTS.recentActivities.overview);

    if (!container) {
        console.error("Conteneur des activités récentes non trouvé")
        return
    }

    // Cache le conteneur si aucune activité
    if (!activities || activities.length === 0) {
<<<<<<< Updated upstream
        overviewContainer.querySelector(PROFILE_ELEMENTS.recentActivities.template).style.display = 'none';
=======
        container.querySelector(PROFILE_ELEMENTS.recentActivities.template).style.display = 'none';
>>>>>>> Stashed changes
        if (emptyState) emptyState.style.display = 'flex';
        return;
    }

    // Affiche le conteneur et cache l'empty state
    overviewContainer.style.display = 'flex';
    if (emptyState) emptyState.style.display = 'none';

    // Selectionner l'element qui sert de template)
    const items = overviewContainer.querySelector(PROFILE_ELEMENTS.recentActivities.template);
    // for (let i = 1; i < items.length; i++) {
    //     items[i].remove();
    // }

    // Clone et remplit le template pour chaque activité
    activities.forEach((activity, index) => {
        if (index === 0) {
            // Met à jour le premier élément (template)
            updateActivityItem(items, activity);
        } else {
            // Clone et ajoute pour les autres activités
            const clone = items.cloneNode(true);
            updateActivityItem(clone, activity);
            overviewContainer.appendChild(clone);
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

    // Détermine la classe CSS en fonction du niveau d'activité
    const activityClass = {
        'info': 'bg-blue-700/30',
        'error': 'bg-red-700/30',
        'success': 'bg-green-700/30',
        'warning': 'bg-orange-700/30',
        'register_error': 'bg-red-700/30'
    }[activity.level] || 'bg-gray-700/30';

    // element.classList.add(activityClass);

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
<<<<<<< Updated upstream

        const iconColor = {
            'info': 'text-blue-500',
            'error': 'text-red-500',
            'success': 'text-green-500',
            'warning': 'text-orange-500',
            'register_error': 'text-red-500'
        }[activity.level] || 'text-gray-500';

=======
        
>>>>>>> Stashed changes
        const iconName = iconMap[activity.level] ||
            iconMap[activity.action] ||
            iconMap['default'];
        icon.classList.add(iconColor);
        icon.setAttribute('data-lucide', iconName);
        icon.parentElement.classList.add(activityClass);
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
<<<<<<< Updated upstream
        timeElement.textContent = activity.timestamp ?
            formatDate(activity.timestamp) :
            'Récemment';
=======
        timeElement.textContent = activity.created_at ?
        formatDate(activity.created_at) :
        'Récemment';
>>>>>>> Stashed changes
    }

    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

<<<<<<< Updated upstream
function updateChallengesInprogress(challenges) {
    const container = document.querySelector(PROFILE_ELEMENTS.challenges.inProgress.container);
    const emptyState = document.querySelector(PROFILE_ELEMENTS.challenges.inProgress.emptyState);
    if (!challenges || challenges.length === 0) {
        container.querySelector(PROFILE_ELEMENTS.challenges.inProgress.template).style.display = 'none';
        if (emptyState) emptyState.style.display = 'flex';
        console.log('Aucun challenge en cours');
        return;
=======
function updateChallengesInprogress (challenges){
    if (!challenges || challenges.length === 0) {
        console.log('Aucun défi en cours');
        return;
    }

    // Affiche le conteneur et cache l'empty state
    container.style.display = 'flex';
    if (emptyState) emptyState.style.display = 'none';

    // Supprime toutes les défis existants sauf le premier (qui sert de template)
    const items = container.querySelector(PROFILE_ELEMENTS.challenges.inProgress.template);
    for (let i = 1; i < items.length; i++) {
        items[i].remove();
    }

    // Clone et remplit le template pour chaque défi
    challenges.forEach((challenge, index) => {
        if (index === 0) {
            // Met à jour le premier élément (template)
            updateChallengeInProgressItem(items[0], challenge);
        } else {
            // Clone et ajoute pour les autres défis
            const clone = items[0].cloneNode(true);
            updateChallengeInProgressItem(clone, challenge);
            container.appendChild(clone);
        }
    });
}

function updateChallengeInProgressItem(element, challenge){
    element.querySelector('.chaenge-tag').textContent= challenge.tag;
    element.querySelector('.chaenge-level').textContent= challenge.level;
    element.querySelector('.challenge-title').textContent = challenge.title;
    element.querySelector('.challenge-description').textContent = challenge.description;
    element.querySelector('.challenge-progress').textContent = `${challenge.progress}%`;
    element.querySelector('.challenge-end-date').textContent = formatDate(challenge.end_date);
    
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
>>>>>>> Stashed changes
    }

    if (!container) {
        console.error("Conteneur des activités récentes non trouvé")
        return
    }

    // Affiche le conteneur et cache l'empty state
    container.style.display = 'grid';
    if (emptyState) emptyState.style.display = 'none';

    // Supprime toutes les défis existants sauf le premier (qui sert de template)
    const items = container.querySelector(PROFILE_ELEMENTS.challenges.inProgress.template);
    // for (let i = 1; i < items.length; i++) {
    //     items[i].remove();
    // }

    // Clone et remplit le template pour chaque défi
    challenges.forEach((challenge, index) => {
        if (index === 0) {
            // Met à jour le premier élément (template)
            updateChallengeInProgressItem(items, challenge);
        } else {
            // Clone et ajoute pour les autres défis
            const clone = items.cloneNode(true);
            updateChallengeInProgressItem(clone, challenge);
            container.appendChild(clone);
        }
    });
}

function updateChallengeInProgressItem(element, challenge) {
    console.log(challenge)
    const bgStyle = {
        'hard': 'bg-red-500/20',
        'easy': 'bg-green-500/20',
        'medium': 'bg-orange-500/20',
    }[challenge.difficulty] || 'bg-gray-500/20';
    const textStyle = {
        'hard': 'text-red-500',
        'easy': 'text-green-500',
        'medium': 'text-orange-500',
    }[challenge.difficulty] || 'text-gray-500';

    if (!element || !challenge) { console.log('item ou data challenge non defini !', element + '-' + challenge); return; }
    element.querySelector('.challenge-tag').textContent = challenge.type ?? 'type';

    element.querySelector('.challenge-level').textContent = challenge.difficulty ?? 'normal';

    element.querySelector('.challenge-level').classList.add(bgStyle ?? 'normal');
    element.querySelector('.challenge-level').classList.add(textStyle ?? 'normal');

    element.querySelector('.challenge-title').textContent = challenge.title;
    element.querySelector('.challenge-description').textContent = challenge.description;
    element.querySelector('.challenge-end-date').textContent = formatDate(challenge.start_date);
    element.querySelector('.challenge-points').textContent = challenge.points + ' points';

    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

function updateCompletedChallenge(challenges) {
    const container = document.querySelector(PROFILE_ELEMENTS.challenges.completed.container);
    const emptyState = document.querySelector(PROFILE_ELEMENTS.challenges.completed.emptyState);
    if (!challenges || challenges.length === 0) {
        container.querySelector(PROFILE_ELEMENTS.challenges.completed.template).style.display = 'none';
        if (emptyState) emptyState.style.display = 'flex';
        console.log('Aucun challenge completé');
        return;
    }

    if (!container) {
        console.error("Conteneur des activités récentes non trouvé")
        return;
    }

    // Affiche le conteneur et cache l'empty state
    container.style.display = 'grid';
    if (emptyState) emptyState.style.display = 'none';

    // Supprime toutes les défis existants sauf le premier (qui sert de template)
    const items = container.querySelector(PROFILE_ELEMENTS.challenges.completed.template);

    // Clone et remplit le template pour chaque défi
    challenges.forEach((challenge, index) => {
        if (index === 0) {
            // Met à jour le premier élément (template)
            updateChallengeCompletedItem(items, challenge);
        } else {
            // Clone et ajoute pour les autres défis
            const clone = items.cloneNode(true);
            updateChallengeCompletedItem(clone, challenge);
            container.appendChild(clone);
        }
    });
}

function updateChallengeCompletedItem(element, challenge) {
    console.log(challenge)
    const bgStyle = {
        'hard': 'bg-red-500/20',
        'easy': 'bg-green-500/20',
        'medium': 'bg-orange-500/20',
    }[challenge.difficulty] || 'bg-gray-500/20';
    const textStyle = {
        'hard': 'text-red-500',
        'easy': 'text-green-500',
        'medium': 'text-orange-500',
    }[challenge.difficulty] || 'text-gray-500';

    if (!element || !challenge) { console.log('item ou data challenge non defini !', element + '-' + challenge); return; }
    element.querySelector('.challenge-tag').textContent = challenge.type ?? 'type';

    element.querySelector('.challenge-level').textContent = challenge.difficulty ?? 'normal';

    element.querySelector('.challenge-level').classList.add(bgStyle ?? 'normal');
    element.querySelector('.challenge-level').classList.add(textStyle ?? 'normal');

    element.querySelector('.challenge-title').textContent = challenge.title;
    element.querySelector('.challenge-description').textContent = challenge.description;
    element.querySelector('.challenge-end-date').textContent = formatDate(challenge.completed_date);
    element.querySelector('.challenge-points').textContent = challenge.points + ' points';

    // Actualiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons();
    }
}

/**
 * Nettoie le texte pour prévenir les attaques XSS
*/
function sanitizeText(text) {
    if (!text) return ""
    const div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
}

/**
 * Formate une date
 * @param {string} dateString - Chaîne de date à formater
 * @param {boolean} shortFormat - Format court (jour mois année)
 * @returns {string} - Date formatée
 */
function formatDate(dateString, shortFormat = false) {
    try {
        const date = new Date(dateString)
        if (isNaN(date.getTime())) return "Date invalide";

        if (shortFormat) {
            return date.toLocaleDateString("fr-FR", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            })
        }

        // Calculer la différence de temps
        const now = new Date()
        const diffMs = now - date
        const diffSec = Math.floor(diffMs / 1000)
        const diffMin = Math.floor(diffSec / 60)
        const diffHour = Math.floor(diffMin / 60)
        const diffDay = Math.floor(diffHour / 24)

        // Afficher un format relatif si c'est récent
        if (diffDay < 1) {
            if (diffHour < 1) {
                if (diffMin < 1) {
                    return "À l'instant"
                }
                return `Il y a ${diffMin} minute${diffMin > 1 ? "s" : ""}`
            }
            return `Il y a ${diffHour} heure${diffHour > 1 ? "s" : ""}`
        } else if (diffDay < 7) {
            return `Il y a ${diffDay} jour${diffDay > 1 ? "s" : ""}`
        }

        // Sinon, afficher la date complète
        return date.toLocaleDateString("fr-FR", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        })
    } catch (e) {
        console.error("Erreur de formatage de date", e)
        return "Date inconnue"
    }
}

/**
 * Fonction utilitaire pour gérer les erreurs
 * @param {string} title - Titre de l'erreur
 * @param {Error} error - Objet d'erreur
 * @param {string} type - Type de notification
 */
function handleError(title = "Une erreur est survenue", error = null, type = "error") {
    console.error(title, error);

    // Affiche une notification à l'utilisateur
    showNotification(title, error, type);

    // Actualise les icônes
    if (window.lucide) {
        window.lucide.createIcons()
    }
}

// Fonction pour récupérer l'ID utilisateur
async function getUserId() {
    try {
        const response = await fetch("/HACKATHON_ESGIS/public/api/users/me", {
            method: "GET",
            credentials: "include",
            headers: { Accept: "application/json" },
        })

        if (!response.ok) {
            throw new Error("Utilisateur non authentifié")
        }

        const data = await response.json()
        return data.data?.id
    } catch (error) {
        handleError("Impossible de récupérer l'ID utilisateur", error, "error")
        return null
    }
}

/**
 * Charge les informations de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadUserInfo(userId) {
    try {
        if (!userId) {
            console.error("Utilisateur non authentifié")
            return
        }
        const data = await apiRequest(`/users/${userId}`)
        console.log("Informations utilisateur:", data)

        if (data.success) {
            const userData = data.data;
            // Mettre à jour les champs du formulaire avec les valeurs par défaut
            document.getElementById('fullname').value = userData.fullname || '';
            document.getElementById('username').value = userData.username || '';
            document.getElementById('email').value = userData.email || '';
            document.getElementById('school').value = userData.school || '';
            document.getElementById('location').value = userData.location || '';
            document.getElementById('bio').value = userData.bio || '';
            updateDOM(
                {
                    username: PROFILE_ELEMENTS.username,
                    email: PROFILE_ELEMENTS.email,
                    fullname: PROFILE_ELEMENTS.fullName,
                    special_comp: PROFILE_ELEMENTS.special_comp,
                    school: PROFILE_ELEMENTS.university,
                    skill: PROFILE_ELEMENTS.skill,
                    languages: PROFILE_ELEMENTS.languages,
                    study_level: PROFILE_ELEMENTS.study_level,
                    number: PROFILE_ELEMENTS.number,
                },
                data.data,
            )
        }
    } catch (error) {
        handleError("Erreur lors de la récupération des informations utilisateur", error, "error")
    }
}

/**
 * Charge les statistiques de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadUserStats(userId) {
    try {
        if (!userId) {
            console.error("Utilisateur non authentifié")
            return
        }

        const response = await apiRequest(`/users/${userId}/stats`)
        console.log("Statistiques utilisateur:", response)

        if (response.success) {
            updateUserStats(response.data.stats || {})
        }
    } catch (error) {
        handleError("Erreur lors de la récupération des statistiques", error, "error")
    }
}

/**
 * Charge les défis en cours de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadChallengesInProgress(userId) {
    try {
        if (!userId) return

        const response = await apiRequest(`/users/${userId}/current-challenges`)
        console.log("Défis en cours:", response)

        if (response.success) {
            updateChallengesInprogress(response.data || [])
        }
    } catch (error) {
        handleError("Erreur lors de la récupération des défis en cours", error)
    }
}

/**
 * Charge les défis complétés de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadCompletedChallenges(userId) {
    try {
        if (!userId) return

        const response = await apiRequest(`/users/${userId}/completed-challenges`)
        console.log("Défis complétés:", response)

        if (response.success) {
            updateCompletedChallenge(response.data || [])
        }
    } catch (error) {
        handleError("Erreur lors de la récupération des défis complétés", error)
    }
}

/**
 * Charge les activités récentes de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadRecentActivity(userId) {
    try {
        if (!userId) return

        const response = await apiRequest(`/users/${userId}/recent-activities`)
        console.log("Activités récentes:", response)

        if (response.success) {
            updateRecentActivities(response.data || [])
        }
    } catch (error) {
        handleError("Erreur lors de la récupération des activités récentes", error)
    }
}

async function loadActivities(userId) {
    try {
        if (!userId) return

        const response = await apiRequest(`/users/${userId}/all-activities`)
        console.log("Activités récentes:", response)

        if (response.success) {
            updateRecentActivities(response.data || [])
        }
    } catch (error) {
        handleError("Erreur lors de la récupération des activités.", error)
    }
}

function showLoading() {
    const spinner = document.querySelector(PROFILE_ELEMENTS.loadingSpinner);
    if (spinner) {
        spinner.classList.remove('opacity-0', 'pointer-events-none');
        spinner.classList.add('opacity-100');
    }
}

// Cacher le spinner de chargement
function hideLoading() {
    const spinner = document.querySelector(PROFILE_ELEMENTS.loadingSpinner);
    if (spinner) {
        spinner.classList.remove('opacity-100');
        spinner.classList.add('opacity-0', 'pointer-events-none');
    }
}
/**
 * Effectue une requête API
 * @param {string} endpoint - Point de terminaison de l'API
 * @param {Object} options - Options de la requête
 * @returns {Promise<Object>} - Réponse de l'API
 */
async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...options,
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                ...options.headers,
            },
            credentials: "include",
        })

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}))
            throw new Error(errorData.message || errorData.error || `Erreur API: ${response.status} ${response.statusText}`)
        }

        return await response.json()
    } catch (error) {
        console.error("Erreur lors de la requête API", error)
        throw error
    }
}

/**
 * Initialise la page de profil
 */
async function initializeProfilePage() {
    try {
        const userId = await getUserId()
        if (!userId) {
            hideLoading()
            return
        }

        // Charger toutes les données en parallèle
        await Promise.all([
            loadUserInfo(userId),
            loadUserStats(userId),
            loadChallengesInProgress(userId),
            loadCompletedChallenges(userId),
            loadRecentActivity(userId)
        ])

        // Configurer les gestionnaires d'événements
        setupEventListeners()
    } catch (error) {
        handleError("Erreur lors de l'initialisation de la page", error, "error")
    } finally {
        hideLoading()
    }
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
    // Gestionnaire pour le bouton "Modify the profile"
    const modifyProfileButton = document.querySelector(".modify-profile")
    if (modifyProfileButton) {
        modifyProfileButton.addEventListener("click", () => {
            // Activer l'onglet des paramètres
            document.querySelector('[data-tab="tab4"]').click()
        })
    }

    // Gestionnaire pour le bouton "All challenges"
    const allChallengesButton = document.querySelector(".All-challenges")
    if (allChallengesButton) {
        allChallengesButton.addEventListener("click", () => {
            window.location.href = "/HACKATHON_ESGIS/public/user/challenge_security"
        })
    }

    // Gestionnaire pour le formulaire de mise à jour du profil
    const updateProfileForm = document.querySelector("form:nth-of-type(1)")
    if (updateProfileForm) {
        updateProfileForm.addEventListener("submit", async (e) => {
            e.preventDefault()

            try {
                showLoading()

                const formData = new FormData(e.target)
                const userData = {
                    csrf_token: formData.get("csrf_token"),
                    fullname: formData.get("fullname"),
                    username: formData.get("username"),
                    email: formData.get("email"),
                    school: formData.get("school"),
                    location: formData.get("location"),
                    bio: formData.get("bio"),
                }
                console.log("FormData comme objet :", Object.fromEntries(formData));
                const userId = await getUserId()
                const response = await apiRequest(`/users/${userId}`, {
                    method: "PUT",
                    body: JSON.stringify(userData),
                })

                if (response.success) {
                    handleError("Profil mis à jour avec succès", null, "success")
                    await loadUserInfo(userId)
                }
            } catch (error) {
                handleError("Erreur lors de la mise à jour du profil", error, "error")
            } finally {
                hideLoading()
            }
        })
    }

    // Gestionnaire pour le formulaire de changement de mot de passe
    const changePasswordForm = document.querySelector("form:nth-of-type(2)")
    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", async (e) => {
            e.preventDefault()

            try {
                showLoading()

                const formData = new FormData(e.target)
                const passwordData = {
                    csrf_token: formData.get("csrf_token"),
                    currentPassword: formData.get("currentPassword"),
                    newPassword: formData.get("newPassword"),
                    confirmPassword: formData.get("confirmPassword"),
                }

                // Vérifier que les mots de passe correspondent
                if (passwordData.newPassword !== passwordData.confirmPassword) {
                    throw new Error("Les mots de passe ne correspondent pas")
                }

                const userId = await getUserId()
                const response = await apiRequest(`/users/${userId}/password`, {
                    method: "PUT",
                    body: JSON.stringify(passwordData),
                })

                if (response.success) {
                    handleError("Mot de passe mis à jour avec succès", null, "success")
                    e.target.reset()
                }
            } catch (error) {
                handleError("Erreur lors de la mise à jour du mot de passe", error, "error")
            } finally {
                hideLoading()
            }
        })
    }
}

async function loadChalengesInProgress(userId) {
    try{
        if (!userId) {
            console.error('Utilisateur non authentifié');
            return;
        }
        const data = await apiRequest(`/users/${userId}/challenges`);
        console.log(data);
        updateDOM({
            challenges: PROFILE_ELEMENTS.challenges
        }, data);
    } catch (error) {
        handleError('Erreur lors de la récupération des défis', error, 'error');
    }
}
document.addEventListener("DOMContentLoaded", async () => {
    /* Tabs for main content */
    // tabs button link
    const tabs = document.querySelectorAll(".tab-link")
    // tabs content
    const contents = document.querySelectorAll(".tab-content")

    tabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            const target = this.getAttribute("data-tab")

            // Supprime la classe active de tous les onglets et cache le contenu
            tabs.forEach((t) => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"))
            contents.forEach((c) => c.classList.add("hidden"))

            // Active le bon onglet et affiche le bon contenu
            this.classList.add("text-white", "border-blue-500", "bg-gray-900/75")
            document.getElementById(target).classList.remove("hidden")
        })
    })

    /* Tabs for challenges subcontent */
    // subtabs button link
    const subTabs = document.querySelectorAll(".sub-tab-link")
    // subtabs content
    const subContents = document.querySelectorAll(".sub-tab-content")

    subTabs.forEach((subTab) => {
        subTab.addEventListener("click", function () {
            const target = this.getAttribute("data-sub-tab")

            // Supprime la classe active de tous les onglets et cache le contenu
            subTabs.forEach((t) => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"))
            subContents.forEach((c) => c.classList.add("hidden"))

            // Active le bon onglet et affiche le bon contenu
            this.classList.add("text-white", "border-blue-500", "bg-gray-900/75")
            document.getElementById(target).classList.remove("hidden")
        })
    })

    // Initialiser la page de profil
    await initializeProfilePage()

    // Initialiser les icônes Lucide
    if (window.lucide) {
        window.lucide.createIcons()
    }
})
