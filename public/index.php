<?php
// Début de la logique d'inclusion simple

// Récupérer l'URL demandée (par exemple /home ou /about)
$url = $_SERVER['REQUEST_URI'] ?? "/HACKATHON_ESGIS/public/";

// Vérifier l'URL et inclure le fichier correspondant
switch ($url) {
    case '/HACKATHON_ESGIS/public/':
        include '../frontend/home.php';  // Inclure la page d'accueil
        break;
    case '/HACKATHON_ESGIS/public/challenges': 
        include '../frontend/challenge.php';  // Inclure la page "Challenge"
        break;
    case '/HACKATHON_ESGIS/public/hackathon':
        include '../frontend/hackathon.php'; // Inclure la page "Hackaton"
        break;
    case '/HACKATHON_ESGIS/public/resources':
        include '../frontend/resources.php'; // Inclure la page "Ressources"
        break;
    case '/HACKATHON_ESGIS/public/leaderboard':
        include '../frontend/leaderboard.php'; // Inclure la page "Leaderboard"
        break;
    case '/HACKATHON_ESGIS/public/signup':
        include '../frontend/signup.php'; // Inclure la page "Signup"
        break;
    case '/HACKATHON_ESGIS/public/signin': 
        include '../frontend/signin.php'; // Inclure la page "Connexion"
        break;
    case '/HACKATHON_ESGIS/public/profile':
        include '../frontend/profile.php'; // Inclure la page "Profil"
        break;
    default:
        include '../frontend/404.php'; // Inclure la page 404 si rien ne correspond
        break;
}
// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas
?>
