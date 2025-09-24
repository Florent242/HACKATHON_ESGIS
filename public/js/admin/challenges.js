// Configuration de base
const API_BASE_URL = "/api";
const CHALLENGES_PER_PAGE = 10;

// État global de l'application
let appState = {
    challenges: [],
    filteredChallenges: [],
    currentPage: 1,
    totalPages: 1,
    totalChallenges: 0,
    editingChallenge: null,
    codeEditors: {},
    hackathons: [],
    phases: [],
    technologies: []
};

// Sélecteurs pour les éléments de la page
const ELEMENTS = {
    loadingSpinner: "#global-loading-spinner",
    stats: {
        totalChallenges: "#totalChallenges",
        activeChallenges: "#activeChallenges",
        totalParticipants: "#totalParticipants",
        totalPoints: "#totalPoints"
    },
    table: {
        body: "#challengesTableBody",
        selectAll: "#selectAll"
    },
    filters: {
        search: ".search-input",
        type: "#typeFilter",
        difficulty: "#difficultyFilter",
        status: "#statusFilter"
    },
    pagination: {
        prev: "#prevPage",
        next: "#nextPage",
        current: "#currentPage",
        total: "#totalPages",
        info: "#paginationInfo"
    },
    modal: {
        challenge: "#challengeModal",
        deleteConfirm: "#deleteConfirmModal"
    },
    form: {
        challenge: "#challengeForm",
        challengeId: "#challengeId",
        title: "#title",
        type: "#type",
        category: "#category",
        difficulty: "#difficulty",
        hackathon_id: "#hackathon_id",
        phase_id: "#phase_id",
        points: "#points",
        is_active: "#is_active",
        description: "#description",
        instructions: "#instructions",
        url_path: "#url_path",
        resource_link: "#resource_link",
        hint: "#hint",
        is_dynamic: "#is_dynamic",
        algo_config: "#algo_config",
        created_by: "#created_by"
    },
    tabs: {
        general: "#generalTab",
        content: "#contentTab",
        configuration: "#configurationTab",
        flags: "#flagsTab",
        code: "#codeTab",
        technologies: "#technologiesTab"
    },
    containers: {
        flags: "#flagsContainer",
        tests: "#testsContainer",
        technologies: "#technologiesContainer"
    },
    buttons: {
        addFlag: "#addFlagBtn",
        addTest: "#addTestBtn",
        addTechnology: "#addTechnologyBtn",
        export: "#exportBtn",
        confirmDelete: "#confirmDeleteBtn"
    }
};

/**
 * Wizard step-by-step navigation for the challenge form
 */
function initWizardNavigation() {
    const prevBtn = document.getElementById('wizardPrev');
    const nextBtn = document.getElementById('wizardNext');
    const submitBtn = document.getElementById('wizardSubmit');
    const form = document.querySelector(ELEMENTS.form.challenge);

    if (!form || !prevBtn || !nextBtn || !submitBtn) return;

    // Compute current wizard order based on visible tab buttons
    function getWizardTabs() {
        const base = ['general', 'content', 'configuration'];
        const optional = ['flags', 'code', 'technologies'];
        const result = [...base];
        optional.forEach(tab => {
            const btn = document.querySelector(`.tab-button[data-tab="${tab}"]`);
            if (btn && btn.style.display !== 'none') {
                result.push(tab);
            }
        });
        // Ensure unique and existing tab-content elements
        return result.filter((tab, idx) => result.indexOf(tab) === idx && document.getElementById(`${tab}Tab`));
    }

    function currentTabName() {
        const active = document.querySelector('.tab-button.active');
        return active ? active.getAttribute('data-tab') : 'general';
    }

    function goToTab(tab) {
        switchTab(tab);
        updateControls();
    }

    function goToIndex(delta) {
        const order = getWizardTabs();
        const cur = currentTabName();
        const idx = Math.max(0, order.indexOf(cur));
        const nextIdx = Math.min(order.length - 1, Math.max(0, idx + delta));
        if (nextIdx !== idx) {
            goToTab(order[nextIdx]);
        }
    }

    function updateControls() {
        const order = getWizardTabs();
        const cur = currentTabName();
        const idx = Math.max(0, order.indexOf(cur));
        prevBtn.disabled = idx <= 0;
        const isLast = idx >= order.length - 1;
        nextBtn.style.display = isLast ? 'none' : 'inline-flex';
        submitBtn.style.display = isLast ? 'inline-flex' : 'none';
    }

    function isFieldEmpty(field) {
        if (field.type === 'checkbox' || field.type === 'radio') {
            return !field.checked;
        }
        return !String(field.value || '').trim();
    }

    function validateCurrentTab() {
        const cur = currentTabName();
        const panel = document.getElementById(`${cur}Tab`);
        if (!panel) return true;
        let ok = true;
        const required = panel.querySelectorAll('input[required], select[required], textarea[required]');
        required.forEach(field => {
            // Clear previous inline errors
            const err = field.parentNode.querySelector('.field-error');
            if (err) err.remove();
            field.classList.remove('is-invalid');

            if (isFieldEmpty(field)) {
                ok = false;
                try { if (typeof validateField === 'function') validateField(field); } catch (e) {
                    field.classList.add('is-invalid');
                    const div = document.createElement('div');
                    div.className = 'field-error';
                    div.textContent = 'Ce champ est requis';
                    const parent = field.parentNode.classList.contains('form-group') ? field.parentNode : field.parentNode;
                    parent.appendChild(div);
                }
            }
        });
        if (!ok && typeof showNotification === 'function') {
            showNotification('Attention', 'Veuillez compléter les informations de cet onglet avant de continuer.', 'warning');
        }
        return ok;
    }

    prevBtn.addEventListener('click', (e) => {
        e.preventDefault();
        goToIndex(-1);
    });

    nextBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (!validateCurrentTab()) return;
        goToIndex(1);
    });

    // Recompute wizard when type/category impacts optional tabs
    document.querySelector(ELEMENTS.form.type)?.addEventListener('change', () => {
        updateControls();
    });
    document.querySelector(ELEMENTS.form.category)?.addEventListener('change', () => {
        updateControls();
    });

    // Initialize on modal open
    updateControls();
}


