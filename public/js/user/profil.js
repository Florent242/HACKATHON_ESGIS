const API_BASE_URL = "/HACKATHON_ESGIS/public/api"
const PROFILE_ELEMENTS = {
  username: ".Username",
  email: ".Email",
  fullName: ".fullName",
  special_comp: ".special_comp",
  university: ".university",
  skill: ".skill",
  languages: ".languages",
  study_level: ".study_level",
  number: ".number",
  loadingSpinner: "#global-loading-spinner",
  stats: {
    challengesSolved: "#number-challenges-solved",
    hackingChallenges: "#number-hacking-challenges",
    devChallengesOn: "#number-dev-challenges-on",
    hackingChallengesValidate: "#number-hacking-challenges-validate",
    submittedProjects: "#number-submitted-projects",
    totalPoints: "#total-points",
    ranking: "#number-ranking",
    devStat: "#dev-stat",
    hackingStat: "#hacking-stat",
    totalPointsStat: "#total-points-stat",
  },
  currentChallenges: {
    container: "#subTab1",
    emptyState: "#no-current-challenges",
  },
  completedChallenges: {
    container: "#subTab2",
    emptyState: "#no-completed-challenges",
  },
  recentActivities: {
    container: "#recent-activities-container",
    template: ".recent-activity-item",
    emptyState: "#no-recent-activities",
  },
  nextEvent: {
    container: "#next-event-container",
    title: ".next-event-title",
    description: ".next-event-description",
    startDate: ".next-event-start-date",
    endDate: ".next-event-end-date",
    location: ".next-event-location",
    noEventMessage: "#no-next-event",
  },
}

/**
 * Affiche le spinner de chargement
 */
function showLoading() {
  // Créer un spinner s'il n'existe pas déjà
  let spinner = document.querySelector(PROFILE_ELEMENTS.loadingSpinner)
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
                background-color: rgba(0, 0, 0, 0.5);
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
  const spinner = document.querySelector(PROFILE_ELEMENTS.loadingSpinner)
  if (spinner) {
    spinner.classList.add("hidden")
  }
}

/**
 * Met à jour la section des activités récentes
 * @param {Array} activities - Liste des activités récentes
 */
/*
function updateRecentActivities(activities) {
  const container = document.querySelector(PROFILE_ELEMENTS.recentActivities.container)
  const emptyState = document.querySelector(PROFILE_ELEMENTS.recentActivities.emptyState)

  if (!container) {
    console.error("Conteneur des activités récentes non trouvé")
    return
  }

  // Cache le conteneur si aucune activité
  if (!activities || activities.length === 0) {
    if (emptyState) emptyState.style.display = "flex"
    container.innerHTML = "" // Vider le conteneur
    return
  }

  // Affiche le conteneur et cache l'empty state
  container.style.display = "flex"
  if (emptyState) emptyState.style.display = "none"

  // Vider le conteneur
  container.innerHTML = ""

  // Créer et ajouter chaque activité
  activities.forEach((activity) => {
    const activityElement = createActivityElement(activity)
    container.appendChild(activityElement)
  })

  // Actualiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
}
*/
function updateRecentActivities(activities = []) {
  const container = document.querySelector(PROFILE_ELEMENTS.recentActivities.container)
  const emptyState = document.querySelector(PROFILE_ELEMENTS.recentActivities.emptyState)

  if (!container) return

  // Vider le contenu
  container.innerHTML = ""

  // Vérifier si c'est un tableau
  if (!Array.isArray(activities)) {
    console.error("Données d'activités invalides:", activities)
    activities = []
  }

  if (activities.length === 0) {
    if (emptyState) emptyState.style.display = "flex"
    return
  }

  if (emptyState) emptyState.style.display = "none"

  // Ajouter chaque activité
  activities.forEach((activity) => {
    const element = createActivityElement(activity)
    container.appendChild(element)
  })

  if (window.lucide) window.lucide.createIcons()
}
/**
 * Crée un élément d'activité
 * @param {Object} activity - Données de l'activité
 * @returns {HTMLElement} - Élément DOM de l'activité
 */
