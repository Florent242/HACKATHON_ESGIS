document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des éléments du header
    initializeHeaderElements();
    // Initialisation des icônes Lucide
    if (window.lucide) {
        lucide.createIcons();
    }
});

function initializeHeaderElements() {
    // Gestion du menu mobile
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const nav = document.querySelector('.nav');
    const navLinks = document.querySelector('.nav-links');
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const logoutButton = document.getElementById('logout-button');
    const html = document.documentElement;
    let isMobileMenuOpen = false;

    // Initialiser les menus déroulants Bootstrap
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl, {
            popperConfig: function(defaultBsPopperConfig) {
                return {
                    ...defaultBsPopperConfig,
                    strategy: 'fixed'
                };
            }
        });
    });

    // Toggle du menu mobile
    if (mobileMenuButton && navLinks) {
        mobileMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            isMobileMenuOpen = !isMobileMenuOpen;
            this.setAttribute('aria-expanded', isMobileMenuOpen);
            navLinks.classList.toggle('mobile-menu-open', isMobileMenuOpen);
            
            // Empêcher le défilement du body quand le menu est ouvert
            if (isMobileMenuOpen) {
                html.style.overflow = 'hidden';
                // Fermer tous les menus déroulants lors de l'ouverture du menu mobile
                dropdownList.forEach(function(dropdown) {
                    dropdown.hide();
                });
            } else {
                html.style.overflow = '';
            }
        });
    }

    // Gestion de la déconnexion
    if (logoutButton) {
        logoutButton.addEventListener('click', async function(e) {
            e.preventDefault();
            
            // Afficher un indicateur de chargement
            const originalContent = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Déconnexion...';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const response = await apiRequest('/auth/logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                if (response.redirect) {
                    // Ajouter une animation de sortie
                    document.documentElement.classList.add('fade-out');
                    
                    // Rediriger après un court délai pour permettre l'animation
                    setTimeout(() => {
                        window.location.href = response.redirect || '/admin/login';
                    }, 300);
                } else {
                    throw new Error(response.message || 'Erreur lors de la déconnexion');
                }
            } catch (error) {
                console.error('Erreur:', error);
                // Afficher une notification d'erreur
                if (window.showNotification) {
                    showNotification(
                        'Erreur lors de la déconnexion',
                        error.message || 'Une erreur est survenue',
                        'error'
                    );
                } else {
                    alert(error.message || 'Une erreur est survenue lors de la déconnexion');
                }
                
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = originalContent;
            }
        });
    }

    // Fermer le menu mobile lors du redimensionnement de la fenêtre
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && isMobileMenuOpen) {
            isMobileMenuOpen = false;
            if (mobileMenuButton) mobileMenuButton.setAttribute('aria-expanded', 'false');
            if (navLinks) navLinks.classList.remove('mobile-menu-open');
            html.style.overflow = '';
        }
    });
}