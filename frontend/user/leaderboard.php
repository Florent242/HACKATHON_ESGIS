<?php
// index.php - Page principale du leaderboard
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Classement en direct</title>
    
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="/css/styles/user/leaderboard.css">
    <?php require_once '../includes/user/head.php'; ?>
    
    <!-- JavaScript personnalisé -->
    <script defer src="/js/user/leaderboard.js"></script>
    
</head>
<body class="bg-gradient-to-br from-slate-900 to-blue-900 min-h-screen">

    <?php require_once("../includes/user/header.php"); ?>
    <!-- Container principal -->
    <div class="leaderboard-container max-w-7xl mx-auto p-4 md:p-6 lg:p-8" data-refresh="30s">
        
        <!-- En-tête -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Titre principal -->
                <div class="space-y-1">
                    <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
                        <i data-lucide="trophy" class="flex items-center justify-center stroke-current p-2 bg-[rgba(37,99,235,0.1)] border border-blue-800/50 rounded-xl w-14 h-14 text-blue-600"></i>
                        <span>Classement en direct</span>
                    </h1>
                    <p class="text-blue-200/80 text-sm pl-11">Suivez les performances des équipes en temps réel</p>
                </div>
                
                <!-- Informations -->
                <div class="bg-blue-950/50 rounded-lg p-3 border border-blue-800/50">
                    <div class="grid grid-cols-1 gap-2 text-sm">
                        <div class="flex items-center gap-2 text-blue-100">
                            <i data-lucide="calendar" class="w-4 h-4 text-blue-400"></i>
                            <span class="font-medium flex items-center gap-2">Événement: <span id="current-event" class="font-medium">Sélectionner un hackathon</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-blue-100">
                            <i data-lucide="layers" class="w-4 h-4 text-purple-400"></i>
                            <span class="font-medium flex items-center gap-2">Phase: <span id="current-phase" class="font-medium">Aucune phase</span></span>
                        </div>
                        <div class="flex items-center gap-2 text-blue-100">
                            <i data-lucide="clock" class="w-4 h-4 text-yellow-400"></i>
                            <span class="font-medium flex items-center gap-2">Période: <span id="countdown" class="font-mono font-medium">--:--:--</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sélecteurs -->
        <div class="bg-blue-950/40 rounded-xl p-5 border border-blue-800/30 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Sélecteur d'événement -->
                <div>
                    <label for="event-select" class="block text-sm font-medium text-blue-200 mb-1.5">
                        Sélectionnez un hackathon
                    </label>
                    <div class="relative">
                        <select id="event-select" class="w-full px-4 py-2.5 pr-10 text-sm border border-blue-800/50 rounded-lg focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 bg-blue-900/30 text-white transition-all duration-150 appearance-none cursor-pointer">
                            <option value="" disabled selected class="bg-blue-950 text-blue-300">Choisir un hackathon</option>
                            <option value="1" class="bg-blue-950 text-white hover:bg-blue-800 transition-all duration-150">HackSec CTF</option>
                            <option value="2" class="bg-blue-950 text-white hover:bg-blue-800 transition-all duration-150">HackDev</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-blue-400"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Sélecteur de phase -->
                <div>
                    <label for="phase-select" class="block text-sm font-medium text-blue-200 mb-1.5">
                        Sélectionnez une phase
                    </label>
                    <div class="relative">
                        <select id="phase-select" class="w-full px-4 py-2.5 pr-10 text-sm border border-blue-800/50 rounded-lg focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500 bg-blue-900/30 text-white transition-all duration-150 appearance-none cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed" disabled>
                            <option value="" disabled selected class="bg-blue-950 text-blue-300 hover:bg-blue-800 transition-all duration-150">Choisir une phase</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i data-lucide="chevron-down" class="w-4 h-4 text-blue-400"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Indicateur de chargement -->
            <div id="selector-loading" class="hidden mt-4 flex items-center gap-2 text-sm text-blue-300">
                <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-400"></div>
                <span>Chargement des phases...</span>
            </div>
        </div>
        
        <!-- Messages d'erreur -->
        <div id="error-message" class="hidden bg-red-900/30 border border-red-700 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-2 text-red-300">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
                <span class="font-medium">Erreur de chargement</span>
            </div>
            <p class="text-red-200 text-sm mt-1" id="error-text"></p>
        </div>
        
        <!-- Tableau du classement -->
        <div class="card-bg rounded-xl shadow-lg border border-gray-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    Classement
                </h2>
            </div>
            
            <!-- Container avec scroll horizontal pour mobile -->
            <div class="table-container overflow-x-auto">
                <table class="leaderboard w-full">
                    <thead class="bg-gray-800">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider min-w-[60px]">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="hash" class="w-4 h-4"></i>
                                    Rang
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider min-w-[200px]">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="users" class="w-4 h-4"></i>
                                    Équipe
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider min-w-[120px]">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="star" class="w-4 h-4"></i>
                                    Points
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-300 uppercase tracking-wider min-w-[180px]">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    Dernière soumission
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="leaderboard-body" class="bg-slate-900 divide-y divide-gray-700">
                        <!-- État initial : message d'attente -->
                        <tr id="waiting-state">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-4">
                                    <i data-lucide="search" class="w-12 h-12 text-gray-500"></i>
                                    <div>
                                        <p class="text-lg font-medium text-white">Sélectionnez un hackathon et une phase</p>
                                        <p class="text-sm">pour afficher le classement</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer avec informations de mise à jour -->
        <div class="mt-8 text-center text-sm text-gray-400">
            <div class="flex items-center justify-center gap-2 mb-2">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Mise à jour automatique toutes les 30 secondes</span>
            </div>
            <p>Dernière mise à jour: <span id="last-update" class="font-mono text-white">--:--:--</span></p>
        </div>
    </div>
    
    <!-- Templates pour les états du tableau -->
    <script id="loading-template" type="text/template">
        <tr>
            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-400"></div>
                    <div>
                        <p class="text-lg font-medium text-white">Chargement en cours...</p>
                        <p class="text-sm">Récupération des données du classement</p>
                    </div>
                </div>
            </td>
        </tr>
    </script>
    
    <script id="no-data-template" type="text/template">
        <tr>
            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                <div class="flex flex-col items-center gap-4">
                    <i data-lucide="inbox" class="w-12 h-12 text-gray-500"></i>
                    <div>
                        <p class="text-lg font-medium text-white">Aucune équipe classée</p>
                        <p class="text-sm">pour cette phase actuellement</p>
                    </div>
                </div>
            </td>
        </tr>
    </script>
    
    <script id="error-template" type="text/template">
        <tr>
            <td colspan="4" class="px-6 py-12 text-center text-red-400">
                <div class="flex flex-col items-center gap-4">
                    <i data-lucide="wifi-off" class="w-12 h-12"></i>
                    <div>
                        <p class="text-lg font-medium text-white">Erreur de connexion</p>
                        <p class="text-sm">Impossible de charger les données</p>
                    </div>
                </div>
            </td>
        </tr>
    </script>
    
</body>
</html>