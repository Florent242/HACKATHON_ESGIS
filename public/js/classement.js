
// Fonction pour créer un élément du leaderboard
function createLeaderboardItem(user, index) {
    const item = document.createElement('div');
    item.className = 'leaderboard-item';
    item.style.animationDelay = `${index * 0.1}s`;

    const rankClass =   index === 0 ? 'rank-trophy' : 
                        index === 1 ? 'rank-silver' : 
                        index === 2 ? 'rank-bronze' : '';

    const rankSymbol = index < 3 ? ['🏆', '🥈', '🥉'][index] : (index + 1);

    item.innerHTML = `
        <div class="user-info">
            <div class="rank-circle ${rankClass}">${rankSymbol}</div>
            <div class="user-details">
                <h3>${user.name}</h3>
                <div class="user-stats">
                    ${user.points} points • ${user.badges} badges earned
                </div>
            </div>
        </div>
    `;

    return item;
}

// Fonction pour mettre à jour les statistiques
function updateStats(data) {
    const totalParticipants = document.getElementById('total-participants');
    const totalPoints = document.getElementById('total-points');
    const totalBadges = document.getElementById('total-badges');

    if (data.length > 0) {
        totalParticipants.textContent = data.length;
        totalPoints.textContent = data.reduce((sum, user) => sum + user.points, 0);
        totalBadges.textContent = data.reduce((sum, user) => sum + user.badges, 0);
    }
}

// Fonction pour charger et afficher le leaderboard
async function loadLeaderboard() {
    try {
        const response = await fetch('../backend/api.php');
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        const data = await response.json();
        
        const leaderboardContainer = document.getElementById('leaderboard');
        leaderboardContainer.innerHTML = ''; // Vider le conteneur

        // Afficher les éléments du leaderboard
        data.forEach((user, index) => {
            const item = createLeaderboardItem(user, index);
            leaderboardContainer.appendChild(item);
        });

        // Mettre à jour les statistiques
        updateStats(data);

    } catch (error) {
        console.error('Erreur lors du chargement du leaderboard:', error);
        // En cas d'erreur, utiliser les données de test
        const leaderboardContainer = document.getElementById('leaderboard');
        leaderboardContainer.innerHTML = '';
        
        // Données de test pour le développement
        const testData = [
            { name: "Florent Boudz", points: 2500, badges: 15 },
            { name: "Adechina B", points: 2350, badges: 12 },
            { name: "Imma ODJO", points: 2200, badges: 10 },
            { name: "Kenel", points: 2100, badges: 9 },
            { name: "Eliot", points: 2000, badges: 8 }
        ];

        testData.forEach((user, index) => {
            const item = createLeaderboardItem(user, index);
            leaderboardContainer.appendChild(item);
        });

        updateStats(testData);
    }
}

// Fonction pour la mise à jour en temps réel
function startRealtimeUpdates() {
    // Mettre à jour toutes les 30 secondes
    setInterval(loadLeaderboard, 30000);
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    loadLeaderboard();
    startRealtimeUpdates();
});