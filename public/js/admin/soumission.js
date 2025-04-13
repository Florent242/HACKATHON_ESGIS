// Configuration de base
const API_BASE_URL = "/HACKATHON_ESGIS/public/api"

// Sélecteurs pour les éléments de la page
const SUBMISSION_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  stats: {
    totalSubmissions: ".stat-card:nth-child(1) .number",
    pointsAwarded: ".stat-card:nth-child(2) .number",
    pendingSubmissions: ".stat-card:nth-child(3) .number",
    approvalRate: ".stat-card:nth-child(4) .number",
  },
  submissionsTable: {
    container: "#submissionsTable tbody",
    rows: "#submissionsTable tbody tr",
  },
  submissionDetails: {
    container: ".submission-detail",
  },
  searchInput: ".search-input",
  statusFilter: "select.dropdown-toggle",
  exportButton: ".btn-primary",
}

/**
 * Initialise la page de gestion des soumissions
 */
async function initializeSubmissionPage() {
  try {
    showLoading()

    // Charger toutes les données en parallèle
    await Promise.all([loadSubmissions(), loadSubmissionStats(), loadRecentSubmissions()])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error)
  } finally {
    hideLoading()
  }
}

/**
 * Charge la liste des soumissions
 */
async function loadSubmissions() {
  try {
    const response = await apiRequest("/admin/submissions")

    if (response.success && response.data) {
      updateSubmissionsTable(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des soumissions", error)
  }
}

/**
 * Met à jour le tableau des soumissions
 * @param {Array} submissions - Liste des soumissions
 */
function updateSubmissionsTable(submissions) {
  const container = document.querySelector(SUBMISSION_ELEMENTS.submissionsTable.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucune soumission
  if (!submissions || !submissions.length) {
    container.innerHTML = `
      <tr>
        <td colspan="7">
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
    `
    return
  }

  // Ajouter chaque soumission
  submissions.forEach((submission) => {
    const row = document.createElement("tr")

    // Déterminer le statut et la classe
    let statusClass = "badge-warning"
    let statusText = "En attente"

    if (submission.status === "approved") {
      statusClass = "badge-success"
      statusText = "Approuvé"
    } else if (submission.status === "rejected") {
      statusClass = "badge-danger"
      statusText = "Rejeté"
    }

    // Déterminer les actions disponibles
    let actions = `
      <a href="#" class="action-button" data-action="view" data-id="${submission.id}">
        <i class="fas fa-eye"></i>
      </a>
    `

    if (submission.status === "pending") {
      actions += `
        <a href="#" class="action-button" data-action="approve" data-id="${submission.id}">
          <i class="fas fa-check" style="color: var(--secondary);"></i>
        </a>
        <a href="#" class="action-button" data-action="reject" data-id="${submission.id}">
          <i class="fas fa-times" style="color: var(--danger);"></i>
        </a>
      `
    }

    row.innerHTML = `
      <td>${sanitizeText(submission.username || submission.user_name)}</td>
      <td>${sanitizeText(submission.team_name || "Solo")}</td>
      <td>${sanitizeText(submission.challenge_title)}</td>
      <td><span class="badge badge-warning">${submission.points}</span></td>
      <td>${formatDate(submission.created_at, true)}</td>
      <td><span class="badge ${statusClass}">${statusText}</span></td>
      <td>${actions}</td>
    `

    container.appendChild(row)
  })
}

/**
 * Charge les statistiques des soumissions
 */
async function loadSubmissionStats() {
  try {
    const response = await apiRequest("/admin/submission-stats")

    if (response.success && response.data) {
      updateSubmissionStats(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des statistiques", error)
  }
}

/**
 * Met à jour les statistiques des soumissions
 * @param {Object} stats - Statistiques à afficher
 */
function updateSubmissionStats(stats) {
  if (!stats) return

  // Mettre à jour les compteurs
  const elements = SUBMISSION_ELEMENTS.stats
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key])
    if (element && stats[key] !== undefined) {
      element.textContent = key === "approvalRate" ? `${stats[key]}%` : stats[key]
    }
  })
}

/**
 * Charge les soumissions récentes
 */
async function loadRecentSubmissions() {
  try {
    const response = await apiRequest("/admin/submissions/recent")

    if (response.success && response.data) {
      updateSubmissionDetails(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des soumissions récentes", error)
  }
}

/**
 * Met à jour les détails des soumissions récentes
 * @param {Array} submissions - Liste des soumissions récentes
 */
function updateSubmissionDetails(submissions) {
  const containers = document.querySelectorAll(SUBMISSION_ELEMENTS.submissionDetails.container)

  if (!containers || !containers.length) return

  // Afficher l'état vide si aucune soumission
  if (!submissions || !submissions.length) {
    containers.forEach((container) => {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="empty-state-text">
            <h3>Aucune soumission récente</h3>
            <p>Les soumissions récentes apparaîtront ici.</p>
          </div>
        </div>
      `
    })
    return
  }

  // Mettre à jour chaque conteneur de détails de soumission
  submissions.forEach((submission, index) => {
    if (index < containers.length) {
      const container = containers[index]

      // Déterminer le statut et la classe
      let statusClass = "badge-warning"
      let statusText = "En attente"

      if (submission.status === "approved") {
        statusClass = "badge-success"
        statusText = "Approuvé"
      } else if (submission.status === "rejected") {
        statusClass = "badge-danger"
        statusText = "Rejeté"
      }

      container.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
          <h3>${sanitizeText(submission.challenge_title)}</h3>
          <span class="badge ${statusClass}">${statusText}</span>
        </div>
        <p>Soumis par ${sanitizeText(submission.username || submission.user_name)} (${sanitizeText(submission.team_name || "Solo")})</p>
        <div style="display: flex; align-items: center; margin-top: 10px;">
          <i class="fas fa-star" style="color: #f59e0b; margin-right: 5px;"></i>
          <span>${submission.points} points</span>
          <span style="margin-left: auto;">${formatDate(submission.created_at, true)}</span>
        </div>
      `
    }
  })
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton d'exportation
  const exportButton = document.querySelector(SUBMISSION_ELEMENTS.exportButton)
  if (exportButton) {
    exportButton.addEventListener("click", exportSubmissions)
  }

  // Gestionnaire pour le filtre de statut
  const statusFilter = document.querySelector(SUBMISSION_ELEMENTS.statusFilter)
  if (statusFilter) {
    statusFilter.addEventListener("change", filterSubmissions)
  }

  // Gestionnaire pour les boutons d'action
  document.addEventListener("click", (e) => {
    const actionButton = e.target.closest(".action-button")
    if (actionButton) {
      e.preventDefault()
      const action = actionButton.dataset.action
      const id = actionButton.dataset.id

      switch (action) {
        case "view":
          viewSubmission(id)
          break
        case "approve":
          approveSubmission(id)
          break
        case "reject":
          rejectSubmission(id)
          break
      }
    }
  })

  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(SUBMISSION_ELEMENTS.searchInput)
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch)
  }
}

/**
 * Exporte les soumissions
 */
async function exportSubmissions() {
  try {
    showLoading()

    // Récupérer les soumissions
    const response = await apiRequest("/admin/submissions/export")

    if (response.success && response.data) {
      // Créer un fichier CSV
      const csv = convertToCSV(response.data)

      // Télécharger le fichier
      downloadCSV(csv, "submissions_export.csv")

      // Afficher un message de succès
      showNotification("Soumissions exportées avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de l'exportation des soumissions", error)
  } finally {
    hideLoading()
  }
}

/**
 * Convertit un tableau d'objets en CSV
 * @param {Array} data - Données à convertir
 * @returns {string} - Chaîne CSV
 */
function convertToCSV(data) {
  if (!data || !data.length) return ""

  // Obtenir les en-têtes
  const headers = Object.keys(data[0])

  // Créer la ligne d'en-tête
  let csv = headers.join(",") + "\n"

  // Ajouter les lignes de données
  data.forEach((row) => {
    const values = headers.map((header) => {
      const value = row[header]
      // Échapper les virgules et les guillemets
      return `"${String(value).replace(/"/g, '""')}"`
    })
    csv += values.join(",") + "\n"
  })

  return csv
}

/**
 * Télécharge un fichier CSV
 * @param {string} csv - Contenu CSV
 * @param {string} filename - Nom du fichier
 */
function downloadCSV(csv, filename) {
  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" })
  const url = URL.createObjectURL(blob)

  const link = document.createElement("a")
  link.setAttribute("href", url)
  link.setAttribute("download", filename)
  link.style.visibility = "hidden"

  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

/**
 * Filtre les soumissions en fonction du statut sélectionné
 */
function filterSubmissions() {
  const statusFilter = document.querySelector(SUBMISSION_ELEMENTS.statusFilter)
  const selectedStatus = statusFilter.value

  // Récupérer toutes les lignes du tableau
  const rows = document.querySelectorAll(`${SUBMISSION_ELEMENTS.submissionsTable.rows}`)

  rows.forEach((row) => {
    const statusCell = row.querySelector("td:nth-child(6)")

    if (selectedStatus === "all") {
      row.style.display = ""
    } else {
      const statusText = statusCell.textContent.toLowerCase()

      switch (selectedStatus) {
        case "approved":
          row.style.display = statusText.includes("approuvé") ? "" : "none"
          break
        case "pending":
          row.style.display = statusText.includes("attente") ? "" : "none"
          break
        case "rejected":
          row.style.display = statusText.includes("rejeté") ? "" : "none"
          break
      }
    }
  })
}

/**
 * Affiche les détails d'une soumission
 * @param {string} id - ID de la soumission
 */
async function viewSubmission(id) {
  try {
    showLoading()

    // Récupérer les détails de la soumission
    const response = await apiRequest(`/admin/submissions/${id}`)

    if (response.success && response.data) {
      // Rediriger vers la page de détails ou afficher un modal
      // window.location.href = `/HACKATHON_ESGIS/public/admin/soumissions/view.php?id=${id}`

      // Afficher un modal avec les détails
      showSubmissionModal(response.data)
    }
  } catch (error) {
    handleError("Erreur lors de la récupération de la soumission", error)
  } finally {
    hideLoading()
  }
}

/**
 * Affiche un modal avec les détails d'une soumission
 * @param {Object} submission - Données de la soumission
 */
function showSubmissionModal(submission) {
  // Créer le modal s'il n'existe pas déjà
  let modal = document.querySelector("#submissionModal")
  if (!modal) {
    modal = document.createElement("div")
    modal.id = "submissionModal"
    modal.className = "modal"
    document.body.appendChild(modal)
  }

  // Déterminer le statut et la classe
  let statusClass = "badge-warning"
  let statusText = "En attente"

  if (submission.status === "approved") {
    statusClass = "badge-success"
    statusText = "Approuvé"
  } else if (submission.status === "rejected") {
    statusClass = "badge-danger"
    statusText = "Rejeté"
  }

  // Remplir le modal avec les détails de la soumission
  modal.innerHTML = `
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-file-alt"></i> Détails de la soumission</h2>
        <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3>${sanitizeText(submission.challenge_title)}</h3>
          <span class="badge ${statusClass}">${statusText}</span>
        </div>
        
        <div class="form-group">
          <label>Soumis par</label>
          <div>${sanitizeText(submission.username || submission.user_name)}</div>
        </div>
        
        <div class="form-group">
          <label>Équipe</label>
          <div>${sanitizeText(submission.team_name || "Solo")}</div>
        </div>
        
        <div class="form-group">
          <label>Date de soumission</label>
          <div>${formatDate(submission.created_at)}</div>
        </div>
        
        <div class="form-group">
          <label>Points</label>
          <div>${submission.points}</div>
        </div>
        
        <div class="form-group">
          <label>Description</label>
          <div>${sanitizeText(submission.description || "Aucune description fournie.")}</div>
        </div>
        
        ${
          submission.repository_url
            ? `
          <div class="form-group">
            <label>Dépôt</label>
            <div><a href="${submission.repository_url}" target="_blank">${submission.repository_url}</a></div>
          </div>
        `
            : ""
        }
        
        ${
          submission.file_url
            ? `
          <div class="form-group">
            <label>Fichier</label>
            <div><a href="${submission.file_url}" target="_blank">Télécharger le fichier</a></div>
          </div>
        `
            : ""
        }
        
        ${
          submission.status === "pending"
            ? `
          <div class="form-actions">
            <button class="btn btn-secondary modal-close">Fermer</button>
            <button class="btn btn-danger" data-action="reject" data-id="${submission.id}"><i class="fas fa-times"></i> Rejeter</button>
            <button class="btn btn-primary" data-action="approve" data-id="${submission.id}"><i class="fas fa-check"></i> Approuver</button>
          </div>
        `
            : `
          <div class="form-actions">
            <button class="btn btn-secondary modal-close">Fermer</button>
          </div>
        `
        }
      </div>
    </div>
  `

  // Afficher le modal
  modal.style.display = "flex"
  document.body.style.overflow = "hidden"

  // Gestionnaire pour le bouton de fermeture
  const closeButton = modal.querySelector(".modal-close")
  closeButton.addEventListener("click", () => {
    modal.style.display = "none"
    document.body.style.overflow = ""
  })

  // Gestionnaire pour les boutons d'action
  const actionButtons = modal.querySelectorAll("[data-action]")
  actionButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
      const action = button.dataset.action
      const id = button.dataset.id

      switch (action) {
        case "approve":
          approveSubmission(id)
          modal.style.display = "none"
          document.body.style.overflow = ""
          break
        case "reject":
          rejectSubmission(id)
          modal.style.display = "none"
          document.body.style.overflow = ""
          break
      }
    })
  })

  // Fermer le modal quand on clique en dehors
  modal.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.style.display = "none"
      document.body.style.overflow = ""
    }
  })
}

