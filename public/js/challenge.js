// Initialize Lucide icons
// lucide.createIcons();

// Add event listeners for interactive elements
document.addEventListener('DOMContentLoaded', () => {
    // Search functionality
    const searchInput = document.querySelector('.search-input-wrapper input');
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.challenge-card');
        
        cards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('p').textContent.toLowerCase();
            const isVisible = title.includes(searchTerm) || description.includes(searchTerm);
            
            card.style.display = isVisible ? 'block' : 'none';
        });
    });

    // Filter button click handler (placeholder)
    const filterBtn = document.querySelector('.filter-btn');
    filterBtn.addEventListener('click', () => {
        alert('Filter functionality coming soon!');
    });

    // View Challenge button click handlers
    const viewButtons = document.querySelectorAll('.view-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', () => {
            const challengeTitle = button.closest('.challenge-card').querySelector('h3').textContent;
            alert(`Viewing challenge: ${challengeTitle}`);
        });
    });

    // Notification button click handler
    const notificationBtn = document.querySelector('.notification-btn');
    notificationBtn.addEventListener('click', () => {
        alert('Notifications coming soon!');
    });

    // Start Challenge button click handler
    const startChallengeBtn = document.querySelector('.start-challenge');
    startChallengeBtn.addEventListener('click', () => {
        alert('Start a new challenge coming soon!');
    });
});