// Configuration de base
const API_BASE_URL = "/api"

// Sélecteurs pour les éléments de la page
const HACKATHON_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  hackathonsTable: {
    container: "#hackathonsTable tbody",
    rows: "#hackathonsTable tbody tr",
  },
  activityFeed: {
    container: ".activity-feed",
    items: ".activity-item",
  },
  newHackathonModal: "#newHackathonModal",
  searchInput: ".search-input",
}

/**
 * Initialise la page de gestion des hackathons
 */
async function initializeHackathonPage() {
  try {
    showLoading()

    // Charger toutes les données en parallèle
    await Promise.all([loadHackathons(), loadHackathonActivity()])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error)
  } finally {
    hideLoading()
  }
}

/**
 * Charge la liste des hackathons
 */
async function loadHackathons() {
  try {
    const response = await apiRequest("/admin/hackathons")

    if (response.success && response.data) {
      updateHackathonsTable(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des hackathons", error)
  }
}

/**
 * Met à jour le tableau des hackathons
 * @param {Array} hackathons - Liste des hackathons
 */
function updateHackathonsTable(hackathons) {
  const container = document.querySelector(HACKATHON_ELEMENTS.hackathonsTable.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucun hackathon
  if (!hackathons || !hackathons.length) {
    container.innerHTML = `
      <tr>
        <td colspan="5">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="fas fa-laptop-code"></i>
            </div>
            <div class="empty-state-text">
              <h3>Aucun hackathon</h3>
              <p>Créez votre premier hackathon en cliquant sur le bouton "Nouveau Hackathon".</p>
            </div>
          </div>
        </td>
      </tr>
    `
    return
  }

  // Ajouter chaque hackathon
  hackathons.forEach((hackathon) => {
    const row = document.createElement("tr")

    // Déterminer le statut et l'icône
    let statusClass = "badge-primary"
    let statusIcon = "calendar-alt"
    let statusText = "À venir"

    const now = new Date()
    const startDate = new Date(hackathon.start_date)
    const endDate = new Date(hackathon.end_date)

    if (now > endDate) {
      statusClass = "badge-success"
      statusIcon = "check-circle"
      statusText = "Terminé"
    } else if (now >= startDate && now <= endDate) {
      statusClass = "badge-warning"
      statusIcon = "play-circle"
      statusText = "En cours"
    }

    row.innerHTML = `
      <td>${sanitizeText(hackathon.name || hackathon.title)}</td>
      <td>${formatDate(hackathon.start_date, true)}</td>
      <td>${hackathon.participants_count || 0}</td>
      <td><span class="badge ${statusClass}"><i class="fas fa-${statusIcon}"></i> ${statusText}</span></td>
      <td>
        <div class="dropdown">
          <button class="dropdown-toggle">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="dropdown-menu">
            <a href="#" class="dropdown-item action-button" data-action="edit" data-id="${hackathon.id}"><i class="fas fa-edit"></i> Modifier</a>
            <a href="#" class="dropdown-item action-button" data-action="view" data-id="${hackathon.id}"><i class="fas fa-eye"></i> Voir détails</a>
            <div class="dropdown-divider"></div>
            <a href="#" class="dropdown-item action-button" data-action="delete" data-id="${hackathon.id}"><i class="fas fa-trash"></i> Supprimer</a>
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
 * Charge les activités récentes des hackathons
 */
async function loadHackathonActivity() {
  try {
    const response = await apiRequest("/admin/activity")

    if (response.success && response.data) {
      // Filtrer pour obtenir uniquement les activités liées aux hackathons
      const hackathonActivities = response.data
        .filter((activity) => {
          const type = activity.type || activity.action_type || ""
          return type.includes("hackathon") || type.includes("team") || type.includes("user")
        })
        .slice(0, 5) // Limiter à 5 activités

      updateActivityFeed(hackathonActivities)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des activités", error)
  }
}

/**
 * Met à jour le flux d'activité
 * @param {Array} activities - Liste des activités
 */
function updateActivityFeed(activities) {
  const container = document.querySelector(HACKATHON_ELEMENTS.activityFeed.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucune activité
  if (!activities || !activities.length) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-history"></i>
        </div>
        <div class="empty-state-text">
          <h3>Aucune activité récente</h3>
          <p>Les activités récentes apparaîtront ici.</p>
        </div>
      </div>
    `
    return
  }

  // Ajouter chaque activité
  activities.forEach((activity) => {
    const activityElement = createActivityElement(activity)
    container.appendChild(activityElement)
  })
}

/**
 * Crée un élément d'activité
 * @param {Object} activity - Données de l'activité
 * @returns {HTMLElement} - Élément DOM de l'activité
 */
function createActivityElement(activity) {
  const div = document.createElement("div")
  div.className = "activity-item"

  // Déterminer l'icône et la classe en fonction du type d'activité
  let iconClass = "background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;"
  let iconName = "activity"

  // Adapter en fonction de la structure de données renvoyée par l'API
  const activityType = activity.type || activity.action_type || "default"

  switch (activityType) {
    case "hackathon":
    case "create_hackathon":
    case "update_hackathon":
      iconName = "calendar-alt"
      iconClass = "background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;"
      break
    case "challenge":
    case "create_challenge":
    case "solve_challenge":
      iconName = "trophy"
      iconClass = "background-color: rgba(16, 185, 129, 0.2); color: #10b981;"
      break
    case "user":
    case "register":
    case "login":
      iconName = "user"
      iconClass = "background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;"
      break
    case "team":
    case "create_team":
    case "join_team":
      iconName = "users"
      iconClass = "background-color: rgba(245, 158, 11, 0.2); color: #f59e0b;"
      break
    case "submission":
    case "submit_solution":
      iconName = "file-code"
      iconClass = "background-color: rgba(16, 185, 129, 0.2); color: #10b981;"
      break
  }

  // Formater la date
  const date =
    activity.timestamp || activity.created_at ? formatDate(activity.timestamp || activity.created_at) : "Récemment"

  // Déterminer le texte de l'activité
  const activityText = activity.description || activity.action || "Activité inconnue"
  const username = activity.username || activity.user_name || ""

  div.innerHTML = `
    <div class="activity-icon" style="${iconClass}">
      <i class="fas fa-${iconName}"></i>
    </div>
    <div class="activity-content">
      <div class="activity-title">${sanitizeText(username)}</div>
      <div class="activity-subtitle">${sanitizeText(activityText)}</div>
    </div>
    <div class="activity-time">
      <i class="fas fa-clock"></i> ${date}
    </div>
  `

  return div
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton "Nouveau Hackathon"
  const newHackathonButton = document.querySelector('[data-modal="newHackathonModal"]')
  if (newHackathonButton) {
    newHackathonButton.addEventListener("click", () => {
      openModal("newHackathonModal")
    })
  }

  // Gestionnaire pour le formulaire de nouveau hackathon
  const newHackathonForm = document.querySelector("#newHackathonModal form")
  if (newHackathonForm) {
    newHackathonForm.addEventListener("submit", handleNewHackathonSubmit)
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
          editHackathon(id)
          break
        case "view":
          viewHackathon(id)
          break
        case "delete":
          deleteHackathon(id)
          break
      }
    }
  })

  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(HACKATHON_ELEMENTS.searchInput)
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch)
  }

  // Initialiser les modals
  initializeModals()
}