function createActivityElement(activity) {
  const div = document.createElement("div")
  div.className =
    "flex flex-row justify-start p-4 items-center w-full bg-(--card-bg) rounded-xl gap-5 border-b border-slate-800 pb-4 transition duration-300 ease-in-out hover:-translate-y-1 recent-activity-item"

  // Déterminer l'icône et la classe en fonction du type d'activité
  let iconClass = "text-blue-500"
  let iconName = "activity"
  let bgClass = "bg-blue-700/30"

  // Adapter en fonction de la structure de données renvoyée par l'API
  const activityType = activity.type || activity.action_type || activity.level || "default"

  switch (activityType) {
    case "success":
      iconName = "check-square"
      iconClass = "text-green-500"
      bgClass = "bg-green-700/30"
      break
    case "error":
    case "register_error":
      iconName = "alert-circle"
      iconClass = "text-red-500"
      bgClass = "bg-red-700/30"
      break
    case "warning":
      iconName = "alert-triangle"
      iconClass = "text-yellow-500"
      bgClass = "bg-yellow-700/30"
      break
    case "info":
      iconName = "info"
      iconClass = "text-blue-500"
      bgClass = "bg-blue-700/30"
      break
    case "challenge":
    case "solve_challenge":
      iconName = "flag"
      iconClass = "text-purple-500"
      bgClass = "bg-purple-700/30"
      break
    case "hackathon":
      iconName = "calendar"
      iconClass = "text-blue-500"
      bgClass = "bg-blue-700/30"
      break
    case "team":
      iconName = "users"
      iconClass = "text-orange-500"
      bgClass = "bg-orange-700/30"
      break
  }

  // Formater la date
  const date =
    activity.timestamp || activity.created_at ? formatDate(activity.timestamp || activity.created_at) : "Récemment"

  // Déterminer le texte de l'activité
  const activityText = activity.description || activity.action || "Activité inconnue"
  const activityDetails = activity.details || activity.metadata || ""

  div.innerHTML = `
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center">
                <div class="flex items-center justify-center size-fit ${bgClass} p-2 rounded-full mr-4">
                    <i data-lucide="${iconName}" class="w-4 h-4 stroke-current ${iconClass} activity-icon"></i>
                </div>
                <div class="flex flex-col items-start justify-between">
                    <p class="max-lg:text-xs text-white max-md:text-xs max-md:font-normal activity-text">${sanitizeText(activityText)}</p>
                    ${activityDetails ? `<p class="max-lg:text-xs max-md:text-xs max-md:font-normal activity-details">${sanitizeText(activityDetails)}</p>` : ""}
                </div>
            </div>
            <p class="max-lg:text-xs flex self-baseline text-gray-400 max-md:text-xs max-md:font-normal activity-time">${date}</p>
        </div>
    `

  return div
}

/**
 * Met à jour les statistiques de l'utilisateur
 * @param {Object} stats - Statistiques à afficher
 */
function updateUserStats(stats) {
  if (!stats) return

  // Mettre à jour les compteurs
  const elements = PROFILE_ELEMENTS.stats
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key])
    if (element && stats[key] !== undefined) {
      element.textContent = stats[key]
    }
  })
}

/**
 * Met à jour les défis en cours
 * @param {Array} challenges - Liste des défis en cours
 */
