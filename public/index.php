<?php
// Récupérer l'URL demandée (par exemple /home ou /about)
$url = $_SERVER['REQUEST_URI'] ?? "/HACKATHON_ESGIS/public/";

// Vérifier l'URL et inclure le fichier correspondant
switch ($url) {
    case '/HACKATHON_ESGIS/public/':
        require_once '../frontend/home.php';  // Inclure la page d'accueil
        break;
    case '/HACKATHON_ESGIS/public/challenges':
        require_once '../frontend/challenge.php';  // Inclure la page "Challenge"
        break;
    case '/HACKATHON_ESGIS/public/hackathon':
        require_once '../frontend/hackathon.php'; // Inclure la page "Hackaton"
        break;
    case '/HACKATHON_ESGIS/public/resources':
        require_once '../frontend/resources.php'; // Inclure la page "Ressources"
        break;
    case '/HACKATHON_ESGIS/public/leaderboard':
        require_once '../frontend/leaderboard.php'; // Inclure la page "Leaderboard"
        break;
    case '/HACKATHON_ESGIS/public/auth':
        require_once '../frontend/auth.php'; // Inclure la page "auth"
        break;
    case '/HACKATHON_ESGIS/public/auth_admin':
        require_once '../frontend/auth_admin.php'; // Inclure la page "logout"
        break;
    case '/HACKATHON_ESGIS/public/profile':
        require_once '../frontend/profile.php'; // Inclure la page "Profil"
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

    // Page user
    case '/HACKATHON_ESGIS/public/user':
        require_once '../frontend/user/dashboard.php'; // Inclure la page "User"
        break;
    case '/HACKATHON_ESGIS/public/user/challenges':
        require_once '../frontend/user/challenge.php'; // Inclure la page "user/challenges"
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