/**
 * Approuve une soumission
 * @param {string} id - ID de la soumission
 */
async function approveSubmission(id) {
  try {
    showLoading()

    // Envoyer la requête d'approbation
    const response = await apiRequest(`/admin/submissions/${id}/approve`, {
      method: "POST",
    })

    if (response.success) {
      // Recharger les soumissions
      await Promise.all([loadSubmissions(), loadSubmissionStats(), loadRecentSubmissions()])

      // Afficher un message de succès
      showNotification("Soumission approuvée avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de l'approbation de la soumission", error)
  } finally {
    hideLoading()
  }
}

/**
 * Rejette une soumission
 * @param {string} id - ID de la soumission
 */
async function rejectSubmission(id) {
  try {
    showLoading()

    // Envoyer la requête de rejet
    const response = await apiRequest(`/admin/submissions/${id}/reject`, {
      method: "POST",
    })

    if (response.success) {
      // Recharger les soumissions
      await Promise.all([loadSubmissions(), loadSubmissionStats(), loadRecentSubmissions()])

      // Afficher un message de succès
      showNotification("Soumission rejetée avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors du rejet de la soumission", error)
  } finally {
    hideLoading()
  }
}

/**
 * Gère la recherche
 * @param {Event} e - Événement de saisie
 */
function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase()
  const tableId = e.target.dataset.table
  const rows = document.querySelectorAll(`#${tableId} tbody tr`)

  rows.forEach((row) => {
    const text = row.textContent.toLowerCase()
    row.style.display = text.includes(searchTerm) ? "" : "none"
  })
}

/**
 * Affiche le spinner de chargement
 */
function showLoading() {
  // Créer un spinner s'il n'existe pas déjà
  let spinner = document.querySelector("#global-loading-spinner")
  if (!spinner) {
    spinner = document.createElement("div")
    spinner.id = "global-loading-spinner"
    spinner.className = "loading-spinner"
    spinner.innerHTML = '<div class="spinner"></div>'
    document.body.appendChild(spinner)

    // Ajouter les styles nécessaires
    const style = document.createElement("style")
    style.textContent = `
      .loading-spinner {
        display: flex;
        justify-content: center;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 1000;
      }
      .loading-spinner .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      .hidden {
        display: none !important;
      }
    `
    document.head.appendChild(style)
  }

  spinner.classList.remove("hidden")
}

/**
 * Cache le spinner de chargement
 */
function hideLoading() {
    const spinner = document.querySelector("#global-loading-spinner")
    if (spinner) {
      spinner.classList.add("hidden")
    }
  }
  
  /**
   * Affiche une notification
   * @param {string} message - Message à afficher
   * @param {string} type - Type de notification (success, error, warning, info)
   */
  function showNotification(message, type = "info") {
    // Créer un conteneur de notification s'il n'existe pas déjà
    let notifContainer = document.querySelector("#notification-container")
    if (!notifContainer) {
      notifContainer = document.createElement("div")
      notifContainer.id = "notification-container"
      notifContainer.style.position = "fixed"
      notifContainer.style.top = "20px"
      notifContainer.style.right = "20px"
      notifContainer.style.zIndex = "1000"
      document.body.appendChild(notifContainer)
    }
  
    // Créer la notification
    const notification = document.createElement("div")
    notification.className = `notification notification-${type}`
    notification.innerHTML = `
      <div class="notification-icon">
        <i class="fas fa-${getNotificationIcon(type)}"></i>
      </div>
      <div class="notification-content">
        <div class="notification-message">${message}</div>
      </div>
      <button class="notification-close">&times;</button>
    `
  
    // Ajouter les styles nécessaires
    const style = document.createElement("style")
    style.textContent = `
      .notification {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.3s ease-out;
      }
      .notification-success {
        background-color: #d4edda;
        color: #155724;
      }
      .notification-error {
        background-color: #f8d7da;
        color: #721c24;
      }
      .notification-warning {
        background-color: #fff3cd;
        color: #856404;
      }
      .notification-info {
        background-color: #d1ecf1;
        color: #0c5460;
      }
      .notification-icon {
        margin-right: 10px;
      }
      .notification-content {
        flex: 1;
      }
      .notification-close {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1.2rem;
        color: inherit;
      }
      @keyframes slideIn {
        from {
          transform: translateX(100%);
          opacity: 0;
        }
        to {
          transform: translateX(0);
          opacity: 1;
        }
      }
      @keyframes slideOut {
        from {
          transform: translateX(0);
          opacity: 1;
        }
        to {
          transform: translateX(100%);
          opacity: 0;
        }
      }
    `
    document.head.appendChild(style)
  
    // Ajouter la notification au conteneur
    notifContainer.appendChild(notification)
  
    // Gestionnaire pour le bouton de fermeture
    const closeButton = notification.querySelector(".notification-close")
    closeButton.addEventListener("click", () => {
      notification.remove()
    })
  
    // Supprimer la notification après 5 secondes
    setTimeout(() => {
      notification.style.animation = "slideOut 0.3s ease-in"
      notification.style.opacity = "0"
      setTimeout(() => {
        notification.remove()
      }, 300)
    }, 5000)
  }
  
  /**
   * Obtient l'icône appropriée pour un type de notification
   * @param {string} type - Type de notification
   * @returns {string} - Nom de l'icône FontAwesome
   */
  function getNotificationIcon(type) {
    switch (type) {
      case "success":
        return "check-circle"
      case "error":
        return "exclamation-circle"
      case "warning":
        return "exclamation-triangle"
      case "info":
      default:
        return "info-circle"
    }
  }
  
  /**
   * Gère les erreurs
   * @param {string} message - Message d'erreur
   * @param {Error} error - Objet d'erreur
   */
  function handleError(message, error) {
    console.error(message, error)
    showNotification(`${message}: ${error.message || "Erreur inconnue"}`, "error")
  }
  
  /**
   * Effectue une requête API
   * @param {string} endpoint - Point de terminaison de l'API
   * @param {Object} options - Options de la requête
   * @returns {Promise<Object>} - Réponse de l'API
   */
  async function apiRequest(endpoint, options = {}) {
    try {
      const response = await fetch(`${API_BASE_URL}${endpoint}`, {
        ...options,
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          ...options.headers,
        },
        credentials: "include",
      })
  
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || errorData.error || `Erreur API: ${response.status} ${response.statusText}`)
      }
  
      return await response.json()
    } catch (error) {
      handleError("Erreur lors de la requête API", error)
      throw error
    }
  }
  
  /**
   * Nettoie le texte pour prévenir les attaques XSS
   * @param {string} text - Texte à nettoyer
   * @returns {string} - Texte nettoyé
   */
  function sanitizeText(text) {
    if (!text) return ""
    const div = document.createElement("div")
    div.textContent = text
    return div.innerHTML
  }
  
  /**
   * Formate une date
   * @param {string} dateString - Chaîne de date à formater
   * @param {boolean} shortFormat - Format court (jour mois année)
   * @returns {string} - Date formatée
   */
  function formatDate(dateString, shortFormat = false) {
    try {
      const date = new Date(dateString)
      if (isNaN(date.getTime())) return "Date invalide"
  
      if (shortFormat) {
        return date.toLocaleDateString("fr-FR", {
          day: "2-digit",
          month: "long",
          year: "numeric",
        })
      }
  
      // Calculer la différence de temps
      const now = new Date()
      const diffMs = now - date
      const diffSec = Math.floor(diffMs / 1000)
      const diffMin = Math.floor(diffSec / 60)
      const diffHour = Math.floor(diffMin / 60)
      const diffDay = Math.floor(diffHour / 24)
  
      // Afficher un format relatif si c'est récent
      if (diffDay < 1) {
        if (diffHour < 1) {
          if (diffMin < 1) {
            return "À l'instant"
          }
          return `Il y a ${diffMin} minute${diffMin > 1 ? "s" : ""}`
        }
        return `Il y a ${diffHour} heure${diffHour > 1 ? "s" : ""}`
      } else if (diffDay < 7) {
        return `Il y a ${diffDay} jour${diffDay > 1 ? "s" : ""}`
      }
  
      // Sinon, afficher la date complète
      return date.toLocaleDateString("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      })
    } catch (e) {
      console.error("Erreur de formatage de date", e)
      return "Date inconnue"
    }
  }
  
  // Déclaration de la fonction initializeSubmissionPage
  function initializeSubmissionPage() {
    // Ajoutez ici le code d'initialisation de votre page de soumission
    console.log("Page de soumission initialisée !")
  }
  
  // Initialiser la page lorsque le DOM est chargé
  document.addEventListener("DOMContentLoaded", () => {
    // Initialiser la page
    initializeSubmissionPage()
  })
  