// Fonction pour initialiser l'application
function initApp() {
    try {
        console.log('Initialisation de l\'application...');

        // Initialiser uniquement les composants nécessaires
        // Vérifier les dropdowns personnalisés (sauf ceux de Bootstrap)
        const customDropdowns = document.querySelectorAll('.dropdown:not(.bootstrap-dropdown)');
        if (customDropdowns.length > 0) {
            console.log(`Initialisation de ${customDropdowns.length} menus déroulants personnalisés`);
            initDropdowns();
        }

        // Vérifier les modaux personnalisés (sauf ceux de Bootstrap)
        const customModals = document.querySelectorAll('.modal:not(.bootstrap-modal)');
        if (customModals.length > 0) {
            console.log(`Initialisation de ${customModals.length} modaux personnalisés`);
            initModals();
        }

        // Initialiser les autres composants si nécessaire
        if (document.querySelector('[data-search]')) {
            initSearchFilters();
        }

        // Initialiser les autres fonctionnalités
        initFileUploads();
        initFormValidation();
        initTooltips();
        initTablesSort();

        // Marquer les menus déroulants Bootstrap pour éviter les conflits
        document.querySelectorAll('.dropdown[data-bs-toggle="dropdown"]').forEach(el => {
            el.classList.add('bootstrap-dropdown');
        });

        // Marquer les modaux Bootstrap pour éviter les conflits
        document.querySelectorAll('.modal[data-bs-toggle="modal"]').forEach(el => {
            el.classList.add('bootstrap-modal');
        });

        // Ajouter la classe 'loaded' au body pour les animations d'entrée
        document.body.classList.add('loaded');

        console.log('Initialisation terminée');
    } catch (error) {
        console.error('Erreur lors de l\'initialisation de l\'application:', error);
    }
}

// Gérer le cas où le DOM est déjà chargé
if (document.readyState === 'interactive' || document.readyState === 'complete') {
    initApp();
}

// Gestionnaire d'erreurs global
// window.addEventListener('error', function(event) {
//     console.error('Erreur non gérée:', event.error || event.message, event);

//     // Afficher un message d'erreur convivial à l'utilisateur
//     const errorMessage = `Une erreur s'est produite: ${event.message || 'Erreur inconnue'}`;
//     showNotification(errorMessage, 'Veuillez recharger la page et réessayer.', 'error');

//     // Empêcher la propagation de l'erreur
//     event.preventDefault();
//     return false;
// });

// Gestionnaire pour les promesses non gérées
window.addEventListener('unhandledrejection', function (event) {
    console.error('Promesse rejetée non gérée:', event.reason);

    // Afficher un message d'erreur convivial à l'utilisateur
    const errorMessage = event.reason?.message || 'Une erreur est survenue lors du chargement des données';
    showNotification('Erreur', errorMessage, 'error');

    // Empêcher la propagation de l'erreur
    event.preventDefault();
});

/**
 * Positionne le menu dropdown par rapport à son bouton déclencheur
 * Solution robuste qui fonctionne même avec des conteneurs complexes
 */
