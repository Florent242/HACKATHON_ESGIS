<?php
// Début de la logique d'inclusion simple

// Récupérer l'URL demandée (par exemple /home ou /about)
$url = $_SERVER['REQUEST_URI'];

// Vérifier l'URL et inclure le fichier correspondant
switch ($url) {
    case '/':
        include '../frontend/home.php';  // Inclure la page d'accueil
        break;
    case '/about':
        include '../frontend/about.php'; // Inclure la page "À propos"
        break;
    case '/contact':
        include '../frontend/contact.php'; // Inclure la page de contact
        break;
    default:
        include '../frontend/404.php'; // Page 404 si la route n'est pas définie
        break;
}
?>

// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas