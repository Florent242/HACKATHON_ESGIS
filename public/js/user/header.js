import { createEle } from "../dom.js";

// Handle scroll annimations for elements 
// Select all element that have the .fade-in class for the animation when they are visible
const fadeElements = document.querySelectorAll('.fade-in, .fade-out, .fade-in-left, .fade-in-right');
// Intersection Observer to trigger the animation when the element is visible...hehe that's cool tho
const heroObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
        else {
            entry.target.classList.remove('visible');
        }
    });
}, {
    root: null, // Utilise la fenêtre de visualisation
    threshold: 0.1 // Déclenche l'événement lorsque 10% de l'élément est visible
});

if (fadeElements) {
    fadeElements.forEach(element => {
        heroObserver.observe(element);
    });
}

// Helper function to calculate the total height needed
function calculateTotalHeight(element) {
    // Get the computed styles of the element
    const style = window.getComputedStyle(element);
    const paddingTop = parseFloat(style.paddingTop);
    const paddingBottom = parseFloat(style.paddingBottom);
    const borderTop = parseFloat(style.borderTopWidth);
    const borderBottom = parseFloat(style.borderBottomWidth);
    const marginTop = parseFloat(style.marginTop);
    const marginBottom = parseFloat(style.marginBottom);

    // Calculate total height needed (element height + padding + borders + margins)
    return element.offsetHeight +
        paddingTop +
        paddingBottom +
        borderTop +
        borderBottom +
        marginTop +
        marginBottom;
}

// Setup a MutationObserver to watch for DOM changes inside dropdown
const dropdownObserver = new MutationObserver((mutations) => {
    const activeItem = document.querySelector('.dropdown-item.active');
    if (activeItem) {
        const newHeight = calculateTotalHeight(activeItem);
        dropdown.style.height = `${newHeight}px`;
        dropdownContainer.style.height = `${newHeight}px`;
    }
});

// Start observing the dropdown
const dropdownItems = document.querySelectorAll('.dropdown-item');
if (dropdownItems) {
    dropdownItems.forEach(item => {
        dropdownObserver.observe(item, {
            childList: true,
            subtree: true,
            attributes: true,
            characterData: true
        });
    });
}

// Dropdown menu handling
const headerDropdown = document.querySelector('.header-dropdown');
const dropdown = document.querySelector('.dropdown');
const dropdownContainer = document.querySelector('.dropdown-container');

document.querySelectorAll('.main-nav li').forEach(link => {
    link.addEventListener('mouseenter', function () {
        const itemIndex = this.getAttribute('data-item'); // Get the index of the item to show
        const dropdownItems = document.querySelectorAll('.dropdown-item');

        dropdownItems.forEach((item, index) => {
            item.classList.remove('active');
            if (index == itemIndex) {
                item.classList.add('active');
            }
        });

        headerDropdown.classList.add('visible');
        const activeItem = document.querySelector('.dropdown-item.active');

        // Calculate initial height
        const initialHeight = calculateTotalHeight(dropdownItems[itemIndex]);
        if (activeItem) {
            const height = calculateTotalHeight(activeItem);
        }
        dropdown.style.height = `${initialHeight}px`;
        dropdownContainer.style.height = `${initialHeight}px`;

        dropdown.style.transform = `translateX(-${itemIndex * 100}%)`;

        setTimeout(() => {
            const activeItem = dropdownItems[itemIndex];
            if (activeItem) {
                const newHeight = calculateTotalHeight(activeItem);
                dropdownObserver.disconnect();
                dropdown.style.height = `${newHeight}px`;
                dropdownContainer.style.height = `${newHeight}px`;
                dropdownObserver.observe(activeItem, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    characterData: true
                });
            }
        }, 50);
    });

    link.addEventListener('mouseleave', function () {
        // Hide the dropdown
        headerDropdown.classList.remove('visible'); // Remove the visible class
    });
});

// Show the dropdown when mouse enters the dropdown area
headerDropdown.addEventListener('mouseenter', function () {
    this.classList.add('visible'); // Keep it visible
});

// Hide the dropdown when mouse leaves both the main nav and the dropdown
document.querySelector('.nav-container').addEventListener('mouseleave', function () {
    headerDropdown.classList.remove('visible'); // Hide it when mouse leaves
});

// Hide the dropdown when mouse leaves the dropdown area
headerDropdown.addEventListener('mouseleave', function () {
    this.classList.remove('visible'); // Hide it when mouse leaves
});

