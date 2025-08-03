<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= $_SESSION["csrf_token"]; ?>">

    <?php require_once '../includes/user/head.php'; ?>

    <title>Défi de développement | ESGIS HACKATHON</title>
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="/css/styles/user/interfacechallenge.css">

    <!-- Fonts et Icônes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.js" defer></script>
</head>

<body class="min-h-screen text-slate-100 overflow-x-hidden">
    <?php require_once '../includes/user/header.php'; ?>

    <!-- Fond animé -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
    </div>

    <main class="relative z-10 container mx-auto px-4 sm:px-6 py-8 max-w-7xl">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 min-h-[calc(100vh-140px)]">
            <!-- Colonne gauche - Informations du défi -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Carte d'informations du défi -->
                <div class="relative rounded-2xl bg-gradient-to-br from-slate-800/50 to-slate-900/70 backdrop-blur-lg border border-slate-700/50 shadow-2xl shadow-slate-900/30 overflow-hidden transition-all duration-300 hover:shadow-blue-900/20 hover:border-blue-500/30">
                    <!-- Effet de bordure animée -->
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-cyan-500/5 rounded-2xl -z-10"></div>
                    
                    <!-- En-tête de la carte -->
                    <div class="relative p-5 border-b border-slate-700/50 bg-gradient-to-r from-slate-800/80 to-slate-800/60">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/20 shadow-lg shadow-blue-500/10">
                                <i data-lucide="terminal" class="w-5 h-5 text-blue-400"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-md font-bold text-slate-100">Détails du défi</span>
                                <span class="text-xs text-slate-400">Informations essentielles</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Corps de la carte -->
                    <div class="p-5 space-y-5">
                        <!-- En-tête avec titre et difficulté -->
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition duration-300"></div>
                            <div class="relative p-4 bg-slate-800/50 rounded-xl border border-slate-700/50 transition-all duration-300 group-hover:border-blue-500/30">
                                <div class="flex items-start justify-between">
                                    <div class="space-y-1">
                                        <span id="challenge-title" class="text-2xl font-semibold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                                            Chargement...
                                        </span>
                                        <!-- <div class="flex flex-wrap items-center gap-2 pt-1">
                                            <span id="challenge-time" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-slate-700/50 text-blue-300 border border-slate-600/50 hover:bg-slate-700/80 transition-colors" data-tooltip="Temps limite">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span>1s</span>
                                            </span>
                                            <span id="challenge-memory" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-slate-700/50 text-cyan-300 border border-slate-600/50 hover:bg-slate-700/80 transition-colors" data-tooltip="Mémoire maximale">
                                                <i data-lucide="cpu" class="w-3 h-3"></i>
                                                <span>1MB</span>
                                            </span>
                                        </div> -->
                                    </div>
                                    <span id="challenge-difficulty" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 text-blue-300 border border-blue-500/20 shadow-md shadow-blue-500/5">
                                        <i data-lucide="zap" class="w-3 h-3"></i>
                                        <span>Facile</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu déroulant -->
                        <div class="space-y-5">
                            <!-- Description -->
                            <div class="group">
                                <div class="flex items-center justify-between mb-2 cursor-pointer">
                                    <span class="flex items-center gap-2 text-sm font-semibold text-blue-400/90 uppercase tracking-wider">
                                        <i data-lucide="align-left" class="w-4 h-4"></i>
                                        Description
                                    </span>
                                </div>
                                <div id="challenge-description" class="prose prose-invert prose-sm text-sm max-w-none text-slate-300 leading-relaxed transition-all duration-300 ease-in-out">
                                    <div class="space-y-2">
                                        <div class="h-3 bg-slate-700/50 rounded-full loading-shimmer"></div>
                                        <div class="h-3 bg-slate-700/50 rounded-full loading-shimmer w-5/6"></div>
                                        <div class="h-3 bg-slate-700/50 rounded-full loading-shimmer w-4/6"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="group">
                                <div class="flex items-center justify-between mb-2 cursor-pointer">
                                    <span class="flex items-center gap-2 text-sm font-semibold text-amber-400/90 uppercase tracking-wider">
                                        <i data-lucide="info" class="w-4 h-4"></i>
                                        Instructions
                                    </span>
                                </div>
                                <div id="challenge-instructions" class="prose prose-invert prose-sm text-sm max-w-none text-slate-300 leading-relaxed transition-all duration-300 ease-in-out">
                                    <div class="space-y-2">
                                        <div class="h-3 bg-slate-700/50 rounded-full loading-shimmer"></div>
                                        <div class="h-3 bg-slate-700/50 rounded-full loading-shimmer w-5/6"></div>
                                        <div class="h-3 bg-slate-700/50 rounded-full loading-shimmer w-4/6"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Console -->
                <div class="glass-effect">
                    <div class="card-header">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="card-title">Console</span>
                        </div>
                        <button id="toggleConsole" class="p-1.5 hover:bg-slate-700/50 rounded-lg transition-colors" aria-label="Réduire/agrandir la console">
                            <i id="toggleConsoleIcon" data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>
                    </div>
                    <div id="consoleOutput" class="p-4 font-mono text-sm bg-slate-900/50 min-h-[120px] max-h-60 overflow-y-auto">
                        <div class="h-full flex flex-col items-center justify-center text-center text-slate-500 text-sm">
                            <i data-lucide="terminal" class="w-6 h-6 mb-2"></i>
                            <span>Console prête pour l'exécution...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite - Éditeur et tests -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Éditeur de code -->
                <div class="glass-effect" id="editorContainer">
                    <div class="card-header">
                        <div class="flex items-center gap-3">
                            <div class="header-icon">
                                <i data-lucide="code" class="w-5 h-5"></i>
                            </div>
                            <span class="card-title">Éditeur de code</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Sélecteur de langage -->
                            <div class="relative">
                                <button id="languageSelector" class="flex items-center gap-2 px-3 py-1.5 bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700/50 rounded-lg transition-colors text-sm font-medium">
                                    <span id="languageName">Langage</span>
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                </button>
                                <div id="languageDropdown" class="hidden absolute right-0 mt-2 w-48 bg-slate-800 border border-slate-700 rounded-xl shadow-xl z-20 max-h-[300px] overflow-y-auto">
                                    <div class="p-1.5 space-y-1" id="languageDropdownOptions">
                                        <!-- Options injectées par JS -->
                                    </div>
                                </div>
                            </div>
                            <button id="runCode" class="btn btn-primary" title="Exécuter le code">
                                <i data-lucide="play" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Exécuter</span>
                            </button>
                            <button id="resetCode" class="btn btn-secondary" title="Réinitialiser l'éditeur">
                                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <div id="monaco-editor" class="h-[400px]"></div>
                </div>

                <!-- Panneau de tests -->
                <div class="glass-effect">
                    <div class="card-header">
                        <div class="flex items-center gap-3">
                            <div class="header-icon bg-purple-500/10 border-purple-500/20 text-purple-400">
                                <i data-lucide="flask-conical" class="w-5 h-5"></i>
                            </div>
                            <span class="card-title">Tests & Validation</span>
                        </div>
                    </div>
                    <div id="testResults" class="p-4 space-y-3 max-h-64 overflow-y-auto">
                        <div class="h-40 flex flex-col items-center justify-center text-center text-slate-500">
                            <i data-lucide="flask-conical" class="w-8 h-8 mb-3"></i>
                            <span class="font-medium text-slate-300 mb-1">Prêt pour les tests</span>
                            <p class="text-sm">Exécutez votre code pour voir les résultats</p>
                        </div>
                    </div>
                    <div class="p-4 border-t border-slate-800/50 flex flex-col sm:flex-row gap-3">
                        <button id="runAllTests" class="btn btn-primary flex-1">
                            <i data-lucide="play-circle" class="w-4 h-4"></i>
                            <span>Tester la solution</span>
                        </button>
                        <button id="submitChallenge" class="btn bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white flex-1">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <span>Soumettre</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script>
        function showTooltip(e) {
            const tooltip = this.getAttribute('data-tooltip');
            if (!tooltip) return;

            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip';
            tooltipEl.textContent = tooltip;

            // Positionnement
            const rect = this.getBoundingClientRect();
            tooltipEl.style.position = 'fixed';
            tooltipEl.style.left = `${rect.left + (rect.width / 2)}px`;
            tooltipEl.style.top = `${rect.top - 40}px`;
            tooltipEl.style.transform = 'translateX(-50%)';
            tooltipEl.style.zIndex = '1000';
            tooltipEl.style.pointerEvents = 'none';
            tooltipEl.classList.add('bg-slate-800', 'text-white', 'text-xs', 'px-2', 'py-1', 'rounded', 'shadow-lg', 'border', 'border-slate-700');

            document.body.appendChild(tooltipEl);

            // Stocker la référence pour pouvoir la supprimer
            this._tooltip = tooltipEl;
        }

        function hideTooltip() {
            if (this._tooltip) {
                this._tooltip.remove();
                this._tooltip = null;
            }
        }
    </script>

    <!-- Script principal -->
    <script src="/js/user/interfacechallenge.js" defer></script>
</body>

</html>