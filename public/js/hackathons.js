// Global variables
let hackathonsData = [];
let filteredData = [];
let currentFilter = 'all';

// DOM Elements
const loadingState = document.getElementById('loading-state');
const errorState = document.getElementById('error-state');
const emptyState = document.getElementById('empty-state');
const hackathonsGrid = document.getElementById('hackathons-grid');
const searchInput = document.getElementById('search-input');
const filterBtns = document.querySelectorAll('.filter-btn');
const totalHackathons = document.getElementById('total-hackathons');
const totalParticipants = document.getElementById('total-participants');

// Initialize event listeners
function initializeEventListeners() {
    // Search functionality
    searchInput.addEventListener('input', debounce(handleSearch, 300));

    // Filter buttons
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const filter = e.target.dataset.filter;
            setActiveFilter(filter);
            applyFilters();
        });
    });
}

// Load hackathons from API
async function loadHackathons() {
    showState('loading');

    try {
        const response = await apiRequest('/hackathons/public');

        if (response.success && response.data) {
            hackathonsData = response.data;
            filteredData = [...hackathonsData];
            updateStats();
            renderHackathons();
            showState('success');
        } else {
            throw new Error('Invalid response format');
        }
    } catch (error) {
        console.error('Error loading hackathons:', error);
        showState('error');
    }
}

