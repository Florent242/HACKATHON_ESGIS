// Gestion des actions de notification
class NotificationActionManager {
  constructor() {
    this.actions = [];
    this.container = document.getElementById('notificationActions');
    this.setupEventListeners();
  }

  setupEventListeners() {
    const addButton = document.getElementById('addActionBtn');
    if (!addButton) return;

    // Supprimer d'abord tous les écouteurs existants
    addButton.replaceWith(addButton.cloneNode(true));

    document.getElementById('addActionBtn')?.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.addAction();
    });
  }

  addAction(action = null) {
    const actionId = Date.now();
    const actionData = action || { type: 'link', label: '', url: '' };

    const actionElement = document.createElement('div');
    actionElement.className = 'p-3 bg-slate-700/50 rounded-lg';
    actionElement.dataset.id = actionId;

    actionElement.innerHTML = `
      <div class="flex justify-between items-center mb-2">
        <select class="action-type-select border-slate-600 bg-slate-700 text-white rounded">
          <option value="link" ${actionData.type === 'link' ? 'selected' : ''}>Lien</option>
          <option value="modal" ${actionData.type === 'modal' ? 'selected' : ''}>Modal</option>
          <option value="api" ${actionData.type === 'api' ? 'selected' : ''}>API Call</option>
        </select>
        <button type="button" class="remove-action text-red-400 hover:text-red-300">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
      </div>
      <div class="action-config"></div>
    `;

    this.container.appendChild(actionElement);
    this.updateActionConfig(actionElement, actionData);
    this.setupActionEventListeners(actionElement, actionId, actionData);

    lucide.createIcons();
  }

  updateActionConfig(element, actionData) {
    const configContainer = element.querySelector('.action-config');
    let configHtml = '';

    switch (actionData.type) {
      case 'link':
        configHtml = `
          <div class="space-y-2">
            <input type="text" class="action-label w-full bg-slate-700 border-slate-600 text-white rounded" 
                   placeholder="Texte du bouton" value="${actionData.label || ''}">
            <input type="text" class="action-url w-full bg-slate-700 border-slate-600 text-white rounded mt-1" 
                   placeholder="URL" value="${actionData.url || ''}">
            <select class="action-target w-full bg-slate-700 border-slate-600 text-white rounded mt-1">
              <option value="_self" ${actionData.target === '_self' ? 'selected' : ''}>Même onglet</option>
              <option value="_blank" ${actionData.target === '_blank' ? 'selected' : ''}>Nouvel onglet</option>
            </select>
          </div>
        `;
        break;
      case 'modal':
        configHtml = `
          <div class="space-y-2">
            <input type="text" class="action-label w-full bg-slate-700 border-slate-600 text-white rounded" 
                   placeholder="Texte du bouton" value="${actionData.label || ''}">
            <input type="text" class="action-modal-id w-full bg-slate-700 border-slate-600 text-white rounded mt-1" 
                   placeholder="ID de la modal" value="${actionData.modal_id || ''}">
            <textarea class="action-params w-full bg-slate-700 border-slate-600 text-white rounded mt-1" 
                     placeholder="Paramètres (JSON)">${actionData.params ? JSON.stringify(actionData.params, null, 2) : ''}</textarea>
          </div>
        `;
        break;
      case 'api':
        configHtml = `
          <div class="space-y-2">
            <input type="text" class="action-label w-full bg-slate-700 border-slate-600 text-white rounded" 
                   placeholder="Texte du bouton" value="${actionData.label || ''}">
            <input type="text" class="action-endpoint w-full bg-slate-700 border-slate-600 text-white rounded mt-1" 
                   placeholder="Endpoint" value="${actionData.endpoint || ''}">
            <select class="action-method w-full bg-slate-700 border-slate-600 text-white rounded mt-1">
              <option value="GET" ${actionData.method === 'GET' ? 'selected' : ''}>GET</option>
              <option value="POST" ${actionData.method === 'POST' ? 'selected' : ''}>POST</option>
              <option value="PUT" ${actionData.method === 'PUT' ? 'selected' : ''}>PUT</option>
              <option value="DELETE" ${actionData.method === 'DELETE' ? 'selected' : ''}>DELETE</option>
            </select>
            <textarea class="action-params w-full bg-slate-700 border-slate-600 text-white rounded mt-1" 
                     placeholder="Paramètres (JSON)">${actionData.params ? JSON.stringify(actionData.params, null, 2) : ''}</textarea>
          </div>
        `;
        break;
    }

    configContainer.innerHTML = configHtml;
    lucide.createIcons();
  }

  setupActionEventListeners(element, actionId, actionData) {
    const typeSelect = element.querySelector('.action-type-select');
    const removeBtn = element.querySelector('.remove-action');

    typeSelect.addEventListener('change', (e) => {
      actionData.type = e.target.value;
      this.updateActionConfig(element, actionData);
    });

    removeBtn.addEventListener('click', () => {
      element.remove();
      this.actions = this.actions.filter(a => a.id !== actionId);
    });
  }

  getActions() {
    const actions = [];
    const actionElements = this.container.querySelectorAll('[data-id]');

    actionElements.forEach(element => {
      const type = element.querySelector('.action-type-select').value;
      const action = { type };

      switch (type) {
        case 'link':
          action.label = element.querySelector('.action-label')?.value || '';
          action.url = element.querySelector('.action-url')?.value || '';
          action.target = element.querySelector('.action-target')?.value || '_self';
          break;

        case 'modal':
          action.label = element.querySelector('.action-label')?.value || '';
          action.modal_id = element.querySelector('.action-modal-id')?.value || '';
          try {
            const params = element.querySelector('.action-params')?.value;
            action.params = params ? JSON.parse(params) : {};
          } catch (e) {
            console.error('Erreur de parsing des paramètres JSON', e);
            action.params = {};
          }
          break;

        case 'api':
          action.label = element.querySelector('.action-label')?.value || '';
          action.endpoint = element.querySelector('.action-endpoint')?.value || '';
          action.method = element.querySelector('.action-method')?.value || 'POST';
          try {
            const params = element.querySelector('.action-params')?.value;
            action.params = params ? JSON.parse(params) : {};
          } catch (e) {
            console.error('Erreur de parsing des paramètres JSON', e);
            action.params = {};
          }
          break;
      }

      actions.push(action);
    });

    return actions;
  }

  validateActions() {
    const actions = this.getActions();
    let isValid = true;

    actions.forEach((action, index) => {
      const element = this.container.querySelector(`[data-id]:nth-child(${index + 1})`);

      if (!action.label) {
        showError(element.querySelector('.action-label') || element, 'Le libellé est requis');
        isValid = false;
      }

      switch (action.type) {
        case 'link':
          if (!action.url) {
            showError(element.querySelector('.action-url'), 'L\'URL est requise');
            isValid = false;
          }
          break;
        case 'modal':
          if (!action.modal_id) {
            showError(element.querySelector('.action-modal-id'), 'L\'ID de la modal est requis');
            isValid = false;
          }
          break;
        case 'api':
          if (!action.endpoint) {
            showError(element.querySelector('.action-endpoint'), 'L\'endpoint est requis');
            isValid = false;
          }
          break;
      }
    });

    return isValid;
  }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
  if (!window.notificationActionManager) {
    window.notificationActionManager = new NotificationActionManager();
  }
});
// Configuration
const ITEMS_PER_PAGE = 10;
let currentPage = 1;
let currentSort = { field: 'created_at', order: 'desc' };
let totalPages = 1;
let selectedUsers = new Set();
let currentUserId = null;
let tabClickHandler = null;
let allUsers = [];
let filteredUsers = [];

// Initialisation
document.addEventListener('DOMContentLoaded', function () {
  initializePage();
  setupEventListeners();

});

function initializePage() {
  loadUsers(1);
  loadTeams();
  loadUserStats();
}

function sortUsers(field) {
  if (currentSort.field === field) {
    // Inverser l'ordre de tri si on clique sur la même colonne
    currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
  } else {
    // Nouvelle colonne, tri ascendant par défaut
    currentSort.field = field;
    currentSort.order = 'asc';
  }
  loadUsers(1); // Retour à la première page lors du tri
}