/**
 * Initialise la page de gestion des challenges
 */
async function initializeChallengesPage() {
    try {
        showLoading();
        
        // Charger toutes les données en parallèle
        await Promise.all([
            loadChallenges(),
            loadStats(),
            loadHackathons(),
            loadTechnologies()
        ]);
        
        // Configurer les gestionnaires d'événements
        setupEventListeners();
        setupFormValidation();
        initializeCodeEditors();
        
    } catch (error) {
        handleError("Erreur lors de l'initialisation de la page", error);
    } finally {
        hideLoading();
    }
}

/**
 * Charge la liste des challenges
 */
async function loadChallenges() {
    try {
        const response = await apiRequest("/admin/challenges", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include"
        })

        if (response.success && response.data) {
            appState.challenges = response.data;
            appState.filteredChallenges = [...response.data];
            updateChallengesTable();
            updatePagination();
        }
    } catch (error) {
        handleError("Erreur lors du chargement des challenges", error);
    }
}

/**
 * Charge les statistiques
 */
async function loadStats() {
    try {
        const response = await apiRequest("/admin/challenges/stats", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include"
        })

        if (response.success && response.data) {
            updateStats(response.data);
        }
    } catch (error) {
        handleError("Erreur lors du chargement des statistiques", error);
    }
}

/**
 * Charge la liste des hackathons
 */
async function loadHackathons() {
    try {
        const response = await apiRequest("/admin/hackathons", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include"
        })
        console.log(response);
        if (response.success && response.data) {
            appState.hackathons = response.data;
            updateHackathonSelect();
        }
    } catch (error) {
        handleError("Erreur lors du chargement des hackathons", error);
    }
}

/**
 * Charge la liste des technologies
 */
async function loadTechnologies() {
    try {
        const response = await apiRequest("/admin/technologies", {
            method: "GET",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include"
        })
        
        if (response.success && response.data) {
            appState.technologies = response.data;
        }
    } catch (error) {
        handleError("Erreur lors du chargement des technologies", error);
    }
}

/**
 * Met à jour le tableau des challenges
 */
