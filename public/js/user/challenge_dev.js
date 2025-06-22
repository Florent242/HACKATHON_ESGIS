console.log("challenge_dev.js chargé");
// Variable globale pour stocker les challenges
let allChallenges = [];

/**
 * Initialisation à la fin du chargement du DOM
 */
document.addEventListener('DOMContentLoaded', async () => {
    // Récupération de l'ID du hackathon depuis l'URL
    const urlParams = new URLSearchParams(window.location.search);
    const hackathonId = urlParams.get('hackathon_id');

    if (hackathonId) {
        await fetchChallenges(hackathonId);
    } else {
        console.error("Hackathon ID manquant dans l'URL.");
        const grid = document.getElementById('challenges-grid');
        if (grid) {
            grid.innerHTML = `<p class="text-red-400 text-center col-span-full">Impossible de charger les challenges : ID du hackathon non spécifié.</p>`;
        }
    }
});

/**
 * Récupère les challenges depuis l'API
 * @param {number} hackathonId 
 */
async function fetchChallenges(hackathonId) {
    const grid = document.getElementById('challenges-grid');
    try {
        if (grid) grid.innerHTML = `<p class="text-gray-400 text-center col-span-full">Chargement des challenges...</p>`;

        const response = await fetch('/api/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ hackathon_id: hackathonId })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Erreur HTTP' }));
            throw new Error(`Erreur ${response.status}: ${errorData.message || 'Impossible de contacter le serveur'}`);
        }

        allChallenges = await response.json();
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

    challenges.forEach(challenge => {
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

    card.innerHTML = `
        <div class="flex items-center gap-2 mb-2">
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-900/40 text-blue-200">${challenge.type || 'Type inconnu'}</span>
        </div>
        <h2 class="text-xl font-semibold mb-1">${challenge.title || 'Challenge sans titre'}</h2>
        <p class="text-sec mb-4 h-20 overflow-hidden">${challenge.description || 'Pas de description.'}</p>
        <div class="flex gap-2 mb-2">
            <span class="${difficultyClass} px-2 py-0.5 rounded text-xs font-medium capitalize">${challenge.difficulty || 'N/A'}</span>
        </div>
    `;
    return card;
}