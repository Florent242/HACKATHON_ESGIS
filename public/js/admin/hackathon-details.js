let currentHackathonId = null;
let currentHackathonData = null;

document.addEventListener('DOMContentLoaded', () => {

  currentHackathonId = window.location.pathname.split('/').pop();

  if (!currentHackathonId) {
    showNotification('Erreur', 'ID du hackathon manquant', 'error');
    return;
  }

  initTabs();
  initDetailsModals();
  loadHackathonDetails();

  document.getElementById('editInfoBtn').addEventListener('click', openEditInfoModal);
  document.getElementById('editInfoForm').addEventListener('submit', handleEditInfoSubmit);
  document.getElementById('addPhaseBtn').addEventListener('click', openAddPhaseModal);
  document.getElementById('phaseForm').addEventListener('submit', handlePhaseSubmit);
});

function initTabs() {
  const tabBtns = document.querySelectorAll('.tab-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetTab = btn.dataset.tab;

      tabBtns.forEach(b => b.classList.remove('active'));
      tabContents.forEach(c => c.classList.remove('active'));

      btn.classList.add('active');
      document.getElementById(`${targetTab}-content`).classList.add('active');

      loadTabData(targetTab);

      lucide.createIcons();
    });
  });
}

function initDetailsModals() {
  const closeButtons = document.querySelectorAll('.modal-close, [data-modal]');
  closeButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (e.target.classList.contains('btn-primary')) return;
      const modalId = btn.dataset.modal;
      if (modalId) {
        closeDetailsModal(modalId);
      }
    });
  });

  document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeDetailsModal(modal.id);
      }
    });
  });
}

function openDetailsModal(modalId) {
  document.getElementById(modalId).classList.add('active');
  lucide.createIcons();
}

function closeDetailsModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
}

async function loadHackathonDetails() {
  try {
    const response = await apiRequest(`/hackathons/${currentHackathonId}`);

    if (response.success) {
      currentHackathonData = response.data;
      displayHackathonInfo(response.data);
      loadTabData('infos');

      // Charger les règles, critères et prix depuis les données du hackathon
      loadRulesFromData(response.data);
      loadEligibilityFromData(response.data);
      loadPrizesFromData(response.data);
    } else {
      throw new Error(response.message || 'Impossible de charger les détails');
    }
  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur', error.message || 'Erreur de chargement des données', 'error');
  }
}

function displayHackathonInfo(data) {
  document.getElementById('hackathonTitle').textContent = data.name || 'Hackathon';
  document.getElementById('hackathonTheme').textContent = data.theme || '';

  const statusBadge = document.getElementById('hackathonStatus');
  statusBadge.textContent = data.status || 'upcoming';
  statusBadge.className = `status-badge ${data.status || 'upcoming'}`;

  document.getElementById('infoType').textContent = data.type === 'dev' ? 'Développement' : 'CTF';
  document.getElementById('infoStartDate').textContent = formatDate(data.start_date);
  document.getElementById('infoEndDate').textContent = formatDate(data.end_date);
  document.getElementById('infoLocation').textContent = data.location || 'Non spécifié';
  document.getElementById('infoDescription').textContent = data.description || 'Aucune description';
  document.getElementById('infoRules').textContent = data.rules || 'Aucune règle spécifiée';
  document.getElementById('infoPrizes').textContent = data.prizes || 'Aucun prix spécifié';

  lucide.createIcons();
}

async function loadTabData(tab) {
  switch (tab) {
    case 'infos':
      break;
    case 'phases':
      await loadPhases();
      break;
    case 'teams':
      await loadTeams();
      break;
    case 'participants':
      await loadParticipants();
      break;
    case 'challenges':
      await loadChallenges();
      break;
    case 'leaderboard':
      await loadLeaderboard();
      break;
    case 'registrations':
      await loadRegistrations();
      break;
  }
}

