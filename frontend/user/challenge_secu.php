<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>EsgisHub - Challenges</title>
    <link rel="stylesheet" href="/css/styles/user/challenge_secu.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer type="module" src="/js/user/challenge_secu.js"></script>
</head>

<body>
    <!-- Header -->
    <?php require_once '../includes/user/header.php'; ?>

    <!-- Main structure -->
    <section class="main-container mb-2 w-full h-screen">

        <div>

            <!-- Sidebar Filters -->
            <div class="filters-container">
                <aside class="filters-sidebar bg-gray-900/80 backdrop-blur-sm border border-gray-800 rounded-xl p-5 shadow-lg">
                    <!-- Filtre global -->
                    <div class="filter-group space-y-6">
                        <div class="border-b border-gray-800 pb-4">
                            <h2 class="text-lg font-semibold text-white flex items-center gap-2 mb-4">
                                <i class="w-5 h-5 text-blue-400" data-lucide="filter"></i>
                                <span>Filtres</span>
                            </h2>

                            <!-- Difficulté -->
                            <div class="mb-5">
                                <h3 class="text-sm font-medium text-gray-300 mb-3 flex items-center gap-2">
                                    <i class="w-4 h-4 text-blue-400" data-lucide="gauge"></i>
                                    <span>Niveau de difficulté</span>
                                </h3>
                                <div class="filter-buttons grid grid-cols-2 gap-2" data-type="difficulty">
                                    <button class="filter-btn difficulty-filter text-sm py-1.5 px-3 rounded-lg" id="easy" data-difficulty="easy">
                                        <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                                        <span>Facile</span>
                                    </button>
                                    <button class="filter-btn difficulty-filter text-sm py-1.5 px-3 rounded-lg" id="medium" data-difficulty="medium">
                                        <span class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></span>
                                        <span>Moyen</span>
                                    </button>
                                    <button class="filter-btn difficulty-filter text-sm py-1.5 px-3 rounded-lg" id="hard" data-difficulty="hard">
                                        <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>
                                        <span>Difficile</span>
                                    </button>
                                    <button class="filter-btn difficulty-filter text-sm py-1.5 px-3 rounded-lg" id="expert" data-difficulty="expert">
                                        <span class="w-2 h-2 rounded-full bg-purple-500 mr-2"></span>
                                        <span>Expert</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Catégories -->
                            <div class="mb-5">
                                <h3 class="text-sm font-medium text-gray-300 mb-3 flex items-center gap-2">
                                    <i class="w-4 h-4 text-blue-400" data-lucide="layers"></i>
                                    <span>Catégories</span>
                                </h3>
                                <div class="filter-buttons" data-type="category">
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="web">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="globe"></i>
                                        <span>Web</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="binary">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="code"></i>
                                        <span>Binary</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="crypto">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="lock-keyhole"></i>
                                        <span>Crypto</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="network">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="server"></i>
                                        <span>Network</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="reversing">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="rotate-ccw"></i>
                                        <span>Reversing</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="osint">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="eye"></i>
                                        <span>OSINT</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="forensics">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="terminal"></i>
                                        <span>Forensics</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2" data-category="pwn">
                                        <i class="w-4 h-4 text-blue-400" data-lucide="shield"></i>
                                        <span>PWN</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Statut -->
                            <div class="mb-5">
                                <h3 class="text-sm font-medium text-gray-300 mb-3 flex items-center gap-2">
                                    <i class="w-4 h-4 text-blue-400" data-lucide="activity"></i>
                                    <span>Statut</span>
                                </h3>
                                <div class="filter-buttons" data-type="status">
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2">
                                        <i class="w-4 h-4 text-green-400" data-lucide="check-circle"></i>
                                        <span>Résolu</span>
                                    </button>
                                    <button class="filter-btn w-full text-left px-3 py-2 rounded-lg hover:bg-gray-800/50 transition-colors flex items-center gap-2">
                                        <i class="w-4 h-4 text-red-400" data-lucide="x-circle"></i>
                                        <span>Non résolu</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton Clear Filters -->
                        <button class="clear-filters w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                            <i class="w-4 h-4" data-lucide="refresh-ccw"></i>
                            <span>Réinitialiser les filtres</span>
                        </button>
                    </div>
                </aside>
            </div>


            <aside class="filters-sidebar flex flex-col gap-5">
                <h3 class="flex items-center justify-center gap-0.5 text-lg font-bold">
                    <i class="w-4 h-4 stroke-current" data-lucide="users"></i> <span>Top Hackers</span>
                </h3>

                <ol id="top-hackers" class="flex flex-col gap-1">
                    <li>Chargement...</li> <!-- Contenu remplacé dynamiquement -->
                </ol>

                <div id="hacker-list-empty-state" class="py-4 hidden items-center justify-center flex-col">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-4">
                        <i data-lucide="user-x" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <h3 class="text-base font-medium text-white mb-1">Aucun hacker trouvé</h3>
                    <p class="text-gray-500 text-center">Aucun hacker en tête de liste pour l'instant.</p>
                </div>

                <button id="view-leaderboard" class=" text-sm font-medium rounded-lg p-2" onclick="window.location.href='/user/leaderboard'">
                    View Full Leaderboard
                </button>

            </aside>


        </div>

        <!-- Main content -->
        <div class="challenges-main">
            <!-- Search and filters -->
            <div class="search-container" style="background: var(--card-bg); border-radius: 1rem; padding: 1rem; margin-bottom: 1.5rem;">
                <div class="search-bar" style="width: 100%;">
                    <div class="search-input-wrapper">
                        <i class="w-4 h-4 stroke-current" data-lucide="search"></i>
                        <input type="text" placeholder="Search challenges by name, category, or tag..." id="search-input">
                    </div>
                </div>
                <div class="popular-tags">
                    <span class="bg-slate-700/50 self-center text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700 text-sm font-medium relative text-gray-300 whitespace-nowrap max-sm:self-start">Popular :</span>
                    <div class="tags flex flex-wrap gap-2 max-md:text-xs max-md:font-medium max-sm:font-semibold max-sm:text-xs">
                        <button class="tag max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">SQL Injection</button>
                        <button class="tag max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">XSS</button>
                        <button class="tag max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">Buffer Overflow</button>
                        <button class="tag max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">Password Cracking</button>
                    </div>
                </div>
            </div>

            <!-- Challenges section -->
            <section class="challenges-section">
                <!-- Section header with filters -->
                <div class="section-header">
                    <div class="filters-section">
                        <div class="inline-flex overflow-hidden">
                            <button class="filter-btn px-4 py-2 text-sm font-medium text-gray-300 hover:bg-blue-700 transition-colors duration-200">
                                Tous les défis
                            </button>

                        </div>
                    </div>
                    <div class="sort-filter">
                        <span>Sort By</span>
                        <div class="sort-select relative">
                            <button class="sort-btn">
                                <span>Latest</span>
                                <i class="w-4 h-4 stroke-current" data-lucide="chevron-down" style="color: var(--text-secondary);"></i>
                            </button>
                            <div class="sort-options text-center align-middle whitespace-nowrap flex-col items-start justify-start absolute top-[100%] right-0 bg-[#0f172a] border border-white/10 rounded-lg p-2 z-5" style="display: none;">
                                <button class="sort-option active" data-direction="desc">Latest</button>
                                <button class="sort-option" data-direction="desc">Most Solved</button>
                                <button class="sort-option" data-direction="asc">Difficulty</button>
                                <button class="sort-option" data-direction="asc">Title</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Challenge grid -->
                <div class="challenge-grid grid-cols-3 grid max-lg:grid-cols-2 max-sm:grid-cols-1 gap-4">
                    <!-- Challenge card -->

                </div>

                <!-- Empty state -->
                <div id="challenges-empty-state" class="w-full py-12 hidden items-center justify-center flex-col animate-fade-in">
                    <div class="relative mx-auto flex items-center justify-center">
                        <!-- Effet de halo animé -->
                        <div class="absolute inset-0 rounded-full bg-blue-500/10 blur-xl animate-pulse-slow"></div>
                        <!-- Icône principale -->
                        <div class="relative z-10 flex items-center justify-center h-20 w-20 rounded-2xl bg-gradient-to-br from-blue-600/20 to-blue-800/30 backdrop-blur-sm border border-blue-500/20 shadow-lg shadow-blue-500/10 mb-6 animate-pulse-slow">
                            <i data-lucide="shield-question" class="w-10 h-10 text-blue-400"></i>
                        </div>
                    </div>

                    <h3 class="text-2xl font-bold text-white mb-2 text-center">Aucun challenge disponible</h3>
                    <p class="text-gray-400 text-center max-w-md mb-6 leading-relaxed">
                        Il n'y a pas encore de challenge de sécurité disponible pour le moment. Revenez bientôt pour découvrir de nouveaux défis passionnants !
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="/user/hackathon"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2 group">
                            <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-0.5"></i>
                            Voir les hackathons
                        </a>
                        <button onclick="window.location.reload()"
                            class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-gray-200 font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2 group">
                            <i data-lucide="refresh-ccw" class="w-4 h-4 transition-transform group-hover:rotate-180"></i>
                            Actualiser
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </section>





    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/50 z-40 backdrop-blur-sm hidden" id="challenge-modal">

        <!-- Modal -->
        <div id="modal-container" class="fixed z-1000 inset-0 flex items-center justify-center h-fit w-fit mx-auto my-auto max-md:mx-4 max-sm:mx-2">
            <div id="modal-content" class="bg-[#0f172a] text-white w-full max-w-3xl rounded-xl shadow-xl p-6 space-y-6 border border-white/10 max-md:text-xs max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2">

                <!-- Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">Details Du Challenge</h2>
                        <p class="text-sm text-gray-400 max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">Resolvez le challenge pour gagner des points et améliorer vos compétences en hacking !</p>
                    </div>
                    <button class="text-gray-400 hover:text-white max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1" id="close-modal">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Challenge info -->
                <div>
                    <div class="flex items-center gap-2 mb-1 max-md:text-xs max-md:font-medium max-sm:font-semibold max-sm:text-xs ">
                        <span class="px-2 py-1 text-xs rounded-full max-md:text-xs max-md:font-medium max-sm:font-semibold max-sm:text-xs " id="challenge-difficulty"></span>
                        <span class="px-2 py-1 text-xs bg-slate-700/40 text-gray-300 rounded-full max-md:text-xs max-md:font-medium max-sm:font-semibold max-sm:text-xs " id="challenge-category"></span>
                    </div>
                    <h3 class="text-2xl font-bold max-md:text-xl max-md:font-semibold max-sm:text-lg" id="challenge-title"></h3>
                    <div class="flex items-center text-sm text-gray-400 gap-4 max-md:gap-2 max-sm:gap-1 mt-1 max-md:text-xs max-md:flex-wrap max-sm:font-normal max-sm:text-xs ">
                        <span class="flex items-center max-md:text-xs max-md:font-medium max-sm:font-normal max-sm:text-xs "><i data-lucide="clock" class="stroke-current inline w-4 h-4 mr-1"></i><span id="challenge-time"></span></span>
                        <span class="flex items-center max-md:text-xs max-md:font-medium max-sm:font-normal max-sm:text-xs "><i data-lucide="users" class="stroke-current inline w-4 h-4 mr-1"></i><span id="challenge-hackers"></span></span>
                        <span class="flex items-center max-md:text-xs max-md:font-medium max-sm:font-normal max-sm:text-xs "><i data-lucide="user" class="stroke-current inline w-4 h-4 mr-1"></i><span id="challenge-author">#</span></span>
                        <span class="ml-auto max-md:ml-0 text-yellow-400 font-semibold flex items-center max-md:text-xs max-md:font-medium max-sm:font-normal max-sm:text-xs "><i data-lucide="trophy" class="stroke-current inline w-4 h-4 mr-1"></i><span id="challenge-points"></span>pts</span>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h4 class="font-semibold text-base mb-1">Description</h4>
                    <div class="bg-slate-800/50 p-3 rounded-lg text-sm text-gray-300 border border-white/10 max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1" id="challenge-description">

                    </div>
                    <div class="flex flex-wrap gap-2 mt-3 max-md:mt-1 max-md:flex-col max-md:items-center max-md:w-full" id="challenge-tags">
                        <span class="bg-slate-700/50 text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700 max-md:w-full max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1" id="challenge-tag-1"></span>
                        <span class="bg-slate-700/50 text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700 max-md:w-full max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1" id="challenge-tag-2"></span>
                        <span class="bg-slate-700/50 text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700 max-md:w-full max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1" id="challenge-tag-3"></span>
                    </div>
                </div>

                <!-- Resources -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-base">Resources</h4>
                    <div class="flex gap-3 w-full justify-between max-md:justify-center max-md:flex-col max-md:items-center max-md:w-full">
                        <button id="download-files-button" class="flex items-center gap-2 w-1/2 border border-white/10 px-4 py-2 bg-slate-800 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-slate-800 rounded-md text-sm max-md:w-full">
                            <i data-lucide="download" class="w-4 h-4"></i> Download files
                        </button>
                        <span id="launch-instance-button" class="clamp-1 flex items-center gap-2 w-1/2 border border-white/10 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-md text-sm max-md:w-full">
                            <i data-lucide="play-circle" class="w-4 h-4"></i> Launch instance
                        </span>
                    </div>
                </div>

                <!-- Hint -->
                <div class="bg-slate-800/50 p-4 rounded-lg text-sm text-gray-300 flex flex-col items-start gap-2 border border-yellow-500/30 max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">
                    <div class="flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-yellow-300 mt-0.5"></i>Indice :</div>
                    <ul id="challenge-hint" class="text-gray-300">
                    </ul>
                </div>

                <!-- Submit flag -->
                <div>
                    <h4 class="font-semibold mb-1 flex items-center gap-2 text-blue-400 max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">
                        <i data-lucide="flag" class="w-5 h-5"></i> Soumettre Flag
                    </h4>
                    <form id="submit-flag-form">
                        <div class="flex gap-2 relative mb-2 items-center">
                            <div class="w-full relative rounded-md ">
                                <input type="hidden" name="challenge_id" id="challenge_id">
                                <input type="text" name="flag_value" placeholder="ESGISFLAG{. . .}" class="w-full shadow-lg shadow-indigo-300/10 px-4 py-2 rounded-md bg-slate-900 text-sm text-white placeholder-gray-400 focus:outline-none border border-slate-700 max-md:text-xs max-md:font-medium max-sm:font-semibold max-sm:text-xs" id="flag" />
                            </div>
                            <span class="error-message absolute ml-auto top-full text-red-500 text-xs mt-1 hidden" id="flagError"></span>
                            <button class="whitespace-nowrap px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm max-md:text-xs max-md:px-2 max-md:py-1 max-md:font-medium max-sm:font-semibold max-sm:text-xs max-sm:px-2 max-sm:py-1">
                                <i data-lucide="send" class="inline w-4 h-4 mr-1"></i> Soumettre Flag
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>



</body>

</html>