function positionDropdownMenu(toggle, menu) {
    // Déplacer le menu au niveau du body pour éviter les problèmes de overflow
    if (!menu.dataset.originalParent) {
        // Sauvegarder le parent original pour pouvoir le restaurer plus tard
        const originalParent = menu.parentNode;
        menu.dataset.originalParent = true;

        // Créer un placeholder pour maintenir la structure DOM
        const placeholder = document.createElement('div');
        placeholder.style.display = 'none';
        placeholder.classList.add('dropdown-placeholder');
        placeholder.dataset.for = menu.id || `dropdown-${Math.random().toString(36).substr(2, 9)}`;
        if (!menu.id) menu.id = placeholder.dataset.for;

        // Remplacer le menu par le placeholder
        originalParent.replaceChild(placeholder, menu);

        // Ajouter le menu au body
        document.body.appendChild(menu);
    }

    // Réinitialiser les styles pour mesurer correctement
    menu.style.position = 'fixed';
    menu.style.top = '';
    menu.style.left = '';
    menu.style.right = '';
    menu.style.bottom = '';
    menu.style.transform = '';
    menu.style.maxHeight = '';

    // Obtenir les dimensions et positions
    const toggleRect = toggle.getBoundingClientRect();
    const menuRect = menu.getBoundingClientRect();

    // Calculer l'espace disponible
    const windowHeight = window.innerHeight;
    const windowWidth = window.innerWidth;

    // Positionner le menu sous le bouton par défaut
    let top = toggleRect.bottom + 5;
    let left = toggleRect.left;

    // Vérifier si le menu dépasse à droite
    if (left + menuRect.width > windowWidth) {
        left = toggleRect.right - menuRect.width;
        if (left < 0) left = 5; // Éviter de sortir à gauche
    }

    // Vérifier si le menu dépasse en bas
    const spaceBelow = windowHeight - top - menuRect.height;
    if (spaceBelow < 0) {
        // Si l'espace au-dessus est plus grand que l'espace en dessous
        if (toggleRect.top > (windowHeight - toggleRect.bottom)) {
            top = toggleRect.top - menuRect.height - 5;
        } else {
            // Sinon, limiter la hauteur du menu et ajouter un défilement
            menu.style.maxHeight = `${windowHeight - top - 20}px`;
            menu.style.overflowY = 'auto';
        }
    }

    // Appliquer les positions
    menu.style.top = `${top}px`;
    menu.style.left = `${left}px`;

    // Ajouter une classe pour l'animation
    menu.classList.add('dropdown-positioned');
}

/**
 * Ferme tous les dropdowns et restaure leur position d'origine
 */
function closeAllDropdowns() {
    document.querySelectorAll('.dropdown.active').forEach(dropdown => {
        dropdown.classList.remove('active');

        const menu = dropdown.querySelector('.dropdown-menu') ||
            document.getElementById(dropdown.querySelector('.dropdown-placeholder')?.dataset.for);

        if (menu) {
            menu.classList.remove('show');
            menu.classList.remove('dropdown-positioned');

            // Restaurer le menu à sa position d'origine si nécessaire
            if (menu.dataset.originalParent) {
                const placeholder = document.querySelector(`.dropdown-placeholder[data-for="${menu.id}"]`);
                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.replaceChild(menu, placeholder);
                    delete menu.dataset.originalParent;
                }
            }
        }
    });
}

/**
 * Gestion des dropdowns
 * Solution robuste qui fonctionne même avec des conteneurs complexes
 */