/*
function updateCurrentChallenges(challenges) {
  const container = document.querySelector(PROFILE_ELEMENTS.currentChallenges.container)
  const emptyState = document.querySelector(PROFILE_ELEMENTS.currentChallenges.emptyState)

  if (!container) {
    console.error("Conteneur des défis en cours non trouvé")
    return
  }

  // Vider le conteneur existant
  const grid = container.querySelector(".grid")
  if (grid) {
    grid.innerHTML = ""
  } else {
    // Créer la grille si elle n'existe pas
    const newGrid = document.createElement("div")
    newGrid.className = "w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto"
    container.appendChild(newGrid)
  }

  // Afficher l'état vide si aucun défi
  if (!challenges || challenges.length === 0) {
    if (emptyState) {
      emptyState.style.display = "flex"
    } else {
      // Créer un état vide si nécessaire
      const emptyStateDiv = document.createElement("div")
      emptyStateDiv.id = "no-current-challenges"
      emptyStateDiv.className = "flex flex-col items-center justify-center p-8 text-center"
      emptyStateDiv.innerHTML = `
                <i data-lucide="flag-off" class="w-12 h-12 text-gray-500 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-300">Aucun défi en cours</h3>
                <p class="text-sm text-gray-400 mt-2">Explorez les défis disponibles pour commencer.</p>
                <button class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                    Découvrir les défis
                </button>
            `
      container.appendChild(emptyStateDiv)
    }
    return
  }

  // Cacher l'état vide
  if (emptyState) {
    emptyState.style.display = "none"
  }

  // Ajouter chaque défi
  challenges.forEach((challenge) => {
    const challengeElement = createChallengeElement(challenge, "current")
    container.querySelector(".grid").appendChild(challengeElement)
  })

  // Actualiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
}
*/
function updateCurrentChallenges(challenges) {
  const container = document.querySelector(PROFILE_ELEMENTS.currentChallenges.container)
  const emptyState = document.querySelector(PROFILE_ELEMENTS.currentChallenges.emptyState)

  if (!container) {
    console.error("Conteneur des défis en cours non trouvé")
    return
  }

  // Vider le contenu existant
  container.innerHTML = ""

  // Créer la grille si elle n'existe pas
  const grid = document.createElement("div")
  grid.className = "w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto"
  container.appendChild(grid)

  // Afficher l'état vide si pas de défis
  if (!Array.isArray(challenges)) {
    console.error("Données de défis invalides:", challenges)
    challenges = []
  }

  if (challenges.length === 0) {
    if (emptyState) {
      emptyState.style.display = "flex"
    } else {
      const emptyStateDiv = document.createElement("div")
      emptyStateDiv.id = "no-current-challenges"
      emptyStateDiv.className = "flex flex-col items-center justify-center p-8 text-center"
      emptyStateDiv.innerHTML = `
        <i data-lucide="flag-off" class="w-12 h-12 text-gray-500 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-300">Aucun défi en cours</h3>
        <p class="text-sm text-gray-400 mt-2">Explorez les défis disponibles pour commencer.</p>
        <button class="mt-4 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
          Découvrir les défis
        </button>
      `
      container.appendChild(emptyStateDiv)
    }
    return
  }

  // Masquer l'état vide
  if (emptyState) emptyState.style.display = "none"

  // Ajouter chaque défi
  challenges.forEach((challenge) => {
    const challengeElement = createChallengeElement(challenge, "current")
    grid.appendChild(challengeElement)
  })

  // Actualiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
}
/**
 * Met à jour les défis complétés
 * @param {Array} challenges - Liste des défis complétés
 */
