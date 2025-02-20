// script.js
const leaderboardData = [
    {
        name: "Florent Boudz",
        specialty: "Full-Stack",
        points: 2500,
        badges: 15,
    },
    {
        name: "Adechina B",
        specialty: "Security",
        points: 2350,
        badges: 12,
    },
    {
        name: "Imma ODJO",
        specialty: "Frontend",
        points: 2200,
        badges: 10,
    },
    {
        name: "Kenel",
        specialty: "Backend",
        points: 2100,
        badges: 9,
    },
    {
        name: "Eliot",
        specialty: "DevOps",
        points: 2000,
        badges: 8,
    }
];

function createLeaderboardItem(user, index) {
    const item = document.createElement('div');
    item.className = 'leaderboard-item';

    const rankSymbol = index === 0 ? '🏆' : (index + 1);
    const rankClass = index === 0 ? 'rank-1' : '';

    item.innerHTML = `
        <div class="user-info">
            <div class="rank-circle ${rankClass}">${rankSymbol}</div>
            <div class="user-details">
                <h3>
                    ${user.name}
                    <span class="specialty">${user.specialty}</span>
                </h3>
                <div class="user-stats">
                    ${user.points} points • ${user.badges} badges earned
                </div>
            </div>
        </div>
        <div class="rank-number">#${index + 1}</div>
    `;

    return item;
}

function initLeaderboard() {
    const leaderboardContainer = document.getElementById('leaderboard');
    
    leaderboardData.forEach((user, index) => {
        const item = createLeaderboardItem(user, index);
        leaderboardContainer.appendChild(item);
    });
}

// Initialiser le leaderboard au chargement de la page
document.addEventListener('DOMContentLoaded', initLeaderboard);