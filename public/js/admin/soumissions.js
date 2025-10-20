// Configuration de base
const API_BASE_URL = '/api';
const ITEMS_PER_PAGE = 20;

// État de l'application
let appState = {
  currentPage: 1,
  totalPages: 1,
  filters: {
    status: '',
    hackathon: '',
    difficulty: '',
    search: ''
  },
  sort: {
    field: 'submitted_at',
    direction: 'DESC'
  },
  submissions: [],
  hackathons: []
};

// Sélecteurs pour les éléments de la page
const SUBMISSION_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  stats: {
    total_submissions: ".stat-card:nth-child(1) .number",
    points_awarded: ".stat-card:nth-child(2) .number",
    pending_submissions: ".stat-card:nth-child(3) .number",
    success_rate: ".stat-card:nth-child(4) .number",
  },
  submissionsTable: {
    container: "#submissionsTable",
    rows: "#submissionsTable tr",
    header: "#submissionsTable thead th"
  },
  filters: {
    status: "#statusFilter",
    hackathon: "#hackathonFilter",
    difficulty: "#difficultyFilter",
    search: "#searchInput"
  },
  pagination: {
    prev: "#prevPage",
    next: "#nextPage",
    current: ".pagination .page-item.active"
  },
  exportButton: "#exportBtn",
  refreshButton: "#refreshBtn"
};

/**
 * Initialise la page de gestion des soumissions
 */
async function initializeSubmissionPage() {
  try {
    showLoading();

    // Charger les hackathons d'abord
    await loadHackathons();

    // Puis charger les autres données en parallèle
    await Promise.all([
      loadSubmissions(),
      loadSubmissionStats(),
      loadRecentSubmissions()
    ]);

    // Configurer les gestionnaires d'événements
    setupEventListeners();
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error);
  } finally {
    hideLoading();
  }
}

/**
 * Charge la liste des hackathons pour le filtre
 */
async function loadHackathons() {
  try {
    const response = await apiRequest("/admin/hackathons");
    if (response.success && response.data) {
      appState.hackathons = response.data;
      updateHackathonFilter();
    }
  } catch (error) {
    handleError("Erreur lors du chargement des hackathons", error);
  }
}

/**
 * Met à jour la liste déroulante des hackathons
 */
function updateHackathonFilter() {
  const select = document.querySelector(SUBMISSION_ELEMENTS.filters.hackathon);
  if (!select) return;

  // Sauvegarder la valeur sélectionnée
  const selectedValue = select.value;

  // Vider et reconstruire les options
  select.innerHTML = '<option value="">Tous les hackathons</option>';

  appState.hackathons.forEach(hackathon => {
    const option = document.createElement('option');
    option.value = hackathon.id;
    option.textContent = hackathon.name;
    select.appendChild(option);
  });

  // Restaurer la sélection si elle existe toujours
  if (selectedValue && appState.hackathons.some(h => h.id == selectedValue)) {
    select.value = selectedValue;
  }
}

/**
 * Charge la liste des soumissions avec les filtres actuels
 */
async function loadSubmissions() {
  try {
    showLoading();

    // Construire les paramètres de requête
    const params = new URLSearchParams();

    // Ajouter les filtres
    if (appState.filters.status) params.append('status', appState.filters.status);
    if (appState.filters.hackathon) params.append('hackathon_id', appState.filters.hackathon);
    if (appState.filters.difficulty) params.append('difficulty', appState.filters.difficulty);
    if (appState.filters.search) params.append('search', appState.filters.search);

    // Ajouter la pagination
    params.append('page', appState.currentPage);
    params.append('limit', ITEMS_PER_PAGE);

    // Ajouter le tri
    params.append('sort', appState.sort.field);
    params.append('order', appState.sort.direction);

    const url = `/admin/submissions?${params.toString()}`;
    const response = await apiRequest(url);

    if (response.success && response.data) {
      appState.submissions = response.data.items || [];
      appState.totalPages = response.data.total_pages || 1;
      appState.currentPage = response.data.current_page || 1;

      updateSubmissionsTable(appState.submissions);
      updatePagination();
    }
  } catch (error) {
    handleError("Erreur lors du chargement des soumissions", error);
  } finally {
    hideLoading();
  }
}

/**
 * Met à jour la pagination
 */
