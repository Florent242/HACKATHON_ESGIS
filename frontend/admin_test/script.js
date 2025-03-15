document.addEventListener('DOMContentLoaded', function() {
    // Gestion des menus déroulants
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('.dropdown-trigger');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (trigger && menu) {
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                menu.classList.toggle('show');
            });
        }
    });
    
    // Fermer les menus déroulants en cliquant ailleurs
    document.addEventListener('click', function() {
        const openMenus = document.querySelectorAll('.dropdown-menu.show');
        openMenus.forEach(menu => {
            menu.classList.remove('show');
        });
    });
    
    // Gestion des alertes fermables
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.close-alert');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        }
    });
    
    // Gestion des onglets
    const tabContainers = document.querySelectorAll('.tabs-container');
    
    tabContainers.forEach(container => {
        const tabs = container.querySelectorAll('.tab');
        const tabContents = container.querySelectorAll('.tab-content');
        
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', function() {
                // Désactiver tous les onglets et contenus
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Activer l'onglet cliqué et son contenu
                tab.classList.add('active');
                tabContents[index].classList.add('active');
            });
        });
    });
    
    // Gestion des modales
    const modalTriggers = document.querySelectorAll('[data-modal]');
    
    modalTriggers.forEach(trigger => {
        const modalId = trigger.getAttribute('data-modal');
        const modal = document.getElementById(modalId);
        
        if (modal) {
            const closeBtn = modal.querySelector('.modal-close');
            
            trigger.addEventListener('click', function(e) {
                e.preventDefault();
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            });
            
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                });
            }
            
            // Fermer la modale en cliquant en dehors
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        }
    });
    
    // Gestion des confirmations
    const confirmBtns = document.querySelectorAll('[data-confirm]');
    
    confirmBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = btn.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir effectuer cette action ?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Gestion des tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        const text = tooltip.getAttribute('data-tooltip');
        
        tooltip.addEventListener('mouseenter', function() {
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip';
            tooltipEl.textContent = text;
            
            document.body.appendChild(tooltipEl);
            
            const rect = tooltip.getBoundingClientRect();
            tooltipEl.style.top = rect.top - tooltipEl.offsetHeight - 10 + 'px';
            tooltipEl.style.left = rect.left + (rect.width / 2) - (tooltipEl.offsetWidth / 2) + 'px';
            
            setTimeout(() => {
                tooltipEl.classList.add('show');
            }, 10);
        });
        
        tooltip.addEventListener('mouseleave', function() {
            const tooltipEl = document.querySelector('.tooltip');
            
            if (tooltipEl) {
                tooltipEl.classList.remove('show');
                
                setTimeout(() => {
                    tooltipEl.remove();
                }, 300);
            }
        });
    });
    
    // Gestion des filtres de tableau
    const tableFilters = document.querySelectorAll('.table-filter');
    
    tableFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            const tableId = filter.getAttribute('data-table');
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                const filterValue = filter.value.toLowerCase();
                const filterColumn = filter.getAttribute('data-column');
                
                rows.forEach(row => {
                    const cell = row.querySelector(`td:nth-child(${filterColumn})`);
                    
                    if (cell) {
                        const text = cell.textContent.toLowerCase();
                        
                        if (filterValue === '' || text.includes(filterValue)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    }
                });
            }
        });
    });
    
    // Gestion de la recherche dans les tableaux
    const tableSearches = document.querySelectorAll('.table-search');
    
    tableSearches.forEach(search => {
        search.addEventListener('input', function() {
            const tableId = search.getAttribute('data-table');
            const table = document.getElementById(tableId);
            
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                const searchValue = search.value.toLowerCase();
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    let found = false;
                    
                    cells.forEach(cell => {
                        const text = cell.textContent.toLowerCase();
                        
                        if (text.includes(searchValue)) {
                            found = true;
                        }
                    });
                    
                    if (found || searchValue === '') {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });
});