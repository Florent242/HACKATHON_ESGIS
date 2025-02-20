document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    lucide.createIcons();

    // Handle notification button
    const notificationBtn = document.querySelector('.notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', () => {
            alert('Notifications coming soon!');
        });
    }

    // Handle hero buttons
    const startJourneyBtn = document.querySelector('.btn-primary');
    const exploreChallengesBtn = document.querySelector('.btn-secondary');

    startJourneyBtn?.addEventListener('click', () => {
        // window.location.href = '/HACKATHON_ESGIS/public/signup';
        alert('Start a new challenge coming soon!');
    });

    exploreChallengesBtn?.addEventListener('click', () => {
        window.location.href = '/HACKATHON_ESGIS/public/challenges';
    });

    // Animate stats on scroll
    const stats = document.querySelectorAll('.stat-item h3');
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
                if (stat.textContent.includes('$')) {
                    stat.textContent = '$' + stat.textContent;
                }
            }, 50);
        });
    };

    // Intersection Observer for stats animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                observer.unobserve(entry.target);
            }
        });
    });

    const statsSection = document.querySelector('.stats');
    if (statsSection) {
        observer.observe(statsSection);
    }

    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    // Add parallax effect to hero section
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            hero.style.backgroundPositionY = scrolled * 0.5 + 'px';
        });
    }
});