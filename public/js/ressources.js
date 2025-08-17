
// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
});

// Smooth scrolling for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add intersection observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all cards for scroll animations
document.querySelectorAll('.card-gradient').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});

// Progress tracking for learning paths
const progressButtons = document.querySelectorAll('.card-gradient button');
progressButtons.forEach(button => {
    button.addEventListener('click', function () {
        const card = this.closest('.card-gradient');
        const progressSpan = card.querySelector('span[class*="Progression"]');
        if (progressSpan && progressSpan.textContent.includes('0%')) {
            progressSpan.textContent = 'Progression: 5%';
            this.textContent = 'Continuer';
            this.classList.remove('hover:bg-green-500/30');
            this.classList.add('bg-green-500/30');
        }
    });
});

// Add dynamic typing effect to hero title
const heroTitle = document.querySelector('h1.text-gradient');
if (heroTitle) {
    const originalText = heroTitle.textContent;
    heroTitle.textContent = '';
    let i = 0;
    const typeWriter = () => {
        if (i < originalText.length) {
            heroTitle.textContent += originalText.charAt(i);
            i++;
            setTimeout(typeWriter, 100);
        }
    };
    // Start typing effect after page load
    setTimeout(typeWriter, 1000);
}

// Add particles background effect
function createParticle() {
    const particle = document.createElement('div');
    particle.style.cssText = `
                position: fixed;
                width: 2px;
                height: 2px;
                background: rgba(59, 130, 246, 0.5);
                border-radius: 50%;
                pointer-events: none;
                z-index: 1;
                left: ${Math.random() * 100}vw;
                top: 100vh;
                animation: float-up ${5 + Math.random() * 10}s linear infinite;
            `;

    document.body.appendChild(particle);

    setTimeout(() => {
        particle.remove();
    }, 15000);
}

// Create floating particles
setInterval(createParticle, 2000);

// Add CSS for particle animation
const style = document.createElement('style');
style.textContent = `
            @keyframes float-up {
                to {
                    transform: translateY(-100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
document.head.appendChild(style);