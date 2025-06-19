// Configuration de base
const API_BASE_URL = "/api"

// Sélecteurs pour les éléments de la page
const USER_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  stats: {
    totalUsers: ".stat-card:nth-child(1) .number",
    admins: ".stat-card:nth-child(2) .number",
    activeUsers: ".stat-card:nth-child(3) .number",
    suspendedUsers: ".stat-card:nth-child(4) .number",
  },
  usersTable: {
    container: "#usersTable tbody",
    rows: "#usersTable tbody tr",
  },
  activityFeed: {
    container: ".activity-feed",
    items: ".activity-item",
  },
  searchInput: ".search-input",
}

/**
 * Initialise la page de gestion des utilisateurs
 */
async function initializeUserPage() {
  try {
    showLoading()

    // Charger toutes les données en parallèle
    await Promise.all([loadUsers(), loadUserStats(), loadUserActivity()])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error)
  } finally {
    hideLoading()
  }
}

/**
 * Charge la liste des utilisateurs
 */
async function loadUsers() {
  try {
    const response = await apiRequest("/admin/users")

    if (response.success && response.data) {
      updateUsersTable(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des utilisateurs", error)
  }
}

/**
 * Met à jour le tableau des utilisateurs
 * @param {Array} users - Liste des utilisateurs
 */
function updateUsersTable(users) {
  const container = document.querySelector(USER_ELEMENTS.usersTable.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucun utilisateur
  if (!users || !users.length) {
    container.innerHTML = `
      <tr>
        <td colspan="5">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="empty-state-text">
              <h3>Aucun utilisateur</h3>
              <p>Ajoutez des utilisateurs en cliquant sur le bouton "Ajouter utilisateur".</p>
            </div>
          </div>
        </td>
      </tr>
    `
    return
  }

  // Ajouter chaque utilisateur
  users.forEach((user) => {
    const row = document.createElement("tr")

    // Déterminer le rôle et la classe
    let roleClass = "badge-info"
    let roleText = "Utilisateur"

    if (user.role === "admin") {
      roleClass = "badge-primary"
      roleText = "Admin"
    } else if (user.role === "moderator") {
      roleClass = "badge-info"
      roleText = "Modérateur"
    }

    // Déterminer le statut et la classe
    let statusClass = "badge-success"
    let statusText = "Actif"

    if (user.status === "suspended") {
      statusClass = "badge-warning"
      statusText = "Suspendu"
    } else if (user.status === "inactive") {
      statusClass = "badge-danger"
      statusText = "Inactif"
    }

    row.innerHTML = `
      <td>${sanitizeText(user.name || user.username)}</td>
      <td>${sanitizeText(user.email)}</td>
      <td><span class="badge ${roleClass}">${roleText}</span></td>
      <td><span class="badge ${statusClass}">${statusText}</span></td>
      <td>
        <div class="dropdown">
          <button class="dropdown-toggle">
            <i class="fas fa-ellipsis-v"></i>
          </button>
          <div class="dropdown-menu">
            <a href="#" class="dropdown-item action-button" data-action="edit" data-id="${user.id}">Modifier</a>
            <a href="#" class="dropdown-item action-button" data-action="view" data-id="${user.id}">Voir profil</a>
            ${
              user.status === "suspended"
                ? `<a href="#" class="dropdown-item action-button" data-action="activate" data-id="${user.id}">Activer</a>`
                : `<a href="#" class="dropdown-item action-button" data-action="suspend" data-id="${user.id}">Suspendre</a>`
            }
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
 * Charge les statistiques des utilisateurs
 */
async function loadUserStats() {
  try {
    const response = await apiRequest("/admin/user-stats")

    if (response.success && response.data) {
      updateUserStats(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des statistiques", error)
  }
}

/**
 * Met à jour les statistiques des utilisateurs
 * @param {Object} stats - Statistiques à afficher
 */
function updateUserStats(stats) {
  if (!stats) return

  // Mettre à jour les compteurs
  const elements = USER_ELEMENTS.stats
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key])
    if (element && stats[key] !== undefined) {
      element.textContent = stats[key]
    }
  })
}

/**
 * Charge les activités récentes des utilisateurs
 */
async function loadUserActivity() {
  try {
    const response = await apiRequest("/admin/activity")

    if (response.success && response.data) {
      // Filtrer pour obtenir uniquement les activités liées aux utilisateurs
      const userActivities = response.data
        .filter((activity) => {
          const type = activity.type || activity.action_type || ""
          return type.includes("user") || type.includes("login") || type.includes("register")
        })
        .slice(0, 3) // Limiter à 3 activités

      updateActivityFeed(userActivities)
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
  const container = document.querySelector(USER_ELEMENTS.activityFeed.container)

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
  let iconName = "user"

  // Adapter en fonction de la structure de données renvoyée par l'API
  const activityType = activity.type || activity.action_type || "default"

  switch (activityType) {
    case "login":
      iconName = "sign-in-alt"
      iconClass = "background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;"
      break
    case "register":
      iconName = "user-plus"
      iconClass = "background-color: rgba(16, 185, 129, 0.2); color: #10b981;"
      break
    case "submission":
    case "submit_solution":
      iconName = "file-code"
      iconClass = "background-color: rgba(16, 185, 129, 0.2); color: #10b981;"
      break
    case "hackathon":
      iconName = "calendar-alt"
      iconClass = "background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;"
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
      ${date}
    </div>
  `

  return div
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton "Ajouter utilisateur"
  const addUserButton = document.querySelector(".btn-primary:nth-of-type(2)")
  if (addUserButton) {
    addUserButton.addEventListener("click", () => {
      // Rediriger vers la page d'ajout d'utilisateur ou ouvrir un modal
      // window.location.href = "/admin/utilisateurs/add.php"
      alert("Fonctionnalité d'ajout d'utilisateur à implémenter")
    })
  }

  // Gestionnaire pour le bouton "Notification globale"
  const notificationButton = document.querySelector(".btn-primary:nth-of-type(1)")
  if (notificationButton) {
    notificationButton.addEventListener("click", () => {
      // Ouvrir un modal pour envoyer une notification globale
      alert("Fonctionnalité de notification globale à implémenter")
    })
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
          editUser(id)
          break
        case "view":
          viewUser(id)
          break
        case "suspend":
          suspendUser(id)
          break
        case "activate":
          activateUser(id)
          break
      }
    }
  })

  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(USER_ELEMENTS.searchInput)
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch)
  }

  // Initialiser les dropdowns
  initializeDropdowns()
}

