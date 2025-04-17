// Configuration de base
const API_BASE_URL = '/HACKATHON_ESGIS/public/api';
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

// Fonction pour charger les challenges
async function loadChallenges() {
    try {
        const data = await apiRequest('/challenges');
        renderChallenges(data.data || []);
        console.log('challenge data', data);
    } catch (error) {
        handleError('Erreur lors du chargement des challenges', error);
    }
}

// Fonction pour afficher les challenges
/*
function renderChallenges(challenges) {
    const container = document.querySelector(CHALLENGE_ELEMENTS.cardsContainer);
    if (!container) return;

    container.innerHTML = '';

    challenges.forEach(challenge => {
        const card = document.createElement('div');
        card.className = 'cyber-card';
        card.setAttribute('data-title', challenge.title);
        card.setAttribute('data-description', challenge.description);
        card.setAttribute('data-difficulty', challenge.difficulty);
        card.setAttribute('data-category', challenge.category);
        card.setAttribute('data-time', challenge.time);
        card.setAttribute('data-points', challenge.points);
        card.setAttribute('data-hint', challenge.hint);
        card.setAttribute('data-tags', Array.isArray(challenge.tags) ? challenge.tags.join(',') : '');
        card.setAttribute('data-solved', challenge.solved || 'false');

        card.innerHTML = `
            <div class="card-header">
                <div class="card-header-info">
                    <div class="left-info">
                        <i data-lucide="file-text" style="color:var(--blue);"></i> 
                        <span class="difficulty" style="color: ${getDifficultyColor(challenge.difficulty)};">${challenge.difficulty}</span>
                    </div>
                    <div class="right-info">
                        <i data-lucide="trophy" style="color: gold;"></i> 
                        <span>${challenge.points} pts</span>
                    </div>
                </div>
                <h3>${challenge.title}</h3>
                <div class="meta">
                    <span class="category" style="background: rgba(59, 130, 246, 0.2);">${challenge.category}</span>
                    <div><i data-lucide="timer"></i><span class="time">${challenge.time}</span></div>
                </div>
            </div>
            <p class="description">${challenge.description}</p>
            <div class="tags">
                ${Array.isArray(challenge.tags) ? challenge.tags.map(tag => `<span class="tag">${tag.toUpperCase()}</span>`).join('') : ''}
            </div>
            <div class="stats-table">
                <div class="stat">
                    <i data-lucide="user"></i>
                    <span class="value">${challenge.solves || 0} solves</span>
                </div>
            </div>
            <div class="card-footer">
                <button class="badge hack-now">HACK NOW</button>
                ${challenge.solved ? '<div class="status solved"><i data-lucide="check-circle"></i><span>Solved</span></div>' : ''}
            </div>
        `;

        container.appendChild(card);
    });

    // Initialiser les icônes Lucide
    lucide.createIcons();
}*/
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
        card.classList.add('cyber-card', 'flex', 'flex-col', 'justify-between', 'w-fit', 'mx-auto', 'bg-[#0f172a]', 'text-white', 'p-6', 'rounded-2xl', 'shadow-lg', 'border', 'border-slate-700', 'space-y-4');
        card.setAttribute('data-title', challenge.title || '');
        card.setAttribute('data-hackers', challenge.hackers || '0');
        card.setAttribute('data-description', challenge.description || '');
        card.setAttribute('data-type', challenge.type || '');
        card.setAttribute('data-difficulty', challenge.difficulty || 'Unknown');
        card.setAttribute('data-category', challenge.category?.name || challenge.category || 'Unknown');
        card.setAttribute('data-created-at', challenge.created_at || new Date().toISOString());
        card.setAttribute('data-author', challenge.created_by || 'Unknown');
        card.setAttribute('data-points', challenge.points || 0);
        card.setAttribute('data-hint', challenge.hint || '');
        card.setAttribute('data-tags', Array.isArray(challenge.tags) ? challenge.tags.join(',') : '');
        card.setAttribute('data-solved', challenge.solved ? 'true' : 'false');

        const timeAgo = challenge.created_at ? `Il y a ${formatTimeDifference(challenge.created_at)}` : 'Nouveau challenge';

        card.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i data-lucide="file-lock" class="w-5 h-5 text-blue-400"></i>
                                <span class="text-sm px-2 py-0.5 transition hover:shadow-[0_0_8px] hover:shadow-current rounded-full ${getDifficultyColor(challenge.difficulty)} text-white font-medium align-middle">${challenge.difficulty}</span>
                            </div>
                            <div class="flex items-center gap-1 text-yellow-400">
                                <i data-lucide="trophy" class="w-4 h-4"></i>
                                <span class="text-sm font-medium align-middle">${challenge.points} pts</span>
                            </div>
                        </div>

                        <!-- Title -->
                        <h2 class="cyber-card-title text-xl font-bold align-middle">${challenge.title}</h2>

                        <!-- Tag and duration -->
                        <div class="flex items-center gap-3 text-sm text-slate-400">
                            <div class="flex items-center gap-1">
                                <span class="bg-slate-700 px-2 border border-slate-400 py-0.5 rounded-full text-xs font-medium align-middle">${challenge.category?.name || challenge.category || challenge.type || 'Unknown'}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span class="align-middle">${timeAgo || 'Récemment créé'}</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="cyber-card-description text-clamp-3 line-clamp-2 text-sm text-slate-300 align-middle">
                            ${challenge.description || 'Description non disponible'}
                        </p>

                        <!-- Tags -->
                        <div class="flex flex-wrap gap-2 text-xs align-middle">
                            ${challenge.tags ? challenge.tags.map(tag => `<span class="px-2 py-1 bg-slate-700 rounded-full text-slate-200">${tag}</span>`).join('') : ''}
                        </div>

                        <!-- Footer row -->
                        <div class="flex items-center justify-between text-sm text-slate-400">
                            <div class="flex gap-4 items-center">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="users" class="w-4 h-4"></i>
                                    <span class="align-middle">${challenge.hackers || 0} résolus</span>
                                </div>
                            </div>

                            <!-- Hack Now button -->
                            ${!challenge.solved ? `
                            <button class="hack-now flex items-center gap-2 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i data-lucide="play-circle" class="w-4 h-4"></i>
                                <span class="text-sm font-semibold align-middle">Hack Now</span>
                            </button>
                            ` : ''}
                            <div class="status solved" id="status" style="display: ${challenge.solved ? 'flex' : 'none'};">
                                <i class="w-4 h-4 stroke-current" data-lucide="check-circle" style="color: var(--green);"></i>
                                <span>Solved</span>
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