function updatePagination() {
  const prevBtn = document.querySelector(SUBMISSION_ELEMENTS.pagination.prev);
  const nextBtn = document.querySelector(SUBMISSION_ELEMENTS.next);
  const currentPage = document.querySelector(SUBMISSION_ELEMENTS.pagination.current);

  if (prevBtn) {
    prevBtn.classList.toggle('disabled', appState.currentPage <= 1);
  }

  if (nextBtn) {
    nextBtn.classList.toggle('disabled', appState.currentPage >= appState.totalPages);
  }

  if (currentPage) {
    currentPage.textContent = appState.currentPage;
  }
}

/**
 * Met à jour le tableau des soumissions
 * @param {Array} submissions - Liste des soumissions
 */
function updateSubmissionsTable(submissions) {
  const container = document.querySelector(SUBMISSION_ELEMENTS.submissionsTable.container);
  if (!container) {
    console.error("Conteneur du tableau des soumissions non trouvé");
    return;
  }
  // Vider le conteneur
  container.innerHTML = "";

  // Afficher l'état vide si aucune soumission
  if (!submissions || !submissions.length) {
    container.innerHTML = `
      <tr>
        <td colspan="8">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="empty-state-text">
              <h3>Aucune soumission</h3>
              <p>Les soumissions apparaîtront ici lorsque les utilisateurs soumettront des solutions.</p>
            </div>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  // Ajouter chaque soumission
  submissions.forEach((submission) => {
    const row = document.createElement("tr");

    // Déterminer le statut et la classe
    let statusClass = "badge-warning";
    let statusText = "En attente";

    if (submission.status === "approved" || submission.status === "validated") {
      statusClass = "badge-success";
      statusText = "Validé";
    } else if (submission.status === "rejected") {
      statusClass = "badge-danger";
      statusText = "Rejeté";
    } else if (submission.status === "completed") {
      statusClass = "badge-info";
      statusText = "Complété";
    } else if (submission.status === "error") {
      statusClass = "badge-danger";
      statusText = "Erreur";
    } else if (submission.status === "running") {
      statusClass = "badge-primary";
      statusText = "En cours";
    }

    // Formater les données
    const executionTime = submission.execution_time_ms ? `${submission.execution_time_ms}ms` : 'N/A';
    const memoryUsed = submission.memory_used_bytes ?
      (submission.memory_used_bytes / 1024).toFixed(2) + ' KB' : 'N/A';
    const successRate = submission.total_tests > 0 ?
      Math.round((submission.tests_passed / submission.total_tests) * 100) : 0;

    // Déterminer les actions disponibles
    let actions = `
      <div class="action-buttons">
        <button class="btn btn-sm btn-icon" data-action="view" data-id="${submission.id}" title="Voir les détails">
          <i class="fas fa-eye"></i>
        </button>
    `;

    if (submission.status === "pending") {
      actions += `
        <button class="btn btn-sm btn-icon text-success" data-action="approve" data-id="${submission.id}" title="Approuver">
          <i class="fas fa-check"></i>
        </button>
        <button class="btn btn-sm btn-icon text-danger" data-action="reject" data-id="${submission.id}" title="Rejeter">
          <i class="fas fa-times"></i>
        </button>
      `;
    } else if (submission.status === "completed") {
      
    }

    actions += `</div>`;

    // Créer la ligne du tableau
    row.innerHTML = `
      <td>
        <div class="d-flex align-items-center">
          <div class="user-avatar">
            ${submission.username ? submission.username.charAt(0).toUpperCase() : 'U'}
          </div>
          <div class="ms-2">
            <div class="fw-bold">${sanitizeText(submission.username || 'Utilisateur inconnu')}</div>
            <div class="text-muted small">${submission.email || ''}</div>
          </div>
        </div>
      </td>
      <td>
        <div class="fw-bold">${sanitizeText(submission.challenge_title || 'Challenge inconnu')}</div>
        <div class="text-muted small">${submission.hackathon_title || ''}</div>
      </td>
      <td>
        <span class="badge bg-secondary">${sanitizeText(submission.difficulty || 'N/A')}</span>
      </td>
      <td>
        <div class="d-flex flex-column">
          <div>${submission.points || submission.total_score || 0} pts</div>
          <div class="progress mt-1" style="height: 4px;">
            <div class="progress-bar bg-success" style="width: ${successRate}%" role="progressbar"></div>
          </div>
          <small class="text-muted">${submission.tests_passed || 0}/${submission.total_tests || 0} tests</small>
        </div>
      </td>
      <td>
        <div>${executionTime}</div>
        <div class="text-muted small">${memoryUsed}</div>
      </td>
      <td><span class="badge ${statusClass}">${statusText}</span></td>
      <td>${formatDate(submission.submitted_at, true)}</td>
      <td>${actions}</td>
    `;

    container.appendChild(row);
  });
}

/**
 * Charge les statistiques des soumissions
 */
async function loadSubmissionStats() {
  try {
    const response = await apiRequest("/admin/submission-stats");

    if (response.success && response.data) {
      updateSubmissionStats(response.data);
    }
  } catch (error) {
    handleError("Erreur lors du chargement des statistiques", error);
  }
}

/**
 * Met à jour les statistiques des soumissions
 * @param {Object} stats - Statistiques à afficher
 */
function updateSubmissionStats(stats) {
  if (!stats) return;

  // Mettre à jour les compteurs
  const elements = SUBMISSION_ELEMENTS.stats;
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key]);
    if (element && stats[key] !== undefined) {
      element.textContent = key.includes("rate") ? `${stats[key]}%` : stats[key];
    }
  });
}

/**
 * Charge les soumissions récentes
 */
async function loadRecentSubmissions() {
  try {
    const response = await apiRequest("/admin/submissions/recent");

    if (response.success && response.data && response.data.items) {
      updateSubmissionDetails(response.data.items);
    } else {
      console.warn("Aucune donnée de soumission récente trouvée");
      updateSubmissionDetails([]);
    }
  } catch (error) {
    console.error("Erreur dans loadRecentSubmissions:", error);
    handleError("Erreur lors du chargement des soumissions récentes", error);
  }
}

/**
 * Met à jour les détails des soumissions récentes
 * @param {Array} submissions - Liste des soumissions récentes
 */
function updateSubmissionDetails(submissions) {
  const container = document.getElementById("recentSubmissions");
  if (!container) {
    console.warn("Conteneur des soumissions récentes non trouvé");
    return;
  }

  try {
    // Afficher l'état vide si aucune soumission
    if (!submissions || !Array.isArray(submissions) || submissions.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
          </div>
          <h3>Aucune soumission récente</h3>
          <p>Les nouvelles soumissions apparaîtront ici.</p>
        </div>`;
      return;
    }

    // Créer le HTML pour les soumissions récentes
    let html = `
      <div class="recent-submissions-list">
        ${submissions.slice(0, 2).map((submission, index) => {
      // Déterminer la classe et le texte du statut
      let statusClass = "badge-warning";
      let statusText = "En attente";

      if (submission.status === "approved" || submission.status === "completed") {
        statusClass = "badge-success";
        statusText = "Terminé";
      } else if (submission.status === "rejected" || submission.status === "failed") {
        statusClass = "badge-danger";
        statusText = "Échoué";
      } else if (submission.status === "pending") {
        statusClass = "badge-info";
        statusText = "En cours";
      }

      // Formater la date
      const submittedDate = submission.submitted_at ? new Date(submission.submitted_at) : new Date();
      const formattedDate = submittedDate.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });

      return `
            <div class="submission-detail">
              <div class="submission-card">
                <div class="submission-header">
                  <span class="submission-id">Soumission #${submission.id || 'N/A'}</span>
                  <span class="badge ${statusClass}">
                    ${statusText}
                  </span>
                </div>
                <div class="submission-body">
                  <div class="submission-info">
                    <div class="info-item">
                      <i class="fas fa-user"></i>
                      <span>${submission.username || 'Utilisateur inconnu'}</span>
                    </div>
                    <div class="info-item">
                      <i class="fas fa-tasks"></i>
                      <span title="${submission.challenge_title || 'Sans titre'}">
                        ${(submission.challenge_title || 'Sans titre').substring(0, 30)}${(submission.challenge_title && submission.challenge_title.length > 30) ? '...' : ''}
                      </span>
                    </div>
                    <div class="info-item">
                      <i class="fas fa-trophy"></i>
                      <span>${submission.difficulty || 'N/A'}</span>
                    </div>
                    <div class="info-item">
                      <i class="fas fa-check-circle"></i>
                      <span>${submission.tests_passed || 0}/${submission.total_tests || 0} tests</span>
                    </div>
                    <div class="info-item">
                      <i class="fas fa-star"></i>
                      <span>Score: ${submission.total_score || 0} pts</span>
                    </div>
                    <div class="info-item">
                      <i class="far fa-clock"></i>
                      <span>${formattedDate}</span>
                    </div>
                  </div>
                  <div class="submission-actions">
                    <button class="btn btn-sm btn-outline-primary view-submission" 
                            data-id="${submission.id}"
                            onclick="viewSubmission('${submission.id}')">
                      <i class="fas fa-eye"></i> Voir
                    </button>
                  </div>
                </div>
              </div>
            </div>`;
    }).join('')}
      </div>`;

    container.innerHTML = html;
  } catch (error) {
    console.error("Erreur lors de la mise à jour des soumissions récentes:", error);
    container.innerHTML = `
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i>
        Une erreur est survenue lors du chargement des soumissions récentes. error: ${error}
      </div>`;
  }
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(SUBMISSION_ELEMENTS.filters.search);
  if (searchInput) {
    searchInput.addEventListener('input', debounce(handleSearch, 300));
  }

  // Gestionnaires pour les filtres
  Object.entries(SUBMISSION_ELEMENTS.filters).forEach(([filterName, selector]) => {
    const element = document.querySelector(selector);
    if (element) {
      element.addEventListener('change', handleFilterChange);
    }
  });

  // Gestionnaire pour le tri des colonnes
  const tableHeaders = document.querySelectorAll(SUBMISSION_ELEMENTS.submissionsTable.header);
  tableHeaders.forEach(header => {
    if (header.dataset.sort) {
      header.style.cursor = 'pointer';
      header.addEventListener('click', () => handleSort(header.dataset.sort));
    }
  });

  // Gestionnaire pour le bouton d'export
  const exportButton = document.querySelector(SUBMISSION_ELEMENTS.exportButton);
  if (exportButton) {
    exportButton.addEventListener('click', exportSubmissions);
  }

  // Gestionnaire pour le bouton de rafraîchissement
  const refreshButton = document.querySelector(SUBMISSION_ELEMENTS.refreshButton);
  if (refreshButton) {
    refreshButton.addEventListener('click', () => loadSubmissions());
  }

  // Gestionnaires pour la pagination
  const prevButton = document.querySelector(SUBMISSION_ELEMENTS.pagination.prev);
  const nextButton = document.querySelector(SUBMISSION_ELEMENTS.next);

  if (prevButton) {
    prevButton.addEventListener('click', (e) => {
      e.preventDefault();
      if (appState.currentPage > 1) {
        appState.currentPage--;
        loadSubmissions();
      }
    });
  }

  if (nextButton) {
    nextButton.addEventListener('click', (e) => {
      e.preventDefault();
      if (appState.currentPage < appState.totalPages) {
        appState.currentPage++;
        loadSubmissions();
      }
    });
  }

  // Gestion des actions sur les soumissions
  document.addEventListener("click", (e) => {
    const actionButton = e.target.closest(".btn");
    if (!actionButton) return;

    e.preventDefault();
    const action = actionButton.dataset.action;
    const id = actionButton.dataset.id;

    switch (action) {
      case "view":
        viewSubmission(id);
        break;
      case "approve":
        approveSubmission(id);
        break;
      case "reject":
        rejectSubmission(id);
        break;
    }
  });
}

