import { createEle } from "/js/dom.js";

// Configuration de base
const API_BASE_URL = '/api';
const CHALLENGE_ELEMENTS = {
    // Filtres et recherche
    filterGroups: ".filter-buttons[data-type]",
    clearFiltersBtn: ".clear-filters",
    searchInput: '.search-input-wrapper input',

    // Conteneurs
    cardsContainer: '.challenge-grid',
    topHackersList: "#top-hackers",

    // Tri
    sortBtn: '.sort-btn',
    sortOptions: '.sort-option',

    // Modale
    modal: "#challenge-modal",
    closeButton: "#close-modal",
    openButtons: ".hack-now",

    // Stats
    solvesCount: "#solves-count",

    // État vide
    hackerListEmptyState: "#hacker-list-empty-state",
    challengeEmptyState: "#challenges-empty-state"
};

// Fonction utilitaire pour gérer les erreurs
function handleError(title = 'Une erreur est survenue', error = null, type = 'error') {
    console.error(title, error);
    showNotification(title, error, type);
    // Vous pouvez ajouter ici une notification à l'utilisateur
}

// Fonction utilitaire pour les requêtes API
async function apiRequest(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE_URL}${endpoint}`, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers
            }
        });

        if (!response.ok) {
            throw new Error(`Erreur API: ${response.status} ${response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        handleError('Erreur lors de la requête API', error);
        throw error;
    }
}

// Fonction utilitaire pour vérifier la participation au hackathon
async function checkHackathonAccess(hackathonId) {
    const response = await apiRequest(`/check-participation`, {
        method: 'POST',
        body: JSON.stringify({
            hackathon_id: hackathonId,
            csrf_token: document.querySelector('meta[name="csrf-token"]').content
        })
    });

    console.log(response);
    if (!response.success) {
        return {
            success: false,
            message: response.message || 'Erreur lors de la vérification d\'accès au hackathon',
            status: response.status
        };
    }

    return {
        success: true,
        message: response.message || 'Accès autorisé',
        status: response.status
    };
}

// Fonction pour charger les challenges
async function loadChallenges() {
    try {
        const userId = await getUserId();
        const data = await apiRequest(`/challenges/ctf/1/${userId}`);
        renderChallenges(data.data || []);
        console.log('challenge data', data);
    } catch (error) {
        handleError('Erreur lors du chargement des challenges', error);
    }
}

