// ==================================================
// CONFIGURATION
// ==================================================
const API_BASE_URL = '/api'; // À adapter selon votre environnement
const CHALLENGE_ID = getChallengeIdFromURL(); // getChallengeIdFromURL();

// ==================================================
// STATE MANAGEMENT
// ==================================================
let challengeData = null;
let phaseData = null;
let hackathonData = null;
let countdownInterval = null;

// ==================================================
// DOM ELEMENTS
// ==================================================
const elements = {
    loading: document.getElementById('loading'),
    content: document.getElementById('content'),
    hackathonTitle: document.getElementById('hackathon-title'),
    phaseTitle: document.getElementById('phase-title'),
    hackathonTheme: document.getElementById('hackathon-theme'),
    challengeTitle: document.getElementById('challenge-title'),
    challengeDescription: document.getElementById('challenge-description'),
    challengeInstructions: document.getElementById('challenge-instructions'),
    challengePoints: document.getElementById('challenge-points'),
    submitBtn: document.getElementById('submit-btn'),
    countdown: {
        days: document.getElementById('days'),
        hours: document.getElementById('hours'),
        minutes: document.getElementById('minutes'),
        seconds: document.getElementById('seconds'),
        status: document.getElementById('countdown-status')
    }
};

function getChallengeIdFromURL() {
    const pathParts = window.location.pathname.split('/');
    return pathParts[pathParts.length - 1] || '1';
}

function formatTime(value) {
    return String(value).padStart(2, '0');
}

function formatDescription(text) {
    if (!text) return 'Aucune description disponible.';
    
    // Conversion simple Markdown vers HTML
    return text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>')
        .replace(/^/, '<p>')
        .replace(/$/, '</p>');
}

function formatInstructions(text) {
    if (!text) return 'Aucune instruction disponible.';
    
    // Conversion des listes et formatage
    let formatted = text
        .replace(/^\d+\.\s/gm, '<li>')
        .replace(/^-\s/gm, '<li>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\n\n/g, '</li></ul><p>')
        .replace(/\n/g, '</li><li>');
    
    // Ajout des balises ul si nécessaire
    if (formatted.includes('<li>')) {
        formatted = '<ul class="list-none space-y-2">' + formatted + '</li></ul>';
    } else {
        formatted = '<p>' + formatted.replace(/<li>/g, '').replace(/<\/li>/g, '<br>') + '</p>';
    }
    
    return formatted;
}

async function loadChallengeData() {
    try {
        const userId = await getUserId();
        // Chargement du challenge principal
        response = await apiRequest(`/challenges/dev/${userId}/${CHALLENGE_ID}`);
        if (!response.success) {
            throw new Error('Challenge non trouvé');
        }

        challengeData = response.data;

        // Chargement des données de phase
        if (challengeData.phase_id) {
            phaseDataResponse = await apiRequest(`/phases/${challengeData.phase_id}`);
            if (!phaseDataResponse.success) {
                throw new Error('Phase non trouvée');
            }
            phaseData = phaseDataResponse.data;
        }

        // Chargement des données hackathon
        if (challengeData.hackathon_id) {
            hackathonDataResponse = await apiRequest(`/hackathons/${challengeData.hackathon_id}`);
            if (!hackathonDataResponse.success) {
                throw new Error('Hackathon non trouvé');
            }
            hackathonData = hackathonDataResponse.data;
        }
        return true;
    } catch (error) {
        console.error('Erreur d\'initialisation:', error);
        showError('Une erreur est survenue lors du chargement des données. Veuillez réessayer.');
        return false;
    }
}

document.addEventListener('DOMContentLoaded', init);

// Gestion du redimensionnement pour le responsive
window.addEventListener('resize', () => {
    // Réinitialiser les icônes après redimensionnement
    setTimeout(() => {
        lucide.createIcons();
    }, 100);
});

// Gestion de la visibilité de la page (pour pause/resume du countdown)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Page cachée - on peut pauser le countdown pour économiser les ressources
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
    } else {
        // Page visible - relancer le countdown
        if (phaseData && phaseData.end) {
            startCountdown();
        }
    }
});

