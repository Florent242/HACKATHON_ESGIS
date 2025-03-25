<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
<script defer src="/HACKATHON_ESGIS/public/js/lucide.js"></script>
<script defer src="/HACKATHON_ESGIS/public/js/user/header.js"></script>
<header>
    <div class="header-container">
        <div class="logo-nav">
            <div class="logo">
                <div class="logo-circle">E</div>
                <span>EsgisHub</span>
            </div>
            <a href="/HACKATHON_ESGIS/public/user">Dashboard</a>
            <div class="nav-container">
                <nav class="main-nav">
                    <!-- verifie et attribut la classe active au lien correspondant -->
                    <li data-item="0">Événements <i data-lucide="chevron-down"></i></li>
                    <li data-item="1">Communauté <i data-lucide="chevron-down"></i></li>
                    <li data-item="2">Resources <i data-lucide="chevron-down"></i></li>
                </nav>
                <nav class="header-dropdown">
                    <div class="dropdown-container">
                        <div class="dropdown">
                            <ul class="dropdown-item" data-item="0">
                                <a href="/HACKATHON_ESGIS/public/user/challenges">
                                    <li>
                                        Challenges
                                    </li>
                                </a>
                                <a href="/HACKATHON_ESGIS/public/user/hackathon">
                                    <li>
                                        Hackathons
                                    </li>
                                </a>
                                <a href="/HACKATHON_ESGIS/public/user/workshop">
                                    <li>
                                        Workshop
                                    </li>
                                </a>
                            </ul>
                            <ul class="dropdown-item" data-item="1">
                                <a href="/HACKATHON_ESGIS/public/user/teams">
                                    <li>
                                        Teams
                                    </li>
                                </a>
                                <a href="/HACKATHON_ESGIS/public/user/mentors">
                                    <li>
                                        Mentors
                                    </li>
                                </a>
                                <a href="/HACKATHON_ESGIS/public/user/leaderboard">
                                    <li>
                                        Leaderboard
                                    </li>
                                </a>
                            </ul>
                            <ul class="dropdown-item" data-item="2">
                                <a href="/HACKATHON_ESGIS/public/user/documentation">
                                    <li>
                                        Documentation
                                    </li>
                                </a>
                                <a href="/HACKATHON_ESGIS/public/user/blog">
                                    <li>
                                        Blog
                                    </li>
                                </a>
                                <a href="/HACKATHON_ESGIS/public/user/faq">
                                    <li>
                                        FAQ
                                    </li>
                                </a>
                            </ul>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="header-actions">
            <div class="notification-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
            <div class="relative group"> <!-- Ajoutez "group" ici -->
                <!-- Bouton du profil -->
                <div class="profile-btn cursor-pointer">
                    <i data-lucide="circle-user"></i>
                </div>

                <!-- Dropdown menu -->
                <div class="profile-dropdown opacity-0 invisible absolute top-full right-0 card-bg border border-gray-300 rounded-lg w-48 overflow-hidden mt-2 transition-all duration-300 ease-in-out group-hover:opacity-100 group-hover:visible">
                    <div class="border-b border-gray-300 p-3">
                        <span class="text-white">Mon Compte</span>
                    </div>
                    <div class="flex flex-col gap-2 p-2 border-b border-gray-300">
                        <a href="/HACKATHON_ESGIS/public/user/profile">
                            <li class="flex items-center gap-2 p-1 rounded-lg text-white hover:text-blue-500 hover:bg-slate-900">
                                <i data-lucide="circle-user" class="w-4 h-4 stroke-current"></i>
                                Mon espace
                            </li>
                        </a>
                        <a href="/HACKATHON_ESGIS/public/user/profile">
                            <li class="flex items-center gap-2 p-1 rounded-lg text-white hover:text-blue-500 hover:bg-slate-900">
                                <i data-lucide="trophy" class="w-4 h-4 stroke-current"></i>
                                Mes défis
                            </li>
                        </a>
                        <a href="/HACKATHON_ESGIS/public/user/profile">
                            <li class="flex items-center gap-2 p-1 rounded-lg text-white hover:text-blue-500 hover:bg-slate-900">
                                <i data-lucide="settings" class="w-4 h-4 stroke-current"></i>
                                Paramètres
                            </li>
                        </a>
                    </div>
                    <div class="p-2">
                        <a href="/HACKATHON_ESGIS/public/user/logout">
                            <li class="flex items-center gap-2 p-1 rounded-lg text-red-500 hover:bg-slate-900">
                                <i data-lucide="log-out" class="w-4 h-4 stroke-current"></i>
                                Logout
                            </li>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
</header>
<div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>