async function loadPhases() {
  try {
    const response = await apiRequest(`/phases/${currentHackathonId}/all-phases`);
    const tbody = document.getElementById('phasesTableBody');

    // Vérifier si la réponse contient des données
    if (!response || !response.data) {
      tbody.innerHTML = '<tr><td colspan="6">Aucune phase trouvée</td></tr>';
      return;
    }

    // Convertir les données en tableau si nécessaire
    const phases = Array.isArray(response.data)
      ? response.data
      : Object.values(response.data);

    if (phases.length > 0) {
      tbody.innerHTML = phases.map(phase => `
        <tr>
          <td>${phase.name || 'Non défini'}</td>
          <td>
            <span class="phase-type ${phase.type || 'inconnu'}">
              ${phase.phase_type === 'open' ? 'Ouverte' : (phase.phase_type === 'qualified' ? 'Qualifiée' : 'Inconnu')}
            </span>
          </td>
          <td>${phase.start ? formatDate(phase.start) : 'Non défini'}</td>
          <td>${phase.end ? formatDate(phase.end) : 'Non défini'}</td>
          <td>${phase.order_index || '0'}</td>
          <td>
            <div class="action-buttons">
              <button class="btn-icon btn-sm" onclick="editPhase(${phase.id})">
                <i data-lucide="edit" class="w-4 h-4"></i>
              </button>
              <button class="btn-icon btn-sm btn-danger" onclick="deletePhase(${phase.id})">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>
          </td>
        </tr>
      `).join('');

      // Initialiser les icônes Lucide
      lucide.createIcons();
    } else {
      tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="empty-state">
                        <i data-lucide="calendar"></i>
                        <p>Aucune phase définie</p>
                    </td>
                </tr>
            `;
    }
    lucide.createIcons();
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement des phases', 'error');
  }
}

async function loadTeams() {
  try {
    const response = await apiRequest(`/hackathons/${currentHackathonId}/teams`);
    const tbody = document.getElementById('teamsTableBody');

    if (response.success && response.data && response.data.length > 0) {
      tbody.innerHTML = response.data.map(team => `
                <tr>
                    <td>${team.name}</td>
                    <td>${team.leader_username || 'N/A'}</td>
                    <td>${team.member_count || 0}</td>
                    <td>${formatDate(team.created_at)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-primary btn-sm" onclick="viewTeam(${team.id})">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                <span>Voir</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
    } else {
      tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <i data-lucide="users"></i>
                        <p>Aucune équipe inscrite</p>
                    </td>
                </tr>
            `;
    }
    lucide.createIcons();
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement des équipes', 'error');
  }
}

async function loadParticipants() {
  try {
    const response = await apiRequest(`/hackathons/${currentHackathonId}/participants`);
    const tbody = document.getElementById('participantsTableBody');

    if (response.success && response.data && response.data.length > 0) {
      tbody.innerHTML = response.data.map(participant => `
                <tr>
                    <td>${participant.username || participant.fullname || 'N/A'}</td>
                    <td>${participant.email}</td>
                    <td>${participant.school || 'N/A'}</td>
                    <td><span class="status-badge ${participant.participation_status}">${participant.participation_status}</span></td>
                    <td>
                        <div class="action-buttons flex gap-2">
                          ${participant.participation_status === 'pending' ? `
                              <button class="btn-success btn-sm" onclick="updateParticipantStatus(${participant.id}, 'accepted')">
                                  <i data-lucide="check" class="w-4 h-4"></i>
                                  <span>Accepter</span>
                              </button>
                              <button class="btn-danger btn-sm" onclick="updateParticipantStatus(${participant.id}, 'rejected')">
                                  <i data-lucide="x" class="w-4 h-4"></i>
                                  <span>Refuser</span>
                              </button>
                          ` : ''}
                          ${participant.participation_status === 'accepted' ? `
                              <button class="btn-warning btn-sm" 
                                      onclick="updateParticipantStatus(${participant.id}, 'rejected')"
                                      title="Disqualifier le participant">
                                  <i data-lucide="user-x" class="w-4 h-4"></i>
                                  <span>Disqualifier</span>
                              </button>
                          ` : ''}
                          ${participant.participation_status === 'rejected' ? `
                              <button class="btn-primary btn-sm" 
                                      onclick="updateParticipantStatus(${participant.id}, 'accepted')"
                                      title="Réintégrer le participant">
                                  <i data-lucide="user-check" class="w-4 h-4"></i>
                                  <span>Réintégrer</span>
                              </button>
                          ` : ''}
                      </div>
                    </td>
                </tr>
            `).join('');
    } else {
      tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                        <p>Aucun participant</p>
                    </td>
                </tr>
            `;
    }
    lucide.createIcons();
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement des participants', 'error');
  }
}

async function loadChallenges() {
  try {
    const response = await apiRequest(`/hackathons/${currentHackathonId}/challenges`);
    const tbody = document.getElementById('challengesTableBody');

    if (response.success && response.data && response.data.length > 0) {
      tbody.innerHTML = response.data.map(challenge => `
                <tr>
                    <td>${challenge.title}</td>
                    <td>${challenge.type || 'N/A'}</td>
                    <td>${challenge.points || 0}</td>
                    <td><span class="status-badge ${challenge.is_active}">${challenge.is_active ? 'Actif' : 'Inactif'}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-primary btn-sm" onclick="viewChallenge(${challenge.id})">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                <span>Voir</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
    } else {
      tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="empty-state">
                        <i data-lucide="flag" class="w-4 h-4"></i>
                        <p>Aucun challenge</p>
                    </td>
                </tr>
            `;
    }
    lucide.createIcons();
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement des challenges', 'error');
  }
}

async function loadLeaderboard() {
  try {
    const response = await apiRequest(`/hackathons/${currentHackathonId}/leaderboard`);
    const tbody = document.getElementById('leaderboardTableBody');

    if (response.success && response.data && response.data.length > 0) {
      tbody.innerHTML = response.data.map((entry, index) => {
        let trophy = '';
        if (index === 0) trophy = '<span class="trophy-icon rank-1">🥇</span>';
        else if (index === 1) trophy = '<span class="trophy-icon rank-2">🥈</span>';
        else if (index === 2) trophy = '<span class="trophy-icon rank-3">🥉</span>';

        return `
                    <tr>
                        <td>${trophy}${entry.rank || index + 1}</td>
                        <td>${entry.team_name || entry.name}</td>
                        <td><strong>${entry.score || 0}</strong></td>
                    </tr>
                `;
      }).join('');
    } else {
      tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="empty-state">
                        <i data-lucide="trophy"></i>
                        <p>Aucun classement disponible</p>
                    </td>
                </tr>
            `;
    }
    lucide.createIcons();
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement du classement', 'error');
  }
}

async function loadRegistrations() {
  try {
    const response = await apiRequest(`/hackathons/${currentHackathonId}/registrations`);
    const tbody = document.getElementById('registrationsTableBody');

    if (response.success && response.data && response.data.length > 0) {
      tbody.innerHTML = response.data.map(registration => `
                <tr>
                    <td>${registration.team_name || registration.name || 'N/A'}</td>
                    <td>${registration.email}</td>
                    <td>${formatDate(registration.registered_at)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-success btn-sm" onclick="handleRegistration(${registration.id}, 'accept')">
                                <i data-lucide="check"></i>
                                <span>Accepter</span>
                            </button>
                            <button class="btn-danger btn-sm" onclick="handleRegistration(${registration.id}, 'reject')">
                                <i data-lucide="x"></i>
                                <span>Refuser</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
    } else {
      tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="empty-state">
                        <i data-lucide="user-plus"></i>
                        <p>Aucune demande d'inscription</p>
                    </td>
                </tr>
            `;
    }
    lucide.createIcons();
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement des inscriptions', 'error');
  }
}

function openEditInfoModal() {
  const form = document.getElementById('editInfoForm');
  form.description.value = currentHackathonData.description || '';
  form.rules.value = currentHackathonData.rules || '';
  form.prizes.value = currentHackathonData.prizes || '';
  openDetailsModal('editInfoModal');
}

// Fonction utilitaire pour nettoyer et parser le JSON
function safeJsonParse(jsonString, defaultValue = []) {
  if (!jsonString) return defaultValue;

  try {
    // Remplacer les espaces insécables et nettoyer la chaîne
    const cleaned = jsonString
      .replace(/\u00a0/g, ' ')  // Remplacer les espaces insécables
      .replace(/\n/g, '')       // Supprimer les retours à la ligne
      .trim();

    return cleaned ? JSON.parse(cleaned) : defaultValue;
  } catch (error) {
    console.error('Erreur lors du parsing JSON:', error, 'Chaine originale:', jsonString);
    return defaultValue;
  }
}

async function updateParticipantStatus(participantId, status) {
  if (!confirm(`Êtes-vous sûr de vouloir ${getStatusActionText(status)} ce participant ?`)) {
    return;
  }

  try {
    const response = await apiRequest(
      `/participants/${participantId}/status`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ status })
      }
    );

    if (response.success) {
      showNotification('Succès', `Participant ${getStatusSuccessText(status)} avec succès`, 'success');
      await loadParticipants(); // Recharger la liste des participants
    } else {
      throw new Error(response.error || 'Erreur lors de la mise à jour du statut');
    }
  } catch (error) {
    console.error('Erreur:', error);
    showNotification('Erreur', error.message || 'Erreur lors de la mise à jour du statut', 'error');
  }
}

// Fonction utilitaire pour les textes des actions
function getStatusActionText(status) {
  const actions = {
    'accepted': 'accepter',
    'rejected': 'refuser',
    'disqualified': 'disqualifier',
    'pending': 'mettre en attente'
  };
  return actions[status] || 'modifier le statut de';
}

// Fonction utilitaire pour les messages de succès
function getStatusSuccessText(status) {
  const messages = {
    'accepted': 'accepté',
    'rejected': 'refusé',
    'disqualified': 'disqualifié',
    'pending': 'mis en attente'
  };
  return messages[status] || 'mis à jour';
}

async function handleEditInfoSubmit(e) {
  e.preventDefault();
  const form = e.target;

  try {
    // Récupérer les valeurs des champs
    const description = form.description.value;
    const rules = safeJsonParse(document.getElementById('hackathonRules').value) || '[]';
    const prizes = safeJsonParse(document.getElementById('hackathonPrizes').value) || '[]';
    const eligibility = safeJsonParse(document.getElementById('hackathonEligibility').value) || '[]';

    // Préparer les données pour l'API
    const formData = {
      description,
      rules,
      prizes,
      eligibility_criteria: eligibility
    };

    // Envoyer la requête
    const response = await apiRequest(
      `/hackathons/${currentHackathonId}`,
      {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData) // Convertir en chaîne JSON
      }
    );

    if (response.success) {
      showNotification('Succès', 'Informations mises à jour', 'success');
      closeDetailsModal('editInfoModal');
      await loadHackathonDetails();
    } else {
      throw new Error(response.message || response.error || 'Erreur lors de la mise à jour');
    }
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error);
    showNotification(
      'Erreur',
      error.message || 'Une erreur est survenue lors de la mise à jour',
      'error'
    );
  }
}

function openAddPhaseModal() {
  document.getElementById('phaseModalTitle').textContent = 'Ajouter une phase';
  document.getElementById('phaseForm').reset();
  document.getElementById('phaseForm').phase_id.value = '';
  openDetailsModal('phaseModal');
}

async function editPhase(phaseId) {
  try {
    const response = await apiRequest(`/phases/${phaseId}`);

    if (response.success) {
      const phase = response.data;

      if (phase) {
        document.getElementById('phaseModalTitle').textContent = 'Modifier la phase';
        const form = document.getElementById('phaseForm');
        form.phase_id.value = phase.id;
        form.hackathon_id.value = phase.hackathon_id;
        form.name.value = phase.name;
        form.type.value = phase.phase_type;
        form.start_date.value = formatDateForInput(phase.start);
        form.end_date.value = formatDateForInput(phase.end);
        form.order.value = phase.order_index;
        openDetailsModal('phaseModal');
      }
    }
  } catch (error) {
    showNotification('Erreur', 'Erreur de chargement de la phase', 'error');
  }
}

async function handlePhaseSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const phaseId = form.phase_id.value;
  const hackathonId = form.hackathon_id.value;

  const phaseData = {
    name: form.name.value,
    type: form.type.value,
    start: form.start_date.value,
    end: form.end_date.value,
    order: parseInt(form.order.value)
  };

  try {
    let response;

    // gerer le cas d'un edit/add
    if (hackathonId && !phaseId) {
      response = await apiRequest(`/phases/${hackathonId}`, {
        method: 'POST',
        body: JSON.stringify(phaseData)
      });
    } else if (hackathonId && phaseId) {
      response = await apiRequest(`/phases/${hackathonId}/${phaseId}`, {
        method: 'PUT',
        body: JSON.stringify(phaseData)
      });
    }

    if (response.success) {
      showNotification('Succès', phaseId ? 'Phase mise à jour' : 'Phase créée', 'success');
      closeDetailsModal('phaseModal');
      await loadPhases();
    } else {
      showNotification('Erreur', response.message || response.error || 'Erreur lors de l\'enregistrement', 'error');
    }
  } catch (error) {
    showNotification('Erreur', 'Erreur de connexion', 'error');
  }
}

async function deletePhase(phaseId) {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette phase ?')) return;

  try {
    const response = await apiRequest(`/phases/${currentHackathonId}/${phaseId}`, { method: 'DELETE' });

    if (response.success) {
      showNotification('Succès', 'Phase supprimée', 'success');
      await loadPhases();
    } else {
      showNotification('Erreur', response.message || response.error || 'Erreur lors de la suppression', 'error');
    }
  } catch (error) {
    showNotification('Erreur', 'Erreur de connexion', 'error');
  }
}

async function handleRegistration(registrationId, action) {
  try {
    const response = await apiRequest(`/participants/${registrationId}`, {
      action,
      status: action === 'accept' ? 'accepted' : 'rejected'
    });

    if (response.success) {
      showNotification('Succès', `Inscription ${action === 'accept' ? 'acceptée' : 'refusée'}`, 'success');
      await loadRegistrations();
    } else {
      showNotification('Erreur', response.message || response.error || 'Erreur lors du traitement', 'error');
    }
  } catch (error) {
    showNotification('Erreur', 'Erreur de connexion', 'error');
  }
}

function viewTeam(teamId) {
  window.location.href = `/admin/equipes#id=${teamId}`;
}

function viewChallenge(challengeId) {
  window.location.href = `/admin/challenges#id=${challengeId}`;
}

function formatDate(dateString) {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

let debounceTimer;
function debounceSave(callback, delay = 1000) {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(callback, delay);
}
function initializeEventListeners() {
  // Sauvegarder automatiquement lors de la modification des champs
  document.addEventListener('input', (e) => {
    if (e.target.closest('.rule-item, .eligibility-item, .prize-item')) {
      updateRelevantField(e.target);
    }
  });
}

function updateRelevantField(element) {
  const item = element.closest('[class$="-item"]');
  if (!item) return;

  if (item.classList.contains('rule-item')) {
    updateRulesField();
  } else if (item.classList.contains('eligibility-item')) {
    updateEligibilityField();
  } else if (item.classList.contains('prize-item')) {
    updatePrizesField();
  }
}

function formatDateForInput(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Fonctions pour gérer les règles
function addRule(rule = { title: '', description: '' }) {
  const container = document.getElementById('rules-container');
  const ruleId = Date.now();

  const ruleElement = document.createElement('div');
  ruleElement.className = 'rule-item p-4 bg-gray-800 rounded-lg border border-gray-700';
  ruleElement.dataset.id = ruleId;

  ruleElement.innerHTML = `
      <div class="flex justify-between items-start mb-2">
          <input type="text" 
                 class="bg-gray-900 border border-gray-700 text-white text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 mb-2" 
                 placeholder="Titre de la règle"
                 value="${rule.title || ''}">
          <button type="button" onclick="removeItem(this, 'rules')" class="text-red-400 hover:text-red-300 ml-2">
              <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
      </div>
      <textarea class="bg-gray-900 border border-gray-700 text-white text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                rows="2" 
                placeholder="Description de la règle">${rule.description || ''}</textarea>
  `;

  container.appendChild(ruleElement);
  lucide.createIcons();
  updateRulesField();
}

// Fonctions pour les critères d'éligibilité
function addEligibilityCriterion(criterion = {}) {
  const container = document.getElementById('eligibility-container');
  const criterionId = Date.now();

  const criterionElement = document.createElement('div');
  criterionElement.className = 'eligibility-item p-4 bg-gray-800 rounded-lg border border-gray-700';
  criterionElement.dataset.id = criterionId;

  criterionElement.innerHTML = `
      <div class="flex justify-between items-start">
          <input type="text" 
                 class="bg-gray-900 border border-gray-700 text-white text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                 placeholder="Critère d'éligibilité"
                 value="${criterion.text || ''}">
          <button type="button" onclick="removeItem(this, 'eligibility')" class="text-red-400 hover:text-red-300 ml-2">
              <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
      </div>
  `;

  container.appendChild(criterionElement);
  lucide.createIcons();
  updateEligibilityField();
}

// Fonctions pour les récompenses
function addPrize(prize = { position: '', description: '' }) {
  const container = document.getElementById('prizes-container');
  const prizeId = Date.now();

  const prizeElement = document.createElement('div');
  prizeElement.className = 'prize-item p-4 bg-gray-800 rounded-lg border border-gray-700';
  prizeElement.dataset.id = prizeId;

  prizeElement.innerHTML = `
      <div class="grid grid-cols-12 gap-4">
          <div class="col-span-2">
              <input type="text" 
                     class="prize-position bg-gray-900 border border-gray-700 text-white text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                     placeholder="Position (1er, 2e...)"
                     value="${prize.position || ''}">
          </div>
          <div class="col-span-9">
              <input type="text" 
                     class="prize-description bg-gray-900 border border-gray-700 text-white text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" 
                     placeholder="Description de la récompense"
                     value="${prize.description || ''}">
          </div>
          <div class="col-span-1 flex justify-end">
              <button type="button" onclick="removeItem(this, 'prizes')" class="text-red-400 hover:text-red-300">
                  <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
          </div>
      </div>
  `;

  container.appendChild(prizeElement);
  lucide.createIcons();
  updatePrizesField();
}
function loadRulesFromData(hackathonData) {
  const container = document.getElementById('rules-container');
  container.innerHTML = '';

  // Vérifier si rules est une chaîne JSON ou un tableau
  const rules = typeof hackathonData.rules === 'string'
    ? JSON.parse(hackathonData.rules || '[]')
    : (hackathonData.rules || []);

  rules.forEach(rule => addRule(rule));
}

function loadEligibilityFromData(hackathonData) {
  const container = document.getElementById('eligibility-container');
  container.innerHTML = '';

  const criteria = typeof hackathonData.eligibility_criteria === 'string'
    ? JSON.parse(hackathonData.eligibility_criteria || '[]')
    : (hackathonData.eligibility_criteria || []);

  criteria.forEach(criterion => addEligibilityCriterion(criterion));
}

function loadPrizesFromData(hackathonData) {
  const container = document.getElementById('prizes-container');
  container.innerHTML = '';

  const prizes = parseJsonField(hackathonData, 'prizes');

  prizes.forEach(prize => {
    const formattedPrize = {
      position: prize.label || prize.position || '',
      description: prize.reward || prize.description || ''
    };
    addPrize(formattedPrize);
  });
}

function parseJsonField(data, fieldName, defaultValue = []) {
  if (!data[fieldName]) return defaultValue;

  if (typeof data[fieldName] === 'string') {
    try {
      const cleaned = data[fieldName]
        .replace(/\u00a0/g, ' ')
        .replace(/\n/g, '')
        .trim();
      return cleaned ? JSON.parse(cleaned) : defaultValue;
    } catch (error) {
      console.error(`Erreur lors du parsing de ${fieldName}:`, error);
      return defaultValue;
    }
  }

  return Array.isArray(data[fieldName]) ? data[fieldName] : defaultValue;
}

// Fonction utilitaire pour supprimer un élément
function removeItem(button, type) {
  const container = button.closest(`#${type}-container`);
  button.closest(`.${type}-item`).remove();

  if (type === 'rules') updateRulesField();
  else if (type === 'eligibility') updateEligibilityField();
  else if (type === 'prizes') updatePrizesField();
}

// Mise à jour des champs cachés
function updateRulesField() {
  const rules = [];
  document.querySelectorAll('.rule-item').forEach(item => {
    const title = item.querySelector('input[type="text"]').value;
    const description = item.querySelector('textarea').value;
    if (title || description) {
      rules.push({ title, description });
    }
  });
  document.getElementById('hackathonRules').value = JSON.stringify(rules);
}

function updateEligibilityField() {
  const criteria = [];
  document.querySelectorAll('.eligibility-item').forEach(item => {
    const text = item.querySelector('input[type="text"]').value;
    if (text) criteria.push({ text });
  });
  document.getElementById('hackathonEligibility').value = JSON.stringify(criteria);
}

function updatePrizesField() {
  const prizes = [];
  document.querySelectorAll('.prize-item').forEach(item => {
    const position = item.querySelector('.prize-position').value;
    const description = item.querySelector('.prize-description').value;
    if (position || description) {
      prizes.push({ position, description });
    }
  });
  document.getElementById('hackathonPrizes').value = JSON.stringify(prizes);
}

// Initialisation des événements
document.addEventListener('DOMContentLoaded', function () {
  // Ajouter des écouteurs pour la mise à jour en temps réel
  document.addEventListener('input', function (e) {
    if (e.target.closest('.rule-item')) updateRulesField();
    else if (e.target.closest('.eligibility-item')) updateEligibilityField();
    else if (e.target.closest('.prize-item')) updatePrizesField();
  });

  // Charger les données existantes si en mode édition
  loadExistingData();
});

// Charger les données existantes
function loadExistingData() {
  // Récupérer les données du hackathon depuis l'API
  const hackathonData = {}; // Remplacer par l'appel API réel

  // Charger les règles
  if (hackathonData.rules && Array.isArray(hackathonData.rules)) {
    hackathonData.rules.forEach(rule => addRule(rule));
  }

  // Charger les critères d'éligibilité
  if (hackathonData.eligibility_criteria && Array.isArray(hackathonData.eligibility_criteria)) {
    hackathonData.eligibility_criteria.forEach(criterion => addEligibilityCriterion(criterion));
  }

  // Charger les récompenses
  if (hackathonData.prizes && Array.isArray(hackathonData.prizes)) {
    hackathonData.prizes.forEach(prize => addPrize(prize));
  }
}