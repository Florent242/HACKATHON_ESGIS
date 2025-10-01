// Sélecteurs pour les éléments de la page
const HACKATHON_ELEMENTS = {
    loadingSpinner: "#global-loading-spinner",
    hackathonsTable: {
        container: "#hackathonsTable tbody",
        rows: "#hackathonsTable tbody tr",
        searchInput: ".search-input",
        statusFilter: "#statusFilter",
        typeFilter: "#typeFilter",
        exportCsv: "#exportCsv",
        exportExcel: "#exportExcel"
    },
    activityFeed: {
        container: ".activity-feed",
        items: ".activity-item",
    },
    newHackathonModal: "#newHackathonModal"
};
// Au début du fichier hackathon.js
let currentTab = 0; // Déclaration globale
const tabContents = document.querySelectorAll('.tab-content');
const tabButtons = document.querySelectorAll('.tab-button');
let allHackathons = [];
let filteredHackathons = [];

// Initialize page
document.addEventListener('DOMContentLoaded', () => {
    // Initialisation
    if (tabButtons.length > 0) {
        switchTab(0);
    }

    function switchTab(tabIndex) {
        // Valider l'onglet actuel avant de changer
        if (tabIndex > currentTab) {
            const {
                isValid,
                field
            } = validateCurrentTab();
            if (!isValid && field) {
                field.reportValidity();
                return false;
            }
        }

        // Mettre à jour l'interface
        tabButtons.forEach((btn, index) => {
            const isActive = index === tabIndex;
            btn.classList.toggle('border-purple-500', isActive);
            btn.classList.toggle('text-white', isActive);
            btn.classList.toggle('bg-purple-600/20', isActive);
            btn.classList.toggle('text-gray-300', !isActive);
        });

        // Afficher le contenu de l'onglet
        tabContents.forEach((content, index) => {
            content.classList.toggle('hidden', index !== tabIndex);
        });

        currentTab = tabIndex;
        return true;
    }

    // Fonction pour valider l'onglet actuel
    function validateCurrentTab() {
        const currentTabContent = tabContents[currentTab];
        const requiredFields = currentTabContent.querySelectorAll('input[required], select[required], textarea[required]');
        let isValid = true;
        let firstInvalidField = null;

        requiredFields.forEach(field => {
            if (!field.checkValidity() && !firstInvalidField) {
                isValid = false;
                firstInvalidField = field;
            }
        });

        return {
            isValid,
            field: firstInvalidField
        };
    }


    // Gestion des clics sur les onglets
    tabButtons.forEach((button, index) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = button.getAttribute('data-tab');
            const targetIndex = Array.from(tabButtons).findIndex(btn => btn.getAttribute('data-tab') === targetTab);
            switchTab(targetIndex);
        });
    });
    // Activer le premier onglet par défaut
    if (tabButtons.length > 0) {
        switchTab(0);
    }
    loadHackathons();
    setupEventListeners();
    lucide.createIcons();
});

function setupEventListeners() {
    document.getElementById('btnNewHackathon').addEventListener('click', () => {
        openHackathonModal();
    });
    document.getElementById('btnCloseModal').addEventListener('click', closeHackathonModal);
    document.getElementById('btnCancelModal').addEventListener('click', closeHackathonModal);
    document.getElementById('hackathonForm').addEventListener('submit', handleSubmit);
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('filterType').addEventListener('change', applyFilters);
    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('btnExportCSV').addEventListener('click', exportToCSV);

    document.getElementById('hackathonModal').addEventListener('click', (e) => {
        if (e.target.id === 'hackathonModal') closeHackathonModal();
    });
}