/**
 * Gère le changement de filtre
 */
function handleFilterChange(e) {
  const filterName = e.target.id.replace('Filter', '');
  appState.filters[filterName] = e.target.value;
  appState.currentPage = 1; // Réinitialiser à la première page
  loadSubmissions();
}

/**
 * Gère le tri des colonnes
 */
function handleSort(field) {
  // Changer la direction si on clique sur le même champ
  if (appState.sort.field === field) {
    appState.sort.direction = appState.sort.direction === 'ASC' ? 'DESC' : 'ASC';
  } else {
    // Sinon, trier par défaut en ordre croissant
    appState.sort.field = field;
    appState.sort.direction = 'ASC';
  }

  // Mettre à jour les icônes de tri
  updateSortIcons();

  // Recharger les données
  loadSubmissions();
}

/**
 * Met à jour les icônes de tri dans l'en-tête du tableau
 */
function updateSortIcons() {
  const headers = document.querySelectorAll(SUBMISSION_ELEMENTS.submissionsTable.header);

  headers.forEach(header => {
    const icon = header.querySelector('.sort-icon');
    if (header.dataset.sort === appState.sort.field) {
      header.classList.add('sorting-active');
      if (icon) {
        icon.className = `fas fa-sort-${appState.sort.direction === 'ASC' ? 'up' : 'down'}`;
      } else {
        const sortIcon = document.createElement('i');
        sortIcon.className = `fas fa-sort-${appState.sort.direction === 'ASC' ? 'up' : 'down'} ms-1`;
        header.appendChild(sortIcon);
      }
    } else {
      header.classList.remove('sorting-active');
      if (icon) {
        icon.remove();
      }
    }
  });
}