function updateSortIcons(activeColumn) {
  document.querySelectorAll('.sortable').forEach(header => {
    const icon = header.querySelector('i');
    if (header.dataset.column === activeColumn) {
      icon.className = currentSort.order === 'asc' ? 'fas fa-sort-up' : 'fas fa-sort-down';
      icon.style.visibility = 'visible';
    } else {
      icon.className = 'fas fa-sort';
      icon.style.visibility = 'hidden';
    }
  });
}

function setupEventListeners() {
  // Recherche
  const searchInput = document.getElementById('searchInput');
  let searchTimeout;
  searchInput?.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      currentPage = 1;
      loadUsers(1);
    }, 500);
  });

  // Filtres
  document.querySelectorAll('#roleFilter, #statusFilter, #teamFilter').forEach(select => {
    select.addEventListener('change', () => {
      currentPage = 1;
      loadUsers(1);
    });
  });

  // Tri des colonnes
  document.querySelectorAll('[data-sort]').forEach(header => {
    header.addEventListener('click', () => {
      const field = header.dataset.sort;
      sortUsers(field);
    });
  });

  // Réinitialisation des filtres
  document.getElementById('resetFiltersBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    document.querySelectorAll('#roleFilter, #statusFilter, #teamFilter, #searchInput').forEach(input => {
      input.value = '';
    });
    currentPage = 1;
    refreshDisplayedData();
  });

  // Sélection globale
  document.getElementById('selectAll')?.addEventListener('change', (e) => {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => {
      checkbox.checked = e.target.checked;
      toggleUserSelection(checkbox);
    });
    updateBulkActions();
  });

  // Bouton d'export
  document.getElementById('exportUsersBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    exportUsers();
  });

  // Bouton d'ajout
  document.getElementById('addUserBtn')?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    showUserModal();
  });

  // Bouton Annuler
  const cancelBtn = document.getElementById('cancelModalBtn');
  if (cancelBtn) {
    // Ajouter le nouvel écouteur
    cancelBtn.addEventListener('click', closeUserModal);
  }

  // Bouton closemodal
  const closeBtn = document.getElementById('closeModalBtn');
  if (closeBtn) {
    // Ajouter le nouvel écouteur
    closeBtn.addEventListener('click', closeUserModal);
  }

  // Fermeture avec la touche Échap
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeUserModal();
    }
  });

  // Bouton d'actions de masse
  document.getElementById('bulkActionsBtn')?.addEventListener('click', (e) => {
    if (selectedUsers.size > 0) {
      showBulkActionsModal();
    }
  });

  // faire en sorte que si on selectionne un bulkaction on rende le boutton utilisable et inversement
  document.getElementById('bulkAction')?.addEventListener('change', (e) => {
    e.preventDefault();
    e.stopPropagation();
    const confirmBtn = document.getElementById('confirmBulkAction');
    confirmBtn.disabled = e.target.value === '';
    confirmBtn.classList.toggle('disabled', e.target.value === '');
  });

  // Gestion des onglets
  const tabEls = document.querySelectorAll('button[data-bs-toggle="tab"]');
  tabEls.forEach(tabEl => {
    tabEl.addEventListener('shown.bs.tab', (event) => {
      if (event.target.id === 'activity-tab' && currentUserId) {
        loadUserActivity(currentUserId);
      }
    });
  });

  // Soumission du formulaire
  document.getElementById('userForm')?.addEventListener('submit', async (e) => {
    await handleUserFormSubmit(e);
  });

  // Actions de masse - Sélection d'action
  document.getElementById('bulkAction')?.addEventListener('change', (e) => {
    e.preventDefault();
    e.stopPropagation();
    const roleContainer = document.getElementById('bulkRoleContainer');
    const warningContainer = document.getElementById('bulkActionWarning');
    const confirmBtn = document.getElementById('confirmBulkAction');

    roleContainer.classList.toggle('hidden', e.target.value !== 'change_role');

    let warning = '';
    switch (e.target.value) {
      case 'delete':
        warning = 'Attention : Cette action supprimera définitivement les utilisateurs sélectionnés.';
        break;
      case 'suspend':
        warning = 'Les utilisateurs ne pourront plus se connecter jusqu\'à ce que vous les réactiviez.';
        break;
      case 'activate':
        warning = 'Les utilisateurs sélectionnés seront réactivés.';
        break;
      case 'deactivate':
        warning = 'Les utilisateurs sélectionnés seront désactivés.';
        break;
      case 'change_role':
        warning = 'Le rôle sera changé pour tous les utilisateurs sélectionnés.';
        break;
      case 'send_notification':
        warning = 'Une notification sera envoyée aux utilisateurs sélectionnés.';
        break;
    }

    if (warning) {
      warningContainer.textContent = warning;
      warningContainer.classList.remove('hidden');
    } else {
      warningContainer.classList.add('hidden');
    }

    confirmBtn.disabled = !e.target.value;
  });

  // Actions de masse - Confirmation
  document.getElementById('confirmBulkAction')?.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    handleBulkAction(e);
  });

  // Validation des mots de passe
  document.getElementById('password_confirmation')?.addEventListener('input', (e) => {
    e.preventDefault();
    e.stopPropagation();
    validatePasswordMatch(e);
  });
  document.getElementById('password')?.addEventListener('input', (e) => {
    e.preventDefault();
    e.stopPropagation();
    validatePasswordMatch(e);
  });

  document.getElementById('createNotificationBtn')?.addEventListener('click', () => {
    // Réinitialiser le formulaire
    const form = document.getElementById('notificationForm');
    if (form) form.reset();

    // Réinitialiser le gestionnaire d'actions si nécessaire
    if (window.notificationActionManager) {
      window.notificationActionManager = new NotificationActionManager();
    }

    // Ouvrir le modal
    openNotificationModal();

    // Optionnel : Mettre le focus sur le premier champ
    const firstInput = document.querySelector('#notificationModal input, #notificationModal select, #notificationModal textarea');
    if (firstInput) firstInput.focus();
  });

  // Gestionnaires d'événements
  document.getElementById('closeNotificationModal')?.addEventListener('click', closeNotificationModal);
  document.getElementById('cancelNotificationBtn')?.addEventListener('click', closeNotificationModal);

  // Gestionnaire d'événement pour le bouton d'envoi
  document.addEventListener('click', async function (e) {
    // Gestion du clic sur le bouton d'envoi de notification dans le menu
    if (e.target.closest('.send-notification')) {
      e.preventDefault();
      const userId = parseInt(e.target.closest('tr').getAttribute('data-user-id'));
      if (userId) {
        selectedUsers.clear();
        selectedUsers.add(userId);
        openNotificationModal([userId]);
      }
    }

    // Gestion du clic sur le bouton "Envoyer la notification" dans le modal
    if (e.target.closest('#sendNotificationBtn')) {
      const form = document.getElementById('notificationForm');
      const formData = new FormData(form);

      if (!formData.get('title') || !formData.get('message')) {
        showNotification('Attention', 'Veuillez remplir tous les champs obligatoires', 'error');
        return;
      }

      await handleSendNotification(e);
    }
  });

  document.getElementById('notificationScope')?.addEventListener('change', handleScopeChange);

  // Gestion de l'envoi de notification
  document.getElementById('sendNotificationBtn')?.addEventListener('click', (e) => handleSendNotification(e));

  // Initialisation du formulaire de notification
  initNotificationForm();

  lucide.createIcons();
}

// Fonction pour femer la modal
function closeUserModal() {
  const modal = document.getElementById('userModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.classList.remove('modal-open');
  }
}

// Fonction pour gérer le changement de scope
function handleScopeChange() {
  const scope = this.value;
  const userField = document.getElementById('userField');
  const teamField = document.getElementById('teamField');
  const hackathonField = document.getElementById('hackathonField');

  // Masquer tous les champs
  userField.classList.add('hidden');
  teamField.classList.add('hidden');
  hackathonField.classList.add('hidden');

  // Afficher le champ correspondant au scope
  if (scope === 'user') {
    userField.classList.remove('hidden');
    if (!window.usersLoaded) {
      loadUsersForSelect();
    }
  } else if (scope === 'team') {
    teamField.classList.remove('hidden');
    if (!window.teamsLoaded) {
      loadTeamsForSelect();
    }
  } else if (scope === 'hackathon') {
    hackathonField.classList.remove('hidden');
    if (!window.hackathonsLoaded) {
      loadHackathonsForSelect();
    }
  }
}

