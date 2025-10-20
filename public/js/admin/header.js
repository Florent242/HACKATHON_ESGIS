document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des icônes Lucide
    if (window.lucide) {
        lucide.createIcons();
    }

    // Éléments du DOM
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const navLinks = document.querySelector('.nav-links');
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    const logoutButton = document.getElementById('logout-button');
    const html = document.documentElement;
    let isMobileMenuOpen = false;

    // Gestion du menu déroulant utilisateur
    if (dropdownToggle && dropdownMenu) {        
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Basculer l'affichage du menu
            if (dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
            } else {
                dropdownMenu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            }
        });

        // Fermer le menu déroulant en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.remove('show');
                dropdownToggle.setAttribute('aria-expanded', 'false');
            }
        });

        // Empêcher la fermeture lors d'un clic dans le menu
        dropdownMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Gestion du menu mobile
    if (mobileMenuButton && navLinks) {
        mobileMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();
            isMobileMenuOpen = !isMobileMenuOpen;
            this.setAttribute('aria-expanded', isMobileMenuOpen);
            navLinks.classList.toggle('mobile-menu-open');
            
            // Empêcher le défilement du body quand le menu est ouvert
            if (isMobileMenuOpen) {
                html.style.overflow = 'hidden';
            } else {
                html.style.overflow = '';
            }
        });
    }

    // Gestion de la déconnexion
    if (logoutButton) {
        logoutButton.addEventListener('click', async function(e) {
            e.preventDefault();
            
            // Désactiver le bouton et afficher le spinner
            const originalContent = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Déconnexion en cours...';

            try {
                const response = await apiRequest('/auth/logout', {
                    method: 'POST',
                    credentials: 'same-origin'
                });

                const data = response;

                if (response.success) {
                    // Ajouter une animation de sortie
                    document.documentElement.classList.add('fade-out');

                    // Rediriger après un court délai pour permettre l'animation
                    setTimeout(() => {
                        window.location.href = data.redirect || '/admin/login';
                    }, 300);
                } else {
                    throw new Error(data.message || 'Erreur lors de la déconnexion');
                }
            } catch (error) {
                console.error('Erreur:', error);
                // Réactiver le bouton
                this.disabled = false;
                this.innerHTML = originalContent;
                
                // Afficher un message d'erreur
                showNotification('Erreur', error.message || 'Une erreur est survenue lors de la déconnexion', 'error');
            }
        });
    }

    // Fermer le menu mobile lors du redimensionnement
    function handleResize() {
        if (window.innerWidth > 768) {
            if (isMobileMenuOpen) {
                isMobileMenuOpen = false;
                if (mobileMenuButton) {
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                }
                if (navLinks) {
                    navLinks.classList.remove('mobile-menu-open');
                }
                html.style.overflow = '';
            }
        }
    }

    // Détecter le redimensionnement avec debounce
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleResize, 100);
    });

    //  // Gestion des notifications de session
    // const notificationData = document.getElementById('notification-data');
    // if (notificationData) {
    //     try {
    //         const notification = JSON.parse(notificationData.dataset.notification);
    //         if (notification && notification.message) {
    //             showNotification(notification.title || 'Notification', notification.message, notification.type || 'info');
                
    //             // Effacer la notification après affichage
    //             fetch('/HACKATHON_ESGIS/public/api/clear-notification', {
    //                 method: 'POST',
    //                 headers: {
    //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    //                 }
    //             });
    //         }
    //     } catch (e) {
    //         console.error('Erreur lors du traitement de la notification:', e);
    //     }
    // }
});