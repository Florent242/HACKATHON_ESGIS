<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
<script defer src="/js/admin/header.js"></script>
<script defer src="/js/admin/main.js"></script>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION['csrf_token'] ?>">
    <title><?php echo $pageTitle ?? 'Plateforme de Hackathon'; ?></title>
    <link rel="stylesheet" href="/css/styles/admin/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>
    <header class="header">
        <div class="container">
            <nav class="nav">

                <!-- Bouton menu mobile -->
                <button class="mobile-menu-button" aria-label="Menu" aria-expanded="false" aria-controls="nav-links">
                    <i data-lucide="menu"></i>
                </button>

                <!-- Liens de navigation -->
                <div class="nav-links" id="nav-links">
                    <?php
                    // Utilise uniquement le chemin sans les paramètres de requête
                    $currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                    $navItems = [
                        '/admin' => ['Tableau de bord', 'layout-dashboard'],
                        '/admin/hackathon' => ['Hackathons', 'laptop'],
                        '/admin/challenges' => ['Challenges', 'trophy'],
                        '/admin/utilisateurs' => ['Utilisateurs', 'users'],
                        '/admin/equipes' => ['Équipes', 'users-2'],
                        '/admin/resources' => ['Ressources', 'book-open'],
                        '/admin/logs' => ['Journaux', 'file-text'],
                        '/admin/soumissions' => ['Soumissions', 'file-code-2'],
                    ];

                    // Générer les éléments de navigation principaux
                    foreach ($navItems as $page => $item) {
                        $activeClass = ($currentPage === $page) ? 'active' : '';
                        echo "<a href=\"$page\" class=\"nav-link $activeClass\">";
                        echo "<i data-lucide=\"{$item[1]}\" class=\"nav-icon\"></i> {$item[0]}";
                        echo "</a>";
                    }
                    ?>

                    <!-- Menu utilisateur -->
                    <div class="user-menu dropdown">
                        <button class="dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i data-lucide="user" class="nav-icon"></i>
                            <span>Mon compte</span>
                            <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                        </button>
                        
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/admin/notifications">
                                <i data-lucide="bell" class="nav-icon me-2"></i> Notifications
                            </a></li>
                            <li><a class="dropdown-item" href="/admin/parametres">
                                <i data-lucide="settings" class="nav-icon me-2"></i> Paramètres
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><button id="logout-button" class="dropdown-item text-danger">
                                <i data-lucide="log-out" class="nav-icon me-2"></i> Déconnexion
                            </button></li>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Script pour gérer le menu mobile et la déconnexion -->
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser les icônes Lucide
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

        // Gestion du menu mobile
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

        // Fermer le menu mobile lors du redimensionnement de la fenêtre
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && isMobileMenuOpen) {
                isMobileMenuOpen = false;
                mobileMenuButton.setAttribute('aria-expanded', 'false');
                navLinks.classList.remove('mobile-menu-open');
                html.style.overflow = '';
            }
        });

        // Gestion de la déconnexion
        if (logoutButton) {
            // Mettre à jour la structure du bouton de déconnexion
            logoutButton.innerHTML = '<i data-lucide="log-out" class="nav-icon me-2"></i> Déconnexion';
            
            // Réinitialiser les icônes Lucide
            if (window.lucide) {
                lucide.createIcons();
            }
            
            logoutButton.addEventListener('click', async function(e) {
                e.preventDefault();
                
                // Désactiver le bouton et ajouter la classe de chargement
                const originalContent = this.innerHTML;
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Déconnexion en cours...';
                
                try {
                    const response = await fetch('/HACKATHON_ESGIS/public/api/auth/admin/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        credentials: 'same-origin'
                    });

                    const data = await response.json();
                    
                    if (response.ok) {
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
                    
                    // Afficher un message d'erreur élégant
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded';
                    errorMessage.role = 'alert';
                    errorMessage.innerHTML = `
                        <strong class="font-bold">Erreur !</strong>
                        <span class="block sm:inline">${error.message || 'Une erreur est survenue lors de la déconnexion'}</span>
                    `;
                    document.body.appendChild(errorMessage);
                    
                    // Supprimer le message après 5 secondes
                    setTimeout(() => {
                        errorMessage.remove();
                    }, 5000);
                    
                    // Réactiver le bouton
                    this.disabled = false;
                    this.classList.remove('button-loading');
                }
            });
        }

        // Fermer le menu mobile en cliquant à l'extérieur
        document.addEventListener('click', function(event) {
            if (mobileMenuButton && navLinks && isMobileMenuOpen) {
                const clickedInsideNav = navLinks.contains(event.target) || mobileMenuButton.contains(event.target);
                
                if (!clickedInsideNav) {
                    isMobileMenuOpen = false;
                    navLinks.classList.remove('mobile-menu-open');
                    mobileMenuButton.setAttribute('aria-expanded', 'false');
                    html.style.overflow = '';
                }
            }
            
            // Fermer le menu déroulant si on clique en dehors
            if (dropdownToggle && dropdownToggle.getAttribute('aria-expanded') === 'true') {
                const subnav = document.querySelector('.subnav');
                const dropdownMenu = document.querySelector('.dropdown-menu');
                const target = event.target;
                
                // Vérifier si on a cliqué en dehors du menu déroulant et de son déclencheur
                if ((!dropdownMenu || !dropdownMenu.contains(target)) && 
                    !dropdownToggle.contains(target) && 
                    (!subnav || !subnav.contains(target))) {
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    if (dropdownMenu) dropdownMenu.classList.remove('show');
                    if (subnav) subnav.classList.remove('show');
                }
            }
        });
        
        // Gestion du redimensionnement de la fenêtre
        let resizeTimer;
        
        function updateNavTexts() {
            const navLinkSpans = document.querySelectorAll('.nav-link:not(.user-menu .nav-link) span');
            const logoText = document.querySelector('.logo span');
            
            if (window.innerWidth < 1400) {
                navLinkSpans.forEach(span => {
                    span.style.display = 'none';
                });
                if (logoText) logoText.style.display = 'none';
            } else {
                navLinkSpans.forEach(span => {
                    span.style.display = 'inline';
                });
                if (logoText) logoText.style.display = 'inline';
            }
        }
        
        function handleResize() {
            // Réinitialiser le style overflow du body
            html.style.overflow = '';
            
            // Fermer le menu mobile sur grand écran
            if (window.innerWidth >= 1024) {
                if (navLinks) navLinks.classList.remove('mobile-menu-open');
                if (mobileMenuButton) mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
            
            // Gérer l'affichage des textes dans la nav
            updateNavTexts();
        }
        
        // Détecter le redimensionnement avec debounce
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(handleResize, 100);
        });
        
        // Initialiser l'état du menu
        handleResize();
    });
    </script>

    <main class="main-content">
        <div class="container">
            <!-- Contenu de la page -->
        </div>
    </main>
</body>

</html>