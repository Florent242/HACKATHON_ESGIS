import { createEle } from "/js/dom.js";

// Configuration de base
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

let hackathonId = document.querySelector('meta[name="hackathon-id"]').content;
let phaseId = null;

// Fonction utilitaire pour gérer les erreurs
function handleError(title = 'Une erreur est survenue', error = null, type = 'error') {
    console.error(title, error);
    showNotification(title, error, type);
}

// Fonction utilitaire pour vérifier la participation au hackathon
async function checkHackathonAccess(hackathonId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const response = await apiRequest(`/check-participation`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            hackathon_id: hackathonId,
            csrf_token: csrfToken
        })
    });

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
        const data = await apiRequest(`/challenges/ctf/${hackathonId}/${userId}/${phaseId}`);

        console.log('challenge data', data);
        if (!data.success) {
            if (
                data.status === "phase_inactive" ||
                data.message?.includes("période de l'événement") ||
                data.message?.includes("phase")
            ) {
                showPhaseInactiveState(data.message);
            } else {
                handleError("Erreur lors de la récupération des challenges", data.message);
            }
            return;
        }
        renderChallenges(data.data || []);
    } catch (error) {
        handleError('Erreur lors du chargement des challenges', error);
    }
}

function showPhaseInactiveState(message = "Les challenges ne sont pas disponibles pour le moment.") {
    const emptyState = document.getElementById("challenges-empty-state");
    if (!emptyState) return;

    const title = document.getElementById("empty-title");
    const desc = document.getElementById("empty-message");
    const icon = document.getElementById("empty-icon");

    if (title) title.textContent = "Phase inactive";
    if (desc) desc.textContent = message;
    if (icon) icon.setAttribute("data-lucide", "lock");

    emptyState.classList.remove("hidden");
    emptyState.classList.add("flex");

    // Met à jour les icônes si besoin
    lucide.createIcons();
}

function showAccessDeniedModal(message) {
    const html = document.createElement('div');
    html.innerHTML = `
    <div class="flex flex-col items-center justify-center min-h-screen w-full bg-slate/80 backdrop-blur-md fixed inset-0 p-6 text-center z-99 ">
        <div class="bg-slate-800/90 border border-slate-700 rounded-xl p-8 max-w-2xl w-full mx-auto shadow-2xl">
            <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mb-6 mx-auto">
                <i data-lucide="alert-triangle" class="w-10 h-10 text-red-500"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-4">Accès refusé</h1>
            <p class="text-gray-300 text-lg mb-8">${message}</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/user/hackathon" 
                   class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    Retour aux hackathons
                </a>
                <a href="/user" 
                   class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                    <i data-lucide="home" class="w-5 h-5"></i>
                    Tableau de bord
                </a>
            </div>
        </div>
        <p class="text-gray-500 text-sm mt-8">Besoin d'aide ? <a href="https://discord.gg/FbztK5Uagd" class="text-blue-400 hover:underline">Contactez le support</a></p>
    </div>`;

    const mainContainer = document.querySelector(".main-container");
    mainContainer.appendChild(html);
    lucide.createIcons();
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

        Object.entries(challenge).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                card.dataset[key] = typeof value === 'object' ? JSON.stringify(value) : value;
            }
        });

        card.dataset["solved"] = challenge.is_validated ? "true" : "false";
        card.dataset["hackers"] = challenge.solvers_count || 0;


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

                        <!-- Tag -->
                        <div class="flex items-center justify-between gap-3 text-sm text-slate-400">
                            <div class="flex items-center gap-1">
                                <span class="bg-slate-700 px-2 py-0.5 border border-slate-400 rounded-full text-sm max-lg:text-xs font-medium align-middle category">${challenge.category?.name || challenge.category || challenge.type || 'Unknown'}</span>
                            </div>
                            <div class="status solved" id="status" style="display: ${challenge.is_validated ? 'flex' : 'none'};">
                                <i class="w-4 h-4 stroke-current" data-lucide="check-circle" style="color: var(--green);"></i>
                                <span class="text-sm max-lg:text-xs">Solved</span>
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
                                    <span class="stat-value align-middle text-sm max-lg:text-xs">${challenge.solvers_count} Résolutions</span>
                                </div>
                            </div>

                            <!-- Hack Now button -->
                            ${!challenge.solved ? `
                            <button class="hack-now flex items-center gap-2 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i data-lucide="play-circle" class="w-4 h-4"></i>
                                <span class="text-sm max-lg:text-xs font-semibold align-middle">Hack Now</span>
                            </button>
                            ` : ''}
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
        if (!data.success) {
            container.style.display = 'none';
            emptyState.style.display = 'flex';
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
        item.innerHTML = `
        <div class="flex items-center justify-between gap-2 bg-slate-800/50 p-2 rounded-lg">
            <span class="text-xs font-semibold text-white align-middle">${index + 1}</span>
            <span class="text-xs font-semibold text-white align-middle">${hacker.username}</span>
            <span class="text-xs font-semibold text-white align-middle">${hacker.points} pts</span>
        </div>
        `;
        container.innerHTML = '';
        container.appendChild(item);
    });
}

