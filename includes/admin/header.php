<script defer src="/HACKATHON_ESGIS/public/js/lucide.js"></script>
<script defer src="/HACKATHON_ESGIS/public/js/admin/header.js"></script>
<script defer src="/HACKATHON_ESGIS/public/js/admin/main.js"></script>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Plateforme de Hackathon'; ?></title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/admin/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <nav class="nav">
                <?php
                // Utilise uniquement le chemin sans les paramètres de requête
                $currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                $navItems = [
                    '/HACKATHON_ESGIS/public/admin' => ['Dashboard', 'fa-solid fa-gauge-high'],
                    '/HACKATHON_ESGIS/public/admin/hackathon' => ['Hackathons', 'fa-solid fa-laptop-code'],
                    '/HACKATHON_ESGIS/public/admin/challenges' => ['Challenges', 'fa-solid fa-trophy'],
                    '/HACKATHON_ESGIS/public/admin/utilisateurs' => ['Utilisateurs', 'fa-solid fa-users'],
                    '/HACKATHON_ESGIS/public/admin/equipes' => ['Equipes', 'fa-solid fa-user-group'],
                    '/HACKATHON_ESGIS/public/admin/resources' => ['Ressources', 'fa-solid fa-book'],
                    '/HACKATHON_ESGIS/public/admin/logs' => ['Logs', 'fa-solid fa-clock-rotate-left'],
                    '/HACKATHON_ESGIS/public/admin/soumissions' => ['Soumissions', 'fa-solid fa-file-code']
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