async function loadHackathons() {
    showLoading(true);
    try {
        const response = await apiRequest('/hackathons');
        if (response.success && response.data) {
            allHackathons = response.data;

            // Fetch teams and projects for each hackathon
            await Promise.all(allHackathons.map(async (h) => {
                try {
                    const [teamsRes, projectsRes] = await Promise.all([
                        apiRequest(`/hackathons/${h.id}/teams`),
                        apiRequest(`/hackathons/${h.id}/projects`)
                    ]);
                    h.teamsCount = teamsRes.success && teamsRes.data ? teamsRes.data.length : 0;
                    h.projectsCount = projectsRes.success && projectsRes.data ? projectsRes.data.length : 0;
                } catch {
                    h.teamsCount = 0;
                    h.projectsCount = 0;
                }
            }));

            applyFilters();
        } else {
            displayEmptyState();
        }
    } catch (error) {
        showNotification('Erreur', 'Impossible de charger les hackathons', 'error');
        displayEmptyState();
    } finally {
        showLoading(false);
    }
}

function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const type = document.getElementById('filterType').value;
    const status = document.getElementById('filterStatus').value;

    filteredHackathons = allHackathons.filter(h => {
        const matchSearch = !search ||
            h.name.toLowerCase().includes(search) ||
            (h.theme && h.theme.toLowerCase().includes(search));
        const matchType = !type || h.type === type;
        const matchStatus = !status || h.status === status;
        return matchSearch && matchType && matchStatus;
    });

    renderTable();
}

