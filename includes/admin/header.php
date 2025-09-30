<div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>
<header class="header">
    <div class="container">
        <nav class="nav">

            <!-- Bouton menu mobile -->
            <button class="mobile-menu-button" aria-label="Menu" aria-expanded="false" aria-controls="nav-links">
                <i data-lucide="menu"></i>
            </button>

            <!-- Liens de navigation -->
            <div class="nav-links" id="nav-links">
                <?php
                // Utilise uniquement le chemin sans les paramètres de requête
                $currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

                $navItems = [
                    '/admin' => ['Tableau de bord', 'layout-dashboard'],
                    '/admin/hackathons' => ['Hackathons', 'laptop'],
                    '/admin/challenges' => ['Challenges', 'trophy'],
                    '/admin/utilisateurs' => ['Utilisateurs', 'users'],
                    '/admin/equipes' => ['Équipes', 'users-2'],
                    '/admin/resources' => ['Ressources', 'book-open'],
                    '/admin/logs' => ['Journaux', 'file-text'],
                    '/admin/soumissions' => ['Soumissions', 'file-code-2'],
                    '/admin/validation_projet' => ['Validation', 'check-circle'],
                    
                ];

                // Générer les éléments de navigation principaux
                foreach ($navItems as $page => $item) {
                    $activeClass = ($currentPage === $page) ? 'active' : '';
                    echo "<a href=\"$page\" class=\"nav-link $activeClass\">";
                    echo "<i data-lucide=\"{$item[1]}\" class=\"nav-icon\"></i> {$item[0]}";
                    echo "</a>";
                }
                ?>

                <!-- Menu utilisateur -->
                <div class="user-menu">
                    <button class="dropdown-toggle" type="button" id="userDropdown" aria-expanded="false">
                        <i data-lucide="user" class="nav-icon"></i>
                        <span>Mon compte</span>
                        <i data-lucide="chevron-down" class="dropdown-arrow"></i>
                    </button>

                    <ul class="dropdown-menu" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/admin/notifications">
                                <i data-lucide="bell" class="nav-icon me-2"></i> Notifications
                            </a></li>
                        <li><a class="dropdown-item" href="/admin/parametres">
                                <i data-lucide="settings" class="nav-icon me-2"></i> Paramètres
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><button id="logout-button" class="dropdown-item text-danger">
                                <i data-lucide="log-out" class="nav-icon me-2"></i> Déconnexion
                            </button></li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Script pour gérer le menu mobile et la déconnexion -->

</body>

</html>