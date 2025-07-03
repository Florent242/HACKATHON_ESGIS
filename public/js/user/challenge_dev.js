console.log("challenge_dev.js chargé");
// Variable globale pour stocker les challenges
let allChallenges = [];

// Les styles des boutons sont déjà définis dans le HTML

/**
 * Initialisation à la fin du chargement du DOM
 */
// Variable pour stocker le filtre actif
let currentFilter = 'all';
let currentSort = 'none'; // Ajout pour le tri
let currentSearch = ''; // Ajout pour la recherche

document.addEventListener('DOMContentLoaded', async () => {
    // Sélectionner tous les boutons de filtre
    const filterButtons = document.querySelectorAll('.custom-btn');

    // Ajouter les attributs data-filter aux boutons
    const filters = {
        'Tous': 'all',
        'Facile': 'easy',
        'Moyen': 'medium',
        'Difficile': 'hard'
    };

    filterButtons.forEach(button => {
        const filterValue = filters[button.textContent.trim()];
        if (filterValue) {
            button.setAttribute('data-filter', filterValue);

            // Ajouter les événements de clic
            button.addEventListener('click', () => {
                // Mettre à jour les styles des boutons
                filterButtons.forEach(btn => {
                    btn.classList.remove('bg-primary', 'text-white');
                    btn.classList.add('bg-card', 'text-main', 'border', 'border-main');
                });

                // Appliquer le style actif
                button.classList.remove('bg-card', 'text-main', 'border', 'border-main');
                button.classList.add('bg-primary', 'text-white');

                // Mettre à jour le filtre et réafficher
                currentFilter = filterValue;
                displayChallenges(allChallenges);
            });
        }
    });

    // Ajout de la recherche
    const searchInput = document.querySelector('input[type="text"][placeholder*="Rechercher"]');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            currentSearch = e.target.value.toLowerCase();
            displayChallenges(allChallenges);
        });
    }

    // Ajout des boutons de tri (asc/desc)
    const sortAscBtn = document.createElement('button');
    sortAscBtn.textContent = 'Tri difficulté ↑';
    sortAscBtn.className = 'custom-btn bg-card text-main px-2 py-1 rounded-lg border border-main';
    sortAscBtn.style.marginLeft = '8px';
    sortAscBtn.addEventListener('click', () => {
        currentSort = 'asc';
        displayChallenges(allChallenges);
    });

    const sortDescBtn = document.createElement('button');
    sortDescBtn.textContent = 'Tri difficulté ↓';
    sortDescBtn.className = 'custom-btn bg-card text-main px-2 py-1 rounded-lg border border-main';
    sortDescBtn.style.marginLeft = '4px';
    sortDescBtn.addEventListener('click', () => {
        currentSort = 'desc';
        displayChallenges(allChallenges);
    });

    // Ajout des boutons de tri à côté des filtres
    const filterContainer = document.querySelector('.flex.gap-2');
    if (filterContainer) {
        filterContainer.appendChild(sortAscBtn);
        filterContainer.appendChild(sortDescBtn);
    }

    await fetchChallenges(2);
});

/**
 * Récupère les challenges depuis l'API
 * @param {number} hackathonId 
 */