function renderTable() {
    const tbody = document.getElementById('hackathonsTableBody');
    const container = document.getElementById('hackathonsContainer');
    const emptyState = document.getElementById('emptyState');

    if (filteredHackathons.length === 0) {
        container.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }

    container.classList.remove('hidden');
    emptyState.classList.add('hidden');
    tbody.innerHTML = '';

    filteredHackathons.forEach(h => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-[var(--background)] transition-colors';
        tr.innerHTML = `
            <td class="px-6 py-4 text-gray-300">#${String(h.id).padStart(3, '0')}</td>
            <td class="px-6 py-4">
                <div class="font-medium text-white">${escapeHtml(h.name)}</div>
            </td>
            <td class="px-6 py-4 text-gray-300">${escapeHtml(h.theme || '-')}</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${h.type === 'ctf' ? 'bg-red-500/20 text-red-400' : 'bg-blue-500/20 text-blue-400'}">
                    ${h.type === 'ctf' ? 'CTF' : 'Développement'}
                </span>
            </td>
            <td class="px-6 py-4 text-gray-300 text-sm">
                ${formatDate(h.start_date)}<br>
                <span class="text-gray-500">→ ${formatDate(h.end_date)}</span>
            </td>
            <td class="px-6 py-4">
                ${getStatusBadge(h.status)}
            </td>
            <td class="px-6 py-4 text-gray-300">${h.teamsCount || 0}</td>
            <td class="px-6 py-4 text-gray-300">${h.projectsCount || 0}</td>
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <button onclick="viewHackathon(${h.id})" class="p-2 text-blue-400 hover:bg-blue-500/10 rounded-lg transition-all" title="Voir détails">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                    <button onclick="editHackathon(${h.id})" class="p-2 text-purple-400 hover:bg-purple-500/10 rounded-lg transition-all" title="Modifier">
                        <i data-lucide="edit" class="w-4 h-4"></i>
                    </button>
                    <button onclick="toggleStatus(${h.id}, '${h.status}')" class="p-2 text-yellow-400 hover:bg-yellow-500/10 rounded-lg transition-all" title="Changer statut">
                        <i data-lucide="toggle-left" class="w-4 h-4"></i>
                    </button>
                    <button onclick="deleteHackathon(${h.id})" class="p-2 text-red-400 hover:bg-red-500/10 rounded-lg transition-all" title="Supprimer">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    lucide.createIcons();
}

function getStatusBadge(status) {
    const badges = {
        draft: 'bg-gray-500/20 text-gray-400',
        active: 'bg-green-500/20 text-green-400',
        inactive: 'bg-orange-500/20 text-orange-400',
        ended: 'bg-red-500/20 text-red-400'
    };
    const labels = { draft: 'Brouillon', active: 'Actif', inactive: 'Inactif', ended: 'Terminé' };
    return `<span class="px-3 py-1 rounded-full text-xs font-semibold ${badges[status] || badges.draft}">${labels[status] || status}</span>`;
}

function openHackathonModal(hackathon = null) {
    const modal = document.getElementById('hackathonModal');
    const form = document.getElementById('hackathonForm');
    const title = document.getElementById('modalTitle');
    const submitText = document.getElementById('submitButtonText');

    // Réinitialisation complète du formulaire
    form.reset();

    // Réinitialisation des champs non gérés par reset()
    document.getElementById('hackathonId').value = '';
    document.getElementById('hackathonDescription').value = '';

    if (hackathon) {
        // Remplissage des champs avec les données du hackathon
        const fields = {
            'hackathonId': hackathon.id,
            'hackathonName': hackathon.name,
            'hackathonTheme': hackathon.theme || '',
            'hackathonType': hackathon.type || 'ctf',
            'hackathonStatus': hackathon.status || 'draft',
            'hackathonVisibility': hackathon.visibility || 'public',
            'hackathonDescription': hackathon.description || '',
            'hackathonLocation': hackathon.location || '',
            'hackathonMaxTeams': hackathon.max_teams || 10,
            'hackathonMinMembers': hackathon.min_team_members || 1,
            'hackathonMaxMembers': hackathon.max_team_members || 4
        };

        // Remplir les champs
        Object.entries(fields).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.value = value;
        });

        // Gestion des dates
        const dateFields = {
            'hackathonStartDate': hackathon.start_date,
            'hackathonEndDate': hackathon.end_date,
            'hackathonRegistrationDeadline': hackathon.registration_deadline
        };

        Object.entries(dateFields).forEach(([id, dateValue]) => {
            const element = document.getElementById(id);
            if (element && dateValue) {
                element.value = formatDateTimeForInput(dateValue);
            }
        });

        // Mise à jour de l'interface
        title.innerHTML = '<i data-lucide="edit" class="w-6 h-6"></i> Modifier le Hackathon';
        submitText.textContent = 'Mettre à jour';
    } else {
        // Valeurs par défaut pour un nouveau hackathon
        const defaultValues = {
            'hackathonType': 'ctf',
            'hackathonStatus': 'draft',
            'hackathonVisibility': 'public',
            'hackathonMaxTeams': 10,
            'hackathonMinMembers': 1,
            'hackathonMaxMembers': 4
        };

        Object.entries(defaultValues).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.value = value;
        });

        title.innerHTML = '<i data-lucide="plus-circle" class="w-6 h-6"></i> Nouveau Hackathon';
        submitText.textContent = 'Créer';
    }

    // Afficher la modale
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Mettre à jour les icônes Lucide
    lucide.createIcons();
}

// Fonction utilitaire pour formater les dates
function formatDateTimeForInput(dateTimeString) {
    if (!dateTimeString) return '';
    try {
        const date = new Date(dateTimeString);
        return date.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
    } catch (e) {
        console.error('Erreur de format de date:', e);
        return '';
    }
}

function closeHackathonModal() {
    document.getElementById('hackathonModal').classList.add('hidden');
    document.body.style.overflow = '';
}

async function handleSubmit(e) {
    e.preventDefault();

    const id = document.getElementById('hackathonId').value;
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;
    let currentTabIndex = 0;

    try {
        // Désactiver le bouton de soumission
        submitButton.disabled = true;
        submitButton.innerHTML = '<i data-lucide="loader" class="animate-spin w-4 h-4"></i> Traitement...';
        lucide.createIcons();

        // Valider tous les onglets avant soumission
        let allValid = true;
        let firstInvalidTab = -1;

        tabContents.forEach((tab, index) => {
            currentTab = index;
            const { isValid, field } = validateCurrentTab();
            if (!isValid && firstInvalidTab === -1) {
                allValid = false;
                firstInvalidTab = index;
            }
        });

        if (!allValid) {
            switchTab(firstInvalidTab);
            return false; // Empêcher la soumission du formulaire
        }

        // Récupération des données du formulaire
        const formData = {
            name: form.hackathonName.value.trim(),
            slug: form.hackathonSlug?.value?.trim() || null,
            theme: form.hackathonTheme.value?.trim() || null,
            type: form.hackathonType.value,
            status: form.hackathonStatus.value,
            visibility: form.hackathonVisibility.value,
            description: form.hackathonDescription.value?.trim() || null,
            start_date: form.hackathonStartDate.value,
            end_date: form.hackathonEndDate.value,
            registration_deadline: form.hackathonRegistrationDeadline.value || null,
            location: form.hackathonLocation.value?.trim() || null,
            max_teams: parseInt(form.hackathonMaxTeams.value) || 10,
            min_team_members: parseInt(form.hackathonMinMembers.value) || 1,
            max_team_members: parseInt(form.hackathonMaxMembers.value) || 4,
            created_by: await getUserId() || 0,
            rules: form.hackathonRules ? JSON.parse(form.hackathonRules.value || '[]') : [],
            eligibility_criteria: form.hackathonEligibility ? JSON.parse(form.hackathonEligibility.value || '[]') : [],
            prizes: form.hackathonPrizes ? JSON.parse(form.hackathonPrizes.value || '[]') : []
        };

        // Validation des dates
        const startDate = new Date(formData.start_date);
        const endDate = new Date(formData.end_date);

        if (endDate <= startDate) {
            showNotification('Erreur', 'La date de fin doit être postérieure à la date de début', 'error');
            document.getElementById('hackathonEndDate').focus();
            return;
        }

        if (formData.registration_deadline) {
            const regDeadline = new Date(formData.registration_deadline);
            if (regDeadline > startDate) {
                showNotification('Erreur', 'La date limite d\'inscription doit être antérieure à la date de début', 'error');
                document.getElementById('hackathonRegistrationDeadline').focus();
                return;
            }
        }

        // Envoi de la requête
        const response = await apiRequest(
            id ? `/hackathons/${id}` : '/hackathons',
            {
                method: id ? 'PUT' : 'POST',
                body: JSON.stringify(formData),
                headers: {
                    'Content-Type': 'application/json',
                }
            }
        );

        if (response.success) {
            showNotification(
                'Succès',
                id ? 'Hackathon modifié avec succès' : 'Hackathon créé avec succès',
                'success'
            );
            closeHackathonModal();
            loadHackathons();
        } else {
            throw new Error(response.message || response.error || 'Une erreur est survenue');
        }

    } catch (error) {
        console.error('Erreur lors de la soumission du formulaire:', error);
        showNotification(
            'Erreur',
            error.response?.data?.error || error.message || 'Une erreur est survenue lors de la sauvegarde',
            'error'
        );
    } finally {
        // Réactiver le bouton de soumission
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            lucide.createIcons();
        }

        // Restaurer l'onglet actuel
        if (currentTab !== undefined) {
            currentTab = currentTabIndex;
        }
    }
}

async function editHackathon(id) {
    try {
        const response = await apiRequest(`/hackathons/${id}`);
        if (response.success && response.data) {
            openHackathonModal(response.data);
        }
    } catch (error) {
        showNotification('Erreur', 'Impossible de charger les détails', 'error');
    }
}

function viewHackathon(id) {
    window.location.href = `/admin/hackathon-details/${id}`;
}

async function toggleStatus(id, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    try {
        const response = await apiRequest(`/hackathons/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ status: newStatus })
        });
        if (response.success) {
            showNotification('Succès', `Hackathon ${newStatus === 'active' ? 'activé' : 'désactivé'}`, 'success');
            loadHackathons();
        }
    } catch (error) {
        showNotification('Erreur', 'Impossible de changer le statut', 'error');
    }
}

