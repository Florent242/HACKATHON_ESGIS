// Configuration de base
const API_BASE_URL = "/api"

// Sélecteurs pour les éléments de la page
const CHALLENGE_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  stats: {
    totalChallenges: ".stat-card:nth-child(1) .number",
    activeChallenges: ".stat-card:nth-child(2) .number",
    completedChallenges: ".stat-card:nth-child(3) .number",
    averagePoints: ".stat-card:nth-child(4) .number",
  },
  challengesTable: {
    container: "#challengesTable tbody",
    rows: "#challengesTable tbody tr",
  },
  pointsTable: {
    container: "table:nth-of-type(2) tbody",
    rows: "table:nth-of-type(2) tbody tr",
  },
  newChallengeModal: "#newChallengeModal",
  searchInput: ".search-input",
}

/**
 * Initialise la page de gestion des challenges
 */
async function initializeChallengePage() {
  try {
    showLoading()

    // Charger toutes les données en parallèle
    await Promise.all([loadChallenges(), loadChallengeStats(), loadChallengePoints()])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error)
  } finally {
    hideLoading()
  }
}

/**
 * Charge la liste des challenges
 */
async function loadChallenges() {
  try {
    const response = await apiRequest("/admin/challenges")

    if (response.success && response.data) {
      updateChallengesTable(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des challenges", error)
  }
}

/**
 * Met à jour le tableau des challenges
 * @param {Array} challenges - Liste des challenges
 */
function updateChallengesTable(challenges) {
  const container = document.querySelector(CHALLENGE_ELEMENTS.challengesTable.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucun challenge
  if (!challenges || !challenges.length) {
    container.innerHTML = `
      <tr>
        <td colspan="5">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="fas fa-trophy"></i>
            </div>
            <div class="empty-state-text">
              <h3>Aucun challenge</h3>
              <p>Créez votre premier challenge en cliquant sur le bouton "Nouveau Challenge".</p>
            </div>
          </div>
        </td>
      </tr>
    `
    return
  }

  // Ajouter chaque challenge
  challenges.forEach((challenge) => {
    const row = document.createElement("tr")

    // Déterminer la difficulté et l'icône
    let difficultyClass = "badge-primary"
    let difficultyText = "Intermédiaire"

    switch (challenge.difficulty) {
      case "easy":
        difficultyClass = "badge-success"
        difficultyText = "Débutant"
        break
      case "medium":
        difficultyClass = "badge-primary"
        difficultyText = "Intermédiaire"
        break
      case "hard":
        difficultyClass = "badge-danger"
        difficultyText = "Avancé"
        break
      case "expert":
        difficultyClass = "badge-dark"
        difficultyText = "Expert"
        break
    }

    // Déterminer le statut et l'icône
    let statusClass = "badge-primary"
    let statusIcon = "play-circle"
    let statusText = "En cours"

    if (challenge.status === "completed") {
      statusClass = "badge-success"
      statusIcon = "check-circle"
      statusText = "Terminé"
    } else if (challenge.status === "upcoming") {
      statusClass = "badge-info"
      statusIcon = "hourglass-start"
      statusText = "À venir"
    }

    row.innerHTML = `
      <td>${sanitizeText(challenge.title)}</td>
      <td><span class="badge ${difficultyClass}"><i class="fas fa-fire"></i> ${difficultyText}</span></td>
      <td>${challenge.participants_count || 0}</td>
      <td><span class="badge ${statusClass}"><i class="fas fa-${statusIcon}"></i> ${statusText}</span></td>
      <td>
        <div class="dropdown">
          <button class="dropdown-toggle">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="dropdown-menu">
            <a href="#" class="dropdown-item action-button" data-action="edit" data-id="${challenge.id}"><i class="fas fa-edit"></i> Modifier</a>
            <a href="#" class="dropdown-item action-button" data-action="view" data-id="${challenge.id}"><i class="fas fa-eye"></i> Voir détails</a>
            <a href="#" class="dropdown-item action-button" data-action="delete" data-id="${challenge.id}"><i class="fas fa-trash"></i> Supprimer</a>
          </div>
        </div>
      </td>
    `

    container.appendChild(row)
  })

  // Initialiser les dropdowns
  initializeDropdowns()
}

/**
 * Charge les statistiques des challenges
 */
async function loadChallengeStats() {
  try {
    const response = await apiRequest("/admin/challenge-stats")

    if (response.success && response.data) {
      updateChallengeStats(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des statistiques", error)
  }
}

/**
 * Met à jour les statistiques des challenges
 * @param {Object} stats - Statistiques à afficher
 */
function updateChallengeStats(stats) {
  if (!stats) return

  // Mettre à jour les compteurs
  const elements = CHALLENGE_ELEMENTS.stats
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key])
    if (element && stats[key] !== undefined) {
      element.textContent = stats[key]
    }
  })
}

/**
 * Charge les points des challenges
 */
async function loadChallengePoints() {
  try {
    const response = await apiRequest("/admin/submission-stats")

    if (response.success && response.data) {
      updateChallengePointsTable(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des points", error)
  }
}

/**
 * Met à jour le tableau des points des challenges
 * @param {Array} points - Liste des points
 */
function updateChallengePointsTable(points) {
  const container = document.querySelector(CHALLENGE_ELEMENTS.pointsTable.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucun point
  if (!points || !points.length) {
    container.innerHTML = `
      <tr>
        <td colspan="4">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="empty-state-text">
              <h3>Aucun point attribué</h3>
              <p>Les points attribués aux défis apparaîtront ici.</p>
            </div>
          </div>
        </td>
      </tr>
    `
    return
  }

  // Ajouter chaque point
  points.forEach((point) => {
    const row = document.createElement("tr")

    row.innerHTML = `
      <td>${sanitizeText(point.username)}</td>
      <td>${sanitizeText(point.challenge_title)}</td>
      <td><span class="badge badge-warning"><i class="fas fa-star"></i> ${point.points}</span></td>
      <td>${formatDate(point.created_at)}</td>
    `

    container.appendChild(row)
  })
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton "Nouveau Challenge"
  const newChallengeButton = document.querySelector('[data-modal="newChallengeModal"]')
  if (newChallengeButton) {
    newChallengeButton.addEventListener("click", () => {
      openModal("newChallengeModal")
    })
  }

  // Gestionnaire pour le formulaire de nouveau challenge
  const newChallengeForm = document.querySelector("#newChallengeModal form")
  if (newChallengeForm) {
    newChallengeForm.addEventListener("submit", handleNewChallengeSubmit)
  }

  // Gestionnaire pour les boutons d'action
  document.addEventListener("click", (e) => {
    const actionButton = e.target.closest(".action-button")
    if (actionButton) {
      e.preventDefault()
      const action = actionButton.dataset.action
      const id = actionButton.dataset.id

      switch (action) {
        case "edit":
          editChallenge(id)
          break
        case "view":
          viewChallenge(id)
          break
        case "delete":
          deleteChallenge(id)
          break
      }
    }
  })

  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(CHALLENGE_ELEMENTS.searchInput)
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch)
  }

  // Initialiser les modals
  initializeModals()
}

/**
 * Gère la soumission du formulaire de nouveau challenge
 * @param {Event} e - Événement de soumission
 */
async function handleNewChallengeSubmit(e) {
  e.preventDefault()

  try {
    showLoading()

    // Récupérer les données du formulaire
    const formData = new FormData(e.target)
    const challengeData = {
      title: formData.get("challengeTitle"),
      category: formData.get("challengeCategory"),
      difficulty: formData.get("challengeDifficulty"),
      points: formData.get("challengePoints"),
      duration: formData.get("challengeDuration"),
      start_date: formData.get("challengeStartDate"),
      end_date: formData.get("challengeEndDate"),
      skills: formData.get("challengeSkills"),
      description: formData.get("challengeDescription"),
      criteria: formData.get("challengeCriteria"),
    }

    // Envoyer les données à l'API
    const response = await apiRequest("/admin/challenges", {
      method: "POST",
      body: JSON.stringify(challengeData),
    })

    if (response.success) {
      // Fermer le modal
      closeModal("newChallengeModal")

      // Recharger les challenges
      await loadChallenges()

      // Afficher un message de succès
      showNotification("Challenge créé avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de la création du challenge", error)
  } finally {
    hideLoading()
  }
}

/**
 * Édite un challenge
 * @param {string} id - ID du challenge
 */
async function editChallenge(id) {
  try {
    showLoading()

    // Récupérer les données du challenge
    const response = await apiRequest(`/admin/challenges/${id}`)

    if (response.success && response.data) {
      // Remplir le formulaire
      const challenge = response.data
      document.querySelector("#challengeTitle").value = challenge.title
      document.querySelector("#challengeCategory").value = challenge.category
      document.querySelector("#challengeDifficulty").value = challenge.difficulty
      document.querySelector("#challengePoints").value = challenge.points
      document.querySelector("#challengeDuration").value = challenge.duration
      document.querySelector("#challengeStartDate").value = formatDateForInput(challenge.start_date)
      document.querySelector("#challengeEndDate").value = formatDateForInput(challenge.end_date)
      document.querySelector("#challengeSkills").value = challenge.skills
      document.querySelector("#challengeDescription").value = challenge.description
      document.querySelector("#challengeCriteria").value = challenge.criteria

      // Modifier le titre du modal
      const modalTitle = document.querySelector("#newChallengeModal .modal-header h2")
      if (modalTitle) {
        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Modifier le Challenge'
      }

      // Modifier le bouton de soumission
      const submitButton = document.querySelector("#newChallengeModal .btn-primary")
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-save"></i> Mettre à jour'
        submitButton.dataset.id = id
      }

      // Ouvrir le modal
      openModal("newChallengeModal")
    }
  } catch (error) {
    handleError("Erreur lors de la récupération du challenge", error)
  } finally {
    hideLoading()
  }
}

/**
 * Affiche les détails d'un challenge
 * @param {string} id - ID du challenge
 */
async function viewChallenge(id) {
  try {
    showLoading()

    // Rediriger vers la page de détails
    window.location.href = `/admin/challenges/view.php?id=${id}`
  } catch (error) {
    handleError("Erreur lors de la récupération du challenge", error)
  } finally {
    hideLoading()
  }
}

/**
 * Supprime un challenge
 * @param {string} id - ID du challenge
 */
async function deleteChallenge(id) {
  if (!confirm("Êtes-vous sûr de vouloir supprimer ce challenge ?")) {
    return
  }

  try {
    showLoading()

    // Envoyer la requête de suppression
    const response = await apiRequest(`/admin/challenges/${id}`, {
      method: "DELETE",
    })

    if (response.success) {
      // Recharger les challenges
      await loadChallenges()

      // Afficher un message de succès
      showNotification("Challenge supprimé avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de la suppression du challenge", error)
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
 * Initialise les modals
 */
function initializeModals() {
  // Gestionnaire pour les boutons d'ouverture de modal
  document.querySelectorAll("[data-modal]").forEach((button) => {
    button.addEventListener("click", () => {
      const modalId = button.dataset.modal
      openModal(modalId)
    })
  })

  // Gestionnaire pour les boutons de fermeture de modal
  document.querySelectorAll(".modal-close").forEach((button) => {
    button.addEventListener("click", () => {
      const modal = button.closest(".modal")
      if (modal) {
        closeModal(modal.id)
      }
    })
  })

  // Fermer le modal quand on clique en dehors
  document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        closeModal(modal.id)
      }
    })
  })
}