/*
function updateCompletedChallenges(challenges) {
  const container = document.querySelector(PROFILE_ELEMENTS.completedChallenges.container)
  const emptyState = document.querySelector(PROFILE_ELEMENTS.completedChallenges.emptyState)

  if (!container) {
    console.error("Conteneur des défis complétés non trouvé")
    return
  }

  // Vider le conteneur existant
  const grid = container.querySelector(".grid")
  if (grid) {
    grid.innerHTML = ""
  } else {
    // Créer la grille si elle n'existe pas
    const newGrid = document.createElement("div")
    newGrid.className = "w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto"
    container.appendChild(newGrid)
  }

  // Afficher l'état vide si aucun défi
  if (!challenges || challenges.length === 0) {
    if (emptyState) {
      emptyState.style.display = "flex"
    } else {
      // Créer un état vide si nécessaire
      const emptyStateDiv = document.createElement("div")
      emptyStateDiv.id = "no-completed-challenges"
      emptyStateDiv.className = "flex flex-col items-center justify-center p-8 text-center"
      emptyStateDiv.innerHTML = `
                <i data-lucide="trophy" class="w-12 h-12 text-gray-500 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-300">Aucun défi complété</h3>
                <p class="text-sm text-gray-400 mt-2">Terminez des défis pour les voir apparaître ici.</p>
            `
      container.appendChild(emptyStateDiv)
    }
    return
  }

  // Cacher l'état vide
  if (emptyState) {
    emptyState.style.display = "none"
  }

  // Ajouter chaque défi
  challenges.forEach((challenge) => {
    const challengeElement = createChallengeElement(challenge, "completed")
    container.querySelector(".grid").appendChild(challengeElement)
  })

  // Actualiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
}*/
function updateCompletedChallenges(challenges) {
  const container = document.querySelector(PROFILE_ELEMENTS.completedChallenges.container)
  const emptyState = document.querySelector(PROFILE_ELEMENTS.completedChallenges.emptyState)

  if (!container) {
    console.error("Conteneur des défis complétés non trouvé")
    return
  }

  // Vider le contenu existant
  container.innerHTML = ""

  // Créer la grille si elle n'existe pas
  const grid = document.createElement("div")
  grid.className = "w-full grid grid-cols-2 max-md:grid-cols-1 gap-4 rounded-lg shadow-md mx-auto"
  container.appendChild(grid)

  // Afficher l'état vide si pas de défis
  if (!Array.isArray(challenges)) {
    console.error("Données de défis invalides:", challenges)
    challenges = []
  }

  if (challenges.length === 0) {
    if (emptyState) {
      emptyState.style.display = "flex"
    } else {
      const emptyStateDiv = document.createElement("div")
      emptyStateDiv.id = "no-completed-challenges"
      emptyStateDiv.className = "flex flex-col items-center justify-center p-8 text-center"
      emptyStateDiv.innerHTML = `
        <i data-lucide="trophy" class="w-12 h-12 text-gray-500 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-300">Aucun défi complété</h3>
        <p class="text-sm text-gray-400 mt-2">Terminez des défis pour les voir apparaître ici.</p>
      `
      container.appendChild(emptyStateDiv)
    }
    return
  }

  // Masquer l'état vide
  if (emptyState) emptyState.style.display = "none"

  // Ajouter chaque défi
  challenges.forEach((challenge) => {
    const challengeElement = createChallengeElement(challenge, "completed")
    grid.appendChild(challengeElement)
  })

  // Actualiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
}

/**
 * Crée un élément de défi
 * @param {Object} challenge - Données du défi
 * @param {string} type - Type de défi (current ou completed)
 * @returns {HTMLElement} - Élément DOM du défi
 */
function createChallengeElement(challenge, type) {
  const div = document.createElement("div")
  div.className =
    "card-bg w-full flex flex-col justify-between gap-2 p-4 border border-gray-700 rounded-xl shadow-md mx-auto transition delay-150 duration-300 ease-in-out hover:-translate-y-1"

  // Déterminer la difficulté et la classe
  let difficultyClass = "bg-green-500/20 text-green-500"
  let difficultyText = "Easy"

  switch (challenge.difficulty) {
    case "medium":
      difficultyClass = "bg-orange-500/20 text-orange-500"
      difficultyText = "Medium"
      break
    case "hard":
      difficultyClass = "bg-red-500/20 text-red-500"
      difficultyText = "Hard"
      break
    case "expert":
      difficultyClass = "bg-purple-500/20 text-purple-500"
      difficultyText = "Expert"
      break
  }

  // Formater la date
  const startDate = challenge.start_date ? formatDate(challenge.start_date, true) : "Date inconnue"
  const completedDate = challenge.completed_date ? formatDate(challenge.completed_date, true) : ""

  if (type === "current") {
    div.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">${sanitizeText(challenge.category || "web")}</span>
                <span class="${difficultyClass} rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">${difficultyText}</span>
            </div>
            <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal">${sanitizeText(challenge.title)}</h2>
            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">${sanitizeText(challenge.description || "")}</p>
            <div class="flex flex-row items-center justify-between mb-2">
                <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Started: ${startDate}</p>
                <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">${challenge.points || 0} points</p>
            </div>
            <button class="max-lg:text-sm bg-blue-500 text-white px-4 py-2 rounded transition duration-300 ease-in-out hover:bg-blue-600 hover:shadow-md hover:shadow-blue-500/50 max-md:font-normal" data-challenge-id="${challenge.id}">Continue Challenge</button>
        `
  } else {
    div.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <span class="bg-gray-700 rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">${sanitizeText(challenge.category || "web")}</span>
                <span class="${difficultyClass} rounded-full text-xs px-2 py-0.5 font-normal text-center flex items-center justify-center">${difficultyText}</span>
            </div>
            <h2 class="text-xl font-bold text-md text-white max-lg:text-sm max-md:font-normal">${sanitizeText(challenge.title)}</h2>
            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">${sanitizeText(challenge.description || "")}</p>
            <div class="flex flex-row items-center justify-between mb-2">
                <p class="flex flex-row items-center text-sm font-normal max-lg:text-xs text-white max-md:font-normal"><i data-lucide="trophy" class="w-4 h-4 stroke-current"></i>Completed</p>
                <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">${challenge.points || 0} points</p>
            </div>
            <p class="text-base font-normal max-lg:text-xs text-gray-400 max-md:font-normal">Completed on: ${completedDate}</p>
        `
  }

  // Ajouter un gestionnaire d'événements pour le bouton "Continue Challenge"
  if (type === "current") {
    const button = div.querySelector("button")
    if (button) {
      button.addEventListener("click", () => {
        window.location.href = `/HACKATHON_ESGIS/public/challenges/view.php?id=${challenge.id}`
      })
    }
  }

  return div
}

