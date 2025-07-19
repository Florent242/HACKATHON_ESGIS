<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
<head>
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <link rel="shortcut icon" href="/assets/20ans-gold.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<script>
    // Rendre le token CSRF disponible globalement pour JavaScript
    window.csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
</script>
<script defer type="module" src="/js/user/header.js"></script>
<!-- Modal -->
<div id="fenetre_modal" class="mymodal">
    <div class="modal_content">
        <!-- Header avec icône d'alerte -->
        <div class="modal_header">
            <button class="close_btn" id="close_header_btn">
                <i data-lucide="x"></i>
            </button>
            <div class="modal_icon">
                <i data-lucide="alert-triangle"></i>
            </div>
            <h3 class="modal_title">Confirmer la déconnexion</h3>
            <p class="modal_subtitle">Cette action va terminer votre session</p>
        </div>

        <!-- Body -->
        <div class="modal_body">
            <p class="modal_message">
                Êtes-vous sûr de vouloir vous déconnecter ?
                Vous devrez vous reconnecter pour accéder à votre compte.
            </p>

            <!-- Actions -->
            <div class="modal_actions">
                <button id="fermer_modal" onclick="hideModal()">
                    <i data-lucide="x"></i>
                    <span>Annuler</span>
                </button>
                <button id="logout-btn" onclick="handleLogout()">
                    <i data-lucide="log-out"></i>
                    <span id="logout-text">Se déconnecter</span>
                </button>
            </div>
        </div>
    </div>
</div>