async function fetchChallenges(hackathonId) {
    
    const grid = document.getElementById('challenges-grid');
    try {
        if (grid) grid.innerHTML = `<p class="text-gray-400 text-center col-span-full">Chargement des challenges...</p>`;
        const user_id= await getUserId()
        const response = await apiRequest(`/challenges/dev/2/${user_id}`, {
            method: "GET",
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        console.log(response);

        if (!response.success) {
            const errorData = await response.json().catch(() => ({ message: 'Erreur HTTP' }));
            throw new Error(`Erreur ${response.status}: ${errorData.message || 'Impossible de contacter le serveur'}`);
        }

        allChallenges = response.data;
        console.log(allChallenges);
        displayChallenges(allChallenges);

    } catch (error) {
        console.error('Erreur lors de la récupération des challenges:', error);
        if (grid) {
            grid.innerHTML = `<p class="text-red-400 text-center col-span-full">Une erreur est survenue: ${error.message}.</p>`;
        }
    }
}

/**
 * Affiche les challenges dans la grille
 * @param {Array} challenges 
 */
function displayChallenges(challenges) {
    const grid = document.getElementById('challenges-grid');
    if (!grid) return;

    grid.innerHTML = '';
    if (!challenges || challenges.length === 0) {
        grid.innerHTML = `<p class="text-gray-400 text-center col-span-full">Aucun challenge disponible pour le moment.</p>`;
        return;
    }

    // Filtrer par difficulté
    let filteredChallenges = currentFilter === 'all' 
        ? challenges 
        : challenges.filter(challenge => challenge.difficulty === currentFilter);

    // Filtrer par recherche
    if (currentSearch && currentSearch.length > 0) {
        filteredChallenges = filteredChallenges.filter(challenge => {
            const title = (challenge.title || '').toLowerCase();
            const description = (challenge.description || '').toLowerCase();
            return title.includes(currentSearch) || description.includes(currentSearch);
        });
    }

    // Trier par difficulté si demandé
    if (currentSort === 'asc' || currentSort === 'desc') {
        const difficultyOrder = { 'easy': 1, 'medium': 2, 'hard': 3 };
        filteredChallenges.sort((a, b) => {
            const aVal = difficultyOrder[a.difficulty] || 99;
            const bVal = difficultyOrder[b.difficulty] || 99;
            return currentSort === 'asc' ? aVal - bVal : bVal - aVal;
        });
    }

    if (filteredChallenges.length === 0) {
        grid.innerHTML = `<p class="text-gray-400 text-center col-span-full">Aucun challenge trouvé.</p>`;
        return;
    }

    filteredChallenges.forEach(challenge => {
        const card = createChallengeCard(challenge);
        grid.appendChild(card);
    });
}

/**
 * Crée une carte HTML pour un challenge
 * @param {object} challenge 
 * @returns {HTMLElement}
 */
function createChallengeCard(challenge) {
    const card = document.createElement("div");
    card.className = "bg-card p-6 shadow flex flex-col relative card";

    // Couleur selon la difficulté
    const difficultyColors = {
        'easy': 'bg-green-900/40 text-green-300',
        'medium': 'bg-yellow-900/40 text-yellow-300',
        'hard': 'bg-red-900/40 text-red-300'
    };
    const difficultyClass = difficultyColors[challenge.difficulty] || 'bg-gray-700 text-gray-300';

    // Badge statut et bouton
    let statusBadge = '';
    let actionButton = '';
    if (challenge.submission_status === null) {
        statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-700 text-gray-400">Non tenté</span>';
        actionButton = `<button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition ml-auto flex items-center gap-2 challenge-link-btn" data-challenge-id="${challenge.id}"><i class="fa fa-brain"></i> Voir le défi</button>`;
    } else {
        statusBadge = '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-700 text-green-300">Résolu</span>';
        actionButton = `<button class="bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold transition ml-auto flex items-center gap-2 challenge-link-btn" data-challenge-id="${challenge.id}"><i class="fa fa-brain"></i> Revoir</button>`;
    }

    // Bloc points en haut à droite
    const pointsBlock = `
        <div style="position:absolute;top:1rem;right:1rem;text-align:center;">
            <span style="font-size:1.3rem;font-weight:bold;color:#FFD600;line-height:1;">${challenge.points || 0}</span><br>
            <span style="font-size:0.9rem;color:#bcbcbc;">pts</span>
        </div>
    `;

    card.innerHTML = `
        ${pointsBlock}
        <div class="flex items-center gap-2 mb-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-900/40 text-blue-200">${challenge.type || 'Type inconnu'}</span>
        </div>
        <h2 class="text-xl font-semibold mb-1">${challenge.title || 'Challenge sans titre'}</h2>
        <p class="text-sec mb-4 h-20 overflow-hidden">${challenge.description || 'Pas de description.'}</p>
        <div class="flex gap-2 mb-2">
            <span class="${difficultyClass} px-2 py-0.5 rounded text-xs font-medium capitalize">${challenge.difficulty || 'N/A'}</span>
        </div>
        <hr class="my-2 border-gray-700">
        <div class="flex items-center gap-2 mt-2">
            ${statusBadge}
            ${actionButton}
        </div>
    `;

    // Ajout du listener sur le bouton d'accès au challenge
    const btn = card.querySelector('.challenge-link-btn');
    if (btn) {
        btn.addEventListener('click', (e) => {
            const id = btn.getAttribute('data-challenge-id');
            if (id) {
                window.location.href = `/user/interfacechallenges/${id}`;
            }
        });
    }

    return card;
}