/**
 * Affiche les détails d'une soumission
 * @param {string} id - ID de la soumission
 */
async function viewSubmission(id) {
  try {
    const response = await apiRequest(`/admin/submissions/${id}`);

    if (response.success && response.data) {
      // Prendre le premier élément du tableau items s'il existe
      const submissionData = response.data.items && response.data.items.length > 0
        ? response.data.items[0]
        : response.data; // Fallback si la structure change
      showSubmissionModal(submissionData);
    }

  } catch (error) {
    handleError("Erreur lors du chargement des détails de la soumission", error);
  }
}

/**
 * Affiche un modal avec les détails d'une soumission
 * @param {Object} submission - Données de la soumission
 */
function showSubmissionModal(submission) {
  if (!submission) {
    console.error('Aucune donnée de soumission fournie');
    return;
  }

  // Formater la date
  const formattedDate = submission.submitted_at
    ? new Date(submission.submitted_at).toLocaleString('fr-FR')
    : 'Date inconnue';

  // Créer le contenu du modal
  const modalContent = `
    <div class="modal-header">
      <h2>Détails de la soumission #${submission.id || ''}</h2>
      <button class="close-modal" aria-label="Fermer">&times;</button>
    </div>
    <div class="modal-body">
      <div class="detail-grid">
        <!-- Informations utilisateur -->
        <div class="detail-section">
          <h3>Informations participant</h3>
          <div class="detail-row">
            <span class="detail-label">Nom d'utilisateur:</span>
            <span class="detail-value">${sanitizeText(submission.username || 'Non spécifié')}</span>
          </div>
          ${submission.team_name ? `
          <div class="detail-row">
            <span class="detail-label">Équipe:</span>
            <span class="detail-value">${sanitizeText(submission.team_name)}</span>
          </div>` : ''}
          <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value">${sanitizeText(submission.email || 'Non spécifié')}</span>
          </div>
        </div>

        <!-- Détails du défi -->
        <div class="detail-section">
          <h3>Détails du défi</h3>
          <div class="detail-row">
            <span class="detail-label">Titre:</span>
            <span class="detail-value">${sanitizeText(submission.challenge_title || 'Non spécifié')}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Difficulté:</span>
            <span class="detail-value">${getDifficultyBadge(submission.difficulty)}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Hackathon:</span>
            <span class="detail-value">${sanitizeText(submission.hackathon_title || 'Non spécifié')}</span>
          </div>
        </div>

        <!-- Résultats -->
        <div class="detail-section">
          <h3>Résultats</h3>
          <div class="detail-row">
            <span class="detail-label">Statut:</span>
            <span class="detail-value">${getStatusBadge(submission.status)}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Score:</span>
            <span class="detail-value">${submission.total_score || '0'} points</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Tests réussis:</span>
            <span class="detail-value">
              ${submission.tests_passed || 0}/${submission.total_tests || 0}
              ${submission.total_tests > 0 ?
      `(${Math.round((submission.tests_passed / submission.total_tests) * 100)}%)` : ''}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Soumis le:</span>
            <span class="detail-value">${formattedDate}</span>
          </div>
          ${submission.execution_time_ms !== null ? `
          <div class="detail-row">
            <span class="detail-label">Temps d'exécution:</span>
            <span class="detail-value">${submission.execution_time_ms} ms</span>
          </div>` : ''}
          ${submission.memory_used_bytes !== null ? `
          <div class="detail-row">
            <span class="detail-label">Mémoire utilisée:</span>
            <span class="detail-value">${formatBytes(submission.memory_used_bytes)}</span>
          </div>` : ''}
        </div>
      </div>

      ${submission.solution ? `
      <div class="solution-section">
        <h3>Solution soumise</h3>
        <div class="code-block">
          <pre><code class="language-python">${sanitizeText(submission.solution)}</code></pre>
        </div>
      </div>
      ` : ''}
    </div>
  `;

  // Créer et afficher le modal
  const modal = document.createElement('div');
  modal.className = 'modal';
  modal.innerHTML = `
    <div class="modal-content">
      ${modalContent}
    </div>
  `;

  document.body.appendChild(modal);
  document.body.classList.add('modal-open');
  openModal(modal);

  // Gérer la fermeture du modal
  const closeButton = modal.querySelector('.close-modal');
  if (closeButton) {
    closeButton.addEventListener('click', () => {
      document.body.removeChild(modal);
      document.body.classList.remove('modal-open');
    });
  }

  // Mettre en surbrillance la syntaxe du code
  if (typeof Prism !== 'undefined') {
    Prism.highlightAllUnder(modal);
  }
}