// Fonction pour charger les utilisateurs dans le select
async function loadUsersForSelect() {
  try {
    const select = document.getElementById('notificationUser');
    if (!select) return;

    const response = await apiRequest('/users/all?limit=1000');
    select.innerHTML = Array.from(response.data)?.map(user =>
      `<option value="${user.id}">${user.username || user.email} == (${user.role || 'Utilisateur'})</option>`
    ).join('');
    usersLoaded = true;
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error);
    throw error;
  }
}


// Fonction pour charger les équipes dans le select
async function loadTeamsForSelect() {
  try {
    const response = await apiRequest('/teams');
    const select = document.getElementById('notificationTeam');
    select.innerHTML = Array.from(response.data)?.map(team =>
      `<option value="${team.id}">${team.name}</option>`
    ).join('');
    window.teamsLoaded = true;
  } catch (error) {
    console.error('Erreur lors du chargement des équipes:', error);
  }
}

// Fonction pour charger les hackathons dans le select
async function loadHackathonsForSelect() {
  try {
    const response = await apiRequest('/hackathons');
    const select = document.getElementById('notificationHackathon');
    select.innerHTML = Array.from(response.data)?.map(hackathon =>
      `<option value="${hackathon.id}">${hackathon.name} (${new Date(hackathon.start_date).getFullYear()})</option>`
    ).join('');
    window.hackathonsLoaded = true;
  } catch (error) {
    console.error('Erreur lors du chargement des hackathons:', error);
  }
}

// Fonction pour gérer l'envoi de notification
async function handleSendNotification(e) {
  e.preventDefault();
  e.stopPropagation();

  // Valider les actions
  if (!window.notificationActionManager.validateActions()) {
    showNotification('Erreur dans les actions', 'Veuillez corriger les erreurs dans les actions', 'error');
    return;
  }

  const form = document.getElementById('notificationForm');
  const formData = new FormData(form);

  // Validation
  if (!formData.get('title') || !formData.get('message')) {
    showNotification('Formulaire invalide', 'Veuillez remplir tous les champs obligatoires', 'error');
    return;
  }

  try {
    const actions = window.notificationActionManager.getActions();
    const notificationData = {
      scope: formData.get('scope'),
      title: formData.get('title'),
      message: formData.get('message'),
      type: formData.get('type'),
      important: formData.get('important') === 'on',
      send_email: formData.get('send_email') === 'on',
      action: actions.length > 0 ? actions[0] : null
    };

    // Ajouter les paramètres spécifiques au scope
    if (notificationData.scope === 'user') {
      notificationData.user_id = formData.get('user_id');
    } else if (notificationData.scope === 'team') {
      notificationData.team_id = formData.get('team_id');
    } else if (notificationData.scope === 'hackathon') {
      notificationData.hackathon_id = formData.get('hackathon_id');
    } else if (notificationData.scope === 'selected') {
      notificationData.user_ids = Array.from(selectedUsers).join(',');
    }

    const response = await apiRequest('/notifications/user', {
      method: 'POST',
      body: JSON.stringify(notificationData)
    });

    if (response.success) {
      showNotification('Succès', 'Notification(s) envoyée(s) avec succès', 'success');
      closeNotificationModal();
    } else {
      throw new Error(response.error || 'Erreur lors de l\'envoi de la notification');
    }
  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur', error.message || 'Une erreur est survenue', 'error');
  }
}

// Fonction pour ouvrir le modal de notification
function openNotificationModal(userIds = []) {
  const modal = document.getElementById('notificationModal');
  const scopeSelect = document.getElementById('notificationScope');
  const recipientsInfo = document.getElementById('notificationRecipientsInfo');
  const recipientsCount = document.getElementById('notificationRecipientsCount');

  if (!modal || !scopeSelect || !recipientsInfo || !recipientsCount) {
    console.error('Éléments du modal de notification introuvables');
    return;
  }

  // Réinitialiser le formulaire
  const form = document.getElementById('notificationForm');
  if (form) form.reset();

  // Si des IDs sont fournis, on présélectionne le scope "Sélection d'utilisateurs"
  if (userIds && userIds.length > 0) {
    scopeSelect.value = 'selected';
    if (recipientsInfo) {
      recipientsInfo.textContent = `${userIds.length} utilisateur(s) sélectionné(s)`;
      recipientsInfo.classList.remove('hidden');
    }
    if (recipientsCount) {
      recipientsCount.textContent = userIds.length;
    }
  } else {
    scopeSelect.value = 'user';
    recipientsInfo.classList.add('hidden');
    recipientsCount.textContent = '0';
  }

  // Déclencher le changement de scope
  const event = new Event('change');
  scopeSelect.dispatchEvent(event);

  // Afficher le modal
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

// Fonction pour fermer le modal de notification
function closeNotificationModal() {
  const modal = document.getElementById('notificationModal');
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }

  selectedUsers.clear();
  refreshDisplayedData();
  updateBulkActions();
}

// Gestionnaire pour le bouton d'annulation
document.getElementById('cancelNotificationBtn')?.addEventListener('click', closeNotificationModal);

// Fermer en cliquant en dehors du modal
document.getElementById('notificationModal')?.addEventListener('click', (e) => {
  if (e.target === document.getElementById('notificationModal')) {
    closeNotificationModal();
  }
});

// Initialisation du formulaire de notification
function initNotificationForm() {
  const scopeSelect = document.getElementById('notificationScope');
  if (scopeSelect) {
    // Charger les données nécessaires
    loadUsersForSelect();
    loadTeamsForSelect();
    loadHackathonsForSelect();
  }
}

// Chargement des données
async function loadUsers(page = 1) {
  try {
    showTableLoading();
    currentPage = page;

    const params = new URLSearchParams({
      page: page,
      per_page: ITEMS_PER_PAGE,
      search: document.getElementById('searchInput')?.value || '',
      role: document.getElementById('roleFilter')?.value || '',
      status: document.getElementById('statusFilter')?.value || '',
      team: document.getElementById('teamFilter')?.value || '',
      sort: currentSort.field,
      order: currentSort.order
    });

    const response = await apiRequest(`/admin/users?${params.toString()}`);

    if (response.success) {
      renderUsersTable(response.data);
      updatePagination(response.meta);
      updateTableInfo(response.meta);
    } else {
      throw new Error(response.message || 'Erreur lors du chargement des utilisateurs');
    }
  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Impossible de charger les utilisateurs - ' + error.message, null, 'error');
    showEmptyTable();
  }
}

/**
 * Met à jour les données sans recharger depuis le serveur
 */
function refreshDisplayedData() {
  loadUsers(currentPage);
}

async function loadUserStats() {
  try {
    const response = await apiRequest('/admin/users/stats');

    if (response.success) {
      const stats = response.data;

      document.getElementById('totalUsers').textContent = stats.total_users || 0;
      document.getElementById('activeUsers').textContent = stats.active_users || 0;
      document.getElementById('inactiveUsers').textContent = stats.inactive_users || 0;
      document.getElementById('suspendedUsers').textContent = stats.suspended_users || 0;
      document.getElementById('bannedUsers').textContent = stats.banned_users || 0;
      document.getElementById('newUsersThisWeek').textContent = stats.new_users_this_week || 0;
    }
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques:', error);
  }
}

async function loadTeams() {
  try {
    const response = await apiRequest('/admin/teams');

    if (response.success) {
      const teams = response.data;
      const select = document.getElementById('teamFilter');
      if (select) {
        select.innerHTML = '<option value="">Toutes les équipes</option>';
        teams.forEach(team => {
          const option = document.createElement('option');
          option.value = team.id;
          option.textContent = team.name;
          select.appendChild(option);
        });
      }
    }
  } catch (error) {
    console.error('Erreur lors du chargement des équipes:', error);
  }
}