async function deleteHackathon(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce hackathon ?')) return;

    try {
        const response = await apiRequest(`/hackathons/${id}`, { method: 'DELETE' });
        if (response.success) {
            showNotification('Succès', 'Hackathon supprimé avec succès', 'success');
            loadHackathons();
        }
    } catch (error) {
        showNotification('Erreur', 'Impossible de supprimer le hackathon', 'error');
    }
}

// Fonctions pour gérer les règles
function addRule(rule = { title: '', description: '' }, index = null) {
    const container = document.getElementById('rules-container');
    const ruleId = index !== null ? index : container.children.length;

    const ruleElement = document.createElement('div');
    ruleElement.className = 'rule-item bg-[var(--background)] border border-[var(--border)] rounded-lg p-4';
    ruleElement.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-medium text-white">Règle #${ruleId + 1}</h4>
            <button type="button" class="delete-rule text-gray-400 hover:text-red-400">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-sm text-gray-300 mb-1">Titre *</label>
                <input type="text" class="rule-title w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    value="${escapeHtml(rule.title)}" oninput="updateHiddenFields()" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Description *</label>
                <textarea class="rule-description w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    rows="2" oninput="updateHiddenFields()" required>${escapeHtml(rule.description || '')}</textarea>
            </div>
        </div>
    `;

    // Ajouter le gestionnaire d'événements pour le bouton de suppression
    ruleElement.querySelector('.delete-rule').addEventListener('click', function () {
        ruleElement.remove();
        updateHiddenFields();
    });

    if (index !== null && container.children[index]) {
        container.replaceChild(ruleElement, container.children[index]);
    } else {
        container.appendChild(ruleElement);
    }

    lucide.createIcons();
    updateHiddenFields();
}

// Fonctions pour gérer les critères d'éligibilité
function addEligibilityCriterion(criterion = { field: 'team_size', operator: '<=', value: '', description: '' }, index = null) {
    const container = document.getElementById('eligibility-container');
    const criterionId = index !== null ? index : container.children.length;

    const fieldOptions = {
        'team_size': 'Taille de l\'équipe',
        'type': 'Type d\'équipe',
        'school': 'École'
    };

    const operatorOptions = {
        '<': 'Inférieur à',
        '<=': 'Inférieur ou égal à',
        '>': 'Supérieur à',
        '>=': 'Supérieur ou égal à',
        '==': 'Égal à',
        '!=': 'Différent de',
        'in': 'Dans la liste'
    };

    const criterionElement = document.createElement('div');
    criterionElement.className = 'criterion-item bg-[var(--background)] border border-[var(--border)] rounded-lg p-4';

    criterionElement.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-medium text-white">Critère #${criterionId + 1}</h4>
            <button type="button" class="delete-rule text-gray-400 hover:text-red-400">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-300 mb-1">Champ *</label>
                <select class="eligibility-field w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    onchange="updateEligibilityField(this)" required>
                    ${Object.entries(fieldOptions).map(([value, label]) =>
        `<option value="${value}" ${criterion.field === value ? 'selected' : ''}>${label}</option>`
    ).join('')}
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Opérateur *</label>
                <select class="eligibility-operator w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    onchange="updateHiddenFields()" required>
                    ${Object.entries(operatorOptions).map(([value, label]) =>
        `<option value="${value}" ${criterion.operator === value ? 'selected' : ''}>${label}</option>`
    ).join('')}
                </select>
            </div>
            <div class="eligibility-value-container">
                <label class="block text-sm text-gray-300 mb-1">Valeur *</label>
                <input type="text" class="eligibility-value w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    value="${criterion.field === 'type' ? (Array.isArray(criterion.value) ? criterion.value.join(',') : criterion.value) : criterion.value}" 
                    oninput="updateHiddenFields()" required>
                ${criterion.field === 'type' ?
            '<p class="text-xs text-gray-400 mt-1">Séparez les valeurs par des virgules (ex: ctf,mixte)</p>' : ''}
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-300 mb-1">Description *</label>
                <input type="text" class="eligibility-description w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    value="${escapeHtml(criterion.description || '')}" oninput="updateHiddenFields()" required>
            </div>
        </div>
    `;

    if (index !== null && container.children[index]) {
        container.replaceChild(criterionElement, container.children[index]);
    } else {
        container.appendChild(criterionElement);
    }

    const deleteBtn = criterionElement.querySelector('.delete-rule');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            criterionElement.remove();
            updateHiddenFields();
        });
    }

    lucide.createIcons();
    updateHiddenFields();
}