function initDropdowns() {
    // Ne pas initialiser les dropdowns Bootstrap car ils sont déjà gérés par Bootstrap
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle:not([data-bs-toggle="dropdown"]):not(.bootstrap-dropdown)');

    // Si aucun dropdown personnalisé n'est trouvé, ne rien faire
    if (dropdownToggles.length === 0) {
        console.log('Aucun menu déroulant personnalisé à initialiser');
        return;
    }

    console.log(`Initialisation de ${dropdownToggles.length} menus déroulants personnalisés`);

    dropdownToggles.forEach(toggle => {
        // Vérifier si le toggle a déjà un gestionnaire d'événements
        if (toggle.hasAttribute('data-dropdown-initialized')) return;

        // Marquer comme initialisé
        toggle.setAttribute('data-dropdown-initialized', 'true');

        // Ajouter un ID unique si non défini
        if (!toggle.id) {
            toggle.id = 'dropdown-toggle-' + Math.random().toString(36).substr(2, 9);
        }

        // Ajouter l'écouteur d'événements
        toggle.addEventListener('click', function (e) {
            // Ne pas empêcher le comportement par défaut pour les liens
            if (this.tagName !== 'A') {
                e.preventDefault();
            }
            e.stopPropagation();

            const dropdown = this.closest('.dropdown');
            if (!dropdown) {
                console.warn('Élément parent .dropdown non trouvé pour', this);
                return;
            }

            // Trouver le menu déroulant correspondant
            let menu = dropdown.querySelector('.dropdown-menu');
            if (!menu) {
                // Essayer de trouver le menu par aria-labelledby
                const menuId = this.getAttribute('aria-controls') || this.getAttribute('aria-labelledby');
                if (menuId) {
                    menu = document.getElementById(menuId);
                }

                if (!menu) {
                    console.warn('Menu déroulant non trouvé pour', this);
                    return;
                }
            }

            // Si le dropdown est déjà actif, le fermer
            if (dropdown.classList.contains('active')) {
                dropdown.classList.remove('active');
                menu.classList.remove('show');

                // Restaurer le menu à sa position d'origine si nécessaire
                if (menu.dataset.originalParent) {
                    const placeholder = document.querySelector(`.dropdown-placeholder[data-for="${menu.id}"]`);
                    if (placeholder && placeholder.parentNode) {
                        placeholder.parentNode.replaceChild(menu, placeholder);
                        delete menu.dataset.originalParent;
                    }
                }

                return;
            }

            // Fermer tous les autres dropdowns
            closeAllDropdowns();

            // Activer le dropdown actuel
            dropdown.classList.add('active');
            menu.classList.add('show');

            // Positionner le menu
            positionDropdownMenu(toggle, menu);
        });
    });

    // Fermer les dropdowns quand on clique ailleurs
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.dropdown') && !e.target.closest('.dropdown-menu')) {
            closeAllDropdowns();
        }
    });

    // Fermer les dropdowns quand on appuie sur Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    // Repositionner les dropdowns lors du défilement ou du redimensionnement
    window.addEventListener('scroll', function () {
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu') ||
                document.getElementById(dropdown.querySelector('.dropdown-placeholder')?.dataset.for);

            if (toggle && menu && menu.classList.contains('show')) {
                positionDropdownMenu(toggle, menu);
            }
        });
    }, { passive: true });

    window.addEventListener('resize', function () {
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu') ||
                document.getElementById(dropdown.querySelector('.dropdown-placeholder')?.dataset.for);

            if (toggle && menu && menu.classList.contains('show')) {
                positionDropdownMenu(toggle, menu);
            }
        });
    });
}


/**
* Gestion des modals
*/
function initModals() {
    // Ouvrir les modals
    document.querySelectorAll('[data-modal]').forEach(button => {
        button.addEventListener('click', function () {
            const modalId = this.getAttribute('data-modal');
            const modal = document.getElementById(modalId);

            if (modal) {
                openModal(modal);
            }
        });
    });

    // Fermer les modals avec le bouton de fermeture
    document.querySelectorAll('.modal-close').forEach(closeButton => {
        closeButton.addEventListener('click', function () {
            const modal = this.closest('.modal');
            closeModal(modal);
        });
    });

    // Fermer les modals en cliquant en dehors
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                closeModal(this);
            }
        });
    });

    // Fermer les modals avec Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                closeModal(openModal);
            }
        }
    });
}

/**
 * 
 * @param {HTMLElement} modal 
 */
function openModal(modal) {
    // gerer les cas ou c'est un id ou une classe
    if (typeof modal === 'string') {
        modal = document.querySelector(modal);
    }

    if (!modal) return;

    // Fermer les autres modals
    document.querySelectorAll('.modal.show').forEach(openModal => {
        if (openModal !== modal) {
            closeModal(openModal);
        }
    });

    // Ouvrir le modal
    modal.classList.add('show');
    document.body.classList.add('modal-open');

    // Animation d'entrée
    setTimeout(() => {
        modal.querySelector('.modal-content').classList.add('show');
    }, 10);
}

/**
 * 
 * @param {HTMLElement} modal 
 */
function closeModal(modal) {
    // gerer les cas ou c'est un id ou une classe
    if (typeof modal === 'string') {
        modal = document.querySelector(modal);
    }
    if (!modal) return;

    // Animation de sortie
    modal.querySelector('.modal-content').classList.remove('show');

    // Fermer le modal après l'animation
    setTimeout(() => {
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');

        // Réinitialiser les formulaires dans le modal
        const form = modal.querySelector('form');
        if (form) {
            form.reset();

            // Réinitialiser les messages de validation
            form.querySelectorAll('.is-invalid').forEach(field => {
                field.classList.remove('is-invalid');
            });

            form.querySelectorAll('.invalid-feedback').forEach(feedback => {
                feedback.remove();
            });
        }
    }, 300);
}

