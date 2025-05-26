function toggleFilters() {
    const container = document.getElementById('filterTags');
    const filterText = document.getElementById('filterText');
    const icon = document.getElementById('filterIcon');

    container.classList.toggle('show');

    if (container.classList.contains('show')) {
        filterText.textContent = 'Masquer les filtres';
        icon.setAttribute('data-lucide', 'chevron-up');

        // Animation d'entrée des filtres
        const filterTags = container.querySelectorAll('.filter-tag');
        filterTags.forEach((tag, index) => {
            tag.style.animationDelay = `${index * 0.05}s`;
            tag.classList.add('filter-appear');
        });
    } else {
        filterText.textContent = 'Afficher les filtres';
        icon.setAttribute('data-lucide', 'chevron-down');
    }
    lucide.createIcons();
}

// Initialize filters (hide them by default)
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById("filterTags");
    if (container) {
        container.classList.remove("show");
    }
});

// Variables globales de filtrage améliorées
let selectedCategory = "all";
let selectedDifficulty = "all";
let selectedStatus = "all";
let searchTerm = "";
let sortBy = "date"; // date, title, difficulty
let sortOrder = "desc"; // asc, desc

// Tableau de ressources étendu avec plus de variété
const resources = [
    {
        title: "API REST - Module d'authentification",
        description: "Créez une API REST sécurisée avec un système d'authentification JWT complet, gestion des rôles et middleware de sécurité.",
        difficulty: "intermédiaire",
        category: "backend",
        date: "29 mai 2025",
        status: "En cours",
        tags: ["NodeJS", "Express", "MongoDB", "JWT"],
        participants: 15
    },
    {
        title: "Application Mobile React Native",
        description: "Développez une application mobile cross-platform avec React Native, Redux et Firebase pour la gestion de tâches.",
        difficulty: "avancé",
        category: "mobile",
        date: "29 mai 2025",
        status: "Soumis",
        tags: ["React Native", "Redux", "Firebase"],
        participants: 8
    },
    {
        title: "Architecture Microservices",
        description: "Développez une architecture microservices complète avec Docker, Kubernetes et monitoring avancé.",
        difficulty: "avancé",
        category: "devops",
        date: "30 juin 2025",
        status: "En cours",
        tags: ["Docker", "Kubernetes", "NodeJS"],
        participants: 5
    },
    {
        title: "Application Web Frontend avec React",
        description: "Créez une interface utilisateur moderne pour un tableau de bord d'analytique avec graphiques interactifs.",
        difficulty: "intermédiaire",
        category: "frontend",
        date: "30 juin 2025",
        status: "Disponible",
        tags: ["React", "TypeScript", "Recharts"],
        participants: 22
    },
    {
        title: "Application d'IA pour la Reconnaissance d'Images",
        description: "Développez un système de reconnaissance d'images utilisant le deep learning et TensorFlow.",
        difficulty: "avancé",
        category: "ia",
        date: "20 juin 2025",
        status: "Disponible",
        tags: ["Python", "TensorFlow", "Keras"],
        participants: 12
    },
    {
        title: "Application Blockchain Simple",
        description: "Développez une application décentralisée (DApp) simple sur Ethereum avec smart contracts.",
        difficulty: "intermédiaire",
        category: "blockchain",
        date: "25 juin 2025",
        status: "Disponible",
        tags: ["Solidity", "Web3.js", "React"],
        participants: 7
    },
    {
        title: "Système de Base de Données NoSQL",
        description: "Concevez et implémentez un système de base de données NoSQL pour des données à grande échelle.",
        difficulty: "avancé",
        category: "database",
        date: "15 juillet 2025",
        status: "Disponible",
        tags: ["MongoDB", "Redis", "ElasticSearch"],
        participants: 18
    },
    {
        title: "Site Web Statique avec Optimisation SEO",
        description: "Créez un site vitrine optimisé pour les moteurs de recherche et les performances.",
        difficulty: "facile",
        category: "web",
        date: "10 juin 2025",
        status: "Terminé",
        tags: ["HTML5", "CSS3", "JavaScript"],
        participants: 31
    }
];

