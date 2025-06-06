// Variables globales de filtrage améliorées
let selectedCategory = "all";
let selectedDifficulty = "all";
let selectedStatus = "all";
let searchTerm = "";
let sortBy = "date"; // date, title, difficulty
let sortOrder = "desc"; // asc, desc
let currentView = 'list'; // 'list' ou 'detail'
let currentChallenge = null;
let isRegistredToHackathon;

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
        participants: 15,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Application Mobile React Native",
        description: "Développez une application mobile cross-platform avec React Native, Redux et Firebase pour la gestion de tâches.",
        difficulty: "avancé",
        category: "mobile",
        date: "29 mai 2025",
        status: "Soumis",
        tags: ["React Native", "Redux", "Firebase"],
        participants: 8,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Architecture Microservices",
        description: "Développez une architecture microservices complète avec Docker, Kubernetes et monitoring avancé.",
        difficulty: "avancé",
        category: "devops",
        date: "30 juin 2025",
        status: "En cours",
        tags: ["Docker", "Kubernetes", "NodeJS"],
        participants: 5,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Application Web Frontend avec React",
        description: "Créez une interface utilisateur moderne pour un tableau de bord d'analytique avec graphiques interactifs.",
        difficulty: "intermédiaire",
        category: "frontend",
        date: "30 juin 2025",
        status: "Disponible",
        tags: ["React", "TypeScript", "Recharts"],
        participants: 22,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Application d'IA pour la Reconnaissance d'Images",
        description: "Développez un système de reconnaissance d'images utilisant le deep learning et TensorFlow.",
        difficulty: "avancé",
        category: "ia",
        date: "20 juin 2025",
        status: "Disponible",
        tags: ["Python", "TensorFlow", "Keras"],
        participants: 12,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Application Blockchain Simple",
        description: "Développez une application décentralisée (DApp) simple sur Ethereum avec smart contracts.",
        difficulty: "intermédiaire",
        category: "blockchain",
        date: "25 juin 2025",
        status: "Disponible",
        tags: ["Solidity", "Web3.js", "React"],
        participants: 7,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Système de Base de Données NoSQL",
        description: "Concevez et implémentez un système de base de données NoSQL pour des données à grande échelle.",
        difficulty: "avancé",
        category: "database",
        date: "15 juillet 2025",
        status: "Disponible",
        tags: ["MongoDB", "Redis", "ElasticSearch"],
        participants: 18,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    },
    {
        title: "Site Web Statique avec Optimisation SEO",
        description: "Créez un site vitrine optimisé pour les moteurs de recherche et les performances.",
        difficulty: "facile",
        category: "web",
        date: "10 juin 2025",
        status: "Terminé",
        tags: ["HTML5", "CSS3", "JavaScript"],
        participants: 31,
        documentation: "https://example.com/docs",
        repository: "https://example.com/repo"
    }
];

