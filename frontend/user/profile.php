<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Profil</title>
    <link rel="stylesheet" href="/css/styles/user/profil.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script defer src="/js/user/profil.js"></script>
    <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
</head>

<body class="size-fit overflow-y-scroll h-screen min-h-screen w-full">
    <div id="global-loading-spinner" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-t-transparent border-blue-500"></div>
    </div>
    <?php require_once '../includes/user/header.php'; ?>
    <!-- Container -->
    <div class="container mx-auto flex gap-6 flex-row justify-between max-md:flex-col mb-30 px-4">

        <!-- Sidebar (Profil) -->
        <aside class="w-1/3 max-md:w-full rounded-lg shadow-lg flex flex-col gap-6">
            <div class="flex flex-col items-start gap-6">
                <i data-lucide="circle-user" class="w-24 h-24 stroke-current"></i>
                <!-- <img src="https://via.placeholder.com/100" alt="Profile Picture" class="rounded-full mx-auto"> -->
                <div class="flex flex-col items-start gap-1">
                    <h3 class="text-center text-2xl font-semibold fullName">John Doe</h3>
                    <p class="max-lg:text-xs text-center text-blue-400">@<span class="Username">hackmaster</span></p>
                    <p class="max-lg:text-xs text-center text-sm text-gray-400 special_comp">Security Engineer | Bug Hunter</p>
                </div>

                <div class="flex flex-row gap-2 justify-center space-x-2">
                    <span class="bg-blue-600 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center gap-1"><i class="w-4 h-4 stroke-current" data-lucide="star"></i>Top #</span>
                    <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center gap-1"><i class="w-4 h-4 stroke-current" data-lucide="flag"></i>0 Flags</span>
                    <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center gap-1"><i class="w-4 h-4 stroke-current" data-lucide="users"></i>0 Teams</span>
                </div>

                <div class="modify-profile cursor-pointer w-full flex flex-row items-center justify-center gap-2 rounded-xl transition-all duration-300 ease-in-out text-white hover:bg-gray-700 p-1 max-md:w-full border border-gray-700">
                    <i data-lucide="user"></i> <span class="text-sm max-md:text-center text-center text-nowrap font-medium max-md:text-xs">Modify the profile</span>
                </div>
            </div>
            <!-- Informations personnelles -->
            <div class="fade-in-left">
                <div class="space-y-4 w-full flex flex-col gap-4 border border-gray-700 p-5 rounded-2xl shadow-lg card-bg">
                    <h3 class="text-lg font-semibold m-0">Personal Information</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li id="mail" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="mail"></i><span class="Email"><i data-lucide="loader-circle" class="animate-spin"></i></span></li>
                        <li id="university" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="university"></i><span class="university"><i data-lucide="loader-circle" class="animate-spin"></i></span></li>
                        <li id="study_level" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="graduation-cap"></i><span class="study_level"><i data-lucide="loader-circle" class="animate-spin"></i></span></li>
                        <li id="number" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="contact"></i><span class="number"><i data-lucide="loader-circle" class="animate-spin"></i></span></li>
                        <li id="web-security" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="shield"></i><span class="special_comp"><i data-lucide="loader-circle" class="animate-spin"></i></span></li>
                    </ul>
                </div>
            </div>
        </aside>

        <!-- Section principale avec Tabs -->
        <section class="fade-in-right flex-1 flex flex-col gap-8 rounded-lg shadow-lg">

            <!-- Tabs Navigation -->
            <div class="flex flex-row items-start gap-1 p-1 bg-gray-700 rounded-xl max-md:sticky">
                <button class="max-lg:text-sm tab-link transition-all duration-300 ease-in-out flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg bg-gray-900/75" data-tab="tab1">
                    <i class="w-4 h-4 stroke current" data-lucide="chart-no-axes-combined"></i>
                    Overview
                </button>
                <button class="max-lg:text-sm tab-link transition-all duration-300 ease-in-out flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg " data-tab="tab2">
                    <i class="w-4 h-4 stroke current" data-lucide="flag"></i>
                    Challenges
                </button>
                <button class="max-lg:text-sm tab-link transition-all duration-300 ease-in-out flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg " data-tab="tab3">
                    <i class="w-4 h-4 stroke current" data-lucide="activity-square"></i>
                    Activity
                </button>
                <button class="max-lg:text-sm tab-link transition-all duration-300 ease-in-out flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg " data-tab="tab4">
                    <i class="w-4 h-4 stroke current" data-lucide="settings"></i>
                    Settings
                </button>
            </div>

            <!-- Contenu des Tabs -->
            <div class="flex flex-col gap-4 transition-transform duration-300 ease-in-out">

                <div class="tab-content" id="tab1">
                    <div class="min-h-screen">
                        <!-- Cards Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <!-- Challenges Card -->
                            <div class="challenges-card rounded-lg p-6 transition-all duration-300 ease-in-out hover:border-blue-600 hover:shadow-md hover:shadow-blue-600/70 hover:-translate-y-1">
                                <div class="flex flex-col gap-6 justify-between items-start w-full h-full">
                                    <div class="w-full flex flex-row justify-between items-center gap-2">
                                        <p class="max-lg:text-xs text-gray-300">Challenges total résolus</p>
                                        <i data-lucide="flag" class="w-8 h-8 p-2 stroke-current bg-(--blue-opac) text-blue-600 rounded-lg"></i>
                                    </div>
                                    <div class="w-full flex flex-col gap-1 items-start">

                                        <h3 class="text-3xl font-bold text-white m-0" id="number-challenges-solved"><i data-lucide="loader-circle" class="stroke-current animate-spin w-8 h-8"></i></h3>

                                        <p class="max-lg:text-xs text-gray-400"><span id="number-hacking-challenges"></span> en cours de participation</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Points Card -->
                            <div class="points-card rounded-lg p-6 transition-all duration-300 ease-in-out hover:border-purple-600 hover:shadow-md hover:shadow-purple-600/50 hover:-translate-y-1">
                                <div class="flex flex-col gap-6 justify-between items-start w-full h-full">
                                    <div class="w-full flex flex-row justify-between items-center gap-2">
                                        <p class="max-lg:text-xs text-gray-300">Points Actuels</p>
                                        <i data-lucide="trophy" class="w-8 h-8 p-2 stroke-current bg-(--blue-opac) text-purple-600 rounded-lg"></i>
                                    </div>
                                    <div class="w-full flex flex-col gap-1 items-start">
                                        <h3 class="text-3xl font-bold text-white m-0" id="total-points"><i data-lucide="loader-circle" class="stroke-current animate-spin w-8 h-8"></i></h3>
                                        <p class="max-lg:text-xs text-green-400">+<span id="points-change-percent"></span> points derniers jours</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Ranking Card -->
                            <div class="ranking-card rounded-lg p-6 transition-all duration-300 ease-in-out hover:border-blue-600/60 hover:shadow-md hover:shadow-blue-600/40 hover:-translate-y-1">
                                <div class="flex flex-col gap-6 justify-between items-start w-full h-full">
                                    <div class="w-full flex flex-row justify-between items-center gap-2">
                                        <p class="max-lg:text-xs text-gray-300">Global Ranking</p>
                                        <i data-lucide="star" class="w-8 h-8 p-2 stroke-current bg-(--blue-opac) text-blue-600 rounded-lg"></i>
                                    </div>
                                    <div class="w-full flex flex-col gap-1 items-start">
                                        <h3 class="flex flex-row items-center text-3xl font-bold text-white m-0">
                                            #
                                            <span id="number-ranking">
                                                <i data-lucide="loader-circle" class="stroke-current animate-spin w-8 h-8"></i>
                                            </span>
                                        </h3>
                                        <p class="max-lg:text-xs text-gray-400">Top 50%</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Recent Activity Section -->
                        <div class="card-bg rounded-lg p-6 border border-gray-700">
                            <div class="flex items-center justify-start gap-2 mb-6">
                                <i data-lucide="activity" class="w-6 h-6 stroke-blue-500"></i>
                                <h3 class="text-xl font-bold text-white">Recent Activity</h3>
                            </div>

                            <!-- Activity Items -->
                            <div class="flex flex-col items-center text-center space-y-4" id="overview-activities-container">

                                <div class="flex flex-row justify-start p-4 items-center w-full bg-(--card-bg) rounded-xl gap-5 border-b border-slate-800 pb-4 transition duration-300 ease-in-out hover:-translate-y-1 recent-activity-item">
                                    <div class="flex items-center justify-between w-full">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center size-fit p-2 rounded-full mr-4">
                                                <i data-lucide="check-square" class="w-4 h-4 stroke-current activity-icon"></i>
                                            </div>
                                            <div class="flex flex-col items-start justify-between">
                                                <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal activity-text">Completed "SQL Injection Basics"</p>
                                                <p class="max-lg:text-xs max-md:text-xs max-md:font-normal activity-details">+100 points</p>
                                            </div>
                                        </div>
                                        <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal activity-time">2 days ago</p>
                                    </div>
                                </div>

                                <!-- 
                                <div class="border-b border-slate-800 pb-4 transition duration-300 ease-in-out hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center size-fit bg-blue-700/30 p-2 rounded-full mr-4">
                                                <i data-lucide="shield" class="w-4 h-4 stroke-current text-blue-500"></i>
                                            </div>
                                            <div>
                                                <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Started "XSS Attack Simulation"</p>
                                                <p class="max-lg:text-xs text-gray-400 max-md:text-xs max-md:font-normal">Challenge in progress</p>
                                            </div>
                                        </div>
                                        <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">5 days ago</p>
                                    </div>
                                </div>

                                <div class="border-b border-slate-800 pb-4 transition duration-300 ease-in-out hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center size-fit bg-purple-700/30 p-2 rounded-full mr-4">
                                                <i data-lucide="message-circle-code" class="w-4 h-4 stroke-current text-purple-500"></i>
                                            </div>
                                            <div>
                                                <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Received feedback on "Network Security"</p>
                                                <p class="max-lg:text-xs text-gray-400 max-md:text-xs max-md:font-normal">From moderator</p>
                                            </div>
                                        </div>
                                        <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">1 week ago</p>
                                    </div>
                                </div> -->
                                <div class="flex flex-col items-center text-center py-10 px-6" id="no-recent-activities">
                                    <div class="animate-fade-in mb-3">
                                        <i data-lucide="history" class="w-12 h-12 text-emerald-400/60"></i>
                                    </div>
                                    <h3 class="text-gray-200 text-lg font-medium">Aucune activité enregistrée</h3>
                                    <p class="text-gray-400 text-sm mt-1">Commencez un défi ou explorez les challenges à venir.</p>
                                </div>
                            </div>

                            <!-- View All Button -->
                            <button class="max-lg:text-sm w-full mt-4 py-3 text-center rounded-lg border border-slate-700 text-white hover:bg-slate-800 transition max-md:text-xs max-md:font-normal">
                                View All Activity
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tab-content hidden" id="tab2">
                    <div class="flex flex-1 flex-col gap-6 rounded-lg ">
                        <!-- Tabs Navigation -->
                        <div class="size-fit flex flex-row items-start gap-1 p-1 bg-gray-400/50 rounded-xl max-md:sticky">

                            <button class="max-lg:text-sm sub-tab-link flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg bg-gray-900/75" data-sub-tab="subTab1">
                                In Progress
                            </button>
                            <button class="max-lg:text-sm sub-tab-link flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg " data-sub-tab="subTab2">
                                Completed
                            </button>
                            <button class="max-lg:text-sm sub-tab-link flex flex-row items-center gap-1 px-2 py-0.5 text-xm max-md:text-xs text-white border-transparent hover:bg-gray-900/50 rounded-lg " data-sub-tab="subTab3">
                                All Challenges
                            </button>

                        </div>

                        <!-- Contenu des subtabs -->
                        <div class="flex flex-col gap-4">
                            <div class="sub-tab-content" id="subTab1">
                                <div class="w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto" id="in-progress-challenges-container">
                                    <!-- XSS Attack Simulation Card -->
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1 in-progress-challenge-item">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center challenge-tag">web</span>
                                            <span class="rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center challenge-level">Medium</span>
                                        </div>
                                        <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal challenge-title">XSS Attack Simulation</h2>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal challenge-description">Find and exploit cross-site scripting vulnerabilities</p>
                                        <div class="flex flex-row items-center justify-between mb-2">
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal challenge-end-date">Started: Dec 12, 2023</p>
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal challenge-points">100 points</p>
                                        </div>
                                        <a href="/user/challenge_security">
                                            <button class="max-lg:text-sm bg-blue-500 text-white px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50 max-md:font-normal">Continue Challenge</button>
                                        </a>
                                    </div>

                                    <!-- Data Exfiltration Challenge Card -->
                                    <!-- 
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1" id="in-progress-challenge-item">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">Forencis</span>
                                            <span class="bg-red-500/20 text-red-500 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">Hard</span>
                                        </div>
                                        <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal">Data Exfiltration Challenge</h2>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Find hidden data exfiltration methods</p>
                                        <div class="flex flex-row items-center justify-between mb-2">
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Started: Jan 5, 2024</p>
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">200 points</p>
                                        </div>
                                        <button class="max-lg:text-sm bg-blue-500 text-white px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50 max-md:font-normal">Continue Challenge</button>
                                    </div> -->
                                    
                                </div>
                                <div
                                    id="no-in-progress-challenges"
                                    class="flex flex-col items-center justify-center text-center py-12 px-6 border border-dashed border-gray-700/50 rounded-xl bg-gray-800/40 shadow-inner">
                                    <div class="animate-bounce-slow mb-4">
                                        <i data-lucide="clock" class="w-12 h-12 text-yellow-400/80"></i>
                                    </div>
                                    <h3 class="text-white text-lg font-semibold">Aucun challenge en cours</h3>
                                    <p class="text-gray-400 text-sm mt-1">Commence un challenge pour qu’il apparaisse ici.</p>
                                </div>
                            </div>

                            <div class="sub-tab-content hidden" id="subTab2">
                                <div class="w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto" id="completed-challenges-container">
                                    <!-- SQL Injection Basics Card -->
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1 completed-challenge-item">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center challenge-tag">web</span>
                                            <span class="bg-green-500/20 text-green-500 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center challenge-level">Easy</span>
                                        </div>
                                        <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal challenge-title">SQL Injection Basics</h2>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal challenge-description">Exploit SQL injection vulnerabilities</p>
                                        <div class="flex flex-row items-center justify-between mb-2">
                                            <p class="flex flex-row items-center text-sm font-normal max-lg:text-xs text-white max-md:font-normal"><i data-lucide="trophy" class="w-4 h-4 stroke-current"></i> Completed</p>
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal challenge-points">100 points</p>
                                        </div>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal challenge-end-date">Completed on: March 15, 2023</p>
                                    </div>

                                    <!-- 
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">Cryptography</span>
                                            <span class="bg-green-500/20 text-green-500 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">Easy</span>
                                        </div>
                                        <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal">Base64 Encoding</h2>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Decode and encode base64 messages</p>
                                        <div class="flex flex-row items-center justify-between mb-2">
                                            <p class="flex flex-row items-center text-sm font-normal max-lg:text-xs text-white max-md:font-normal"><i data-lucide="trophy" class="w-4 h-4 stroke-current"></i>Completed</p>
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">100 points</p>
                                        </div>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Completed on: March 15, 2023</p>
                                    </div> -->
                                    
                                </div>
                                <div
                                    id="no-completed-challenges"
                                    class="flex-col items-center justify-center text-center py-12 px-6 border border-dashed border-gray-700/50 rounded-xl bg-gray-800/40 shadow-inner hidden">
                                    <div class="animate-bounce-slow mb-4">
                                        <i data-lucide="check-circle" class="w-12 h-12 text-emerald-400/80"></i>
                                    </div>
                                    <h3 class="text-white text-lg font-semibold">Rien de terminé… encore</h3>
                                    <p class="text-gray-400 text-sm mt-1">Complète un challenge pour le voir ici.</p>
                                </div>
                            </div>

                            <div class="sub-tab-content hidden" id="subTab3">
                                <div id="all-challenges-container">
                                    <div class="All-challenges cursor-pointer w-fit mx-auto flex flex-row items-center justify-center gap-2 rounded-full transition-all duration-300 ease-in-out text-white hover:bg-gray-700 py-2 px-4 max-md:w-full border border-gray-700 hover:-translate-y-1 all-challenge-item">
                                        <i data-lucide="link-2"></i><span class="text-sm max-md:text-center text-center text-nowrap font-medium max-md:text-xs">All challenges</span>
                                    </div>
                                </div>
                                <div id="no-all-challenges" class="flex-col items-center justify-center text-center py-12 px-6 border border-dashed border-gray-700/50 rounded-xl bg-gray-800/40 shadow-inner hidden">
                                    <div class="animate-bounce-slow mb-4">
                                        <i data-lucide="package-x" class="w-12 h-12 text-red-400/80"></i>
                                    </div>
                                    <h3 class="text-white text-lg font-semibold">Aucun challenge disponible</h3>
                                    <p class="text-gray-400 text-sm mt-1">Sois patient, de nouveaux défis arrivent très bientôt !</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu de tab3 -->
                <div class="tab-content hidden" id="tab3">
                    <!-- Activity Section -->
                    <div class="card-bg rounded-lg p-6 border border-gray-700">
                        <div class="flex items-center justify-start gap-2 mb-6">
                            <i data-lucide="activity" class="w-6 h-6 stroke-blue-500"></i>
                            <h3 class="text-xl font-bold text-white">Activities</h3>
                        </div>

                        <!-- Activity Items -->
                        <div class="space-y-4" id="recent-activities-container">
                            <!-- Completed Challenge -->
                            <div class="border-b border-slate-800 pb-4 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hidden">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center size-fit bg-green-700/30 p-2 rounded-full mr-4">
                                            <i data-lucide="check-square" class="w-4 h-4 stroke-current text-green-500"></i>
                                        </div>
                                        <div>
                                            <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Completed "SQL Injection Basics"</p>
                                            <p class="max-lg:text-xs max-md:text-xs max-md:font-normal">100 points</p>
                                        </div>
                                    </div>
                                    <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">2 days ago</p>
                                </div>
                            </div>

                            <!-- Started Challenge -->
                            <!-- 
                            <div class="border-b border-slate-800 pb-4 transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center size-fit bg-blue-700/30 p-2 rounded-full mr-4">
                                            <i data-lucide="shield" class="w-4 h-4 stroke-current text-blue-500"></i>
                                        </div>
                                        <div>
                                            <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Started "XSS Attack Simulation"</p>
                                            <p class="max-lg:text-xs text-gray-400 max-md:text-xs max-md:font-normal">Challenge in progress</p>
                                        </div>
                                    </div>
                                    <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">5 days ago</p>
                                </div>
                            </div>

                            <div class="border-b border-slate-800 pb-4 transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center size-fit bg-purple-700/30 p-2 rounded-full mr-4">
                                            <i data-lucide="message-circle-code" class="w-4 h-4 stroke-current text-purple-500"></i>
                                        </div>
                                        <div>
                                            <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Received feedback on "Network Security"</p>
                                            <p class="max-lg:text-xs text-gray-400 max-md:text-xs max-md:font-normal">From moderator</p>
                                        </div>
                                    </div>
                                    <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">1 week ago</p>
                                </div>
                            </div> -->
                            <div class="flex flex-col items-center justify-center text-center py-12 px-6 border border-dashed border-gray-700/50 rounded-xl bg-gray-800/40 shadow-inner" id="no-activities">
                                <div class="animate-bounce-slow mb-4">
                                    <i data-lucide="activity" class="w-12 h-12 text-sky-400/80"></i>
                                </div>
                                <h3 class="text-white text-lg font-semibold">Pas encore de mouvement !</h3>
                                <p class="text-gray-400 text-sm mt-1 max-w-md">
                                    Une fois que vous commencerez à interagir avec les challenges, votre activité s’affichera ici.
                                </p>
                            </div>

                        </div>

                        <!-- Load more button -->
                        <button class="max-lg:text-sm w-full mt-4 py-3 text-center rounded-lg border border-slate-700 text-white hover:bg-slate-800 transition max-md:text-xs max-md:font-normal load-more-button">
                            Load More
                        </button>
                    </div>
                </div>

                <!-- Contenu de tab4 -->
                <div class="tab-content hidden" id="tab4">
                    <div class="flex flex-col items-center justify-center gap-6 max-md:gap-4 max-w-[1400px] max-md:mx-[5%] my-1 mx-auto p-5">
                        <form class="form-card-background p-6 rounded-lg shadow-md w-full mx-auto border border-gray-700">

                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="flex items-center justify-start gap-2 mb-6">
                                <i data-lucide="user" class="w-6 h-6 stroke-blue-500"></i>
                                <h1 class="text-2xl font-bold text-white">Personal Information</h1>
                            </div>

                            <!-- Full Name -->
                            <div class="flex flex-row gap-4 mb-6 w-full">
                                <div class="w-full">
                                    <label for="fullname" class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                                    <input type="text" id="fullname" name="fullname" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                    <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="fullNameError"></span>
                                </div>
                            </div>

                            <!-- Display Name and Email -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Nom d'utilisateur</label>
                                    <input type="text" id="username" name="username" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                    <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="usernameError"></span>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" id="email" name="email"
                                        class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                </div>
                            </div>

                            <!-- School/University and Location -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label for="school" class="block text-sm font-medium text-gray-700 mb-2">School/University</label>
                                    <input type="text" id="school"
                                        name="school"
                                        value="ESGIS University" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                    <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="schoolError"></span>
                                </div>
                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                    <input type="text" id="location"
                                        name="location"
                                        value="Paris, France" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                    <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="locationError"></span>
                                </div>
                            </div>

                            <!-- Bio -->
                            <div class="mb-6">
                                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                <textarea class="input-gradient-royal block w-full rounded-md border border-gray-700/70 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" rows="4"
                                    id="bio"
                                    name="bio"></textarea>
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="bioError"></span>
                            </div>

                            <!-- Update Profile Button -->
                            <button class="bg-blue-500 w-full text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50">Update Profile</button>
                        </form>

                        <form class="form-card-background p-6 rounded-lg shadow-md w-full mx-auto border border-gray-700">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="flex items-center justify-start gap-2 mb-6">
                                <i data-lucide="settings" class="w-6 h-6 stroke-blue-500"></i>
                                <h1 class="text-2xl font-bold text-white">Account Settings</h1>
                            </div>

                            <!-- Current Password -->
                            <div class="mb-6">
                                <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                                <input type="password" id="current_password"
                                    name="currentPassword"
                                    class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="currentPasswordError"></span>
                            </div>

                            <!-- New Password -->
                            <div class="mb-6">
                                <label for="new_password" class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                                <input type="password" id="new_password"
                                    name="newPassword"
                                    class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="newPasswordError"></span>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-6">
                                <label for="confirm_new_password" class="block text-sm font-medium text-gray-300 mb-2">Confirm New Password</label>
                                <input type="password" id="confirm_new_password"
                                    name="confirmPassword"
                                    class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="confirmNewPasswordError"></span>
                            </div>

                            <!-- Change Password Button -->
                            <button class="bg-blue-500 w-full text-white px-4 py-3 rounded-lg transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50 mb-6">Change Password</button>

                            <!-- Email Notifications -->
                            <!-- <div class="mb-6">
                                <h2 class="text-lg font-bold text-white mb-2">Email Notifications</h2>
                                <p class="text-sm text-gray-400">Receive emails about your account activity</p>
                            </div> -->

                        </form>
                    </div>
                </div>

        </section>

    </div>

    <!-- Footer -->
    <?php require_once '../includes/user/footer.php'; ?>
</body>

</html>