// Fonction améliorée qui construit une carte de ressource moderne
function createResourceCard(resource) {
    const card = document.createElement("div");
    card.className = "resource-card";

    // Stocker les infos en attributs data
    card.setAttribute("data-title", resource.title);
    card.setAttribute("data-description", resource.description);
    card.setAttribute("data-difficulty", resource.difficulty);
    card.setAttribute("data-category", resource.category);
    card.setAttribute("data-date", resource.date);
    card.setAttribute("data-status", resource.status);
    card.setAttribute("data-tags", resource.tags.join(", "));

    // Fonction pour obtenir l'icône du statut
    function getStatusIcon(status) {
        switch (status.toLowerCase()) {
            case 'en cours': return 'clock';
            case 'disponible': return 'play-circle';
            case 'soumis': return 'check-circle';
            case 'terminé': return 'check-circle-2';
            default: return 'circle';
        }
    }

    // Fonction pour formater la date
    function formatDate(dateStr) {
        const months = {
            'mai': 'Mai',
            'juin': 'Juin',
            'juillet': 'Juillet'
        };
        return dateStr.replace(/(mai|juin|juillet)/i, match => months[match.toLowerCase()]);
    }

    card.innerHTML = `
        <div class="card-header">
            <span class="resource-category" data-category="${resource.category}">
                ${resource.category.charAt(0).toUpperCase() + resource.category.slice(1)}
            </span>
            <span class="resource-difficulty" data-difficulty="${resource.difficulty}">
                ${resource.difficulty.charAt(0).toUpperCase() + resource.difficulty.slice(1)}
            </span>
        </div>
        
        <div class="card-body">
            <h3 class="resource-title">${resource.title}</h3>
            <p class="resource-description">
                ${resource.description}
            </p>
            <div class="resource-tags">
                ${resource.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
            </div>
            <div class="resource-info">
                <div class="resource-date">
                    <i data-lucide="calendar"></i>
                    ${formatDate(resource.date)}
                </div>
                <div class="resource-participants">
                    <i data-lucide="users"></i>
                    ${resource.participants} participants
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <span class="resource-status" data-status="${resource.status.toLowerCase()}">
                <i data-lucide="${getStatusIcon(resource.status)}"></i>
                ${resource.status}
            </span>
            <div class="card-actions">
                ${resource.status === "En cours" || resource.status === "Disponible" ? `
                    <button class="submit-button" onclick="openSubmitModal(this)">
                        <i data-lucide="upload-cloud"></i>
                        ${resource.status === "En cours" ? "Soumettre" : "Commencer"}
                    </button>
                ` : ""}
                <span class="resource-details" onclick="openDetailsModal(this)">
                    <i data-lucide="info"></i>
                    Détails
                </span>
            </div>
        </div>
    `;

    return card;
}

// Fonction de tri améliorée
function sortResources(resourceList) {
    return resourceList.sort((a, b) => {
        let comparison = 0;

        switch (sortBy) {
            case 'title':
                comparison = a.title.localeCompare(b.title);
                break;
            case 'difficulty':
                const diffOrder = { 'facile': 1, 'intermédiaire': 2, 'avancé': 3 };
                comparison = diffOrder[a.difficulty] - diffOrder[b.difficulty];
                break;
            case 'participants':
                comparison = a.participants - b.participants;
                break;
            case 'date':
            default:
                const dateA = new Date(a.date.replace(/(\d+) (\w+) (\d+)/, '$2 $1, $3'));
                const dateB = new Date(b.date.replace(/(\d+) (\w+) (\d+)/, '$2 $1, $3'));
                comparison = dateA - dateB;
                break;
        }

        return sortOrder === 'asc' ? comparison : -comparison;
    });
}

// Fonction qui affiche dans le conteneur la liste (filtrée) de ressources
function displayResources(resourceList = resources) {
    const grid = document.getElementById("resourcesGrid");

    // Étape 1 : Ajouter une classe de sortie douce
    grid.classList.add("fade-out");

    setTimeout(() => {
        grid.innerHTML = "";

        // Étape 2 : Trier les ressources à afficher
        const sortedResources = sortResources([...resourceList]);

        // Étape 3 : Ajouter les nouvelles cartes avec animation
        sortedResources.forEach((resource, index) => {
            const card = createResourceCard(resource);
            card.classList.add("fade-in");
            card.style.animationDelay = `${index * 0.05}s`;
            grid.appendChild(card);
        });

        // Réinitialiser Lucide (icônes)
        if (typeof lucide !== "undefined" && lucide.createIcons) {
            lucide.createIcons();
        }

        // Étape 4 : Supprimer la classe de sortie
        grid.classList.remove("fade-out");
    }, 200); // délai égal à la durée de l'animation de sortie
}


