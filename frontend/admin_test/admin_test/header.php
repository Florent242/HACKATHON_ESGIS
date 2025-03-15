<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>../frontend/admin_test/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="main-header">
            <?php if(isLoggedIn()): ?>
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?= BASE_URL ?>/dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                        <li><a href="<?= BASE_URL ?>/hackathons" class="<?= $currentPage === 'hackathons' ? 'active' : '' ?>">Hackathons</a></li>
                        <li><a href="<?= BASE_URL ?>/challenges" class="<?= $currentPage === 'challenges' ? 'active' : '' ?>">Challenges</a></li>
                        <li><a href="<?= BASE_URL ?>/utilisateurs" class="<?= $currentPage === 'utilisateurs' ? 'active' : '' ?>">Utilisateurs</a></li>
                        <li><a href="<?= BASE_URL ?>/equipes" class="<?= $currentPage === 'equipes' ? 'active' : '' ?>">Équipes</a></li>
                        <li><a href="<?= BASE_URL ?>/ressources" class="<?= $currentPage === 'ressources' ? 'active' : '' ?>">Ressources</a></li>
                        <li><a href="<?= BASE_URL ?>/logs" class="<?= $currentPage === 'logs' ? 'active' : '' ?>">Logs</a></li>
                        <li><a href="<?= BASE_URL ?>/soumissions" class="<?= $currentPage === 'soumissions' ? 'active' : '' ?>">Soumissions</a></li>
                    </ul>
                </nav>
            <?php endif; ?>
        </header>
        <main class="content">
            <?= flash() ?>