function updateChallengesTable() {
    const container = document.querySelector(ELEMENTS.table.body);
    if (!container) return;

    const startIndex = (appState.currentPage - 1) * CHALLENGES_PER_PAGE;
    const endIndex = startIndex + CHALLENGES_PER_PAGE;
    const pageChallenges = appState.filteredChallenges.slice(startIndex, endIndex);

    container.innerHTML = "";

    if (!pageChallenges.length) {
        container.innerHTML = `
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="empty-state-text">
                            <h3>Aucun challenge trouvé</h3>
                            <p>Créez votre premier challenge en cliquant sur le bouton "Nouveau Challenge".</p>
                        </div>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    pageChallenges.forEach((challenge, index) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>
                <input type="checkbox" class="checkbox-input" value="${challenge.id}">
            </td>
            <td>
                <div class="challenge-title">
                    <strong>${sanitizeText(challenge.title)}</strong>
                    ${challenge.category ? `<span class="challenge-category">${challenge.category}</span>` : ''}
                </div>
            </td>
            <td>
                <span class="badge badge-${getTypeBadgeClass(challenge.type)}">
                    ${getTypeLabel(challenge.type)}
                </span>
            </td>
            <td>
                <span class="badge badge-${getDifficultyBadgeClass(challenge.difficulty)}">
                    ${getDifficultyLabel(challenge.difficulty)}
                </span>
            </td>
            <td>
                <span class="points-badge">
                    <i class="fas fa-star"></i>
                    ${challenge.points}
                </span>
            </td>
            <td>
                <span class="participants-count">
                    ${challenge.participants_count || 0}
                </span>
            </td>
            <td>
                <span class="badge badge-${challenge.is_active ? 'success' : 'secondary'}">
                    ${challenge.is_active ? 'Actif' : 'Inactif'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="viewChallenge(${challenge.id})" title="Voir">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editChallenge(${challenge.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteChallenge(${challenge.id})" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        container.appendChild(row);
    });
}

/**
 * Met à jour les statistiques
 */
function updateStats(stats) {
    document.querySelector(ELEMENTS.stats.totalChallenges).textContent = stats.total || 0;
    document.querySelector(ELEMENTS.stats.activeChallenges).textContent = stats.active || 0;
    document.querySelector(ELEMENTS.stats.totalParticipants).textContent = stats.participants || 0;
    document.querySelector(ELEMENTS.stats.totalPoints).textContent = stats.totalPoints || 0;
}

/**
 * Met à jour la pagination
 */
function updatePagination() {
    appState.totalPages = Math.ceil(appState.filteredChallenges.length / CHALLENGES_PER_PAGE);
    
    const startIndex = (appState.currentPage - 1) * CHALLENGES_PER_PAGE + 1;
    const endIndex = Math.min(appState.currentPage * CHALLENGES_PER_PAGE, appState.filteredChallenges.length);
    
    document.querySelector(ELEMENTS.pagination.current).textContent = appState.currentPage;
    document.querySelector(ELEMENTS.pagination.total).textContent = appState.totalPages;
    document.querySelector(ELEMENTS.pagination.info).textContent = 
        `Affichage de ${startIndex} à ${endIndex} sur ${appState.filteredChallenges.length} résultats`;
    
    document.querySelector(ELEMENTS.pagination.prev).disabled = appState.currentPage <= 1;
    document.querySelector(ELEMENTS.pagination.next).disabled = appState.currentPage >= appState.totalPages;
}

/**
 * Met à jour le select des hackathons
 */
function updateHackathonSelect() {
    const select = document.querySelector(ELEMENTS.form.hackathon_id);
    if (!select) return;

    select.innerHTML = '<option value="">Sélectionner un hackathon</option>';
    appState.hackathons.forEach(hackathon => {
        const option = document.createElement('option');
        option.value = hackathon.id;
        option.textContent = hackathon.name;
        select.appendChild(option);
    });
}

/**
 * Configure les gestionnaires d'événements
 */
function setupEventListeners() {
    // Filtres
    document.querySelector(ELEMENTS.filters.search).addEventListener('input', handleSearch);
    document.querySelector(ELEMENTS.filters.type).addEventListener('change', handleFilter);
    document.querySelector(ELEMENTS.filters.difficulty).addEventListener('change', handleFilter);
    document.querySelector(ELEMENTS.filters.status).addEventListener('change', handleFilter);
    
    // Pagination
    document.querySelector(ELEMENTS.pagination.prev).addEventListener('click', () => changePage(-1));
    document.querySelector(ELEMENTS.pagination.next).addEventListener('click', () => changePage(1));
    
    // Formulaire
    document.querySelector(ELEMENTS.form.challenge).addEventListener('submit', handleFormSubmit);
    document.querySelector(ELEMENTS.form.type).addEventListener('change', handleTypeChange);
    document.querySelector(ELEMENTS.form.hackathon_id).addEventListener('change', handleHackathonChange);
    
    // Boutons
    document.querySelector('[data-modal="newChallengeModal"]').addEventListener('click', () => openNewChallengeModal());
    document.querySelector(ELEMENTS.buttons.addFlag).addEventListener('click', addFlag);
    document.querySelector(ELEMENTS.buttons.addTest).addEventListener('click', addTest);
    document.querySelector(ELEMENTS.buttons.addTechnology).addEventListener('click', addTechnology);
    document.querySelector(ELEMENTS.buttons.export).addEventListener('click', exportChallenges);
    document.querySelector(ELEMENTS.buttons.confirmDelete).addEventListener('click', confirmDelete);
    
    // Modals
    setupModalEventListeners();
    
    // Onglets
    setupTabEventListeners();

    // Wizard navigation (Prev/Next/Submit)
    initWizardNavigation();
}

/**
 * Configure la validation du formulaire
 */
function setupFormValidation() {
    const form = document.querySelector(ELEMENTS.form.challenge);
    
    // Validation en temps réel
    form.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
        field.addEventListener('blur', () => validateField(field));
        field.addEventListener('input', () => clearFieldError(field));
    });
}

/**
 * Initialise les éditeurs de code
 */
function initializeCodeEditors() {
    if (!appState.codeEditors) {
        appState.codeEditors = {};
    }
    
    // Configuration des éditeurs par langage
    const editorsConfig = [
        { lang: 'python', mode: 'python' },
        { lang: 'bash', mode: 'shell' },
        { lang: 'javascript', mode: 'javascript' },
        { lang: 'cpp', mode: 'text/x-c++src' },
        { lang: 'c', mode: 'text/x-csrc' },
        { lang: 'csharp', mode: 'text/x-csharp' },
        { lang: 'php', mode: 'php', options: { htmlMode: true } },
        { lang: 'ruby', mode: 'ruby' },
        { lang: 'pascal', mode: 'text/x-pascal' },
        { lang: 'typescript', mode: 'text/typescript' }
    ];

    editorsConfig.forEach(config => {
        const textarea = document.querySelector(`#${config.lang}_snippet`);
        if (textarea && !appState.codeEditors[config.lang]) {
            const editorOptions = {
                mode: config.mode,
                theme: 'monokai',
                lineNumbers: true,
                autoCloseBrackets: true,
                matchBrackets: true,
                indentUnit: 4,
                tabSize: 4,
                lineWrapping: true,
                readOnly: false,
                viewportMargin: Infinity,
                ...(config.options || {}) // Options spécifiques au langage
            };

            try {
                const editor = CodeMirror.fromTextArea(textarea, editorOptions);
                appState.codeEditors[config.lang] = editor;
                
                // Rafraîchir l'éditeur après un court délai
                setTimeout(() => {
                    if (editor.refresh) editor.refresh();
                }, 100);
            } catch (error) {
                console.error(`Erreur lors de l'initialisation de l'éditeur ${config.lang}:`, error);
            }
        }
    });
}

// Mise à jour de la fonction getCodeMirrorMode
function getCodeMirrorMode(language) {
    const modes = {
        'python': 'python',
        'bash': 'shell',
        'javascript': 'javascript',
        'cpp': 'text/x-c++src',
        'c': 'text/x-csrc',
        'csharp': 'text/x-csharp',
        'php': 'php',
        'ruby': 'ruby',
        'pascal': 'pascal',
        'typescript': 'text/typescript'
    };
    return modes[language] || 'text/plain';
}

/**
 * Gère la recherche
 */
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    appState.filteredChallenges = appState.challenges.filter(challenge => 
        challenge.title.toLowerCase().includes(searchTerm) ||
        challenge.description.toLowerCase().includes(searchTerm) ||
        (challenge.category && challenge.category.toLowerCase().includes(searchTerm))
    );
    
    appState.currentPage = 1;
    updateChallengesTable();
    updatePagination();
}

/**
 * Gère les filtres
 */
function handleFilter() {
    const typeFilter = document.querySelector(ELEMENTS.filters.type).value;
    const difficultyFilter = document.querySelector(ELEMENTS.filters.difficulty).value;
    const statusFilter = document.querySelector(ELEMENTS.filters.status).value;
    
    appState.filteredChallenges = appState.challenges.filter(challenge => {
        const typeMatch = !typeFilter || challenge.type === typeFilter;
        const difficultyMatch = !difficultyFilter || challenge.difficulty === difficultyFilter;
        const statusMatch = !statusFilter || challenge.is_active.toString() === statusFilter;
        
        return typeMatch && difficultyMatch && statusMatch;
    });
    
    appState.currentPage = 1;
    updateChallengesTable();
    updatePagination();
}

