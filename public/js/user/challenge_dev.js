// Variables globales pour la gestion des challenges
let allChallenges = [];
let filteredChallenges = [];
let currentFilter = 'all';
let currentSort = 'none';
let currentSearch = '';

// Configuration des filtres et tri
const DIFFICULTY_ORDER = { 'easy': 1, 'medium': 2, 'hard': 3 };
const DIFFICULTY_LABELS = { 'easy': 'Facile', 'medium': 'Moyen', 'hard': 'Difficile' };

const hackathonId = document.querySelector('meta[name="hackathon-id"]').content;
const phaseId = document.querySelector('meta[name="phase-id"]').content;
/**
 * Initialisation de l'application
 */
document.addEventListener('DOMContentLoaded', async () => {
    try {
        await initializeApp();
    } catch (error) {
        console.error('Erreur lors de l\'initialisation:', error);
        showErrorState('Une erreur est survenue lors du chargement de la page.');
    }
});

/**
 * Initialise l'application
 */
async function initializeApp() {
    // Initialiser les icônes Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Configurer les événements
    setupEventListeners();

    // Vérifier l'accès au hackathon
    const accessCheck = await checkHackathonAccess(hackathonId);
    if (!accessCheck.success) {
        showAccessDeniedModal(accessCheck.message);
        return;
    }

    // Charger les challenges
    await loadChallenges(hackathonId);
}

/**
 * Configure tous les event listeners
 */
function setupEventListeners() {
    // Recherche
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', debounce((e) => {
            currentSearch = e.target.value.toLowerCase().trim();
            updateDisplay();
        }, 300));
    }

    // Filtres de difficulté
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const filter = button.getAttribute('data-filter');
            setActiveFilter(button, filter);
        });
    });

    // Boutons de tri
    const sortButtons = document.querySelectorAll('.sort-btn');
    sortButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const sort = button.getAttribute('data-sort');
            setActiveSort(button, sort);
        });
    });

    // Modal
    const modal = document.getElementById('challenge-modal');
    const closeModal = document.getElementById('close-modal');
    
    if (closeModal) {
        closeModal.addEventListener('click', () => {
            hideModal();
        });
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal();
            }
        });
    }

    // Gestion du clavier pour la modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.style.display !== 'none') {
            hideModal();
        }
    });
}

/**
 * Vérifie l'accès au hackathon
 */
async function checkHackathonAccess(hackathonId) {
    try {
        const response = await apiRequest('/check-participation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                hackathon_id: hackathonId,
                csrf_token: getCsrfToken()
            })
        });

        return {
            success: response.success,
            message: response.message || (response.success ? 'Accès autorisé' : 'Accès refusé'),
            status: response.status
        };
    } catch (error) {
        console.error('Erreur lors de la vérification d\'accès:', error);
        return {
            success: false,
            message: 'Erreur lors de la vérification d\'accès au hackathon',
            status: 'error'
        };
    }
}

/**
 * Charge les challenges depuis l'API
 */