// Fonction pour mettre à jour le champ de valeur en fonction du type de champ sélectionné
function updateEligibilityField(select) {
    const container = select.closest('.grid').querySelector('.eligibility-value-container');
    const valueInput = container.querySelector('.eligibility-value');
    const fieldType = select.value;

    if (fieldType === 'type') {
        valueInput.placeholder = 'ctf,mixte,dev';
        container.querySelector('p')?.remove();
        container.insertAdjacentHTML('beforeend',
            '<p class="text-xs text-gray-400 mt-1">Séparez les valeurs par des virgules (ex: ctf,mixte)</p>'
        );
    } else if (fieldType === 'team_size') {
        valueInput.placeholder = '4';
        container.querySelector('p')?.remove();
        valueInput.type = 'number';
        valueInput.min = '1';
    } else {
        valueInput.placeholder = 'Valeur';
        container.querySelector('p')?.remove();
        valueInput.type = 'text';
    }

    updateHiddenFields();
}

// Fonctions pour gérer les récompenses
function addPrize(prize = { rank: '', label: '', reward: '' }, index = null) {
    const container = document.getElementById('prizes-container');
    const prizeId = index !== null ? index : container.children.length;

    const prizeElement = document.createElement('div');
    prizeElement.className = 'prize-item bg-[var(--background)] border border-[var(--border)] rounded-lg p-4';

    prizeElement.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-medium text-white">Récompense #${prizeId + 1}</h4>
            <button type="button" class="delete-rule text-gray-400 hover:text-red-400">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-gray-300 mb-1">Rang *</label>
                <input type="number" min="1" class="prize-rank w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    value="${prize.rank || ''}" oninput="updateHiddenFields()" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Libellé *</label>
                <input type="text" class="prize-label w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    value="${escapeHtml(prize.label || '')}" oninput="updateHiddenFields()" required>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">Récompense *</label>
                <input type="text" class="prize-reward w-full bg-[var(--background-light)] border border-[var(--border)] text-white px-3 py-2 rounded-md" 
                    value="${escapeHtml(prize.reward || '')}" oninput="updateHiddenFields()" required>
            </div>
        </div>
    `;


    if (index !== null && container.children[index]) {
        container.replaceChild(prizeElement, container.children[index]);
    } else {
        container.appendChild(prizeElement);
    }

    const deleteBtn = prizeElement.querySelector('.delete-rule');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            prizeElement.remove();
            updateHiddenFields();
        });
    }

    lucide.createIcons();
    updateHiddenFields();
}

// Fonction pour mettre à jour les champs cachés
function updateHiddenFields() {
    // Mettre à jour les règles
    const rules = [];
    document.querySelectorAll('#rules-container > div').forEach((ruleEl, index) => {
        rules.push({
            title: ruleEl.querySelector('.rule-title').value,
            description: ruleEl.querySelector('.rule-description').value
        });
    });
    document.getElementById('hackathonRules').value = JSON.stringify(rules);

    // Mettre à jour les critères d'éligibilité
    const criteria = [];
    document.querySelectorAll('#eligibility-container > div').forEach((criterionEl, index) => {
        const field = criterionEl.querySelector('.eligibility-field').value;
        let value = criterionEl.querySelector('.eligibility-value').value;

        // Convertir la valeur en fonction du type de champ
        if (field === 'team_size') {
            value = parseInt(value) || 0;
        } else if (field === 'type') {
            value = value.split(',').map(v => v.trim()).filter(v => v);
        }

        criteria.push({
            field: field,
            operator: criterionEl.querySelector('.eligibility-operator').value,
            value: value,
            description: criterionEl.querySelector('.eligibility-description').value
        });
    });
    document.getElementById('hackathonEligibility').value = JSON.stringify(criteria);

    // Mettre à jour les récompenses
    const prizes = [];
    document.querySelectorAll('#prizes-container > div').forEach((prizeEl, index) => {
        prizes.push({
            rank: parseInt(prizeEl.querySelector('.prize-rank').value) || 0,
            label: prizeEl.querySelector('.prize-label').value,
            reward: prizeEl.querySelector('.prize-reward').value
        });
    });
    // Trier les récompenses par rang
    prizes.sort((a, b) => a.rank - b.rank);
    document.getElementById('hackathonPrizes').value = JSON.stringify(prizes);
}

// Fonction utilitaire pour échapper le HTML
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Initialiser les champs au chargement
document.addEventListener('DOMContentLoaded', function () {
    // Ajouter des exemples si les champs sont vides
    if (document.getElementById('hackathonRules').value === '') {
        const defaultRules = [
            { title: "Accès aux challenges", description: "Les challenges sont accessibles via l'interface web CTF." },
            { title: "Interdiction du partage de flag", description: "Le flag découvert doit rester confidentiel. En cas de fraude vous serez disqualifié" },
            { title: "One shot", description: "Aucune modification d'équipe après validation." }
        ];
        defaultRules.forEach(rule => addRule(rule));
    }

    if (document.getElementById('hackathonEligibility').value === '') {
        const defaultCriteria = [
            { field: "team_size", operator: "<=", value: "4", description: "Maximum 4 membres par équipe" },
            { field: "type", operator: "in", value: "ctf,mixte", description: "Équipe de type CTF ou mixte uniquement" }
        ];
        defaultCriteria.forEach((criterion, index) => addEligibilityCriterion(criterion, index));
    }

    if (document.getElementById('hackathonPrizes').value === '') {
        const defaultPrizes = [
            { rank: 1, label: "1er Prix", reward: "Bientôt révélé" },
            { rank: 2, label: "2e Prix", reward: "Bientôt révélé" },
            { rank: 3, label: "3e Prix", reward: "Bientôt révélé" }
        ];
        defaultPrizes.forEach(prize => addPrize(prize));
    }
});

// Fonction pour charger les données existantes
function loadHackathonData(data) {
    // Charger les règles
    if (data.rules) {
        try {
            const rules = typeof data.rules === 'string' ? JSON.parse(data.rules) : data.rules;
            rules.forEach((rule, index) => addRule(rule, index));
        } catch (e) {
            console.error('Erreur lors du chargement des règles:', e);
        }
    }

    // Charger les critères d'éligibilité
    if (data.eligibility_criteria) {
        try {
            const criteria = typeof data.eligibility_criteria === 'string'
                ? JSON.parse(data.eligibility_criteria)
                : data.eligibility_criteria;
            criteria.forEach((criterion, index) => addEligibilityCriterion(criterion, index));
        } catch (e) {
            console.error('Erreur lors du chargement des critères d\'éligibilité:', e);
        }
    }

    // Charger les récompenses
    if (data.prizes) {
        try {
            const prizes = typeof data.prizes === 'string' ? JSON.parse(data.prizes) : data.prizes;
            prizes.forEach((prize, index) => addPrize(prize, index));
        } catch (e) {
            console.error('Erreur lors du chargement des récompenses:', e);
        }
    }
}

function exportToCSV() {
    if (filteredHackathons.length === 0) {
        showNotification('Info', 'Aucune donnée à exporter', 'info');
        return;
    }

    const headers = ['ID', 'Nom', 'Thème', 'Type', 'Date début', 'Date fin', 'Statut', 'Lieu', 'Équipes', 'Projets'];
    const rows = filteredHackathons.map(h => [
        h.id,
        h.name,
        h.theme || '',
        h.type,
        h.start_date,
        h.end_date,
        h.status,
        h.location || '',
        h.teamsCount || 0,
        h.projectsCount || 0
    ]);

    const csv = [headers, ...rows].map(row => row.map(cell => `"${cell}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `hackathons_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();

    showNotification('Succès', 'Export CSV téléchargé', 'success');
}

function showLoading(show) {
    document.getElementById('loadingState').classList.toggle('hidden', !show);
    document.getElementById('hackathonsContainer').classList.toggle('hidden', show);
    document.getElementById('emptyState').classList.add('hidden');
}

function displayEmptyState() {
    document.getElementById('hackathonsContainer').classList.add('hidden');
    document.getElementById('emptyState').classList.remove('hidden');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTimeForInput(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toISOString().slice(0, 16);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