// Fonctions utilitaires
function getStatusBadge(status) {
  const statusMap = {
    'pending': { text: 'En attente', class: 'badge-warning' },
    'completed': { text: 'Complété', class: 'badge-success' },
    'approved': { text: 'Approuvé', class: 'badge-success' },
    'rejected': { text: 'Rejeté', class: 'badge-danger' },
    'error': { text: 'Erreur', class: 'badge-danger' }
  };
  const statusInfo = statusMap[status] || { text: status, class: 'badge-secondary' };
  return `<span class="badge ${statusInfo.class}">${statusInfo.text}</span>`;
}

function getDifficultyBadge(difficulty) {
  const difficultyMap = {
    'easy': { text: 'Facile', class: 'badge-success' },
    'medium': { text: 'Moyen', class: 'badge-primary' },
    'hard': { text: 'Difficile', class: 'badge-warning' },
    'expert': { text: 'Expert', class: 'badge-danger' }
  };
  const diffInfo = difficultyMap[difficulty?.toLowerCase()] || { text: difficulty || 'N/A', class: 'badge-secondary' };
  return `<span class="badge ${diffInfo.class}">${diffInfo.text}</span>`;
}

function formatBytes(bytes, decimals = 2) {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Approuve une soumission
 * @param {string} id - ID de la soumission
 */
async function approveSubmission(id) {
  try {
    const response = await apiRequest(`/admin/submissions/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        status: 'approved',
        csrf_token: document.querySelector('meta[name="csrf_token"]').content
      })
    });

    if (response.success) {
      showNotification('Soumission approuvée avec succès', 'success');
      // Recharger les données
      await Promise.all([loadSubmissions(), loadSubmissionStats(), loadRecentSubmissions()]);
    }
  } catch (error) {
    handleError("Erreur lors de l'approbation de la soumission", error);
  }
}

/**
 * Rejette une soumission
 * @param {string} id - ID de la soumission
 */
async function rejectSubmission(id) {
  try {
    const response = await apiRequest(`/admin/submissions/${id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        status: 'rejected',
        csrf_token: document.querySelector('meta[name="csrf_token"]').content
      })
    });

    if (response.success) {
      showNotification('Soumission rejetée avec succès', 'success');
      // Recharger les données
      await Promise.all([loadSubmissions(), loadSubmissionStats(), loadRecentSubmissions()]);
    }
  } catch (error) {
    handleError("Erreur lors du rejet de la soumission", error);
  }
}