async function loadUserActivity(userId) {
  try {
    const activityFeed = document.getElementById('userActivityFeed');
    if (!activityFeed) return;

    activityFeed.innerHTML = `
    <div class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <i data-lucide="loader" class="w-5 h-5"></i>
      </div>
      <span class="visually-hidden">Chargement...</span>
    </div>`;

    const response = await apiRequest(`/admin/users/${userId}/activities`);

    if (response.success) {
      renderActivityFeed(response.data);
    } else {
      throw new Error(response.message || 'Erreur lors du chargement de l\'activité');
    }
  } catch (error) {
    console.error('Erreur:', error);
    const activityFeed = document.getElementById('userActivityFeed');
    if (activityFeed) {
      activityFeed.innerHTML = `
        <div class="alert alert-danger">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Impossible de charger l'activité de l'utilisateur. ${error.message}
        </div>
      `;
    }
  }
}

function renderUsersTable(users) {
  const tbody = document.getElementById('usersTableBody');
  if (!tbody) return;

  if (!users || users.length === 0) {
    showEmptyTable();
    return;
  }

  const template = document.getElementById('userRowTemplate');
  tbody.innerHTML = '';

  users.forEach(user => {
    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('tr');
    row.dataset.userId = user.id;

    // Remplir les données utilisateur
    const fullName = getFullName(user) || user.username;
    clone.querySelector('.user-name').textContent = fullName;
    clone.querySelector('.user-id').textContent = `#${user.id}`;
    clone.querySelector('.user-email').textContent = user.email;

    // Rôle
    const roleBadge = clone.querySelector('.user-role-badge');
    roleBadge.textContent = getRoleLabel(user.role || 'user');
    roleBadge.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getRoleBadgeClass(user.role || 'user')}`;

    // Statut
    const statusBadge = clone.querySelector('.user-status-badge');
    statusBadge.textContent = getStatusLabel(user.status || 'inactive');
    statusBadge.className = `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusBadgeClass(user.status || 'inactive')}`;

    // Équipe
    const teamCell = clone.querySelector('.user-team');
    if (user.team_name) {
      teamCell.innerHTML = `<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">${user.team_name}</span>`;
    }

    // Dernière connexion
    const lastLoginCell = clone.querySelector('.user-last-login');
    const lastIpCell = clone.querySelector('.user-last-ip');

    if (user.last_login_at) {
      lastLoginCell.textContent = formatDate(user.last_login_at);
      lastIpCell.textContent = user.last_login_ip || '';
    } else {
      lastLoginCell.textContent = 'Connexion unregistered';
    }

    // Avatar
    const avatar = clone.querySelector('.user-avatar');
    const initials = fullName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    avatar.src = user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&background=random`;
    avatar.alt = fullName;

    // Gestion des événements
    setupUserRowEventListeners(row, user);
    tbody.appendChild(row);
  });

  // Mettre à jour les actions groupées
  updateBulkActions();
}

function setupUserRowEventListeners(row, user) {
  // Case à cocher
  const checkbox = row.querySelector('.user-checkbox');
  if (checkbox) {
    checkbox.addEventListener('change', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (e.target.checked) {
        if (!selectedUsers.has(user.id)) selectedUsers.add(user.id);
      } else {
        selectedUsers.delete(user.id);
      }
      updateBulkActions();
    });
  }

  // Cocher case si ligne cliquer
  row.addEventListener('click', (e) => {
    e.stopPropagation();
    const checkbox = row.querySelector('.user-checkbox');
    if (checkbox) {
      checkbox.checked = !checkbox.checked;
      checkbox.dispatchEvent(new Event('change'));
    }
  });

  // Menu déroulant
  const menuButton = row.querySelector('[aria-haspopup="true"]');
  const menu = row.querySelector('.dropdown-menu-li');

  if (menuButton && menu) {
    menuButton.addEventListener('click', (e) => {
      e.stopPropagation();
      const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', !isExpanded);
      menu.classList.toggle('hidden', isExpanded);
    });

    // Fermer le menu quand on clique ailleurs
    document.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!row.contains(e.target)) {
        menuButton.setAttribute('aria-expanded', 'false');
        menu.classList.add('hidden');
      }
    });
  }

  // Actions du menu
  const actions = {
    '.edit-user': () => showUserModal(user.id),
    '.view-activity': () => showUserModal(user.id, 'activity'),
    '.reset-password': () => resetUserPassword(user.id, user.username),
    '.toggle-status': (e) => {
      e.preventDefault();
      const newStatus = user.status === 'active' ? 'inactive' : 'active';
      toggleUserStatus(user.id, newStatus, user.username);
    },
    '.delete-user': (e) => {
      e.preventDefault();
      confirmDeleteUser(user.id, user.username);
    }
  };

  Object.entries(actions).forEach(([selector, handler]) => {
    const element = row.querySelector(selector);
    if (element) {
      element.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        handler(e);
      });
    }
  });

  lucide.createIcons();
}

function setupUserActions(clone, user) {
  clone.querySelector('.edit-user').addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    showUserModal(user.id);
  });

  clone.querySelector('.view-activity').addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    showUserModal(user.id, 'activity');
  });

  clone.querySelector('.reset-password').addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    resetUserPassword(user.id, user.username);
  });

  const toggleStatusBtn = clone.querySelector('.toggle-status');
  const statusText = clone.querySelector('.status-action-text');

  if (user.status === 'active') {
    statusText.textContent = 'Suspendre';
    toggleStatusBtn.querySelector('i').className = 'fas fa-user-slash me-2';
  } else if (user.status === 'suspended') {
    statusText.textContent = 'Activer';
    toggleStatusBtn.querySelector('i').className = 'fas fa-user-check me-2';
  } else {
    statusText.textContent = 'Changer le statut';
  }

  toggleStatusBtn.addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    const newStatus = user.status === 'active' ? 'suspend' : 'activate';
    toggleUserStatus(user.id, newStatus, user.username);
  });

  clone.querySelector('.delete-user').addEventListener('click', (e) => {
    e.preventDefault();
    e.stopPropagation();
    confirmDeleteUser(user.id, user.username);
  });
}

function showEmptyTable() {
  const tbody = document.getElementById('usersTableBody');
  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="d-flex flex-column align-items-center">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted mb-0">Aucun utilisateur trouvé</p>
          </div>
        </td>
      </tr>
    `;
  }
}