/**
 * Change de page
 */
function changePage(direction) {
    const newPage = appState.currentPage + direction;
    
    if (newPage >= 1 && newPage <= appState.totalPages) {
        appState.currentPage = newPage;
        updateChallengesTable();
        updatePagination();
    }
}

/**
 * Ouvre le modal de nouveau challenge
 */
function openNewChallengeModal() {
    resetForm();
    openModal(ELEMENTS.modal.challenge);
    document.querySelector('#modalTitleText').textContent = 'Nouveau Challenge';
    document.querySelector('#saveButtonText').textContent = 'Créer';
}

/**
 * Gère la soumission du formulaire
 */
async function handleFormSubmit(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
        return;
    }
    
    try {
        showLoading();
        
        const formData = new FormData(e.target);
        const challengeData = Object.fromEntries(formData.entries());
        
        // Traitement des données spéciales
        challengeData.flags = getFlagsData();
        challengeData.tests = getTestsData();
        challengeData.snippets = getSnippetsData();
        challengeData.technologies = getTechnologiesData();
        
        // Validation JSON
        if (challengeData.hint) {
            try {
                JSON.parse(challengeData.hint);
            } catch (error) {
                throw new Error('Format JSON invalide pour les indices');
            }
        }
        
        if (challengeData.algo_config) {
            try {
                JSON.parse(challengeData.algo_config);
            } catch (error) {
                throw new Error('Format JSON invalide pour la configuration algo');
            }
        }
        
        const isEditing = challengeData.id;
        const endpoint = isEditing ? `/admin/challenges/${challengeData.id}` : '/admin/challenges/create';
        const method = isEditing ? 'PUT' : 'POST';
        
        const response = await apiRequest(endpoint, {
            method: method,
            body: JSON.stringify(challengeData),
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        if (response.success) {
            showNotification(
                isEditing ? 'Challenge modifié avec succès' : 'Challenge créé avec succès',
                'success'
            );
            closeModal(ELEMENTS.modal.challenge);
            await loadChallenges();
            await loadStats();
        } else {
            throw new Error(response.message || response.error || 'Erreur lors de la sauvegarde');
        }
        
    } catch (error) {
        handleError('Erreur lors de la sauvegarde du challenge', error);
    } finally {
        hideLoading();
    }
}

/**
 * Gère le changement de type de challenge
 */
function handleTypeChange(e) {
    const type = e.target.value;

    // Tabs
    const flagsButton = document.querySelector('.tab-button[data-tab="flags"]');
    const codeButton = document.querySelector('.tab-button[data-tab="code"]');
    const technologiesButton = document.querySelector('.tab-button[data-tab="technologies"]');

    flagsButton.style.display = 'none';
    codeButton.style.display = 'none';
    technologiesButton.style.display = 'none';

    // Afficher les onglets selon le type
    switch (type) {
        case 'ctf':
            flagsButton.style.display = 'block';
            break;
        case 'dev':
            if (document.querySelector(ELEMENTS.form.category).value === 'algo') {
                codeButton.style.display = 'block';
            } else {
                technologiesButton.style.display = 'block';
            }
            break;
    }
}

/**
 * Gère le changement de hackathon
 */
async function handleHackathonChange(e) {
    const hackathonId = e.target.value;
    const phaseSelect = document.querySelector(ELEMENTS.form.phase_id);
    
    if (!hackathonId) {
        phaseSelect.innerHTML = '<option value="">Sélectionner une phase</option>';
        return;
    }
    
    try {
        const response = await apiRequest(`/admin/hackathon-phases/${hackathonId}`);
        if (response.success && response.data) {
            phaseSelect.innerHTML = '<option value="">Sélectionner une phase</option>';
            response.data.forEach(phase => {
                const option = document.createElement('option');
                option.value = phase.id;
                option.textContent = phase.name;
                phaseSelect.appendChild(option);
            });
        }
    } catch (error) {
        handleError('Erreur lors du chargement des phases', error);
    }
}

/**
 * Ajoute un flag
 */
function addFlag() {
    const container = document.querySelector(ELEMENTS.containers.flags);
    const template = document.querySelector('#flagTemplate');
    const flagItem = template.content.cloneNode(true);
    
    const flagNumber = container.children.length + 1;
    flagItem.querySelector('.flag-number').textContent = flagNumber;
    
    // Gestionnaire pour supprimer le flag
    flagItem.querySelector('.remove-flag').addEventListener('click', function() {
        this.closest('.flag-item').remove();
        updateFlagNumbers();
    });
    
    container.appendChild(flagItem);
}

/**
 * Met à jour les numéros des flags
 */
function updateFlagNumbers() {
    const flags = document.querySelectorAll('.flag-item');
    flags.forEach((flag, index) => {
        if (flag.querySelector('.flag-number')) {
            flag.querySelector('.flag-number').textContent = index + 1;
        }
    });
}

/**
 * Ajoute un test
 */
function addTest() {
    const container = document.querySelector(ELEMENTS.containers.tests);
    const template = document.querySelector('#testTemplate');
    const testItem = template.content.cloneNode(true);
    
    const testNumber = container.children.length + 1;
    testItem.querySelector('.test-number').textContent = testNumber;
    
    // Gestionnaire pour supprimer le test
    testItem.querySelector('.remove-test').addEventListener('click', function() {
        this.closest('.test-item').remove();
        updateTestNumbers();
    });
    
    container.appendChild(testItem);
}

/**
 * Met à jour les numéros des tests
 */
function updateTestNumbers() {
    const tests = document.querySelectorAll('.test-item');
    tests.forEach((test, index) => {
        if (test.querySelector('.test-number')) {
            const testNumber = index + 1;
            const testNumberElement = test.querySelector('.test-number');
            testNumberElement.textContent = testNumber;
        }
    });
}

/**
 * Ajoute une technologie
 */
function addTechnology() {
    const container = document.querySelector(ELEMENTS.containers.technologies);
    const template = document.querySelector('#technologyTemplate');
    const techItem = template.content.cloneNode(true);
    
    const techNumber = container.children.length + 1;
    techItem.querySelector('.technology-number').textContent = techNumber;
    
    // Remplir le select avec les technologies disponibles
    const select = techItem.querySelector('select');
    appState.technologies.forEach(tech => {
        const option = document.createElement('option');
        option.value = tech.id;
        option.textContent = tech.name;
        select.appendChild(option);
    });
    
    // Gestionnaire pour supprimer la technologie
    techItem.querySelector('.remove-technology').addEventListener('click', function() {
        this.closest('.technology-item').remove();
        updateTechnologyNumbers();
    });
    
    container.appendChild(techItem);
}

/**
 * Met à jour les numéros des technologies
 */
function updateTechnologyNumbers() {
    const technologies = document.querySelectorAll('.technology-item');
    technologies.forEach((tech, index) => {
        if (tech.querySelector('.technology-number')) {
            tech.querySelector('.technology-number').textContent = index + 1;
        }
    });
}

/**
 * Récupère les données des flags
 */
function getFlagsData() {
    const flags = [];
    document.querySelectorAll('.flag-item').forEach(flagItem => {
        const flag = {};
        flagItem.querySelectorAll('input, select').forEach(input => {
            if (input.type === 'checkbox') {
                flag[input.name.replace('flags[][', '').replace(']', '')] = input.checked;
            } else {
                flag[input.name.replace('flags[][', '').replace(']', '')] = input.value;
            }
        });
        flags.push(flag);
    });
    return flags;
}

/**
 * Récupère les données des tests
 */
function getTestsData() {
    const tests = [];
    document.querySelectorAll('.test-item').forEach(testItem => {
        const test = {};
        testItem.querySelectorAll('input, select, textarea').forEach(input => {
            if (input.type === 'checkbox') {
                test[input.name.replace('tests[][', '').replace(']', '')] = input.checked;
            } else {
                test[input.name.replace('tests[][', '').replace(']', '')] = input.value;
            }
        });
        tests.push(test);
    });
    return tests;
}

/**
 * Récupère les données des snippets
 */
function getSnippetsData() {
    const snippets = {};
    Object.keys(appState.codeEditors).forEach(lang => {
        snippets[lang] = appState.codeEditors[lang].getValue();
    });
    return snippets;
}

/**
 * Récupère les données des technologies
 */
function getTechnologiesData() {
    const technologies = [];
    document.querySelectorAll('.technology-item select').forEach(select => {
        if (select.value) {
            technologies.push(parseInt(select.value));
        }
    });
    return technologies;
}

/**
 * Édite un challenge
 */
async function editChallenge(id) {
    try {
        showLoading();
        const response = await apiRequest(`/admin/challenges/${id}`);
        
        if (response.success && response.data) {
            appState.editingChallenge = response.data;
            populateForm(response.data);
            
            document.querySelector('#modalTitleText').textContent = 'Modifier le Challenge';
            document.querySelector('#saveButtonText').textContent = 'Modifier';
            openModal(ELEMENTS.modal.challenge);
        } else {
            throw new Error(response.error || 'Challenge non trouvé');
        }
        
    } catch (error) {
        handleError('Erreur lors du chargement du challenge', error);
    } finally {
        hideLoading();
    }
}

/**
 * Affiche un challenge
 */
async function viewChallenge(id) {
    window.location.href = `/admin/challenges/view/${id}`;
}

/**
 * Supprime un challenge
 */
function deleteChallenge(id) {
    appState.challengeToDelete = id;
    openModal(ELEMENTS.modal.deleteConfirm);
}

/**
 * Confirme la suppression
 */
async function confirmDelete() {
    if (!appState.challengeToDelete) return;
    
    try {
        showLoading();
        
        const response = await apiRequest(`/admin/challenges/${appState.challengeToDelete}`, {
            method: 'DELETE',
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "include",
            body: JSON.stringify({ id: appState.challengeToDelete,
                csrf_token: getCSRFToken()
             })
        });
        
        if (response.success) {
            showNotification('Challenge supprimé avec succès', 'success');
            closeModal(ELEMENTS.modal.deleteConfirm);
            await loadChallenges();
            await loadStats();
            appState.challengeToDelete = null;
        } else {
            console.log(response);
            throw new Error(response.error || response.message || response.debug_message || response.debug_file || response.debug_line || response.debug_trace || 'Erreur lors de la suppression');
        }
        
    } catch (error) {
        handleError('Erreur lors de la suppression du challenge', error);
    } finally {
        hideLoading();
    }
}

/**
 * Exporte les challenges
 */
function exportChallenges() {
    const data = appState.filteredChallenges.map(challenge => ({
        ID: challenge.id,
        Titre: challenge.title,
        Type: getTypeLabel(challenge.type),
        Catégorie: challenge.category || '',
        Difficulté: getDifficultyLabel(challenge.difficulty),
        Points: challenge.points,
        Statut: challenge.is_active ? 'Actif' : 'Inactif',
        'Date de création': formatDate(challenge.created_at)
    }));
    
    const csv = convertToCSV(data);
    downloadCSV(csv, 'challenges_export.csv');
}

/**
 * Remplit le formulaire avec les données d'un challenge
 */
function populateForm(challenge) {
    // Champs de base
    document.querySelector(ELEMENTS.form.challengeId).value = challenge.id;
    document.querySelector(ELEMENTS.form.title).value = challenge.title;
    document.querySelector(ELEMENTS.form.type).value = challenge.type;
    document.querySelector(ELEMENTS.form.category).value = challenge.category || '';
    document.querySelector(ELEMENTS.form.difficulty).value = challenge.difficulty;
    document.querySelector(ELEMENTS.form.hackathon_id).value = challenge.hackathon_id;
    document.querySelector(ELEMENTS.form.phase_id).value = challenge.phase_id || '';
    document.querySelector(ELEMENTS.form.points).value = challenge.points;
    document.querySelector(ELEMENTS.form.is_active).value = challenge.is_active ? '1' : '0';
    document.querySelector(ELEMENTS.form.description).value = challenge.description;
    document.querySelector(ELEMENTS.form.instructions).value = challenge.instructions || '';
    document.querySelector(ELEMENTS.form.url_path).value = challenge.url_path || '';
    document.querySelector(ELEMENTS.form.resource_link).value = challenge.resource_link || '';
    document.querySelector(ELEMENTS.form.hint).value = challenge.hint || '';
    document.querySelector(ELEMENTS.form.is_dynamic).value = challenge.is_dynamic ? '1' : '0';
    document.querySelector(ELEMENTS.form.algo_config).value = challenge.algo_config || '';
    document.querySelector(ELEMENTS.form.created_by).value = challenge.created_by || '';
    
    // Déclencher les changements pour afficher les onglets appropriés
    handleTypeChange({ target: { value: challenge.type } });
    handleHackathonChange({ target: { value: challenge.hackathon_id } });
    
    // Charger les données liées
    if (challenge.flags && Array.isArray(challenge.flags) && challenge.flags.length > 0) {
        loadFlags(challenge.flags);
    }
    
    if (challenge.snippets && Object.keys(challenge.snippets).length > 0) {
        loadSnippets(challenge.snippets);
    }
    
    if (challenge.tests && Array.isArray(challenge.tests) && challenge.tests.length > 0) {
        loadTests(challenge.tests);
    }
    
    if (challenge.technologies && Array.isArray(challenge.technologies) && challenge.technologies.length > 0) {
        loadTechnologies(challenge.technologies);
    }
}

/**
 * Réinitialise le formulaire
 */
function resetForm() {
    document.querySelector(ELEMENTS.form.challenge).reset();
    document.querySelector(ELEMENTS.form.challengeId).value = '';
    
    // Vider les conteneurs
    document.querySelector(ELEMENTS.containers.flags).innerHTML = '';
    document.querySelector(ELEMENTS.containers.tests).innerHTML = '';
    document.querySelector(ELEMENTS.containers.technologies).innerHTML = '';
    
    // Réinitialiser les éditeurs de code
    Object.values(appState.codeEditors).forEach(editor => {
        editor.setValue('');
    });
    
    // Masquer tous les onglets spéciaux
    document.querySelectorAll('.tab-button[data-tab="flags"], .tab-button[data-tab="code"], .tab-button[data-tab="technologies"]').forEach(tab => {
        tab.style.display = 'none';
    });
    
    appState.editingChallenge = null;
}

/**
 * Valide le formulaire
 */
function validateForm() {
    let isValid = true;
    
    // Valider les champs requis
    document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Valide un champ
 */
function validateField(field) {
    const value = field.value.trim();
    const isRequired = field.hasAttribute('required');
    
    if (isRequired && !value) {
        showFieldError(field, 'Ce champ est requis');
        return false;
    }
    
    // Validation spécifique selon le type
    switch (field.type) {
        case 'number':
            if (value && (isNaN(value) || value < 0)) {
                showFieldError(field, 'Veuillez entrer un nombre positif');
                return false;
            }
            break;
        case 'url':
            if (value && !isValidURL(value)) {
                showFieldError(field, 'Veuillez entrer une URL valide');
                return false;
            }
            break;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Affiche une erreur de champ
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#dc3545';
    errorDiv.style.fontSize = '0.875rem';
    errorDiv.style.marginTop = '0.25rem';
    
    field.parentNode.appendChild(errorDiv);
    field.classList.add('is-invalid');
}

/**
 * Efface l'erreur d'un champ
 */
function clearFieldError(field) {
    if (!field) return;
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
    field.classList.remove('is-invalid');
}

/**
 * Utilitaires
 */
function getTypeBadgeClass(type) {
    const classes = {
        'ctf': 'danger',
        'dev': 'primary',
        'project': 'warning',
        'finale': 'success'
    };
    return classes[type] || 'secondary';
}

function getTypeLabel(type) {
    const labels = {
        'ctf': 'CTF',
        'dev': 'Développement',
        'project': 'Projet',
        'finale': 'Finale'
    };
    return labels[type] || type;
}

function getDifficultyBadgeClass(difficulty) {
    const classes = {
        'easy': 'success',
        'medium': 'warning',
        'hard': 'danger'
    };
    return classes[difficulty] || 'secondary';
}

function getDifficultyLabel(difficulty) {
    const labels = {
        'easy': 'Facile',
        'medium': 'Moyen',
        'hard': 'Difficile'
    };
    return labels[difficulty] || difficulty;
}

function getCodeMirrorMode(language) {
    const modes = {
        'python': 'python',
        'java': 'text/x-java-source',
        'javascript': 'javascript',
        'cpp': 'text/x-c++src',
        'c': 'text/x-csrc'
    };
    return modes[language] || 'text/plain';
}

function getCSRFToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : null;
}

function isValidURL(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

function sanitizeText(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR');
}

function convertToCSV(data) {
    if (!data.length) return '';
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => headers.map(header => `"${row[header]}"`).join(','))
    ].join('\n');
    
    return csvContent;
}

function downloadCSV(csv, filename) {
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/**
 * Gestion des modals
 */
function setupModalEventListeners() {
    // Fermer les modals
    document.querySelectorAll('.modal-closed').forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            closeModal(modal);
        });
    });
    
    // Fermer en cliquant à l'extérieur
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });
}

