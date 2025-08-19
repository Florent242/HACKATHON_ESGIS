<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0A0F1C">
    <meta name="description" content="Participez au défi de développement et démontrez vos compétences techniques">

    <title>Challenge Développement | Hackathon ESGIS</title>

    <?php require_once '../includes/user/head.php'; ?>

    <link rel="stylesheet" href="/css/styles/user/challenge_project.css">
    <script defer src="/js/user/challenge_project.js"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-[#0A0F1C] to-[#0D1225] text-white font-sans antialiased leading-relaxed">
    <!-- En-tête du site -->
    <?php require_once '../includes/user/header.php'; ?>
    <!-- Loading State -->
    <div id="loading" class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <div class="animate-spin w-12 h-12 border-2 border-blue-500 border-t-transparent rounded-full mx-auto mb-4"></div>
            <p class="text-slate-400">Chargement des données du challenge...</p>
        </div>
    </div>

    <!-- Main Content -->
    <div id="content" class="hidden">
        <!-- Header -->
        <sectio class="header-blur top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                            <i data-lucide="code-2" class="w-6 h-6 text-white icon-enhanced"></i>
                        </div>
                        <div>
                            <h1 id="hackathon-title" class="text-xl font-bold text-white">Chargement...</h1>
                            <p id="phase-title" class="text-slate-400 text-sm">Phase en cours...</p>
                        </div>
                    </div>
                </div>
            </div>
        </sectio>

        <main>
            <section class="relative overflow-hidden py-10">
                <div class="absolute inset-0">
                    <div class="absolute top-20 left-10 w-20 h-20 bg-blue-500/10 rounded-full floating blur-xl"></div>
                    <div class="absolute top-40 right-20 w-32 h-32 bg-purple-500/10 rounded-full floating blur-xl"></div>
                    <div class="absolute bottom-20 left-1/3 w-24 h-24 bg-cyan-500/10 rounded-full floating blur-xl"></div>
                </div>

                <div class="relative max-w-7xl mx-auto px-6">
                    <div class="text-center mb-16">
                        <div class="inline-flex items-center space-x-3 card-bg px-3 py-2 rounded-full mb-8">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-400 to-cyan-500 flex items-center justify-center">
                                <i data-lucide="zap" class="w-4 h-4 text-white"></i>
                            </div>
                            <span id="hackathon-theme" class="text-lg font-semibold text-white">Innovation & Impact Social</span>
                        </div>

                        <!-- Main Title -->
                        <h1 id="challenge-title" class="text-5xl md:text-7xl font-black mb-8 bg-gradient-to-r from-white via-blue-100 to-slate-300 bg-clip-text text-transparent leading-tight">
                            Challenge en cours...
                        </h1>

                        <!-- Subtitle with enhanced styling -->
                        <p class="text-xl md:text-2xl text-slate-300 mb-12 max-w-4xl mx-auto leading-relaxed">
                            Créez une solution innovante qui transforme l'expérience utilisateur et démontre votre excellence technique
                        </p>

                        <!-- Enhanced Countdown Timer -->
                        <div class="card-bg rounded-3xl p-10 max-w-4xl mx-auto mb-12 border border-white/10">
                            <div class="flex items-center justify-center mb-6">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center mr-4">
                                    <i data-lucide="timer" class="w-6 h-6 text-white"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-white">Temps restant pour soumettre</h2>
                            </div>

                            <div id="countdown" class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                                <div class="countdown-digit rounded-2xl p-6 text-center">
                                    <div id="days" class="text-4xl md:text-5xl font-black text-blue-400 mb-2">00</div>
                                    <div class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Jours</div>
                                </div>
                                <div class="countdown-digit rounded-2xl p-6 text-center">
                                    <div id="hours" class="text-4xl md:text-5xl font-black text-blue-400 mb-2">00</div>
                                    <div class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Heures</div>
                                </div>
                                <div class="countdown-digit rounded-2xl p-6 text-center">
                                    <div id="minutes" class="text-4xl md:text-5xl font-black text-blue-400 mb-2">00</div>
                                    <div class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Minutes</div>
                                </div>
                                <div class="countdown-digit rounded-2xl p-6 text-center">
                                    <div id="seconds" class="text-4xl md:text-5xl font-black text-blue-400 mb-2">00</div>
                                    <div class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Secondes</div>
                                </div>
                            </div>

                            <div id="countdown-status" class="text-center mt-6 hidden">
                                <span class="bg-red-500/20 border border-red-500/30 text-red-400 px-6 py-3 rounded-full text-lg font-semibold">
                                    <i data-lucide="x-circle" class="w-5 h-5 inline mr-2"></i>
                                    Soumissions terminées
                                </span>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Challenge Content -->
            <section class="max-w-7xl mx-auto px-4 py-16">
                <div class="grid lg:grid-cols-3 gap-8">
                    <!-- Main Content Column -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Description -->
                        <div class="card-bg rounded-3xl p-8">
                            <div class="flex items-center mb-8">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mr-4">
                                    <i data-lucide="file-text" class="w-5 h-5 text-white icon-enhanced"></i>
                                </div>
                                <h2 class="text-3xl font-bold text-white">Description du Challenge</h2>
                            </div>
                            <div id="challenge-description" class="text-slate-300 text-lg leading-relaxed space-y-4">
                                <p>Chargement de la description...</p>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="card-bg rounded-3xl p-8">
                            <div class="flex items-center mb-8">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mr-4">
                                    <i data-lucide="list-checks" class="w-5 h-5 text-white icon-enhanced"></i>
                                </div>
                                <h2 class="text-3xl font-bold text-white">Instructions Détaillées</h2>
                            </div>
                            <div id="challenge-instructions" class="text-slate-300 text-lg leading-relaxed space-y-4">
                                <p>Chargement des instructions...</p>
                            </div>
                        </div>

                        <!-- Criteria Section -->
                        <div class="card-bg rounded-3xl p-8">
                            <div class="flex items-center mb-8">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl flex items-center justify-center mr-4">
                                    <i data-lucide="target" class="w-5 h-5 text-white icon-enhanced"></i>
                                </div>
                                <h2 class="text-3xl font-bold text-white">Critères d'Évaluation</h2>
                            </div>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div class="criteria-card rounded-2xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i data-lucide="lightbulb" class="w-5 h-5 text-blue-400 mr-3"></i>
                                        <h3 class="font-semibold text-white">Innovation</h3>
                                        <span class="ml-auto text-blue-400 font-bold">30%</span>
                                    </div>
                                    <p class="text-slate-400 text-sm">Originalité de l'approche et créativité de la solution</p>
                                </div>
                                <div class="criteria-card rounded-2xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i data-lucide="code" class="w-5 h-5 text-green-400 mr-3"></i>
                                        <h3 class="font-semibold text-white">Qualité Technique</h3>
                                        <span class="ml-auto text-green-400 font-bold">25%</span>
                                    </div>
                                    <p class="text-slate-400 text-sm">Architecture, performance et bonnes pratiques</p>
                                </div>
                                <div class="criteria-card rounded-2xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i data-lucide="heart" class="w-5 h-5 text-purple-400 mr-3"></i>
                                        <h3 class="font-semibold text-white">UX/UI</h3>
                                        <span class="ml-auto text-purple-400 font-bold">25%</span>
                                    </div>
                                    <p class="text-slate-400 text-sm">Expérience utilisateur et design interface</p>
                                </div>
                                <div class="criteria-card rounded-2xl p-6">
                                    <div class="flex items-center mb-3">
                                        <i data-lucide="book-open" class="w-5 h-5 text-orange-400 mr-3"></i>
                                        <h3 class="font-semibold text-white">Documentation</h3>
                                        <span class="ml-auto text-orange-400 font-bold">20%</span>
                                    </div>
                                    <p class="text-slate-400 text-sm">Clarté et complétude de la documentation</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submission Formats -->
                        <div class="card-bg rounded-3xl p-8">
                            <div class="flex items-center mb-8">
                                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl flex items-center justify-center mr-4">
                                    <i data-lucide="upload" class="w-5 h-5 text-white icon-enhanced"></i>
                                </div>
                                <h2 class="text-3xl font-bold text-white">Formats de Soumission</h2>
                            </div>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="format-card recommended relative rounded-2xl p-6 border-2">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center mr-3">
                                            <i data-lucide="github" class="w-4 h-4 text-green-400"></i>
                                        </div>
                                        <h3 class="font-semibold text-white">Dépôt GitHub</h3>
                                    </div>
                                    <p class="text-slate-400 mb-4">Repository public ou privé avec accès jury</p>
                                    <div class="space-y-2 text-sm text-slate-400">
                                        <div class="flex items-center">
                                            <i data-lucide="check" class="w-3 h-3 text-green-400 mr-2"></i>
                                            <span>Historique des commits</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="check" class="w-3 h-3 text-green-400 mr-2"></i>
                                            <span>README détaillé requis</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="check" class="w-3 h-3 text-green-400 mr-2"></i>
                                            <span>Demo link dans description</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="format-card rounded-2xl p-6 border-2">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center mr-3">
                                            <i data-lucide="archive" class="w-4 h-4 text-blue-400"></i>
                                        </div>
                                        <h3 class="font-semibold text-white">Archive ZIP</h3>
                                    </div>
                                    <p class="text-slate-400 mb-4">Projet complet en archive</p>
                                    <div class="space-y-2 text-sm text-slate-400">
                                        <div class="flex items-center">
                                            <i data-lucide="info" class="w-3 h-3 text-blue-400 mr-2"></i>
                                            <span>Maximum 50 MB</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="info" class="w-3 h-3 text-blue-400 mr-2"></i>
                                            <span>Exclure node_modules/</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="info" class="w-3 h-3 text-blue-400 mr-2"></i>
                                            <span>Guide d'installation requis</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Challenge Stats -->
                        <div class="card-bg rounded-2xl p-6">
                            <h3 class="font-bold text-white mb-6 flex items-center">
                                <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-400 icon-enhanced"></i>
                                Informations
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Points</span>
                                    <div class="flex items-center">
                                        <span id="challenge-points" class="font-bold text-2xl text-blue-400">-</span>
                                        <i data-lucide="coins" class="w-4 h-4 ml-1 text-yellow-400"></i>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Type</span>
                                    <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded-full text-sm font-medium">Développement</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Soumissions</span>
                                    <span class="text-white font-semibold">Leader uniquement</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submission Button -->
                        <div class="card-bg rounded-2xl p-6">
                            <h3 class="font-bold text-white mb-4 flex items-center">
                                <i data-lucide="rocket" class="w-5 h-5 mr-2 text-green-400 icon-enhanced"></i>
                                Action
                            </h3>
                            <button id="submit-btn" class="w-full btn-premium text-white font-bold py-4 px-6 rounded-2xl transition-all duration-500 transform disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none mb-4 btn-primary btn-startchallenge">
                                <i data-lucide="send" class="w-5 h-5 inline mr-2"></i>
                                Soumettre mon projet
                            </button>
                            <p class="text-xs text-slate-400 text-center leading-relaxed">
                                Seul le leader peut soumettre le projet.
                            </p>
                        </div>

                        <!-- Tips -->
                        <div class="card-bg rounded-2xl p-6">
                            <h3 class="font-bold text-white mb-4 flex items-center">
                                <i data-lucide="lightbulb" class="w-5 h-5 mr-2 text-green-500 icon-enhanced"></i>
                                Conseils
                            </h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="w-3 h-3 text-green-400"></i>
                                    </div>
                                    <span class="text-sm text-slate-300">Utilisez Git pour le versionnement avec des commits atomiques et des messages clairs</span>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="w-3 h-3 text-green-400"></i>
                                    </div>
                                    <span class="text-sm text-slate-300">Documentez votre code pour faciliter l'intégration</span>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="w-3 h-3 text-green-400"></i>
                                    </div>
                                    <span class="text-sm text-slate-300">Mettez en place des tests unitaires pour les fonctionnalités critiques</span>
                                </div>
                                <div class="flex items-start">
                                    <div class="w-6 h-6 rounded-full bg-green-500/20 flex items-center justify-center mr-3 mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="w-3 h-3 text-green-400"></i>
                                    </div>
                                    <span class="text-sm text-slate-300">Optimisez les performances côté client (lazy loading, code splitting)</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rules & Constraints -->
                        <div class="card-bg rounded-2xl p-6">
                            <h3 class="font-bold text-white mb-4 flex items-center">
                                <i data-lucide="shield-check" class="w-5 h-5 mr-2 text-red-400 icon-enhanced"></i>
                                Règlement
                            </h3>
                            <div class="space-y-3 text-sm">
                                <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20">
                                    <div class="flex items-center mb-2">
                                        <i data-lucide="clock" class="w-4 h-4 text-red-400 mr-2"></i>
                                        <span class="font-semibold text-red-400">Délai strict</span>
                                    </div>
                                    <p class="text-slate-300">Aucune soumission acceptée après l'échéance</p>
                                </div>
                                <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20">
                                    <div class="flex items-center mb-2">
                                        <i data-lucide="download" class="w-4 h-4 text-blue-400 mr-2"></i>
                                        <span class="font-semibold text-blue-400">Formats</span>
                                    </div>
                                    <p class="text-slate-300">GitHub recommandé, ZIP &lt; 50MB accepté</p>
                                </div>
                                <div class="p-3 rounded-xl bg-green-500/10 border border-green-500/20">
                                    <div class="flex items-center mb-2">
                                        <i data-lucide="download" class="w-4 h-4 text-green-400 mr-2"></i>
                                        <span class="font-semibold text-green-400">Soumission</span>
                                    </div>
                                    <p class="text-slate-300">Seul le leader peut soumettre le projet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

</body>


</html>