async function loadChallenges(hackathonId) {
    const grid = document.getElementById('challenges-grid');
    
    try {
        // Afficher l'état de chargement
        showLoadingState();

        const userId = await getUserId();
        const response = await apiRequest(`/challenges/algo/${hackathonId}/${userId}/${phaseId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (!response.success) {
            if (
                response.status === "phase_inactive" ||
                response.message?.includes("période de l'événement")
            ) {
                showPhaseInactiveState(response.message);
            } else {
                handleError("Erreur lors de la récupération des challenges", response.message);
            }
            return;
        }

        allChallenges = response.data || [];

        updatePerformanceStats();
        updateDisplay();

    } catch (error) {
        console.error('Erreur lors du chargement des challenges:', error);
        showErrorState(error.message);
    }
}

function showPhaseInactiveState(message = "Les challenges ne sont pas disponibles pour le moment.") {
    const emptyState = document.getElementById("challenges-empty-state");
    if (!emptyState) return;

    const title = document.getElementById("empty-title");
    const desc = document.getElementById("empty-message");
    const icon = document.getElementById("empty-icon");

    if (title) title.textContent = "Phase inactive";
    if (desc) desc.textContent = message;
    if (icon) icon.setAttribute("data-lucide", "lock");

    emptyState.classList.remove("hidden");
    emptyState.classList.add("flex");

    // Met à jour les icônes si besoin
    lucide.createIcons();
}

/**
 * Met à jour les statistiques de performance
 */
function updatePerformanceStats() {
    const solvedCount = allChallenges.filter(c => c.team_has_solved === 1).length;
    const totalPoints = allChallenges
        .filter(c => c.team_has_solved === 1)
        .reduce((sum, c) => sum + (parseInt(c.points) || 0), 0);

    // Mettre à jour la carte de performance
    const scoreValue = document.querySelector('.score-value');
    const solvedValue = document.querySelector('.perf-value.solved');
    
    if (scoreValue) scoreValue.textContent = totalPoints;
    if (solvedValue) solvedValue.textContent = solvedCount;
}

/**
 * Définit le filtre actif
 */
function setActiveFilter(button, filter) {
    // Mise à jour des styles des boutons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');

    // Mise à jour du filtre
    currentFilter = filter;
    updateDisplay();
}

/**
 * Définit le tri actif
 */
function setActiveSort(button, sort) {
    // Mise à jour des styles des boutons
    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');

    // Mise à jour du tri
    currentSort = sort;
    updateDisplay();
}

/**
 * Met à jour l'affichage des challenges
 */
function updateDisplay() {
    filteredChallenges = filterAndSortChallenges();
    displayChallenges(filteredChallenges);
    updateResultsCounter();
    updateActiveFiltersText();
}

/**
 * Filtre et trie les challenges
 */
function filterAndSortChallenges() {
    let filtered = [...allChallenges];

    // Filtrer par difficulté
    if (currentFilter !== 'all') {
        filtered = filtered.filter(challenge => challenge.difficulty === currentFilter);
    }

    // Filtrer par recherche
    if (currentSearch) {
        filtered = filtered.filter(challenge => {
            const title = (challenge.title || '').toLowerCase();
            const description = (challenge.description || '').toLowerCase();
            return title.includes(currentSearch) || description.includes(currentSearch);
        });
    }

    // Trier
    switch (currentSort) {
        case 'points-desc':
            filtered.sort((a, b) => (parseInt(b.points) || 0) - (parseInt(a.points) || 0));
            break;
        case 'points-asc':
            filtered.sort((a, b) => (parseInt(a.points) || 0) - (parseInt(b.points) || 0));
            break;
        case 'difficulty-asc':
            filtered.sort((a, b) => (DIFFICULTY_ORDER[a.difficulty] || 99) - (DIFFICULTY_ORDER[b.difficulty] || 99));
            break;
        case 'difficulty-desc':
            filtered.sort((a, b) => (DIFFICULTY_ORDER[b.difficulty] || 99) - (DIFFICULTY_ORDER[a.difficulty] || 99));
            break;
        default:
            // Tri par défaut : points décroissants
            filtered.sort((a, b) => (parseInt(b.points) || 0) - (parseInt(a.points) || 0));
            break;
    }

    return filtered;
}

/**
 * Affiche les challenges dans la grille
 */
function displayChallenges(challenges) {
    const grid = document.getElementById('challenges-grid');
    if (!grid) return;

    grid.innerHTML = '';

    if (!challenges || challenges.length === 0) {
        showEmptyState();
        return;
    }

    challenges.forEach((challenge, index) => {
        const card = createChallengeCard(challenge, index);
        grid.appendChild(card);
    });

    // Réinitialiser les icônes Lucide
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Crée une carte de challenge
 */
function createChallengeCard(challenge, index) {
    const card = document.createElement('div');
    card.className = 'challenge-card';
    card.style.animationDelay = `${(index % 8) * 0.1}s`;

    const isSolved = challenge.team_has_solved === 1;
    const difficultyClass = challenge.difficulty || 'medium';
    const difficultyLabel = DIFFICULTY_LABELS[challenge.difficulty] || 'Moyen';

    card.innerHTML = `
        <div class="challenge-header">
            <div class="challenge-points">
                <span class="points-value">${challenge.points || 0}</span>
                <span class="points-label">pts</span>
            </div>
            
            <div class="challenge-meta">
                <div class="challenge-type">
                    <i data-lucide="cpu" class="w-3 h-3"></i>
                    ${challenge.type || 'Algorithmique'}
                </div>
            </div>
        </div>

        <div class="challenge-body">
            <h3 class="challenge-title">${escapeHtml(challenge.title || 'Challenge sans titre')}</h3>
            <p class="challenge-description">${escapeHtml(challenge.description || 'Aucune description disponible.')}</p>
            
            <div class="challenge-difficulty ${difficultyClass}">
                <i data-lucide="${getDifficultyIcon(challenge.difficulty)}" class="w-3 h-3"></i>
                ${difficultyLabel}
            </div>
        </div>

        <div class="challenge-footer">
            <div class="challenge-status ${isSolved ? 'solved' : 'not-attempted'}">
                <i data-lucide="${isSolved ? 'check-circle' : 'circle'}" class="w-4 h-4"></i>
                ${isSolved ? 'Résolu' : 'Non tenté'}
            </div>
            
            <button class="challenge-action-btn ${isSolved ? 'secondary' : 'primary'}" 
                    data-challenge-id="${challenge.id}"
                    onclick="handleChallengeClick(${challenge.id})">
                <i data-lucide="${isSolved ? 'eye' : 'play'}" class="w-4 h-4"></i>
                ${isSolved ? 'Revoir' : 'Commencer'}
            </button>
        </div>
    `;

    return card;
}

/**
 * Gère le clic sur un challenge
 */
function handleChallengeClick(challengeId) {
    if (challengeId) {
        window.location.href = `/user/interfacechallenges/${challengeId}`;
    }
}

/**
 * Retourne l'icône appropriée selon la difficulté
 */
function getDifficultyIcon(difficulty) {
    switch (difficulty) {
        case 'easy': return 'circle';
        case 'medium': return 'minus-circle';
        case 'hard': return 'alert-circle';
        default: return 'circle';
    }
}

/**
 * Met à jour le compteur de résultats
 */
function updateResultsCounter() {
    const countElement = document.getElementById('filtered-count');
    if (countElement) {
        countElement.textContent = filteredChallenges.length;
    }
}

/**
 * Met à jour le texte des filtres actifs
 */
function updateActiveFiltersText() {
    const element = document.getElementById('active-filters-text');
    if (!element) return;

    const filters = [];
    
    if (currentFilter !== 'all') {
        filters.push(`Difficulté: ${DIFFICULTY_LABELS[currentFilter]}`);
    }
    
    if (currentSearch) {
        filters.push(`Recherche: "${currentSearch}"`);
    }
    
    if (currentSort !== 'none') {
        const sortLabels = {
            'points-desc': 'Tri par points (décroissant)',
            'points-asc': 'Tri par points (croissant)',
            'difficulty-asc': 'Tri par difficulté (croissant)',
            'difficulty-desc': 'Tri par difficulté (décroissant)'
        };
        filters.push(sortLabels[currentSort] || 'Tri actif');
    }

    element.textContent = filters.length > 0 ? filters.join(' • ') : 'Aucun filtre actif';
}

/**
 * Affiche l'état de chargement
 */
function showLoadingState() {
    const grid = document.getElementById('challenges-grid');
    if (!grid) return;

    grid.innerHTML = `
        <div class="loading-state">
            <div class="loading-spinner">
                <i data-lucide="loader-2" class="w-8 h-8 animate-spin"></i>
            </div>
            <p class="loading-text">Chargement des challenges...</p>
        </div>
    `;

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Affiche l'état vide
 */
function showEmptyState() {
    const grid = document.getElementById('challenges-grid');
    if (!grid) return;

    const hasFilters = currentFilter !== 'all' || currentSearch || currentSort !== 'none';
    
    grid.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">
                <i data-lucide="${hasFilters ? 'search-x' : 'cpu'}" class="w-8 h-8"></i>
            </div>
            <h3 class="empty-title">
                ${hasFilters ? 'Aucun challenge trouvé' : 'Aucun challenge disponible'}
            </h3>
            <p class="empty-description">
                ${hasFilters 
                    ? 'Essayez de modifier vos critères de recherche ou de filtrage.' 
                    : 'Les challenges algorithmiques seront bientôt disponibles.'}
            </p>
            ${hasFilters ? `
                <button class="challenge-action-btn primary" onclick="resetFilters()">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                    Réinitialiser les filtres
                </button>
            ` : ''}
        </div>
    `;

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Affiche l'état d'erreur
 */
function showErrorState(message) {
    const grid = document.getElementById('challenges-grid');
    if (!grid) return;

    grid.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.2); color: var(--danger);">
                <i data-lucide="alert-triangle" class="w-8 h-8"></i>
            </div>
            <h3 class="empty-title">Erreur de chargement</h3>
            <p class="empty-description">${escapeHtml(message)}</p>
            <button class="challenge-action-btn primary" onclick="location.reload()">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                Réessayer
            </button>
        </div>
    `;

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Affiche la modal d'accès refusé
 */
function showAccessDeniedModal(message) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.display = 'flex';
    
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i data-lucide="shield-alert" class="w-5 h-5" style="color: var(--danger); margin-right: 0.5rem;"></i>
                    Accès restreint
                </h3>
            </div>
            <div class="modal-body">
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; text-align: center;">
                    ${escapeHtml(message)}
                </p>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="/user/hackathon" 
                       class="challenge-action-btn primary" 
                       style="text-decoration: none; text-align: center;">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Retour aux hackathons
                    </a>
                    <a href="/user" 
                       class="challenge-action-btn secondary" 
                       style="text-decoration: none; text-align: center;">
                        <i data-lucide="home" class="w-4 h-4"></i>
                        Tableau de bord
                    </a>
                </div>
                <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border); text-align: center;">
                    <p style="font-size: 0.875rem; color: var(--text-muted);">
                        Besoin d'aide ? 
                        <a href="https://discord.gg/FbztK5Uagd" 
                           target="_blank"
                           style="color: var(--primary-light); text-decoration: none;">
                            Contactez le support
                        </a>
                    </p>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

/**
 * Cache la modal
 */
function hideModal() {
    const modal = document.getElementById('challenge-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}
/**
 * Réinitialise tous les filtres
 */
function resetFilters() {
    // Réinitialiser les variables
    currentFilter = 'all';
    currentSort = 'none';
    currentSearch = '';

    // Réinitialiser l'interface
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
    }

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('data-filter') === 'all');
    });

    document.querySelectorAll('.sort-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Mettre à jour l'affichage
    updateDisplay();
}
// Fonctions utilitaires

/**
 * Fonction de debounce pour limiter les appels
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Échappe les caractères HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

/**
 * Récupère le token CSRF
 */
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : '';
}

/**
 * Affiche une notification toast
 */
function showToast(message, type = 'info', duration = 3000) {
    // Créer l'élément toast
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        color: var(--text);
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: var(--shadow-lg);
        z-index: 1000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        max-width: 400px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    `;

    // Icône selon le type
    const icons = {
        success: 'check-circle',
        error: 'x-circle',
        warning: 'alert-triangle',
        info: 'info'
    };

    const colors = {
        success: 'var(--success)',
        error: 'var(--danger)',
        warning: 'var(--warning)',
        info: 'var(--info)'
    };

    toast.innerHTML = `
        <i data-lucide="${icons[type] || icons.info}" style="color: ${colors[type] || colors.info}; flex-shrink: 0;"></i>
        <span>${escapeHtml(message)}</span>
    `;

    document.body.appendChild(toast);

    // Initialiser l'icône
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Animation d'entrée
    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    });

    // Suppression automatique
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, duration);
}