/**
 * Gestion des onglets
 */
function setupTabEventListeners() {
    // Onglets du formulaire
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const tabName = button.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
    // Onglets des snippets
    document.querySelectorAll('.snippet-tab').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const language = button.getAttribute('data-language');
            switchSnippetTab(language);
        });
    });
}

function switchTab(tabName) {
    // Désactiver tous les onglets
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Activer l'onglet sélectionné
    const tabButton = document.querySelector(`[data-tab="${tabName}"]`)
    tabButton.classList.add('active');
    const tabContent = document.querySelector(`#${tabName}Tab`)
    tabContent.classList.add('active');
}

function switchSnippetTab(language) {
    if (!language) return;

    // Désactiver tous les onglets de snippets
    document.querySelectorAll('.snippet-tab').forEach(button => {
        button.classList.remove('active');
    });
    
    // Masquer tous les éditeurs
    document.querySelectorAll('.CodeMirror').forEach(editor => {
        editor.style.display = 'none';
    });

    // Activer l'onglet sélectionné
    const tabButton = document.querySelector(`.snippet-tab[data-language="${language}"]`);
    const editorWrapper = document.querySelector(`#${language}_snippet`).nextSibling;
    
    if (tabButton) {
        tabButton.classList.add('active');
    }
    
    if (editorWrapper && editorWrapper.CodeMirror) {
        editorWrapper.style.display = 'block';
        
        // Rafraîchir l'éditeur après un court délai
        setTimeout(() => {
            editorWrapper.CodeMirror.refresh();
        }, 50);
    }
}