/**
 * Filtres de recherche pour les tableaux
 */
function initSearchFilters() {
    document.querySelectorAll('.search-input').forEach(input => {
        input.addEventListener('keyup', function () {
            const tableId = this.getAttribute('data-table');
            const table = document.getElementById(tableId);

            if (!table) return;

            const searchText = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Afficher un message si aucun résultat
            const noResults = table.querySelector('.no-results');
            const visibleRows = table.querySelectorAll('tbody tr:not([style*="display: none"])');

            if (visibleRows.length === 0) {
                if (!noResults) {
                    const tbody = table.querySelector('tbody');
                    const tr = document.createElement('tr');
                    tr.className = 'no-results';
                    tr.innerHTML = `<td colspan="${table.querySelectorAll('thead th').length}" class="text-center">Aucun résultat trouvé</td>`;
                    tbody.appendChild(tr);
                }
            } else {
                if (noResults) {
                    noResults.remove();
                }
            }
        });
    });
}

/**
* Gestion des uploads de fichiers
*/
function initFileUploads() {
    document.querySelectorAll('.file-upload input[type="file"]').forEach(input => {
        const fileUpload = input.closest('.file-upload');
        const fileUploadText = fileUpload.querySelector('.file-upload-text');
        const originalText = fileUploadText.textContent;

        // Drag & Drop
        fileUpload.addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add('dragover');
        });

        fileUpload.addEventListener('dragleave', function () {
            this.classList.remove('dragover');
        });

        fileUpload.addEventListener('drop', function (e) {
            e.preventDefault();
            this.classList.remove('dragover');

            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                updateFileUploadText(input, fileUploadText, originalText);
            }
        });

        // Click pour sélectionner
        fileUpload.addEventListener('click', function () {
            input.click();
        });

        // Mise à jour du texte quand un fichier est sélectionné
        input.addEventListener('change', function () {
            updateFileUploadText(this, fileUploadText, originalText);
        });
    });
}

function updateFileUploadText(input, fileUploadText, originalText) {
    if (input.files.length) {
        const fileName = input.files[0].name;
        fileUploadText.textContent = fileName;
        input.closest('.file-upload').classList.add('has-file');
    } else {
        fileUploadText.textContent = originalText;
        input.closest('.file-upload').classList.remove('has-file');
    }
}

/**
* Validation des formulaires
*/
function initFormValidation() {
    document.querySelectorAll('.needs-validation').forEach(form => {
        form.addEventListener('submit', function (event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            this.classList.add('was-validated');

            // Ajouter des messages d'erreur personnalisés
            this.querySelectorAll('input, select, textarea').forEach(field => {
                if (!field.validity.valid) {
                    field.classList.add('is-invalid');

                    // Supprimer les anciens messages
                    const existingFeedback = field.parentElement.querySelector('.invalid-feedback');
                    if (existingFeedback) {
                        existingFeedback.remove();
                    }

                    // Ajouter un nouveau message
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';

                    if (field.validity.valueMissing) {
                        feedback.textContent = 'Ce champ est requis';
                    } else if (field.validity.typeMismatch) {
                        feedback.textContent = 'Veuillez entrer une valeur valide';
                    } else if (field.validity.tooShort) {
                        feedback.textContent = `Veuillez entrer au moins ${field.minLength} caractères`;
                    } else if (field.validity.tooLong) {
                        feedback.textContent = `Veuillez entrer au maximum ${field.maxLength} caractères`;
                    } else if (field.validity.rangeUnderflow) {
                        feedback.textContent = `La valeur minimale est ${field.min}`;
                    } else if (field.validity.rangeOverflow) {
                        feedback.textContent = `La valeur maximale est ${field.max}`;
                    } else if (field.validity.patternMismatch) {
                        feedback.textContent = 'Le format est incorrect';
                    }

                    field.parentElement.appendChild(feedback);
                } else {
                    field.classList.remove('is-invalid');
                    const existingFeedback = field.parentElement.querySelector('.invalid-feedback');
                    if (existingFeedback) {
                        existingFeedback.remove();
                    }
                }
            });
        }, false);
    });
}