// ==================================================
// ANALYTICS & TRACKING (optionnel)
// ==================================================
function trackEvent(eventName, properties = {}) {
    // Implémentation du tracking analytics
    console.log(`Event: ${eventName}`, properties);
    
    // Exemple avec Google Analytics 4
    // if (typeof gtag !== 'undefined') {
    //     gtag('event', eventName, properties);
    // }
}

// Tracking des interactions
elements.submitBtn?.addEventListener('click', () => {
    trackEvent('challenge_submission_clicked', {
        challenge_id: challengeData?.id,
        challenge_title: challengeData?.title,
        time_remaining: elements.countdown.days.textContent + ':' + 
                       elements.countdown.hours.textContent + ':' + 
                       elements.countdown.minutes.textContent + ':' + 
                       elements.countdown.seconds.textContent
    });
});

function setupAccessibility() {
    // Annonces ARIA pour le countdown
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        countdownElement.setAttribute('aria-live', 'polite');
        countdownElement.setAttribute('aria-label', 'Compte à rebours jusqu\'à la fin des soumissions');
    }

    // Focus management pour les boutons
    const submitButton = elements.submitBtn;
    if (submitButton) {
        submitButton.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                submitButton.click();
            }
        });
    }

    // Amélioration des contrastes selon les préférences utilisateur
    if (window.matchMedia('(prefers-contrast: high)').matches) {
        document.documentElement.style.setProperty('--text-secondary', '#E2E8F0');
        document.documentElement.style.setProperty('--border', '#475569');
    }
}

function optimizePerformance() {
    const images = document.querySelectorAll('img[data-src]');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // Préchargement de la page de soumission au hover du bouton
    let preloadTimer;
    elements.submitBtn?.addEventListener('mouseenter', () => {
        preloadTimer = setTimeout(() => {
            const submissionUrl = challengeData?.code_name ? 
                `/user/challenge_submission/${challengeData.code_name}` : 
                `/user/challenge_submission/${challengeData.id}`;
            
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = submissionUrl;
            document.head.appendChild(link);
        }, 500);
    });

    elements.submitBtn?.addEventListener('mouseleave', () => {
        if (preloadTimer) {
            clearTimeout(preloadTimer);
        }
    });
}

let retryCount = 0;
const maxRetries = 3;

async function loadDataWithRetry() {
    for (let i = 0; i < maxRetries; i++) {
        try {
            const success = await loadMockData();
            if (success) {
                return true;
            }
        } catch (error) {
            console.warn(`Tentative ${i + 1}/${maxRetries} échouée:`, error);
            if (i < maxRetries - 1) {
                // Attendre avant de réessayer (backoff exponentiel)
                await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000));
            }
        }
    }
    return false;
}

window.addEventListener('beforeunload', () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});

async function loadMockData() {
    await new Promise(resolve => setTimeout(resolve, 1500));

    challengeData = {
        id: '1',
        code_name: 'CHALL-223D33K2',
        title: 'Application Web Innovante',
        description: `Développez une **application web moderne** qui révolutionne l'expérience utilisateur dans le domaine de votre choix.

Votre solution doit démontrer une approche innovante, une excellente **expérience utilisateur** et une architecture technique solide.

*L'originalité et la qualité technique seront particulièrement valorisées.*`,
        instructions: `**Étapes à suivre :**

1. **Analyse** - Identifiez un problème réel et définissez votre solution
2. **Conception** - Créez les maquettes et l'architecture technique
3. **Développement** - Implémentez votre solution avec les technologies de votre choix
4. **Tests** - Vérifiez le bon fonctionnement et l'accessibilité
5. **Documentation** - Rédigez un README complet avec guide d'installation
6. **Déploiement** - Mettez en ligne une version de démonstration

**Critères d'évaluation :**
- Innovation et originalité (30%)
- Qualité technique (25%)
- Expérience utilisateur (25%)
- Documentation (20%)

**Livrables attendus :**
- Code source sur GitHub
- Application déployée
- Documentation complète
- Vidéo de présentation (3 min max)`,
        points: 1500,
        type: 'dev',
        phase_id: '2',
        hackathon_id: '1'
    };

    phaseData = {
        id: '2',
        title: 'Phase 2 – Projet Innovant',
        end: '2025-08-20T23:59:59',
        hackathon_id: '1'
    };

    hackathonData = {
        id: '1',
        title: 'HackDev 2024',
        theme: 'Innovation & Impact Social'
    };

    return true;
}