<header>
    <!-- Navigation mobile -->
    <div class="mobile-nav-overlay"></div>

    <!-- Navigation mobile (modifiée pour le style modal) -->
    <div class="mobile-nav">
        <div class="mobile-nav-header">
            <div class="logo">
                <div class="logo-circle">
                    <img src="/assets/20ans-gold.png" alt="Logo EsgisHub" class="logo-img">
                </div>
                <span>EsgisHub</span>
            </div>
            <button class="close-mobile-nav">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="mobile-nav-items">
            <div class="mobile-nav-category">
                <div class="mobile-nav-category-header" data-category="0">
                    <span>Événements</span>
                    <i data-lucide="chevron-down"></i>
                </div>
                <div class="mobile-nav-category-content" data-category="0">
                    <a href="/user/challenge_security" class="mobile-nav-link">
                        Challenges de sécurité
                    </a>
                    <a href="/user/challenge_dev" class="mobile-nav-link">
                        Challenges de développement
                    </a>
                    <a href="/user/hackathon" class="mobile-nav-link">
                        Hackathons
                    </a>
                </div>
            </div>

            <div class="mobile-nav-category">
                <div class="mobile-nav-category-header" data-category="1">
                    <span>Communauté</span>
                    <i data-lucide="chevron-down"></i>
                </div>
                <div class="mobile-nav-category-content" data-category="1">
                    <a href="/user/teams" class="mobile-nav-link">
                        Teams
                    </a>
                    <a href="/user/leaderboard" class="mobile-nav-link">
                        Leaderboard
                    </a>
                </div>
            </div>

            <div class="mobile-nav-category">
                <div class="mobile-nav-category-header" data-category="2">
                    <span>Ressources</span>
                    <i data-lucide="chevron-down"></i>
                </div>
                <div class="mobile-nav-category-content" data-category="2">
                    <a href="/user/documentation" class="mobile-nav-link">
                        Documentation
                    </a>
                    <a href="/user/faq" class="mobile-nav-link">
                        FAQ
                    </a>
                </div>
            </div>
        </div>

        <div class="mobile-nav-actions">
            <a href="/user" class="mobile-nav-action">
                <i data-lucide="layout-dashboard"></i>
                <span>Dashboard</span>
            </a>
            <a href="/user/profile" class="mobile-nav-action">
                <i data-lucide="circle-user"></i>
                <span>Mon espace</span>
            </a>
            <a href="/user/profile#settings" class="mobile-nav-action">
                <i data-lucide="settings"></i>
                <span>Paramètres</span>
            </a>
            <div class="mobile-nav-action" id="mobile-logout">
                <i data-lucide="log-out"></i>
                <span>Déconnexion</span>
            </div>
        </div>
    </div>

    <div class="header-container">
        <div class="logo-nav">
            <div class="logo">
                <div class="logo-circle">
                    <img src="/assets/20ans-gold.png" alt="Logo EsgisHub" class="logo-img">
                </div>
                <span>EsgisHub</span>
            </div>
            <a href="/user">Dashboard</a>
            <div class="nav-container">
                <nav class="main-nav">
                    <!-- verifie et attribut la classe active au lien correspondant -->
                    <li data-item="0">Événements <i data-lucide="chevron-down"></i></li>
                    <li data-item="1">Communauté <i data-lucide="chevron-down"></i></li>
                    <li data-item="2">Ressources <i data-lucide="chevron-down"></i></li>
                </nav>
                <nav class="header-dropdown">
                    <div class="dropdown-container">
                        <div class="dropdown">
                            <ul class="dropdown-item" data-item="0">
                                <a href="/user/challenge_security">
                                    <li>
                                        Challenges de sécurité
                                    </li>
                                </a>
                                <a href="/user/challenge_dev">
                                    <li>
                                        Challenges de développement
                                    </li>
                                </a>
                                <a href="/user/hackathon">
                                    <li>
                                        Hackathons
                                    </li>
                                </a>
                            </ul>
                            <ul class="dropdown-item" data-item="1">
                                <a href="/user/teams">
                                    <li>
                                        Teams
                                    </li>
                                </a>
                                <a href="/user/leaderboard">
                                    <li>
                                        Leaderboard
                                    </li>
                                </a>
                            </ul>
                            <ul class="dropdown-item" data-item="2">
                                <a href="/user/documentation">
                                    <li>
                                        Documentation
                                    </li>
                                </a>
                                <a href="/user/faq">
                                    <li>
                                        FAQ
                                    </li>
                                </a>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        <div class="header-actions">
            <div class="notification-btn">
                <i data-lucide="bell" class="stroke-current"></i>
            </div>

            <!-- Menu hamburger (pour mobile) -->
            <div class="mobile-menu-btn">
                <i data-lucide="menu" class="stroke-current"></i>
            </div>

            <div class="relative group profile-dropdown-container">
                <!-- Bouton du profil -->
                <div class="profile-btn cursor-pointer">
                    <i data-lucide="circle-user" class="stroke-current"></i>
                </div>

                <!-- Dropdown menu -->
                <div class="profile-dropdown opacity-0 invisible absolute top-full right-0 card-bg border border-gray-300 rounded-lg w-48 overflow-hidden mt-2 transition-all duration-300 ease-in-out group-hover:opacity-100 group-hover:visible">
                    <div class="border-b border-gray-300 p-3">
                        <span class="text-white">Mon Compte</span>
                    </div>
                    <div class="flex flex-col gap-2 p-2 border-b border-gray-300">
                        <a href="/user/profile">
                            <li class="flex items-center gap-2 p-1 rounded-lg text-white hover:text-blue-500 hover:bg-slate-900">
                                <i data-lucide="circle-user" class="w-4 h-4 stroke-current"></i>
                                Mon espace
                            </li>
                        </a>
                        <a href="/user/profile#settings">
                            <li class="flex items-center gap-2 p-1 rounded-lg text-white hover:text-blue-500 hover:bg-slate-900">
                                <i data-lucide="settings" class="w-4 h-4 stroke-current"></i>
                                Paramètres
                            </li>
                        </a>
                    </div>
                    <div id="deco" class="p-2 cursor-pointer">
                        <li class="flex items-center gap-2 p-1 rounded-lg text-red-500 hover:bg-slate-900">
                            <i data-lucide="log-out" class="w-4 h-4 stroke-current"></i>
                            Logout
                        </li>
                    </div>
                </div>
            </div>
        </div>
    </div>

</header>
<div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>