/**
 * Met à jour les éléments du DOM avec les données utilisateur
 * @param {Object} selectors - Sélecteurs des éléments à mettre à jour
 * @param {Object} data - Données utilisateur
 */
function updateDOM(selectors, data) {
  if (!data) return

  Object.keys(selectors).forEach((key) => {
    const selector = selectors[key]
    const value = data[key]

    if (value !== undefined) {
      const element = document.querySelector(selector)
      if (element) {
        element.textContent = value
      }
    }
  })
}

/**
 * Nettoie le texte pour prévenir les attaques XSS
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
      hour: "2-digit",
      minute: "2-digit",
    })
  } catch (e) {
    console.error("Erreur de formatage de date", e)
    return "Date inconnue"
  }
}

/**
 * Fonction utilitaire pour gérer les erreurs
 * @param {string} title - Titre de l'erreur
 * @param {Error} error - Objet d'erreur
 * @param {string} type - Type de notification
 */
function handleError(title = "Une erreur est survenue", error = null, type = "error") {
  console.error(title, error)

  // Affiche une notification à l'utilisateur
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.innerHTML = `
        <i data-lucide="${type === "error" ? "alert-circle" : "info"}"></i>
        <span>${sanitizeText(title)}</span>
    `

  const notificationContainer = document.querySelector(".notifications-container") || document.body
  notificationContainer.appendChild(notification)

  // Supprime la notification après 5 secondes
  setTimeout(() => {
    notification.remove()
  }, 5000)

  // Actualise les icônes
  if (window.lucide) {
    window.lucide.createIcons()
  }
}

// Fonction pour récupérer l'ID utilisateur
async function getUserId() {
  try {
    const response = await fetch("/HACKATHON_ESGIS/public/api/users/me", {
      method: "GET",
      credentials: "include",
      headers: { Accept: "application/json" },
    })

    if (!response.ok) {
      throw new Error("Utilisateur non authentifié")
    }

    const data = await response.json()
    return data.data?.id
  } catch (error) {
    handleError("Impossible de récupérer l'ID utilisateur", error, "error")
    return null
  }
}

