<!DOCTYPE html>
<script defer src="/js/lucide.min.js"></script>
<script defer src="/js/main.js"></script>
<?php
require_once __DIR__ . '/../backend/includes/authMiddleware.php';

// empecher l'affichage des erreurs serveur
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Vérifier l'authentification
AuthMiddleware::checkAuth();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo htmlspecialchars($_ENV['JWT_SECRET']);  // Sécurisé pour l'affichage HTML
// echo print_r($_SESSION, true);
// Récupérer l'URL demandée (par exemple /home ou /about)
$url = $_SERVER['REQUEST_URI'] ?? "/";
$url = parse_url($url, PHP_URL_PATH);

// Gestion du téléchargement sécurisé
if (preg_match('#^/download/([\w\-\.]+)$#', $url, $matches)) {
    $filename = basename($matches[1]); // empêche toute traversée de répertoires

    // Exemple : les fichiers sont dans /storage/challenges_resources/
    $path = __DIR__ . '/../storage/challenges_resources/' . $filename;

    if (file_exists($path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        flush();
        readfile($path);
        exit;
    } else {
        http_response_code(404);
        echo "Fichier introuvable.";
        exit;
    }
}

// Vérifier l'URL et inclure le fichier correspondant
switch ($url) {
    case '/':
        require_once '../frontend/home.php';  // Inclure la page d'accueil
        break;
    case '/hackathon':
        require_once '../frontend/hackathon.php'; // Inclure la page "Hackaton"
        break;
    case '/resources':
        require_once '../frontend/resources.php'; // Inclure la page "Ressources"
        break;
    case '/auth':
        require_once '../frontend/auth.php'; // Inclure la page "auth"
        break;
    case '/contact':
        require_once '../frontend/contact.php'; // Inclure la page "contact"
        break;
    case '/error403':
        require_once '../frontend/error403.php'; // Inclure la page "error403"
        break;
    case '/error404':
        require_once '../frontend/error404.php'; // Inclure la page "error404"
        break;
    case '/error500':
        require_once '../frontend/error500.php'; // Inclure la page "error500"
        break;
    case '/buttonUX':
        require_once '../frontend/buttonUX.php'; // Inclure la page "faq"
        break;
    case '/typo':
        require_once '../frontend/typo.php'; // Inclure la page "typo"
        break;
    // decommenter pour faire des tests
    // case '/mes_tests':
    //     require_once '../frontend/test.php'; // Inclure la page "redis"
    //     break;

    // Page user
    case '/user':
        require_once '../frontend/user/dashboard.php'; // Inclure la page "User"
        break;
    case '/user/challenge_security':
        require_once '../frontend/user/challenge_secu.php'; // Inclure la page "user/challenges"
        break;
    case '/user/challenge_dev':
        require_once '../frontend/user/challenge_dev.php'; // Inclure la page "user/challenges"
        break;
    case '/user/hackathon':
        require_once '../frontend/user/hackathon.php'; // Inclure la page "user/hacka"
        break;
    case '/user/leaderboard':
        require_once '../frontend/user/leaderboard.php'; // Inclure la page "Admin"
        break;
    case '/user/faq':
        require_once '../frontend/user/faq.php'; // Inclure la page "Ressources"
        break;
    case '/user/documentation':
        require_once '../frontend/user/resources.php'; // Inclure la page "Ressources"
        break;
    case '/user/profile':
        require_once '../frontend/user/profile.php'; // Inclure la page "Admin"
        break;
    case '/user/teams':
        require_once '../frontend/user/team.php'; // Inclure la page "team"
        break;
    default:
        // TODO: ajouter la gestion des urls avec le format CHALL-[A-Za-z0-9]{8,}
        //  if (preg_match('#^/user/challenge_submission/(CHALL-[A-Za-z0-9]{8,})$#', $url, $matches)) {
        //     $challenge_id = $matches[1]; // Format: CHALL-XXXXXXXX où X est alphanumérique
        //     require_once '../frontend/user/challenge_submission.php';

        if (preg_match('#^/user/challenge_submission/(\d+)$#', $url, $matches)) {
            $_GET['challenge_id'] = $matches[1];
            require_once '../frontend/user/challenge_submission.php';
        } else if (preg_match('#^/user/interfacechallenges/(\d+)$#', $url, $matches)) {
            $_GET['challenge_id'] = $matches[1];
            require_once '../frontend/user/interfacechallenge.php';
        } else if (preg_match('#^/user/teams/overview/(\d+)$#', $url, $matches)) {
            $_GET['team_id'] = $matches[1];
            require_once '../frontend/user/overview.php';
        } else if (preg_match('#^/user/hackathon/overview/(\d+)$#', $url, $matches)) {
            $_GET['hackathon_id'] = $matches[1];
            require_once '../frontend/user/overviewHackathon.php';
        } else if (strpos($_SERVER['REQUEST_URI'], '/user') !== false) {
            require_once '../frontend/user/404.php'; // Inclure la page 404 pour les utilisateurs
        } else {
            require_once '../frontend/404.php'; // Inclure la page 404 générale si rien ne correspond
        }
        break;
}

// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas
?>

<script defer>
    window.addEventListener('DOMContentLoaded', async () => {
        lucide.createIcons();
    });
</script>