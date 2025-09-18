// ==================================================
// HEADER JAVASCRIPT AMÉLIORÉ
// ==================================================

class HeaderManager {
    constructor() {
        this.mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        this.mobileMenu = document.querySelector('.mobile-menu');
        this.header = document.querySelector('header');
        this.lastScrollY = window.scrollY;
        this.isMenuOpen = false;
        this.scrollPosition = 0;
        this.body = document.body;
        this.touchStartY = 0;
        this.scrollTop = 0;
        
        // Lier les méthodes pour le contexte this
        this.handleTouchStart = this.handleTouchStart.bind(this);
        this.handleTouchMove = this.handleTouchMove.bind(this);
        this.toggleMobileMenu = this.toggleMobileMenu.bind(this);
        this.closeMobileMenu = this.closeMobileMenu.bind(this);
        this.handleOutsideClick = this.handleOutsideClick.bind(this);
        this.handleKeydown = this.handleKeydown.bind(this);
        this.handleResize = this.handleResize.bind(this);

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupScrollAnimations();
        this.setupSmoothScroll();
        this.setupHeaderScroll();
        this.preloadAnimations();
    }

    // Configuration des écouteurs d'événements
    setupEventListeners() {
        // Bouton Start Challenge
        const startBtn = document.querySelector('.start-challenge');
        if (startBtn) {
            startBtn.addEventListener('click', this.handleStartChallenge.bind(this));
        }
        // Menu mobile
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.addEventListener('click', this.toggleMobileMenu);
            
            // Ajouter les écouteurs tactiles pour le menu mobile
            if (this.mobileMenu) {
                this.mobileMenu.addEventListener('touchstart', this.handleTouchStart, { passive: true });
                this.mobileMenu.addEventListener('touchmove', this.handleTouchMove, { passive: false });
            }
        }
        
        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', this.handleOutsideClick);
        
        // Fermer avec Echap
        document.addEventListener('keydown', this.handleKeydown);
        
        // Gestion du redimensionnement
        window.addEventListener('resize', this.handleResize);
        
