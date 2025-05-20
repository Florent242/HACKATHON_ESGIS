<script defer src="/HACKATHON_ESGIS/public/js/lucide.min.js"></script>
<script defer src="/HACKATHON_ESGIS/public/js/main.js"></script>
<?php
require_once __DIR__ . '/../backend/includes/authMiddleware.php';

// Vérifier l'authentification
AuthMiddleware::checkAuth();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// echo print_r($_SESSION, true); 
// Récupérer l'URL demandée (par exemple /home ou /about)
$url = $_SERVER['REQUEST_URI'] ?? "/HACKATHON_ESGIS/public/";

// Vérifier l'URL et inclure le fichier correspondant
switch ($url) {
    case '/HACKATHON_ESGIS/public/':
        require_once '../frontend/home.php';  // Inclure la page d'accueil
        break;
    case '/HACKATHON_ESGIS/public/hackathon':
        require_once '../frontend/hackathon.php'; // Inclure la page "Hackaton"
        break;
    case '/HACKATHON_ESGIS/public/resources':
        require_once '../frontend/resources.php'; // Inclure la page "Ressources"
        break;

    case '/HACKATHON_ESGIS/public/auth':
        require_once '../frontend/auth.php'; // Inclure la page "auth"
        break;
    case '/HACKATHON_ESGIS/public/contact':
        require_once '../frontend/contact.php'; // Inclure la page "contact"
        break;
    case '/HACKATHON_ESGIS/public/sponsors':
        require_once '../frontend/sponsors.php'; // Inclure la page "sponsors"
        break;

    // Page admin
    case '/HACKATHON_ESGIS/public/admin':
        require_once '../frontend/admin/home.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/challenges':
        require_once '../frontend/admin/challenge.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/hackathon':
        require_once '../frontend/admin/hackathon.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/leaderboard':
        require_once '../frontend/admin/leaderboard.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/resources':
        require_once '../frontend/admin/resources.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/utilisateurs': // Correct
        require_once '../frontend/admin/utilisateurs.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/equipes': // Correct
        require_once '../frontend/admin/equipes.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/logs': // Correct
        require_once '../frontend/admin/logs.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin/soumissions': // Correct
        require_once '../frontend/admin/soumissions.php'; // Inclure la page "Admin"
        break;



    // Page admin_test
    case '/HACKATHON_ESGIS/public/admin_test':
        require_once '../frontend/admin_test/home.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin_test/challenges':
        require_once '../frontend/admin_test/challenges.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin_test/hackathon':
        require_once '../frontend/admin_test/hackathons.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin_test/leaderboard':
        require_once '../frontend/admin_test/leaderboard.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin_test/ressources':
        require_once '../frontend/admin_test/ressources.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin_test/soumissions':
        require_once '../frontend/admin_test/soumissions.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/admin_test/utilisateurs':
        require_once '../frontend/admin_test/utilisateurs.php'; // Inclure la page "Admin"
        break;

    // Page user
    case '/HACKATHON_ESGIS/public/user':
        require_once '../frontend/user/dashboard.php'; // Inclure la page "User"
        break;
    case '/HACKATHON_ESGIS/public/user/challenge_security':
        require_once '../frontend/user/challenge_secu.php'; // Inclure la page "user/challenges"
        break;
    case '/HACKATHON_ESGIS/public/user/challenge_dev':
        require_once '../frontend/user/challenge_dev.php'; // Inclure la page "user/challenges"
        break;
    case '/HACKATHON_ESGIS/public/user/hackathon':
        require_once '../frontend/user/hackathon.php'; // Inclure la page "user/hacka"
        break;
    case '/HACKATHON_ESGIS/public/user/leaderboard':
        require_once '../frontend/user/leaderboard.php'; // Inclure la page "Admin"
        break;
    case '/HACKATHON_ESGIS/public/user/resources':
        require_once '../frontend/user/resources.php'; // Inclure la page "Ressources"
        break;
    case '/HACKATHON_ESGIS/public/user/faq':
        require_once '../frontend/user/faq.php'; // Inclure la page "Ressources"
        break;
    case '/HACKATHON_ESGIS/public/user/documentation':
        require_once '../frontend/user/resources.php'; // Inclure la page "Ressources"
        break;
    case '/HACKATHON_ESGIS/public/user/profile':
        require_once '../frontend/user/profile.php'; // Inclure la page "Admin"
        break;

    default:
        if (strpos($_SERVER['REQUEST_URI'], '/user') !== false) {
            require_once '../frontend/user/404.php'; // Inclure la page 404 pour les utilisateurs
        } else if (strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
            require_once '../frontend/admin/404.php'; // Inclure la page 404 pour les admins
        } else {
            require_once '../frontend/404.php'; // Inclure la page 404 générale si rien ne correspond
        }
        break;
}
// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas
?>

<script defer>
    window.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
    });
</script>