// Configuration for the observer
const observerConfig = {
    childList: true,      // Watch for changes to the child elements
    subtree: true,        // Watch all descendants, not just direct children
    attributes: true,     // Watch for changes to attributes
    characterData: true   // Watch for changes to text content
};

document.addEventListener('DOMContentLoaded', async () => {

    //deco's modal window
    const decoInitModal = document.querySelector('#deco');
    const modal = document.querySelector('#fenetre_modal');
    const annuler = document.querySelector('#fermer_modal');
    const closeHeaderBtn = document.getElementById('close_header_btn');

    function showModal() {
        modal.classList.add('show');
        modalContent.classList.add('animate-in');

        // Focus management pour l'accessibilité
        setTimeout(() => {
            document.getElementById('fermer_modal').focus();
        }, 100);
    }

    function hideModal() {
        modal.classList.remove('show');
        modalContent.classList.remove('animate-in');
    }

    //modal window for logout
    decoInitModal.addEventListener('click', (e) => {
        e.preventDefault();
        showModal();
    });

    annuler.addEventListener('click', () => {
        hideModal();
    });

    if (closeHeaderBtn) {
        closeHeaderBtn.addEventListener('click', () => {
            hideModal();
        });
    }

    window.addEventListener('click', (event) => {
        if (event.target === modal && modal.classList.contains('show')) {
            hideModal();
        }
    });

    // Logout button click handler
    const logoutBtn = document.querySelector('#logout-btn');
    const logoutText = document.getElementById('logout-text');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                // État de chargement
                logoutBtn.classList.add('loading');
                logoutBtn.disabled = true;

                // Remplacer le texte par un spinner
                logoutText.innerHTML = '<div class="spinner"></div> Déconnexion...';

                const data = await apiRequest('/auth/logout', {
                    method: 'POST'
                })

                if (data.success) {
                    window.location.href = '/';
                } else {
                    setFlashMessage('error', 'Echec de déconnexion', data.message);
                    return;
                }
            } catch (error) {
                setFlashMessage('error', 'Echec de déconnexion', error.message);
                console.error('Logout failed:', error);
            } finally {
                // Réinitialiser l'état
                logoutBtn.classList.remove('loading');
                logoutBtn.disabled = false;
                logoutText.innerHTML = 'Se déconnecter';
            }
        });
    }
    // Fermer avec Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            hideModal();
        }
    });
})

document.addEventListener('DOMContentLoaded', async () => {

    // Gestion du menu mobile avec style modal
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileNav = document.querySelector('.mobile-nav');
    const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');
    const closeMobileNav = document.querySelector('.close-mobile-nav');
    const mobileNavCategoryHeaders = document.querySelectorAll('.mobile-nav-category-header');
    const mobileLogout = document.querySelector('#mobile-logout');

    // Fonction pour ouvrir le menu mobile
    const openMobileMenu = () => {
        mobileNav.classList.add('active');
        mobileNavOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Empêche le défilement du body
    };

    // Fonction pour fermer le menu mobile
    const closeMobileMenu = () => {
        mobileNav.classList.remove('active');
        mobileNavOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Rétablit le défilement du body
    };

    // Ouvrir le menu mobile
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', openMobileMenu);
    }

    // Fermer le menu mobile
    if (closeMobileNav) {
        closeMobileNav.addEventListener('click', closeMobileMenu);
    }

    // Fermer le menu en cliquant sur l'overlay
    if (mobileNavOverlay) {
        mobileNavOverlay.addEventListener('click', closeMobileMenu);
    }

    // Gérer les catégories déroulantes dans le menu mobile
    if (mobileNavCategoryHeaders) {
        mobileNavCategoryHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const categoryIndex = header.getAttribute('data-category');
                const content = document.querySelector(`.mobile-nav-category-content[data-category="${categoryIndex}"]`);

                // Toggle la classe active pour afficher/masquer le contenu
                content.classList.toggle('active');

                // Rotation de l'icône
                const icon = header.querySelector('[data-lucide="chevron-down"]');
                if (content.classList.contains('active')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            });
        });
    }

    // Gestion de la déconnexion mobile
    if (mobileLogout) {
        mobileLogout.addEventListener('click', () => {
            // Ferme d'abord le menu mobile
            closeMobileMenu();

            // Ouvre la fenêtre modale de déconnexion après un court délai
            setTimeout(() => {
                const modal = document.querySelector('#fenetre_modal');
                if (modal) {
                    modal.classList.add('show');
                }
            }, 300); // Délai pour permettre la transition de fermeture du menu
        });
    }

    // Adaptation pour les grands et petits écrans
    const adjustForScreenSize = () => {
        // Éléments à ajuster
        const profileDropdownContainer = document.querySelector('.profile-dropdown-container');

        if (window.innerWidth <= 768) {
            // Mobile: cacher le dropdown du profil
            if (profileDropdownContainer) {
                profileDropdownContainer.style.display = 'none';
            }
        } else {
            // Desktop: afficher le dropdown du profil
            if (profileDropdownContainer) {
                profileDropdownContainer.style.display = 'block';
            }

            // Fermer le menu mobile si on revient en vue desktop
            if (mobileNav && mobileNav.classList.contains('active')) {
                closeMobileMenu();
            }
        }

        // Adapter la taille des icônes pour mobile
        const icons = document.querySelectorAll('[data-lucide]');
        if (window.innerWidth <= 480) {
            icons.forEach(icon => {
                if (!icon.hasAttribute('width')) {
                    icon.setAttribute('width', '18');
                    icon.setAttribute('height', '18');
                }
            });
        } else {
            icons.forEach(icon => {
                if (icon.getAttribute('width') === '18') {
                    icon.removeAttribute('width');
                    icon.removeAttribute('height');
                }
            });
        }
    };

    // Appeler la fonction d'ajustement au chargement et au redimensionnement
    adjustForScreenSize();
    window.addEventListener('resize', adjustForScreenSize);

    // Gestion des touches clavier (fermer le menu avec Echap)
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileNav.classList.contains('active')) {
            closeMobileMenu();
        }
    });

    // Initialisation des icônes Lucide pour les nouveaux éléments
    if (window.lucide) {
        window.lucide.createIcons();
    }
});

