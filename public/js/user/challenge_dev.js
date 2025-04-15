

function toggleFilters() {
    const container = document.getElementById('filterTags');
    const filterText = document.getElementById('filterButton');
    const icon = document.getElementById('filterIcon');

    container.classList.toggle('show');

    if (container.classList.contains('show')) {
        filterText.textContent = 'Masquer filtres';
        icon.classList.replace('chevron-down', 'chevron-up');
    } else {
        filterText.textContent = 'Afficher filtres';
        icon.classList.replace('chevron-up', 'chevron-down');
    }
}
// Initialize filters (hide them by default)
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('filterTags');
    if (container) {
        container.classList.remove('show');
    }
});





// Variables globales de filtrage
let selectedCategory = "all";
let selectedDifficulty = "all";
let searchTerm = "";

// Tableau de ressources (exemples, à adapter ou récupérer via API)
const resources = [
    {
        title: "API REST - Module d’authentification",
        description: "Créez une API REST sécurisée avec un système d’authentification JWT complet.",
        difficulty: "intermédiaire",
        category: "backend",
        date: "29 mai 2025",
        status: "En cours",
        tags: ["NodeJS", "Express", "MongoDB"]
    },
    {
        title: "Application Mobile React Native",
        description: "Développez une application mobile cross-platform avec React Native, Redux et Firebase.",
        difficulty: "avancé",
        category: "mobile",
        date: "29 mai 2025",
        status: "Soumis",
        tags: ["React Native", "Redux", "Firebase"]
    },
    {
        title: "Architecture Microservices",
        description: "Développez une architecture microservices Docker et Kubernetes.",
        difficulty: "avancé",
        category: "devops",
        date: "30 juin 2025",
        status: "En cours",
        tags: ["Docker", "Kubernetes", "NodeJS"]
    },
    {
        title: "Application Web Frontend avec React",
        description: "Créez une interface utilisateur moderne pour un tableau de bord d'analytique.",
        difficulty: "intermédiaire",
        category: "frontend",
        date: "30 juin 2025",
        status: "Disponible",
        tags: ["React", "TypeScript", "MongoDB", "Recharts"]
    }
];

// Fonction qui construit une carte de ressource identique à ta maquette
function createResourceCard(resource) {
    const card = document.createElement("div");
    card.className = "resource-card";
    // Stocker les infos en attributs data si besoin
    card.setAttribute("data-title", resource.title);
    card.setAttribute("data-description", resource.description);
    card.setAttribute("data-difficulty", resource.difficulty);
    card.setAttribute("data-category", resource.category);
    card.setAttribute("data-date", resource.date);
    card.setAttribute("data-status", resource.status);
    card.setAttribute("data-tags", resource.tags.join(", "));

        card.innerHTML = `
        <div class="card-header">
        <span class="resource-category">${resource.category.charAt(0).toUpperCase() + resource.category.slice(1)}</span>
        <span class="resource-difficulty">${resource.difficulty.charAt(0).toUpperCase() + resource.difficulty.slice(1)}</span>
        </div>
        <h3 class="resource-title">${resource.title}</h3>
        <p class="resource-description">
        ${resource.description}
        </p>
        <div class="resource-tags">
        ${resource.tags.map(tag => `<span class="tag">${tag}</span>`).join('')}
        </div>
        <span class="resource-date">${resource.date}</span>
        <div class="card-footer">
        <span class="resource-status">
            <i data-lucide="clock"></i>
            ${resource.status}
        </span>
        ${resource.status === "En cours" ? `
            <button class="submit-button" onclick="openSubmitModal(this)">
            <i data-lucide="upload-cloud"></i>
            Soumettre
            </button>
        ` : ""}
        <span class="resource-details">
            <i data-lucide="info"></i>
            Détails
        </span>
        </div>
    `;
        return card;
    }

// Fonction qui affiche dans le conteneur la liste (filtrée) de ressources
function displayResources(resourceList = resources) {
    const grid = document.getElementById("resourcesGrid");
    grid.innerHTML = "";
    resourceList.forEach(resource => {
        const card = createResourceCard(resource);
        grid.appendChild(card);
    });
    // Mise à jour des icônes avec Lucide (si utilisé)
    if (typeof lucide !== "undefined" && lucide.replace) {
        lucide.replace();
    }
}

// Appliquer les filtres et la recherche
function applyFilters() {
    const filtered = resources.filter(resource => {
        const matchCategory = selectedCategory === "all" || resource.category.toLowerCase() === selectedCategory;
        const matchDifficulty = selectedDifficulty === "all" || resource.difficulty.toLowerCase() === selectedDifficulty;
        const matchSearch = searchTerm === "" ||
            resource.title.toLowerCase().includes(searchTerm) ||
            resource.description.toLowerCase().includes(searchTerm) ||
            resource.tags.some(tag => tag.toLowerCase().includes(searchTerm));
        return matchCategory && matchDifficulty && matchSearch;
    });
    displayResources(filtered);
}

// Gestion du clic sur les filtres (catégorie et difficulté)
document.getElementById("filterTags").addEventListener("click", (e) => {
    if (e.target.classList.contains("filter-tag")) {
        // Selon la section de filtre (catégorie ou difficulté)
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
        applyFilters();
    }
});

// Gestion de la recherche
document.getElementById("searchInput").addEventListener("input", (e) => {
    searchTerm = e.target.value.toLowerCase();
    applyFilters();
});

// Fonction pour réinitialiser tous les filtres
function resetFilters() {
    // Réinitialise les variables globales
    selectedCategory = "all";
    selectedDifficulty = "all";
    searchTerm = "";

    // Réinitialiser la valeur de la barre de recherche
    document.getElementById('searchInput').value = "";

    // Supprime la classe active de tous les tags de filtre
    document.querySelectorAll('.filter-tag').forEach(tag => tag.classList.remove("active"));

    

    // Affiche toutes les ressources
    applyFilters();
}



// Initialisation au chargement de la page
document.addEventListener("DOMContentLoaded", () => {
    // Masquer par défaut le conteneur des filtres (si souhaité)
    const container = document.getElementById("filterTags");
    if (container) {
        container.classList.remove("show");
    }
    displayResources();
});