/**
 * Exporte les soumissions au format CSV
 */
async function exportSubmissions() {
  try {
    const response = await apiRequest('/admin/submissions/export');

    if (response.success && response.data) {
      // Créer un fichier CSV et le télécharger
      const csvContent = convertToCSV(response.data);
      downloadCSV(csvContent, 'soumissions.csv');
    }
  } catch (error) {
    handleError("Erreur lors de l'export des soumissions", error);
  }
}

/**
 * Convertit un tableau d'objets en CSV
 * @param {Array} data - Données à convertir
 * @returns {string} - Chaîne CSV
 */
function convertToCSV(data) {
  if (!data || !data.length) return '';

  // Extraire les en-têtes
  const headers = Object.keys(data[0]);

  // Créer les lignes CSV
  const rows = data.map(item =>
    headers.map(header => {
      // Échapper les guillemets et les virgules
      const value = String(item[header] || '').replace(/"/g, '""');
      return `"${value}"`;
    }).join(',')
  );

  // Ajouter l'en-tête
  rows.unshift(headers.join(','));

  return rows.join('\n');
}

/**
 * Télécharge un fichier CSV
 * @param {string} csv - Contenu CSV
 * @param {string} filename - Nom du fichier
 */
function downloadCSV(csv, filename) {
  const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');

  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

/**
 * Gère la recherche
 * @param {Event} e - Événement de saisie
 */
function handleSearch(e) {
  appState.filters.search = e.target.value.trim();
  appState.currentPage = 1; // Réinitialiser à la première page
  loadSubmissions();
}

/**
 * Affiche le spinner de chargement
 */
function showLoading() {
  const spinner = document.querySelector(SUBMISSION_ELEMENTS.loadingSpinner);
  if (spinner) {
    spinner.style.display = 'block';
  }
}

/**
 * Cache le spinner de chargement
 */
function hideLoading() {
  const spinner = document.querySelector(SUBMISSION_ELEMENTS.loadingSpinner);
  if (spinner) {
    spinner.style.display = 'none';
  }
}

/**
 * Affiche une notification
 * @param {string} message - Message à afficher
 * @param {string} type - Type de notification (success, error, warning, info)
 */
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <span class="notification-message">${message}</span>
      <button class="notification-close">&times;</button>
    </div>
  `;

  document.body.appendChild(notification);

  // Fermer la notification après 5 secondes
  setTimeout(() => {
    notification.classList.add('fade-out');
    setTimeout(() => {
      if (notification.parentNode) {
        document.body.removeChild(notification);
      }
    }, 300);
  }, 5000);

  // Fermer la notification au clic sur le bouton
  const closeButton = notification.querySelector('.notification-close');
  if (closeButton) {
    closeButton.addEventListener('click', () => {
      notification.classList.add('fade-out');
      setTimeout(() => {
        if (notification.parentNode) {
          document.body.removeChild(notification);
        }
      }, 300);
    });
  }
}

/**
 * Gère les erreurs
 * @param {string} message - Message d'erreur
 * @param {Error} error - Objet d'erreur
 */
function handleError(message, error) {
  console.error(message, error);
  showNotification(`${message}: ${error.message}`, 'error');
}

/**
 * Effectue une requête API
 * @param {string} endpoint - Point de terminaison de l'API
 * @param {Object} options - Options de la requête
 * @returns {Promise<Object>} - Réponse de l'API
 */
// async function apiRequest(endpoint, options = {}) {
//   const defaultOptions = {
//     method: 'GET',
//     headers: {
//       'Content-Type': 'application/json',
//       'X-Requested-With': 'XMLHttpRequest'
//     },
//     credentials: 'same-origin'
//   };

//   const config = { ...defaultOptions, ...options };

//   // Ajouter le token d'authentification s'il existe
//   const token = localStorage.getItem('token') || getCookie('token');
//   if (token) {
//     config.headers['Authorization'] = `Bearer ${token}`;
//   }

//   // Gérer le corps de la requête
//   if (config.body && typeof config.body === 'object') {
//     config.body = JSON.stringify(config.body);
//   }

//   try {
//     const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
//     const data = await response.json();

//     if (!response.ok) {
//       throw new Error(data.error || 'Une erreur est survenue');
//     }

//     return data;
//   } catch (error) {
//     console.error('Erreur API:', error);
//     throw error;
//   }
// }

/**
 * Nettoie le texte pour prévenir les attaques XSS
 * @param {string} text - Texte à nettoyer
 * @returns {string} - Texte nettoyé
 */
function sanitizeText(text) {
  if (!text) return '';

  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Formate une date
 * @param {string} dateString - Chaîne de date à formater
 * @param {boolean} shortFormat - Format court (jour mois année)
 * @returns {string} - Date formatée
 */
function formatDate(dateString, shortFormat = false) {
  if (!dateString) return 'N/A';

  const date = new Date(dateString);

  if (isNaN(date.getTime())) return 'Date invalide';

  if (shortFormat) {
    return date.toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  return date.toLocaleDateString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

/**
 * Fonction utilitaire pour récupérer un cookie
 * @param {string} name - Nom du cookie
 * @returns {string|null} - Valeur du cookie ou null
 */
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  return null;
}

/**
 * Fonction utilitaire pour limiter la fréquence d'exécution d'une fonction
 * @param {Function} func - Fonction à exécuter
 * @param {number} wait - Délai d'attente en millisecondes
 * @returns {Function} - Fonction avec debounce
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

// Initialiser la page lorsque le DOM est chargé
document.addEventListener('DOMContentLoaded', initializeSubmissionPage);
