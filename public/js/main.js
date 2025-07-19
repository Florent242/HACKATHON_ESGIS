class AuthService {
    // Configurations des routes par rôle (à adapter selon votre structure)
    static ROUTES = {
        guest: '/auth',
        admin: '/admin',
        participant: '/user',
        visitor: '/' // Nouvelle route visiteur
    };

    // Vérifie l'authentification et redirige si nécessaire
    static async verifyAuth() {
        try {
            const response = await fetch('/api/auth/check', {
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            console.dir(response);
            if (!response.ok) {
                throw new Error('Échec de la vérification d\'authentification');
            }

            const data = await response.json();
            console.log(data);
            return {
                authenticated: data.authenticated,
                userId: data.id || null,
                userRole: data.role || null,
                error: null
            };

        } catch (error) {
            console.error('AuthService error:', error);
            return {
                authenticated: false,
                user: null,
                error: error.message
            };
        }
    }

    // Gère un utilisateur authentifié
    static handleAuthenticated(user) {
        const isPathAllowed = this.checkPathPermission(user.role);

        if (!isPathAllowed) {
            this.redirectToRoleHome(user.role);
            return false;
        }

        return true;
    }

    // Gère un utilisateur non authentifié
    static handleUnauthenticated() {
        if (this.isVisitorPath()) {
            return true; // Autorise à rester sur la page visiteur
        }

        this.redirectToLogin();
        return false;
    }

    // Vérifie si le chemin actuel est autorisé pour le rôle
    static checkPathPermission(role) {
        const currentPath = window.location.pathname;
        const pathPatterns = {
            admin: /^\/admin/,
            participant: /^\/user/,
            visitor: /^\/(auth|challenge|contact|sponsors|hackathon|leaderboard|resources)/ // Chemins publics
        };

        return pathPatterns[role]?.test(currentPath); // Les routes visiteur sont accessibles à tous
    }

    // Vérifie si l'utilisateur est sur une page visiteur
    static isVisitorPath() {
        return /^\/HACKATHON_ESGIS\/public\/(auth|challenge|contact|sponsors|hackathon|leaderboard|resources)/.test(window.location.pathname) || /^\/HACKATHON_ESGIS\/public\/$/.test(window.location.pathname);
    }

    // Redirige vers la page d'accueil correspondant au rôle
    static redirectToRoleHome(role) {
        window.location.href = this.ROUTES[role] || this.ROUTES.visitor;
    }

    // Redirige vers la page de login appropriée
    static redirectToLogin() {
        const isAdminPath = window.location.pathname.includes('/admin');
        window.location.href = isAdminPath
            ? '/auth_admin'
            : '/auth';
    }

    // Déconnexion
    static async logout() {
        try {
            await fetch('/api/auth/logout', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            this.redirectToVisitorHome();
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }

    // Redirige vers l'accueil visiteur après déconnexion
    static redirectToVisitorHome() {
        window.location.href = this.ROUTES.visitor;
    }
}

/**
 * Affiche une notification.
 * @param {string} message - Le message à afficher.
 * @param {string} details - Les détails de la notification (optionnel).
 * @param {string} type - Le type de notification ('success', 'error', 'info', 'warning').
 * @param {number} duration - Durée en millisecondes avant disparition (optionnel).
 */
let activeNotifications = [];
const NOTIFICATION_OFFSET = 10; // Espacement entre les notifications en pixels

function updateNotificationsPosition() {
    let topPosition = 20; // Position de départ en haut

    // Parcourir toutes les notifications visibles
    activeNotifications.forEach(notification => {
        if (document.body.contains(notification)) {
            notification.style.top = `${topPosition}px`;
            // Ajouter la hauteur de la notification + l'espacement pour la prochaine
            topPosition += notification.offsetHeight + NOTIFICATION_OFFSET;
        }
    });
}

// Fonctions d'affichage/masquage des erreurs
/**
 * @description Affiche et anime un message d'erreur
 * @param {HTMLElement} inputElement 
 * @param {HTMLElement} errorElement 
 * @param {string} message 
 */
function showError(inputElement, errorElement, message) {
    // Ajouter la classe d'erreur à l'input
    inputElement.parentElement.classList.add('input-error');

    // Afficher et animer le message d'erreur
    errorElement.textContent = message;
    errorElement.classList.remove('hidden', 'fade-out');
}

/**
 * @description Masque et anime un message d'erreur
 * @param {HTMLElement} inputElement 
 * @param {HTMLElement} errorElement 
 */
function hideError(inputElement, errorElement) {
    // Retirer la classe d'erreur de l'input
    inputElement.parentElement.classList.remove('input-error');

    // Vérifier si l'erreur est déjà masquée
    if (errorElement.classList.contains('hidden')) return;

    // Supprimer l'ancienne animation si elle est encore en cours
    errorElement.classList.remove('fade-in');

    // Ajouter la classe de disparition
    errorElement.classList.add('fade-out');

    // Attendre la fin de l'animation avant de cacher complètement
    errorElement.addEventListener('animationend', function () {
        errorElement.classList.add('hidden');
        errorElement.classList.remove('fade-out'); // Nettoyage après animation
    }, { once: true });
}

/**
 * @description Affiche une notification
 * @param {string} message 
 * @param {string} details 
 * @param {string} type 
 * @param {number} duration 
 */
function showNotification(message, details = null, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `fixed ${type === 'success' ? 'left-1/2 transform -translate-x-1/2' : 'right-4'} bg-gray-900/90 backdrop-blur-sm border ${type === 'success' ? 'border-green-500/30' : type === 'error' ? 'border-red-500/30' : type === 'warning' ? 'border-yellow-500/30' : 'border-blue-500/30'} rounded-lg shadow-lg shadow-black/30 p-3 flex items-start justify-between gap-3 animate-fade-in z-[1000] cursor-pointer min-h-[60px] w-[35vw] sm:w-[45vw] md:w-[45vw] lg:w-[45vw]`;

    let timeoutId;
    const startTimer = () => {
        timeoutId = setTimeout(() => {
            hideNotification(notification);
        }, duration);
    };

    const pauseTimer = () => {
        clearTimeout(timeoutId);
    };

    // Démarrer le timer initial
    startTimer();

    // Gestion du survol
    notification.addEventListener('mouseenter', pauseTimer);
    notification.addEventListener('mouseleave', startTimer);

    // Conteneur d'icône
    const iconContainer = document.createElement('div');
    iconContainer.className = 'flex-shrink-0 pt-0.5';

    // Icône Lucide
    const icon = document.createElement('i');
    icon.setAttribute('data-lucide',
        type === 'success' ? 'check-circle' :
            type === 'error' ? 'x-circle' :
                type === 'warning' ? 'alert-triangle' :
                    'info'
    );
    icon.className = `w-4 h-4 sm:w-5 sm:h-5 ${type === 'success' ? 'text-green-400' :
        type === 'error' ? 'text-red-400' :
            type === 'warning' ? 'text-yellow-400' :
                'text-blue-400'
        }`;

    iconContainer.appendChild(icon);
    notification.appendChild(iconContainer);

    // Contenu du texte
    const textContainer = document.createElement('div');
    textContainer.className = 'flex-1';

    // Message principal avec clamp
    const messageElement = document.createElement('p');
    messageElement.className = 'text-white font-medium text-sm max-md:text-xs line-clamp-1';
    messageElement.innerText = message;
    messageElement.title = message;
    textContainer.appendChild(messageElement);

    // Message de détails (en option)
    if (details) {
        const detailsElement = document.createElement('p');
        detailsElement.className = 'text-gray-300/90 font-normal text-sm max-md:text-[0.6rem] mt-1 line-clamp-2';
        detailsElement.innerText = details;
        detailsElement.title = details;
        textContainer.appendChild(detailsElement);
    }

    notification.appendChild(textContainer);

    // Bouton de fermeture
    const closeContainer = document.createElement('div');
    closeContainer.className = 'flex-shrink-0 pt-0.5';

    const closeButton = document.createElement('button');
    closeButton.className = 'text-gray-400 hover:text-white transition-colors focus:outline-none';

    const closeIcon = document.createElement('i');
    closeIcon.setAttribute('data-lucide', 'x');
    closeIcon.className = 'w-4 h-4 max-sm:w-3 max-sm:h-3';

    closeButton.appendChild(closeIcon);
    closeButton.addEventListener('click', (e) => {
        e.stopPropagation();
        hideNotification(notification);
    });

    closeContainer.appendChild(closeButton);
    notification.appendChild(closeContainer);

    notification.addEventListener('click', () => {
        hideNotification(notification);
    });

    // Ajouter la notification au DOM
    document.body.appendChild(notification);

    // Ajouter à la liste des notifications actives
    activeNotifications.push(notification);
    updateNotificationsPosition();

    // Initialiser Lucide pour les nouvelles icônes
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Nettoyer le timeout si la notification est supprimée
    notification.addEventListener('animationend', (e) => {
        if (e.animationName === 'fadeOut') {
            clearTimeout(timeoutId);
        }
    });

    return notification;
}

/**
 * @description Masque et anime une notification
 * @param {HTMLElement} notification 
 */
function hideNotification(notification) {
    notification.classList.add('animate-fade-out');
    notification.addEventListener('animationend', () => {
        // Retirer la notification du DOM
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
        // Retirer de la liste des notifications actives
        activeNotifications = activeNotifications.filter(n => n !== notification);
        // Mettre à jour la position des notifications restantes
        updateNotificationsPosition();
    }, { once: true });
}

/**
 * @description Stocke un message flash dans localStorage
 * @param {string} type 
 * @param {string} message 
 * @param {string} details 
 */
function setFlashMessage(type, message, details = null) {
    // Stocker le message dans localStorage
    localStorage.setItem('flashMessage', JSON.stringify({
        type: type,
        message: message,
        details: details,
        timestamp: Date.now()
    }));
}

/**
 * @description Récupère un message flash depuis localStorage
 * @returns {Object|null}
 */
function getFlashMessage() {
    const message = localStorage.getItem('flashMessage');
    if (message) {
        const flash = JSON.parse(message);
        return flash;
    }
    return null;
}

/**
 * @description Fonction utilitaire pour gérer les requêtes API
 * @param {string} endpoint 
 * @param {Object} options 
 * @returns {Promise<Object>}
 */
async function apiRequest(endpoint, options = {}) {
    try {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        const response = await fetch(`/api${endpoint}`, {
            ...options,
            headers: { ...headers, ...options.headers }
        });

        const data = await response.json(); // On parse toujours le body

        // Afficher les détails de debug si disponibles (même pour les réponses 200)
        if (data.debug_message) {
            console.group('🔍 Debug API Info');
            console.log('Message:', data.debug_message);
            console.log('File:', data.debug_file);
            console.log('Line:', data.debug_line);
            if (data.debug_trace) console.log('Trace:', data.debug_trace);
            console.groupEnd();
        }

        if (!response.ok) {
            return {
                success: false,
                status: data.status || response.status,
                message: data.message || data.error || 'Erreur inconnue',
                debug_message: data.debug_message || null,
                debug_file: data.debug_file || null,
                debug_line: data.debug_line || null,
                debug_trace: data.debug_trace || null,
                data: null
            };
        }

        // Vérifier si la réponse contient une erreur même avec un status 200
        if (data.success === false) {
            return {
                success: false,
                status: response.status,
                message: data.error || data.message || 'Erreur inconnue',
                debug_message: data.debug_message || null,
                debug_file: data.debug_file || null,
                debug_line: data.debug_line || null,
                debug_trace: data.debug_trace || null,
                data: data
            };
        }

        return data;  // Retourne bien les données récupérées
    } catch (error) {
        handleError('Erreur lors de la requête API', error, 'error');
        return {
            success: false,
            status: 'client_error',
            message: 'Erreur côté client',
            data: null
        };
    }
}

// TODO: Retirer certaines parties potentiellements a titre exploitable de cette fonction
/**
 * @description Fonction utilitaire pour gérer les erreurs
 * @param {string} message 
 * @param {Error} error 
 * @param {string} type 
 */
function handleError(message, error, type = 'error') {
    // Log détaillé dans la console
    console.group('🔴 Erreur API');
    console.error('Message:', message);
    console.error('Erreur:', error);
    
    // Si l'erreur a des propriétés de debug, les afficher
    if (error && typeof error === 'object') {
        if (error.debug_message) console.error('Debug message:', error.debug_message);
        if (error.debug_file) console.error('Debug file:', error.debug_file);
        if (error.debug_line) console.error('Debug line:', error.debug_line);
        if (error.debug_trace) console.error('Debug trace:', error.debug_trace);
        if (error.stack) console.error('Stack:', error.stack);
    }
    console.groupEnd();
    
    // Préparer le message pour la notification
    let notificationMessage = message;
    let notificationDetails = error.message || error.error || error || "Erreur inconnue";
    
    // Si on a des infos de debug, les ajouter aux détails
    if (error && error.debug_message) {
        notificationDetails = `${error.debug_message}`;
        if (error.debug_file && error.debug_line) {
            notificationDetails += ` (${error.debug_file}:${error.debug_line})`;
        }
    }
    
    showNotification(notificationMessage, notificationDetails, type);
}

/**
 * @description Fonction helper pour debug - testez une requête API depuis la console
 * Usage: await testApiRequest('/challenges/dev/1/1', 'POST', {action: 'validate', challenge_id: 47, code: 'print("test")', language: 'python'})
 * @param {string} endpoint 
 * @param {string} method 
 * @param {Object} body 
 */
async function testApiRequest(endpoint, method = 'GET', body = null) {
    console.group('🧪 Test API Request');
    console.log('Endpoint:', endpoint);
    console.log('Method:', method);
    console.log('Body:', body);
    
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        if (body && method !== 'GET') {
            options.body = JSON.stringify(body);
        }
        
        const result = await apiRequest(endpoint, options);
        
        console.log('Result:', result);
        console.groupEnd();
        
        return result;
    } catch (error) {
        console.error('Test failed:', error);
        console.groupEnd();
        throw error;
    }
}

// Exposer la fonction de test globalement pour faciliter le debug
window.testApiRequest = testApiRequest;

/**
 * @description Fonction utilitaire pour vérifier l'état de connexion
 */
async function initVerification() {
    const authCheck = await AuthService.verifyAuth();
    console.log('Auth check:', authCheck);
    if (!authCheck.authenticated) {
        // Si non authentifié ET pas sur une page visiteur -> redirection
        if (!AuthService.isVisitorPath()) {
            AuthService.redirectToLogin();
            setFlashMessage('info', "Non connecté");
            return; // On arrête l'exécution pour éviter tout traitement inutile
        }
    } else {
        // Si authentifié mais sur une page non autorisée une redirection est faite
        if (!AuthService.checkPathPermission(authCheck.userRole)) {
            AuthService.redirectToRoleHome(authCheck.userRole);
            return;
        }

        // Ici l'utilisateur est bien authentifié et autorisé
        console.log('Utilisateur connecté:', authCheck.authenticated);

    }
}

/**
 * @description Pour une gestion des connexions cote client mais non implemente
 */
try {
    // initVerification();
} catch (error) {
    console.error('Erreur lors de la vérification de l\'authentification:', error);

    // En cas d'erreur, on considère comme non authentifié
    if (!AuthService.isVisitorPath()) {
        setFlashMessage('info', "Non connecté");
        AuthService.redirectToLogin();
    }
}

/**
 * @description Fonction pour récupérer l'ID de l'utilisateur
 */
async function getUserId() {
    try {
        const response = await fetch('/api/users/me', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
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

/**
 * @description Fonction pour mettre à jour les éléments du DOM
 * @param {Object} elements 
 * @param {Object} data 
 */
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
document.addEventListener('DOMContentLoaded', async () => {
    // initialisation des notifications
    const notificationElement = document.getElementById('notification-data');
    if (notificationElement) {
        try {
            // TODO: nettoyer la notification de la session après affichage 
            fetch('clearNotification.php', { method: 'POST' })
            const notificationData = JSON.parse(notificationElement.getAttribute('data-notification'));
            if (notificationData) {
                showNotification(
                    notificationData.message,
                    notificationData.details || null,
                    notificationData.type || 'info'
                );
                // Supprimer la notification de la session après affichage
                fetch('clearNotification.php', { method: 'POST' })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la suppression de la notification');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
            const flashMessage = getFlashMessage();
            if (flashMessage) {
                showNotification(
                    flashMessage.message,
                    flashMessage.details || null,
                    flashMessage.type || 'info'
                );
                // Supprimer le message après l'avoir affiché
                localStorage.removeItem('flashMessage');
            }
        } catch (e) {
            console.error('Erreur lors du parsing des données de notification:', e);
        }
    }

});