// ==================================================
// NOTIFICATIONS SYSTEM
// ==================================================

class NotificationManager {
    constructor() {
        this.notifications = [];
        this.unreadCount = 0;
        this.isDropdownOpen = false;
        this.refreshInterval = null;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        this.init();
    }

    async init() {
        this.userId = await getUserId();
        this.createNotificationElements();
        this.bindEvents();
        this.loadNotifications();
        this.startRefreshTimer();
    }

    createNotificationElements() {
        const container = document.querySelector('.notifications-container');
        if (!container) return;

        // Créer le dropdown
        const dropdown = document.createElement('div');
        dropdown.className = 'notifications-dropdown';
        dropdown.innerHTML = `
            <div class="notifications-header">
                <h3>Notifications</h3>
                <button class="mark-all-read-btn">Tout marquer comme lu</button>
            </div>
            <div class="notifications-list">
                <div class="notifications-loading">
                    <div class="loading-spinner"></div>
                    Chargement...
                </div>
            </div>
            <div class="notifications-footer">
                <button class="view-all-notifications">
                    <i data-lucide="eye"></i>
                    Voir toutes les notifications
                </button>
            </div>
        `;

        container.appendChild(dropdown);
        this.dropdown = dropdown;
    }

    bindEvents() {
        const notificationBtn = document.querySelector('.notification-btn');
        const markAllReadBtn = this.dropdown.querySelector('.mark-all-read-btn');
        const viewAllBtn = this.dropdown.querySelector('.view-all-notifications');

        // Toggle dropdown
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        // Fermer le dropdown en cliquant ailleurs
        document.addEventListener('click', (e) => {
            if (!this.dropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
                this.closeDropdown();
            }
        });

        // Marquer toutes comme lues
        markAllReadBtn.addEventListener('click', () => {
            this.markAllAsRead();
        });

        // Voir toutes les notifications
        viewAllBtn.addEventListener('click', () => {
            window.location.href = '/user/profile#notifications';
        });

        // Fermer avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isDropdownOpen) {
                this.closeDropdown();
            }
        });
    }

    async loadNotifications() {
        if (!this.userId) return;

        try {
            const response = await apiRequest(`/notifications/user/${this.userId}`, {
                method: 'GET'
            });

            if (response.success) {
                this.notifications = response.data.items;
                this.unreadCount = response.data.unread_count;
                this.updateUI();
            } else {
                this.showError('Erreur lors du chargement des notifications');
            }
        } catch (error) {
            console.error('Erreur notifications:', error);
            this.showError('Impossible de charger les notifications');
        }
    }

    async loadUnreadCount() {
        if (!this.userId) return;

        try {
            const response = await apiRequest(`/notifications/unread-count/${this.userId}`, {
                method: 'GET'
            });

            if (response.success) {
                const newCount = response.data.unread_count;
                if (newCount > this.unreadCount) {
                    // Nouvelles notifications
                    this.animateBell();
                }
                this.unreadCount = newCount?newCount:this.unreadCount;
                this.updateBadge();
            }
        } catch (error) {
            console.error('Erreur compteur notifications:', error);
        }
    }

    updateUI() {
        this.updateBadge();
        this.renderNotifications();
    }

    updateBadge() {
        let badge = document.querySelector('.notification-badge');
        const notificationBtn = document.querySelector('.notification-btn');

        if (this.unreadCount > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'notification-badge';
                notificationBtn.appendChild(badge);
            }
            badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
            badge.classList.add('animate');
            setTimeout(() => badge.classList.remove('animate'), 600);
        } else if (badge) {
            badge.remove();
        }
    }

    renderNotifications() {
        const list = this.dropdown.querySelector('.notifications-list');

        if (this.notifications.length === 0) {
            list.innerHTML = `
                <div class="notifications-empty">
                    <i data-lucide="bell-off"></i>
                    <p>Aucune notification</p>
                </div>
            `;
        } else {
            list.innerHTML = this.notifications.map(notification =>
                this.createNotificationHTML(notification)
            ).join('');
        }

        // Réinitialiser les icônes Lucide
        if (window.lucide) {
            window.lucide.createIcons();
        }

        // Binder les événements sur les notifications
        this.bindNotificationEvents();
        initializeTooltips();
    }

    createNotificationHTML(notification) {
        const isUnread = 
        notification.read_status?notification.read_status== 0:
        !notification.lu;
        const timeAgo = this.formatTimeAgo(notification.created_at);
        const iconType = this.getNotificationIcon(notification.type);

        return `
            <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}" data-tooltip="${isUnread ? 'Cliquer pour marquer comme lue' : 'Lu'}">
                <div class="notification-icon ${notification.type}">
                    <i data-lucide="${iconType}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${this.escapeHtml(notification.title)}</div>
                    <div class="notification-message">${this.escapeHtml(notification.message)}</div>
                    <div class="notification-meta">
                        <span class="notification-time">${timeAgo}</span>
                        ${notification.action ? this.createActionButton(JSON.parse(notification.action)) : ''}
                    </div>
                </div>
            </div>
        `;
    }

    createActionButton(action) {
        if (!action) return '';
        if (action.type == 'multi') {
            return `
                <div class="notification-actions">
                    ${action.actions.map(action =>
                        `<button class="notification-action" data-action='${JSON.stringify(action)}'>${action.label || 'Action'}</button>`
                    ).join('')}
                </div>
            `;
        } else if (action.type == 'none') {
            return '';
        }
        return `<button class="notification-action" data-action='${JSON.stringify(action)}'>${action.label || 'Action'}</button>`;
    }

    getNotificationIcon(type) {
        const icons = {
            info: 'info',
            success: 'check-circle',
            warning: 'alert-triangle',
            error: 'alert-circle'
        };
        return icons[type] || 'bell';
    }

    bindNotificationEvents() {
        const items = this.dropdown.querySelectorAll('.notification-item');

        items.forEach(item => {
            // Marquer comme lu au clic
            item.addEventListener('click', async (e) => {
                if (!e.target.classList.contains('notification-action')) {
                    const id = item.dataset.id;
                    if (item.classList.contains('unread')) {
                        await this.markAsRead(id);
                    }
                }
            });

            // Gérer les actions
            const actionBtn = item.querySelector('.notification-action');
            if (actionBtn) {
                actionBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const action = JSON.parse(actionBtn.dataset.action);
                    await this.handleNotificationAction(action, item.dataset.id);
                });
            }
        });
    }

    async handleNotificationAction(action, notificationId) {
        try {
            switch (action.type) {
                case 'link':
                    window.location.href = action.url;
                    break;

                case 'modal':
                    this.openModal(action.modal_id, action.params || {});
                    break;

                case 'api':
                    const response = await apiRequest(action.endpoint, {
                        method: action.method || 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: action.data ? JSON.stringify({
                            id: notificationId,
                            csrf_token: this.csrfToken,
                            ...action.data
                        }) : null
                    });

                    if (response.success) {
                        await this.markAsRead(notificationId);
                        this.refreshNotifications();
                        showNotification('success', 'Action effectuée', response.message || '');
                    } else {
                        showNotification('Erreur', response.message || response.error || 'Erreur lors de l\'action', 'error');
                    }
                    break;
            }
        } catch (error) {
            console.error('Erreur action notification:', error);
            showNotification('Erreur', 'Impossible d\'effectuer l\'action', 'error');
        }
    }

    openModal(modalId, params) {
        const modal = document.getElementById(modalId);
        if (modal) {
            // Passer les paramètres au modal si nécessaire
            if (params) {
                Object.keys(params).forEach(key => {
                    modal.dataset[key] = params[key];
                });
            }
            modal.classList.add('show');
        }
    }

    async markAsRead(id) {
        try {
            const response = await apiRequest(`/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: id,
                    csrf_token: this.csrfToken
                })
            });

            if (response.success) {
                // Mettre à jour l'UI localement
                const item = this.dropdown.querySelector(`[data-id="${id}"]`);
                if (item) {
                    item.classList.remove('unread');
                }

                // Mettre à jour le compteur
                if (this.unreadCount > 0) {
                    this.unreadCount--;
                    this.updateBadge();
                }

                // Mettre à jour les données locales
                const notification = this.notifications.find(n => n.id == id);
                if (notification) {
                    notification.lu = true;
                }
            }
        } catch (error) {
            console.error('Erreur marquage lu:', error);
        }
    }

    async markAllAsRead() {
        if (!this.userId) return;

        try {
            const response = await apiRequest(`/notifications/mark-all-read/${this.userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: this.userId,
                    csrf_token: this.csrfToken
                })
            });

            if (response.success) {
                // Mettre à jour l'UI
                this.dropdown.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });

                this.unreadCount = 0;
                this.updateBadge();

                // Mettre à jour les données locales
                this.notifications.forEach(n => n.lu = true);

                showNotification('success', 'Succès', 'Toutes les notifications ont été marquées comme lues');
            }
        } catch (error) {
            console.error('Erreur marquage global:', error);
            showNotification('error', 'Erreur', 'Impossible de marquer les notifications');
        }
    }

    toggleDropdown() {
        if (this.isDropdownOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.dropdown.classList.add('show');
        this.isDropdownOpen = true;

        // Recharger les notifications à l'ouverture
        this.loadNotifications();
    }

    closeDropdown() {
        this.dropdown.classList.remove('show');
        this.isDropdownOpen = false;
    }

    animateBell() {
        const btn = document.querySelector('.notification-btn');
        btn.classList.add('notification-bell-ring');
        setTimeout(() => btn.classList.remove('notification-bell-ring'), 800);
    }

    startRefreshTimer() {
        // Vérifier le compteur toutes les 30 secondes
        this.refreshInterval = setInterval(() => {
            this.loadUnreadCount();
        }, 30000);
    }

    refreshNotifications() {
        this.loadNotifications();
    }

    showError(message) {
        const list = this.dropdown.querySelector('.notifications-list');
        list.innerHTML = `
            <div class="notification-error">
                <i data-lucide="alert-circle"></i>
                <p>${message}</p>
                <button class="notification-retry-btn" onclick="notificationManager.loadNotifications()">
                    Réessayer
                </button>
            </div>
        `;

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    formatTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffSecs = Math.floor(diffMs / 1000);
        const diffMins = Math.floor(diffSecs / 60);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffSecs < 60) return 'À l\'instant';
        if (diffMins < 60) return `il y a ${diffMins}m`;
        if (diffHours < 24) return `il y a ${diffHours}h`;
        if (diffDays < 7) return `il y a ${diffDays}j`;

        return date.toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'short'
        });
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Fonction pour rafraîchir les notifications (utilisable globalement)
function refreshNotifications() {
    if (window.notificationManager) {
        window.notificationManager.refreshNotifications();
    }
}

// Initialisation du gestionnaire de notifications
document.addEventListener('DOMContentLoaded', () => {
    // Créer le conteneur de notifications si il n'existe pas
    const existingBtn = document.querySelector('.notification-btn');
    if (existingBtn && !document.querySelector('.notifications-container')) {
        const container = document.createElement('div');
        container.className = 'notifications-container';

        // Déplacer le bouton dans le conteneur
        existingBtn.parentNode.insertBefore(container, existingBtn);
        container.appendChild(existingBtn);
    }

    // Initialiser le gestionnaire si le conteneur existe
    if (document.querySelector('.notifications-container')) {
        window.notificationManager = new NotificationManager();
        window.notificationManager.updateUI();
    }

    // Initialiser les tooltips
    initializeTooltips();
});

// Nettoyer à la fermeture de la page
window.addEventListener('beforeunload', () => {
    if (window.notificationManager) {
        window.notificationManager.destroy();
    }
});