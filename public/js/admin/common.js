/**
 * Fichier commun pour les fonctionnalités partagées entre les pages admin
 */

// Base URL for API requests
const API_BASE_URL = '/api';

/**
 * Récupère un cookie par son nom
 * @param {string} name - Nom du cookie
 * @returns {string|null} - Valeur du cookie ou null
 */
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

/**
 * Effectue une requête API avec gestion des erreurs
 * @param {string} endpoint - Point de terminaison de l'API
 * @param {Object} options - Options de la requête fetch
 * @returns {Promise<Object>} - Données de la réponse
 */
async function apiRequest(endpoint, options = {}) {
    try {
        // Ajouter les en-têtes par défaut
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'include' // Pour envoyer les cookies
        };
        
        // Fusionner les options par défaut avec les options fournies
        const fetchOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...(options.headers || {})
            }
        };
        
        // Récupérer le token JWT depuis les cookies
        const token = getCookie('jwt_token') || getCookie('long_term_token');
        if (token) {
            fetchOptions.headers['Authorization'] = `Bearer ${token}`;
        }
        
        // Effectuer la requête
        const response = await fetch(`${API_BASE_URL}${endpoint}`, fetchOptions);
        
        // Vérifier si la réponse est OK
        if (!response.ok) {
            // Essayer de lire le corps de la réponse pour obtenir plus d'informations sur l'erreur
            let errorData;
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                errorData = await response.json();
            } else {
                const text = await response.text();
                throw new Error(`Erreur HTTP ${response.status}: ${text.substring(0, 100)}...`);
            }
            
            throw new Error(errorData.error || `Erreur HTTP ${response.status}`);
        }
        
        // Vérifier si la réponse est du JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            const text = await response.text();
            console.warn('La réponse n\'est pas du JSON:', text.substring(0, 100));
            throw new Error('La réponse n\'est pas au format JSON attendu');
        }
    } catch (error) {
        console.error('Erreur lors de la requête API', error);
        throw error;
    }
}

/**
 * Récupère l'ID de l'utilisateur connecté
 * @returns {Promise<number|null>} - ID de l'utilisateur ou null
 */
async function getUserId() {
    try {
        // Essayer d'abord de récupérer l'utilisateur actuel
        const response = await apiRequest('/admin/me');
        if (response && response.data && response.data.id) {
            return response.data.id;
        }
        return null;
    } catch (error) {
        console.error('Impossible de récupérer l\'ID utilisateur', error);
        return null;
    }
}

/**
 * Formate une date en format français
 * @param {string} dateString - Date au format ISO
 * @param {boolean} includeTime - Inclure l'heure
 * @returns {string} - Date formatée
 */
function formatDate(dateString, includeTime = false) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    const options = {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    };
    
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    
    return date.toLocaleDateString('fr-FR', options);
}

/**
 * Formate un nombre avec séparateur de milliers
 * @param {number} number - Nombre à formater
 * @returns {string} - Nombre formaté
 */
function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

/**
 * Affiche une notification toast
 * @param {string} message - Message à afficher
 * @param {string} type - Type de notification (success, error, warning, info)
 * @param {number} duration - Durée d'affichage en ms
 */
function showToast(message, type = 'info', duration = 3000) {
    // Créer l'élément toast s'il n'existe pas
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <div class="toast-progress"></div>
    `;
    
    // Ajouter le toast au conteneur
    toastContainer.appendChild(toast);
    
    // Animer la barre de progression
    const progress = toast.querySelector('.toast-progress');
    progress.style.animation = `toast-progress ${duration / 1000}s linear forwards`;
    
    // Supprimer le toast après la durée spécifiée
    setTimeout(() => {
        toast.classList.add('toast-hide');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duration);
}

/**
 * Obtient l'icône pour un type de toast donné
 * @param {string} type - Type de toast
 * @returns {string} - Nom de l'icône
 */
function getToastIcon(type) {
    switch (type) {
        case 'success':
            return 'check-circle';
        case 'error':
            return 'times-circle';
        case 'warning':
            return 'exclamation-triangle';
        case 'info':
        default:
            return 'info-circle';
    }
}

// Initialiser les fonctionnalités communes lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser Lucide icons si disponible
    if (window.lucide) {
        window.lucide.createIcons();
    }
    
    // Initialiser les tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip';
            tooltipEl.textContent = text;
            document.body.appendChild(tooltipEl);
            
            const rect = this.getBoundingClientRect();
            tooltipEl.style.top = `${rect.top - tooltipEl.offsetHeight - 5}px`;
            tooltipEl.style.left = `${rect.left + rect.width / 2 - tooltipEl.offsetWidth / 2}px`;
            tooltipEl.classList.add('tooltip-show');
            
            this.addEventListener('mouseleave', function() {
                tooltipEl.remove();
            }, { once: true });
        });
    });
});

// Exporter les fonctions pour les utiliser dans d'autres fichiers
window.apiRequest = apiRequest;
window.getUserId = getUserId;
window.formatDate = formatDate;
window.formatNumber = formatNumber;
window.showToast = showToast;