// Appliquer les filtres et la recherche avec une meilleure logique
function applyFilters() {
    const filtered = resources.filter(resource => {
        const matchCategory = selectedCategory === "all" || resource.category.toLowerCase() === selectedCategory.toLowerCase();
        const matchDifficulty = selectedDifficulty === "all" || resource.difficulty.toLowerCase() === selectedDifficulty.toLowerCase();
        const matchStatus = selectedStatus === "all" || resource.status.toLowerCase() === selectedStatus.toLowerCase();
        const matchSearch = searchTerm === "" ||
            resource.title.toLowerCase().includes(searchTerm) ||
            resource.description.toLowerCase().includes(searchTerm) ||
            resource.category.toLowerCase().includes(searchTerm) ||
            resource.tags.some(tag => tag.toLowerCase().includes(searchTerm));
        return matchCategory && matchDifficulty && matchStatus && matchSearch;
    });

    if (filtered.length === 0) {
        const grid = document.getElementById("resourcesGrid");
        grid.innerHTML = `
            <div class="col-span-full flex flex-col items-center justify-center text-center py-16 px-4 gap-4">
            <i data-lucide="search-x" class="w-16 h-16 text-blue-500"></i>
        
            <h2 class="text-xl text-white font-semibold">
                Aucun défi trouvé
            </h2>
        
            <p class="text-sm text-zinc-400 max-w-md">
                Aucun challenge ne correspond à vos critères actuels.<br />
                Essayez de modifier vos filtres ou votre recherche.
            </p>
        
            <button
                onclick="resetFilters()"
                class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow transition"
            >
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                Réinitialiser les filtres
            </button>
            </div>
        `;
        lucide.createIcons();


        updateResultsCounter(0, resources.length);
        updateActiveFiltersIndicator();
        return; // Arrêter ici car pas besoin d’afficher de cartes
    }


    displayResources(filtered);

    // Mise à jour du compteur de résultats
    updateResultsCounter(filtered.length, resources.length);

    // Mise à jour des indicateurs de filtres actifs
    updateActiveFiltersIndicator();
}

// Fonction pour mettre à jour le compteur de résultats
function updateResultsCounter(filteredCount, totalCount) {
    let counter = document.getElementById('resultsCounter');
    if (!counter) {
        counter = document.createElement('div');
        counter.id = 'resultsCounter';
        counter.className = 'results-counter';
        const grid = document.getElementById('resourcesGrid');
        grid.parentNode.insertBefore(counter, grid);
    }

    const filterInfo = getActiveFiltersText();

    counter.innerHTML = `
        <div class="results-info">
            <span class="results-text">
                ${filteredCount} défi${filteredCount > 1 ? 's' : ''} trouvé${filteredCount > 1 ? 's' : ''} sur ${totalCount}
            </span>
            ${filterInfo ? `<span class="active-filters">${filterInfo}</span>` : ''}
        </div>
        <div class="sort-controls">
            <select id="sortSelect" onchange="handleSortChange(this.value)">
                <option value="date-desc">Date (récent)</option>
                <option value="date-asc">Date (ancien)</option>
                <option value="title-asc">Titre (A-Z)</option>
                <option value="title-desc">Titre (Z-A)</option>
                <option value="difficulty-asc">Difficulté (croissant)</option>
                <option value="difficulty-desc">Difficulté (décroissant)</option>
                <option value="participants-desc">Participants (plus)</option>
                <option value="participants-asc">Participants (moins)</option>
            </select>
        </div>
    `;
}

// Fonction pour obtenir le texte des filtres actifs
function getActiveFiltersText() {
    const activeFilters = [];

    if (selectedCategory !== "all") {
        activeFilters.push(`Catégorie: ${selectedCategory}`);
    }
    if (selectedDifficulty !== "all") {
        activeFilters.push(`Difficulté: ${selectedDifficulty}`);
    }
    if (selectedStatus !== "all") {
        activeFilters.push(`Statut: ${selectedStatus}`);
    }
    if (searchTerm) {
        activeFilters.push(`Recherche: "${searchTerm}"`);
    }

    return activeFilters.length > 0 ? `Filtres actifs: ${activeFilters.join(', ')}` : '';
}

// Fonction pour mettre à jour l'indicateur de filtres actifs
function updateActiveFiltersIndicator() {
    const filterButton = document.getElementById('filterButton');
    const activeCount = getActiveFiltersCount();

    // Supprimer l'ancien badge s'il existe
    const existingBadge = filterButton.querySelector('.filter-badge');
    if (existingBadge) {
        existingBadge.remove();
    }

    if (activeCount > 0) {
        const badge = document.createElement('span');
        badge.className = 'filter-badge';
        badge.textContent = activeCount;
        filterButton.appendChild(badge);
        filterButton.classList.add('has-active-filters');
    } else {
        filterButton.classList.remove('has-active-filters');
    }
}

// Fonction pour compter les filtres actifs
function getActiveFiltersCount() {
    let count = 0;
    if (selectedCategory !== "all") count++;
    if (selectedDifficulty !== "all") count++;
    if (selectedStatus !== "all") count++;
    if (searchTerm) count++;
    return count;
}

// Fonction pour gérer le changement de tri
function handleSortChange(value) {
    const [field, order] = value.split('-');
    sortBy = field;
    sortOrder = order;
    applyFilters();
}