function showTableLoading() {
  const tbody = document.getElementById('usersTableBody');
  if (tbody) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <i data-lucide="loader" class="w-5 h-5"></i>
          </div>
          <span class="visually-hidden">Chargement...</span>
        </td>
      </tr>
    `;
  }
}

function renderActivityFeed(activities) {
  const container = document.getElementById('userActivityFeed');
  if (!container) return;

  const template = document.getElementById('activityItemTemplate');
  container.innerHTML = '';

  if (!activities || activities.length === 0) {
    container.innerHTML = `
      <div class="text-center py-8 text-gray-400">
        <i class="fas fa-inbox text-3xl mb-2"></i>
        <p class="text-sm">Aucune activité récente</p>
      </div>
    `;
    return;
  }

  // gerer l'onglet activity
  const activityTab = document.getElementById('activity-tab');
  if (activityTab) {
    const isExpanded = activityTab.getAttribute('aria-expanded') === 'true';
    activityTab.classList.toggle('hidden', isExpanded);
    activityTab.setAttribute('aria-expanded', !isExpanded);
  }
  // Mappage des icônes selon le type d'activité
  const iconMap = {
    'login': 'fa-sign-in-alt',
    'logout': 'fa-sign-out-alt',
    'profile_update': 'fa-user-edit',
    'password_change': 'fa-key',
    'team_join': 'fa-users',
    'challenge_complete': 'fa-flag-checkered',
    'project_submit': 'fa-paper-plane',
    'default': 'fa-circle'
  };

  activities.forEach(activity => {
    const clone = template.content.cloneNode(true);
    const action = activity.action || 'default';

    // Définir l'icône
    const icon = clone.querySelector('[data-icon]');
    icon.className = `fas text-lg ${iconMap[action] || iconMap.default}`;

    // Titre et détails
    clone.querySelector('[data-title]').textContent = getActivityTitle(action, activity);
    clone.querySelector('[data-details]').textContent = activity.description || '';

    // Date formatée
    if (activity.created_at) {
      const date = new Date(activity.created_at);
      clone.querySelector('[data-time]').textContent = formatTimeAgo(date);
    }

    // Adresse IP
    const ipElement = clone.querySelector('[data-ip-text]');
    if (ipElement) {
      ipElement.textContent = activity.ip_address || 'IP inconnue';
    } else {
      clone.querySelector('[data-ip]').classList.add('hidden');
    }

    // Appareil
    const deviceElement = clone.querySelector('[data-device-text]');
    if (deviceElement && activity.user_agent) {
      deviceElement.textContent = getDeviceInfo(activity.user_agent);
    } else {
      clone.querySelector('[data-device]').classList.add('hidden');
    }

    container.appendChild(clone);
  });
}

// Fonction utilitaire pour formater la date en "il y a X temps"
function formatTimeAgo(date) {
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) return 'À l\'instant';
  if (diffInSeconds < 3600) {
    const mins = Math.floor(diffInSeconds / 60);
    return `Il y a ${mins} min${mins > 1 ? 's' : ''}`;
  }
  if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600);
    return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
  }

  // Si plus d'un jour, afficher la date complète
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Fonction pour générer un titre d'activité lisible
function getActivityTitle(action, activity) {
  const titles = {
    'login': 'Connexion',
    'logout': 'Déconnexion',
    'profile_update': 'Mise à jour du profil',
    'password_change': 'Changement de mot de passe',
    'team_join': 'A rejoint une équipe',
    'challenge_complete': 'Défi complété',
    'project_submit': 'Projet soumis'
  };

  let title = titles[action] || 'Activité récente';

  // Ajouter des détails spécifiques si disponibles
  if (action === 'challenge_complete' && activity.challenge_name) {
    title += ` : ${activity.challenge_name}`;
  } else if (action === 'team_join' && activity.team_name) {
    title += ` : ${activity.team_name}`;
  }

  return title;
}

// Fonction pour obtenir des informations sur l'appareil (version simplifiée)
function getDeviceInfo(userAgent) {
  if (!userAgent) return 'Appareil inconnu';

  const ua = userAgent.toLowerCase();
  if (ua.includes('mobile')) return 'Mobile';
  if (ua.includes('tablet')) return 'Tablette';
  if (ua.includes('windows')) return 'Windows';
  if (ua.includes('mac')) return 'Mac';
  if (ua.includes('linux')) return 'Linux';

  return 'Ordinateur';
}

/**
 * Met à jour les contrôles de pagination
 */
function updatePagination(meta) {
  const paginationContainer = document.getElementById('pagination');
  if (!paginationContainer) return;

  const { current_page, last_page } = meta;
  let paginationHTML = '';

  // Bouton Précédent
  paginationHTML += `
    <button 
      onclick="loadUsers(${current_page - 1})" 
      ${current_page === 1 ? 'disabled' : ''}
      class="p-2 rounded-md ${current_page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-100'}"
    >
      <i data-lucide="chevron-left" class="w-4 h-4"></i>
    </button>
  `;

  // Afficher les numéros de page
  const maxPagesToShow = 5;
  let startPage = Math.max(1, current_page - Math.floor(maxPagesToShow / 2));
  let endPage = Math.min(last_page, startPage + maxPagesToShow - 1);

  if (endPage - startPage + 1 < maxPagesToShow) {
    startPage = Math.max(1, endPage - maxPagesToShow + 1);
  }

  // Premier bouton de page
  if (startPage > 1) {
    paginationHTML += createPageButton(1);
    if (startPage > 2) {
      paginationHTML += '<span class="px-2 py-1">...</span>';
    }
  }

  // Boutons des pages
  for (let i = startPage; i <= endPage; i++) {
    paginationHTML += createPageButton(i);
  }

  // Dernier bouton de page
  if (endPage < last_page) {
    if (endPage < last_page - 1) {
      paginationHTML += '<span class="px-2 py-1">...</span>';
    }
    paginationHTML += createPageButton(last_page);
  }

  // Bouton Suivant
  paginationHTML += `
    <button 
      onclick="loadUsers(${current_page + 1})" 
      ${current_page === last_page ? 'disabled' : ''}
      class="p-2 rounded-md ${current_page === last_page ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-100'}"
    >
      <i data-lucide="chevron-right" class="w-4 h-4"></i>
    </button>
  `;

  paginationContainer.innerHTML = paginationHTML;
  lucide.createIcons();
}

// Mettre à jour la fonction createPageButton
function createPageButton(pageNumber) {
  const isActive = pageNumber === currentPage;
  return `
    <button 
      onclick="loadUsers(${pageNumber})" 
      class="w-10 h-10 rounded-md ${isActive
      ? 'bg-blue-600 text-white'
      : 'text-slate-600 hover:bg-slate-100'
    }"
      ${isActive ? 'aria-current="page"' : ''}
    >
      ${pageNumber}
    </button>
  `;
}


/**
 * Met à jour les informations du tableau (ex: "Affichage de 1 à 10 sur 50 entrées")
 */
function updateTableInfo(meta) {
  const tableInfo = document.getElementById('tableInfo');
  if (!tableInfo) return;

  const { from, to, total } = meta;
  tableInfo.textContent = `Affichage de ${from} à ${to} sur ${total} entrée${total !== 1 ? 's' : ''}`;
}

(async function initSchoolSelect() {
  const schoolSelect = document.getElementById('school');
  const schoolError = document.getElementById('schoolError');
  if (!schoolSelect) return;

  function populateSchools(list) {
    for (let i = schoolSelect.options.length - 1; i >= 1; i--) {
      schoolSelect.remove(i);
    }
    (list || []).forEach(name => {
      if (!name || typeof name !== 'string') return;
      const opt = document.createElement('option');
      opt.value = name;
      opt.textContent = name;
      schoolSelect.appendChild(opt);
    });
  }

  try {
    async function loadSchoolsScript() {
      if (window.SCHOOLS) return;
      if (document.querySelector('script[data-schools="true"]')) return new Promise((res) => {
        if (window.SCHOOLS) return res();
        document.addEventListener('schools:loaded', () => res(), { once: true });
      });

      await new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = '/assets/schools.js';
        s.async = true;
        s.defer = true;
        s.dataset.schools = 'true';
        s.onload = () => {
          document.dispatchEvent(new CustomEvent('schools:loaded'));
          resolve();
        };
        s.onerror = () => reject(new Error('Impossible de charger /assets/schools.js'));
        document.head.appendChild(s);
      });
    }

    await loadSchoolsScript();

    const raw = window.SCHOOLS;
    const schools = Array.isArray(raw) ? raw : (Array.isArray(raw?.schools) ? raw.schools : []);
    if (!schools.length) throw new Error('Aucune école dans window.SCHOOLS');

    schools.sort((a, b) => ('' + a).localeCompare(b));
    populateSchools(schools);
  } catch (e) {
    populateSchools([
      'Autre / Non listée'
    ]);
    console.warn('Impossible de charger /assets/schools.js:', e);
  }

  function validateSchool() {
    if (!schoolSelect.value) {
      schoolError && (schoolError.textContent = 'Veuillez sélectionner votre école');
      schoolError && schoolError.classList.remove('hidden');
      return false;
    }
    schoolError && schoolError.classList.add('hidden');
    return true;
  }

  schoolSelect.addEventListener('change', validateSchool);

  const signupBtn = document.querySelector('#signup');
  if (signupBtn) {
    signupBtn.addEventListener('click', (e) => {
      if (!validateSchool()) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  }
})();

// Gestion des utilisateurs
async function showUserModal(userId = null, activeTab = 'profile') {
  const modal = document.getElementById('userModal');
  modal.classList.remove('hidden');

  // Stocker l'ID de l'utilisateur actuellement modifié
  if (userId !== null) {
    currentUserId = userId;
  }

  // Ne réinitialiser le formulaire que pour une nouvelle création (userId === null)
  const form = document.getElementById('userForm');
  if (userId === null) {
    form.reset();
  }

  // Gestion des onglets
  const tabs = {
    profile: {
      tab: document.getElementById('profile-tab'),
      content: document.getElementById('profile')
    },
    security: {
      tab: document.getElementById('security-tab'),
      content: document.getElementById('security')
    },
    activity: {
      tab: document.getElementById('activity-tab'),
      content: document.getElementById('activity')
    }
  };

  // Supprimer l'ancien gestionnaire d'événements s'il existe
  if (tabClickHandler) {
    document.querySelectorAll('[id$="-tab"]').forEach(tab => {
      tab.removeEventListener('click', tabClickHandler);
    });
  }

  // Nouveau gestionnaire d'événements pour les onglets
  tabClickHandler = (e) => {
    e.preventDefault();
    e.stopPropagation();
    const tabId = e.currentTarget.id.replace('-tab', '');
    showUserModal(currentUserId, tabId);  // On passe currentUserId au lieu de userId
  };

  // Ajouter le gestionnaire d'événements aux onglets
  document.querySelectorAll('[id$="-tab"]').forEach(tab => {
    tab.addEventListener('click', tabClickHandler);
  });

  // Activer l'onglet demandé
  Object.entries(tabs).forEach(([key, { tab, content }]) => {
    if (tab && content) {
      if (key === activeTab) {
        tab.classList.add('border-blue-500', 'text-blue-600');
        tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        content.classList.remove('hidden');

        // Charger les données spécifiques à l'onglet si nécessaire
        if (key === 'activity' && currentUserId) {
          loadUserActivity(currentUserId);
        }
      } else {
        tab.classList.remove('border-blue-500', 'text-blue-600');
        tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        content.classList.add('hidden');
      }
    }
  });

  // Mode édition
  if (currentUserId) {
    document.getElementById('modal-title').textContent = 'Modifier l\'utilisateur';
    document.getElementById('saveButtonText').textContent = 'Enregistrer les modifications';

    try {
      // Vérifier si on a déjà les données de l'utilisateur
      if (!form.dataset.loaded || form.dataset.loaded !== currentUserId) {
        const response = await apiRequest(`/admin/users/${currentUserId}`);

        if (response.success) {
          const user = response.data;
          // Remplir le formulaire avec les données de l'utilisateur
          fillUserForm(user);
          form.dataset.loaded = currentUserId;
        } else {
          throw new Error(response.message || 'Erreur lors du chargement des données utilisateur');
        }
      }
    } catch (error) {
      console.error('Erreur:', error);
      showNotification('Erreur', 'Impossible de charger les données de l\'utilisateur', 'error');
    }
  } else {
    // Mode création
    document.getElementById('modal-title').textContent = 'Ajouter un utilisateur';
    document.getElementById('saveButtonText').textContent = 'Ajouter l\'utilisateur';
    document.getElementById('userId').value = '';
    delete form.dataset.loaded;
  }
}

async function handleUserFormSubmit(e) {
  e.preventDefault();
  e.stopPropagation();

  const form = e.target;
  const invalidFields = form.querySelectorAll(':invalid');

  for (const field of invalidFields) {
    const isVisible = !!(field.offsetWidth || field.offsetHeight || field.getClientRects().length);
    const isDisabled = field.disabled;

    if (!isVisible || isDisabled) {
      console.warn(`Champ requis non focusable: ${field.name}`);
      showNotification('Attention', `Le champ '${field.name}' est requis. Veuillez le corriger.`, 'warning');
      return;
    }
  }
  const formData = new FormData(form);
  const userId = formData.get('userId');

  try {
    const url = userId ? `/admin/users/${userId}` : '/admin/users';
    const method = userId ? 'PUT' : 'POST';

    const response = await apiRequest(url, {
      method,
      body: JSON.stringify(Object.fromEntries(formData))
    });

    const result = response;

    if (result.success) {
      showNotification(
        'Succès',
        userId ? 'Utilisateur mis à jour avec succès' : 'Utilisateur créé avec succès',
        'success'
      );
      loadUsers();
      document.getElementById('userModal').classList.add('hidden');
    } else {
      throw new Error(result.message || 'Une erreur est survenue');
    }
  } catch (error) {
    console.error('Erreur:', error);
    showNotification(
      'Erreur',
      error.message || 'Une erreur est survenue lors de l\'enregistrement',
      'error'
    );
  }
}

// Fonction utilitaire pour remplir le formulaire
function fillUserForm(user) {
  // Ne pas réinitialiser les champs s'ils sont déjà remplis
  if (!document.getElementById('userId').value) {
    document.getElementById('userId').value = user.id;
    document.getElementById('username').value = user.username || '';
    document.getElementById('email').value = user.email || '';
    document.getElementById('fullName').value = user.fullname || '';
    document.getElementById('role').value = user.role || 'user';
    document.getElementById('status').value = user.status || 'active';
    document.getElementById('bio').value = user.bio || '';
    document.getElementById('twoFactorEnabled').checked = user.two_factor_enabled || false;
    document.getElementById('number').value = user.number || '';
    document.getElementById('study_level').value = user.study_level || '';
  }

  // Gérer l'école
  const schoolSelect = document.getElementById('school');
  if (schoolSelect) {
    // Ne pas réinitialiser si déjà une valeur
    if (!schoolSelect.value && user.school) {
      // Vérifier si l'option existe déjà
      let optionExists = false;
      for (let i = 0; i < schoolSelect.options.length; i++) {
        if (schoolSelect.options[i].value === user.school) {
          optionExists = true;
          break;
        }
      }

      if (!optionExists) {
        const option = document.createElement('option');
        option.value = user.school;
        option.textContent = user.school;
        schoolSelect.appendChild(option);
      }
      schoolSelect.value = user.school;
    }
  }
}

function validateForm(form) {
  const requiredFields = ['username', 'email'];
  let isValid = true;

  requiredFields.forEach(fieldName => {
    const field = form[fieldName];
    if (!field.value.trim()) {
      field.classList.add('is-invalid');
      isValid = false;
    } else {
      field.classList.remove('is-invalid');
    }
  });

  // Validation email
  const emailField = form.email;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (emailField.value && !emailPattern.test(emailField.value)) {
    emailField.classList.add('is-invalid');
    isValid = false;
  }

  if (!isValid) {
    showNotification('Veuillez corriger les erreurs dans le formulaire', null, 'warning');
  }

  return isValid;
}

function validatePasswordMatch() {
  const password = document.getElementById('password');
  const confirmation = document.getElementById('password_confirmation');

  if (password.value && confirmation.value) {
    if (password.value !== confirmation.value) {
      confirmation.classList.add('is-invalid');
    } else {
      confirmation.classList.remove('is-invalid');
    }
  }
}

async function toggleUserStatus(userId, status, username) {
  const actionText = status === 'inactive' ? 'suspendre' : 'activer';

  const confirmed = await showConfirmDialog(
    `Êtes-vous sûr de vouloir ${actionText} l'utilisateur "${username}" ?`,
    'Confirmer le statut',
    'Confirmer',
    'Annuler'
  );

  if (!confirmed) return;

  try {
    const response = await apiRequest(`/admin/users/${userId}/status`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ status })
    });

    if (response.success) {
      showNotification(
        `Utilisateur ${status === 'inactive' ? 'suspendu' : 'activé'} avec succès`,
        `"${username}" a été ${status === 'inactive' ? 'suspendu' : 'activé'}`,
        'success'
      );
      loadUsers();
      loadUserStats();
    } else {
      throw new Error(response.message || 'Erreur lors de la mise à jour du statut');
    }

  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur lors de la mise à jour du statut', error.message, 'error');
  }
}

