// Configuration
const API_BASE = '/api';
let currentPage = 1;
let currentFilters = {
    action: '',
    level: '',
    period: '',
    search: ''
};

// Charger les statistiques
async function loadStats() {
    try {
        const response = await apiRequest('/logs/stats');
        if (response.success) {
            const stats = response.data;
            document.getElementById('totalLogs').textContent = stats.total_logs || 0;
            document.getElementById('connections').textContent = stats.connections || 0;
            document.getElementById('teamActions').textContent = stats.team_actions || 0;
            document.getElementById('challenges').textContent = stats.challenges || 0;
        }
    } catch (error) {
        console.error('Erreur lors du chargement des stats:', error);
    }
}

// Charger les actions disponibles
async function loadActions() {
    try {
        const response = await apiRequest('/logs/actions');
        if (response.success) {
            const dropdown = document.getElementById('actionDropdown');
            dropdown.innerHTML = '<a href="#" class="dropdown-item" data-action="">Toutes les actions</a>';
            
            response.data.forEach(action => {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'dropdown-item';
                item.dataset.action = action;
                item.textContent = action;
                dropdown.appendChild(item);
            });
        }
    } catch (error) {
        console.error('Erreur lors du chargement des actions:', error);
    }
}

// Charger les logs
async function loadLogs(page = 1) {
    try {
        currentPage = page;
        const params = new URLSearchParams({
            page: page,
            per_page: 20,
            ...currentFilters
        });

        // Gérer les périodes
        if (currentFilters.period) {
            const dates = getPeriodDates(currentFilters.period);
            if (dates) {
                params.set('date_from', dates.from);
                params.set('date_to', dates.to);
            }
        }

        const response = await apiRequest(`/logs?${params.toString()}`);
        if (response.success) {
            displayLogs(response.data);
            displayPagination(response.pagination);
            document.getElementById('totalEntries').textContent = response.pagination.total;
        }
    } catch (error) {
        console.error('Erreur lors du chargement des logs:', error);
        document.getElementById('logsContainer').innerHTML = 
            '<p style="text-align: center; padding: 20px; color: var(--text-muted);">Erreur lors du chargement des logs</p>';
    }
}

// Afficher les logs
function displayLogs(logs) {
    const container = document.getElementById('logsContainer');
    
    if (logs.length === 0) {
        container.innerHTML = '<p style="text-align: center; padding: 20px; color: var(--text-muted);">Aucun log trouvé</p>';
        return;
    }

    container.innerHTML = logs.map(log => {
        const iconClass = getIconClass(log.action);
        const iconColor = getIconColor(log.level);
        
        return `
            <div class="activity-item">
                <div class="activity-icon" style="background-color: ${iconColor.bg}; color: ${iconColor.color};">
                    <i class="${iconClass}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-title">${log.user.fullname || log.user.username}</div>
                    <div class="activity-subtitle">${log.description}</div>
                    <div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">
                        IP: ${log.ip_address || 'N/A'}, Navigateur: ${log.user_agent}
                    </div>
                </div>
                <div class="activity-time">
                    ${log.relative_time}
                </div>
            </div>
        `;
    }).join('');
}

