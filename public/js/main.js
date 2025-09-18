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

// ========== Security helpers (XSS) ==========
/**
 * Encode plain text to HTML entities (safe for text insertion)
 * @param {string} str
 * @returns {string}
 */
function escapeHTML(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Sanitize an HTML string with a conservative whitelist.
 * - Removes disallowed tags and attributes
 * - Strips event handlers (on*) and javascript: URLs
 * - Keeps only safe URL protocols (http, https, mailto, tel)
 * @param {string} dirtyHTML
 * @param {{allowedTags?: string[], allowedAttrs?: Record<string,string[]>, allowDataImages?: boolean}} [opts]
 * @returns {string}
 */
function sanitizeHTML(dirtyHTML, opts = {}) {
    if (!dirtyHTML) return '';

    const DEFAULT_ALLOWED_TAGS = ['b','i','em','strong','u','s','br','p','ul','ol','li','blockquote','code','pre','span','a'];
    const DEFAULT_ALLOWED_ATTRS = {
        a: ['href','title','target','rel'],
        span: ['class'],
        code: ['class'],
        pre: ['class']
    };
    const allowedTags = new Set((opts.allowedTags || DEFAULT_ALLOWED_TAGS).map(t => t.toLowerCase()));
    const allowedAttrs = opts.allowedAttrs || DEFAULT_ALLOWED_ATTRS;
    const allowDataImages = Boolean(opts.allowDataImages);

    const SAFE_URL = /^(https?:|mailto:|tel:)/i;
    const DATA_IMAGE = /^data:image\/(png|jpeg|jpg|gif|webp);base64,/i;

    const template = document.createElement('template');
    template.innerHTML = dirtyHTML;

    const sanitizeNode = (node) => {
        // Remove comment nodes
        if (node.nodeType === Node.COMMENT_NODE) {
            node.remove();
            return;
        }
        // Text nodes are safe
        if (node.nodeType === Node.TEXT_NODE) {
            return;
        }
        // Element nodes
        if (node.nodeType === Node.ELEMENT_NODE) {
            const tag = node.tagName.toLowerCase();
            if (!allowedTags.has(tag)) {
                // Replace disallowed element with its text content
                const text = document.createTextNode(node.textContent || '');
                node.replaceWith(text);
                return;
            }
            // Clone allowed attributes safely
            [...node.attributes].forEach(attr => {
                const name = attr.name.toLowerCase();
                const value = attr.value;

                // Strip all event handlers (on*) and style attributes
                if (name.startsWith('on') || name === 'style') {
                    node.removeAttribute(attr.name);
                    return;
                }

                const tagAllowed = (allowedAttrs[tag] || []).map(a => a.toLowerCase());
                if (!tagAllowed.includes(name)) {
                    node.removeAttribute(attr.name);
                    return;
                }

                // Special handling for URL-bearing attributes
                if ((tag === 'a' && name === 'href')) {
                    const val = value.trim();
                    const safe = SAFE_URL.test(val) || (allowDataImages && DATA_IMAGE.test(val));
                    if (!safe) {
                        node.removeAttribute(attr.name);
                    } else {
                        // Security: enforce rel and target safety
                        if (!node.getAttribute('rel')) node.setAttribute('rel', 'noopener noreferrer');
                        if (/_blank/i.test(node.getAttribute('target') || '')) {
                            node.setAttribute('rel', 'noopener noreferrer');
                        }
                    }
                }
            });
        }
        // Recurse children (use slice to avoid live collection issues if nodes removed)
        Array.from(node.childNodes).forEach(sanitizeNode);
    };

    Array.from(template.content.childNodes).forEach(sanitizeNode);
    return template.innerHTML;
}

/**
 * Safely set innerHTML: sanitize first
 * @param {HTMLElement} el
 * @param {string} html
 * @param {Parameters<typeof sanitizeHTML>[1]} [opts]
 */
function setSafeHTML(el, html, opts) {
    if (!el) return;
    el.innerHTML = sanitizeHTML(html, opts);
}
// ========== End Security helpers ==========

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
    let topPosition = 70; // Position de départ en haut

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
    notification.className = `fixed right-4 bg-gray-900/90 backdrop-blur-sm border ${type === 'success' ? 'border-green-500/30' : type === 'error' ? 'border-red-500/30' : type === 'warning' ? 'border-yellow-500/30' : 'border-blue-500/30'} rounded-lg shadow-lg shadow-black/30 p-3 max-md:p-2 flex items-start justify-between gap-3 animate-fade-in z-[100000] cursor-pointer min-h-[4rem] max-h-[6rem] w-[45vw] sm:w-[45vw] md:w-[35vw] lg:w-[35vw]`;

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
    messageElement.className = 'text-white font-medium text-sm max-md:text-xs line-clamp-1 whitespace-pre-line';
    setSafeHTML(messageElement, message);
    messageElement.dataset.tooltip = message;
    textContainer.appendChild(messageElement);

    // Message de détails (en option)
    if (details) {
        const detailsElement = document.createElement('p');
        detailsElement.className = 'text-gray-300/90 font-normal text-xs max-md:text-[0.6rem] mt-1 line-clamp-2 max-md:line-clamp-3';
        setSafeHTML(detailsElement, details);
        detailsElement.dataset.tooltip = details;
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

    // Initioliser les tooltips
    initializeTooltips();

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

        const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
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

// Fonction pour rafraîchir le token CSRF
async function refreshCsrfToken() {
    try {
        const response = await fetch('/api/users/refresh-csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!response.ok) throw new Error('Failed to refresh CSRF token');

        const data = await response.json();
        if (data.csrf_token) {
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.content = data.csrf_token;
            return data.csrf_token;
        }
    } catch (error) {
        console.error('Error refreshing CSRF token:', error);
        throw error;
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
        const response = await apiRequest('/users/me', {
            method: 'GET',
            credentials: 'include',
            headers: { 'Accept': 'application/json' }
        });

        return response.data?.id;  // Retourne bien l'ID utilisateur
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

/**
 * @description Fonction pour initialiser les tooltips
 * @returns {void}
 * @usage initializeTooltips();
 * @prerequis mettre en place les tooltips dans le HTML avec le data-tooltip
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');

    tooltipElements.forEach(el => {
        let tooltipEl;
        let hideTimeout;

        const show = () => {
            clearTimeout(hideTimeout);

            const tooltip = el.getAttribute('data-tooltip');
            if (!tooltip) return;

            // Si déjà un tooltip affiché, on le supprime
            if (tooltipEl) tooltipEl.remove();

            tooltipEl = document.createElement('div');
            tooltipEl.className = `
                fixed px-2 py-2 text-sm rounded-lg shadow-lg border
                border-slate-700 bg-slate-900/95 backdrop-blur-sm text-white
                opacity-0 transition-all duration-200 transform scale-95
                pointer-events-none z-[100001]! max-w-xs break-words
                text-left font-normal leading-normal
            `;
            tooltipEl.textContent = tooltip;
            tooltipEl.setAttribute("role", "tooltip");
            tooltipEl.setAttribute("aria-hidden", "true");

            document.body.appendChild(tooltipEl);

            // Calculate positions with viewport boundaries
            const rect = el.getBoundingClientRect();
            const tooltipRect = tooltipEl.getBoundingClientRect();
            const viewportPadding = 12;

            // Default position: centered above the element
            let top = rect.top - tooltipRect.height - 10;
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
            let arrowPosition = '';

            // Check for viewport collisions
            // Horizontal adjustment
            if (left < viewportPadding) {
                left = viewportPadding;
            } else if (left + tooltipRect.width > window.innerWidth - viewportPadding) {
                left = window.innerWidth - tooltipRect.width - viewportPadding;
            }

            // Vertical adjustment
            if (top < viewportPadding) {
                // Not enough space above, position below
                top = rect.bottom + 10;
                arrowPosition = 'bottom';
            } else {
                arrowPosition = 'top';
            }

            // Add arrow class based on position
            tooltipEl.classList.add(`tooltip-arrow-${arrowPosition}`);

            // Apply final position
            tooltipEl.style.top = `${Math.max(viewportPadding, Math.min(top, window.innerHeight - tooltipRect.height - viewportPadding))}px`;
            tooltipEl.style.left = `${Math.max(viewportPadding, Math.min(left, window.innerWidth - tooltipRect.width - viewportPadding))}px`;

            // Trigger reflow and animate in
            void tooltipEl.offsetWidth; // Force reflow
            tooltipEl.style.opacity = '1';
            tooltipEl.style.transform = 'scale(1)';
        };

        const hide = () => {
            if (tooltipEl) {
                tooltipEl.classList.add("opacity-0", "scale-95");
                hideTimeout = setTimeout(() => {
                    tooltipEl?.remove();
                    tooltipEl = null;
                }, 200);
            }
        };

        el.addEventListener('mouseenter', show);
        el.addEventListener('mouseleave', hide);
        el.addEventListener('blur', hide);   // accessibilité (clavier)
        el.addEventListener('click', hide);  // si clic sur élément
    });
}

function showTooltip(e) {
    const tooltip = this.getAttribute('data-tooltip');
    if (!tooltip) return;

    const tooltipEl = document.createElement('div');
    tooltipEl.className = 'tooltip';
    tooltipEl.textContent = tooltip;

    // Positionnement
    const rect = this.getBoundingClientRect();
    tooltipEl.style.position = 'fixed';
    tooltipEl.style.left = `${rect.left + (rect.width / 2)}px`;
    tooltipEl.style.top = `${rect.top - 40}px`;
    tooltipEl.style.transform = 'translateX(-50%)';
    tooltipEl.style.zIndex = '1000';
    tooltipEl.style.pointerEvents = 'none';
    tooltipEl.classList.add('bg-slate-800', 'text-white', 'text-xs', 'px-2', 'py-1', 'rounded', 'shadow-lg', 'border', 'border-slate-700');

    document.body.appendChild(tooltipEl);
    this._tooltip = tooltipEl;
}

function hideTooltip() {
    if (this._tooltip) {
        this._tooltip.remove();
        this._tooltip = null;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    // initialisation des tooltips
    initializeTooltips();
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