async function resetUserPassword(userId, username) {
  const confirmed = await showConfirmDialog(
    `Voulez-vous vraiment réinitialiser le mot de passe de l'utilisateur "${username}" ?\n\nUn email lui sera envoyé avec les instructions.`,
    'Confirmer la réinitialisation',
    'Confirmer',
    'Annuler'
  );

  if (!confirmed) return;

  try {
    const response = await apiRequest(`/admin/users/${userId}/reset-password`, {
      method: 'POST'
    });

    if (response.success) {
      showNotification(
        'Email de réinitialisation envoyé',
        `Un email a été envoyé à "${username}" avec les instructions`,
        'success'
      );
    } else {
      throw new Error(response.message || response.error || 'Erreur lors de la réinitialisation du mot de passe');
    }

  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur lors de la réinitialisation', error.message, 'error');
  }
}

function confirmDeleteUser(userId, username) {
  // Créer la modale avec Tailwind
  const modal = document.createElement('div');
  modal.id = 'deleteUserModal';
  modal.className = 'fixed inset-0 backdrop-blur-sm bg-opacity-50 flex items-center justify-center z-50 p-4';
  modal.innerHTML = `
    <div class="bg-slate-400 dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md">
      <div class="p-6">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Confirmer la suppression</h3>
        <p class="text-slate-600 dark:text-slate-300 mb-6">
          Vous êtes sur le point de supprimer l'utilisateur <strong class="text-slate-900 dark:text-white">"${username}"</strong>.
        </p>
        <div class="flex justify-end space-x-3">
          <button id="cancelDeleteBtn" class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
            Annuler
          </button>
          <button id="confirmDeleteBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
            Supprimer
          </button>
        </div>
      </div>
    </div>
  `;

  // Ajouter la modale au DOM
  document.body.appendChild(modal);
  document.body.classList.add('overflow-hidden');

  // Gestion des événements
  const confirmBtn = modal.querySelector('#confirmDeleteBtn');
  const cancelBtn = modal.querySelector('#cancelDeleteBtn');

  function closeUserModal() {
    const modal = document.getElementById('userModal');
    if (modal) {
      // Réinitialiser le formulaire
      const form = document.getElementById('userForm');
      if (form) {
        form.reset();
        form.dataset.loaded = '';
      }

      // Réinitialiser les variables
      currentUserId = null;

      // Cacher la modal
      modal.classList.add('hidden');

      // Réinitialiser l'onglet actif
      const activeTab = document.querySelector('.tab-button.active');
      if (activeTab) {
        activeTab.classList.remove('active');
      }
      const firstTab = document.querySelector('.tab-button');
      if (firstTab) {
        firstTab.classList.add('active');
      }
    }
  }

  const handleConfirm = async (e) => {
    e.preventDefault();
    e.stopPropagation();
    await deleteUser(userId, username);
    closeUserModal();
  };

  confirmBtn.addEventListener('click', handleConfirm);
  cancelBtn.addEventListener('click', closeUserModal);

  // Fermer en cliquant en dehors de la modale
  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeUserModal();
    }
  });

  // Nettoyage
  return () => {
    confirmBtn.removeEventListener('click', handleConfirm);
    cancelBtn.removeEventListener('click', closeUserModal);
  };
}