function updateCountdown() {
    if (!phaseData || !phaseData.end) {
        return;
    }

    const now = new Date().getTime();
    const endTime = new Date(phaseData.end).getTime();
    const difference = endTime - now;

    if (difference <= 0) {
        // Temps écoulé
        elements.countdown.days.textContent = '00';
        elements.countdown.hours.textContent = '00';
        elements.countdown.minutes.textContent = '00';
        elements.countdown.seconds.textContent = '00';
        
        elements.countdown.status.classList.remove('hidden');
        elements.submitBtn.disabled = true;
        elements.submitBtn.innerHTML = '<i data-lucide="x-circle" class="w-5 h-5 inline mr-2"></i>Soumissions fermées';
        
        // Réinitialiser les icônes
        lucide.createIcons();
        
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }
        return;
    }

    // Calculs du temps restant
    const days = Math.floor(difference / (1000 * 60 * 60 * 24));
    const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((difference % (1000 * 60)) / 1000);

    // Mise à jour de l'affichage
    elements.countdown.days.textContent = formatTime(days);
    elements.countdown.hours.textContent = formatTime(hours);
    elements.countdown.minutes.textContent = formatTime(minutes);
    elements.countdown.seconds.textContent = formatTime(seconds);
}

function startCountdown() {
    updateCountdown();
    countdownInterval = setInterval(updateCountdown, 1000);
}

// ==================================================
// UI UPDATE FUNCTIONS
// ==================================================
function updateUI() {
    if (!challengeData) return;

    // Header
    elements.hackathonTitle.textContent = hackathonData?.title || hackathonData?.name || 'Hackathon';
    elements.phaseTitle.textContent = phaseData?.title || phaseData?.name || 'Phase en cours';

    // Hero section
    elements.hackathonTheme.textContent = hackathonData?.theme || 'Thématique non définie';
    elements.challengeTitle.textContent = challengeData.title || challengeData.name || 'Titre du Challenge';

    // Description et instructions
    elements.challengeDescription.innerHTML = formatDescription(challengeData.description);
    elements.challengeInstructions.innerHTML = formatInstructions(challengeData.instructions);

    // Points
    elements.challengePoints.textContent = challengeData.points ? 
        `${challengeData.points} pts` : 'À déterminer';
    
    // Mise à jour de l'affichage hero
    if (challengeData.points) {
        const heroPoints = document.getElementById('challenge-points-hero');
        if (heroPoints) {
            heroPoints.textContent = challengeData.points;
        }
    }

    // Bouton de soumission
    const submissionUrl = challengeData.code_name ? 
        `/user/challenge_submission/${challengeData.code_name}` : 
        `/user/challenge_submission/${challengeData.id}`;
    
    elements.submitBtn.onclick = () => {
        window.location.href = submissionUrl;
    };

    // Démarrer le countdown
    startCountdown();

    // Réinitialiser les icônes Lucide
    lucide.createIcons();
}

function showError(message) {
    elements.loading.innerHTML = `
        <div class="text-center">
            <i data-lucide="alert-triangle" class="w-16 h-16 text-red mx-auto mb-4"></i>
            <h2 class="text-xl font-bold mb-2">Erreur de chargement</h2>
            <p class="text-text-secondary mb-6">${message}</p>
            <button onclick="location.reload()" class="bg-gradient hover:bg-primary-hover text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300">
                <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
                Réessayer
            </button>
        </div>
    `;
    lucide.createIcons();
}

async function init() {
    try {
        const success = await loadChallengeData();

        if (!success) {
            throw new Error('Impossible de charger les données du challenge');
        }

        updateUI();
        elements.loading.classList.add('hidden');
        elements.content.classList.remove('hidden');

    } catch (error) {
        console.error('Erreur lors du chargement:', error);
        return false;
    }
}