/**
 * Charge les informations de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadUserInfo(userId) {
  try {
    if (!userId) {
      console.error("Utilisateur non authentifié")
      return
    }
    const data = await apiRequest(`/users/${userId}`)
    console.log("Informations utilisateur:", data)

    if (data.success) {
      updateDOM(
        {
          username: PROFILE_ELEMENTS.username,
          email: PROFILE_ELEMENTS.email,
          fullname: PROFILE_ELEMENTS.fullName,
          special_comp: PROFILE_ELEMENTS.special_comp,
          school: PROFILE_ELEMENTS.university,
          skill: PROFILE_ELEMENTS.skill,
          languages: PROFILE_ELEMENTS.languages,
          study_level: PROFILE_ELEMENTS.study_level,
          number: PROFILE_ELEMENTS.number,
        },
        data.data,
      )
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des informations utilisateur", error, "error")
  }
}

/**
 * Charge les statistiques de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
async function loadUserStats(userId) {
  try {
    if (!userId) {
      console.error("Utilisateur non authentifié")
      return
    }

    const response = await apiRequest(`/users/${userId}/stats`)
    console.log("Statistiques utilisateur:", response)

    if (response.success) {
      updateUserStats(response.data || {})
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des statistiques", error, "error")
  }
}

/**
 * Charge les défis en cours de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
/*
async function loadCurrentChallenges(userId) {
  try {
    if (!userId) {
      console.error("Utilisateur non authentifié")
      return
    }

    const response = await apiRequest(`/users/me/${userId}/challenges/current`)
    console.log("Défis en cours:", response)

    if (response.success) {
      updateCurrentChallenges(response.data || [])
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des défis en cours", error, "error")
  }
}*/
async function loadCurrentChallenges(userId) {
  try {
    if (!userId) return

    const response = await apiRequest(`/users/${userId}/current-challenges`)
    console.log("Défis en cours:", response)

    if (response.success) {
      updateCurrentChallenges(response.data || [])
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des défis en cours", error)
  }
}

/**
 * Charge les défis complétés de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */
/*
async function loadCompletedChallenges(userId) {
  try {
    if (!userId) {
      console.error("Utilisateur non authentifié")
      return
    }

    const response = await apiRequest(`/users/me/${userId}/challenges/completed`)
    console.log("Défis complétés:", response)

    if (response.success) {
      updateCompletedChallenges(response.data || [])
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des défis complétés", error, "error")
  }
}*/
async function loadCompletedChallenges(userId) {
  try {
    if (!userId) return

    const response = await apiRequest(`/users/${userId}/completed-challenges`)
    console.log("Défis complétés:", response)

    if (response.success) {
      updateCompletedChallenges(response.data || [])
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des défis complétés", error)
  }
}

/**
 * Charge les activités récentes de l'utilisateur
 * @param {string} userId - ID de l'utilisateur
 */ /*
async function loadRecentActivity(userId) {
  try {
    if (!userId) {
      console.error("Utilisateur non authentifié")
      return
    }

    // Charge les activités récentes
    const activitiesResponse = await apiRequest(`/users/me/${userId}/recent-activities`)
    console.log("Activités récentes:", activitiesResponse)

    if (activitiesResponse.success) {
      updateRecentActivities(activitiesResponse.data || [])
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des activités récentes", error, "error")
  }
}*/
async function loadRecentActivity(userId) {
  try {
    if (!userId) return

    const response = await apiRequest(`/users/${userId}/recent-activities`)
    console.log("Activités récentes:", response)

    if (response.success) {
      updateRecentActivities(response.data || [])
    }
  } catch (error) {
    handleError("Erreur lors de la récupération des activités récentes", error)
  }
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
    console.error("Erreur lors de la requête API", error)
    throw error
  }
}

/**
 * Initialise la page de profil
 */
async function initializeProfilePage() {
  try {
    showLoading()

    const userId = await getUserId()
    if (!userId) {
      hideLoading()
      return
    }

    // Charger toutes les données en parallèle
    await Promise.all([
      loadUserInfo(userId),
      loadUserStats(userId),
      loadCurrentChallenges(userId),
      loadCompletedChallenges(userId),
      loadRecentActivity(userId),
    ])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error, "error")
  } finally {
    hideLoading()
  }
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton "Modify the profile"
  const modifyProfileButton = document.querySelector(".modify-profile")
  if (modifyProfileButton) {
    modifyProfileButton.addEventListener("click", () => {
      // Activer l'onglet des paramètres
      document.querySelector('[data-tab="tab4"]').click()
    })
  }

  // Gestionnaire pour le bouton "All challenges"
  const allChallengesButton = document.querySelector(".All-challenges")
  if (allChallengesButton) {
    allChallengesButton.addEventListener("click", () => {
      window.location.href = "/HACKATHON_ESGIS/public/challenges/index.php"
    })
  }

  // Gestionnaire pour le formulaire de mise à jour du profil
  const updateProfileForm = document.querySelector("form:nth-of-type(1)")
  if (updateProfileForm) {
    updateProfileForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      try {
        showLoading()

        const formData = new FormData(e.target)
        const userData = {
          firstName: formData.get("firstName"),
          lastName: formData.get("lastName"),
          displayName: formData.get("displayName"),
          email: formData.get("email"),
          school: formData.get("school"),
          location: formData.get("location"),
          bio: formData.get("bio"),
        }

        const userId = await getUserId()
        const response = await apiRequest(`/users/${userId}`, {
          method: "PUT",
          body: JSON.stringify(userData),
        })

        if (response.success) {
          handleError("Profil mis à jour avec succès", null, "success")
          await loadUserInfo(userId)
        }
      } catch (error) {
        handleError("Erreur lors de la mise à jour du profil", error, "error")
      } finally {
        hideLoading()
      }
    })
  }

  // Gestionnaire pour le formulaire de changement de mot de passe
  const changePasswordForm = document.querySelector("form:nth-of-type(2)")
  if (changePasswordForm) {
    changePasswordForm.addEventListener("submit", async (e) => {
      e.preventDefault()

      try {
        showLoading()

        const formData = new FormData(e.target)
        const passwordData = {
          currentPassword: formData.get("currentPassword"),
          newPassword: formData.get("newPassword"),
          confirmPassword: formData.get("confirmPassword"),
        }

        // Vérifier que les mots de passe correspondent
        if (passwordData.newPassword !== passwordData.confirmPassword) {
          throw new Error("Les mots de passe ne correspondent pas")
        }

        const userId = await getUserId()
        const response = await apiRequest(`/users/${userId}/password`, {
          method: "PUT",
          body: JSON.stringify(passwordData),
        })

        if (response.success) {
          handleError("Mot de passe mis à jour avec succès", null, "success")
          e.target.reset()
        }
      } catch (error) {
        handleError("Erreur lors de la mise à jour du mot de passe", error, "error")
      } finally {
        hideLoading()
      }
    })
  }
}