async function deleteUser(userIds, username) {
  try {
    const response = await apiRequest(`/admin/users/${userIds}`, {
      method: 'DELETE'
    });

    if (response.success) {
      showNotification(
        'Utilisateur supprimé avec succès',
        `"${username}" a été supprimé définitivement`,
        'success'
      );
      loadUsers();
      loadUserStats();
    } else {
      throw new Error(response.message || 'Erreur lors de la suppression');
    }

  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur lors de la suppression', error.message, 'error');
  }
}

// Actions de masse
function toggleUserSelection(checkbox) {
  const userId = checkbox.value;
  if (checkbox.checked) {
    selectedUsers.add(userId);
  } else {
    selectedUsers.delete(userId);
  }

  // Mettre à jour la case "tout sélectionner"
  const selectAll = document.getElementById('selectAll');
  const totalCheckboxes = document.querySelectorAll('.user-checkbox').length;

  if (selectAll) {
    selectAll.checked = selectedUsers.size === totalCheckboxes && totalCheckboxes > 0;
    selectAll.indeterminate = selectedUsers.size > 0 && selectedUsers.size < totalCheckboxes;
  }

  updateBulkActions();
}

function updateBulkActions() {
  const bulkActionsBtn = document.getElementById('bulkActionsBtn');
  if (bulkActionsBtn) {
    bulkActionsBtn.disabled = selectedUsers.size === 0;
    bulkActionsBtn.innerHTML = `<i class="fas fa-tasks me-1"></i> Actions (${selectedUsers.size})`;
  }
}

function showBulkActionsModal() {
  const modal = document.getElementById('bulkActionsModal');
  const selectionInfo = document.getElementById('bulkSelectionInfo');
  const bulkAction = document.getElementById('bulkAction');
  const bulkRoleContainer = document.getElementById('bulkRoleContainer');
  const warningContainer = document.getElementById('bulkActionWarning');
  const confirmBtn = document.getElementById('confirmBulkAction');

  // Réinitialiser le formulaire
  bulkAction.value = '';
  bulkRoleContainer.classList.add('hidden');
  warningContainer.classList.add('hidden');

  // Mettre à jour le texte d'information
  selectionInfo.textContent = `${selectedUsers.size} utilisateur(s) sélectionné(s)`;

  // Afficher la modale
  modal.classList.remove('hidden');
  modal.setAttribute('aria-hidden', 'false');
  document.body.classList.add('overflow-hidden');

  // Ajouter un écouteur pour fermer la modale
  const closeBtn = document.getElementById('cancelBulkBtn');
  closeBtn.addEventListener('click', closeBulkActionsModal);
}

function closeBulkActionsModal() {
  const modal = document.getElementById('bulkActionsModal');
  modal.classList.add('hidden');
  modal.setAttribute('aria-hidden', 'true');
  document.body.classList.remove('overflow-hidden');
}

async function handleBulkAction() {
  const action = document.getElementById('bulkAction').value;
  if (!action || selectedUsers.size === 0) return;

  const userIds = Array.from(selectedUsers);
  let confirmMessage = `Êtes-vous sûr de vouloir effectuer cette action sur ${userIds.length} utilisateur(s) ?`;

  if (action === 'send_notification') {
    openNotificationModal(userIds);
    return;
  }
  // Messages de confirmation spécifiques
  switch (action) {
    case 'delete':
      confirmMessage = `Êtes-vous sûr de vouloir SUPPRIMER DÉFINITIVEMENT ces ${userIds.length} utilisateur(s) ?\n\nCette action est irréversible.`;
      break;
    case 'suspend':
      confirmMessage = `Êtes-vous sûr de vouloir suspendre ces ${userIds.length} utilisateur(s) ?`;
      break;
    case 'activate':
      confirmMessage = `Êtes-vous sûr de vouloir activer ces ${userIds.length} utilisateur(s) ?`;
      break;
    case 'deactivate':
      confirmMessage = `Êtes-vous sûr de vouloir désactiver ces ${userIds.length} utilisateur(s) ?`;
      break;
    case 'change_role':
      const role = document.getElementById('bulkRole').value;
      if (!role) {
        showNotification('Veuillez sélectionner un rôle', null, 'warning');
        return;
      }
      confirmMessage = `Êtes-vous sûr de vouloir changer le rôle de ces ${userIds.length} utilisateur(s) vers "${getRoleLabel(role)}" ?`;
      break;
  }

  const confirmed = await showConfirmDialog(
    confirmMessage,
    'Confirmer l\'action',
    'Confirmer',
    'Annuler'
  );

  if (!confirmed) return;

  try {
    const bulkSpinner = document.getElementById('bulkSpinner');
    const confirmBtn = document.getElementById('confirmBulkAction');

    bulkSpinner.classList.remove('hidden');
    confirmBtn.disabled = true;

    let body = { action, user_ids: userIds };

    if (action === 'change_role') {
      body.role = document.getElementById('bulkRole').value;
    }

    const response = await apiRequest('/admin/users/bulk', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    });

    if (response.success) {
      // Fermer le modal et recharger les données
      document.getElementById('bulkActionsModal').classList.add('hidden');
      document.body.classList.remove('overflow-hidden');

      showNotification(
        'Action effectuée avec succès',
        response.message || response.error || 'Action effectuée avec succès',
        'success'
      );

      selectedUsers.clear();
      loadUsers();
      loadUserStats();
    } else {
      throw new Error(response.message || 'Erreur lors de l\'exécution de l\'action');
    }

  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur lors de l\'action de masse', error.message, 'error');
  } finally {
    const bulkSpinner = document.getElementById('bulkSpinner');
    const confirmBtn = document.getElementById('confirmBulkAction');

    bulkSpinner.classList.add('hidden');
    confirmBtn.disabled = false;
  }
}

