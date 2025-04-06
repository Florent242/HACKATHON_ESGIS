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
    closeButton: ".close-modal",
    openButtons: ".hack-now",
    
    // Stats
    solvesCount: "#solves-count"
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
    } catch (error) {
        handleError('Erreur lors du chargement des challenges', error);
    }
}

// Fonction pour afficher les challenges
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
        card.setAttribute('data-tags', challenge.tags.join(','));
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
                ${challenge.tags.map(tag => `<span class="tag">${tag.toUpperCase()}</span>`).join('')}
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
}

function getDifficultyColor(difficulty) {
    const colors = {
        'Easy': 'var(--green)',
        'Medium': 'var(--yellow)',
        'Hard': 'var(--red)',
        'Expert': 'var(--purple)'
    };
    return colors[difficulty] || 'var(--text)';
}

// Fonction pour charger le classement des hackers
async function loadTopHackers() {
    try {
        const data = await apiRequest('/hackers/top');
        renderTopHackers(data.data || []);
    } catch (error) {
        handleError('Erreur lors du chargement du classement', error);
    }
}

function renderTopHackers(hackers) {
    const container = document.querySelector(CHALLENGE_ELEMENTS.topHackersList);
    if (!container) return;

    container.innerHTML = '';
    
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
        const elements = document.querySelectorAll(CHALLENGE_ELEMENTS.solvesCount);
        
        elements.forEach(el => {
            el.textContent = `${data.count || 0} solves`;
        });
    } catch (error) {
        handleError('Erreur lors de la mise à jour des résolutions', error);
    }
}

// Gestion des filtres
function setupFilters() {
    document.querySelectorAll(CHALLENGE_ELEMENTS.filterGroups).forEach(group => {
        group.addEventListener("click", function(e) {
            const btn = e.target.closest(".filter-btn");
            if (!btn) return;
            
            group.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            applyFilters();
        });
    });

    const clearBtn = document.querySelector(CHALLENGE_ELEMENTS.clearFiltersBtn);
    if (clearBtn) {
        clearBtn.addEventListener("click", function() {
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

// Gestion de la recherche
function setupSearch() {
    const searchInput = document.querySelector(CHALLENGE_ELEMENTS.searchInput);
    if (!searchInput) return;

    searchInput.addEventListener('input', debounce(() => {
        const searchTerm = searchInput.value.toLowerCase();
        document.querySelectorAll('.cyber-card').forEach(card => {
            const title = card.querySelector('h3')?.textContent.toLowerCase() || "";
            const description = card.querySelector('p')?.textContent.toLowerCase() || "";
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
    if (!modal) return;

    document.querySelectorAll(CHALLENGE_ELEMENTS.openButtons).forEach(button => {
        button.addEventListener("click", function(e) {
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

    const challengeDetails = {
        title: card.getAttribute("data-title") || (card.querySelector("h3")?.textContent || ""),
        description: card.getAttribute("data-description") || (card.querySelector(".description")?.textContent || ""),
        difficulty: card.getAttribute("data-difficulty") || "Difficulty",
        category: card.getAttribute("data-category") || "Category",
        time: card.getAttribute("data-time") || "Time",
        points: card.getAttribute("data-points") || "Points",
        hint: card.getAttribute("data-hint") || "Hint",
        tags: (card.getAttribute("data-tags") || "").split(",")
    };

    // Mise à jour de la modale
    document.getElementById("challenge-title").textContent = challengeDetails.title;
    document.getElementById("challenge-description").textContent = challengeDetails.description;
    document.getElementById("challenge-difficulty").textContent = challengeDetails.difficulty;
    document.getElementById("challenge-category").textContent = challengeDetails.category;
    document.getElementById("challenge-time").textContent = challengeDetails.time;
    document.getElementById("challenge-points").textContent = challengeDetails.points;
    document.getElementById("challenge-hint").textContent = challengeDetails.hint;

    // Mise à jour des tags
    const tagsContainer = document.getElementById("challenge-tags");
    if (tagsContainer) {
        tagsContainer.innerHTML = "";
        challengeDetails.tags.forEach(tag => {
            if (tag.trim()) {
                const tagElement = document.createElement("span");
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