/**
 * Gestion du chargement et des erreurs
 */
function showLoading() {
    document.querySelector(ELEMENTS.loadingSpinner).style.display = 'flex';
}

function hideLoading() {
    document.querySelector(ELEMENTS.loadingSpinner).style.display = 'none';
}

function handleError(message, error) {
    console.error(message, error);
    showNotification(`${message}`,`${error.message || error.error || error.debug_message || error || "Erreur inconnue"}`, 'error');
}

/**
 * Requête API
 */
async function apiRequest(endpoint, options = {}) {
    try {
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        const response = await fetch(`/api${endpoint}`, {
            ...options,
            headers: { ...headers, ...options.headers }
        });

        const data = await response.json(); // On parse toujours le body

        // Afficher les détails de debug si disponibles (même pour les réponses 200)
        if (data.debug_message) {
            console.group('🔍 Debug API Info');
            console.log('Message:', data.debug_message);
            console.log('File:', data.debug_file);
            console.log('Line:', data.debug_line);
            if (data.debug_trace) console.log('Trace:', data.debug_trace);
            console.groupEnd();
        }

        if (!response.ok) {
            return {
                success: false,
                status: data.status || response.status,
                message: data.message || data.error || 'Erreur inconnue',
                debug_message: data.debug_message || null,
                debug_file: data.debug_file || null,
                debug_line: data.debug_line || null,
                debug_trace: data.debug_trace || null,
                data: null
            };
        }

        // Vérifier si la réponse contient une erreur même avec un status 200
        if (data.success === false) {
            return {
                success: false,
                status: response.status,
                message: data.error || data.message || 'Erreur inconnue',
                debug_message: data.debug_message || null,
                debug_file: data.debug_file || null,
                debug_line: data.debug_line || null,
                debug_trace: data.debug_trace || null,
                data: data
            };
        }

        return data;  // Retourne bien les données récupérées
    } catch (error) {
        handleError('Erreur lors de la requête API', error, 'error');
        return {
            success: false,
            status: 'client_error',
            message: 'Erreur côté client',
            data: null
        };
    }
}

