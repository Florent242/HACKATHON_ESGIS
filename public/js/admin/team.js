
// Configuration de base
const API_BASE_URL = "/api"

// Sélecteurs pour les éléments de la page
const TEAM_ELEMENTS = {
  loadingSpinner: "#global-loading-spinner",
  stats: {
    "teams_count": ".stat-card:nth-child(1) .number",
    "users_count": ".stat-card:nth-child(2) .number",
    "hackathons_count": ".stat-card:nth-child(3) .number",
    "challenges_count": ".stat-card:nth-child(4) .number",
  },
  teamsTable: {
    container: "#teamsTable tbody",
    rows: "#teamsTable tbody tr",
  },
  featuredTeams: {
    container: ".team-card",
  },
  searchInput: ".search-input",
}
let teams = null;

/**
 * Initialise la page de gestion des équipes
 */
async function initializeTeamPage() {
  try {
    showLoading()

    // Charger toutes les données en parallèle
    await Promise.all([loadTeams(), loadTeamStats(), loadFeaturedTeams()])

    // Configurer les gestionnaires d'événements
    setupEventListeners()
  } catch (error) {
    handleError("Erreur lors de l'initialisation de la page", error)
  } finally {
    hideLoading()
  }
}

/**
 * Charge la liste des équipes
 */
