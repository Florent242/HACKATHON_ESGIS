<!DOCTYPE html>
<script defer src="/js/lucide.min.js"></script>
<script defer src="/js/main.js"></script>
<script defer src="/js/admin/main.js"></script>
<?php
require_once __DIR__ . '/../backend/includes/authMiddleware.php';

// Vérifier l'authentification
AuthMiddleware::checkAuth();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// echo print_r($_SESSION, true); 
// Récupérer l'URL demandée (par exemple /home ou /about)
$url = $_SERVER['REQUEST_URI'] ?? "/";

// Vérifier l'URL et inclure le fichier correspondant
switch ($url) {
    case '/':
    case '/admin':
        require_once '../frontend/admin/home.php';
        break;
    case '/admin/login':
        require_once '../frontend/auth_admin.php';
        break;
    case '/admin/hackathons':
        require_once '../frontend/admin/hackathon.php';
        break;
    case '/admin/challenges':
        require_once '../frontend/admin/challenges.php';
        break;
    case '/admin/challenges/create':
        require_once '../frontend/admin/challenges_create.php';
        break;
    case '/admin/challenges/view':
        require_once '../frontend/admin/challenges_view.php';
        break;
    case '/admin/utilisateurs':
        require_once '../frontend/admin/utilisateurs.php';
        break;
    case '/admin/equipes':
        require_once '../frontend/admin/equipes.php';
        break;
    case '/admin/leaderboard':
        require_once '../frontend/admin/leaderboard.php';
        break;
    case '/admin/logs':
        require_once '../frontend/admin/logs.php';
        break;
    case '/admin/soumissions':
        require_once '../frontend/admin/soumissions.php';
        break;
    case '/admin/validation_projet':
        require_once '../frontend/admin/validation.php';
        break;
    case '/admin/evaluations' :
        require_once '../frontend/admin/evaluation.php';
    // Ajoute ici d'autres routes admin si besoin
    default:
        // Gestion des routes dynamiques pour l'édition des challenges
        if (preg_match('#^/admin/challenges/edit/(\d+)$#', $url, $matches)) {
            $_GET['challenge_id'] = $matches[1];
            require_once '../frontend/admin/challenges_edit.php';
            break;
        }
        if (preg_match('#^/admin/challenges/view/(\d+)$#', $url, $matches)) {
            $_GET['challenge_id'] = $matches[1];
            require_once '../frontend/admin/challenges_view.php';
            break;
        }
        if (preg_match('#^/admin/hackathon-details/(\d+)$#', $url, $matches)) {
            $_GET['hackathon_id'] = $matches[1];
            require_once '../frontend/admin/hackathon-details.php';
            break;
        }
        require_once '../frontend/admin/404.php';
        break;
}

// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas
?>

<script defer>
    window.addEventListener('DOMContentLoaded', async () => {
        lucide.createIcons();
    });
</script>