/**
 * Charge les flags dans le formulaire
 * @param {Array} flags - Tableau d'objets contenant les données des flags
 */
function loadFlags(flags) {
    const flagsContainer = document.querySelector(ELEMENTS.containers.flags);
    const flagTemplate = document.querySelector('#flagTemplate');
    
    // Vider les flags existants
    flagsContainer.innerHTML = '';
    
    if (!flags || !Array.isArray(flags)) return;
    
    flags.forEach((flag, index) => {
        // Cloner le template
        const flagElement = flagTemplate.content.cloneNode(true);
        const flagItem = flagElement.querySelector('.flag-item');
        
        // Mettre à jour les champs avec les données du flag
        flagItem.querySelector('.flag-number').textContent = index + 1;
        
        // Mettre à jour les champs du formulaire
        const nameInput = flagItem.querySelector('input[name$="[name]"]');
        const valueInput = flagItem.querySelector('input[name$="[value]"]');
        const pointsInput = flagItem.querySelector('input[name$="[points]"]');
        const minPointsInput = flagItem.querySelector('input[name$="[min_points]"]');
        const decayInput = flagItem.querySelector('input[name$="[decay]"]');
        const isDynamicCheckbox = flagItem.querySelector('input[name$="[is_dynamic]"]');
        
        if (nameInput) nameInput.value = flag.name || '';
        if (valueInput) valueInput.value = flag.value || '';
        if (pointsInput) pointsInput.value = flag.points || flag.initial_points || '100';
        if (minPointsInput) minPointsInput.value = flag.min_points || '50';
        if (decayInput) decayInput.value = flag.decay || '10';
        if (isDynamicCheckbox) isDynamicCheckbox.checked = flag.is_dynamic === 1 || flag.is_dynamic === '1';
        
        // Mettre à jour les noms des champs pour maintenir la structure du tableau
        const updateNames = (element, property, value) => {
            const regex = new RegExp(`(\[flags\]\[\]\[${property}\])`);
            element.name = element.name.replace(regex, `[flags][${index}][${property}]`);
        };
        
        const inputs = flagItem.querySelectorAll('input');
        inputs.forEach(input => {
            const match = input.name.match(/\[flags\]\[\]\[(\w+)\]/);
            if (match && match[1]) {
                input.name = input.name.replace('[flags][]', `[flags][${index}]`);
            }
        });
        
        // Gestionnaire pour supprimer le flag
        flagItem.querySelector('.remove-flag').addEventListener('click', function() {
            this.closest('.flag-item').remove();
            updateFlagNumbers();
        });
        
        // Ajouter le flag au conteneur
        flagsContainer.appendChild(flagItem);
    });
    
    // Mettre à jour les numéros des flags
    updateFlagNumbers();
}