// Export des utilisateurs
async function exportUsers() {
  try {
    showNotification('Préparation de l\'export en cours...', null, 'info', 2000);

    // Construire les paramètres de filtre
    const params = new URLSearchParams({
      search: document.getElementById('searchInput')?.value || '',
      role: document.getElementById('roleFilter')?.value || '',
      status: document.getElementById('statusFilter')?.value || '',
      team: document.getElementById('teamFilter')?.value || ''
    });

    // Créer un lien de téléchargement temporaire
    const exportUrl = `/api/admin/users/export?${params.toString()}`;

    // Ouvrir dans une nouvelle fenêtre pour déclencher le téléchargement
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `utilisateurs_${new Date().toISOString().split('T')[0]}.csv`;
    link.style.display = 'none';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    showNotification('Export démarré', 'Le téléchargement devrait commencer automatiquement', 'success');

  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur lors de l\'export', error.message, 'error');
  }
}

// Fonctions utilitaires
function getFullName(user) {
  const firstName = user.first_name || '';
  const lastName = user.last_name || '';

  if (firstName && lastName) {
    return `${firstName} ${lastName}`;
  }

  return firstName || lastName || null;
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';

  try {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('fr-FR', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }).format(date);
  } catch (error) {
    console.error('Erreur de formatage de date:', error);
    return 'Date invalide';
  }
}

function getRoleLabel(role) {
  const roles = {
    'admin': 'Administrateur',
    'moderator': 'Modérateur',
    'organisateur': 'Organisateur',
    'participant': 'Participant'
  };
  return roles[role] || role;
}

function getRoleBadgeClass(role) {
  const classes = {
    'admin': 'danger',
    'moderator': 'warning',
    'organisateur': 'success',
    'participant': 'info'
  };
  return classes[role] || 'secondary';
}

function getStatusLabel(status) {
  const statuses = {
    'active': 'Actif',
    'inactive': 'Inactif',
    'suspended': 'Suspendu',
    'banned': 'Banni'
  };
  return statuses[status] || status;
}

function getStatusBadgeClass(status) {
  const classes = {
    'active': 'success',
    'inactive': 'secondary',
    'suspended': 'warning',
    'banned': 'danger'
  };
  return classes[status] || 'light';
}

function getActivityAction(action) {
  const actions = {
    'login': 'Connexion',
    'logout': 'Déconnexion',
    'profile_update': 'Mise à jour du profil',
    'password_change': 'Changement de mot de passe',
    'user_created': 'Création de compte',
    'user_updated': 'Mise à jour du compte',
    'user_deleted': 'Suppression du compte',
    'password_reset': 'Réinitialisation de mot de passe',
    'email_verified': 'Email vérifié',
    'two_factor_enabled': 'Authentification 2FA activée',
    'two_factor_disabled': 'Authentification 2FA désactivée'
  };
  return actions[action] || action;
}

/**
 * Affiche une boîte de dialogue de confirmation personnalisée
 * @param {string} message - Le message à afficher
 * @param {string} [title='Confirmer'] - Le titre de la boîte de dialogue
 * @param {string} [confirmText='Confirmer'] - Le texte du bouton de confirmation
 * @param {string} [cancelText='Annuler'] - Le texte du bouton d'annulation
 * @returns {Promise<boolean>} Résout à true si confirmé, false si annulé
 */
async function showConfirmDialog(message, title = 'Confirmer', confirmText = 'Confirmer', cancelText = 'Annuler') {
  return new Promise((resolve) => {
    // Créer la modale
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[100] p-4';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'confirm-dialog-title');
    modal.setAttribute('aria-describedby', 'confirm-dialog-description');

    modal.innerHTML = `
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-md transform transition-all">
              <div class="p-6">
                  <h3 id="confirm-dialog-title" class="text-lg font-semibold text-slate-900 dark:text-white mb-2">
                      ${title}
                  </h3>
                  <p id="confirm-dialog-description" class="text-slate-600 dark:text-slate-300 mb-6">
                      ${message}
                  </p>
                  <div class="flex justify-end space-x-3">
                      <button 
                          type="button" 
                          class="confirm-cancel-btn px-4 py-2 text-sm font-medium rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                          data-action="cancel"
                      >
                          ${cancelText}
                      </button>
                      <button 
                          type="button" 
                          class="confirm-ok-btn px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                          data-action="confirm"
                      >
                          ${confirmText}
                      </button>
                  </div>
              </div>
          </div>
      `;

    // Ajouter la modale au document
    document.body.appendChild(modal);
    document.body.classList.add('overflow-hidden');

    // Gestionnaires d'événements
    const handleAction = (e) => {
      const action = e.currentTarget.getAttribute('data-action');
      document.body.removeChild(modal);
      document.body.classList.remove('overflow-hidden');
      resolve(action === 'confirm');
    };

    const handleKeyDown = (e) => {
      if (e.key === 'Escape') {
        document.body.removeChild(modal);
        document.body.classList.remove('overflow-hidden');
        resolve(false);
      } else if (e.key === 'Enter') {
        document.body.removeChild(modal);
        document.body.classList.remove('overflow-hidden');
        resolve(true);
      }
    };

    // Ajouter les écouteurs
    const confirmBtn = modal.querySelector('[data-action="confirm"]');
    const cancelBtn = modal.querySelector('[data-action="cancel"]');

    confirmBtn.addEventListener('click', handleAction);
    cancelBtn.addEventListener('click', handleAction);
    modal.addEventListener('keydown', handleKeyDown);

    // Focus sur le bouton d'annulation par défaut
    cancelBtn.focus();
  });
}

function getDeviceInfo(userAgent) {
  if (!userAgent) return 'Appareil inconnu';

  // Détection simple du navigateur et de l'OS
  let browser = 'Navigateur inconnu';
  let os = 'Système inconnu';

  // Détection du navigateur
  if (userAgent.includes('Firefox')) {
    browser = 'Firefox';
  } else if (userAgent.includes('Chrome') && !userAgent.includes('Chromium')) {
    browser = 'Chrome';
  } else if (userAgent.includes('Safari') && !userAgent.includes('Chrome')) {
    browser = 'Safari';
  } else if (userAgent.includes('Edge')) {
    browser = 'Edge';
  } else if (userAgent.includes('Opera') || userAgent.includes('OPR')) {
    browser = 'Opera';
  } else if (userAgent.includes('MSIE') || userAgent.includes('Trident/')) {
    browser = 'Internet Explorer';
  }

  // Détection de l'OS
  if (userAgent.includes('Windows NT 10')) {
    os = 'Windows 10';
  } else if (userAgent.includes('Windows NT 6.3')) {
    os = 'Windows 8.1';
  } else if (userAgent.includes('Windows NT 6.1')) {
    os = 'Windows 7';
  } else if (userAgent.includes('Windows')) {
    os = 'Windows';
  } else if (userAgent.includes('Macintosh') || userAgent.includes('Mac OS X')) {
    os = 'macOS';
  } else if (userAgent.includes('Linux')) {
    os = 'Linux';
  } else if (userAgent.includes('Android')) {
    os = 'Android';
  } else if (userAgent.includes('iOS') || userAgent.includes('iPhone') || userAgent.includes('iPad')) {
    os = 'iOS';
  }

  return `${browser} sur ${os}`;
}

// Fonctions de débogage et logging
function debugLog(message, data = null) {
  if (console && console.log) {
    if (data) {
      console.log(`[UserManagement] ${message}:`, data);
    } else {
      console.log(`[UserManagement] ${message}`);
    }
  }
}

// Gestion globale des erreurs pour cette page
window.addEventListener('error', function (event) {
  event.preventDefault();
  event.stopPropagation();
  console.error('Erreur JavaScript dans la gestion des utilisateurs:', event.error);
});

// Nettoyage lors du déchargement de la page
window.addEventListener('beforeunload', function () {
  selectedUsers.clear();
  currentUserId = null;
});

// Export des fonctions pour les tests (si nécessaire)
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    formatDate,
    getRoleLabel,
    getStatusLabel,
    getActivityAction,
    getDeviceInfo,
    getFullName
  };
}