// Fonction pour mettre à jour le nombre de résolutions
async function updateSolvesCount() {
    try {
        const data = await apiRequest('/challenges/solves');
        const elements = document.querySelectorAll('.cyber-card .stat-value');

        if (data && Array.isArray(data.data)) {
            // Mettre à jour chaque carte individuellement
            document.querySelectorAll('.cyber-card').forEach((card, index) => {
                const challengeData = data.data[index];
                if (challengeData) {
                    const solveCount = card.querySelector('.stat-value');
                    if (solveCount) {
                        solveCount.textContent = `${challengeData.solves || 0} Résolution${challengeData.solves !== 1 ? 's' : ''}`;
                    }
                }
            });
        }
    } catch (error) {
        handleError('Erreur lors de la mise à jour des résolutions', error);
        // Valeur par défaut
        document.querySelectorAll('.stat-value').forEach(el => {
            el.textContent = '0 Résolutions';
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
            filters[type] = activeBtn.getAttribute("data-difficulty")?.trim().toLowerCase() || activeBtn.getAttribute("data-category")?.trim().toLowerCase() || activeBtn.getAttribute("data-status")?.trim().toLowerCase() || activeBtn.textContent.trim().toLowerCase() || "all";
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
            const sortDirection = option.getAttribute('data-direction') || 'desc';

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
        sortChallenges(defaultSortOption.textContent, 'desc');
    }

    return cleanup; // Retourne la fonction de nettoyage
}

// Function to sort challenges
function sortChallenges(sortBy, direction = 'desc') {
    const cards = document.querySelectorAll('.cyber-card');
    const sortedCards = Array.from(cards).sort((a, b) => {
        let aValue, bValue;

        switch (sortBy.toLowerCase()) {
            case 'latest':
                // Tri par date (plus récent en premier)
                aValue = new Date(a.getAttribute('data-created_at') || '0');
                bValue = new Date(b.getAttribute('data-created_at') || '0');
                return direction === 'asc' ? aValue - bValue : bValue - aValue;

            case 'most solved':
                // Tri par nombre de résolutions
                aValue = parseInt(a.getAttribute('data-hackers') || '0');
                bValue = parseInt(b.getAttribute('data-hackers') || '0');
                return direction === 'asc' ? aValue - bValue : bValue - aValue;

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
                return direction === 'desc' ? aValue - bValue : bValue - aValue;

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

let downloadHandler = null;
// Gestion de la modale
function setupModal() {
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    const modalContainer = document.querySelector(CHALLENGE_ELEMENTS.modalContainer);
    if (!modal) {
        console.error('Modal not found');
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
    setupFlagForm();

    const downloadBtn = document.getElementById("download-files-button");
    if (downloadBtn) {

        if (downloadHandler) {
            downloadBtn.removeEventListener("click", downloadHandler);
        }

        downloadHandler = async function (e) {
            e.preventDefault();
            const file = downloadBtn.getAttribute("data-resource_link");
            if (!file) {
                showNotification("Oups !", "Aucun fichier à télécharger", "error");
                return;
            }

            const url = `/download/${encodeURIComponent(file)}`;

            try {
                const response = await fetch(url, { method: "GET", credentials: "include" });
                if (!response.ok) throw new Error("Erreur lors du téléchargement");

                // Récupérer le content-type envoyé par PHP
                const contentType = response.headers.get("Content-Type") || "application/octet-stream";

                // Construire le blob avec le bon type MIME
                const arrayBuffer = await response.arrayBuffer();
                const blob = new Blob([arrayBuffer], { type: contentType });

                // Nom du fichier à partir du header Content-Disposition
                let filename = file;
                const disposition = response.headers.get("Content-Disposition");
                if (disposition && disposition.includes("filename=")) {
                    filename = disposition.split("filename=")[1].replace(/"/g, "");
                }

                // Créer un lien temporaire
                const downloadUrl = window.URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = downloadUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(downloadUrl);

            } catch (err) {
                showNotification("Erreur", err.message, "error");
            }
        };

        downloadBtn.addEventListener("click", downloadHandler);
    }


}

function openModal(card) {
    if (!card) return;
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    const modalContainer = document.querySelector('#modal-container');
    const timeAgo = card.getAttribute("data-created_at") ? `Il y a ${formatTimeDifference(card.getAttribute("data-created_at"))}` : 'Nouveau challenge';

    const challengeDetails = {
        author: card.getAttribute("data-created_by") || "Unknown",
        time: timeAgo,
        hackers: "Resolu par " + card.getAttribute("data-solvers_count") + " hackers" || "0 hackers",
        title: card.getAttribute("data-title") || (card.querySelector("h3")?.textContent || ""),
        description: card.getAttribute("data-description") || (card.querySelector(".description")?.textContent || ""),
        difficulty: card.getAttribute("data-difficulty") || "Difficulty",
        category: card.getAttribute("data-category") || "Category",
        points: card.getAttribute("data-points") || "Points",
        hint: card.getAttribute("data-hint") || "",
        tags: (card.getAttribute("data-tags") || "").split(","),
        resource: card.getAttribute("data-resource_link") || "",
        instance: card.getAttribute("data-url_path") || "",
    };
    const difficultyColor = getDifficultyColor(challengeDetails.difficulty).split(" ");

    // Mise à jour de la modale
    document.getElementById("challenge_id").value = card.getAttribute("data-id");
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
    document.getElementById("launch-instance-button").setAttribute("data-instance", challengeDetails.instance);
    document.getElementById("launch-instance-button").textContent = challengeDetails.instance ?? "Instance non disponible";

    // Gestion du bouton de téléchargement
    const downloadButton = document.getElementById("download-files-button");
    if (downloadButton && challengeDetails.resource) {
        downloadButton.setAttribute("data-resource_link", challengeDetails.resource);
        downloadButton.disabled = false;
    } else {
        downloadButton.setAttribute("data-resource_link", "");
        downloadButton.disabled = true;
    }

    // Gestion du bouton de lancement de l'instance
    const launchInstanceButton = document.getElementById("launch-instance-button");
    if (launchInstanceButton && challengeDetails.instance) {
        launchInstanceButton.setAttribute("data-instance", challengeDetails.instance);
        launchInstanceButton.disabled = false;
        launchInstanceButton.addEventListener('click', handleInstanceCopy);
        launchInstanceButton.setAttribute("data-tooltip", challengeDetails.instance);

        if (typeof initializeTooltips === "function") {
            initializeTooltips();
        }
    } else {
        launchInstanceButton.setAttribute("data-instance", "");
        launchInstanceButton.textContent = "Instance non disponible";
        launchInstanceButton.disabled = true;
    }

    // Réinitialiser le conteneur
    document.querySelector("#challenge-hint").innerHTML = "";

    // Récupération des hints (possiblement JSON encodé en string)
    let hints = challengeDetails.hint;

    // Si hint est une string JSON, on la parse
    if (typeof hints === "string" && hints.trim() !== "") {
        try {
            hints = safeJsonParse(hints);
        } catch (e) {
            console.error("Erreur de parsing JSON du champ hint :", e);
            hints = [];
        }
    }

    // Vérifie si on a bien un tableau
    if (Array.isArray(hints)) {
        const hintList = document.createElement("ul");
        hintList.className = "list-none space-y-2 text-yellow-100 w-full"; // Tailwind : espace entre les <li>

        hints.forEach(hint => {
            const hintItem = document.createElement("li");
            hintItem.className = "flex justify-start items-center gap-2 text-sm leading-relaxed"; // style propre + spacing
            hintItem.innerHTML = `
            <span class="text-yellow-400 mt-0.5"><i data-lucide="lightbulb" class="w-3 h-3 text-yellow-400"></i></span>
            <span class="flex-1">${hint.trim()}</span>
        `;
            hintList.appendChild(hintItem);
        });

        document.querySelector("#challenge-hint").appendChild(hintList);
    }

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

    lucide.createIcons();
    modal.style.display = "flex";
    modalContainer.classList.add('scale-in-center');
    modal.classList.remove('fade-out-bck');

}

// Déplacer cette fonction en dehors de openModal
async function handleInstanceCopy(e) {
    e.preventDefault();
    e.stopPropagation();
    try {
        const instance = e.currentTarget.getAttribute('data-instance');
        await navigator.clipboard.writeText(instance);
        showNotification("Copié", "Lien de l'instance copié dans le presse-papiers", "success");
    } catch (err) {
        console.error("Erreur lors de la copie du lien de l'instance :", err);
        showNotification("Erreur", "Impossible de copier le lien de l'instance", "error");
    }
}

function safeJsonParse(jsonString) {
    try {
        // Nettoyage de la chaîne
        let clean = jsonString
            .replace(/^"|"$/g, '')  // Enlève les guillemets extérieurs
            .replace(/\\"/g, '"')    // Déséchappe les guillemets
            .replace(/\n/g, '')      // Supprime les sauts de ligne
            .replace(/\\n/g, '')     // Supprime les \n échappés
            .trim();
        
        // S'assure que c'est bien un tableau JSON
        if (!clean.startsWith('[')) clean = `[${clean}]`;
        
        return JSON.parse(clean);
    } catch (e) {
        console.error("Erreur de parsing JSON:", e);
        return []; // ou une valeur par défaut
    }
}

function closeModal() {
    const modal = document.querySelector(CHALLENGE_ELEMENTS.modal);
    const modalContainer = document.querySelector('#modal-container');
    const submitFlagForm = document.getElementById("submit-flag-form");

    submitFlagForm.reset();
    hideError(submitFlagForm.querySelector("#flag"), submitFlagForm.querySelector("#flagError"));
    modalContainer.classList.remove('scale-in-center');
    modal.classList.add('fade-out-bck');
    setTimeout(() => {
        modal ? modal.style.display = "none" : null;
    }, 350);
}

let flagFormIsInitialized = false;

function setupFlagForm() {
    if (flagFormIsInitialized) return;

    const form = document.getElementById("submit-flag-form");
    if (!form) return;

    flagFormIsInitialized = true;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const input = form.querySelector("#flag");
        const error = form.querySelector("#flagError");
        const challengeId = form.querySelector("#challenge_id").value;
        const flag = input.value.trim();

        if (!flag || !challengeId) {
            showError(input, error, "Flag manquant");
            return;
        }

        const userId = await getUserId();
        const formData = new FormData(form);
        formData.append("csrf_token", document.querySelector('meta[name="csrf-token"]').content);
        formData.append("hackathon_id", hackathonId);
        formData.append("phase_id", phaseId);

        try {
            const plainObject = Object.fromEntries(formData.entries());

            const res = await apiRequest(`/challenges/ctf/submit/${userId}`, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(plainObject)
            });

            if (res.success) {
                showNotification("Félicitations !", res.message, "success");
                hideError(input, error);
                form.reset();
                updateSolvesCount();
            } else {
                showNotification(res.message || "Échec de la validation", res.error || null, "error");
                showError(input, error, res.message || "Flag invalide");
            }
        } catch (err) {
            showNotification("Erreur côté client", null, "error");
            console.error(err);
        }
    });
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

/**
 * Vérifie si l'utilisateur est qualifie pour une phase
 */
async function checkPhaseQualification(hackathonId, phaseId) {
    try {
        if (!hackathonId || !phaseId) {
            return {
                success: false,
                is_qualified: false,
                message: 'Hackathon ID et/ou Phase ID manquant',
                status: 'error'
            };
        }
        const response = await apiRequest(`/check-qualification`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                hackathon_id: hackathonId,
                phase_id: phaseId,
            })
        });

        return {
            success: response.success,
            message: response.message || (response.success ? 'Accès autorisé' : 'Accès refusé'),
            is_qualified: response.is_qualified?? null,
            status: response.status ?? null,
            action: response.action || null
        };
    } catch (error) {
        console.error('Erreur lors de la vérification d\'accès:', error);
        return {
            success: false,
            message: 'Erreur lors de la vérification d\'accès au hackathon',
            is_qualified: false,
            status: 'error',
            action: null
        };
    }
}

/**
 * Obtenire la phase actuelle du hackathon en cours
 */
async function getCurrentPhase(hackathonId) {
    try {
        const response = await apiRequest(`/phases/active-phase/${hackathonId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.success) {
            return response.data?.id ?? null;
        } else {
            return null;
        }
    } catch (error) {
        console.error('Erreur lors de la vérification d\'accès:', error);
        return null;
    }
}

// Démarrer l'application
document.addEventListener('DOMContentLoaded', async () => {
    phaseId = await getCurrentPhase(hackathonId);
    if ( !phaseId ) {
        showPhaseInactiveState('Aucune phase active pour le moment');
        return;
    }

    const participationChecked = await checkHackathonAccess(hackathonId);
    if (!participationChecked.success) {
        showAccessDeniedModal(participationChecked.message);
        return;
    }

    // Vérifier si l'utilisateur est qualifie pour une phase
    const phaseCheck = await checkPhaseQualification(hackathonId, phaseId);
    if (phaseCheck?.success && phaseCheck?.action && phaseCheck?.action.includes('/')) {
        // Créer une notification de redirection
        const notification = document.createElement('div');
        notification.className = 'fixed bottom-4 right-4 bg-purple-600/90 text-white p-4 rounded-lg shadow-lg z-50 max-w-md';
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">Vous êtes qualifié pour la phase suivante !</p>
                    <p class="mt-1 text-sm opacity-90">Voulez-vous y accéder maintenant ?</p>
                    <div class="mt-2 flex space-x-3">
                        <a href="${phaseCheck.action}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Aller à la phase suivante
                        </a>
                        <button type="button" class="text-white hover:opacity-80 text-xs font-medium" onclick="this.closest('.fixed').remove()">
                            Continuer ici
                        </button>
                    </div>
                </div>
                <button type="button" class="ml-4 flex-shrink-0 text-white hover:text-gray-200 focus:outline-none" onclick="this.closest('.fixed').remove()">
                    <span class="sr-only">Fermer</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        `;
        
        // Ajouter la notification au DOM
        document.body.appendChild(notification);
        
        // Ajouter un bouton flottant si l'utilisateur ferme la notification
        const floatingButton = document.createElement('a');
        floatingButton.href = phaseCheck.action;
        floatingButton.className = 'fixed bottom-4 right-4 bg-blue-600 text-white p-3 rounded-full shadow-lg z-40 hover:bg-blue-700 transition-colors duration-200';
        floatingButton.title = 'Aller à la phase suivante';
        floatingButton.innerHTML = `
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        `;
        
        // Ajouter le bouton flottant après 5 secondes s'il n'est pas déjà là
        setTimeout(() => {
            if (!document.querySelector('.fixed[href="' + phaseCheck.action + '"]')) {
                document.body.appendChild(floatingButton);
            }
        }, 5000);
    } else if ( !phaseCheck?.success || !phaseCheck?.is_qualified ) {
        showPhaseInactiveState('Vous n\'êtes pas qualifié pour la phase actuelle !');
        return;
    }

    initializeChallenges();
    lucide.createIcons();
});