// Initialiser la page lorsque le DOM est chargé
document.addEventListener("DOMContentLoaded", async () => {
  /* Tabs for main content */
  // tabs button link
  const tabs = document.querySelectorAll(".tab-link")
  // tabs content
  const contents = document.querySelectorAll(".tab-content")

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      const target = this.getAttribute("data-tab")

      // Supprime la classe active de tous les onglets et cache le contenu
      tabs.forEach((t) => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"))
      contents.forEach((c) => c.classList.add("hidden"))

      // Active le bon onglet et affiche le bon contenu
      this.classList.add("text-white", "border-blue-500", "bg-gray-900/75")
      document.getElementById(target).classList.remove("hidden")
    })
  })

  /* Tabs for challenges subcontent */
  // subtabs button link
  const subTabs = document.querySelectorAll(".sub-tab-link")
  // subtabs content
  const subContents = document.querySelectorAll(".sub-tab-content")

  subTabs.forEach((subTab) => {
    subTab.addEventListener("click", function () {
      const target = this.getAttribute("data-sub-tab")

      // Supprime la classe active de tous les onglets et cache le contenu
      subTabs.forEach((t) => t.classList.remove("text-white", "border-blue-500", "bg-gray-900/75"))
      subContents.forEach((c) => c.classList.add("hidden"))

      // Active le bon onglet et affiche le bon contenu
      this.classList.add("text-white", "border-blue-500", "bg-gray-900/75")
      document.getElementById(target).classList.remove("hidden")
    })
  })

  // Initialiser la page de profil
  await initializeProfilePage()

  // Initialiser les icônes Lucide
  if (window.lucide) {
    window.lucide.createIcons()
  }
})
