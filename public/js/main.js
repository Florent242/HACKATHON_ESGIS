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
hanf
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
function showNotification(message, details = null, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 ${type === 'success' ? 'left-1/2' : 'right-4'} transform ${type === 'success' ? '-translate-x-1/2' : 'translate-x-0'} max-w-md w-auto bg-gray-900/90 backdrop-blur-sm border ${type === 'success' ? 'border-green-500/30' : type === 'error' ? 'border-red-500/30' : type === 'warning' ? 'border-yellow-500/30' : 'border-blue-500/30'} rounded-lg shadow-lg shadow-black/30 p-3 flex items-start justify-between gap-3 animate-fade-in z-1000 cursor-pointer`;

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
    icon.className = `w-5 h-5 ${type === 'success' ? 'text-green-400' :
        type === 'error' ? 'text-red-400' :
            type === 'warning' ? 'text-yellow-400' :
                'text-blue-400'
        }`;

    iconContainer.appendChild(icon);
    notification.appendChild(iconContainer);

    // Contenu du texte
    const textContainer = document.createElement('div');
    textContainer.className = 'flex-1';

    // Message principal
    const messageElement = document.createElement('p');
    messageElement.className = 'text-white font-medium text-sm';
    messageElement.innerText = message;
    textContainer.appendChild(messageElement);

    // Message de détails (en option)
    if (details) {
        const detailsElement = document.createElement('p');
        detailsElement.className = 'text-gray-300/90 font-normal text-xs mt-1';
        detailsElement.innerText = details;
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
    closeIcon.className = 'w-4 h-4';

    closeButton.appendChild(closeIcon);
    closeButton.addEventListener('click', () => {
        hideNotification(notification);
    });

    closeContainer.appendChild(closeButton);
    
    notification.appendChild(closeContainer);

    notification.addEventListener('click', () => {
        hideNotification(notification);
    });

    // Ajouter la notification au DOM
    document.body.appendChild(notification);

    // Initialiser Lucide pour les nouvelles icônes
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Masquer la notification après la durée spécifiée
    if (duration) {
        setTimeout(() => hideNotification(notification), duration);
    }

    return notification;
}

function hideNotification(notification) {
    notification.classList.add('animate-fade-out');
    notification.addEventListener('animationend', () => notification.remove(), { once: true });
}

// Dans un fichier utils.js ou directement dans auth.js
function setFlashMessage(type, message, details = null) {
    // Stocker le message dans localStorage
    localStorage.setItem('flashMessage', JSON.stringify({
        type: type,
        message: message,
        details: details,
        timestamp: Date.now()
    }));
}

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

        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': getCsrfToken(),
            ...(options.headers || {})
        };
        if (!headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

        let response = await fetch(`/api${endpoint}`, {
            ...options,
            headers
        });
        const responseText = await response.text();
        // Si le token CSRF a expiré (403)
        if (response.status === 403) {
            let errorData = {};
            try {
                errorData = responseText ? JSON.parse(responseText) : {};
            } catch (e) {
                console.error('Erreur lors du parsing de la réponse d\'erreur:', e);
            }
            if (errorData.error?.includes('controller') || errorData.error?.includes('csrf') || errorData.requires_refresh) {
                const newToken = await refreshCsrfToken();

                options.body.csrf_token ? options.body.csrf_token = newToken : options.body.csrf_token = newToken;
                // On réessaye avec le nouveau token
                response = await fetch(`/api${endpoint}`, {
                    ...options,
                    headers: {
                        ...headers,
                        'X-CSRF-TOKEN': newToken,
                        ...(options.headers || {})
                    }
                });
                // Avant la ligne 395, ajoutez cette vérification
                if (response.status === 403) {
                    const error = new Error('Validation du token de session echouée. Veuillez recharger la page. Si le problème persiste, contactez le support.');
                    error.status = 403;
                    throw error;
                }
            }
        }

        // Nettoyer la réponse des messages de déprication PHP
        const cleanedResponse = responseText.replace(/^(<br \/>\n<b>Deprecated<\/b>:.*?<br \/>\n)+/g, '').trim();

        // Parser le JSON nettoyé
        let data;
        try {
            data = JSON.parse(cleanedResponse);
        } catch (e) {
            console.error('Erreur de parsing JSON:', e);
            console.error('Réponse brute:', responseText);
            throw new Error('Erreur lors de l\'analyse de la réponse du serveur');
        }

        // Gérer les erreurs de debug
        if (data.debug_message) {
            console.group('⚠️ Debug Info');
            console.log('Message:', data.debug_message);
            console.log('File:', data.debug_file);
            console.log('Line:', data.debug_line);
            if (data.debug_trace) console.log('Trace:', data.debug_trace);
            console.groupEnd();
        }
        // Si la réponse n'est pas OK, lancer une erreur
        if (response.status !== 400 && response.status !== 401 && response.status !== 200 && response.status !== 404 && response.status !== 500) {
            const error = new Error(data.message || data.error || 'Une erreur est survenue');
            error.response = response;
            error.data = data;
            throw error;
        }

        return data;
    } catch (error) {
        // Si c'est une erreur réseau, on la gère différemment
        if (error instanceof TypeError && error.message === 'Failed to fetch') {
            handleError('Erreur réseau', { message: 'Impossible de se connecter au serveur' }, 'error');
            return {
                success: false,
                status: 'network_error',
                message: 'Erreur de connexion au serveur',
                data: null
            };
        }

        if (error instanceof Error) {
            handleError('Erreur lors de la requête API', error, 'error');
        }
        return {
            success: false,
            status: error.status || 'client_error',
            message: error.message || 'Erreur inconnue',
            data: error.data || null
        };
    }
}

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

// Pour une gestion des connexions cote client mais non implemente
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
    let notificationDetails = error?.message || error?.error || error?.data?.error || error?.data?.message || "Erreur inconnue";

    // Si on a des infos de debug, les ajouter aux détails
    if (error && error.debug_message) {
        notificationDetails = `${error.debug_message}`;
        if (error.debug_file && error.debug_line) {
            notificationDetails += ` (${error.debug_file}:${error.debug_line})`;
        }
    }

    showNotification(notificationMessage, notificationDetails, type);
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

