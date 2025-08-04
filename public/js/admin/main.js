document.addEventListener('DOMContentLoaded', function() {
  // Initialiser tous les composants
  initDropdowns();
  initModals();
  initSearchFilters();
  initFileUploads();
  initFormValidation();
  initTooltips();
  initTablesSort();
  
  // Ajouter la classe 'loaded' au body pour les animations d'entrée
  document.body.classList.add('loaded');
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
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            
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
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown') && !e.target.closest('.dropdown-menu')) {
            closeAllDropdowns();
        }
    });
    
    // Fermer les dropdowns quand on appuie sur Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    });
    
    // Repositionner les dropdowns lors du défilement ou du redimensionnement
    window.addEventListener('scroll', function() {
        document.querySelectorAll('.dropdown.active').forEach(dropdown => {
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu') || 
                         document.getElementById(dropdown.querySelector('.dropdown-placeholder')?.dataset.for);
            
            if (toggle && menu && menu.classList.contains('show')) {
                positionDropdownMenu(toggle, menu);
            }
        });
    }, { passive: true });
    
    window.addEventListener('resize', function() {
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
      button.addEventListener('click', function() {
          const modalId = this.getAttribute('data-modal');
          const modal = document.getElementById(modalId);
          
          if (modal) {
              openModal(modal);
          }
      });
  });
  
  // Fermer les modals avec le bouton de fermeture
  document.querySelectorAll('.modal-close').forEach(closeButton => {
      closeButton.addEventListener('click', function() {
          const modal = this.closest('.modal');
          closeModal(modal);
      });
  });
  
  // Fermer les modals en cliquant en dehors
  document.querySelectorAll('.modal').forEach(modal => {
      modal.addEventListener('click', function(e) {
          if (e.target === this) {
              closeModal(this);
          }
      });
  });
  
  // Fermer les modals avec Escape
  document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
          const openModal = document.querySelector('.modal.show');
          if (openModal) {
              closeModal(openModal);
          }
      }
  });
}

function openModal(modal) {
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

function closeModal(modal) {
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
      input.addEventListener('keyup', function() {
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
      fileUpload.addEventListener('dragover', function(e) {
          e.preventDefault();
          this.classList.add('dragover');
      });
      
      fileUpload.addEventListener('dragleave', function() {
          this.classList.remove('dragover');
      });
      
      fileUpload.addEventListener('drop', function(e) {
          e.preventDefault();
          this.classList.remove('dragover');
          
          if (e.dataTransfer.files.length) {
              input.files = e.dataTransfer.files;
              updateFileUploadText(input, fileUploadText, originalText);
          }
      });
      
      // Click pour sélectionner
      fileUpload.addEventListener('click', function() {
          input.click();
      });
      
      // Mise à jour du texte quand un fichier est sélectionné
      input.addEventListener('change', function() {
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
      form.addEventListener('submit', function(event) {
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
      element.addEventListener('mouseenter', function() {
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
      
      element.addEventListener('mouseleave', function() {
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
      
      th.addEventListener('click', function() {
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
* Fonction pour afficher une notification
*/
function showNotification(message, type = 'info', duration = 3000) {
  // Créer la notification
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  
  // Ajouter l'icône en fonction du type
  let icon = '';
  switch (type) {
      case 'success':
          icon = '<i class="fas fa-check-circle"></i>';
          break;
      case 'error':
          icon = '<i class="fas fa-exclamation-circle"></i>';
          break;
      case 'warning':
          icon = '<i class="fas fa-exclamation-triangle"></i>';
          break;
      default:
          icon = '<i class="fas fa-info-circle"></i>';
  }
  
  notification.innerHTML = `
      <div class="notification-icon">${icon}</div>
      <div class="notification-content">${message}</div>
      <button class="notification-close">&times;</button>
  `;
  
  // Ajouter au conteneur de notifications ou créer un nouveau
  let container = document.querySelector('.notification-container');
  if (!container) {
      container = document.createElement('div');
      container.className = 'notification-container';
      document.body.appendChild(container);
  }
  
  container.appendChild(notification);
  
  // Afficher la notification avec animation
  setTimeout(() => {
      notification.classList.add('show');
  }, 10);
  
  // Fermer la notification après la durée spécifiée
  const timeout = setTimeout(() => {
      closeNotification(notification);
  }, duration);
  
  // Bouton de fermeture
  notification.querySelector('.notification-close').addEventListener('click', () => {
      clearTimeout(timeout);
      closeNotification(notification);
  });
  
  // Pause du timer au survol
  notification.addEventListener('mouseenter', () => {
      clearTimeout(timeout);
  });
  
  // Reprise du timer à la sortie
  notification.addEventListener('mouseleave', () => {
      const timeout = setTimeout(() => {
          closeNotification(notification);
      }, duration);
  });
}

function closeNotification(notification) {
  notification.classList.remove('show');
  
  // Supprimer après l'animation
  setTimeout(() => {
      if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
          
          // Supprimer le conteneur s'il est vide
          const container = document.querySelector('.notification-container');
          if (container && container.children.length === 0) {
              container.parentNode.removeChild(container);
          }
      }
  }, 300);
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
  confirmModal.addEventListener('click', function(e) {
      if (e.target === this) {
          closeModal(confirmModal);
          setTimeout(() => {
              confirmModal.parentNode.removeChild(confirmModal);
          }, 300);
      }
  });
}