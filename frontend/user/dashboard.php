<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Challenge Yourself</title>
    <link rel="stylesheet" href="/css/styles/user/dashboard.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="/js/user/dashboard.js" type="module"></script>
    <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
</head>

<body>
    <div id="global-loading-spinner" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 transition-opacity duration-300">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-t-transparent border-blue-500"></div>
    </div>

    <!-- Navigation -->
    <?php require_once '../includes/user/header.php'; ?>
    <main>
        <section class="header-dashboard flex flex-col items-center justify-center gap-12 max-md:gap-10 max-w-[1400px] max-md:mx-[5%] my-1 mx-auto p-5">
            <div class="container flex flex-row w-full max-md:flex-col items-center justify-between gap-5 mx-auto">

                <h1 class="text-4xl font-bold w-auto max-md:text-center max-md:text-3xl max-sm:text-2xl">Bienvenue sur votre Dashboard,
                    <span class="text-blue-500 Username">
                        [Username]
                    </span>
                </h1>

                <div class="challenges-link flex flex-row gap-5 items-center">
                    <button class="cursor-pointer flex flex-row items-center justify-center gap-1 rounded-xl bg-blue-500 text-white p-2 text-sm max-md:text-center max-md:text-xs whitespace-nowrap btn-primary btn-participate" id="btn-dev-challenges">
                        <i data-lucide="code" class="max-md:w-3 max-md:h-3 w-4 h-4 stroke-current"></i>Challenge dev
                    </button>
                    <button class="cursor-pointer flex flex-row items-center justify-center gap-1 rounded-xl bg-blue-500 text-white p-2 text-sm max-md:text-center max-md:text-xs whitespace-nowrap btn-primary btn-participate" id="btn-hack-challenges">
                        <i data-lucide="shield" class="max-md:w-3 max-md:h-3 w-4 h-4 stroke-current"></i>Challenge hack
                    </button>
                </div>
            </div>
            <div class="user-info flex flex-row gap-5 items-center justify-between p-3 w-full max-w-[1400px] rounded-xl max-md:flex-col card-bg border border-gray-700">

                <div class="flex flex-row items-center gap-5 w-full max-md:flex-col">

                    <div class="user-circle rounded-full flex items-center justify-center">
                        <i data-lucide="circle-user" class="w-24 h-24 stroke-current"></i>
                    </div>
                    <div class="user-info-text text-left flex flex-col justify-start max-md:text-center">
                        <h2 class="Username text-xl font-semibold w-auto max-md:text-center max-md:text-lg">[Username]</h2>
                        <p class="Email text-gray-400 text-sm max-md:text-center max-md:text-sm">[Email]</p>
                    </div>
                </div>
                <a href="/user/profile">
                    <div class="modify-profile btn-primary btn-standard">
                        <i data-lucide="user"></i> <span class="text-sm max-md:text-center text-center text-nowrap font-medium max-md:text-xs whitespace-nowrap">Modifier le profil</span>
                    </div>
                </a>
            </div>

            <div class="text-white w-full max-w-[1400px]">
                <!-- Grid des cartes -->
                <div class="grid grid-cols-4 max-lg:grid-cols-2 max-md:grid-cols-1 items-stretch  justify-between max-md:flex-col max-md:w-full max-md:justify-center gap-4 w-full">

                    <!-- Carte Défis de développement -->
                    <div class="fade-in-left">
                        <div class="w-full h-full transition-all duration-300 ease-in hover:border-blue-500/20 hover:shadow-md hover:shadow-blue-500/50 flex flex-col gap-2 border border-gray-700 p-5 rounded-2xl shadow-lg relative" style="background: var(--card-bg);">
                            <div class="flex flex-row gap-2 items-center justify-between mb-2">
                                <h3 class="text-md font-normal">Défis de développement</h3>
                                <i data-lucide="code" class="max-md:w-6 max-md:h-6 w-10 h-10 stroke-blue-500 p-2 bg-(--blue-opac) rounded-lg"></i>
                            </div>
                            <div class="text-3xl font-bold mt-2" id="number-dev-challenges"><i data-lucide="loader-circle" class="animate-spin"></i></div>
                            <p class="text-gray-400 flex items-center flex-row"><span id="number-dev-challenges-on"><i data-lucide="loader-circle" class="animate-spin"></i></span> défis en cours</p>
                            <p class="text-green-400 text-sm mt-1 flex items-center flex-row"><span id="dev-stat"><i data-lucide="loader-circle" class="animate-spin"></i></span> défis soumis</p>
                        </div>
                    </div>

                    <!-- Carte Défis de hacking -->
                    <div class="fade-in-left" style="transition-delay: 100ms;">
                        <div class="w-full h-full transition-all duration-300 ease-in hover:border-blue-500/20 hover:shadow-md hover:shadow-blue-500/50 flex flex-col gap-2 border border-gray-700 p-5 rounded-2xl shadow-lg relative" style="background: var(--card-bg);">
                            <div class="flex flex-row gap-2 items-center justify-between mb-2">
                                <h3 class="text-md font-normal">Défis de hacking</h3>
                                <i data-lucide="shield" class="max-md:w-6 max-md:h-6 w-10 h-10 stroke-blue-500 p-2 bg-(--blue-opac) rounded-lg"></i>
                            </div>
                            <div class="text-3xl font-bold mt-2" id="number-hacking-challenges"><i data-lucide="loader-circle" class="animate-spin"></i></div>
                            <p class="text-gray-400 flex items-center flex-row"><span id="number-hacking-challenges-validate"><i data-lucide="loader-circle" class="animate-spin"></i></span> flags validés</p>
                            <p class="text-green-400 text-sm mt-1 flex items-center flex-row"><span id="hacking-stat"><i data-lucide="loader-circle" class="animate-spin"></i></span>% de réussite</p>

                        </div>
                    </div>

                    <!-- Carte Projets soumis -->
                    <div class="fade-in-right" style="transition-delay: 100ms;">
                        <div class="w-full h-full transition-all duration-300 ease-in hover:border-blue-500/20 hover:shadow-md hover:shadow-blue-500/50 flex flex-col gap-2 border border-gray-700 p-5 rounded-2xl shadow-lg relative" style="background: var(--card-bg);">
                            <div class="flex flex-row gap-2 items-center justify-between mb-2">
                                <h3 class="text-md font-normal">Projets soumis</h3>
                                <i data-lucide="file-text" class="max-md:w-6 max-md:h-6 w-10 h-10 stroke-blue-500 p-2 bg-(--blue-opac) rounded-lg"></i>
                            </div>
                            <div class="text-3xl font-bold mt-2" id="number-submitted-projects"><i data-lucide="loader-circle" class="animate-spin"></i></div>
                            <p class="text-gray-400 flex items-center flex-row"><span id="number-submitted-projects-wait">0</span> projet en attente d'évaluation</p>
                        </div>
                    </div>

                    <!-- Carte Points totaux -->
                    <div class="fade-in-right">
                        <div class="w-full h-full transition-all duration-300 ease-in hover:border-blue-500/20 hover:shadow-md hover:shadow-blue-500/50 flex flex-col gap-2 border border-gray-700 p-5 rounded-2xl shadow-lg relative" style="background: var(--card-bg);">
                            <div class="flex flex-row gap-2 items-center justify-between mb-2">
                                <h3 class="text-md font-normal">Points totaux</h3>
                                <i data-lucide="award" class="max-md:w-6 max-md:h-6 w-10 h-10 stroke-blue-500 p-2 bg-(--blue-opac) rounded-lg"></i>
                            </div>
                            <div class="text-3xl font-bold mt-2" id="total-points"><i data-lucide="loader-circle" class="animate-spin"></i></div>
                            <p class="text-gray-400"><span class="user-rank">#</span> sur <span class="rank-max">#</span></p>
                            <p class="text-green-400 text-sm mt-1 flex items-center flex-row">↑ <span id="total-points-stat"><i data-lucide="loader-circle" class="animate-spin"></i></span>% depuis la derniere connexion</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Défis en cours et Notifications -->
            <div class="flex flex-row max-md:flex-col gap-6 bg-(--card-bg) w-full text-white">
                <!-- Colonne principale -->
                <div class="flex-1 space-y-6">
                    <div class="flex flex-row gap-4">
                        <h3 class="text-xl font-medium">Progession</h3>
                    </div>
                    <!-- Défis en cours -->
                    <section class="fade-in-left space-y-4 w-full flex flex-col border border-gray-700 p-5 rounded-2xl shadow-lg card-bg">
                        <div class="flex flex-row justify-start items-center gap-2">
                            <i data-lucide="activity" class="max-md:w-6 max-md:h-6 w-10 h-10 stroke-blue-500 p-2 bg-(--blue-opac) rounded-lg"></i>
                            <h3 class="text-lg font-medium">Défis en cours</h3>
                        </div>
                        <div id="current-challenges-container" class="flex flex-col gap-4">
                            <div class="hidden flex-row justify-between bg-(--card-bg) p-4 rounded-xl border border-gray-700 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 current-challenge-item">
                                <div class="flex flex-col justify-between gap-2">
                                    <h4 class="text-base font-semibold challenge-title">API REST - Module d'authentification</h4>
                                    <p class="text-gray-400 text-sm challenge-description">Créez une API REST sécurisée avec JWT.</p>
                                    <p class="flex flex-row items-center text-gray-500 text-sm">
                                        <i data-lucide="calendar" class="w-4 h-4"></i> <span class="challenge-deadline">Date limite: 23 mai</span>
                                    </p>
                                </div>
                                <button class="cursor-pointer relative flex items-center justify-center mb-auto w-auto h-auto top-0 right-0 bg-blue-500 text-white px-3 py-1 rounded-2xl transition delay-150 duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50">Soumettre le projet</button>
                            </div>
                            
                            <div class="flex flex-col items-center text-center gap-3 py-10 px-6" id="no-current-challenges">
                                <div class="animate-bounce-slow mb-3">
                                    <i data-lucide="activity" class="w-12 h-12 text-blue-400/60"></i>
                                </div>
                                <h3 class="text-gray-200 text-lg font-medium">Pas de défi disponible</h3>
                                <p class="text-sm text-gray-400">Tu n'as pas encore rejoint ou commencé de défi. Explore-les pour te lancer !</p>
                                <a href="/user/hackathon" class="text-blue-500 hover:underline text-sm cursor-pointer transition duration-300 ease-in-out">Explorer les différents hackathons</a>
                            </div>


                        </div>
                    </section>

                    <!-- Activité récente -->
                    <section class="fade-in flex flex-col gap-2 space-y-4 p-5 border border-gray-700 rounded-2xl shadow-xl card-bg">
                        <h2 class="text-lg font-medium">Activité récente</h2>
                        <div class="flex flex-col items-center text-center" id="recent-activities-container">
                            <div class="hidden flex-row justify-start p-4 items-center w-full bg-(--card-bg) rounded-xl gap-5 border-b border-slate-800 pb-4  transition delay-150 duration-300 ease-in-out hover:-translate-y-1 recent-activity-item">
                                <i data-lucide="trophy" class="w-5 h-5 flex self-center stroke-current activity-icon"></i>
                                <div class="flex flex-col items-start justify-between">
                                    <p class="text-gray-400 activity-text">Flag validé pour "XSS Challenge"</p>
                                    <p class="text-gray-500 text-sm activity-time">Il y a 3 heures</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-center text-center py-10 px-6" id="no-recent-activities">
                                <div class="animate-bounce-slow mb-3">
                                    <i data-lucide="history" class="w-12 h-12 text-emerald-400/60"></i>
                                </div>
                                <h3 class="text-gray-200 text-lg font-medium">Aucune activité enregistrée</h3>
                                <p class="text-gray-400 text-sm mt-1">Commencez un défi ou explorez les challenges à venir.</p>
                            </div>

                        </div>
                        <button class="cursor-pointer flex items-center w-full justify-center text-center p-4 rounded-xl border-none text-white max-md:text-sm hover:bg-gray-700 hover:scale-103 transition duration-300 ease-in-out" id="see-all-activities">Voir toutes les activités</button>
                    </section>
                </div>

                <!-- Colonne latérale (Notifications et événements) -->
                <aside class="md:w-1/3 flex flex-col gap-6 w-full">
                    <!-- Notifications -->
                    <section class="space-y-6">
                        <div class="flex flex-row gap-4">
                            <h3 class="text-xl font-medium">Notifications</h3>
                        </div>
                        <!-- Derniere notification -->
                        <div class="fade-in-right space-y-4 w-full flex flex-col border border-gray-700 p-5 rounded-2xl shadow-lg card-bg">
                            <div class="flex flex-row justify-start items-center gap-2">
                                <i data-lucide="bell" class="w-10 h-10 stroke-blue-500 p-2 bg-(--blue-opac) rounded-lg"></i>
                                <h3 class="text-lg font-medium">Dernières notifications</h3>
                            </div>
                            <div class="flex flex-col gap-2" id="notifications-container">
                                <div class="hidden flex-col gap-2 justify-between bg-(--card-bg) p-4 rounded-xl border border-gray-700 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 notification-item">
                                    <p class="font-medium notification-title">Nouveau challenge de développement</p>
                                    <p class="text-gray-400 text-sm notification-message">Un nouveau challenge "Architecture Microservices" ajouté.</p>
                                    <p class="text-gray-500 text-xs notification-time">Il y a environ 2 heures</p>
                                </div>
                                <div class="flex flex-col items-center text-center py-10 px-6" id="no-notifications">
                                    <div class="animate-bounce-slow mb-3">
                                        <i data-lucide="bell-off" class="w-12 h-12 text-purple-400/60"></i>
                                    </div>
                                    <h3 class="text-gray-200 text-lg font-medium">Rien à signaler</h3>
                                    <p class="text-gray-400 text-sm mt-1">Vous êtes à jour. Nous vous tiendrons informé dès qu’il y a du nouveau !</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Prochains événements -->
                    <section class="fade-in space-y-4 w-full flex flex-col border border-gray-700 p-5 rounded-2xl shadow-lg card-bg">
                        <div class="flex flex-row justify-start items-center gap-2">
                            <h3 class="text-xl font-medium">Prochains événements</h3>
                        </div>
                        <div id="next-event-container" class="flex flex-col gap-2">
                            <div class="flex flex-col items-center text-center py-12 px-6 border border-dashed border-gray-600/40 rounded-xl bg-gray-900/20" id="no-next-event">
                                <div class="animate-bounce-slow mb-4">
                                    <i data-lucide="calendar-x" class="w-14 h-14 text-red-400/70"></i>
                                </div>
                                <h3 class="text-gray-100 text-xl font-semibold">Aucun hackathon prévu</h3>
                                <p class="text-gray-400 text-sm mt-2">Nous publierons bientôt de nouveaux événements passionnants. Restez à l'écoute !</p>
                            </div>

                        </div>
                        <button class="cursor-pointer flex items-center w-full justify-center text-center p-4 rounded-xl text-white max-md:text-sm hover:bg-gray-700 hover:scale-105 transition duration-300 ease-in-out" id="see-all-events">Voir tous les événements</button>
                    </section>
                </aside>
            </div>

            <!-- Liens rapides -->
            <section class="flex flex-col w-full gap-4">
                <h2 class="text-white text-lg font-semibold">Liens rapides</h2>
                <div class="flex flex-wrap items-stretch justify-between max-md:flex-col max-md:w-full max-md:justify-center gap-5">

                    <!-- Carte 1 -->
                    <div class="fade-in-left flex-1/3">
                        <a href="/user/challenge_dev" class="flex items-center p-4 bg-gradient-to-r from-gray-900 to-gray-800 rounded-lg w-auto transition hover:scale-105">
                            <div class="bg-blue-900 text-blue-400 p-3 rounded-full">
                                <i data-lucide="code"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-white font-medium">Défis de développement</h3>
                                <p class="text-gray-400 text-sm">Voir tous les défis disponibles</p>
                            </div>
                            <span class="ml-auto text-gray-500"><i data-lucide="square-arrow-out-up-right"></i></span>
                        </a>
                    </div>

                    <!-- Carte 2 -->
                    <div class="fade-in-right flex-1/3">
                        <a href="/user/challenge_hacking" class="flex items-center p-4 bg-gradient-to-r from-gray-900 to-gray-800 rounded-lg w-auto transition hover:scale-105">
                            <div class="bg-gray-800 text-gray-400 p-3 rounded-full">
                                <i data-lucide="shield"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-white font-medium">Challenges de hacking</h3>
                                <p class="text-gray-400 text-sm">Explorer les challenges CTF</p>
                            </div>
                            <span class="ml-auto text-gray-500"><i data-lucide="square-arrow-out-up-right"></i></span>
                        </a>
                    </div>

                    <!-- Carte 3 -->
                    <div class="fade-in flex-1/3">
                        <a href="/user/profile" class="flex items-center p-4 bg-gradient-to-r from-gray-900 to-gray-800 rounded-lg w-auto transition hover:scale-105">
                            <div class="bg-gray-800 text-gray-400 p-3 rounded-full">
                                <i data-lucide="user"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-white font-medium">Modifier mon profil</h3>
                                <p class="text-gray-400 text-sm">Personnaliser votre compte</p>
                            </div>
                            <span class="ml-auto text-gray-500"><i data-lucide="square-arrow-out-up-right"></i></span>
                        </a>
                    </div>

                </div>
            </section>

        </section>
    </main>
</body>

</html>