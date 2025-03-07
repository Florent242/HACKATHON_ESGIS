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
* Gestion des dropdowns
* Permet aux dropdowns d'apparaître en dehors de leurs conteneurs
*/
function initDropdowns() {
  const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
  
  dropdownToggles.forEach(toggle => {
      toggle.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const dropdown = this.parentElement;
          const menu = dropdown.querySelector('.dropdown-menu');
          
          // Fermer tous les autres dropdowns
          document.querySelectorAll('.dropdown.active').forEach(activeDropdown => {
              if (activeDropdown !== dropdown) {
                  activeDropdown.classList.remove('active');
                  activeDropdown.querySelector('.dropdown-menu').classList.remove('show');
              }
          });
          
          // Toggle le dropdown actuel
          dropdown.classList.toggle('active');
          menu.classList.toggle('show');
          
          if (menu.classList.contains('show')) {
              positionDropdownMenu(toggle, menu);
          }
      });
  });
  
  // Fermer les dropdowns quand on clique ailleurs
  document.addEventListener('click', function(e) {
      if (!e.target.closest('.dropdown')) {
          document.querySelectorAll('.dropdown.active').forEach(dropdown => {
              dropdown.classList.remove('active');
              dropdown.querySelector('.dropdown-menu').classList.remove('show');
          });
      }
  });
  
  // Fermer les dropdowns quand on appuie sur Escape
  document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
          document.querySelectorAll('.dropdown.active').forEach(dropdown => {
              dropdown.classList.remove('active');
              dropdown.querySelector('.dropdown-menu').classList.remove('show');
          });
      }
  });
}

/**
* Positionne le menu dropdown en fonction de l'espace disponible
*/
function positionDropdownMenu(toggle, menu) {
  // Réinitialiser les styles pour mesurer correctement
  menu.style.position = 'fixed';
  menu.style.top = '';
  menu.style.left = '';
  menu.style.right = '';
  menu.style.bottom = '';
  
  const toggleRect = toggle.getBoundingClientRect();
  const menuRect = menu.getBoundingClientRect();
  const windowHeight = window.innerHeight;
  const windowWidth = window.innerWidth;
  
  // Déterminer si le menu doit s'ouvrir vers le haut ou vers le bas
  const spaceBelow = windowHeight - toggleRect.bottom;
  const spaceAbove = toggleRect.top;
  const openUpward = spaceBelow < menuRect.height && spaceAbove > menuRect.height;
  
  // Déterminer si le menu doit s'ouvrir vers la gauche ou vers la droite
  const spaceRight = windowWidth - toggleRect.left;
  const spaceLeft = toggleRect.right;
  const openLeftward = spaceRight < menuRect.width && spaceLeft > menuRect.width;
  
  // Positionner le menu
  if (openUpward) {
      menu.style.bottom = (windowHeight - toggleRect.top) + 'px';
  } else {
      menu.style.top = toggleRect.bottom + 'px';
  }
  
  if (openLeftward) {
      menu.style.right = (windowWidth - toggleRect.right) + 'px';
  } else {
      menu.style.left = toggleRect.left + 'px';
  }
  
  // Ajuster si le menu dépasse les bords de la fenêtre
  const updatedMenuRect = menu.getBoundingClientRect();
  
  if (updatedMenuRect.right > windowWidth) {
      menu.style.left = '';
      menu.style.right = '5px';
  }
  
  if (updatedMenuRect.left < 0) {
      menu.style.left = '5px';
      menu.style.right = '';
  }
  
  if (updatedMenuRect.bottom > windowHeight) {
      menu.style.top = '';
      menu.style.bottom = '5px';
  }
  
  if (updatedMenuRect.top < 0) {
      menu.style.top = '5px';
      menu.style.bottom = '';
  }
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