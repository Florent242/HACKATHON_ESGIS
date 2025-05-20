class AuthService {
    // Configurations des routes par rôle (à adapter selon votre structure)
    static ROUTES = {
        guest: '/HACKATHON_ESGIS/public/auth',
        admin: '/HACKATHON_ESGIS/public/admin',
        participant: '/HACKATHON_ESGIS/public/user',
        visitor: '/HACKATHON_ESGIS/public' // Nouvelle route visiteur
    };

    // Vérifie l'authentification et redirige si nécessaire
    static async verifyAuth() {
        try {
            const response = await fetch('/HACKATHON_ESGIS/public/api/auth/check', {
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
            admin: /^\/HACKATHON_ESGIS\/public\/admin/,
            participant: /^\/HACKATHON_ESGIS\/public\/user/,
            visitor: /^\/HACKATHON_ESGIS\/public\/(auth|challenge|contact|sponsors|hackathon|leaderboard|resources)/ // Chemins publics
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
            ? '/HACKATHON_ESGIS/public/auth_admin'
            : '/HACKATHON_ESGIS/public/auth';
    }

    // Déconnexion
    static async logout() {
        try {
            await fetch('/HACKATHON_ESGIS/public/api/auth/logout', {
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
    // notification.addEventListener('animationend', () => notification.remove(), { once: true });
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
// Fonction utilitaire pour gérer les requêtes API
async function apiRequest(endpoint, options = {}) {
    try {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        const response = await fetch(`/HACKATHON_ESGIS/public/api${endpoint}`, {
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
        // Si authentifié mais sur une page non autorisée -> redirection
        if (!AuthService.checkPathPermission(authCheck.userRole)) {
            AuthService.redirectToRoleHome(authCheck.userRole);
            return;
        }

        // Ici l'utilisateur est bien authentifié et autorisé
        console.log('Utilisateur connecté:', authCheck.authenticated);

        // Vous pouvez ajouter ici des initialisations spécifiques aux utilisateurs connectés
        // Par exemple :
        // - Charger des données utilisateur
        // - Mettre à jour l'UI
        // - Initialiser des écouteurs d'événements spécifiques
    }
}
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
            // fetch('clearNotification.php', { method: 'POST' })
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

