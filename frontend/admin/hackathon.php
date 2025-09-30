<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/hackathon.css">
    <script src="/js/admin/hackathon.js"></script>
</head>

<body class="min-h-screen">
    <?php require_once '../includes/admin/header.php'; ?>
    <main class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white flex items-center gap-3">
                    <i data-lucide="laptop" class="w-8 h-8"></i>
                    Gestion des Hackathons
                </h1>
                <p class="text-gray-400 mt-2">Créez et gérez les hackathons de votre plateforme</p>
            </div>
            <button id="btnNewHackathon" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg flex items-center gap-2 transition-all shadow-lg hover:shadow-purple-500/50">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Nouveau Hackathon
            </button>
        </div>

        <!-- Filters & Search -->
        <div class="bg-[var(--background-light)] border border-[var(--border)] rounded-xl p-6 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="flex-1 relative">
                    <i data-lucide="search" class="w-5 h-5 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher par nom, thème..."
                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white pl-10 pr-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <select id="filterType" class="bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Tous les types</option>
                    <option value="dev">Développement</option>
                    <option value="ctf">CTF</option>
                </select>
                <select id="filterStatus" class="bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">Tous les statuts</option>
                    <option value="draft">Brouillon</option>
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                    <option value="ended">Terminé</option>
                </select>
                <button id="btnExportCSV" class="bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg hover:bg-[var(--background-lighter)] transition-all flex items-center gap-2">
                    <i data-lucide="download" class="w-5 h-5"></i>
                    Exporter CSV
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden bg-[var(--background-light)] border border-[var(--border)] rounded-xl p-12 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto"></div>
            <p class="text-gray-400 mt-4">Chargement des hackathons...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden bg-[var(--background-light)] border border-[var(--border)] rounded-xl p-12 text-center">
            <i data-lucide="laptop-minimal" class="w-16 h-16 text-gray-600 mx-auto mb-4"></i>
            <h3 class="text-xl font-semibold text-white mb-2">Aucun hackathon trouvé</h3>
            <p class="text-gray-400">Créez votre premier hackathon en cliquant sur "Nouveau Hackathon"</p>
        </div>

        <!-- Hackathons Table -->
        <div id="hackathonsContainer" class="bg-[var(--background-light)] border border-[var(--border)] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[var(--background)] border-b border-[var(--border)]">
                        <tr>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">ID</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Nom</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Thème</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Type</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Dates</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Statut</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Équipes</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Projets</th>
                            <th class="text-left text-gray-400 font-medium px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="hackathonsTableBody" class="divide-y divide-[var(--border)]">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal: Create/Edit Hackathon -->
    <div id="hackathonModal" class="hidden fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[var(--background-light)] border border-[var(--border)] rounded-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-[var(--background-light)] border-b border-[var(--border)] px-6 py-4 flex justify-between items-center z-10">
                <h2 id="modalTitle" class="text-2xl font-bold text-white flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-6 h-6"></i>
                    Nouveau Hackathon
                </h2>
                <button id="btnCloseModal" class="text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form id="hackathonForm" class="p-6">
                <input type="hidden" id="hackathonId">

                <!-- Navigation des onglets -->
                <div class="border-b border-[var(--border)] mb-6 -mx-6 px-6">
                    <nav class="flex space-x-6" aria-label="Tabs">
                        <button type="button"
                            data-tab="tab1"
                            class="tab-button flex items-center flex-row py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-400 hover:text-white hover:border-gray-300 transition-all duration-200 ease-in-out active">
                            <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                            Informations Générales
                        </button>
                        <button type="button"
                            data-tab="tab2"
                            class="tab-button flex items-center flex-row py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-400 hover:text-white hover:border-gray-300 transition-all duration-200 ease-in-out">
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                            Configuration Avancée
                        </button>
                    </nav>
                </div>

                <!-- Contenu des onglets -->
                <div class="space-y-8">
                    <!-- Onglet Informations Générales -->
                    <div id="general-tab" class="tab-content space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Colonne de gauche -->
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Nom du hackathon <span class="text-red-500">*</span></label>
                                    <input type="text" id="hackathonName" required
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Slug</label>
                                    <input type="text" id="hackathonSlug"
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    <p class="text-xs text-gray-400 mt-1.5">Laissez vide pour générer automatiquement</p>
                                </div>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Thème</label>
                                    <input type="text" id="hackathonTheme"
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>

                            <!-- Colonne de droite -->
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Type <span class="text-red-500">*</span></label>
                                    <select id="hackathonType" required
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        <option value="ctf">CTF</option>
                                        <option value="dev">Développement</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-gray-300 font-medium mb-2">Statut <span class="text-red-500">*</span></label>
                                        <select id="hackathonStatus" required
                                            class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                            <option value="draft">Brouillon</option>
                                            <option value="active" selected>Actif</option>
                                            <option value="inactive">Inactif</option>
                                            <option value="ended">Terminé</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-gray-300 font-medium mb-2">Visibilité <span class="text-red-500">*</span></label>
                                        <select id="hackathonVisibility" required
                                            class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                            <option value="public" selected>Public</option>
                                            <option value="private">Privé</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description en pleine largeur -->
                        <div>
                            <label class="block text-gray-300 font-medium mb-2">Description <span class="text-red-500">*</span></label>
                            <textarea id="hackathonDescription" required rows="3"
                                class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                        </div>
                    </div>

                    <!-- Onglet Configuration Avancée -->
                    <div id="advanced-tab" class="tab-content hidden space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Dates importantes -->
                            <div class="space-y-5">
                                <h3 class="text-lg font-semibold text-white border-b border-[var(--border)] pb-2">Dates importantes</h3>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Date et heure de début <span class="text-red-500">*</span></label>
                                    <input type="datetime-local" id="hackathonStartDate" required
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Date et heure de fin <span class="text-red-500">*</span></label>
                                    <input type="datetime-local" id="hackathonEndDate" required
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Date limite d'inscription</label>
                                    <input type="datetime-local" id="hackathonRegistrationDeadline"
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>

                            <!-- Configuration des équipes -->
                            <div class="space-y-5">
                                <h3 class="text-lg font-semibold text-white border-b border-[var(--border)] pb-2">Configuration des équipes</h3>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Nombre maximum d'équipes <span class="text-red-500">*</span></label>
                                    <input type="number" id="hackathonMaxTeams" min="0" value="10" required
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-300 font-medium mb-2">Membres min/équipe</label>
                                        <input type="number" id="hackathonMinMembers" min="1" value="2"
                                            class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-gray-300 font-medium mb-2">Membres max/équipe <span class="text-red-500">*</span></label>
                                        <input type="number" id="hackathonMaxMembers" min="1" value="4" required
                                            class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-300 font-medium mb-2">Lieu</label>
                                    <input type="text" id="hackathonLocation"
                                        class="w-full bg-[var(--background)] border border-[var(--border)] text-white px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                                </div>
                            </div>
                        </div>

                        <!-- Contenu détaillé -->
                        <div class="space-y-5">
                            <h3 class="text-lg font-semibold text-white border-b border-[var(--border)] pb-2">Contenu détaillé</h3>

                            <!-- Règles -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-gray-300 font-medium">Règles</label>
                                    <button type="button" onclick="addRule()" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une règle
                                    </button>
                                </div>
                                <div id="rules-container" class="space-y-3"></div>
                                <input type="hidden" id="hackathonRules" name="rules">
                            </div>

                            <!-- Critères d'éligibilité -->
                            <div class="mt-6">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-gray-300 font-medium">Critères d'éligibilité</label>
                                    <button type="button" onclick="addEligibilityCriterion()" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Ajouter un critère
                                    </button>
                                </div>
                                <div id="eligibility-container" class="space-y-3"></div>
                                <input type="hidden" id="hackathonEligibility" name="eligibility_criteria">
                            </div>

                            <!-- Récompenses -->
                            <div class="mt-6">
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-gray-300 font-medium">Récompenses</label>
                                    <button type="button" onclick="addPrize()" class="text-sm text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Ajouter une récompense
                                    </button>
                                </div>
                                <div id="prizes-container" class="space-y-3"></div>
                                <input type="hidden" id="hackathonPrizes" name="prizes">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 mt-8 border-t border-[var(--border)]">
                    <button type="button" id="btnCancelModal" class="px-6 py-2.5 bg-[var(--background)] border border-[var(--border)] text-white rounded-lg hover:bg-[var(--background-lighter)] transition-all">
                        Annuler
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-all shadow-lg hover:shadow-purple-500/50 flex items-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        <span id="submitButtonText">Créer</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Script pour gérer les onglets -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Variables globales
            let currentTab = 0;
            const tabContents = document.querySelectorAll('.tab-content');
            const tabButtons = document.querySelectorAll('.tab-button');

            // Fonction pour valider l'onglet actuel
            function validateCurrentTab() {
                const currentTabContent = tabContents[currentTab];
                const requiredFields = currentTabContent.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;
                let firstInvalidField = null;

                requiredFields.forEach(field => {
                    if (!field.checkValidity() && !firstInvalidField) {
                        isValid = false;
                        firstInvalidField = field;
                    }
                });

                return {
                    isValid,
                    field: firstInvalidField
                };
            }

            function switchTab(tabIndex) {
                // Valider l'onglet actuel avant de changer
                if (tabIndex > currentTab) {
                    const {
                        isValid,
                        field
                    } = validateCurrentTab();
                    if (!isValid && field) {
                        field.reportValidity();
                        return false;
                    }
                }

                // Mettre à jour l'interface
                tabButtons.forEach((btn, index) => {
                    const isActive = index === tabIndex;
                    btn.classList.toggle('border-purple-500', isActive);
                    btn.classList.toggle('text-white', isActive);
                    btn.classList.toggle('bg-purple-600/20', isActive);
                    btn.classList.toggle('text-gray-300', !isActive);
                });

                // Afficher le contenu de l'onglet
                tabContents.forEach((content, index) => {
                    content.classList.toggle('hidden', index !== tabIndex);
                });

                currentTab = tabIndex;
                return true;
            }

            // Gestion des clics sur les onglets
            tabButtons.forEach((button, index) => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetTab = button.getAttribute('data-tab');
                    const targetIndex = Array.from(tabButtons).findIndex(btn => btn.getAttribute('data-tab') === targetTab);
                    switchTab(targetIndex);
                });
            });

            // Initialisation
            if (tabButtons.length > 0) {
                switchTab(0);
            }
        });
    </script>

</body>

</html>