// Fonction pour afficher les challenges
function renderChallenges(challenges) {
    const container = document.querySelector(CHALLENGE_ELEMENTS.cardsContainer);
    if (!container) return;

    if (!challenges || !Array.isArray(challenges) || challenges.length === 0) {
        document.querySelector(CHALLENGE_ELEMENTS.challengeEmptyState).classList.remove('hidden');
        document.querySelector(CHALLENGE_ELEMENTS.challengeEmptyState).classList.add('flex');
        return;
    }

    container.innerHTML = '';

    challenges.forEach(challenge => {
        const card = document.createElement('div');
        card.classList.add('cyber-card', 'flex', 'flex-col', 'justify-between', 'w-full', 'mx-auto', 'bg-[#0f172a]', 'text-white', 'p-6', 'rounded-2xl', 'shadow-lg', 'border', 'border-slate-700', 'space-y-4', 'cursor-pointer', 'hover:shadow-xl', 'transition-shadow', 'transform', 'duration-300', 'ease-in-out');
        // card.setAttribute('data-title', challenge.title || '');
        // card.setAttribute('data-hackers', challenge.hackers || '0');
        // card.setAttribute('data-description', challenge.description || '');
        // card.setAttribute('data-type', challenge.type || '');
        // card.setAttribute('data-difficulty', challenge.difficulty || 'Unknown');
        // card.setAttribute('data-category', challenge.category?.name || challenge.category || 'Unknown');
        // card.setAttribute('data-created-at', challenge.created_at || new Date().toISOString());
        // card.setAttribute('data-author', challenge.created_by || 'Unknown');
        // card.setAttribute('data-points', challenge.points || 0);
        // card.setAttribute('data-hint', challenge.hint || '');
        // card.setAttribute('data-tags', Array.isArray(challenge.tags) ? challenge.tags.join(',') : '');
        // card.setAttribute('data-solved', challenge.solved ? 'true' : 'false');

        Object.entries(challenge).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                card.dataset[key] = typeof value === 'object' ? JSON.stringify(value) : value;
            }
        });

        card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="${getChallengeIcon(challenge.category)}" class="w-5 h-5 text-blue-400"></i>

                                <span class="text-sm max-lg:text-xs px-2 py-0.5 transition hover:shadow-[0_0_8px] hover:shadow-current rounded-full ${getDifficultyColor(challenge.difficulty)} font-medium align-middle">
                                    ${challenge.difficulty}
                                </span>
                            </div>
                            <div class="flex items-center gap-1 text-yellow-400">
                                <i data-lucide="trophy" class="w-4 h-4"></i>
                                <span class="text-sm max-lg:text-xs font-medium align-middle">${challenge.points} pts</span>
                            </div>
                        </div>

                        <!-- Title -->
                        <h2 class="cyber-card-title text-xl max-lg:text-lg font-bold align-middle">${challenge.title}</h2>

                        <!-- Tag and duration -->
                        <div class="flex items-center gap-3 text-sm text-slate-400">
                            <div class="flex items-center gap-1">
                                <span class="bg-slate-700 px-2 py-0.5 border border-slate-400 rounded-full text-sm max-lg:text-xs font-medium align-middle category">${challenge.category?.name || challenge.category || challenge.type || 'Unknown'}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="cyber-card-description text-clamp-3 line-clamp-2 text-sm max-lg:text-xs text-slate-300 align-middle">
                            ${challenge.description || 'Description non disponible'}
                        </p>

                        <!-- Tags -->
                        <div class="flex flex-wrap gap-2 text-sm max-lg:text-xs align-middle">
                            ${challenge.tags ? challenge.tags.map(tag => `<span class="px-2 py-1 bg-slate-700 rounded-full text-slate-200">${tag}</span>`).join('') : ''}
                        </div>

                        <!-- Footer row -->
                        <div class="flex items-center justify-between text-sm text-slate-400">
                            <div class="flex gap-4 items-center">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="users" class="w-4 h-4"></i>
                                    <span class="align-middle text-sm max-lg:text-xs">${challenge.solvers_count} résolus</span>
                                </div>
                            </div>

                            <!-- Hack Now button -->
                            ${!challenge.solved ? `
                            <button class="hack-now flex items-center gap-2 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i data-lucide="play-circle" class="w-4 h-4"></i>
                                <span class="text-sm max-lg:text-xs font-semibold align-middle">Hack Now</span>
                            </button>
                            ` : ''}
                            <div class="status solved" id="status" style="display: ${challenge.is_validated ? 'flex' : 'none'};">
                                <i class="w-4 h-4 stroke-current" data-lucide="check-circle" style="color: var(--green);"></i>
                                <span class="text-sm max-lg:text-xs">Solved</span>
                            </div>
                        </div>
                    `;

        container.appendChild(card);
    });

    // Réinitialiser les icônes Lucide
    lucide.createIcons();

    // Reconfigurer les boutons "Hack Now"
    setupModal();
}

function getChallengeIcon(category) {
    const icons = {
        'web': 'globe',
        'pwn': 'cpu',
        'crypto': 'lock',
        'reverse': 'refresh-ccw',
        'osint': 'search',
        'forensic': 'file-search'
    };
    return icons[category?.toLowerCase()] || 'file-lock';
}

function getDifficultyColor(difficulty) {
    const bg = {
        'easy': 'bg-green-600/20',
        'medium': 'bg-yellow-500/20',
        'hard': 'bg-red-500/20',
        'expert': 'bg-purple-500/20'
    };
    const color = {
        'easy': 'text-green-500',
        'medium': 'text-yellow-500',
        'hard': 'text-red-500',
        'expert': 'text-purple-500'
    };
    return `${bg[difficulty]} ${color[difficulty]} border border-white/10 px-2 text-xs`;
}

// Formatage du temps
function formatTimeDifference(createdAt) {
    if (!createdAt) return 'Récemment créé';
    const createdDate = new Date(createdAt);
    const now = new Date();
    const diffInSeconds = Math.floor((now - createdDate) / 1000);

    if (diffInSeconds < 60) return `${diffInSeconds} seconde${diffInSeconds !== 1 ? 's' : ''}`;
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minute${Math.floor(diffInSeconds / 60) !== 1 ? 's' : ''}`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} heure${Math.floor(diffInSeconds / 3600) !== 1 ? 's' : ''}`;
    return `${Math.floor(diffInSeconds / 86400)} jour${Math.floor(diffInSeconds / 86400) !== 1 ? 's' : ''}`;
}

// Fonction pour charger le classement des hackers
async function loadTopHackers() {
    try {
        const container = document.querySelector(CHALLENGE_ELEMENTS.topHackersList);
        const emptyState = document.querySelector(CHALLENGE_ELEMENTS.hackerListEmptyState);
        const data = await apiRequest('/hackers/top');
        console.log('leaderbord: ', data);
        if (!data.success) {
            container.style.display = 'none';
            emptyState.style.display = 'flex';
            console.log('echec lors du chargement du tophackers');
            return;
        }
        renderTopHackers(data.data || []);
    } catch (error) {
        handleError('Erreur lors du chargement du classement', error);
    }
}

function renderTopHackers(hackers) {
    const container = document.querySelector(CHALLENGE_ELEMENTS.topHackersList);
    const emptyState = document.querySelector(CHALLENGE_ELEMENTS.hackerListEmptyState);
    if (!hackers || hackers.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'flex';
        return;
    }

    if (!container || !emptyState) {
        container.innerHTML = '';
        console.error('Elements de classement non trouvés');
        return;
    }


    hackers.forEach((hacker, index) => {
        const item = document.createElement('li');
        item.textContent = `${index + 1}. ${hacker.username} - ${hacker.points} pts`;
        container.appendChild(item);
    });
}

// Fonction pour mettre à jour le nombre de résolutions
async function updateSolvesCount() {
    try {
        const data = await apiRequest('/challenges/solves');
        const elements = document.querySelectorAll('.stat .value'); // Sélectionnez tous les compteurs

        if (data && Array.isArray(data.data)) {
            // Mettre à jour chaque carte individuellement
            document.querySelectorAll('.cyber-card').forEach((card, index) => {
                const challengeData = data.data[index];
                if (challengeData) {
                    const solveCount = card.querySelector('.stat .value');
                    if (solveCount) {
                        solveCount.textContent = `${challengeData.solves || 0} solve${challengeData.solves !== 1 ? 's' : ''}`;
                    }
                }
            });
        }
    } catch (error) {
        handleError('Erreur lors de la mise à jour des résolutions', error);
        // Valeur par défaut
        document.querySelectorAll('.stat .value').forEach(el => {
            el.textContent = '0 solves';
        });
    }
}

// Gestion des filtres
function setupFilters() {
    document.querySelectorAll(CHALLENGE_ELEMENTS.filterGroups).forEach(group => {
        group.addEventListener("click", function (e) {
            const btn = e.target.closest(".filter-btn");
            if (!btn) return;

            if (btn.classList.contains("active")) {
                btn.classList.remove("active");
                applyFilters();
                return;
            }
            group.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            applyFilters();
        });
    });

    const clearBtn = document.querySelector(CHALLENGE_ELEMENTS.clearFiltersBtn);
    if (clearBtn) {
        clearBtn.addEventListener("click", function () {
            document.querySelectorAll(CHALLENGE_ELEMENTS.filterGroups).forEach(group => {
                group.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
            });
            applyFilters();
        });
    }
}

function applyFilters() {
    const filters = {};
    document.querySelectorAll(CHALLENGE_ELEMENTS.filterGroups).forEach(group => {
        const type = group.getAttribute("data-type");
        const activeBtn = group.querySelector(".filter-btn.active");
        if (activeBtn) {
            filters[type] = activeBtn.textContent.trim().toLowerCase();
            console.log('Active filter:', type, filters[type]);
        }
    });

    document.querySelectorAll(".cyber-card").forEach(card => {
        let show = true;

        // Filtre par difficulté
        if (filters.difficulty) {
            console.log('Difficulty filter:', filters.difficulty);
            const cardDifficulty = card.getAttribute("data-difficulty")?.toLowerCase();
            show = show && cardDifficulty === filters.difficulty;
        }

        // Filtre par catégorie
        if (filters.category) {
            const cardCategory = card.querySelector('.category')?.textContent.toLowerCase();
            show = show && cardCategory === filters.category.toLowerCase();
        }

        // Filtre par statut
        if (filters.status) {
            console.log('Status filter:', filters.status);
            const cardStatus = card.getAttribute("data-solved");
            if (filters.status === "solved") {
                show = show && cardStatus === "true";
            } else if (filters.status === "unsolved") {
                show = show && cardStatus === "false";
            }
        }

        card.style.display = show ? "" : "none";
    });
}
// Gestion de la recherche
function setupSearch() {
    const searchInput = document.querySelector(CHALLENGE_ELEMENTS.searchInput);
    if (!searchInput) return;

    searchInput.addEventListener('input', debounce(() => {
        const searchTerm = searchInput.value.toLowerCase();
        document.querySelectorAll('.cyber-card').forEach(card => {
            const title = card.querySelector('.cyber-card-title')?.textContent.toLowerCase() || "";
            const description = card.querySelector('.cyber-card-description')?.textContent.toLowerCase() || "";
            card.style.display = title.includes(searchTerm) || description.includes(searchTerm) ? '' : 'none';
        });
    }, 300));
}

function debounce(func, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => func(...args), delay);
    };
}

// Gestion du tri
function setupSorting() {
    const sortBtn = document.querySelector(CHALLENGE_ELEMENTS.sortBtn);
    const sortOptions = document.querySelectorAll(CHALLENGE_ELEMENTS.sortOptions);

    if (!sortBtn) {
        console.error('Sort button not found');
        return;
    }

    if (!sortOptions || sortOptions.length === 0) {
        console.error('No sort options found');
        return;
    }

    // Gérer le clic en dehors du menu
    function handleClickOutside(e) {
        const options = sortBtn.parentElement.querySelector('.sort-options');
        if (options.style.display === 'flex' && !sortBtn.contains(e.target)) {
            options.classList.remove('slide-in-blurred-left');
            options.classList.add('slide-out-blurred-left');
        }
    }

    // Ajouter l'écouteur une seule fois
    window.addEventListener("click", handleClickOutside);

    // Nettoyer l'écouteur quand le composant est démonté
    function cleanup() {
        window.removeEventListener("click", handleClickOutside);
    }

    // Toggle display of sort options
    sortBtn.addEventListener('click', () => {
        const options = sortBtn.parentElement.querySelector('.sort-options');
        if (options) {
            if (options.classList.contains('slide-in-blurred-left')) {
                options.classList.remove('slide-in-blurred-left');
                options.classList.add('slide-out-blurred-left');
            } else {
                options.classList.add('slide-in-blurred-left');
                options.classList.remove('slide-out-blurred-left');
                options.style.display = 'flex';
            }
        }
    });

    sortOptions.forEach(option => {
        if (!option) {
            console.error('Invalid sort option found');
            return;
        }

        option.addEventListener("click", (e) => {
            // Remove active class from all options
            sortOptions.forEach(opt => opt.classList.remove('active'));

            // Add active class to clicked option
            option.classList.add('active');

            // Get the sort direction from data attribute if exists
            const sortDirection = option.getAttribute('data-direction') || 'asc';

            // Update sort button text
            sortBtn.querySelector('span').textContent = option.textContent;

            // Sort challenges
            sortChallenges(option.textContent, sortDirection);

            // Close sort menu
            sortBtn.click();
        });
    });

    // Add default sort option
    const defaultSortOption = sortOptions[0];
    if (defaultSortOption) {
        defaultSortOption.classList.add('active');
        sortBtn.querySelector('span').textContent = defaultSortOption.textContent;
    }

    return cleanup; // Retourne la fonction de nettoyage
}

// Function to sort challenges
function sortChallenges(sortBy, direction = 'asc') {
    const cards = document.querySelectorAll('.cyber-card');
    const sortedCards = Array.from(cards).sort((a, b) => {
        let aValue, bValue;

        switch (sortBy.toLowerCase()) {
            case 'latest':
                // Tri par date (plus récent en premier)
                aValue = new Date(a.getAttribute('data-created-at') || '0');
                bValue = new Date(b.getAttribute('data-created-at') || '0');
                return direction === 'asc' ? bValue - aValue : aValue - bValue;

            case 'most solved':
                // Tri par nombre de résolutions
                aValue = parseInt(a.getAttribute('data-hackers') || '0');
                bValue = parseInt(b.getAttribute('data-hackers') || '0');
                return direction === 'asc' ? bValue - aValue : aValue - bValue;

            case 'difficulty':
                // Tri par difficulté avec valeurs numériques
                const difficultyValues = {
                    'easy': 1,
                    'medium': 2,
                    'hard': 3,
                    'expert': 4
                };
                aValue = difficultyValues[a.getAttribute('data-difficulty')?.toLowerCase() || 'easy'];
                bValue = difficultyValues[b.getAttribute('data-difficulty')?.toLowerCase() || 'easy'];
                return direction === 'asc' ? aValue - bValue : bValue - aValue;

            case 'title':
                // Tri par titre
                aValue = a.getAttribute('data-title') || '';
                bValue = b.getAttribute('data-title') || '';
                return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);

            default:
                // Tri par défaut (titre)
                aValue = a.getAttribute('data-title') || '';
                bValue = b.getAttribute('data-title') || '';
                return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
        }
    });

    // Reorder cards in the DOM
    sortedCards.forEach(card => {
        card.parentElement.appendChild(card);
    });
}

// Gestion de la modale
function setupModal() {
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    const modalContainer = document.querySelector(CHALLENGE_ELEMENTS.modalContainer);
    if (!modal) {
        console.log('Modal not found');
        return;
    };

    // Gestion des boutons de modale
    document.querySelectorAll(CHALLENGE_ELEMENTS.openButtons).forEach(button => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            openModal(button.closest(".cyber-card"));
        });
    });

    const closeButton = document.querySelector(CHALLENGE_ELEMENTS.closeButton);
    if (closeButton) {
        closeButton.addEventListener("click", closeModal);
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });
}

function openModal(card) {
    if (!card) return;
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    const modalContainer = document.querySelector('#modal-container');
    const timeAgo = card.getAttribute("data-created-at") ? `Il y a ${formatTimeDifference(card.getAttribute("data-created-at"))}` : 'Nouveau challenge';

    const challengeDetails = {
        author: card.getAttribute("data-created_by") || "Unknown",
        time: timeAgo,
        hackers: "Resolu par " + card.getAttribute("data-solvers_count") + " hackers" || "0 hackers",
        title: card.getAttribute("data-title") || (card.querySelector("h3")?.textContent || ""),
        description: card.getAttribute("data-description") || (card.querySelector(".description")?.textContent || ""),
        difficulty: card.getAttribute("data-difficulty") || "Difficulty",
        category: card.getAttribute("data-category") || "Category",
        points: card.getAttribute("data-points") || "Points",
        hint: card.getAttribute("data-hint") || "Hint",
        tags: (card.getAttribute("data-tags") || "").split(",")
    };
    const difficultyColor = getDifficultyColor(challengeDetails.difficulty).split(" ");

    // Mise à jour de la modale
    document.getElementById("challenge-time").textContent = challengeDetails.time;
    document.getElementById("challenge-hackers").textContent = challengeDetails.hackers;
    document.getElementById("challenge-title").textContent = challengeDetails.title;
    document.getElementById("challenge-description").textContent = challengeDetails.description;
    document.getElementById("challenge-difficulty").textContent = challengeDetails.difficulty;
    document.getElementById("challenge-difficulty").classList.add(...difficultyColor);
    document.getElementById("challenge-category").textContent = challengeDetails.category;
    document.getElementById("challenge-points").textContent = challengeDetails.points;
    document.getElementById("challenge-hint").textContent = challengeDetails.hint;
    document.getElementById("challenge-author").textContent = 'By ' + challengeDetails.author || 'Unknown';

    document.querySelector("#challenge-hint").innerHTML = "";
    const hintList = document.createElement("ul");
    challengeDetails.hint.split("\n").forEach(hint => {
        const hintItem = document.createElement("li");
        hintItem.textContent = hint.trim();
        hintList.appendChild(hintItem);
    });
    document.querySelector("#challenge-hint").appendChild(hintList);

    // Mise à jour des tags
    const tagsContainer = document.getElementById("challenge-tags");
    if (tagsContainer) {
        tagsContainer.innerHTML = "";
        challengeDetails.tags.forEach(tag => {
            if (tag.trim()) {
                const tagElement = document.createElement("span");
                tagElement.classList.add("bg-slate-700/50", "text-xs", "flex", "items-center", "justify-center", "px-3", "py-1", "rounded-full", "border", "border-white/10", "hover:bg-slate-700");
                tagElement.textContent = tag.trim();
                tagsContainer.appendChild(tagElement);
            }
        });
    }

    modal.style.display = "flex";
    modalContainer.classList.add('scale-in-center');
    modal.classList.remove('fade-out-bck');

    const submitFlagForm = document.getElementById("submit-flag-form");
    submitFlagForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const formData = new FormData(submitFlagForm);
        formData.append("csrf_token", document.querySelector('meta[name="csrf-token"]').content);
        const req = apiRequest("challenges/submit-flag", {
            method: "POST",
            body: formData
        });

        req.then(data => {
            if (data.success) {
                showNotification(data.message, "success");
                closeModal();
                updateSolvesCount();
            } else {
                showNotification(data.message, "error");
            }
        });
    });
}

function closeModal() {
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    const modalContainer = document.querySelector('#modal-container');
    modalContainer.classList.remove('scale-in-center');
    modal.classList.add('fade-out-bck');
    setTimeout(() => {
        modal ? modal.style.display = "none" : null;
    }, 350);
}

// Initialisation de l'application
async function initializeChallenges() {
    try {
        // Chargement des données
        await Promise.all([
            loadChallenges(),
            loadTopHackers(),
            updateSolvesCount()
        ]);

        // Configuration des interactions
        setupFilters();
        setupSearch();
        setupSorting();
        setupModal();

        // Actualisation périodique
        setInterval(updateSolvesCount, 100000);

    } catch (error) {
        handleError('Erreur lors de l\'initialisation de la page', error);
    }
}

// Démarrer l'application
document.addEventListener('DOMContentLoaded', async () => {
    const participationChecked = await checkHackathonAccess(1);
    if (!participationChecked.success) {
        console.log(participationChecked);
        const div = `
        <div class="flex flex-col items-center justify-center min-h-screen w-full bg-gray-900/90 backdrop-blur-lg z-50 fixed inset-0 p-6 text-center">
        <div class="bg-gray-800/90 border border-gray-700 rounded-xl p-8 max-w-2xl w-full mx-auto shadow-2xl">
        <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mb-6 mx-auto">
            <i data-lucide="alert-triangle" class="w-10 h-10 text-red-500"></i>
        </div>
        <h1 class="text-3xl font-bold text-white mb-4">Accès refusé</h1>
        <p class="text-gray-300 text-lg mb-8">${participationChecked.message}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/user/hackathon" 
               class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                Retour aux hackathons
            </a>
            <a href="/user/dashboard" 
               class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                <i data-lucide="home" class="w-5 h-5"></i>
                Tableau de bord
            </a>
        </div>
    </div>
    <p class="text-gray-500 text-sm mt-8">Besoin d'aide ? <a href="https://discord.gg/FbztK5Uagd" class="text-blue-400 hover:underline">Contactez le support</a></p>
</div>
</div>`;
        const mainContainer = document.querySelector(".main-container");
        mainContainer.innerHTML = div;
        lucide.createIcons();
        return;
    }
    initializeChallenges();
    lucide.createIcons();
});