async function checkUserRegistration() {
    try {
        const response = await fetch(`/HACKATHON_ESGIS/public/api/user/registration`);
        const data = await response.json();
        isRegistredToHackathon = data.data;
        return isRegistredToHackathon;
    } catch (error) {
        console.log(error);
    }
}

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
    checkUserRegistration();
});

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
                ${resource.status === "En cours" ? `
                    <button class="submit-button" onclick="submitSolution('${resource.id}')">
                        <i data-lucide="upload-cloud"></i>
                        Soumettre
                    </button>
                ` : resource.status === "Disponible" ? `
                    <button class="submit-button" onclick="participateInChallenge('${resource.id}')">
                        <i data-lucide="upload-cloud"></i>
                        Participer
                    </button>
                ` : ``}
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
    if (resourceList.length === 0) {
        const grid = document.getElementById("resourcesGrid");
        grid.classList.add("fade-out");
        setTimeout(() => {
            grid.innerHTML = "";
            const noResults = document.getElementById("noResults");
            noResults.classList.remove("hidden");
            noResults.classList.add("flex");
        }, 200);
        return;
    }
    // S'assurer qu'on est en vue liste
    if (currentView !== 'list') {
        return;
    }

    // if (!isRegistredToHackathon) {
    //     const grid = document.getElementById("resourcesGrid");
    //     grid.classList.add("fade-out");
    //     setTimeout(() => {
    //         grid.innerHTML = "";
    //         const noResults = document.getElementById("noResults");
    //         noResults.classList.remove("hidden");
    //     }, 200);
    //     return;
    // }
    const grid = document.getElementById("resourcesGrid");

    // Ajouter une classe de sortie douce
    grid.classList.add("fade-out");

    setTimeout(() => {
        grid.innerHTML = "";

        // Trier les ressources à afficher
        const sortedResources = sortResources([...resourceList]);

        // Ajouter les nouvelles cartes avec animation
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

        // Supprimer la classe de sortie
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
        const emptyState = document.getElementById("noResults");
        grid.innerHTML = ``;
        emptyState.classList.remove("hidden");
        emptyState.classList.add("flex");
        grid.classList.add("fade-out");
        lucide.createIcons();
        updateResultsCounter(0, resources.length);
        updateActiveFiltersIndicator();
        return;
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

// TODO: Implementer les modals
function openSubmitModal(button) {
    const card = button.closest('.resource-card');
    const title = card.getAttribute('data-title');
    console.log('Ouvrir modal de soumission pour:', title);
}

function openDetailsModal(element) {
    const card = element.closest('.resource-card');
    const title = card.getAttribute('data-title');

    // Trouver la ressource correspondante
    const resource = resources.find(r => r.title === title);
    if (resource) {
        currentChallenge = resource;
        currentView = 'detail';
        displayChallengeDetail(resource);
    }
}

function displayChallengeDetail(challenge) {
    const grid = document.getElementById("resourcesGrid");
    const noResults = document.getElementById("noResults");

    // Masquer le message "aucun défi trouvé" s'il est visible
    if (!noResults.classList.contains("hidden")) {
        noResults.classList.add("hidden");
        noResults.classList.remove("flex");
    }

    if (!challenge) {
        return;
    }

    // Animation de sortie
    grid.classList.add("fade-out");

    setTimeout(() => {
        grid.innerHTML = createChallengeDetailCard(challenge);

        // Réinitialiser Lucide (icônes)
        if (typeof lucide !== "undefined" && lucide.createIcons) {
            lucide.createIcons();
        }

        // Animation d'entrée
        grid.classList.remove("fade-out");
        const detailCard = grid.querySelector('.challenge-detail-card');
        if (detailCard) {
            detailCard.classList.add('fade-in-up');
        }
    }, 200);
}

function createChallengeDetailCard(challenge) {
    // Fonction pour obtenir l'icône de catégorie
    function getCategoryIcon(category) {
        const icons = {
            'backend': 'server',
            'frontend': 'layout',
            'mobile': 'smartphone',
            'web': 'globe',
            'ia': 'brain',
            'blockchain': 'link',
            'database': 'database',
            'devops': 'settings'
        };
        return icons[category.toLowerCase()] || 'code';
    }

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

    function getRemainingDays(dateStr) {
        const months = {
            'janvier': 0, 'février': 1, 'mars': 2, 'avril': 3,
            'mai': 4, 'juin': 5, 'juillet': 6, 'août': 7,
            'septembre': 8, 'octobre': 9, 'novembre': 10, 'décembre': 11
        };

        const [day, month, year] = dateStr.split(' ');
        const date = new Date(year, months[month.toLowerCase()], parseInt(day));
        const now = new Date();
        const diffTime = date - now;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return Math.max(0, diffDays);
    }

    return `
        <div class="challenge-detail-card col-span-full">
            <div class="detail-header">
                <button class="back-to-list-btn" onclick="backToList()">
                    <i data-lucide="arrow-left"></i>
                    Retour à la liste
                </button>
                <div class="detail-badges">
                    <span class="detail-category" data-category="${challenge.category}">
                        <i data-lucide="${getCategoryIcon(challenge.category)}"></i>
                        ${challenge.category.charAt(0).toUpperCase() + challenge.category.slice(1)}
                    </span>
                    <span class="detail-status" data-status="${challenge.status.toLowerCase()}">
                        <i data-lucide="${getStatusIcon(challenge.status)}"></i>
                        ${challenge.status}
                    </span>
                </div>
            </div>
            
            <div class="detail-content">
                <div class="detail-main">
                    <h1 class="detail-title">${challenge.title}</h1>
                    <p class="detail-description">${challenge.description}</p>
                    
                    <div class="detail-info-grid">
                        <div class="info-card">
                            <div class="info-header">
                                <i data-lucide="calendar"></i>
                                <h3>Date limite</h3>
                            </div>
                            <p>${formatDate(challenge.date)}</p>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-header">
                                <i data-lucide="users"></i>
                                <h3>Participants</h3>
                            </div>
                            <p>${challenge.participants} inscrits</p>
                        </div>
                    </div>
                    
                    <div class="detail-technologies">
                        <h3>Technologies requises</h3>
                        <div class="tech-tags">
                            ${challenge.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
                        </div>
                    </div>
                    
                    <div class="detail-objectives">
                        <h3>Objectifs du défi</h3>
                        <ul class="objectives-list">
                            <li>Développer une solution fonctionnelle</li>
                            <li>Respecter les bonnes pratiques de développement</li>
                            <li>Documenter le code et les choix techniques</li>
                        </ul>
                    </div>
                </div>
                
                <div class="detail-sidebar">
                    <div class="participation-card">
                        <div class="participation-stats">
                            <div class="stat">
                                <span class="stat-number">${challenge.participants}</span>
                                <span class="stat-label">Participants</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">
                                    ${getRemainingDays(challenge.date)}
                                </span>
                                <span class="stat-label">Jours restants</span>
                            </div>
                        </div>
                        
                        ${challenge.status.toLowerCase() === 'soumis' ? `
                            <button class="participate-btn disabled" disabled>
                                <i data-lucide="lock"></i>
                                Défi ${challenge.status.toLowerCase()}
                            </button>
                        ` : challenge.status.toLowerCase() === 'en cours' ? `
                            <button class="submit-btn" onclick="submitSolution('${challenge.id}')">
                                <i data-lucide="upload"></i>
                                Soumettre votre solution
                            </button>
                        ` : challenge.status.toLowerCase() === 'disponible' ? `
                            <button class="participate-btn" onclick="participateInChallenge('${challenge.id}')">
                                <i data-lucide="user-plus"></i>
                                Participer au défi
                            </button>
                        ` : ''}
                        
                        <div class="participation-note">
                            <i data-lucide="info"></i>
                            <p>En participant, vous acceptez de respecter les règles du hackathon et de soumettre votre projet avant la date limite.</p>
                        </div>
                    </div>
                    
                    <div class="resources-card">
                        <h3>Ressources utiles</h3>
                        <ul class="resource-links">
                            <li>
                                <a href="${challenge.documentation}" class="resource-link" target="_blank">
                                    <i data-lucide="book"></i>
                                    Documentation
                                </a>
                            </li>
                            <li>
                                <a href="${challenge.repository}" class="resource-link" target="_blank">
                                    <i data-lucide="github"></i>
                                    Repository
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function backToList() {
    currentView = 'list';
    currentChallenge = null;
    applyFilters(); // Cela va réafficher la liste avec les filtres actuels
}

// TODO: Implementer la participation au défi
function participateInChallenge(challengeId) {
    console.log('Participation au défi:', challengeId);
    // Ici vous pouvez ajouter la logique pour inscrire l'utilisateur
    // Par exemple, appel API, mise à jour du statut, etc.

    // Exemple de notification de succès
    showNotification('Vous vous êtes inscrit au défi: ' + challengeId, 'success');

    // Optionnel: mettre à jour le nombre de participants localement
    const challenge = resources.find(r => r.id === challengeId);
    if (challenge) {
        challenge.participants += 1;
        // Mettre à jour l'affichage
        displayChallengeDetail(challenge);
    }
}

// TODO: Implementer la soumission de la solution
function submitSolution(challengeId) {
    console.log('Soumission de la solution pour le défi:', challengeId);
    // Ici vous pouvez ajouter la logique pour soumettre la solution
    // Par exemple, appel API, mise à jour du statut, etc.

    // Exemple de notification de succès
    showNotification('Votre solution a été soumise avec succès pour le défi: ' + challengeId, 'success');

    // Optionnel: mettre à jour le statut localement
    const challenge = resources.find(r => r.id === challengeId);
    if (challenge) {
        challenge.status = 'soumis';
        // Mettre à jour l'affichage
        displayChallengeDetail(challenge);
    }
}

function redirectToSubmission(challengeId) {
    console.log('Redirection vers la soumission pour le défi:', challengeId);
    // Ici vous pouvez ajouter la logique pour rediriger l'utilisateur vers la page de soumission
}

function isUserRegistered(challengeId) {
    // Ici vous pouvez ajouter la logique pour vérifier si l'utilisateur est inscrit au défi
    // Par exemple, appel API, vérification du statut, etc.
    return false; // Exemple de retour
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