/**
 * Formate un nombre avec des séparateurs de milliers
 */
function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(num);
}

/**
 * Formate une date en français
 */
function formatDate(dateString) {
    if (!dateString) return 'Date inconnue';
    
    try {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        }).format(date);
    } catch (error) {
        return 'Date invalide';
    }
}

/**
 * Gère le redimensionnement de la fenêtre
 */
function handleResize() {
    // Ajuster la grille si nécessaire
    const grid = document.getElementById('challenges-grid');
    if (grid && window.innerWidth < 768) {
        grid.style.gridTemplateColumns = '1fr';
    } else if (grid && window.innerWidth < 1024) {
        grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(350px, 1fr))';
    } else if (grid) {
        grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(400px, 1fr))';
    }
}

// Ajouter le listener de redimensionnement
window.addEventListener('resize', debounce(handleResize, 250));

/**
 * Initialise les tooltips (si nécessaire)
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(event) {
    const element = event.target;
    const text = element.getAttribute('data-tooltip');
    if (!text) return;

    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = text;
    tooltip.style.cssText = `
        position: absolute;
        background: var(--surface);
        color: var(--text);
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
        z-index: 1000;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
        white-space: nowrap;
    `;

    document.body.appendChild(tooltip);

    // Positionner le tooltip
    const rect = element.getBoundingClientRect();
    const tooltipRect = tooltip.getBoundingClientRect();
    
    tooltip.style.left = `${rect.left + (rect.width - tooltipRect.width) / 2}px`;
    tooltip.style.top = `${rect.top - tooltipRect.height - 8}px`;
    
    // Animation d'apparition
    requestAnimationFrame(() => {
        tooltip.style.opacity = '1';
    });

    element._tooltip = tooltip;
}

function hideTooltip(event) {
    const element = event.target;
    if (element._tooltip) {
        element._tooltip.style.opacity = '0';
        setTimeout(() => {
            if (element._tooltip && element._tooltip.parentNode) {
                element._tooltip.parentNode.removeChild(element._tooltip);
            }
            delete element._tooltip;
        }, 200);
    }
}

/**
 * Gère l'état de focus pour l'accessibilité
 */