/**
 * Initialise les dropdowns
 */
function initializeDropdowns() {
  document.querySelectorAll(".dropdown-toggle").forEach((button) => {
    button.addEventListener("click", (e) => {
      e.preventDefault()
      e.stopPropagation()
      const dropdown = button.nextElementSibling
      dropdown.classList.toggle("show")
    })
  })

  // Fermer les dropdowns quand on clique ailleurs
  document.addEventListener("click", (e) => {
    if (!e.target.matches(".dropdown-toggle")) {
      document.querySelectorAll(".dropdown-menu.show").forEach((dropdown) => {
        dropdown.classList.remove("show")
      })
    }
  })
}

/**
 * Ouvre un modal
 * @param {string} modalId - ID du modal
 */
function openModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.style.display = "flex"
    document.body.style.overflow = "hidden"
  }
}

/**
 * Ferme un modal
 * @param {string} modalId - ID du modal
 */
function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  if (modal) {
    modal.style.display = "none"
    document.body.style.overflow = ""
  }
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

/**
 * Formate une date pour un champ input
 * @param {string} dateString - Chaîne de date à formater
 * @returns {string} - Date formatée (YYYY-MM-DD)
 */
function formatDateForInput(dateString) {
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ""

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, "0")
    const day = String(date.getDate()).padStart(2, "0")

    return `${year}-${month}-${day}`
  } catch (e) {
    console.error("Erreur de formatage de date pour input", e)
    return ""
  }
}

// Initialiser la page lorsque le DOM est chargé
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser la page
  initializeChallengePage()

  // Initialiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
})
