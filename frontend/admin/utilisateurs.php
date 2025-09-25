<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau d'administration - Plateforme de Hackathon</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/utilisateurs.css">
    <script defer src="/js/admin/user.js"></script>

</head>

<body class="bg-slate-50 font-inter">
    <?php require_once '../includes/admin/header.php'; ?>
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header avec titre et actions principales -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-6 mb-8">
                <div class="space-y-2">
                    <h1 class="text-3xl font-bold text-slate-400 tracking-tight">Gestion des Utilisateurs</h1>
                    <p class="text-slate-300">Gérez et administrez tous les utilisateurs de la plateforme</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button id="bulkActionsBtn"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl hover:bg-amber-100 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 disabled:hover:scale-100"
                        disabled>
                        <i data-lucide="check-square" class="w-4 h-4"></i>
                        <span>Actions (0)</span>
                    </button>
                    <button id="exportUsersBtn"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-white text-slate-700 border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 transition-all duration-200 transform hover:scale-105">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Exporter</span>
                    </button>
                    <button id="createNotificationBtn"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:bg-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i data-lucide="bell-plus" class="w-4 h-4 mr-2"></i>
                        Créer une notification
                    </button>

                    <button id="addUserBtn"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Ajouter utilisateur</span>
                    </button>
                </div>
            </div>

            <!-- Statistiques modernisées avec gradients -->
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8" id="userStats">
                <div class="relative overflow-hidden bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="absolute inset-0 bg-white/10"></div>
                    <div class="relative p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold mb-1" id="totalUsers">-</div>
                                <div class="text-blue-100 text-sm font-medium">Total</div>
                            </div>
                            <div class="p-3 bg-white/20 rounded-xl">
                                <i data-lucide="users" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 via-green-600 to-teal-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="absolute inset-0 bg-white/10"></div>
                    <div class="relative p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold mb-1" id="activeUsers">-</div>
                                <div class="text-green-100 text-sm font-medium">Actifs</div>
                            </div>
                            <div class="p-3 bg-white/20 rounded-xl">
                                <i data-lucide="user-check" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-slate-500 via-gray-600 to-slate-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="absolute inset-0 bg-white/10"></div>
                    <div class="relative p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold mb-1" id="inactiveUsers">-</div>
                                <div class="text-slate-100 text-sm font-medium">Inactifs</div>
                            </div>
                            <div class="p-3 bg-white/20 rounded-xl">
                                <i data-lucide="user-x" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 via-yellow-600 to-orange-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="absolute inset-0 bg-white/10"></div>
                    <div class="relative p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold mb-1" id="suspendedUsers">-</div>
                                <div class="text-amber-100 text-sm font-medium">Suspendus</div>
                            </div>
                            <div class="p-3 bg-white/20 rounded-xl">
                                <i data-lucide="user-minus" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-red-500 via-rose-600 to-red-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="absolute inset-0 bg-white/10"></div>
                    <div class="relative p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold mb-1" id="bannedUsers">-</div>
                                <div class="text-red-100 text-sm font-medium">Bannis</div>
                            </div>
                            <div class="p-3 bg-white/20 rounded-xl">
                                <i data-lucide="user-x" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden bg-gradient-to-br from-cyan-500 via-blue-600 to-cyan-700 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="absolute inset-0 bg-white/10"></div>
                    <div class="relative p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-2xl font-bold mb-1" id="newUsersThisWeek">-</div>
                                <div class="text-cyan-100 text-sm font-medium">Nouveaux</div>
                            </div>
                            <div class="p-3 bg-white/20 rounded-xl">
                                <i data-lucide="user-plus" class="w-6 h-6"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres améliorés -->
        <div class="bg-slate-800 rounded-2xl shadow-sm border border-slate-700/60 mb-8 overflow-hidden">
            <div class="p-6 border-b border-slate-700/60">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-slate-700/50 rounded-lg">
                        <i data-lucide="filter" class="w-5 h-5 text-blue-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Filtres de recherche</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label for="searchInput" class="block text-sm font-medium text-slate-300">Rechercher</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                            </div>
                            <input type="text" id="searchInput"
                                class="block w-full pl-11 pr-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-white placeholder-slate-400"
                                placeholder="Nom, email, ID...">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="roleFilter" class="block text-sm font-medium text-slate-300">Rôle</label>
                        <div class="relative">
                            <select id="roleFilter"
                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-white appearance-none">
                                <option value="" class="bg-slate-800">Tous les rôles</option>
                                <option value="participant" class="bg-slate-800">Participant</option>
                                <option value="organizer" class="bg-slate-800">Organisateur</option>
                                <option value="judge" class="bg-slate-800">Juge</option>
                                <option value="admin" class="bg-slate-800">Administrateur</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="statusFilter" class="block text-sm font-medium text-slate-300">Statut</label>
                        <div class="relative">
                            <select id="statusFilter"
                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-white appearance-none">
                                <option value="" class="bg-slate-800">Tous les statuts</option>
                                <option value="active" class="bg-slate-800">Actif</option>
                                <option value="inactive" class="bg-slate-800">Inactif</option>
                                <option value="suspended" class="bg-slate-800">Suspendu</option>
                                <option value="banned" class="bg-slate-800">Banni</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="teamFilter" class="block text-sm font-medium text-slate-300">Équipe</label>
                        <div class="relative">
                            <select id="teamFilter"
                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 text-white appearance-none">
                                <option value="" class="bg-slate-800">Toutes les équipes</option>
                                <!-- Les options seront ajoutées dynamiquement -->
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <button id="resetFiltersBtn"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm text-blue-600 hover:text-blue-900 transition-colors duration-200">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                        <span>Réinitialiser les filtres</span>
                    </button>
                    <div class="text-sm text-slate-500" id="filterResults">
                        <!-- Résultats de filtrage -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Table modernisée -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left">
                                <input type="checkbox" id="selectAll"
                                    class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500 focus:ring-2">
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Utilisateur</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Rôle</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Statut</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Équipe</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Dernière connexion</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody" class="bg-white divide-y divide-slate-100">
                        <!-- Loader initial -->
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"><i data-lucide="loader"></i></div>
                                    <p class="text-slate-500 font-medium">Chargement des utilisateurs...</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer avec pagination améliorée -->
            <div class="bg-slate-600/50 px-6 py-4 border-t border-slate-200">
                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-2 text-sm text-slate-400" id="paginationContainer">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span id="tableInfo">Affichage de 0 à 0 sur 0 utilisateur(s)</span>
                    </div>
                    <nav class="flex items-center space-x-1" id="pagination">
                        <!-- Pagination dynamique -->
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal utilisateur modernisée -->
    <div id="userModal" class="fixed inset-0 z-1000 hidden">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm transition-opacity duration-300"></div>
        <div id="overlayModal" class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-4xl bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl shadow-2xl transform transition-all duration-300 animate-fade-in overflow-hidden">
                    <!-- Effet de texture subtile -->
                    <div class="absolute inset-0 opacity-5 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgwLDAsMCwwLjAzKSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSIvPjwvc3ZnPg==')]"></div>

                    <!-- Conteneur principal -->
                    <div class="relative z-10">
                        <!-- Header -->
                        <div class="flex items-center justify-between p-6 border-b border-slate-700/60 bg-slate-800/50 backdrop-blur-sm">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-blue-500/20 rounded-xl">
                                    <i data-lucide="user-cog" class="w-6 h-6 text-blue-400"></i>
                                </div>
                                <h3 id="modal-title" class="text-2xl font-bold text-white">Ajouter un utilisateur</h3>
                            </div>
                            <button id="closeModal" class="p-2 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-200">
                                <i data-lucide="x" class="w-6 h-6"></i>
                            </button>
                        </div>

                        <!-- Tabs -->
                        <div class="border-b border-slate-700">
                            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                                <button type="button" id="profile-tab"
                                    class="tab-button active py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                        <span>Profil</span>
                                    </div>
                                </button>
                                <button type="button" id="security-tab"
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200">
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="shield" class="w-4 h-4"></i>
                                        <span>Sécurité</span>
                                    </div>
                                </button>
                                <button type="button" id="activity-tab"
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 hidden">
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="activity" class="w-4 h-4"></i>
                                        <span>Activité</span>
                                    </div>
                                </button>
                            </nav>
                        </div>

                        <!-- Content -->
                        <form id="userForm" class="p-6">
                            <input type="hidden" id="userId" name="userId">

                            <!-- Profile Tab -->
                            <div id="profile" class="tab-content">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label for="username" class="block text-sm font-semibold text-slate-300">
                                            Nom d'utilisateur <span class="text-red-400">*</span>
                                        </label>
                                        <input type="text" name="username" id="username" required
                                            class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    </div>

                                    <div class="space-y-2">
                                        <label for="email" class="block text-sm font-semibold text-slate-300">
                                            Email <span class="text-red-400">*</span>
                                        </label>
                                        <input type="email" name="email" id="email" required
                                            class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    </div>

                                    <div class="space-y-2">
                                        <label for="fullName" class="block text-sm font-semibold text-slate-300">Nom complet</label>
                                        <input type="text" name="fullName" id="fullName"
                                            class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                    </div>

                                    <div class="space-y-2">
                                        <label for="role" class="block text-sm font-semibold text-slate-300">
                                            Rôle <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select id="role" name="role" required
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none">
                                                <option value="participant" class="bg-slate-800">Participant</option>
                                                <option value="organisateur" class="bg-slate-800">Organisateur</option>
                                                <option value="admin" class="bg-slate-800">Administrateur</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="status" class="block text-sm font-semibold text-slate-300">
                                            Statut <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select id="status" name="status" required
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none">
                                                <option value="active" class="bg-slate-800">Actif</option>
                                                <option value="inactive" class="bg-slate-800">Inactif</option>
                                                <option value="suspended" class="bg-slate-800">Suspendu</option>
                                                <option value="banned" class="bg-slate-800">Banni</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="school" class="block text-sm font-semibold text-slate-300">
                                            Ecole <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select id="school" name="school" required
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none">
                                                <option value="">Selectionner une ecole</option>

                                            </select>
                                            <div class="schoolError absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="number" class="block text-sm font-semibold text-slate-300">
                                            Numéro de téléphone <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="tel"
                                                placeholder="+XXX XX XX XX XX XX"
                                                id="number" name="number" required
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none">
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label for="study_level" class="block text-sm font-semibold text-slate-300">
                                            Niveau d'étude <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <select id="study_level" name="study_level" required
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 appearance-none">
                                                <option value="">Selectionner un niveau d'étude</option>
                                                <option value="bac">Bac</option>
                                                <option value="licence1">licence1</option>
                                                <option value="licence2">licence2</option>
                                                <option value="licence3">licence3</option>
                                                <option value="master1">master1</option>
                                                <option value="master2">master2</option>
                                                <option value="doctorat">doctorat</option>
                                            </select>
                                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2 space-y-2">
                                        <label for="bio" class="block text-sm font-semibold text-slate-300">Bio</label>
                                        <textarea id="bio" name="bio" rows="3"
                                            class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none"
                                            placeholder="Description de l'utilisateur..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Tab -->
                            <div id="security" class="tab-content hidden">
                                <div class="space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div class="space-y-2">
                                            <label for="password" class="block text-sm font-semibold text-slate-300">Nouveau mot de passe</label>
                                            <input type="password" name="password" id="password" minlength="8"
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                            <p class="text-xs text-slate-500">Minimum 8 caractères</p>
                                        </div>

                                        <div class="space-y-2">
                                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-300">Confirmer le mot de passe</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation"
                                                class="block w-full px-4 py-3 bg-slate-700/50 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="flex items-start space-x-3 p-4 bg-blue-500/20 rounded-xl border border-blue-500/50">
                                            <input id="twoFactorEnabled" name="twoFactorEnabled" type="checkbox"
                                                class="w-5! h-5! text-blue-400 bg-white border-slate-300 rounded focus:ring-blue-500 focus:ring-2 mt-0.5">
                                            <div class="flex-1">
                                                <label for="twoFactorEnabled" class="block text-sm font-medium text-slate-300">
                                                    Activer l'authentification à deux facteurs
                                                </label>
                                                <p class="text-sm text-slate-500 mt-1">L'utilisateur devra configurer l'authentification 2FA à sa prochaine connexion.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start space-x-3 p-4 bg-amber-500/20 rounded-xl border border-amber-500/50">
                                            <input id="forcePasswordReset" name="forcePasswordReset" type="checkbox"
                                                class="w-5! h-5! text-amber-400 bg-white border-slate-300 rounded focus:ring-amber-500 focus:ring-2 mt-0.5">
                                            <div class="flex-1">
                                                <label for="forcePasswordReset" class="block text-sm font-medium text-slate-300">
                                                    Forcer la réinitialisation du mot de passe
                                                </label>
                                                <p class="text-sm text-slate-500 mt-1">L'utilisateur devra définir un nouveau mot de passe à sa prochaine connexion.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Tab -->
                            <div id="activity" class="tab-content hidden">
                                <div id="userActivityFeed" class="space-y-4 max-h-96 overflow-y-auto scrollbar-thin pr-2">
                                    <div class="flex flex-col items-center justify-center py-12 text-center">
                                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mb-4"></div>
                                        <p class="text-slate-500 font-medium">Chargement de l'activité...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-700">
                                <button type="button" id="cancelModalBtn"
                                    class="px-6 py-2 text-slate-400 hover:text-white hover:bg-slate-700/50 rounded-xl transition-all duration-200">
                                    Annuler
                                </button>
                                <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 disabled:opacity-50">
                                    <div id="saveSpinner" class="hidden! animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></div>
                                    <span id="saveButtonText">Enregistrer</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="confirmDeleteModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity duration-300"></div>
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl transform transition-all duration-300 animate-fade-in">
                    <!-- Header -->
                    <div class="p-6 text-center">
                        <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="trash-2" class="w-8 h-8 text-red-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-900 mb-2">Supprimer l'utilisateur</h3>
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0"></i>
                                <div class="text-left">
                                    <p class="text-sm font-medium text-red-800 mb-1">Action irréversible</p>
                                    <p class="text-sm text-red-700">Cette action supprimera définitivement l'utilisateur et toutes ses données.</p>
                                </div>
                            </div>
                        </div>
                        <p id="deleteUserInfo" class="text-slate-600 text-sm"></p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-slate-200">
                        <button type="button" id="cancelDeleteBtn"
                            class="px-4 py-2 text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all duration-200">
                            Annuler
                        </button>
                        <button type="button" id="confirmDeleteBtn"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 transition-all duration-200">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            <span>Supprimer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal d'actions groupées -->
    <div id="bulkActionsModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/30 backdrop-blur-sm transition-opacity duration-300"></div>
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl transform transition-all duration-300 animate-fade-in">
                    <!-- Header -->
                    <div class="p-6 border-b border-slate-200">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-amber-100 rounded-xl">
                                <i data-lucide="zap" class="w-6 h-6 text-amber-600"></i>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900">Actions groupées</h3>
                        </div>
                        <p id="bulkSelectionInfo" class="text-slate-600 mt-2"></p>
                    </div>

                    <!-- Content -->
                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <label for="bulkAction" class="block text-sm font-semibold text-slate-700">Action à effectuer</label>
                            <div class="relative">
                                <select id="bulkAction"
                                    class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white appearance-none">
                                    <option value="">Sélectionner une action</option>
                                    <option value="send_notification">Envoyer une notification</option>
                                    <option value="activate">Activer</option>
                                    <option value="deactivate">Désactiver</option>
                                    <option value="suspend">Suspendre</option>
                                    <option value="ban">Bannir</option>
                                    <option value="change_role">Changer le rôle</option>
                                    <option value="delete">Supprimer définitivement</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                </div>
                            </div>
                        </div>

                        <div id="bulkRoleContainer" class="hidden space-y-2">
                            <label for="bulkRole" class="block text-sm font-semibold text-slate-700">Nouveau rôle</label>
                            <div class="relative">
                                <select id="bulkRole"
                                    class="block w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white appearance-none">
                                    <option value="participant">Participant</option>
                                    <option value="organizer">Organisateur</option>
                                    <option value="admin">Administrateur</option>
                                    <option value="judge">Juge</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                </div>
                            </div>
                        </div>

                        <div id="bulkActionWarning" class="hidden p-4 bg-amber-50 border border-amber-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0"></i>
                                <p id="bulkActionWarningText" class="text-sm text-amber-800"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 p-6 border-t border-slate-200">
                        <button type="button" id="cancelBulkBtn"
                            class="px-4 py-2 text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all duration-200">
                            Annuler
                        </button>
                        <button type="button" id="confirmBulkAction"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-blue-600/50" disabled>
                            <span id="bulkSpinner" class="hidden">
                                <i data-lucide="loader" class="w-4 h-4 animate-spin"></i>
                            </span>
                            <span id="bulkActionText">Confirmer</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de notification -->
    <div id="notificationModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"></div>
        <div class="fixed inset-0 mx-auto overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl shadow-2xl transform transition-all duration-300 border border-slate-700/50">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-blue-500/20">
                                    <i data-lucide="bell" class="w-5 h-5 text-blue-400"></i>
                                </div>
                                <h3 class="ml-3 text-lg font-semibold text-white">
                                    Nouvelle notification
                                </h3>
                            </div>
                            <button type="button" id="closeNotificationModal"
                                class="rounded-full p-1.5 text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors duration-200">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p class="ml-13 mt-1 text-sm text-slate-400">
                            <span id="notificationRecipientsCount" class="font-medium text-blue-400">0</span> destinataire(s)
                        </p>
                    </div>

                    <!-- Contenu -->
                    <form id="notificationForm" class="p-6 space-y-6">
                        <input type="hidden" id="notificationRecipients" name="recipients">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Type de notification -->
                            <div class="space-y-2">
                                <label for="notificationType" class="block text-sm font-medium text-slate-300">
                                    Type de notification <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="notificationType" name="type"
                                        class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm appearance-none">
                                        <option value="info" class="bg-slate-800">Information</option>
                                        <option value="success" class="bg-slate-800">Succès</option>
                                        <option value="warning" class="bg-slate-800">Avertissement</option>
                                        <option value="error" class="bg-slate-800">Erreur</option>
                                        <option value="announcement" class="bg-slate-800">Annonce</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Destinataires -->
                            <div class="space-y-2" id="scopeSection">
                                <label for="notificationScope" class="block text-sm font-medium text-slate-300">
                                    Destinataires <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="notificationScope" name="scope"
                                        class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm appearance-none">
                                        <option value="user">Utilisateur spécifique</option>
                                        <option value="selected">Sélection d'utilisateurs</option>
                                        <option value="team">Équipe</option>
                                        <option value="hackathon">Hackathon</option>
                                        <option value="global">Tous les utilisateurs</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>

                                <!-- Message d'information pour la sélection -->
                                <p id="notificationRecipientsInfo" class="text-sm text-blue-400 hidden flex items-center mt-1">
                                    <i data-lucide="info" class="w-4 h-4 mr-1"></i>
                                    <span></span>
                                </p>
                            </div>
                        </div>

                        <!-- Champs dynamiques -->
                        <div id="dynamicFields" class="space-y-4">
                            <!-- Champs utilisateur -->
                            <div id="userField" class="hidden">
                                <label class="block text-sm font-medium text-slate-300 mb-1">
                                    Utilisateur <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="notificationUser" name="user_id"
                                        class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm appearance-none">
                                        <!-- Les utilisateurs seront chargés dynamiquement -->
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Champs équipe -->
                            <div id="teamField" class="hidden">
                                <label class="block text-sm font-medium text-slate-300 mb-1">
                                    Équipe <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="notificationTeam" name="team_id"
                                        class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm appearance-none">
                                        <!-- Les équipes seront chargées dynamiquement -->
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Champs hackathon -->
                            <div id="hackathonField" class="hidden">
                                <label class="block text-sm font-medium text-slate-300 mb-1">
                                    Hackathon <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select id="notificationHackathon" name="hackathon_id"
                                        class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm appearance-none">
                                        <!-- Les hackathons seront chargés dynamiquement -->
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i data-lucide="trophy" class="w-4 h-4 text-slate-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Actions</label>
                            <div id="notificationActions" class="space-y-3">
                                <!-- Les actions seront ajoutées ici dynamiquement -->
                            </div>
                            <button type="button" id="addActionBtn"
                                class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-blue-100 bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Ajouter une action
                            </button>
                        </div>

                        <!-- Titre -->
                        <div class="space-y-2">
                            <label for="notificationTitle" class="block text-sm font-medium text-slate-300">
                                Titre <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="notificationTitle" name="title"
                                    class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Titre de la notification" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <i data-lucide="type" class="w-4 h-4 text-slate-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="space-y-2">
                            <label for="notificationMessage" class="block text-sm font-medium text-slate-300">
                                Message <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <textarea id="notificationMessage" name="message" rows="4"
                                    class="block w-full pl-3 pr-10 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Contenu de la notification" required></textarea>
                                <div class="absolute top-3 right-3">
                                    <i data-lucide="message-square" class="w-4 h-4 text-slate-400"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="flex items-center">
                                <input id="importantNotification" name="important" type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-700 rounded bg-slate-800">
                                <label for="importantNotification" class="ml-2 block text-sm text-slate-300">
                                    Notification importante
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input id="sendEmailNotification" name="send_email" type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-700 rounded bg-slate-800">
                                <label for="sendEmailNotification" class="ml-2 block text-sm text-slate-300">
                                    Envoyer par email
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="pt-4">
                            <div class="flex justify-end space-x-3">
                                <button type="button" id="cancelNotificationBtn"
                                    class="px-4 py-2.5 text-sm font-medium rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Annuler
                                </button>
                                <button type="submit" id="sendNotificationBtn"
                                    class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                                    Envoyer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates -->
    <template id="userRowTemplate">
        <tr class="bg-slate-800! hover:bg-slate-700/80! transition-colors! duration-150! border-b! border-slate-700!">
            <td class="px-6 py-4 whitespace-nowrap">
                <input type="checkbox" class="user-checkbox w-4 h-4 text-blue-500 bg-slate-700 border-slate-600 rounded focus:ring-blue-500 focus:ring-2">
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <img class="w-12 h-12 rounded-full object-cover user-avatar ring-2 ring-slate-700 shadow-sm" src="" alt="">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-slate-800 user-status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-white user-name"></div>
                        <div class="text-sm text-slate-400 user-email"></div>
                        <div class="text-xs text-slate-500 user-id"></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium user-role-badge"></span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium user-status-badge"></span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                <span class="user-team">-</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-300">
                <div class="user-last-login font-medium">-</div>
                <div class="text-xs text-slate-400 user-last-ip"></div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="relative inline-block text-left dropdown">
                    <button type="button" class="dropdown-toggle p-2 hover:bg-slate-700/50 transition-all duration-200 inline-flex justify-center w-8 h-8 rounded-full text-slate-400 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="user-menu"
                        aria-expanded="false" aria-haspopup="true">
                        <i data-lucide="more-vertical" class="w-5 h-5"></i>
                    </button>
                    <div class="dropdown-menu-li hidden absolute right-0 z-10 mt-2 w-56 origin-top-right bg-slate-800 rounded-lg shadow-lg ring-1 ring-slate-700 ring-opacity-100 border border-slate-700 animate-fade-in" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">
                        <div class="py-1" role="menu">
                            <button class="edit-user flex items-center w-full px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 transition-colors duration-150" role="menuitem">
                                <i data-lucide="edit-3" class="w-4 h-4 mr-3 text-blue-400"></i>
                                Modifier
                            </button>
                            <button class="view-activity flex items-center w-full px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 transition-colors duration-150" role="menuitem">
                                <i data-lucide="activity" class="w-4 h-4 mr-3 text-cyan-400"></i>
                                Voir l'activité
                            </button>
                            <div class="my-1 border-t border-slate-700"></div>
                            <button class="reset-password flex items-center w-full px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 transition-colors duration-150" role="menuitem">
                                <i data-lucide="key" class="w-4 h-4 mr-3 text-amber-400"></i>
                                Réinitialiser mot de passe
                            </button>
                            <button class="toggle-status flex items-center w-full px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 transition-colors duration-150" role="menuitem">
                                <i data-lucide="user-x" class="w-4 h-4 mr-3 text-red-400"></i>
                                Désactiver
                            </button>
                            <button class="send-notification flex items-center w-full px-4 py-2 text-sm text-slate-300 hover:bg-slate-700/50 transition-colors duration-150" role="menuitem">
                                <i data-lucide="message-square" class="w-4 h-4 mr-3 text-red-400"></i>
                                Envoyer une notification
                            </button>
                            <div class="my-1 border-t border-slate-700"></div>
                            <button class="delete-user flex items-center w-full px-4 py-2 text-sm text-red-400 hover:bg-red-500/10 transition-colors duration-150" role="menuitem">
                                <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </template>

    <template id="activityItemTemplate">
        <div class="activity-item flex items-start space-x-4 p-4 hover:bg-slate-700/50 rounded-xl transition-all duration-200 border-b border-slate-700 last:border-0">
            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-sm">
                <i class="w-5 h-5" data-icon></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <p class="text-sm font-semibold text-white" data-title></p>
                    <span class="text-xs text-slate-400 whitespace-nowrap ml-3" data-time></span>
                </div>
                <p class="text-sm text-slate-300 mt-1" data-details></p>
                <div class="flex items-center space-x-4 mt-2 text-xs text-slate-400">
                    <span class="inline-flex items-center" data-ip>
                        <i data-lucide="globe" class="w-3 h-3 mr-1.5 text-blue-400"></i>
                        <span class="text-slate-300" data-ip-text></span>
                    </span>
                    <span class="inline-flex items-center" data-device>
                        <i data-lucide="monitor" class="w-3 h-3 mr-1.5 text-blue-400"></i>
                        <span class="text-slate-300" data-device-text></span>
                    </span>
                </div>
            </div>
        </div>
    </template>

    <!-- Notification Toast -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>


</body>

</html>