/**
 * Gère la soumission du formulaire de nouveau hackathon
 * @param {Event} e - Événement de soumission
 */
async function handleNewHackathonSubmit(e) {
  e.preventDefault()

  try {
    showLoading()

    // Récupérer les données du formulaire
    const formData = new FormData(e.target)
    const hackathonData = {
      name: formData.get("hackathonName"),
      start_date: formData.get("hackathonStartDate"),
      end_date: formData.get("hackathonEndDate"),
      location: formData.get("hackathonLocation"),
      max_participants: formData.get("hackathonMaxParticipants"),
      team_size: formData.get("hackathonTeamSize"),
      duration: formData.get("hackathonDuration"),
      prizes: formData.get("hackathonPrizes"),
      description: formData.get("hackathonDescription"),
    }

    // Envoyer les données à l'API
    const response = await apiRequest("/admin/hackathons", {
      method: "POST",
      body: JSON.stringify(hackathonData),
    })

    if (response.success) {
      // Fermer le modal
      closeModal("newHackathonModal")

      // Recharger les hackathons
      await loadHackathons()

      // Afficher un message de succès
      showNotification("Hackathon créé avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de la création du hackathon", error)
  } finally {
    hideLoading()
  }
}

/**
 * Édite un hackathon
 * @param {string} id - ID du hackathon
 */
async function editHackathon(id) {
  try {
    showLoading()

    // Récupérer les données du hackathon
    const response = await apiRequest(`/admin/hackathons/${id}`)

    if (response.success && response.data) {
      // Remplir le formulaire
      const hackathon = response.data
      document.querySelector("#hackathonName").value = hackathon.name || hackathon.title
      document.querySelector("#hackathonStartDate").value = formatDateForInput(hackathon.start_date)
      document.querySelector("#hackathonEndDate").value = formatDateForInput(hackathon.end_date)
      document.querySelector("#hackathonLocation").value = hackathon.location
      document.querySelector("#hackathonMaxParticipants").value = hackathon.max_participants
      document.querySelector("#hackathonTeamSize").value = hackathon.team_size
      document.querySelector("#hackathonDuration").value = hackathon.duration
      document.querySelector("#hackathonPrizes").value = hackathon.prizes
      document.querySelector("#hackathonDescription").value = hackathon.description

      // Modifier le titre du modal
      const modalTitle = document.querySelector("#newHackathonModal .modal-header h2")
      if (modalTitle) {
        modalTitle.innerHTML = '<i class="fas fa-edit"></i> Modifier le Hackathon'
      }

      // Modifier le bouton de soumission
      const submitButton = document.querySelector("#newHackathonModal .btn-primary")
      if (submitButton) {
        submitButton.innerHTML = '<i class="fas fa-save"></i> Mettre à jour'
        submitButton.dataset.id = id
      }

      // Ouvrir le modal
      openModal("newHackathonModal")
    }
  } catch (error) {
    handleError("Erreur lors de la récupération du hackathon", error)
  } finally {
    hideLoading()
  }
}

/**
 * Affiche les détails d'un hackathon
 * @param {string} id - ID du hackathon
 */
async function viewHackathon(id) {
  try {
    showLoading()

    // Rediriger vers la page de détails
    window.location.href = `/admin/hackathons/view.php?id=${id}`
  } catch (error) {
    handleError("Erreur lors de la récupération du hackathon", error)
  } finally {
    hideLoading()
  }
}

/**
 * Supprime un hackathon
 * @param {string} id - ID du hackathon
 */
async function deleteHackathon(id) {
  if (!confirm("Êtes-vous sûr de vouloir supprimer ce hackathon ?")) {
    return
  }

  try {
    showLoading()

    // Envoyer la requête de suppression
    const response = await apiRequest(`/admin/hackathons/${id}`, {
      method: "DELETE",
    })

    if (response.success) {
      // Recharger les hackathons
      await loadHackathons()

      // Afficher un message de succès
      showNotification("Hackathon supprimé avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de la suppression du hackathon", error)
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
  initializeHackathonPage()
})
