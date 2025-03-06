document.addEventListener('DOMContentLoaded', () => {

    // Handle hero buttons
    const startJourneyBtn = document.querySelector('.btn-primary');
    const exploreChallengesBtn = document.querySelector('.btn-secondary');

    // handle start journey button
    startJourneyBtn?.addEventListener('click', () => {
        // window.location.href = '/HACKATHON_ESGIS/public/signup';
        window.location.href = '/HACKATHON_ESGIS/public/auth';
    });

    // handle explore challenges button
    exploreChallengesBtn?.addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/challenges';
    });

    // Animate stats on scroll
    const stats = document.querySelectorAll('.stat-item h2 span');
    const animateStats = () => {
        stats.forEach(stat => {
            const value = parseInt(stat.textContent);
            let current = 0;
            const increment = value / 30; // Animate over 30 steps
            const timer = setInterval(() => {
                current += increment;
                if (current >= value) {
                    clearInterval(timer);
                    current = value;
                }
                stat.textContent = current.toFixed(0) + (stat.textContent.includes('+') ? '+' : '');
            }, 50);
        });
        console.log('Animating stats');
    };

    // Intersection Observer for stats animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                observer.unobserve(entry.target);
            }
        });
    }, {
        root: null, // Utilise la fenêtre de visualisation
        threshold: 0.9 // Déclenche l'événement lorsque 10% de l'élément est visible
    });

    const statsSection = document.querySelector('#stats');
    if (statsSection) {
        observer.observe(statsSection);
        console.log('Observing stats section'); // Vérifie que l'observation a commencé
    }

    // Add parallax effect to hero section
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            hero.style.backgroundPositionY = scrolled * 0.5 + 'px';
        });
    }

    // Handle event info buttons
    const eventInfo = document.querySelectorAll('.event-info');
    // handle start journey button
    eventInfo?.forEach(button => {
        button.addEventListener('click', () => {
            window.location.href = '/HACKATHON_ESGIS/public/hackathon';
        });
    });
});
