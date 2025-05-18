<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenges</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/challenge_secu.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="/HACKATHON_ESGIS/public/js/user/challenge_secu.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>
    <!-- Header -->
    <?php require_once '../includes/user/header.php'; ?>

    <!-- Main structure -->
    <section class="main-container mb-10">

        <div>

            <!-- Sidebar Filters -->
            <div class="filters-container">
                <aside class="filters-sidebar">

                    <!-- Filtre global -->
                    <div class="filter-group">
                        <h2 style="display: flex; align-items: center; gap: 0.5rem;"> <i class="w-4 h-4 stroke-current" data-lucide="filter"></i> <span>Filters</span></h2>

                        <!-- Difficulté sous forme de boutons -->
                        <h3 style="display: flex; align-items: center; gap: 0.5rem;"> <i class="w-4 h-4 stroke-current" data-lucide="gauge"></i> <span>Difficulty</span></h3>
                        <div class="filter-buttons" data-type="difficulty">
                            <button class="filter-btn" id="easy" style="background-color: var(--green); color: var(--text); border-color: var(--green);">
                                Easy
                            </button>
                            <button class="filter-btn" id="medium" style="background-color: var(--yellow); color: var(--text); border-color: var(--yellow);">
                                Medium
                            </button>
                            <button class="filter-btn" id="hard" style="background-color: var(--red); color: var(--text); border-color: var(--red);">
                                Hard
                            </button>
                            <button class="filter-btn" id="expert" style="background-color: var(--purple); color: var(--text); border-color: var(--purple);">
                                Expert
                            </button>
                        </div>

                        <!-- Catégorie en liste -->
                        <h3 style="display: flex; align-items: center; gap: 0.5rem;"> <i class="w-4 h-4 stroke-current" data-lucide="layers"></i> <span>Category</span></h3>
                        <div class="filter-buttons" data-type="category">
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="globe"></i>
                                <span>Web</span>
                            </button>
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="code"></i>
                                <span>Binary</span>
                            </button>
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="lock-keyhole"></i>
                                <span>Crypto</span>
                            </button>
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="server"></i>
                                <span>Network</span>
                            </button>
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="rotate-ccw"></i>
                                <span>Reversing</span>
                            </button>
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="eye-off"></i>
                                <span>Steganography</span>
                            </button>
                        </div>
                        <br>

                        <!-- Statut en liste -->
                        <h3 style="display: flex; align-items: center; gap: 0.5rem;"> <i class="w-4 h-4 stroke-current" data-lucide="activity"></i> <span>Status</span></h3>
                        <div class="filter-buttons" data-type="status">
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="check-circle"></i>
                                <span>Solved</span>
                            </button>
                            <button class="filter-btn">
                                <i class="w-4 h-4 stroke-current" data-lucide="x-circle"></i>
                                <span>Unsolved</span>
                            </button>
                        </div>
                        <br>

                        <!-- Bouton Clear Filters -->
                        <button class="clear-filters bg-blue-600 text-white rounded-lg p-2">
                            <i class="w-4 h-4 stroke-current" data-lucide="refresh-ccw"></i>
                            Clear Filters
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

                <button id="view-leaderboard" class=" text-sm font-medium rounded-lg p-2" onclick="window.location.href='/HACKATHON_ESGIS/public/user/leaderboard'">
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
                        <i class="w-4 h-4 stroke-current" data-lucide="search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="text" placeholder="Search challenges by name, category, or tag..." style="width: 100%; padding: 1rem 1rem 1rem 2.5rem; background: transparent; border: none; color: white; font-size: 0.875rem;">
                    </div>
                </div>
                <div class="popular-tags">
                    <span>Popular:</span>
                    <div class="tags">
                        <button class="tag">SQL Injection</button>
                        <button class="tag">XSS</button>
                        <button class="tag">Buffer Overflow</button>
                        <button class="tag">Password Cracking</button>
                    </div>
                </div>
            </div>

            <!-- Challenges section -->
            <section class="challenges-section">
                <!-- Section header with filters -->
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <div class="filters-section" style="display: flex; align-items: center; gap: 1rem;">
                        <div class="filter-btn-group" style="display: flex; gap: 0.5rem;">
                            <button class="filter-btn active">All Challenges</button>
                        </div>
                    </div>
                    <div class="sort-filter" style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="color: var(--text-secondary); font-size: 0.875rem;">Sort By</span>
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
                <div id="challenges-empty-state" class="w-full py-4 hidden items-center justify-center flex-col">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-800/30 mb-4">
                        <i data-lucide="file-text" class="w-6 h-6 stroke-current"></i>
                    </div>
                    <h3 class="text-lg font-medium text-white mb-1">Aucun challenge trouvé</h3>
                    <p class="text-gray-500 text-center">Aucun challenge disponible pour l'instant.</p>
                </div>

            </section>
        </div>
    </section>





    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/50 z-40 backdrop-blur-sm hidden" id="challenge-modal">

        <!-- Modal -->
        <div class="fixed z-50 inset-0 flex items-center justify-center px-4 w-fit mx-auto">
            <div id="modal-content" class="bg-[#0f172a] text-white w-full max-w-3xl rounded-xl shadow-xl p-6 space-y-6 border border-white/10">

                <!-- Header -->
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-lg font-semibold">Details Du Challenge</h2>
                        <p class="text-sm text-gray-400">Resolvez le challenge pour gagner des points et améliorer vos compétences en hacking !</p>
                    </div>
                    <button class="text-gray-400 hover:text-white" id="close-modal">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Challenge info -->
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-1 text-xs rounded-full" id="challenge-difficulty"></span>
                        <span class="px-2 py-1 text-xs bg-slate-700/40 text-gray-300 rounded-full" id="challenge-category"></span>
                    </div>
                    <h3 class="text-2xl font-bold" id="challenge-title"></h3>
                    <div class="flex items-center text-sm text-gray-400 gap-4 mt-1">
                        <span class="flex items-center"><i data-lucide="clock" class="inline w-4 h-4 mr-1"></i><span id="challenge-time"></span></span>
                        <span class="flex items-center"><i data-lucide="users" class="inline w-4 h-4 mr-1"></i><span id="challenge-hackers"></span></span>
                        <span class="flex items-center"><i data-lucide="user" class="inline w-4 h-4 mr-1"></i><span id="challenge-author">#</span></span>
                        <span class="ml-auto text-yellow-400 font-semibold flex items-center"><i data-lucide="trophy" class="inline w-4 h-4 mr-1"></i><span id="challenge-points"></span>pts</span>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <h4 class="font-semibold text-base mb-1">Description</h4>
                    <div class="bg-slate-800/50 p-3 rounded-lg text-sm text-gray-300 border border-white/10" id="challenge-description">

                    </div>
                    <div class="flex flex-wrap gap-2 mt-3" id="challenge-tags">
                        <span class="bg-slate-700/50 text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700" id="challenge-tag-1">SQLi</span>
                        <span class="bg-slate-700/50 text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700" id="challenge-tag-2">Authentication Bypass</span>
                        <span class="bg-slate-700/50 text-xs flex items-center justify-center px-2 py-1 rounded-full border border-white/10 hover:bg-slate-700" id="challenge-tag-3">Databases</span>
                    </div>
                </div>

                <!-- Resources -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-base">Resources</h4>
                    <div class="flex gap-3 w-full justify-between">
                        <button class="flex items-center gap-2 w-1/2 border border-white/10 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-md text-sm">
                            <i data-lucide="download" class="w-4 h-4"></i> Download files
                        </button>
                        <button class="flex items-center gap-2 w-1/2 border border-white/10 px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-md text-sm">
                            <i data-lucide="play-circle" class="w-4 h-4"></i> Launch instance
                        </button>
                    </div>
                </div>

                <!-- Hint -->
                <div class="bg-slate-800/50 p-4 rounded-lg text-sm text-gray-300 flex flex-col items-start gap-2 border border-yellow-500/30">
                    <div class="flex items-center gap-2"><i data-lucide="sparkles" class="w-5 h-5 text-yellow-300 mt-0.5"></i>Indice :</div>
                    <p><strong class="text-gray-300" id="challenge-hint"></strong></p>
                </div>

                <!-- Submit flag -->
                <div>
                    <h4 class="font-semibold mb-1 flex items-center gap-2 text-blue-400">
                        <i data-lucide="flag" class="w-5 h-5"></i> Soumettre Flag
                    </h4>
                    <div class="flex gap-2">
                        <input type="text" placeholder="EsgisHub{. . .}" class="flex-1 px-4 py-2 rounded-md bg-slate-900 text-sm text-white placeholder-gray-400 focus:outline-none border border-slate-700" />
                        <button class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm">
                            <i data-lucide="send" class="inline w-4 h-4 mr-1"></i> Soumettre Flag
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>



</body>

</html>