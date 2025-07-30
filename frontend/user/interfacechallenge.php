<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION["csrf_token"]; ?>">

    <?php require_once '../includes/user/head.php'; ?>

    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="/css/styles/user/interfacechallenge.css">

    <!-- Fonts et Icônes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.js" defer></script>

    <style>
        :root {
            /* Couleurs de base du thème */
            --background: #0A0F1C;
            --background-secondary: #0D1225;
            --card-bg: linear-gradient(135deg, #1A1F2B 0%, #141925 100%);
            --primary: #2563EB;
            --primary-dark: #1d4ed8;
            --primary-light: #3b82f6;
            --text: #FFFFFF;
            --text-secondary: #94A3B8;
            --border: #2D3441;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: radial-gradient(ellipse at top, rgba(37, 99, 235, 0.1) 0%, transparent 70%),
                        linear-gradient(180deg, var(--background) 0%, var(--background-secondary) 100%);
            color: var(--text);
            min-height: 100vh;
            line-height: 1.6;
        }

        .glass-effect {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .cyber-glow {
            box-shadow: 0 0 30px rgba(37, 99, 235, 0.3);
        }

        .loading-shimmer {
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.2), transparent);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>

<body class="min-h-screen text-slate-100 overflow-x-hidden">
    <?php require_once '../includes/user/header.php'; ?>
    
    <!-- Fond animé -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute inset-10 bg-gradient-to-r from-primary/10 via-cyan-400/5 to-primary/10 animate-pulse"></div>
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
    </div>

    <main class="relative z-10 container mx-auto px-4 sm:px-6 py-8 max-w-7xl">
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 min-h-[calc(100vh-140px)]">

            <!-- Colonne gauche - Informations du défi -->
            <div class="xl:col-span-1 space-y-6">

                <!-- Carte d'informations du défi -->
                <div class="glass-effect rounded-2xl p-6 animate-fade-in">
                    <!-- En-tête du défi -->
                    <div class="pb-6 border-b border-royal-800/50 mb-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h2 id="challenge-title" class="text-2xl font-bold bg-gradient-to-r from-cyber-400 to-royal-400 bg-clip-text mb-3">
                                    Chargement du défi...
                                </h2>
                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <div class="flex items-center gap-2 px-3 py-1.5 bg-royal-800/30 rounded-lg">
                                        <i data-lucide="clock" class="w-4 h-4 text-cyber-400"></i>
                                        <span id="challenge-time" class="text-slate-300">1s</span>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-1.5 bg-royal-800/30 rounded-lg">
                                        <i data-lucide="cpu" class="w-4 h-4 text-cyber-400"></i>
                                        <span id="challenge-memory" class="text-slate-300">256MB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="px-4 py-2 rounded-xl text-sm font-semibold bg-gradient-to-r from-emerald-500/20 to-cyan-500/20 text-emerald-300 border border-emerald-500/30 animate-pulse-glow">
                                <i data-lucide="zap" class="w-4 h-4 inline mr-1"></i>
                                Facile
                            </div>
                        </div>
                    </div>

                    <!-- Contenu scrollable -->
                    <div class="space-y-6 max-h-96 overflow-y-auto custom-scrollbar">
                        <!-- Objectif -->
                        <div class="group">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-cyber-400/20 to-royal-500/20 flex items-center justify-center border border-cyber-400/30">
                                    <i data-lucide="target" class="w-4 h-4 text-cyber-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-200 group-hover:text-cyber-400 transition-colors">
                                    Objectif
                                </h3>
                            </div>
                            <div id="challenge-description" class="text-slate-300 text-sm leading-relaxed space-y-2">
                                <div class="h-4 bg-slate-700/50 rounded loading-shimmer"></div>
                                <div class="h-4 bg-slate-700/50 rounded loading-shimmer w-5/6" style="animation-delay: 0.2s;"></div>
                                <div class="h-4 bg-slate-700/50 rounded loading-shimmer w-3/4" style="animation-delay: 0.4s;"></div>
                            </div>
                        </div>

                        <!-- Contraintes -->
                        <div class="group">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-orange-400/20 to-red-500/20 flex items-center justify-center border border-orange-400/30">
                                    <i data-lucide="shield-alert" class="w-4 h-4 text-orange-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-200 group-hover:text-orange-400 transition-colors">
                                    Contraintes
                                </h3>
                            </div>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-slate-300 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-gradient-to-r from-cyber-400 to-royal-400 mt-2 flex-shrink-0"></div>
                                    <span>Respecter les limites de temps et mémoire imposées</span>
                                </li>
                                <li class="flex items-start gap-3 text-slate-300 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-gradient-to-r from-cyber-400 to-royal-400 mt-2 flex-shrink-0"></div>
                                    <span>Gérer correctement tous les cas de test</span>
                                </li>
                                <li class="flex items-start gap-3 text-slate-300 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-gradient-to-r from-cyber-400 to-royal-400 mt-2 flex-shrink-0"></div>
                                    <span>Écrire un code propre et optimisé</span>
                                </li>
                                <li class="flex items-start gap-3 text-slate-300 text-sm">
                                    <div class="w-2 h-2 rounded-full bg-gradient-to-r from-cyber-400 to-royal-400 mt-2 flex-shrink-0"></div>
                                    <span>Respecter les spécifications techniques</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Console -->
                <div id="consoleSection" class="glass-effect rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.4s;">
                    <div class="bg-royal-900/50 px-6 py-4 flex items-center justify-between border-b border-royal-800/50">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-sm font-semibold text-slate-200">Console</span>
                        </div>
                        <button id="toggleConsole" class="p-2 hover:bg-royal-800/50 rounded-lg transition-colors" aria-label="Réduire/agrandir la console">
                            <i id="toggleConsoleIcon" data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>
                    </div>
                    <div id="consoleOutput" class="p-6 code-font text-sm min-h-32 max-h-48 overflow-y-auto custom-scrollbar">
                        <div class="h-full flex flex-col items-center justify-center text-center text-slate-500">
                            <div class="w-12 h-12 rounded-xl bg-royal-800/30 flex items-center justify-center mb-3">
                                <i data-lucide="terminal" class="w-6 h-6"></i>
                            </div>
                            <span>Console prête pour l'exécution...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite - Éditeur et tests -->
            <div class="xl:col-span-3 space-y-6">

                <!-- Éditeur de code -->
                <div class="glass-effect rounded-2xl overflow-hidden animate-fade-in" style="animation-delay: 0.1s;">
                    <div class="bg-royal-900/50 px-6 py-4 flex items-center justify-between border-b border-royal-800/50">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-400/20 to-cyan-500/20 flex items-center justify-center border border-blue-400/30">
                                    <i data-lucide="code-2" class="w-4 h-4 text-blue-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-200">Éditeur de code</h3>
                            </div>

                            <!-- Sélecteur de langage -->
                            <div class="relative">
                                <button id="languageSelector" class="flex items-center gap-3 px-4 py-2 bg-royal-800/50 hover:bg-royal-700/50 border border-royal-700/50 rounded-xl transition-all duration-300 group">
                                    <div class="w-5 h-5 rounded bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center">
                                        <i data-lucide="code" class="w-3 h-3 text-white"></i>
                                    </div>
                                    <span id="languageName" class="text-sm font-medium text-slate-200">Langage</span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 group-hover:text-slate-200 transition-colors"></i>
                                </button>
                                <div id="languageDropdown" class="hidden absolute z-20 mt-2 w-48 glass-effect rounded-xl border border-royal-700/50 shadow-2xl">
                                    <div class="p-2 z-100 relative" id="languageDropdownOptions">
                                        <!-- Options injectées par JS -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions de l'éditeur -->
                        <div class="flex items-center gap-2">
                            <button id="runCode" class="group px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25 flex flex-row items-center justify-between gap-2" title="Exécuter le code">
                                <i data-lucide="play" class="w-4 h-4 text-white"></i>
                                <span class="text-sm font-medium text-white hidden sm:inline">Exécuter</span>
                            </button>
                            <button id="resetCode" class="p-2 bg-royal-800/50 hover:bg-royal-700/50 rounded-xl transition-all duration-300 border border-royal-700/50" title="Réinitialiser l'éditeur">
                                <i data-lucide="refresh-cw" class="w-4 h-4 text-orange-400"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Éditeur Monaco -->
                    <div id="monaco-editor" class="h-96 bg-royal-950/50"></div>
                </div>

                <!-- Panneau de tests et résultats -->
                <div class="glass-effect rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.3s;">
                    <div class="bg-royal-900/50 px-6 py-4 flex items-center justify-between border-b border-royal-800/50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-purple-400/20 to-pink-500/20 flex items-center justify-center border border-purple-400/30">
                                <i data-lucide="flask-conical" class="w-4 h-4 text-purple-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-slate-200">Tests & Validation</h3>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-400">
                            <div class="w-2 h-2 rounded-full bg-slate-600"></div>
                            <span>En attente</span>
                        </div>
                    </div>

                    <!-- Contenu des tests -->
                    <div id="testResults" class="p-6 min-h-64 max-h-96 overflow-y-auto custom-scrollbar">
                        <div class="h-full flex flex-col items-center justify-center text-center text-slate-500">
                            <div class="w-16 h-16 rounded-2xl bg-royal-800/30 flex items-center justify-center mb-4">
                                <i data-lucide="flask-conical" class="w-8 h-8"></i>
                            </div>
                            <h4 class="text-lg font-semibold text-slate-300 mb-2">Prêt pour les tests</h4>
                            <p class="text-sm">Lancez l'exécution pour voir les résultats de validation</p>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="p-6 border-t border-royal-800/50 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button id="runAllTests" class="group relative overflow-hidden px-6 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-500/25">
                            <div class="flex items-center justify-center gap-3 relative z-10">
                                <i data-lucide="play-circle" class="w-5 h-5 text-white"></i>
                                <div class="text-left">
                                    <div class="text-sm font-semibold text-white">Tester la solution</div>
                                    <div class="text-xs text-blue-100">Validation rapide</div>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        </button>

                        <button id="submitChallenge" class="group relative overflow-hidden px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl transition-all duration-300 shadow-lg hover:shadow-emerald-500/25">
                            <div class="flex items-center justify-center gap-3 relative z-10">
                                <i data-lucide="send" class="w-5 h-5 text-white"></i>
                                <div class="text-left">
                                    <div class="text-sm font-semibold text-white">Soumettre</div>
                                    <div class="text-xs text-emerald-100">Validation finale</div>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Custom scrollbar styles -->
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(30, 41, 59, 0.3);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.5);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.7);
        }
    </style>

    <!-- Scripts -->
    <script>
        // Ajouter les animations au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        // Observer tous les éléments avec animation
        document.querySelectorAll('.glass-effect').forEach(el => {
            observer.observe(el);
        });
    </script>

    <!-- Script principal -->
    <script src="/js/user/interfacechallenge.js" defer></script>
</body>

</html>