function getDifficultyColor(difficulty) {
    const bg = {
        'easy': 'bg-green-600/20',
        'medium': 'bg-yellow-500/20',
        'hard': 'bg-red-500/20',
        'expert': 'bg-purple-500/20'
    };
    const color = {
        'easy': 'text-green-300',
        'medium': 'text-yellow-300',
        'hard': 'text-red-300',
        'expert': 'text-purple-300'
    };
    return `${bg[difficulty]} ${color[difficulty]} border border-white/10 px-2 text-xs font-medium`;
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
/*
async function updateSolvesCount() {
    try {
        const data = await apiRequest('/challenges/solves');
        const elements = document.querySelectorAll(CHALLENGE_ELEMENTS.solvesCount);
        
        if (data && data.success && data.count !== undefined) {
            elements.forEach(el => {
                el.textContent = `${data.count} solves`;
            });
        } else {
            throw new Error('Réponse API invalide');
        }
    } catch (error) {
        handleError('Erreur lors de la mise à jour des résolutions', error);
        // Valeur par défaut en cas d'erreur
        document.querySelectorAll(CHALLENGE_ELEMENTS.solvesCount).forEach(el => {
            el.textContent = '0 solves';
        });
    }
}
*/
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
/*
function applyFilters() {
    const filters = {};
    document.querySelectorAll(CHALLENGE_ELEMENTS.filterGroups).forEach(group => {
        const type = group.getAttribute("data-type");
        const activeBtn = group.querySelector(".filter-btn.active");
        if (activeBtn) {
            filters[type] = activeBtn.textContent.trim().toLowerCase();
        }
    });

    document.querySelectorAll(".cyber-card").forEach(card => {
        let show = true;
        
        if (filters.difficulty && card.getAttribute("data-difficulty")?.toLowerCase() !== filters.difficulty) {
            show = false;
        }
        
        if (filters.category && card.getAttribute("data-category")?.toLowerCase() !== filters.category) {
            show = false;
        }
        
        if (filters.status) {
            const cardStatus = card.getAttribute("data-solved");
            if ((filters.status === "solved" && cardStatus !== "true") || 
                (filters.status === "unsolved" && cardStatus !== "false")) {
                show = false;
            }
        }
        
        card.style.display = show ? "" : "none";
    });
}
*/
function applyFilters() {
    const filters = {};
    document.querySelectorAll(CHALLENGE_ELEMENTS.filterGroups).forEach(group => {
        const type = group.getAttribute("data-type");
        const activeBtn = group.querySelector(".filter-btn.active");
        if (activeBtn) {
            filters[type] = activeBtn.textContent.trim().toLowerCase();
        }
    });

    document.querySelectorAll(".cyber-card").forEach(card => {
        let show = true;

        // Filtre par difficulté
        if (filters.difficulty) {
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
    if (!sortBtn) return;

    document.querySelectorAll(CHALLENGE_ELEMENTS.sortOptions).forEach(option => {
        option.addEventListener("click", () => {
            sortChallenges(option.textContent);
        });
    });
}

function sortChallenges(sortBy) {
    const challengesArray = Array.from(document.querySelectorAll(".cyber-card"));
    const container = document.querySelector(CHALLENGE_ELEMENTS.cardsContainer);

    challengesArray.sort((a, b) => {
        if (sortBy === "Latest") {
            return new Date(b.dataset.date) - new Date(a.dataset.date);
        }
        if (sortBy === "Most Solved") {
            return parseInt(b.querySelector(".stat .value").textContent) -
                parseInt(a.querySelector(".stat .value").textContent);
        }
        if (sortBy === "Difficulty") {
            return getDifficultyValue(a.dataset.difficulty) - getDifficultyValue(b.dataset.difficulty);
        }
        return 0;
    });

    container.innerHTML = "";
    challengesArray.forEach(challenge => container.appendChild(challenge));
}

function getDifficultyValue(difficulty) {
    const values = {
        'Easy': 1,
        'Medium': 2,
        'Hard': 3,
        'Expert': 4
    };
    return values[difficulty] || 0;
}

// Gestion de la modale
function setupModal() {
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
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
    const timeAgo = card.getAttribute("data-created-at") ? `Il y a ${formatTimeDifference(card.getAttribute("data-created-at"))}` : 'Nouveau challenge';

    const challengeDetails = {
        author: card.getAttribute("data-author") || "Unknown",
        time: timeAgo,
        hackers: "Resolu par " + card.getAttribute("data-hackers") + " hackers" || "0 hackers",
        title: card.getAttribute("data-title") || (card.querySelector("h3")?.textContent || ""),
        description: card.getAttribute("data-description") || (card.querySelector(".description")?.textContent || ""),
        difficulty: card.getAttribute("data-difficulty") || "Difficulty",
        type: card.getAttribute("data-type") || "Category",
        points: card.getAttribute("data-points") || "Points",
        hint: card.getAttribute("data-hint") || "Hint",
        tags: (card.getAttribute("data-tags") || "").split(",")
    };

    // Mise à jour de la modale
    document.getElementById("challenge-time").textContent = challengeDetails.time;
    document.getElementById("challenge-hackers").textContent = challengeDetails.hackers;
    document.getElementById("challenge-title").textContent = challengeDetails.title;
    document.getElementById("challenge-description").textContent = challengeDetails.description;
    document.getElementById("challenge-difficulty").textContent = challengeDetails.difficulty;
    document.getElementById("challenge-category").textContent = challengeDetails.type;
    document.getElementById("challenge-points").textContent = challengeDetails.points;
    document.getElementById("challenge-hint").textContent = challengeDetails.hint;
    document.getElementById("challenge-author").textContent = 'By ' + challengeDetails.author || 'Unknown';

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
}

function closeModal() {
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    if (modal) modal.style.display = "none";
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
        setInterval(updateSolvesCount, 10000);

    } catch (error) {
        handleError('Erreur lors de l\'initialisation de la page', error);
    }
}

// Démarrer l'application
document.addEventListener('DOMContentLoaded', () => {
    initializeChallenges();
    lucide.createIcons();
});