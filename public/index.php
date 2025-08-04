<!DOCTYPE html>
<script defer src="/js/lucide.min.js"></script>
<script defer src="/js/main.js"></script>
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
        require_once '../frontend/admin/login.php';
        break;
    case '/admin/dashboard':
        require_once '../frontend/admin/dashboard.php';
        break;
    case '/admin/users':
        require_once '../frontend/admin/users.php';
        break;
    case '/admin/settings':
        require_once '../frontend/admin/settings.php';
        break;
    case '/admin/logout':
        require_once '../frontend/admin/logout.php';
        break;
    // Ajoute ici d'autres routes admin si besoin
    default:
        require_once '../frontend/admin/error404.php';
        break;
}

// a ce niveau d'autres amelioration devront etre fait n'y toucher donc pas
?>

<script defer>
    window.addEventListener('DOMContentLoaded', async () => {
        lucide.createIcons();
    });
</script>