async function loadTeams() {
  try {
    const response = await apiRequest("/admin/teams")

    if (response.success && response.data) {
      teams = response.data;
      updateTeamsTable(response.data)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des équipes", error)
  }
}

/**
 * Met à jour le tableau des équipes
 * @param {Array} teams - Liste des équipes
 */
function updateTeamsTable(teams) {
  const container = document.querySelector(TEAM_ELEMENTS.teamsTable.container)

  if (!container) return

  // Vider le conteneur
  container.innerHTML = ""

  // Afficher l'état vide si aucune équipe
  if (!teams || !teams.length) {
    container.innerHTML = `
      <tr>
        <td colspan="6">
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="empty-state-text">
              <h3>Aucune équipe</h3>
              <p>Créez votre première équipe en cliquant sur le bouton "Créer une équipe".</p>
            </div>
          </div>
        </td>
      </tr>
    `
    return
  }

  // Ajouter chaque équipe
  teams.forEach((team) => {
    const row = document.createElement("tr")

    // Déterminer le statut et la classe
    let statusClass = "badge-success"
    let statusText = "Actif"

    if (team.status === "suspended") {
      statusClass = "badge-warning"
      statusText = "Suspendu"
    } else if (team.status === "inactive") {
      statusClass = "badge-danger"
      statusText = "Inactif"
    }

    row.innerHTML = `
      <td>${sanitizeText(team.name)}</td>
      <td><span class="badge badge-primary">${team.members_count || 0}</span></td>
      <td>${team.hackathons_count || 0}</td>
      <td>${team.challenges_completed || 0}</td>
      <td><span class="badge ${statusClass}">${statusText}</span></td>
      <td>
        <div class="dropdown">
          <button class="dropdown-toggle" data-id="${team.id}">
            <i class="fas fa-ellipsis-v"></i>
          </button>
        </div>
      </td>
    `
    container.appendChild(row)
  })
}


/**
 * Met à jour les statistiques des équipes
 * @param {Object} stats - Statistiques à afficher
 */
function updateTeamStats(stats) {
  if (!stats) return

  // Mettre à jour les compteurs
  const elements = TEAM_ELEMENTS.stats
  Object.keys(elements).forEach((key) => {
    const element = document.querySelector(elements[key])
    if (element && stats[key] !== undefined) {
      element.textContent = stats[key]
    }
  })
}

/**
 * Charge les équipes en vedette
 */
async function loadFeaturedTeams() {
  try {
    const response = await apiRequest("/admin/teams")

    if (response.success && response.data) {
      // Filtrer pour obtenir uniquement les équipes actives
      const featuredTeams = response.data
        .filter((team) => team.status === "active")
        .sort((a, b) => (b.challenges_completed || 0) - (a.challenges_completed || 0))
        .slice(0, 3) // Limiter à 3 équipes

      updateFeaturedTeams(featuredTeams)
    }
  } catch (error) {
    handleError("Erreur lors du chargement des équipes en vedette", error)
  }
}

/**
 * Met à jour les équipes en vedette
 * @param {Array} teams - Liste des équipes en vedette
 */
function updateFeaturedTeams(teams) {
  const containers = document.querySelectorAll(TEAM_ELEMENTS.featuredTeams.container)

  if (!containers || !containers.length) return

  // Afficher l'état vide si aucune équipe
  if (!teams || !teams.length) {
    containers.forEach((container) => {
      container.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="empty-state-text">
            <h3>Aucune équipe en vedette</h3>
            <p>Les équipes actives apparaîtront ici.</p>
          </div>
        </div>
      `
    })
    return
  }

  // Mettre à jour chaque conteneur d'équipe en vedette
  teams.forEach((team, index) => {
    if (index < containers.length) {
      const container = containers[index]

      // Déterminer le statut et la couleur
      let statusColor = "#10b981" // Vert pour actif
      let statusText = "Actif"

      if (team.status === "suspended") {
        statusColor = "#f59e0b" // Orange pour suspendu
        statusText = "Suspendu"
      } else if (team.status === "inactive") {
        statusColor = "#ef4444" // Rouge pour inactif
        statusText = "Inactif"
      }

      container.innerHTML = `
        <div style="display: flex; align-items: center; margin-bottom: 15px;">
          <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
            <i class="fas fa-users" style="color: #6d28d9;"></i>
          </div>
          <h3>${sanitizeText(team.name)}</h3>
        </div>
        <p>${team.members_count || 0} membre(s)</p>
        
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
          <div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
            <div style="font-weight: bold;">${team.hackathons_count || 0}</div>
          </div>
          <div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
            <div style="font-weight: bold;">${team.challenges_completed || 0}</div>
          </div>
          <div>
            <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
            <div style="font-weight: bold; color: ${statusColor};">${statusText}</div>
          </div>
        </div>
      `
    }
  })
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
  // Gestionnaire pour le bouton "Créer une équipe"
  const createTeamButton = document.querySelector(".btn-primary")
  if (createTeamButton) {
    createTeamButton.addEventListener("click", () => {
      // Rediriger vers la page de création d'équipe ou ouvrir un modal
      // window.location.href = "/admin/equipes/create.php"
      alert("Fonctionnalité de création d'équipe à implémenter")
    })
  }

  // Gestionnaire pour la recherche
  const searchInput = document.querySelector(TEAM_ELEMENTS.searchInput)
  if (searchInput) {
    searchInput.addEventListener("input", handleSearch)
  }

  //Gestion toggle de la modale de filtre 
  const filterIcon = document.querySelector('.filter-icon');
  const filterMenu = document.querySelector('.filter-menu')
  filterIcon?.addEventListener('click', ()=>{
    filterMenu.classList.toggle('visible')
  })

  // Initialiser les dropdowns
  initializeDropdowns()
}

/**
 * Édite une équipe
 * @param {string} id - ID de l'équipe
 */
async function editTeam(id) {
    document.querySelector('.dropdown-menu')?.remove();
    const team = teams.find(tm => tm.id == id);
    const leader = await apiRequest(`/users/${team.leader_id}`)
                    .then(response => {
                      if(response.success)return response.data
                    })
                    .catch(err=>handleError('Erreur lors de la récupération des memebres.', err));
  // Rediriger vers la page d'édition d'équipe
  const form =document.createElement('form');

  form.innerHTML=`
            <div class="flex" style="gap: 10px;">
              <div class="logo">${team.name[0].toUpperCase()}</div>
              <div style="width:100%;">
                <div>
                    <label for="team-name"> Nom de l'équipe</label><br>
                    <input required type="text" id="team-name" name="team-name" placeholder="Entrez un nom" value="${sanitizeText(team.name)}"/>
                </div>
                <div>
                    <label>Description</label><br>
                    <textarea required name="description" id="description" placeholder="Entrez une description">${sanitizeText(team.description)}</textarea>
                </div>
              </div>
            </div>                    
            
            <ul class="team-members">Equipe
                <div class="list">
                  ${renderMembers([leader], true)}
                  <div class="other-members"></div>
                </div>
                <div class="view-more">Voir tout</div>
            </ul>

            <div style="position: relative;">
              <label>Intégrer des membres</label>
              <input type="search" placeholder="Rechercher des participants" style="padding-left: 30px;"/>
              <i data-lucide="search" style="position: absolute; left: 10px; top: 58%;" width="15" height="15"></i>
            </div>

            <button type="submit" disabled class='valid-form' style="width: 49.5%; padding-block: 5px; border-radius: 5px; background: rgba(17, 102, 249, 0.84);">Valider</button>
            <button type="button" class="cancel" style="width: 49.5%; padding-block: 5px; border-radius: 5px; background: rgba(188, 188, 188, 0.84);">Annuler</button>
  `
  form.id="edit-form";
  form.dataset.id = id;
  form.dataset.lead=leader.id;

  form.setAttribute('enctype', 'multipart/form-data');
  document.body.appendChild(form)
  form.style.cssText=`
    top: calc((100vh - ${form.offsetHeight+'px'})/2);
    left: calc((100vw - ${form.offsetWidth+'px'})/2);
  `
  
  handleEditFormBtn()
  blurBackground();
  handleEditForm();
  lucide.createIcons();
}


function renderMembers(members, isLeader){
  if(members){
   return members.map(member =>`
      <li data-id='${member.id}'>
        ${member.profile_picture ? `<img class="profil rounded" src="/storage/${member.profile_picture}" alt="profil"/>` : `<div class="profil rounded">${member.username[0].toUpperCase()}</div>`}
        <div style="display: flex; gap: 10px; align-items: flex-start; width: 100%;">
          <div>
            <p>${sanitizeText(member.username)}</p>
            <p>${member.special_comp}</p> 
          </div>                    
          ${isLeader ? `<div class="lead-flag flex" style="gap:5px; transform: scale(0.8);">
           <p>Leader</p> 
            <i data-lucide="crown" width="15" height="15" style="transform:translateY(1px);"></i>
          </div>` : ''}
        </div>
        <div class="flex buttons-form">
          ${!isLeader ? `<button type="button" class="promote-leader flex"><span>Promouvoir</span> <i data-lucide="crown" width="15" height="15"></i></button>` : ''}
          <button type="button" class="remove-member flex" data-id='${member.id}'><span>Retirer</span> <i data-lucide="trash-2" width="15" height="15"></i></button>
        </div>                    
      </li>
    `);
  }
} 


function handleEditFormBtn(){
  const team = {
    id:document.getElementById('edit-form')?.dataset.id,
    lead: document.getElementById('edit-form')?.dataset.lead
  };

  document.querySelector('.cancel')?.addEventListener('click', ()=>{
    document.getElementById('edit-form')?.remove();
    document.querySelector('.overlay')?.remove();
  });

  const viewMore = document.querySelector('.view-more') ?? document.querySelector('.view-less');
  viewMore?.addEventListener('click', async()=>{
    if(viewMore.className == "view-more"){
      const members = await apiRequest(`/teams/${team.id}/members`)
                      .then(response => {
                        if(response.success)  return response.data;
                      })
                      .catch(err=>handleError('Erreur lors de la récupération des membres.', err));

      document.querySelector('.team-members .other-members').insertAdjacentHTML('beforeend', renderMembers(members.filter(member => member.id != team.lead), false));
      viewMore.className='view-less'
      viewMore.textContent="Voir moins"
      handleRemoveMember();
      lucide.createIcons();
    }else if(viewMore.className === "view-less"){
      document.querySelector('.team-members .other-members').innerHTML="";
      viewMore.className="view-more"
      viewMore.textContent="Voir tout"
    }
  });
}


function blurBackground(){
  const div = document.createElement('div');
  div.className="overlay";
  document.body.appendChild(div);
}

  function handleRemoveMember(members){
    let handler ={
      set: function(target, property, value, receiver){
        if(property === 'deleted' || property==='added'){
          disableButton();
        }

        return Reflect.set(target, property, value, receiver);
      }
    }

  const proxy = new Proxy(members, handler)
  
    const removeBtn = document.querySelectorAll('.remove-member');
    removeBtn.forEach(btn=> {
      btn.onclick = ()=> {
        const team = {
          id:document.getElementById('edit-form')?.dataset.id,
          lead: document.getElementById('edit-form')?.dataset.lead
        };
        const flag = document.querySelector(`li[data-id='${btn.dataset.id}'] .lead-flag`);
        const card = document.createElement('div');
        let memberCount= teams.find(tm => team.id == tm.id)?.members_count;
        if(flag && memberCount>1){
          card.innerHTML =`
            <h2>Attention!</h2>
            <p style="color: rgba(203, 203, 203, 0.89); text-align: left; margin-block: 10px;">Ce participant est leader de son équipe! Veuillez promouvoir un nouveau leader avant son retrait.</p>

            <button style="width: max-content; display: flex; justify-self: end; border-radius: 10px; padding: 7px 20px; background: rgba(82, 125, 254, 1); box-shadow: 0 0 5px 0 rgba(223, 223, 223, 0.67);" onclick="document.querySelector('.modale')?.remove()">Ok</button>
          `;
          card.className = 'modale';

        }else{
          card.innerHTML =`
          <h2>Veuillez confirmer la suppression <br>de ce membre!</h2>
          <div class="flex" style="width: 100%; margin-block: 15px 0; gap: 4%;">
            <button data-id='${btn.dataset.id}' class='confirm-remove' style="background: var(--alert);">Confirmer</button>
            <button onclick="document.querySelector('.remove-member-modal')?.remove()" style="background:var(--gray-fade); color: black;">Annuler</button>
          </div>
        `
          card.className = 'modale remove-member-modal';
        }
        
        document.body.appendChild(card);
        card.style.cssText=`
          top: calc((100vh - ${card.offsetHeight+'px'})/2);
          left: calc((100vw - ${card.offsetWidth+'px'})/2);        
        `
        const confirmRemove= document.querySelector('.confirm-remove');
        confirmRemove?.addEventListener('click', async()=>{       
        
          proxy.deleted=[...proxy.deleted, confirmRemove.dataset.id].flat(Infinity);

          const members = document.querySelector(`.team-members .list`);
          members.querySelector(`li[data-id="${confirmRemove.dataset.id}"]`)?.remove();
          document.querySelector('.view-more')?.classList.add('none');
          memberCount--;

          if(memberCount === 0){
            members.innerHTML=`
              <div class="flex" style="margin-block: 10px; gap: 7px; width:100%; padding: 10px; border-radius: 5px; background: #a54f4fa8;">
                <i data-lucide="info" height="19" width='19' stroke="#ffdfdf"></i>
                <p style="color: #ffdfdf;">Cette équipe sera supprimée après validation!</p>
              </div>

            `   
            lucide.createIcons();     
          }
          document.querySelector('.remove-member-modal')?.remove();
        });      
      };    
    });        
  }

function handleEditForm(){
  const name = {
   previousValue: document.getElementById('team-name').value,
   input: document.getElementById('team-name'),
   newValue:null
  }
  const description = {
    input: document.getElementById('description'),
    previousValue: document.getElementById('description').value,
    newValue: null
  };
  const members = {
    deleted:[],
    added:[]
  };
  
  handleRemoveMember(members);

  name.input.oninput = (e)=>{
    name.newValue=e.target.value;
    disableButton();
  }

  description.input.oninput = (e)=>{
    description.newValue=e.target.value;
    disableButton();
  }

  
  function disableButton(){
    const valid = document.querySelector('#edit-form .valid-form');

    valid.disabled = (!name.newValue) || 
                     (name.newValue.trim() === "") || 
                     (name.newValue === name.previousValue && (description.previousValue === description.newValue && description.previousValue.trim()!=='') && !members.deleted.length && !members.added.length); 
  
    if(members.deleted.length || members.added.length)
      valid.disabled = false;
  }
}

/**
 * Affiche les détails d'une équipe
 * @param {string} id - ID de l'équipe
 */
function viewTeam(id) {
  // Rediriger vers la page de détails d'équipe
  window.location.href = `/admin/equipes/view.php?id=${id}`
}

/**
 * Supprime une équipe
 * @param {string} id - ID de l'équipe
 */
async function deleteTeam(id) {
  if (!confirm("Êtes-vous sûr de vouloir supprimer cette équipe ?")) {
    return
  }

  try {
    showLoading()

    // Envoyer la requête de suppression
    const response = await apiRequest(`/admin/teams/${id}`, {
      method: "DELETE",
    })

    if (response.success) {
      // Recharger les équipes
      await loadTeams()

      // Afficher un message de succès
      showNotification("Équipe supprimée avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de la suppression de l'équipe", error)
  } finally {
    hideLoading()
  }
}

/**
 * Active une équipe
 * @param {string} id - ID de l'équipe
 */
async function activateTeam(id) {
  try {
    showLoading()

    // Envoyer la requête d'activation
    const response = await apiRequest(`/admin/teams/${id}/activate`, {
      method: "POST",
    })

    if (response.success) {
      // Recharger les équipes
      await loadTeams()

      // Afficher un message de succès
      showNotification("Équipe activée avec succès", "success")
    }
  } catch (error) {
    handleError("Erreur lors de l'activation de l'équipe", error)
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
const dropdownButton = document.querySelectorAll('.dropdown-toggle');
  dropdownButton?.forEach(button => {
    button?.addEventListener('click', (e)=>  {
      e.stopPropagation();
      const menu = document.querySelector('.dropdown-menu');
      if(menu) menu.remove();
        const div = document.createElement('ul');
          div.insertAdjacentHTML('afterbegin', `
            <li class="dropdown-item action-button" data-action="edit" data-id="${button.dataset.id}">Modifier</a>
            <li class="dropdown-item action-button" data-action="view" data-id="${button.dataset.id}">Voir détails</a>
            <li class="dropdown-item action-button" data-action="delete" data-id="${button.dataset.id}">Supprimer</a>
          `);
          div.className = 'dropdown-menu show on-window-click-close';
          document.querySelector('.table-container').parentElement.insertAdjacentElement('beforeend', div);
          div.style.cssText=`
            position: absolute;
            top:${button.parentElement.offsetTop}px;
            left:${ e.x - (div.offsetWidth + 30)}px;
            color: white;  
            z-index:500;
          `
          div.onclick=(e)=> e.stopPropagation();
          Array.from(div.childNodes).forEach(li => handleTeamsButtons(li));
    })
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
  initializeTeamPage()
})


/* !!!!!!!!!!!!!!!!!! */


/**
 * Charge les statistiques des équipes
 */

async function loadTeamStats(){
  try{
    await apiRequest('/admin/stats')
    .then(stats => {
        if(stats.success){
          updateTeamStats(stats.data)
        }
    }).catch(err => handleError('Erreur lors du chargement des statistiques.', err))
  }catch(err){
    console.error(err)
  }
}

/*!!!!!!!!!!!!!!!!!!*/

function filterTeams(){
}

function handleTeamsButtons(button){
    button.addEventListener("click", (e) => {
    const actionButton = e.target.closest(".action-button")
    if (actionButton) {
      const action = actionButton.dataset.action
      const id = actionButton.dataset.id
      
      switch (action) {
        case "edit":
          editTeam(id)
          break
        case "view":
          viewTeam(id)
          break
        case "delete":  
          deleteTeam(id)
          break
        case "activate":
          activateTeam(id)
          break
      }
    }
  })

}