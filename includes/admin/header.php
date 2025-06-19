<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
<script defer src="/js/lucide.js"></script>
<script defer src="/js/admin/header.js"></script>
<script defer src="/js/admin/main.js"></script>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Plateforme de Hackathon'; ?></title>
    <link rel="stylesheet" href="/css/styles/admin/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <?php
                // Utilise uniquement le chemin sans les paramètres de requête
                $currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                $navItems = [
                    '/admin' => ['Dashboard', 'fa-solid fa-gauge-high'],
                    '/admin/hackathon' => ['Hackathons', 'fa-solid fa-laptop-code'],
                    '/admin/challenges' => ['Challenges', 'fa-solid fa-trophy'],
                    '/admin/utilisateurs' => ['Utilisateurs', 'fa-solid fa-users'],
                    '/admin/equipes' => ['Equipes', 'fa-solid fa-user-group'],
                    '/admin/resources' => ['Ressources', 'fa-solid fa-book'],
                    '/admin/logs' => ['Logs', 'fa-solid fa-clock-rotate-left'],
                    '/admin/soumissions' => ['Soumissions', 'fa-solid fa-file-code']
                ];

                // Générer les éléments de navigation
                foreach ($navItems as $page => $item) {
                    // Compare le chemin actuel avec l'élément de navigation pour ajouter la classe active
                    $activeClass = ($currentPage === $page) ? 'active' : '';
                    echo "<a href=\"$page\" class=\"nav-link $activeClass\"><i class=\"{$item[1]} nav-icon\"></i> {$item[0]}</a>";
                }
                ?>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Contenu de la page -->
        </div>
    </main>
</body>

</html>