/**
 * Charge les snippets de code dans les éditeurs
 * @param {Object} snippets - Objet contenant les snippets par langage
 */
function loadSnippets(snippets) {
    // Vérifier si snippets est valide
    if (!snippets || typeof snippets !== 'object' || Object.keys(snippets).length === 0) {
        if (appState.codeEditors && appState.codeEditors.python) {
            switchSnippetTab('python');
        }
        return;
    }

    // Parcourir tous les snippets
    for (const language in snippets) {
        if (snippets.hasOwnProperty(language) && 
            appState.codeEditors && 
            appState.codeEditors[language] && 
            snippets[language] !== null) {
            
            // Convertir en chaîne et nettoyer
            const content = String(snippets[language] || '');
            
            // Définir la valeur du snippet dans l'éditeur
            try {
                appState.codeEditors[language].setValue(content);
                
                // Rafraîchir l'éditeur
                setTimeout(() => {
                    if (appState.codeEditors[language]) {
                        appState.codeEditors[language].refresh();
                    }
                }, 100);
            } catch (error) {
                console.error(`Erreur lors du chargement du snippet pour ${language}:`, error);
            }
        }
    }

    // Basculer vers le premier onglet avec du contenu
    let firstLangWithContent = null;
    
    for (const language in snippets) {
        if (snippets.hasOwnProperty(language) && 
            snippets[language] !== null && 
            String(snippets[language]).trim() !== '') {
            firstLangWithContent = language;
            break;
        }
    }
    
    if (firstLangWithContent && appState.codeEditors[firstLangWithContent]) {
        switchSnippetTab(firstLangWithContent);
    } else if (appState.codeEditors.python) {
        switchSnippetTab('python');
    }
}

/**
 * Charge les tests dans le formulaire
 * @param {Array} tests - Tableau d'objets contenant les données des tests
 */
function loadTests(tests) {
    const testsContainer = document.querySelector(ELEMENTS.containers.tests);
    const testTemplate = document.querySelector('#testTemplate');
    
    // Vider les tests existants
    testsContainer.innerHTML = '';
    
    if (!tests || !Array.isArray(tests)) return;
    
    tests.forEach((test, index) => {
        // Cloner le template
        const testElement = testTemplate.content.cloneNode(true);
        const testItem = testElement.querySelector('.test-item');
        
        // Mettre à jour le numéro du test
        testItem.querySelector('.test-number').textContent = index + 1;
        
        // Mettre à jour les champs du formulaire
        const inputData = testItem.querySelector('textarea[name$="[input_data]"]');
        const expectedOutput = testItem.querySelector('textarea[name$="[expected_output]"]');
        const points = testItem.querySelector('input[name$="[points]"]');
        const timeout = testItem.querySelector('input[name$="[timeout_seconds]"]');
        const memory = testItem.querySelector('input[name$="[memory_limit_mb]"]');
        const isPublic = testItem.querySelector('input[name$="[is_public]"]');
        
        if (inputData) inputData.value = test.input_data || '';
        if (expectedOutput) expectedOutput.value = test.expected_output || '';
        if (points) points.value = test.weight || test.points || '10';
        if (timeout) timeout.value = test.timeout_seconds || '2';
        if (memory) memory.value = test.memory_limit_mb || '128';
        if (isPublic) isPublic.checked = test.is_public === 1 || test.is_public === '1' || test.is_public === true;
        
        // Mettre à jour les noms des champs pour maintenir la structure du tableau
        const inputs = testItem.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            const match = input.name.match(/\[tests\]\[\]\[(\w+)\]/);
            if (match && match[1]) {
                input.name = input.name.replace('[tests][]', `[tests][${index}]`);
            }
        });
        
        // Gestionnaire pour supprimer le test
        testItem.querySelector('.remove-test').addEventListener('click', function() {
            this.closest('.test-item').remove();
            updateTestNumbers();
        });
        
        // Ajouter le test au conteneur
        testsContainer.appendChild(testItem);
    });
    
    // Mettre à jour les numéros des tests
    updateTestNumbers();
}

/**
 * Charge les technologies dans le formulaire
 * @param {Array} technologies - Tableau d'objets contenant les données des technologies
 */
function loadTechnologies(technologies) {
    const techContainer = document.querySelector(ELEMENTS.containers.technologies);
    const techTemplate = document.querySelector('#technologyTemplate');
    
    // Vider les technologies existantes
    techContainer.innerHTML = '';
    
    if (!technologies || !Array.isArray(technologies)) return;
    
    technologies.forEach((tech, index) => {
        // Cloner le template
        const techElement = techTemplate.content.cloneNode(true);
        const techItem = techElement.querySelector('.technology-item');
        
        // Mettre à jour le numéro de la technologie
        techItem.querySelector('.technology-number').textContent = index + 1;
        
        // Mettre à jour le sélecteur de technologie
        const techSelect = techItem.querySelector('select[name="technologies[]"]');
        
        if (techSelect) {
            // Sélectionner la technologie correspondante
            const optionToSelect = Array.from(techSelect.options).find(
                option => option.value === tech.id || option.value === tech.technology_id
            );
            
            if (optionToSelect) {
                optionToSelect.selected = true;
            }
            
            // Mettre à jour le nom du champ pour maintenir la structure du tableau
            techSelect.name = `technologies[${index}]`;
        }
        
        // Gestionnaire pour supprimer la technologie
        techItem.querySelector('.remove-technology').addEventListener('click', function() {
            this.closest('.technology-item').remove();
            updateTechnologyNumbers();
        });
        
        // Ajouter la technologie au conteneur
        techContainer.appendChild(techItem);
    });
    
    // Mettre à jour les numéros des technologies
    updateTechnologyNumbers();
}

// Initialisation de la page
document.addEventListener('DOMContentLoaded', initializeChallengesPage); 