// Gestion du clic sur les filtres avec animation améliorée
document.getElementById("filterTags").addEventListener("click", (e) => {
    if (e.target.classList.contains("filter-tag")) {
        // Animation de clic
        e.target.style.transform = 'scale(0.95)';
        setTimeout(() => {
            e.target.style.transform = 'scale(1)';
        }, 150);

        // Selon la section de filtre
        if (e.target.dataset.category) {
            document.querySelectorAll(".categories .filter-tag").forEach(tag => tag.classList.remove("active"));
            e.target.classList.add("active");
            selectedCategory = e.target.dataset.category.toLowerCase();
        }
        if (e.target.dataset.difficulty) {
            document.querySelectorAll(".difficulty-levels .filter-tag").forEach(tag => tag.classList.remove("active"));
            e.target.classList.add("active");
            selectedDifficulty = e.target.dataset.difficulty.toLowerCase();
        }
        if (e.target.dataset.status) {
            document.querySelectorAll(".status-filters .filter-tag").forEach(tag => tag.classList.remove("active"));
            e.target.classList.add("active");
            selectedStatus = e.target.dataset.status.toLowerCase();
        }

        applyFilters();
    }
});

// Gestion de la recherche avec debounce pour les performances
let searchTimeout;
document.getElementById("searchInput").addEventListener("input", (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchTerm = e.target.value.toLowerCase();
        applyFilters();
    }, 300);
});

// Fonction pour réinitialiser tous les filtres avec animation
function resetFilters() {
    // Animation du bouton de reset
    const resetBtn = document.querySelector('.reset-filters');
    resetBtn.style.transform = 'scale(0.95)';
    setTimeout(() => {
        resetBtn.style.transform = 'scale(1)';
    }, 150);

    // Réinitialise les variables globales
    selectedCategory = "all";
    selectedDifficulty = "all";
    selectedStatus = "all";
    searchTerm = "";
    sortBy = "date";
    sortOrder = "desc";

    // Réinitialiser la valeur de la barre de recherche
    document.getElementById('searchInput').value = "";

    // Réinitialiser le sélecteur de tri
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.value = "date-desc";
    }

    // Supprime la classe active de tous les tags de filtre avec animation
    document.querySelectorAll('.filter-tag').forEach((tag, index) => {
        setTimeout(() => {
            tag.classList.remove("active");
        }, index * 30);
    });

    // Affiche toutes les ressources
    setTimeout(() => {
        applyFilters();
    }, 200);
}

// Fonctions pour les modals (à implémenter selon vos besoins)
function openSubmitModal(button) {
    const card = button.closest('.resource-card');
    const title = card.getAttribute('data-title');
    console.log('Ouvrir modal de soumission pour:', title);
}

function openDetailsModal(element) {
    const card = element.closest('.resource-card');
    const title = card.getAttribute('data-title');
    console.log('Ouvrir modal de détails pour:', title);
}

// Fonction pour créer des filtres rapides
function createQuickFilters() {
    const quickFiltersContainer = document.createElement('div');
    quickFiltersContainer.className = 'quick-filters';
    quickFiltersContainer.innerHTML = `
        <h4>Filtres rapides</h4>
        <div class="quick-filter-buttons">
            <button class="quick-filter" onclick="applyQuickFilter('disponible')">
                <i data-lucide="play-circle"></i>
                Défis disponibles
            </button>
            <button class="quick-filter" onclick="applyQuickFilter('facile')">
                <i data-lucide="zap"></i>
                Niveau facile
            </button>
            <button class="quick-filter" onclick="applyQuickFilter('populaire')">
                <i data-lucide="trending-up"></i>
                Plus populaires
            </button>
        </div>
    `;

    const filtersContainer = document.getElementById('filterTags');
    filtersContainer.insertBefore(quickFiltersContainer, filtersContainer.firstChild);
}

// Fonction pour appliquer les filtres rapides
function applyQuickFilter(type) {
    resetFilters();

    switch (type) {
        case 'disponible':
            selectedStatus = 'disponible';
            document.querySelector('[data-status="disponible"]').classList.add('active');
            break;
        case 'facile':
            selectedDifficulty = 'facile';
            document.querySelector('[data-difficulty="facile"]').classList.add('active');
            break;
        case 'populaire':
            sortBy = 'participants';
            sortOrder = 'desc';
            const sortSelect = document.getElementById('sortSelect');
            if (sortSelect) {
                sortSelect.value = 'participants-desc';
            }
            break;
    }

    applyFilters();
}

// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
    // Masquer par défaut le conteneur des filtres
    const container = document.getElementById("filterTags");
    if (container) {
        container.classList.remove("show");
        // Ajouter les filtres rapides
        createQuickFilters();
    }

    // Afficher les ressources avec animation
    displayResources();
});