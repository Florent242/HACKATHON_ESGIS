document.addEventListener('DOMContentLoaded', () => {
    // Initialize Lucide icons
    lucide.createIcons();

    // Appel pour récupérer les ressources d'un hackathon
    const hackathonId = 1; // Remplace par l'ID approprié
    fetch(`/ressources?hackathonId=${hackathonId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur lors de la récupération des ressources');
            }
            return response.json();
        })
        .then(data => {
            console.log(data);
            // Ici, tu peux ajouter du code pour afficher les ressources dans le DOM
        })
        .catch(error => {
            console.error('Erreur:', error);
        });

    /* Handle scroll for hero section */
    // Select all element that have the .fade-in class for the animation when they are visible
    const fadeElements = document.querySelectorAll('.fade-in');
    // Intersection Observer to trigger the animation when the element is visible...hehe that's cool tho
    const heroObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                heroObserver.unobserve(entry.target); // Here we stop the observer when the element is visible
            }
        });
    });

    fadeElements.forEach(element => {
        heroObserver.observe(element);
    });

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

    // handle start journey button
    startJourneyBtn?.addEventListener('click', () => {
        // window.location.href = '/HACKATHON_ESGIS/public/signup';
        alert('Start a new challenge coming soon!');
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
            console.log('Element bounds:', entry.boundingClientRect);
            console.log('Viewport bounds:', entry.rootBounds);
            console.log('Entry:', entry);
            console.log('Entry is intersecting:', entry.isIntersecting);
            if (entry.isIntersecting) {
                console.log('Observed stats section');
            animateStats();
            observer.unobserve(entry.target);
        }
    });
}, {
    root: null, // Utilise la fenêtre de visualisation
    rootMargin: '0px 0px 100% 0px', // Ajuste la marge inférieure pour déclencher l'animation plus tôt
    threshold: 0.1 // Déclenche l'événement lorsque 10% de l'élément est visible
});

    const statsSection = document.querySelector('.stats');
    console.log('Stats section:', statsSection); // Vérifie si l'élément est trouvé
    if (statsSection) {
        observer.observe(statsSection);
        console.log('Observing stats section'); // Vérifie que l'observation a commencé
    }

    // Add smooth scroll behavior. La partie a[href^="#"] est un sélecteur CSS qui cible tous les éléments <a> (liens) dont l'attribut href commence par le caractère #
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