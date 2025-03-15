<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Profil</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/profil.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/user/profil.js"></script>
    <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
</head>

<body class="size-fit overflow-y-scroll h-screen min-h-screen w-full">
    <?php require_once '../includes/user/header.php'; ?>
    <!-- Container -->
    <div class="container mx-auto p-6 flex gap-6 flex-row justify-between max-md:flex-col mb-30">

        <!-- Sidebar (Profil) -->
        <aside class="w-1/3 max-md:w-full rounded-lg shadow-lg flex flex-col gap-6">
            <div class="flex flex-col items-start gap-6">
                <i data-lucide="circle-user" class="w-24 h-24 stroke-current"></i>
                <!-- <img src="https://via.placeholder.com/100" alt="Profile Picture" class="rounded-full mx-auto"> -->
                <div class="flex flex-col items-start gap-1">
                    <h3 class="text-center text-2xl font-semibold">John Doe</h3>
                    <p class="max-lg:text-xs text-center text-blue-400">@hackmaster</p>
                    <p class="max-lg:text-xs text-center text-sm text-gray-400">Security Engineer | Bug Hunter</p>
                </div>

                <div class="flex flex-row gap-2 justify-center space-x-2">
                    <span class="bg-blue-600 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center gap-1"><i class="w-4 h-4 stroke-current" data-lucide="star"></i>Top 50</span>
                    <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center gap-1"><i class="w-4 h-4 stroke-current" data-lucide="flag"></i>45 Flags</span>
                    <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center gap-1"><i class="w-4 h-4 stroke-current" data-lucide="users"></i>12 Teams</span>
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
                        <li id="mail" class="flex flex-row items-cente gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="mail"></i>john.doe@example.com</li>
                        <li id="age" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="cake"></i>27 years old</li>
                        <li id="university" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="university"></i>ESGIS University</li>
                        <li id="city" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="map"></i>Paris, France</li>
                        <li id="web-security" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="shield"></i>Web Security</li>
                        <li id="languages" class="flex flex-row items-center gap-2"><i class="w-4 h-4 stroke-blue-500" data-lucide="globe"></i>English, French</li>
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
                                        <p class="max-lg:text-xs text-gray-300">Total Challenges Solved</p>
                                        <i data-lucide="flag" class="w-8 h-8 p-2 stroke-current bg-(--blue-opac) text-blue-600 rounded-lg"></i>
                                    </div>
                                    <div class="w-full flex flex-col gap-1 items-start">
                                        <h3 class="text-3xl font-bold text-white m-0">37</h3>
                                        <p class="max-lg:text-xs text-gray-400">12 this month</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Points Card -->
                            <div class="points-card rounded-lg p-6 transition-all duration-300 ease-in-out hover:border-purple-600 hover:shadow-md hover:shadow-purple-600/50 hover:-translate-y-1">
                                <div class="flex flex-col gap-6 justify-between items-start w-full h-full">
                                    <div class="w-full flex flex-row justify-between items-center gap-2">
                                        <p class="max-lg:text-xs text-gray-300">Current Points</p>
                                        <i data-lucide="trophy" class="w-8 h-8 p-2 stroke-current bg-(--blue-opac) text-purple-600 rounded-lg"></i>
                                    </div>
                                    <div class="w-full flex flex-col gap-1 items-start">
                                        <h3 class="text-3xl font-bold text-white m-0">1,250</h3>
                                        <p class="max-lg:text-xs text-green-400">+124 points last days</p>
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
                                        <h3 class="text-3xl font-bold text-white m-0">#42</h3>
                                        <p class="max-lg:text-xs text-gray-400">Top 10%</p>
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
                            <div class="space-y-4">
                                <!-- Completed Challenge -->
                                <div class="border-b border-slate-800 pb-4 transition duration-300 ease-in-out hover:-translate-y-1">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex items-center justify-center size-fit bg-green-700/30 p-2 rounded-full mr-4">
                                                <i data-lucide="check-square" class="w-4 h-4 stroke-current text-green-500"></i>
                                            </div>
                                            <div>
                                                <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Completed "SQL Injection Basics"</p>
                                                <p class="max-lg:text-xs max-md:text-xs max-md:font-normal">+100 points</p>
                                            </div>
                                        </div>
                                        <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">2 days ago</p>
                                    </div>
                                </div>

                                <!-- Started Challenge -->
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

                                <!-- Feedback Item -->
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
                                <div class="w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto">
                                    <!-- XSS Attack Simulation Card -->
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">web</span>
                                            <span class="bg-orange-500/20 text-orange-500 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">Medium</span>
                                        </div>
                                        <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal">XSS Attack Simulation</h2>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Find and exploit cross-site scripting vulnerabilities</p>
                                        <div class="flex flex-row items-center justify-between mb-2">
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Started: Dec 12, 2023</p>
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">100 points</p>
                                        </div>
                                        <button class="max-lg:text-sm bg-blue-500 text-white px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50 max-md:font-normal">Continue Challenge</button>
                                    </div>

                                    <!-- Data Exfiltration Challenge Card -->
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
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
                                    </div>
                                </div>
                            </div>

                            <div class="sub-tab-content hidden" id="subTab2">
                                <div class="w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto">
                                    <!-- XSS Attack Simulation Card -->
                                    <div class="card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">web</span>
                                            <span class="bg-green-500/20 text-green-500 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">Easy</span>
                                        </div>
                                        <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal">SQL Injection Basics</h2>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Exploit SQL injection vulnerabilities</p>
                                        <div class="flex flex-row items-center justify-between mb-2">
                                            <p class="flex flex-row items-center text-sm font-normal max-lg:text-xs text-white max-md:font-normal"><i data-lucide="trophy" class="w-4 h-4 stroke-current"></i>Completed</p>
                                            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">100 points</p>
                                        </div>
                                        <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Completed on: March 15, 2023</p>
                                    </div>

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
                                    </div>
                                </div>
                            </div>

                            <div class="sub-tab-content hidden" id="subTab3">
                                <div class="All-challenges cursor-pointer w-fit mx-auto flex flex-row items-center justify-center gap-2 rounded-full transition-all duration-300 ease-in-out text-white hover:bg-gray-700 py-2 px-4 max-md:w-full border border-gray-700 hover:-translate-y-1">
                                    <i data-lucide="link-2"></i><span class="text-sm max-md:text-center text-center text-nowrap font-medium max-md:text-xs">All challenges</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenu de tab3 -->
                <div class="tab-content hidden" id="tab3">
                    <!-- Recent Activity Section -->
                    <div class="card-bg rounded-lg p-6 border border-gray-700">
                        <div class="flex items-center justify-start gap-2 mb-6">
                            <i data-lucide="activity" class="w-6 h-6 stroke-blue-500"></i>
                            <h3 class="text-xl font-bold text-white">Recent Activity</h3>
                        </div>

                        <!-- Activity Items -->
                        <div class="space-y-4">
                            <!-- Completed Challenge -->
                            <div class="border-b border-slate-800 pb-4 transition delay-150 duration-300 ease-in-out hover:-translate-y-1">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex items-center justify-center size-fit bg-green-700/30 p-2 rounded-full mr-4">
                                            <i data-lucide="check-square" class="w-4 h-4 stroke-current text-green-500"></i>
                                        </div>
                                        <div>
                                            <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal">Completed "SQL Injection Basics"</p>
                                            <p class="max-lg:text-xs max-md:text-xs max-md:font-normal">+100 points</p>
                                        </div>
                                    </div>
                                    <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal">2 days ago</p>
                                </div>
                            </div>

                            <!-- Started Challenge -->
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

                            <!-- Feedback Item -->
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
                            </div>
                        </div>

                        <!-- Load more button -->
                        <button class="max-lg:text-sm w-full mt-4 py-3 text-center rounded-lg border border-slate-700 text-white hover:bg-slate-800 transition max-md:text-xs max-md:font-normal">
                            Load More
                        </button>
                    </div>
                </div>

                <!-- Contenu de tab4 -->
                <div class="tab-content hidden" id="tab4">
                    <div class="flex flex-col items-center justify-center gap-6 max-md:gap-4 max-w-[1200px] max-md:mx-[5%] my-1 mx-auto p-5">
                        <form class="form-card-background p-6 rounded-lg shadow-md w-full mx-auto border border-gray-700">
                            <div class="flex items-center justify-start gap-2 mb-6">
                                <i data-lucide="user" class="w-6 h-6 stroke-blue-500"></i>
                                <h1 class="text-2xl font-bold text-white">Personal Information</h1>
                            </div>

                            <!-- First Name and Last Name -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                    <input type="text" value="John" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                    <input type="text" value="Dee" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                                </div>
                            </div>

                            <!-- Display Name and Email -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
                                    <input type="text" value="hackmaster" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="john.doe@example.com" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" disabled>
                                </div>
                            </div>

                            <!-- School/University and Location -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">School/University</label>
                                    <input type="text" value="ESGIS University" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" disabled>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                    <input type="text" value="Paris, France" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" disabled>
                                </div>
                            </div>

                            <!-- Bio -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                                <textarea class="input-gradient-royal block w-full rounded-md border border-gray-700/70 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm" rows="4" disabled>Security Engineer | Bug Hunter with expertise in Web Application Security and Network Penetration Testing.</textarea>
                            </div>

                            <!-- Update Profile Button -->
                            <button class="bg-blue-500 w-full text-white px-4 py-2 rounded-lg transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50">Update Profile</button>
                        </form>

                        <form class="form-card-background p-6 rounded-lg shadow-md w-full mx-auto border border-gray-700">
                            <div class="flex items-center justify-start gap-2 mb-6">
                                <i data-lucide="settings" class="w-6 h-6 stroke-blue-500"></i>
                                <h1 class="text-2xl font-bold text-white">Account Settings</h1>
                            </div>

                            <!-- Current Password -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                                <input type="password" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                            </div>

                            <!-- New Password -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                                <input type="password" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Confirm New Password</label>
                                <input type="password" class="input-gradient-royal block w-full rounded-md border border-gray-700/40 px-3 py-2 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-sky-500 sm:text-sm">
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