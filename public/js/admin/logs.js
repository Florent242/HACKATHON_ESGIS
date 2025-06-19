// Configuration de base
const API_BASE_URL = "/api"

// Sélecteurs pour les éléments de la page
const LOGS_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  stats: {
    totalLogs: ".stat-card:nth-child(1) .number",
    connections: ".stat-card:nth-child(2) .number",
    teamActions: ".stat-card:nth-child(3) .number",
    challenges: ".stat-card:nth-child(4) .number",
  },
  activityFeed: {
    container: ".activity-feed",
    items: ".activity-item",
  },
  searchInput: ".search-input",
  exportButton: ".btn-primary",
  filterDropdowns: ".dropdown-toggle",
}

/**
 * Initialise la page de logs
 */
async function initializeLogsPage() {
  try {
    showLoading()

    // Charger toutes les données en parallèle
    await Promise.all([loadLogs(), loadLogStats()])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error)
  } finally {
    hideLoading()
  }
}

/**
 * Charge la liste des logs
 */
async function loadLogs() {
  try {
    const response = await apiRequest("/admin/logs")

    if (response.success && response.data) {
      updateActivityFeed(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des logs", error)
  }
}

/**
 * Met à jour le flux d'activité
 * @param {Array} logs - Liste des logs
 */
function updateActivityFeed(logs) {
  const container = document.querySelector(LOGS_ELEMENTS.activityFeed.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucun log
  if (!logs || !logs.length) {
    container.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-icon">
          <i class="fas fa-history"></i>
        </div>
        <div class="empty-state-text">
          <h3>Aucun log</h3>
          <p>Les logs d'activité apparaîtront ici.</p>
        </div>
      </div>
    `
    return
  }

  // Ajouter chaque log
  logs.forEach((log) => {
    const logElement = createLogElement(log)
    container.appendChild(logElement)
  })
}

/**
 * Crée un élément de log
 * @param {Object} log - Données du log
 * @returns {HTMLElement} - Élément DOM du log
 */
function createLogElement(log) {
  const div = document.createElement("div")
  div.className = "activity-item"

  // Déterminer l'icône et la classe en fonction du type de log
  let iconClass = "background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;"
  let iconName = "history"

  // Adapter en fonction de la structure de données renvoyée par l'API
  const logType = log.type || log.action_type || "default"

  switch (logType) {
    case "login":
    case "connection":
      iconName = "sign-in-alt"
      iconClass = "background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;"
      break
    case "team":
    case "create_team":
    case "join_team":
      iconName = "users"
      iconClass = "background-color: rgba(245, 158, 11, 0.2); color: #f59e0b;"
      break
    case "challenge":
    case "create_challenge":
    case "solve_challenge":
      iconName = "trophy"
      iconClass = "background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;"
      break
    case "hackathon":
    case "create_hackathon":
      iconName = "calendar-alt"
      iconClass = "background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;"
      break
    case "submission":
    case "submit_solution":
      iconName = "file-code"
      iconClass = "background-color: rgba(16, 185, 129, 0.2); color: #10b981;"
      break
    case "resource":
    case "create_resource":
      iconName = "file-alt"
      iconClass = "background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;"
      break
  }

  // Formater la date
  const date = log.timestamp || log.created_at ? formatDate(log.timestamp || log.created_at) : "Récemment"

  // Déterminer le texte du log
  const logText = log.description || log.action || "Activité inconnue"
  const username = log.username || log.user_name || ""
  const details = log.details || log.metadata || ""

  div.innerHTML = `
    <div class="activity-icon" style="${iconClass}">
      <i class="fas fa-${iconName}"></i>
    </div>
    <div class="activity-content">
      <div class="activity-title">${sanitizeText(username)}</div>
      <div class="activity-subtitle">${sanitizeText(logText)}</div>
      ${details ? `<div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">${sanitizeText(details)}</div>` : ""}
    </div>
    <div class="activity-time">
      ${date}
    </div>
  `

  return div
}

/**
 * Charge les statistiques des logs
 */
async function loadLogStats() {
  try {
    const response = await apiRequest("/admin/log-stats")

    if (response.success && response.data) {
      updateLogStats(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des statistiques", error)
  }
}

/**
 * Met à jour les statistiques des logs
 * @param {Object} stats - Statistiques à afficher
 */
function updateLogStats(stats) {
  if (!stats) return

  // Mettre à jour les compteurs
  const elements = LOGS_ELEMENTS.stats
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key])
    if (element && stats[key] !== undefined) {
      element.textContent = stats[key]
    }
  })
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton d'exportation
  const exportButton = document.querySelector(LOGS_ELEMENTS.exportButton)
  if (exportButton) {
    exportButton.addEventListener("click", exportLogs)
  }

  // Gestionnaire pour les dropdowns de filtre
  document.querySelectorAll(LOGS_ELEMENTS.filterDropdowns).forEach((dropdown) => {
    dropdown.addEventListener("click", (e) => {
      e.preventDefault()
      e.stopPropagation()
      const menu = dropdown.nextElementSibling
      menu.classList.toggle("show")
    })
  })

  // Gestionnaire pour les éléments de dropdown
  document.querySelectorAll(".dropdown-item").forEach((item) => {
    item.addEventListener("click", (e) => {
      e.preventDefault()
      const dropdown = item.closest(".dropdown")
      const toggleButton = dropdown.querySelector(".dropdown-toggle span")

      // Mettre à jour le texte du bouton
      if (toggleButton) {
        toggleButton.textContent = item.textContent
      }

      // Fermer le dropdown
      dropdown.querySelector(".dropdown-menu").classList.remove("show")

      // Filtrer les logs
      filterLogs()
    })
  })

  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(LOGS_ELEMENTS.searchInput)
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch)
  }

  // Initialiser les dropdowns
  initializeDropdowns()
}

/**
 * Exporte les logs
 */
async function exportLogs() {
  try {
    showLoading()

    // Récupérer les logs
    const response = await apiRequest("/admin/logs/export")

    if (response.success && response.data) {
      // Créer un fichier CSV
      const csv = convertToCSV(response.data)

      // Télécharger le fichier
      downloadCSV(csv, "logs_export.csv")

      // Afficher un message de succès
      showNotification("Logs exportés avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de l'exportation des logs", error)
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
 * Filtre les logs en fonction des sélections de dropdown
 */
function filterLogs() {
  // Récupérer les valeurs des filtres
  const typeFilter = document.querySelector(".dropdown-toggle:nth-of-type(1) span").textContent
  const periodFilter = document.querySelector(".dropdown-toggle:nth-of-type(2) span").textContent

  // Récupérer tous les logs
  const logs = document.querySelectorAll(".activity-item")

  logs.forEach((log) => {
    let showLog = true

    // Filtrer par type
    if (typeFilter !== "Tous les logs") {
      const logText = log.textContent.toLowerCase()

      switch (typeFilter) {
        case "Connexions":
          showLog = logText.includes("connecté") || logText.includes("connexion")
          break
        case "Actions utilisateurs":
          showLog = logText.includes("utilisateur") || logText.includes("soumis") || logText.includes("inscrit")
          break
        case "Modifications système":
          showLog = logText.includes("ajouté") || logText.includes("modifié") || logText.includes("supprimé")
          break
      }
    }

    // Filtrer par période
    if (periodFilter !== "Toutes les périodes" && showLog) {
      const dateText = log.querySelector(".activity-time").textContent.toLowerCase()

      switch (periodFilter) {
        case "Aujourd'hui":
          showLog = dateText.includes("instant") || dateText.includes("minute") || dateText.includes("heure")
          break
        case "Cette semaine":
          showLog =
            dateText.includes("instant") ||
            dateText.includes("minute") ||
            dateText.includes("heure") ||
            dateText.includes("jour")
          break
        case "Ce mois":
          showLog = !dateText.includes("mois") || dateText.includes("ce mois")
          break
      }
    }

    // Afficher ou masquer le log
    log.style.display = showLog ? "" : "none"
  })
}

/**
 * Gère la recherche
 * @param {Event} e - Événement de saisie
 */
function handleSearch(e) {
  const searchTerm = e.target.value.toLowerCase()
  const logs = document.querySelectorAll(".activity-item")

  logs.forEach((log) => {
    const text = log.textContent.toLowerCase()
    log.style.display = text.includes(searchTerm) ? "" : "none"
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
  initializeLogsPage()
})