// Afficher la pagination
function displayPagination(pagination) {
    const container = document.getElementById('pagination');
    const { page, total_pages } = pagination;

    if (total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Bouton précédent
    if (page > 1) {
        html += `<button class="btn btn-secondary" onclick="loadLogs(${page - 1})">
            <i class="fas fa-chevron-left"></i> Précédent
        </button>`;
    }

    // Numéros de page
    html += `<span style="padding: 0 10px;">Page ${page} sur ${total_pages}</span>`;

    // Bouton suivant
    if (page < total_pages) {
        html += `<button class="btn btn-secondary" onclick="loadLogs(${page + 1})">
            Suivant <i class="fas fa-chevron-right"></i>
        </button>`;
    }

    container.innerHTML = html;
}

// Obtenir les dates selon la période
function getPeriodDates(period) {
    const now = new Date();
    let from, to;

    switch (period) {
        case 'today':
            from = new Date(now.setHours(0, 0, 0, 0));
            to = new Date(now.setHours(23, 59, 59, 999));
            break;
        case 'week':
            from = new Date(now.setDate(now.getDate() - 7));
            to = new Date();
            break;
        case 'month':
            from = new Date(now.setMonth(now.getMonth() - 1));
            to = new Date();
            break;
        default:
            return null;
    }

    return {
        from: from.toISOString().split('T')[0],
        to: to.toISOString().split('T')[0]
    };
}

// Obtenir l'icône selon l'action
function getIconClass(action) {
    if (action.includes('login') || action.includes('logout')) return 'fas fa-sign-in-alt';
    if (action.includes('team')) return 'fas fa-users';
    if (action.includes('challenge') || action.includes('flag')) return 'fas fa-trophy';
    if (action.includes('project')) return 'fas fa-file-code';
    if (action.includes('user') || action.includes('profile')) return 'fas fa-user';
    if (action.includes('hackathon')) return 'fas fa-calendar-alt';
    if (action.includes('submit')) return 'fas fa-paper-plane';
    return 'fas fa-circle-info';
}

// Obtenir la couleur selon le niveau
function getIconColor(level) {
    const colors = {
        'info': { bg: 'rgba(59, 130, 246, 0.2)', color: '#3b82f6' },
        'success': { bg: 'rgba(16, 185, 129, 0.2)', color: '#10b981' },
        'warning': { bg: 'rgba(245, 158, 11, 0.2)', color: '#f59e0b' },
        'error': { bg: 'rgba(239, 68, 68, 0.2)', color: '#ef4444' }
    };
    return colors[level] || colors['info'];
}

// Exporter les logs
async function exportLogs() {
    try {
        const params = new URLSearchParams(currentFilters);
        
        if (currentFilters.period) {
            const dates = getPeriodDates(currentFilters.period);
            if (dates) {
                params.set('date_from', dates.from);
                params.set('date_to', dates.to);
            }
        }

        const token = getToken();
        window.open(`${API_BASE}/logs/export?${params.toString()}&token=${token}`, '_blank');
    } catch (error) {
        console.error('Erreur lors de l\'export:', error);
        alert('Erreur lors de l\'export des logs');
    }
}

// Gestionnaires d'événements pour les filtres
document.addEventListener('DOMContentLoaded', function() {
    // Recherche avec debounce
    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = e.target.value;
            loadLogs(1);
        }, 500);
    });

    // Filtre par action
    document.getElementById('actionDropdown').addEventListener('click', function(e) {
        if (e.target.classList.contains('dropdown-item')) {
            e.preventDefault();
            currentFilters.action = e.target.dataset.action;
            document.getElementById('actionFilterText').textContent = 
                e.target.textContent;
            loadLogs(1);
        }
    });

    // Filtre par période
    document.getElementById('periodDropdown').addEventListener('click', function(e) {
        if (e.target.classList.contains('dropdown-item')) {
            e.preventDefault();
            currentFilters.period = e.target.dataset.period;
            document.getElementById('periodFilterText').textContent = 
                e.target.textContent;
            loadLogs(1);
        }
    });

    // Filtre par niveau
    document.getElementById('levelDropdown').addEventListener('click', function(e) {
        if (e.target.classList.contains('dropdown-item')) {
            e.preventDefault();
            currentFilters.level = e.target.dataset.level;
            document.getElementById('levelFilterText').textContent = 
                e.target.textContent;
            loadLogs(1);
        }
    });

    // Toggle dropdowns
    document.querySelectorAll('.dropdown-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const dropdown = this.nextElementSibling;
            
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.remove('show');
                }
            });
            
            dropdown.classList.toggle('show');
        });
    });

    // Fermer les dropdowns au clic extérieur
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });

    // Bouton d'export
    document.getElementById('exportLogsBtn').addEventListener('click', exportLogs);

    // Chargement initial
    loadStats();
    loadActions();
    loadLogs(1);
});