/**
* Initialisation des tooltips
*/
function initTooltips() {
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', function () {
            const tooltipText = this.getAttribute('data-tooltip');

            // Créer le tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = tooltipText;
            document.body.appendChild(tooltip);

            // Positionner le tooltip
            const rect = this.getBoundingClientRect();
            tooltip.style.top = (rect.top - tooltip.offsetHeight - 5) + 'px';
            tooltip.style.left = (rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)) + 'px';

            // Afficher le tooltip
            setTimeout(() => {
                tooltip.classList.add('show');
            }, 10);

            // Stocker une référence au tooltip
            this.tooltip = tooltip;
        });

        element.addEventListener('mouseleave', function () {
            if (this.tooltip) {
                this.tooltip.classList.remove('show');

                // Supprimer le tooltip après l'animation
                setTimeout(() => {
                    if (this.tooltip && this.tooltip.parentNode) {
                        this.tooltip.parentNode.removeChild(this.tooltip);
                        this.tooltip = null;
                    }
                }, 300);
            }
        });
    });
}

/**
* Tri des tableaux
*/
function initTablesSort() {
    document.querySelectorAll('table thead th').forEach(th => {
        if (th.classList.contains('no-sort')) return;

        th.style.cursor = 'pointer';
        th.title = 'Cliquer pour trier';

        th.addEventListener('click', function () {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const index = Array.from(this.parentElement.children).indexOf(this);
            const direction = this.classList.contains('sort-asc') ? 'desc' : 'asc';

            // Réinitialiser les autres colonnes
            this.parentElement.querySelectorAll('th').forEach(header => {
                header.classList.remove('sort-asc', 'sort-desc');
            });

            // Définir la direction du tri
            this.classList.add(`sort-${direction}`);

            // Trier les lignes
            rows.sort((a, b) => {
                const aValue = a.children[index].textContent.trim();
                const bValue = b.children[index].textContent.trim();

                // Vérifier si les valeurs sont des nombres
                const aNum = parseFloat(aValue);
                const bNum = parseFloat(bValue);

                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return direction === 'asc' ? aNum - bNum : bNum - aNum;
                }

                // Sinon, trier comme des chaînes
                return direction === 'asc'
                    ? aValue.localeCompare(bValue, undefined, { sensitivity: 'base' })
                    : bValue.localeCompare(aValue, undefined, { sensitivity: 'base' });
            });

            // Réorganiser les lignes
            rows.forEach(row => {
                tbody.appendChild(row);
            });
        });
    });
}

/**
* Fonction pour confirmer une action
*/
function confirmAction(message, callback) {
    // Créer le modal de confirmation
    const confirmModal = document.createElement('div');
    confirmModal.className = 'modal confirm-modal';
    confirmModal.innerHTML = `
      <div class="modal-content">
          <div class="modal-header">
              <h2><i class="fas fa-question-circle"></i> Confirmation</h2>
              <button class="modal-close">&times;</button>
          </div>
          <div class="modal-body">
              <p>${message}</p>
              <div class="form-actions">
                  <button class="btn btn-secondary cancel-btn"><i class="fas fa-times"></i> Annuler</button>
                  <button class="btn btn-danger confirm-btn"><i class="fas fa-check"></i> Confirmer</button>
              </div>
          </div>
      </div>
  `;

    document.body.appendChild(confirmModal);

    // Ouvrir le modal
    openModal(confirmModal);

    // Gérer les boutons
    confirmModal.querySelector('.cancel-btn').addEventListener('click', () => {
        closeModal(confirmModal);
        setTimeout(() => {
            confirmModal.parentNode.removeChild(confirmModal);
        }, 300);
    });

    confirmModal.querySelector('.modal-close').addEventListener('click', () => {
        closeModal(confirmModal);
        setTimeout(() => {
            confirmModal.parentNode.removeChild(confirmModal);
        }, 300);
    });

    confirmModal.querySelector('.confirm-btn').addEventListener('click', () => {
        closeModal(confirmModal);
        setTimeout(() => {
            confirmModal.parentNode.removeChild(confirmModal);
            if (typeof callback === 'function') {
                callback();
            }
        }, 300);
    });

    // Fermer en cliquant en dehors
    confirmModal.addEventListener('click', function (e) {
        if (e.target === this) {
            closeModal(confirmModal);
            setTimeout(() => {
                confirmModal.parentNode.removeChild(confirmModal);
            }, 300);
        }
    });

}


