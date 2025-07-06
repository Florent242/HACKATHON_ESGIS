<?php

/**
 * Header Component - Version Améliorée
 * Gestion sécurisée des sessions, navigation active et notifications
 */

// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Génération du token CSRF si inexistant
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Configuration du header
$header_config = [
    'site_name' => 'EsgisHub',
    'logo_letter' => 'E',
    'show_notifications' => true,
    'sticky' => true
];

// Détection de la page active
$current_path = $_SERVER['REQUEST_URI'] ?? '/';
$current_path = strtok($current_path, '?'); // Retirer les paramètres GET

// Configuration de la navigation
$nav_items = [
    [
        'url' => '/hackathon',
        'label' => 'Hackathons',
        'icon' => 'trophy'
    ],
    [
        'url' => '/resources',
        'label' => 'Resources',
        'icon' => 'book'
    ],
    [
        'url' => '/contact',
        'label' => 'Contact',
        'icon' => 'phone'
    ]
];

// Fonction pour vérifier si une page est active
function isActivePage($url, $current_path)
{
    return $current_path === $url ||
        (strlen($url) > 1 && strpos($current_path, $url) === 0);
}

// Détection mobile
function isMobile()
{
    return preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'] ?? '');
}

// Génération des méta-données pour les performances
$is_mobile = isMobile();
?>

<!-- Styles CSS du header -->
<link rel="stylesheet" href="/css/styles/header.css">

<!-- Script JavaScript du header (chargé de manière différée) -->
<script defer src="/js/header.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Header principal -->
<header role="banner" <?= $header_config['sticky'] ? 'data-sticky="true"' : '' ?>>

    <!-- Menu mobile -->

    <!-- Container principal du header -->
    <div class="header-container">
        <!-- Logo et navigation -->
        <div class="logo-nav">
            <!-- Logo -->
            <a href="/" class="logo" aria-label="Retour à l'accueil - <?= $header_config['site_name'] ?>">
                <div class="logo-circle" aria-hidden="true">
                    <img src="/assets/Esgislogo.png" alt="Logo EsgisHub" class="logo-img">
                </div>
                <span><?= htmlspecialchars($header_config['site_name']) ?></span>
            </a>

            <!-- Navigation principale (desktop)-->
            <nav class="header-nav" role="navigation" aria-label="Menu de navigation principal">
                <?php foreach ($nav_items as $item): ?>
                    <a href="<?= htmlspecialchars($item['url']) ?>"
                        class="<?= isActivePage($item['url'], $current_path) ? 'active' : '' ?>"
                        <?= isActivePage($item['url'], $current_path) ? 'aria-current="page"' : '' ?>>
                        <span class="nav-icon" aria-hidden="true"><i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 stroke-current"></i></span>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- Actions du header -->
        <div class="header-actions">

            <!-- Bouton Start Challenge -->
            <button class="start-challenge"
                type="button"
                aria-label="Commencer un nouveau défi">
                <span class="btn-text">Start Challenge</span>
                <i data-lucide="arrow-right" class="w-4 h-4 stroke-current"></i>
            </button>
        </div>
        <button class="mobile-menu-btn"
            aria-label="Ouvrir le menu de navigation"
            aria-expanded="false"
            aria-controls="mobile-menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <nav class="mobile-menu"
            id="mobile-menu"
            role="navigation"
            aria-label="Menu de navigation mobile">
            <ul>
                <li>
                    <a href="/" class="<?= $current_path === '/' ? 'active' : '' ?>">
                        <span class="nav-icon"><i data-lucide="home" class="w-4 h-4 stroke-current"></i></span>
                        Accueil
                    </a>
                </li>
                <?php foreach ($nav_items as $item): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['url']) ?>"
                            class="<?= isActivePage($item['url'], $current_path) ? 'active' : '' ?>">
                            <span class="nav-icon"><i data-lucide="<?= $item['icon'] ?>" class="w-4 h-4 stroke-current"></i></span>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
    <!-- Bouton menu mobile -->
</header>

<!-- Container pour les données de notification -->
<div id="notification-data"
    data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'
    style="display: none;">
</div>

<?php
/**
 * Fonctions utilitaires pour le header
 */

// Fonction pour obtenir le titre de la page courante
function getCurrentPageTitle($current_path, $nav_items)
{
    foreach ($nav_items as $item) {
        if (isActivePage($item['url'], $current_path)) {
            return $item['label'];
        }
    }
    return 'Accueil';
}

// Fonction pour générer les métadonnées de la page
function generatePageMeta($current_path, $nav_items)
{
    $title = getCurrentPageTitle($current_path, $nav_items);
    $description = "EsgisHub - " . $title;

    echo "<meta name='description' content='" . htmlspecialchars($description) . "'>\n";
    echo "<meta property='og:title' content='" . htmlspecialchars($title) . "'>\n";
    echo "<meta property='og:description' content='" . htmlspecialchars($description) . "'>\n";
}

// Générer les métadonnées si nécessaire
if (!headers_sent()) {
    generatePageMeta($current_path, $nav_items);
}
?>