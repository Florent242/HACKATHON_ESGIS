<?php
// Début de la logique d'inclusion simple

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
    case '/HACKATHON_ESGIS/public/signup':
        require_once '../frontend/signup.php'; // Inclure la page "Signup"
        break;
    case '/HACKATHON_ESGIS/public/signin': 
        require_once '../frontend/signin.php'; // Inclure la page "Connexion"
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

    // Page user
    case '/HACKATHON_ESGIS/public/user':
        require_once '../frontend/user/home.php'; // Inclure la page "User"
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
    case '/HACKATHON_ESGIS/public/user/profile':
        require_once '../frontend/user/profile.php'; // Inclure la page "Admin"
        break;

    default:
        require_once '../frontend/404.php'; // Inclure la page 404 si rien ne correspond
        break;
}
// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas
?>
</body>
</html>