/**
 * Édite un utilisateur
 * @param {string} id - ID de l'utilisateur
 */
function editUser(id) {
  // Rediriger vers la page d'édition d'utilisateur
  window.location.href = `/admin/utilisateurs/edit.php?id=${id}`
}

/**
 * Affiche les détails d'un utilisateur
 * @param {string} id - ID de l'utilisateur
 */
function viewUser(id) {
  // Rediriger vers la page de détails d'utilisateur
  window.location.href = `q/admin/utilisateurs/view.php?id=${id}`
}

/**
 * Suspend un utilisateur
 * @param {string} id - ID de l'utilisateur
 */
async function suspendUser(id) {
  if (!confirm("Êtes-vous sûr de vouloir suspendre cet utilisateur ?")) {
    return
  }

  try {
    showLoading()

    // Envoyer la requête de suspension
    const response = await apiRequest(`/admin/users/${id}/suspend`, {
      method: "POST",
    })

    if (response.success) {
      // Recharger les utilisateurs
      await loadUsers()

      // Afficher un message de succès
      showNotification("Utilisateur suspendu avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de la suspension de l'utilisateur", error)
  } finally {
    hideLoading()
  }
}

/**
 * Active un utilisateur
 * @param {string} id - ID de l'utilisateur
 */
async function activateUser(id) {
  try {
    showLoading()

    // Envoyer la requête d'activation
    const response = await apiRequest(`/admin/users/${id}/activate`, {
      method: "POST",
    })

    if (response.success) {
      // Recharger les utilisateurs
      await loadUsers()

      // Afficher un message de succès
      showNotification("Utilisateur activé avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de l'activation de l'utilisateur", error)
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

// Initialiser la page lorsque le DOM est chargé
document.addEventListener("DOMContentLoaded", () => {
  // Initialiser la page
  initializeUserPage()
})