        // Fermer en cliquant sur un lien du menu
        if (this.mobileMenu) {
            this.mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    this.closeMobileMenu();
                });
            });
        }

        // Gestion du redimensionnement
        window.addEventListener('resize', this.handleResize.bind(this));

        // Prévenir le scroll du body quand le menu mobile est ouvert
        this.preventBodyScroll();
    }

    // Gestion du bouton Start Challenge avec effet de ripple
    handleStartChallenge(e) {
        const button = e.currentTarget;

        // Effet ripple
        const ripple = document.createElement('span');
        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.cssText = `
            position: absolute;
            width: ${size}px;
            height: ${size}px;
            left: ${x}px;
            top: ${y}px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            transform: scale(0);
            animation: ripple 0.6s ease-out;
            pointer-events: none;
        `;

        // Ajouter le style d'animation si nécessaire
        if (!document.getElementById('ripple-style')) {
            const style = document.createElement('style');
            style.id = 'ripple-style';
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        button.style.position = 'relative';
        button.style.overflow = 'hidden';
        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);

        // Navigation avec délai pour l'animation
        setTimeout(() => {
            window.location.href = '/auth';
        }, 200);
    }

    // Gestion du menu mobile
    toggleMobileMenu(e) {
        if (e) e.stopPropagation();

        if (this.isMenuOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }

    openMobileMenu() {
        if (!this.mobileMenu || !this.mobileMenuBtn) return;
        
        // Sauvegarder la position de défilement
        this.scrollPosition = window.scrollY;
        
        // Ajouter les classes actives
        this.mobileMenu.classList.add('active');
        this.mobileMenuBtn.classList.add('active');
        this.isMenuOpen = true;
        
        // Empêcher le défilement de la page
        this.body.style.overflow = 'hidden';
        this.body.style.position = 'fixed';
        this.body.style.width = '100%';
        this.body.style.top = `-${this.scrollPosition}px`;
        
        // Ajouter l'animation séquentielle aux éléments
        const menuItems = this.mobileMenu.querySelectorAll('li');
        menuItems.forEach((item, index) => {
            item.style.transitionDelay = `${0.1 + index * 0.05}s`;
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
            
            // Forcer le reflow
            void item.offsetWidth;
            
            // Ajouter la classe d'animation
            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        });
        
        // Ajouter les écouteurs tactiles
        this.addTouchListeners();
    }

    closeMobileMenu() {
        if (!this.mobileMenu || !this.mobileMenuBtn) return;
        
        // Retirer les classes actives
        this.mobileMenu.classList.remove('active');
        this.mobileMenuBtn.classList.remove('active');
        this.isMenuOpen = false;
        
        // Rétablir le défilement de la page
        this.body.style.overflow = '';
        this.body.style.position = '';
        this.body.style.width = '';
        this.body.style.top = '';
        
        // Restaurer la position de défilement
        window.scrollTo(0, this.scrollPosition);
        
        // Réinitialiser les styles d'animation
        const menuItems = this.mobileMenu.querySelectorAll('li');
        menuItems.forEach(item => {
            item.style.transition = '';
            item.style.opacity = '';
            item.style.transform = '';
        });
        
        // Supprimer les écouteurs tactiles
        this.removeTouchListeners();
    }

    handleOutsideClick(e) {
        if (this.isMenuOpen &&
            !this.mobileMenu.contains(e.target) &&
            !this.mobileMenuBtn.contains(e.target)) {
            this.closeMobileMenu();
        }
    }

    handleKeydown(e) {
        if (e.key === 'Escape' && this.isMenuOpen) {
            this.closeMobileMenu();
        }
    }

    handleResize() {
        // Fermer le menu mobile sur redimensionnement
        if (window.innerWidth > 865 && this.isMenuOpen) {
            this.closeMobileMenu();
        }
    }

    // Gestion des événements tactiles pour le menu mobile
    handleTouchStart(e) {
        if (!this.mobileMenu || !this.isMenuOpen) return;
        this.touchStartY = e.touches[0].clientY;
        this.scrollTop = this.mobileMenu.scrollTop;
    }

    handleTouchMove(e) {
        if (!this.mobileMenu || !this.isMenuOpen) return;
        
        const y = e.touches[0].clientY;
        const deltaY = y - this.touchStartY;
        const isScrollingDown = deltaY > 0;
        
        // Empêcher le défilement du body lorsque l'utilisateur fait défiler le menu
        if ((this.scrollTop <= 0 && isScrollingDown) || 
            (this.scrollTop >= this.mobileMenu.scrollHeight - this.mobileMenu.offsetHeight && !isScrollingDown)) {
            e.preventDefault();
        }
    }
    
    // Nettoyage des écouteurs d'événements
    destroy() {
        // Retirer les écouteurs du menu mobile
        if (this.mobileMenuBtn) {
            this.mobileMenuBtn.removeEventListener('click', this.toggleMobileMenu);
        }
        
        // Retirer les écouteurs tactiles
        if (this.mobileMenu) {
            this.mobileMenu.removeEventListener('touchstart', this.handleTouchStart);
            this.mobileMenu.removeEventListener('touchmove', this.handleTouchMove);
        }
        
        // Retirer les autres écouteurs
        document.removeEventListener('click', this.handleOutsideClick);
        document.removeEventListener('keydown', this.handleKeydown);
        window.removeEventListener('resize', this.handleResize);
    }

    // Gestion du scroll du header
    setupHeaderScroll() {
        let ticking = false;

        const updateHeader = () => {
            const currentScrollY = window.scrollY;

            // Ajouter classe scrolled
            if (currentScrollY > 50) {
                this.header.classList.add('scrolled');
            } else {
                this.header.classList.remove('scrolled');
            }

            this.lastScrollY = currentScrollY;
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });

        // Ajouter les styles pour l'état scrolled
        if (!document.getElementById('scroll-styles')) {
            const style = document.createElement('style');
            style.id = 'scroll-styles';
            style.textContent = `
                header.scrolled {
                    background: rgba(11, 17, 33, 0.95);
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                }
                
                header.scrolled::before {
                    opacity: 1;
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Configuration des animations de scroll
    setupScrollAnimations() {
        const observerOptions = {
            root: null,
            threshold: 0.15,
            rootMargin: '-50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                } else {
                    entry.target.classList.remove('visible');
                }
            });
        }, observerOptions);

        // Observer tous les éléments animés
        const animatedElements = document.querySelectorAll(
            '.fade-in, .fade-out, .fade-in-left, .fade-in-right'
        );

        animatedElements.forEach(element => {
            observer.observe(element);
        });

        // Performance: déconnecter l'observer quand tous les éléments sont visibles
        this.optimizeObserver(observer, animatedElements);
    }

    // Optimisation de l'observer
    optimizeObserver(observer, elements) {
        let visibleCount = 0;
        const totalCount = elements.length;

        const checkCompletion = () => {
            visibleCount++;
            if (visibleCount >= totalCount) {
                // Déconnecter après un délai pour permettre les sorties de viewport
                setTimeout(() => {
                    observer.disconnect();
                }, 5000);
            }
        };

        elements.forEach(element => {
            const originalCallback = () => {
                if (element.classList.contains('visible')) {
                    checkCompletion();
                }
            };

            element.addEventListener('transitionend', originalCallback, { once: true });
        });
    }

    // Configuration du scroll fluide
    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();

                const targetId = anchor.getAttribute('href');
                const target = document.querySelector(targetId);

                if (target) {
                    const headerHeight = this.header.offsetHeight;
                    const targetPosition = target.offsetTop - headerHeight - 20;

                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });

                    // Fermer le menu mobile si ouvert
                    if (this.isMenuOpen) {
                        this.closeMobileMenu();
                    }
                }
            });
        });
    }

    // Préchargement des animations
    preloadAnimations() {
        // Forcer le reflow pour préparer les animations
        document.body.offsetHeight;

        // Pré-calculer les positions pour de meilleures performances
        const animatedElements = document.querySelectorAll(
            '.fade-in, .fade-out, .fade-in-left, .fade-in-right'
        );

        animatedElements.forEach(element => {
            // Stocker la position initiale
            element.dataset.initialTop = element.getBoundingClientRect().top;
        });
    }

    // Méthodes utilitaires
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function () {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// ==================================================
// INITIALISATION ET UTILITAIRES GLOBAUX
// ==================================================

// Instance globale du gestionnaire de header
let headerManager;

// Initialisation quand le DOM est prêt
const initHeader = () => {
    headerManager = new HeaderManager();

    // Gestion des notifications depuis les données PHP
    const notificationData = document.getElementById('notification-data');
    if (notificationData) {
        const notification = JSON.parse(notificationData.dataset.notification || 'null');
        if (notification) {
            headerManager.showNotification(
                notification.title || 'Notification',
                notification.message || '',
                notification.type || 'info'
            );
        }
    }
};

// Attendre que le DOM soit prêt
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeader);
} else {
    initHeader();
}

// ==================================================
// GESTION DES PERFORMANCES
// ==================================================

// Optimisation des performances avec Intersection Observer
const performanceOptimizations = () => {
    // Lazy loading pour les éléments non critiques
    const lazyElements = document.querySelectorAll('[data-lazy]');
    if (lazyElements.length > 0) {
        const lazyObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const element = entry.target;
                    const src = element.dataset.lazy;
                    if (src) {
                        element.src = src;
                        element.removeAttribute('data-lazy');
                        lazyObserver.unobserve(element);
                    }
                }
            });
        });

        lazyElements.forEach(element => lazyObserver.observe(element));
    }

    // Préchargement conditionnel
    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => {
            // Précharger les ressources non critiques
            const preloadLinks = document.querySelectorAll('link[rel="preload"]');
            preloadLinks.forEach(link => {
                if (!link.href) return;

                const prefetchLink = document.createElement('link');
                prefetchLink.rel = 'prefetch';
                prefetchLink.href = link.href;
                document.head.appendChild(prefetchLink);
            });
        });
    }
};

// Lancer les optimisations après l'initialisation
window.addEventListener('load', performanceOptimizations);

// ==================================================
// GESTION DES ERREURS ET DEBUG
// ==================================================

// Gestion globale des erreurs
window.addEventListener('error', (e) => {
    console.warn('Erreur dans le header:', e.error);
    // En production, vous pourriez envoyer ces erreurs à un service de monitoring
});

// Mode debug (activé via console ou paramètre URL)
const enableDebugMode = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const debugMode = urlParams.get('debug') === 'header' || window.headerDebug;

    if (debugMode) {
        console.log('🚀 Header Debug Mode activé');
        console.log('📱 Viewport:', window.innerWidth + 'x' + window.innerHeight);
        console.log('🎯 User Agent:', navigator.userAgent);

        // Ajouter des indicateurs visuels
        document.body.classList.add('debug-mode');

        const debugStyle = document.createElement('style');
        debugStyle.textContent = `
            .debug-mode .header-container {
                outline: 2px dashed #ff0080;
            }
            .debug-mode nav a {
                outline: 1px dashed #00ff80;
            }
            .debug-mode .mobile-menu {
                outline: 2px dashed #ff8000;
            }
        `;
        document.head.appendChild(debugStyle);
    }
};

enableDebugMode();

// ==================================================
// API PUBLIQUE
// ==================================================

// Exposer certaines méthodes pour utilisation externe
window.HeaderAPI = {
    toggleMobileMenu: () => {
        if (headerManager) {
            headerManager.toggleMobileMenu({ stopPropagation: () => { } });
        }
    },

    closeMobileMenu: () => {
        if (headerManager) {
            headerManager.closeMobileMenu();
        }
    }
};

// ==================================================
// GESTION DES RACCOURCIS CLAVIER
// ==================================================

document.addEventListener('keydown', (e) => {
    // Alt + M pour ouvrir/fermer le menu mobile
    if (e.altKey && e.key === 'm') {
        e.preventDefault();
        if (headerManager) {
            headerManager.toggleMobileMenu({ stopPropagation: () => { } });
        }
    }

    // Alt + S pour focus sur le bouton Start Challenge
    if (e.altKey && e.key === 's') {
        e.preventDefault();
        const startBtn = document.querySelector('.start-challenge');
        if (startBtn) {
            startBtn.focus();
        }
    }
});

// Export pour utilisation en module (si nécessaire)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = HeaderManager;
}