/**
 * @description Fonction pour initialiser les tooltips
 * @returns {void}
 * @usage initializeTooltips();
 * @prerequis mettre en place les tooltips dans le HTML avec le data-tooltip
 */
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');

    tooltipElements.forEach(el => {
        let tooltipEl;
        let hideTimeout;

        const show = () => {
            clearTimeout(hideTimeout);

            const tooltip = el.getAttribute('data-tooltip');
            if (!tooltip) return;

            // Si déjà un tooltip affiché, on le supprime
            if (tooltipEl) tooltipEl.remove();

            tooltipEl = document.createElement('div');
            tooltipEl.className = `
                fixed px-2 py-2 text-sm rounded-lg shadow-lg border
                border-slate-700 bg-slate-900/95 backdrop-blur-sm text-white
                opacity-0 transition-all duration-200 transform scale-95
                pointer-events-none z-[100001]! max-w-xs break-words
                text-left font-normal leading-normal
            `;
            tooltipEl.textContent = tooltip;
            tooltipEl.setAttribute("role", "tooltip");
            tooltipEl.setAttribute("aria-hidden", "true");

            document.body.appendChild(tooltipEl);

            // Calculate positions with viewport boundaries
            const rect = el.getBoundingClientRect();
            const tooltipRect = tooltipEl.getBoundingClientRect();
            const viewportPadding = 12;

            // Default position: centered above the element
            let top = rect.top - tooltipRect.height - 10;
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
            let arrowPosition = '';

            // Check for viewport collisions
            // Horizontal adjustment
            if (left < viewportPadding) {
                left = viewportPadding;
            } else if (left + tooltipRect.width > window.innerWidth - viewportPadding) {
                left = window.innerWidth - tooltipRect.width - viewportPadding;
            }

            // Vertical adjustment
            if (top < viewportPadding) {
                // Not enough space above, position below
                top = rect.bottom + 10;
                arrowPosition = 'bottom';
            } else {
                arrowPosition = 'top';
            }

            // Add arrow class based on position
            tooltipEl.classList.add(`tooltip-arrow-${arrowPosition}`);

            // Apply final position
            tooltipEl.style.top = `${Math.max(viewportPadding, Math.min(top, window.innerHeight - tooltipRect.height - viewportPadding))}px`;
            tooltipEl.style.left = `${Math.max(viewportPadding, Math.min(left, window.innerWidth - tooltipRect.width - viewportPadding))}px`;

            // Trigger reflow and animate in
            void tooltipEl.offsetWidth; // Force reflow
            tooltipEl.style.opacity = '1';
            tooltipEl.style.transform = 'scale(1)';
        };

        const hide = () => {
            if (tooltipEl) {
                tooltipEl.classList.add("opacity-0", "scale-95");
                hideTimeout = setTimeout(() => {
                    tooltipEl?.remove();
                    tooltipEl = null;
                }, 200);
            }
        };

        el.addEventListener('mouseenter', show);
        el.addEventListener('mouseleave', hide);
        el.addEventListener('blur', hide);   // accessibilité (clavier)
        el.addEventListener('click', hide);  // si clic sur élément
    });
}

