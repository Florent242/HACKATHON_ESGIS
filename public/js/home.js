/**
 * Hack & Stack Landing Page JavaScript
 * Gestion des interactions et animations
 */

class HackStackLanding {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupScrollAnimations();
        this.setupStatsAnimation();
        this.setupParallaxEffects();
        this.setupTypewriterEffect();
        this.createParticles();
    }

    /**
     * Configuration des événements
     */
    setupEventListeners() {
        // Boutons du hero
        const startJourneyBtns = document.querySelectorAll('.btn-primary, .cta-primary');
        const exploreBtns = document.querySelectorAll('.btn-secondary, .cta-secondary');
        const eventBtns = document.querySelectorAll('.btn-event');
        const sponsorBtn = document.querySelector('.become-sponsor');

        startJourneyBtns.forEach(btn => {
            btn?.addEventListener('click', () => this.handleStartJourney());
        });

        exploreBtns.forEach(btn => {
            btn?.addEventListener('click', () => this.handleExploreHackathons());
        });

        eventBtns.forEach(btn => {
            btn?.addEventListener('click', () => this.handleEventInfo());
        });

        sponsorBtn?.addEventListener('click', () => this.handleBecomePartner());

        // Smooth scroll pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', this.handleSmoothScroll);
        });

        // Écouteur de scroll pour les effets
        window.addEventListener('scroll', this.throttle(() => {
            this.handleScroll();
        }, 16));

        // Écouteur de redimensionnement
        window.addEventListener('resize', this.throttle(() => {
            this.handleResize();
        }, 250));
    }

    /**
     * Gestion des clics sur les boutons
     */
    handleStartJourney() {
        // Animation de bouton
        this.animateButton(event.target);
        
        // Redirection (à adapter selon votre structure)
        setTimeout(() => {
            window.location.href = '/auth';
        }, 300);
    }

    handleExploreHackathons() {
        this.animateButton(event.target);
        setTimeout(() => {
            window.location.href = '/hackathon';
        }, 300);
    }

    handleEventInfo() {
        this.animateButton(event.target);
        setTimeout(() => {
            window.location.href = '/hackathon';
        }, 300);
    }

    handleBecomePartner() {
        this.animateButton(event.target);
    }

    /**
     * Animation des boutons au clic
     */
    animateButton(button) {
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = '';
        }, 150);
    }

    /**
     * Gestion du smooth scroll
     */
    handleSmoothScroll(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    /**
     * Configuration des animations au scroll
     */
    setupScrollAnimations() {
        const observerOptions = {
            root: null,
            rootMargin: '0px 0px -100px 0px',
            threshold: 0.1
        };

        this.scrollObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    
                    // Animation spéciale pour les cartes
                    if (entry.target.classList.contains('argument-card') || 
                        entry.target.classList.contains('event-card')) {
                        this.animateCard(entry.target);
                    }
                }
            });
        }, observerOptions);

        // Observer tous les éléments à animer
        document.querySelectorAll('.fade-in, .fade-in-left, .fade-in-right, .argument-card, .event-card')
            .forEach(el => {
                el.classList.add('animate-on-scroll');
                this.scrollObserver.observe(el);
            });
    }

    /**
     * Animation des cartes
     */
    animateCard(card) {
        const delay = Array.from(card.parentNode.children).indexOf(card) * 100;
        setTimeout(() => {
            card.style.transform = 'translateY(0)';
            card.style.opacity = '1';
        }, delay);
    }

    /**
     * Animation des statistiques
     */
    setupStatsAnimation() {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.animateCounters();
                    statsObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.5
        });

        const statsSection = document.querySelector('#stats');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }
    }

    /**
     * Animation des compteurs
     */
    animateCounters() {
        const counters = document.querySelectorAll('.counter');
        
        counters.forEach(counter => {
            const target = parseInt(counter.dataset.target);
            const duration = 2000; // 2 secondes
            const step = target / (duration / 16); // 60 FPS
            let current = 0;

            const updateCounter = () => {
                current += step;
                if (current >= target) {
                    counter.textContent = target;
                } else {
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                }
            };

            updateCounter();
        });
    }

    /**
     * Effets de parallaxe
     */
    setupParallaxEffects() {
        this.parallaxElements = document.querySelectorAll('.orb, .grid-background');
    }

    handleScroll() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;

        // Effet parallaxe sur les orbes
        this.parallaxElements.forEach((el, index) => {
            const speed = 0.5 + (index * 0.2);
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });

        // Effet sur le hero
        const hero = document.querySelector('.hero');
        if (hero && scrolled < window.innerHeight) {
            hero.style.transform = `translateY(${rate}px)`;
            hero.style.opacity = 1 - (scrolled / window.innerHeight);
        }
    }

    /**
     * Gestion du redimensionnement
     */
    handleResize() {
        // Recalculer les positions si nécessaire
        this.updateParticles();
    }

    /**
     * Effet de machine à écrire pour le titre (optionnel)
     */
    setupTypewriterEffect() {
        const titleElement = document.querySelector('.hero-title');
        if (titleElement && titleElement.dataset.typewriter) {
            const text = titleElement.textContent;
            titleElement.textContent = '';
            this.typeWriter(titleElement, text, 0, 100);
        }
    }

    typeWriter(element, text, i, speed) {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            setTimeout(() => this.typeWriter(element, text, i + 1, speed), speed);
        }
    }

    /**
     * Création d'un système de particules
     */
    createParticles() {
        const hero = document.querySelector('.hero');
        if (!hero) return;

        const particlesContainer = document.createElement('div');
        particlesContainer.className = 'particles';
        hero.appendChild(particlesContainer);

        // Créer des particules
        for (let i = 0; i < 50; i++) {
            setTimeout(() => {
                this.createParticle(particlesContainer);
            }, i * 200);
        }

        // Continuer à créer des particules
        setInterval(() => {
            this.createParticle(particlesContainer);
        }, 3000);
    }

    createParticle(container) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        
        // Position aléatoire
        particle.style.left = Math.random() * 100 + '%';
        particle.style.animationDelay = Math.random() * 2 + 's';
        particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
        
        container.appendChild(particle);

        // Supprimer la particule après l'animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 20000);
    }

    updateParticles() {
        // Mise à jour des particules si nécessaire lors du redimensionnement
        const particles = document.querySelectorAll('.particle');
        particles.forEach(particle => {
            if (window.innerWidth < 768) {
                particle.style.display = 'none';
            } else {
                particle.style.display = 'block';
            }
        });
    }

    /**
     * Fonction utilitaire de throttling
     */
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    /**
     * Fonction utilitaire de debouncing
     */
    debounce(func, wait, immediate) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            const later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }
}

/**
 * Initialisation au chargement du DOM
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser la classe principale
    new HackStackLanding();

    // Initialiser les icônes Lucide si disponibles
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Masquer le loader si présent
    const loader = document.querySelector('.loader');
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 500);
        }, 1000);
    }

});

/**
 * Gestion des erreurs globales
 */
window.addEventListener('error', (e) => {
    console.error('Erreur JavaScript:', e.error);
});