// Render hackathons grid
function renderHackathons() {
    if (filteredData.length === 0) {
        showState('empty');
        return;
    }

    showState('success');

    hackathonsGrid.innerHTML = filteredData.map(hackathon => {
        return createHackathonCard(hackathon);
    }).join('');

    // Re-initialize Lucide icons for new content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}

// Create individual hackathon card
function createHackathonCard(hackathon) {
    const status = getHackathonStatus(hackathon);
    const typeLabel = getTypeLabel(hackathon.type);
    const prizes = JSON.parse(hackathon.prizes || '[]');
    const firstPrize = prizes.find(p => p.rank === 1);

    const startDate = formatDate(hackathon.start_date);
    const endDate = formatDate(hackathon.end_date);
    const registrationDeadline = formatDate(hackathon.registration_deadline);
    const isRegistrationOpen = new Date(hackathon.registration_deadline) > new Date();

    // Définition des couleurs en fonction du statut
    const statusColors = {
        'upcoming': { bg: 'bg-blue-500/10', text: 'text-blue-400', border: 'border-blue-500/20' },
        'ongoing': { bg: 'bg-green-500/10', text: 'text-green-400', border: 'border-green-500/20' },
        'ended': { bg: 'bg-red-500/10', text: 'text-red-400', border: 'border-red-500/20' }
    };
    const statusColor = statusColors[status.class] || statusColors.upcoming;

    return `
        <div class="hackathon-card group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800/50 to-slate-900/50 backdrop-blur-sm border border-slate-700/50 hover:border-blue-500/50 transition-all duration-500 hover:shadow-2xl hover:shadow-blue-500/5" data-status="${hackathon.status}" data-type="${hackathon.type}">
            <!-- Éléments décoratifs de fond -->
            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-500/5 to-cyan-500/5 rounded-2xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
            </div>
            
            <!-- Badge de type avec effet de profondeur -->
            <div class="absolute top-4 right-4 z-10 px-3 py-1.5 rounded-full text-xs font-semibold ${hackathon.type === 'dev' ? 'bg-gradient-to-r from-blue-500 to-cyan-500' : 'bg-gradient-to-r from-purple-500 to-pink-500'} text-white shadow-lg backdrop-blur-sm border border-white/10">
                <div class="flex items-center gap-1.5">
                    <i data-lucide="${hackathon.type === 'dev' ? 'code' : 'shield'}" class="w-3.5 h-3.5"></i>
                    ${typeLabel}
                </div>
            </div>
            
            <!-- En-tête de la carte avec image de couverture -->
            <div class="relative h-36 overflow-hidden bg-gradient-to-br from-slate-700/50 to-slate-800/50">
                ${hackathon.image_url ? `
                    <img src="${hackathon.image_url}" alt="${escapeHtml(hackathon.name)}" class="w-full h-full object-cover opacity-70 group-hover:opacity-80 transition-opacity duration-500">
                ` : `
                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-800">
                        <i data-lucide="code" class="w-12 h-12 text-slate-600"></i>
                    </div>
                `}
                
                <!-- Overlay de statut -->
                <div class="absolute bottom-3 left-3 z-10">
                    <div class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor.bg} ${statusColor.text} ${statusColor.border} backdrop-blur-sm border">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="${status.class === 'upcoming' ? 'clock' : status.class === 'ongoing' ? 'zap' : 'flag'}" class="w-3 h-3"></i>
                            ${status.label}
                        </div>
                    </div>
                </div>
                
                <!-- Prix principal -->
                ${firstPrize ? `
                    <div class="absolute bottom-3 right-3 z-10">
                        <div class="px-3 py-1.5 rounded-full bg-gradient-to-r from-amber-400/90 to-amber-500/90 text-amber-900 text-xs font-bold backdrop-blur-sm border border-amber-300/30 shadow-md">
                            <div class="flex items-center gap-1.5">
                                <i data-lucide="award" class="w-3.5 h-3.5"></i>
                                ${firstPrize.amount} ${firstPrize.currency || 'FCFA'}
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
            
            <!-- Corps de la carte -->
            <div class="p-5">
                <!-- Titre et description -->
                <div class="mb-4">
                    <h2 class="text-xl font-bold text-white mb-1.5 group-hover:text-blue-400 transition-colors duration-300 line-clamp-1">
                        ${escapeHtml(hackathon.name)}
                    </h2>
                    <p class="text-sm text-slate-300 line-clamp-2 leading-relaxed">
                        ${escapeHtml(hackathon.short_description || hackathon.description || 'Aucune description disponible.')}
                    </p>
                </div>
                
                <!-- Métadonnées -->
                <div class="space-y-3">
                    <!-- Dates -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-400 mt-0.5">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">${startDate} - ${endDate}</p>
                            <p class="text-xs text-slate-400">Dates de l'événement</p>
                        </div>
                    </div>
                    
                    <!-- Date limite d'inscription -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg ${isRegistrationOpen ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'} flex items-center justify-center mt-0.5">
                            <i data-lucide="${isRegistrationOpen ? 'clock' : 'alert-circle'}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium ${isRegistrationOpen ? 'text-white' : 'text-red-400'}">
                                ${registrationDeadline}
                                ${!isRegistrationOpen ? ' (Fermé)' : ''}
                            </p>
                            <p class="text-xs text-slate-400">Fin des inscriptions</p>
                        </div>
                    </div>
                    
                    <!-- Équipes et participants -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-cyan-500/10 flex items-center justify-center text-cyan-400 mt-0.5">
                            <i data-lucide="users" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">
                                ${hackathon.min_team_members}-${hackathon.max_team_members} membres par équipe
                            </p>
                            <p class="text-xs text-slate-400">
                                ${hackathon.max_teams > 0 ? `${hackathon.max_teams} équipes max` : 'Équipes illimitées'}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Localisation -->
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-400 mt-0.5">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">
                                ${getLocationDisplay(hackathon.location)}
                            </p>
                            <p class="text-xs text-slate-400">
                                ${hackathon.type === 'online' ? 'En ligne' : 'Présentiel'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pied de carte avec bouton d'action -->
            <div class="px-5 pb-5 pt-2">
                <button 
                    onclick="viewHackathonDetails('${hackathon.id}')" 
                    class="w-full py-2.5 px-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg text-sm transition-all duration-300 flex items-center justify-center group/btn hover:shadow-lg hover:shadow-blue-500/20"
                >
                    <span>Voir les détails</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 ml-2 transform group-hover/btn:translate-x-1 transition-transform duration-300"></i>
                </button>
            </div>
        </div>
    `;
}

// Get hackathon status
function getHackathonStatus(hackathon) {
    const now = new Date();
    const startDate = new Date(hackathon.start_date);
    const endDate = new Date(hackathon.end_date);
    const registrationDeadline = new Date(hackathon.registration_deadline);

    if (now < registrationDeadline) {
        return { class: 'upcoming', label: 'Inscription ouverte' };
    } else if (now >= startDate && now <= endDate) {
        return { class: 'ongoing', label: 'En cours' };
    } else if (now > endDate) {
        return { class: 'ended', label: 'Terminé' };
    } else {
        return { class: 'upcoming', label: 'À venir' };
    }
}

// Get type label
function getTypeLabel(type) {
    const labels = {
        'dev': 'DEV',
        'ctf': 'CTF',
        'design': 'DESIGN',
        'mixte': 'MIXTE'
    };
    return labels[type] || type.toUpperCase();
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

// Get location display
function getLocationDisplay(location) {
    if (location.includes('online')) {
        return '🌐 En ligne';
    }
    return location;
}

// Handle search
function handleSearch() {
    const query = searchInput.value.toLowerCase().trim();

    if (query === '') {
        filteredData = [...hackathonsData];
    } else {
        filteredData = hackathonsData.filter(hackathon =>
            hackathon.name.toLowerCase().includes(query) ||
            hackathon.description.toLowerCase().includes(query) ||
            hackathon.type.toLowerCase().includes(query)
        );
    }

    applyStatusFilter();
    renderHackathons();
}

// Apply filters
function applyFilters() {
    // Start with search results
    handleSearch();
}

// Apply status filter
function applyStatusFilter() {
    if (currentFilter === 'all') {
        return; // filteredData already set by search
    }

    filteredData = filteredData.filter(hackathon => {
        const status = getHackathonStatus(hackathon);

        switch (currentFilter) {
            case 'active':
                return status.class === 'ongoing';
            case 'upcoming':
                return status.class === 'upcoming';
            case 'past':
                return status.class === 'ended';
            default:
                return true;
        }
    });
}

// Set active filter
function setActiveFilter(filter) {
    currentFilter = filter;

    // Update button states
    filterBtns.forEach(btn => {
        btn.classList.toggle('active', btn.dataset.filter === filter);
    });
}

// Update statistics
function updateStats() {
    const activeHackathons = hackathonsData.filter(h =>
        getHackathonStatus(h).class === 'ongoing' ||
        getHackathonStatus(h).class === 'upcoming'
    ).length;

    // Calculate total max participants (rough estimate)
    const totalMaxParticipants = hackathonsData.reduce((sum, h) => {
        if (h.max_teams > 0) {
            return sum + (h.max_teams * h.max_team_members);
        }
        return sum + 100; // Default estimate for unlimited teams
    }, 0);

    // Animate numbers
    animateNumber(totalHackathons, activeHackathons);
    animateNumber(totalParticipants, totalMaxParticipants);
}

// Animate number counting
function animateNumber(element, target) {
    const start = 0;
    const duration = 1000;
    const startTime = Date.now();

    function update() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.floor(start + (target - start) * progress);

        element.textContent = current.toLocaleString();

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    update();
}

// Show different states
function showState(state) {
    const states = {
        loading: loadingState,
        error: errorState,
        empty: emptyState,
        success: hackathonsGrid
    };

    // Hide all states
    Object.values(states).forEach(element => {
        element.classList.add('hidden');
    });

    // Show target state
    if (states[state]) {
        states[state].classList.remove('hidden');
    }
}

// View hackathon details
function viewHackathonDetails(hackathonId) {
    // Redirection directe vers la page de détails du hackathon
    window.location.href = `/user/hackathons/overview/${hackathonId}`;
}

// Utility functions
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initializeEventListeners();
    loadHackathons();
});