function showTooltip(e) {
    const tooltip = this.getAttribute('data-tooltip');
    if (!tooltip) return;

    const tooltipEl = document.createElement('div');
    tooltipEl.className = 'tooltip';
    tooltipEl.textContent = tooltip;

    // Positionnement
    const rect = this.getBoundingClientRect();
    tooltipEl.style.position = 'fixed';
    tooltipEl.style.left = `${rect.left + (rect.width / 2)}px`;
    tooltipEl.style.top = `${rect.top - 40}px`;
    tooltipEl.style.transform = 'translateX(-50%)';
    tooltipEl.style.zIndex = '1000';
    tooltipEl.style.pointerEvents = 'none';
    tooltipEl.classList.add('bg-slate-800', 'text-white', 'text-xs', 'px-2', 'py-1', 'rounded', 'shadow-lg', 'border', 'border-slate-700');

    document.body.appendChild(tooltipEl);
    this._tooltip = tooltipEl;
}

function hideTooltip() {
    if (this._tooltip) {
        this._tooltip.remove();
        this._tooltip = null;
    }
}

// ========== Security helpers (XSS) ==========
/**
 * Encode plain text to HTML entities (safe for text insertion)
 * @param {string} str
 * @returns {string}
 */
function escapeHTML(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

/**
 * Sanitize an HTML string with a conservative whitelist.
 * - Removes disallowed tags and attributes
 * - Strips event handlers (on*) and javascript: URLs
 * - Keeps only safe URL protocols (http, https, mailto, tel)
 * @param {string} dirtyHTML
 * @param {{allowedTags?: string[], allowedAttrs?: Record<string,string[]>, allowDataImages?: boolean}} [opts]
 * @returns {string}
 */
function sanitizeHTML(dirtyHTML, opts = {}) {
    if (!dirtyHTML) return '';

    const DEFAULT_ALLOWED_TAGS = ['b','i','em','strong','u','s','br','p','ul','ol','li','blockquote','code','pre','span','a'];
    const DEFAULT_ALLOWED_ATTRS = {
        a: ['href','title','target','rel'],
        span: ['class'],
        code: ['class'],
        pre: ['class']
    };
    const allowedTags = new Set((opts.allowedTags || DEFAULT_ALLOWED_TAGS).map(t => t.toLowerCase()));
    const allowedAttrs = opts.allowedAttrs || DEFAULT_ALLOWED_ATTRS;
    const allowDataImages = Boolean(opts.allowDataImages);

    const SAFE_URL = /^(https?:|mailto:|tel:)/i;
    const DATA_IMAGE = /^data:image\/(png|jpeg|jpg|gif|webp);base64,/i;

    const template = document.createElement('template');
    template.innerHTML = dirtyHTML;

    const sanitizeNode = (node) => {
        // Remove comment nodes
        if (node.nodeType === Node.COMMENT_NODE) {
            node.remove();
            return;
        }
        // Text nodes are safe
        if (node.nodeType === Node.TEXT_NODE) {
            return;
        }
        // Element nodes
        if (node.nodeType === Node.ELEMENT_NODE) {
            const tag = node.tagName.toLowerCase();
            if (!allowedTags.has(tag)) {
                // Replace disallowed element with its text content
                const text = document.createTextNode(node.textContent || '');
                node.replaceWith(text);
                return;
            }
            // Clone allowed attributes safely
            [...node.attributes].forEach(attr => {
                const name = attr.name.toLowerCase();
                const value = attr.value;

                // Strip all event handlers (on*) and style attributes
                if (name.startsWith('on') || name === 'style') {
                    node.removeAttribute(attr.name);
                    return;
                }

                const tagAllowed = (allowedAttrs[tag] || []).map(a => a.toLowerCase());
                if (!tagAllowed.includes(name)) {
                    node.removeAttribute(attr.name);
                    return;
                }

                // Special handling for URL-bearing attributes
                if ((tag === 'a' && name === 'href')) {
                    const val = value.trim();
                    const safe = SAFE_URL.test(val) || (allowDataImages && DATA_IMAGE.test(val));
                    if (!safe) {
                        node.removeAttribute(attr.name);
                    } else {
                        // Security: enforce rel and target safety
                        if (!node.getAttribute('rel')) node.setAttribute('rel', 'noopener noreferrer');
                        if (/_blank/i.test(node.getAttribute('target') || '')) {
                            node.setAttribute('rel', 'noopener noreferrer');
                        }
                    }
                }
            });
        }
        // Recurse children (use slice to avoid live collection issues if nodes removed)
        Array.from(node.childNodes).forEach(sanitizeNode);
    };

    Array.from(template.content.childNodes).forEach(sanitizeNode);
    return template.innerHTML;
}

/**
 * Safely set innerHTML: sanitize first
 * @param {HTMLElement} el
 * @param {string} html
 * @param {Parameters<typeof sanitizeHTML>[1]} [opts]
 */
function setSafeHTML(el, html, opts) {
    if (!el) return;
    el.innerHTML = sanitizeHTML(html, opts);
}
// ========== End Security helpers ==========

/**
 * Affiche une notification.
 * @param {string} message - Le message à afficher.
 * @param {string} details - Les détails de la notification (optionnel).
 * @param {string} type - Le type de notification ('success', 'error', 'info', 'warning').
 * @param {number} duration - Durée en millisecondes avant disparition (optionnel).
 */
let activeNotifications = [];
const NOTIFICATION_OFFSET = 10; // Espacement entre les notifications en pixels

function updateNotificationsPosition() {
    let topPosition = 70; // Position de départ en haut

    // Parcourir toutes les notifications visibles
    activeNotifications.forEach(notification => {
        if (document.body.contains(notification)) {
            notification.style.top = `${topPosition}px`;
            // Ajouter la hauteur de la notification + l'espacement pour la prochaine
            topPosition += notification.offsetHeight + NOTIFICATION_OFFSET;
        }
    });
}

// Fonctions d'affichage/masquage des erreurs
/**
 * @description Affiche et anime un message d'erreur
 * @param {HTMLElement} inputElement 
 * @param {HTMLElement} errorElement 
 * @param {string} message 
 */
function showError(inputElement, errorElement, message) {
    // Ajouter la classe d'erreur à l'input
    inputElement.parentElement.classList.add('input-error');

    // Afficher et animer le message d'erreur
    errorElement.textContent = message;
    errorElement.classList.remove('hidden', 'fade-out');
}

/**
 * @description Masque et anime un message d'erreur
 * @param {HTMLElement} inputElement 
 * @param {HTMLElement} errorElement 
 */
function hideError(inputElement, errorElement) {
    // Retirer la classe d'erreur de l'input
    inputElement.parentElement.classList.remove('input-error');

    // Vérifier si l'erreur est déjà masquée
    if (errorElement.classList.contains('hidden')) return;

    // Supprimer l'ancienne animation si elle est encore en cours
    errorElement.classList.remove('fade-in');

    // Ajouter la classe de disparition
    errorElement.classList.add('fade-out');

    // Attendre la fin de l'animation avant de cacher complètement
    errorElement.addEventListener('animationend', function () {
        errorElement.classList.add('hidden');
        errorElement.classList.remove('fade-out'); // Nettoyage après animation
    }, { once: true });
}

document.addEventListener('DOMContentLoaded', async () => {
    initApp();
    // initialisation des tooltips
    initializeTooltips();
    // initialisation des notifications
    const notificationElement = document.getElementById('notification-data');
    if (notificationElement) {
        try {
            // TODO: nettoyer la notification de la session après affichage 
            fetch('clearNotification.php', { method: 'POST' })
            const notificationData = JSON.parse(notificationElement.getAttribute('data-notification'));
            if (notificationData) {
                showNotification(
                    notificationData.message,
                    notificationData.details || null,
                    notificationData.type || 'info'
                );
                // Supprimer la notification de la session après affichage
                fetch('clearNotification.php', { method: 'POST' })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erreur lors de la suppression de la notification');
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
            const flashMessage = getFlashMessage();
            if (flashMessage) {
                showNotification(
                    flashMessage.message,
                    flashMessage.details || null,
                    flashMessage.type || 'info'
                );
                // Supprimer le message après l'avoir affiché
                localStorage.removeItem('flashMessage');
            }
        } catch (e) {
            console.error('Erreur lors du parsing des données de notification:', e);
        }
    }

});