function setupAccessibility() {
    // Détecter la navigation au clavier
    let isUsingKeyboard = false;
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            isUsingKeyboard = true;
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', () => {
        isUsingKeyboard = false;
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Améliorer la navigation au clavier dans la grille
    const grid = document.getElementById('challenges-grid');
    if (grid) {
        grid.addEventListener('keydown', (e) => {
            if (!isUsingKeyboard) return;
            
            const focusable = grid.querySelectorAll('.challenge-action-btn');
            const currentIndex = Array.from(focusable).indexOf(document.activeElement);
            
            let nextIndex = -1;
            
            switch (e.key) {
                case 'ArrowRight':
                case 'ArrowDown':
                    nextIndex = currentIndex + 1;
                    break;
                case 'ArrowLeft':
                case 'ArrowUp':
                    nextIndex = currentIndex - 1;
                    break;
                case 'Home':
                    nextIndex = 0;
                    break;
                case 'End':
                    nextIndex = focusable.length - 1;
                    break;
            }
            
            if (nextIndex >= 0 && nextIndex < focusable.length) {
                e.preventDefault();
                focusable[nextIndex].focus();
            }
        });
    }
}

// Initialiser l'accessibilité au chargement
document.addEventListener('DOMContentLoaded', setupAccessibility);

/**
 * Gère les états de performance pour les animations
 */
function setupPerformanceOptimizations() {
    // Réduire les animations sur les appareils moins performants
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        document.body.classList.add('reduced-animations');
    }
    
    // Observer la visibilité pour pauser les animations
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                } else {
                    entry.target.classList.remove('in-view');
                }
            });
        }, { threshold: 0.1 });
        
        // Observer les cartes de challenge
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const cards = document.querySelectorAll('.challenge-card');
                cards.forEach(card => observer.observe(card));
            }, 1000);
        });
    }
}

// Initialiser les optimisations de performance
setupPerformanceOptimizations();

// Exporter les fonctions principales pour les tests (si dans un environnement de test)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        filterAndSortChallenges,
        escapeHtml,
        debounce,
        formatNumber,
        formatDate
    };
}