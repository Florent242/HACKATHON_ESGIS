document.addEventListener('DOMContentLoaded', async function () {
    // ===== ÉTAT GLOBAL DE L'APPLICATION =====
    const AppState = {
        challenge: null,
        editor: null,
        currentLanguage: 'python',
        isConsoleExpanded: true,
        isLoading: false,
        challengeTemplates: {},
        userData: {
            id: null,
            csrf_token: null
        }
    };

    // ===== INITIALISATION PRINCIPALE =====
    try {
        await initializeApplication();
    } catch (error) {
        console.error('Erreur lors de l\'initialisation de l\'application:', error);
        showError('Erreur lors du chargement de l\'interface');
    }

    // ===== FONCTIONS D'INITIALISATION =====
    async function initializeApplication() {
        console.log('Initialisation de l\'application...');
        
        // Récupérer les données utilisateur et CSRF token
        await initializeUserData();
        
        // Initialiser l'interface utilisateur
        initializeUIComponents();
        
        // Charger les données du challenge
        const challengeId = extractChallengeId();
        await loadChallengeData(challengeId);
        
        // Initialiser Monaco Editor
        await initializeMonacoEditor();
        
        // Configurer les gestionnaires d'événements
        setupEventListeners();
        
        console.log('Application initialisée avec succès');
    }

    async function initializeUserData() {
        try {
            AppState.userData.id = await getUserId();
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            AppState.userData.csrf_token = csrfMeta ? csrfMeta.getAttribute('content') : null;
            console.log('Données utilisateur initialisées:', AppState.userData);
        } catch (error) {
            console.error('Erreur lors de la récupération des données utilisateur:', error);
        }
    }

    function extractChallengeId() {
        return window.location.pathname.split('/').pop();
    }

    function initializeUIComponents() {
        // Initialiser les tooltips
        initializeTooltips();
        
        // Configurer le panneau console
        setupConsolePanel();
        
        // Configurer les états initiaux
        updateLoadingState(false);
    }

    function initializeTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(el => {
            el.addEventListener('mouseenter', showTooltip);
            el.addEventListener('mouseleave', hideTooltip);
        });
    }

    function setupConsolePanel() {
        const toggleConsole = document.getElementById('toggleConsole');
        const consoleOutput = document.getElementById('consoleOutput');

        if (toggleConsole && consoleOutput) {
            toggleConsole.addEventListener('click', () => {
                AppState.isConsoleExpanded = !AppState.isConsoleExpanded;
                consoleOutput.style.display = AppState.isConsoleExpanded ? 'block' : 'none';
                toggleConsole.classList.toggle('rotate');
                lucide.createIcons();
            });
        }
    }

    // ===== CHARGEMENT DES DONNÉES DU CHALLENGE =====
    async function loadChallengeData(challengeId) {
        if (!challengeId) {
            throw new Error('ID de challenge manquant');
        }

        updateLoadingState(true, 'Chargement du défi...');
        
        try {
            console.log('Chargement du défi ID:', challengeId);
            
            const response = await apiRequest(`/challenges/${challengeId}`, {
                method: "GET",
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            if (!response || (!response.data && !response.challenge)) {
                throw new Error('Aucune donnée de défi reçue');
            }

            const challengeData = response.data || response.challenge;
            AppState.challenge = challengeData;

            // Initialiser l'interface selon le type de challenge
            if (challengeData.type === 'dev' && challengeData.category === 'algo') {
                await initializeAlgorithmicInterface(challengeData);
            } else {
                await initializeClassicInterface(challengeData);
            }

        } catch (error) {
            console.error('Erreur lors du chargement du défi:', error);
            
            // Essayer une approche de fallback
            try {
                const fallbackResponse = await fetch(`/api/challenges/${challengeId}`, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (fallbackResponse.ok) {
                    const data = await fallbackResponse.json();
                    if (data && data.data) {
                        AppState.challenge = data.data;
                        await initializeClassicInterface(data.data);
                        return;
                    }
                }
            } catch (fallbackError) {
                console.error('Erreur fallback:', fallbackError);
            }
            
            throw error;
        } finally {
            updateLoadingState(false);
        }
    }

    async function initializeAlgorithmicInterface(challengeData) {
        console.log('Initialisation interface algorithmique');
        
        // Afficher les informations du défi
        updateChallengeDisplay(challengeData);
        
        // Configurer les langages et templates
        if (challengeData.snippets && challengeData.snippets.length > 0) {
            setupLanguageConfiguration(challengeData.snippets[0]);
        } else {
            setupDefaultLanguages();
        }
        
        // Configurer les boutons spécifiques aux défis algorithmiques
        setupAlgorithmicButtons();
    }

    async function initializeClassicInterface(challengeData) {
        console.log('Initialisation interface classique');
        
        // Afficher les informations du défi
        updateChallengeDisplay(challengeData);
        
        // Configurer les snippets pour l'interface classique
        if (challengeData.snippets && challengeData.snippets.length > 0) {
            setupLanguageConfiguration(challengeData.snippets[0]);
        } else {
            setupDefaultLanguages();
        }
    }

    // ===== AFFICHAGE DES DONNÉES DU CHALLENGE =====
    function updateChallengeDisplay(challengeData) {
        // Mise à jour du titre
        const titleElement = document.getElementById('challenge-title');
        if (titleElement && challengeData.title) {
            titleElement.textContent = challengeData.title;
        }

        // Mise à jour de la difficulté
        const difficultyElement = document.getElementById('challenge-difficulty');
        if (difficultyElement && challengeData.difficulty) {
            difficultyElement.innerHTML = `
                <i data-lucide="zap" class="w-3 h-3"></i>
                <span>${challengeData.difficulty.charAt(0).toUpperCase() + challengeData.difficulty.slice(1)}</span>
            `;
            lucide.createIcons();
        }

        // Mise à jour des contraintes de temps et mémoire
        updateConstraintsDisplay(challengeData);

        // Mise à jour de la description
        const descElement = document.getElementById('challenge-description');
        if (descElement && challengeData.description) {
            descElement.innerHTML = challengeData.description;
        }

        // Mise à jour des instructions
        const instructionsElement = document.getElementById('challenge-instructions');
        if (instructionsElement && challengeData.instructions) {
            const instructions = Array.isArray(challengeData.instructions) 
                ? challengeData.instructions.join('\n') 
                : challengeData.instructions;
            instructionsElement.innerHTML = instructions || 'Aucune instruction fournie';
        }
    }

    function updateConstraintsDisplay(challengeData) {
        // Temps limite
        const timeElement = document.getElementById('challenge-time');
        if (timeElement) {
            const timeLimit = challengeData.time_limit 
                ? (challengeData.time_limit / 1000) + 's' 
                : '1s';
            timeElement.querySelector('span').textContent = timeLimit;
        }

        // Limite mémoire
        const memoryElement = document.getElementById('challenge-memory');
        if (memoryElement) {
            const memoryLimit = challengeData.memory_limit 
                ? challengeData.memory_limit + 'MB' 
                : '1MB';
            memoryElement.querySelector('span').textContent = memoryLimit;
        }
    }

    // ===== CONFIGURATION DES LANGAGES =====
    function setupLanguageConfiguration(snippetData) {
        const availableLanguages = extractAvailableLanguages(snippetData);
        
        if (availableLanguages.length === 0) {
            console.warn('Aucun langage disponible, utilisation des langages par défaut');
            setupDefaultLanguages();
            return;
        }

        // Créer les templates
        AppState.challengeTemplates = createLanguageTemplates(snippetData, availableLanguages);
        
        // Configurer le sélecteur de langage
        setupLanguageSelector(availableLanguages);
        
        console.log('Langages configurés:', availableLanguages);
    }

    function extractAvailableLanguages(snippetData) {
        const supportedLanguages = ['bash', 'java', 'js', 'python', 'c', 'cpp', 'csharp', 'php', 'ruby', 'typescript', 'pascal', 'golang'];
        
        return supportedLanguages.filter(lang => 
            snippetData[lang] && snippetData[lang].trim() !== ''
        );
    }

    function createLanguageTemplates(snippetData, availableLanguages) {
        const templates = {};
        
        availableLanguages.forEach(lang => {
            templates[lang] = snippetData[lang];
            // Mapping spécial pour JavaScript
            if (lang === 'js') {
                templates['javascript'] = snippetData[lang];
            }
        });
        
        return templates;
    }

    function setupDefaultLanguages() {
        const defaultLangs = ['python', 'javascript', 'java', 'cpp', 'bash', 'php', 'ruby', 'go', 'c', 'csharp', 'typescript', 'pascal'];
        const templates = {};

        defaultLangs.forEach(lang => {
            templates[lang] = getDefaultTemplate(lang);
        });

        AppState.challengeTemplates = templates;
        setupLanguageSelector(defaultLangs);
    }

    function setupLanguageSelector(availableLanguages) {
        const dropdown = document.getElementById('languageDropdown');
        if (!dropdown) {
            console.warn('Dropdown de langages non trouvé');
            return;
        }

        // Nettoyer et remplir le dropdown
        dropdown.innerHTML = availableLanguages.map(lang => `
            <button class="w-full px-4 py-2 text-sm text-white hover:bg-slate-700/50 rounded-lg flex items-center gap-2 transition-colors" 
                    data-language="${lang}">
                <i data-lucide="code" class="w-4 h-4"></i>
                <span>${lang.toUpperCase()}</span>
            </button>
        `).join('');

        // Configurer les événements
        setupLanguageDropdownEvents();
        
        // Sélectionner le premier langage par défaut
        if (availableLanguages.length > 0) {
            selectLanguage(availableLanguages[0]);
        }
        
        // Rafraîchir les icônes
        lucide.createIcons();
    }

    function setupLanguageDropdownEvents() {
        const selector = document.getElementById('languageSelector');
        const dropdown = document.getElementById('languageDropdown');

        if (!selector || !dropdown) return;

        // Toggle dropdown
        selector.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // Fermer en cliquant ailleurs
        document.addEventListener('click', () => {
            dropdown.classList.add('hidden');
        });

        // Sélection de langage
        dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
            const button = e.target.closest('[data-language]');
            if (button) {
                const language = button.getAttribute('data-language');
                selectLanguage(language);
                dropdown.classList.add('hidden');
            }
        });
    }

    function selectLanguage(language) {
        if (!language) return;

        AppState.currentLanguage = language;
        
        // Mettre à jour l'affichage du sélecteur
        const languageLabel = document.querySelector('#languageSelector span');
        if (languageLabel) {
            languageLabel.textContent = language.toUpperCase();
        }

        // Mettre à jour l'éditeur si il existe déjà
        if (AppState.editor) {
            const monacoLang = getMonacoLanguage(language);
            const template = AppState.challengeTemplates[language] || getDefaultTemplate(language);
            createMonacoEditor(monacoLang, template);
        }

        console.log('Langage sélectionné:', language);
    }

    // ===== INITIALISATION DE MONACO EDITOR =====
    async function initializeMonacoEditor() {
        return new Promise((resolve, reject) => {
            require.config({ 
                paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' } 
            });

            require(['vs/editor/editor.main'], function () {
                try {
                    // Créer l'éditeur initial
                    const initialLanguage = AppState.currentLanguage;
                    const initialTemplate = AppState.challengeTemplates[initialLanguage] || getDefaultTemplate(initialLanguage);
                    
                    createMonacoEditor(getMonacoLanguage(initialLanguage), initialTemplate);
                    console.log('Monaco Editor initialisé');
                    resolve();
                } catch (error) {
                    console.error('Erreur lors de l\'initialisation de Monaco:', error);
                    reject(error);
                }
            });
        });
    }

    function createMonacoEditor(language, value) {
        const container = document.getElementById('monaco-editor');
        if (!container) {
            console.error('Container Monaco Editor non trouvé');
            return;
        }

        // Nettoyer l'éditeur existant
        if (AppState.editor) {
            AppState.editor.dispose();
        }

        // Créer le nouvel éditeur
        AppState.editor = monaco.editor.create(container, {
            value: value || '',
            language: language,
            theme: 'vs-dark',
            automaticLayout: true,
            fontSize: 16,
            minimap: { enabled: false },
            scrollBeyondLastLine: false,
            wordWrap: 'on',
            lineNumbers: 'on',
            folding: true,
            renderWhitespace: 'selection'
        });
    }

    // ===== GESTIONNAIRES D'ÉVÉNEMENTS =====
    function setupEventListeners() {
        // Bouton d'exécution du code
        const runCodeBtn = document.getElementById('runCode');
        if (runCodeBtn) {
            runCodeBtn.addEventListener('click', handleCodeExecution);
        }

        // Bouton de réinitialisation
        const resetBtn = document.getElementById('resetCode');
        if (resetBtn) {
            resetBtn.addEventListener('click', handleCodeReset);
        }

        // Boutons de test et soumission
        setupTestButtons();
    }

    function setupTestButtons() {
        const runTestsBtn = document.getElementById('runAllTests');
        const submitBtn = document.getElementById('submitChallenge');

        if (runTestsBtn) {
            runTestsBtn.addEventListener('click', handleTestExecution);
        }

        if (submitBtn) {
            submitBtn.addEventListener('click', handleSubmission);
        }
    }

    function setupAlgorithmicButtons() {
        // Configuration spécifique pour les défis algorithmiques
        const runTestsBtn = document.getElementById('runAllTests');
        if (runTestsBtn) {
            runTestsBtn.innerHTML = `
                <i data-lucide="play-circle" class="w-4 h-4"></i>
                <span>Valider (tests publics)</span>
            `;
        }

        const submitBtn = document.getElementById('submitChallenge');
        if (submitBtn) {
            submitBtn.innerHTML = `
                <i data-lucide="send" class="w-4 h-4"></i>
                <span>Soumettre (tous les tests)</span>
            `;
        }

        lucide.createIcons();
    }

    // ===== GESTIONNAIRES D'ACTIONS =====
    async function handleCodeExecution() {
        if (!AppState.editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const code = AppState.editor.getValue();
        if (!code?.trim()) {
            showError('Veuillez saisir du code avant de lancer l\'exécution');
            return;
        }

        updateLoadingState(true, 'Exécution en cours...');

        try {
            const result = await executeCode(code, AppState.currentLanguage);
            displayExecutionResult(result);
        } catch (error) {
            console.error('Erreur lors de l\'exécution:', error);
            showError('Erreur lors de l\'exécution du code');
        } finally {
            updateLoadingState(false);
        }
    }

    async function handleTestExecution() {
        if (!AppState.editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const code = AppState.editor.getValue();
        if (!code?.trim()) {
            showError('Veuillez saisir du code avant de lancer les tests');
            return;
        }

        updateLoadingState(true, 'Tests en cours...');

        try {
            if (isAlgorithmicChallenge()) {
                await runQuickValidation();
            } else {
                await runClassicTests();
            }
        } catch (error) {
            console.error('Erreur lors des tests:', error);
            showError('Erreur lors de l\'exécution des tests');
        } finally {
            updateLoadingState(false);
        }
    }

    async function handleSubmission() {
        if (!AppState.editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const code = AppState.editor.getValue();
        if (!code?.trim()) {
            showError('Veuillez saisir du code avant de soumettre');
            return;
        }

        // Confirmation de soumission
        if (!confirm('Êtes-vous sûr de vouloir soumettre cette solution ? Elle sera évaluée contre tous les cas de test.')) {
            return;
        }

        updateLoadingState(true, 'Soumission en cours...');

        try {
            await submitFinalSolution();
        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            showError('Erreur lors de la soumission');
        } finally {
            updateLoadingState(false);
        }
    }

    function handleCodeReset() {
        if (!AppState.editor) return;

        const currentTemplate = AppState.challengeTemplates[AppState.currentLanguage] || getDefaultTemplate(AppState.currentLanguage);
        AppState.editor.setValue(currentTemplate);
        showSuccess('Code réinitialisé');
    }

    // ===== EXÉCUTION DU CODE =====
    async function executeCode(code, language) {
        const response = await apiRequest('/piston', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                language: language,
                code: code
            })
        });

        return response;
    }

    function displayExecutionResult(result) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (!consoleOutput) return;

        console.log('Résultat d\'exécution:', result);

        if (result.success) {
            // Exécution réussie
            consoleOutput.innerHTML = `
                <div class="p-4 space-y-2">
                    <div class="flex items-center text-green-400">
                        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                        <span class="font-medium">Exécution réussie (${result.language})</span>
                    </div>
                    ${result.output ? `
                        <div class="mt-2">
                            <div class="text-xs text-slate-400 mb-1">Sortie :</div>
                            <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto">${escapeHtml(result.output)}</pre>
                        </div>
                    ` : ''}
                    ${result.run_info?.stderr ? `
                        <div class="mt-2">
                            <div class="text-xs text-amber-400 mb-1">Avertissements :</div>
                            <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto text-amber-300">${escapeHtml(result.run_info.stderr)}</pre>
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            // Échec d'exécution - structure améliorée pour gérer tous les cas
            let errorDetails = '';
            
            // Gestion des différents types d'erreurs
            if (result.is_timeout) {
                errorDetails += `
                    <div class="text-amber-300 mt-2 flex flex-row items-center gap-2">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i> 
                        Délai d'exécution dépassé
                    </div>
                `;
            }
            
            if (result.is_memory_limit) {
                errorDetails += `
                    <div class="text-amber-300 mt-2 flex flex-row items-center gap-2">
                        <i data-lucide="cpu" class="w-3.5 h-3.5"></i> 
                        Limite de mémoire dépassée
                    </div>
                `;
            }

            // Affichage de l'erreur principale
            const mainError = result.error || 
                            (result.run_info?.stderr) || 
                            (result.compile_info?.stderr) ||
                            'Erreur d\'exécution inconnue';

            errorDetails += `
                <div class="mt-2">
                    <div class="text-xs text-red-400 mb-1">Détails de l'erreur :</div>
                    <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto text-red-300">${escapeHtml(mainError)}</pre>
                </div>
            `;

            // Si il y a une sortie malgré l'erreur
            if (result.output) {
                errorDetails += `
                    <div class="mt-2">
                        <div class="text-xs text-slate-400 mb-1">Sortie partielle :</div>
                        <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto">${escapeHtml(result.output)}</pre>
                    </div>
                `;
            }

            consoleOutput.innerHTML = `
                <div class="p-4 space-y-2">
                    <div class="flex items-center text-red-400">
                        <i data-lucide="circle-x" class="w-5 h-5 mr-2"></i>
                        <span class="font-medium">Erreur d'exécution (${result.language || 'Inconnu'})</span>
                        ${result.exit_code ? `<span class="ml-2 text-xs bg-red-500/20 px-2 py-1 rounded">Code: ${result.exit_code}</span>` : ''}
                    </div>
                    ${errorDetails}
                </div>
            `;
        }

        lucide.createIcons();
    }

    // ===== VALIDATION ET SOUMISSION DES DÉFIS ALGORITHMIQUES =====
    async function runQuickValidation() {
        if (!AppState.editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const code = AppState.editor.getValue();
        if (!code.trim()) {
            showError('Veuillez saisir du code avant de lancer les tests');
            return;
        }

        showProgress('Validation en cours...');

        try {
            console.log('Exécution de la validation rapide pour le défi:', AppState.challenge.id);
            
            const response = await apiRequest(`/challenges/dev/${AppState.challenge.hackathon_id || 2}/${AppState.userData.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    challenge_id: AppState.challenge.id,
                    code: code,
                    language: AppState.currentLanguage,
                    action: 'validate',
                    csrf_token: AppState.userData.csrf_token
                })
            });

            console.log('Réponse de validation reçue:', response);

            if (response && response.success) {
                if (response.data && response.data.success) {
                    displayValidationResults(response.data);
                } else {
                    const errorMessage = (response.data && response.data.error) || response.error || 'Erreur lors de la validation';
                    showError(errorMessage);
                }
            } else {
                const errorMessage = (response && response.error) || 'Erreur lors de la validation';
                showError(errorMessage);
            }

        } catch (error) {
            console.error('Erreur lors de la validation:', error);
            showError('Erreur de communication avec le serveur');
        } finally {
            updateLoadingState(false);
        }
    }

    async function submitFinalSolution() {
        if (!AppState.editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const code = AppState.editor.getValue();
        if (!code.trim()) {
            showError('Veuillez saisir du code avant de soumettre');
            return;
        }

        showProgress('Soumission en cours...');

        try {
            const response = await apiRequest(`/challenges/dev/${AppState.challenge.hackathon_id || 2}/1`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    user_id: AppState.userData.id,
                    hackathon_id: AppState.challenge.hackathon_id || 2,
                    challenge_id: AppState.challenge.id,
                    code: code,
                    language: AppState.currentLanguage,
                    action: 'submit',
                    csrf_token: AppState.userData.csrf_token
                })
            });

            if (response && response.success) {
                showSuccess('Solution soumise avec succès !');

                // Attendre un peu puis récupérer les résultats si disponible
                if (response.data && response.data.submission_id) {
                    setTimeout(async () => {
                        await checkSubmissionResults(response.data.submission_id);
                    }, 3000);
                }
            } else {
                const errorMessage = (response && response.error) || 'Erreur lors de la soumission';
                showError(errorMessage);
            }

        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            showError('Erreur de communication avec le serveur');
        }
    }

    async function runClassicTests() {
        const response = await apiRequest(`/challenges/dev/${AppState.challenge.id}/1`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                code: AppState.editor.getValue(),
                language: AppState.currentLanguage,
                hackathon_id: AppState.challenge.hackathon_id || 1
            })
        });

        if (response.success) {
            displayTestResults(response);
            showSuccess(`Tests terminés ! Score: ${response.score}/${response.max_score}`);
        } else {
            showError(response.error || 'Erreur lors de l\'exécution des tests');
        }
    }

    async function checkSubmissionResults(submissionId) {
        try {
            const response = await apiRequest(`/challenges/submissions/${submissionId}/${AppState.userData.id}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.success) {
                displaySubmissionResults(response.data);
            } else {
                showError('Impossible de récupérer les résultats');
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des résultats:', error);
        }
    }

    // ===== AFFICHAGE DES RÉSULTATS =====
    function displayValidationResults(data) {
        const testContainer = document.getElementById('testResults');
        if (!testContainer) return;

        const results = data.results || [];
        const summary = data.summary || {};

        testContainer.innerHTML = `
            <div class="validation-header mb-4 p-3 bg-slate-800/50 rounded-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">Validation rapide</h3>
                    <div class="text-sm text-slate-400">
                        Score: <span class="text-blue-400 font-bold">${data.score || 0} pts</span>
                        ${data.max_score ? `<span class="text-slate-500">/ ${data.max_score} pts</span>` : ''} |
                        Tests: <span class="text-green-400 font-bold">${data.passed_tests || 0}/${data.total_tests || 0}</span>
                    </div>
                </div>
                <p class="text-sm text-slate-400 mt-2">
                    <i data-lucide="info" class="w-4 h-4 mr-1 inline"></i>
                    Ces résultats concernent uniquement les cas de test publics.
                </p>
            </div>
        `;

        // Afficher chaque résultat de test
        results.forEach((result, index) => {
            const testElement = createValidationTestElement(result, index + 1);
            testContainer.appendChild(testElement);
        });

        testContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        lucide.createIcons();
    }

    function displaySubmissionResults(submission) {
        const testContainer = document.getElementById('testResults');
        if (!testContainer) return;

        testContainer.innerHTML = `
            <div class="submission-header mb-4 p-4 bg-slate-800/50 rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold text-white">Résultats de soumission</h3>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${
                            submission.status === 'completed' ? 'bg-green-500 text-white' :
                            submission.status === 'error' ? 'bg-red-500 text-white' :
                            'bg-yellow-500 text-black'
                        }">
                            ${submission.status.toUpperCase()}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-slate-400">Score:</span>
                        <div class="text-blue-400 font-bold text-lg">${submission.total_score || 0} pts</div>
                        <div class="text-slate-500 text-xs">sur ${submission.max_score || 0} pts</div>
                    </div>
                    <div>
                        <span class="text-slate-400">Tests réussis:</span>
                        <div class="text-green-400 font-bold">${submission.tests_passed || 0}/${submission.total_tests || 0}</div>
                    </div>
                    <div>
                        <span class="text-slate-400">Temps total:</span>
                        <div class="text-yellow-400 font-bold">${submission.execution_time_ms || 0}ms</div>
                    </div>
                    <div>
                        <span class="text-slate-400">Mémoire max:</span>
                        <div class="text-purple-400 font-bold">${submission.memory_used_bytes ? Math.round(submission.memory_used_bytes / 1024) : 0}KB</div>
                    </div>
                </div>
            </div>
        `;

        // Afficher les résultats des tests s'ils sont disponibles
        if (submission.test_results && submission.test_results.length > 0) {
            submission.test_results.forEach((result, index) => {
                const testElement = createSubmissionTestElement(result, index + 1);
                testContainer.appendChild(testElement);
            });
        }

        testContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        lucide.createIcons();
    }

    function createValidationTestElement(result, testNumber) {
        const statusIcon = result.passed 
            ? `<i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>`
            : `<i data-lucide="circle-x" class="w-4 h-4 text-red-500"></i>`;
        
        const statusText = result.passed ? "Réussi" : "Échoué";
        const bgColor = result.passed 
            ? 'bg-green-500/10 border-green-500/30 hover:border-green-500/50' 
            : 'bg-red-500/10 border-red-500/30 hover:border-red-500/50';
    
        const element = document.createElement('div');
        element.className = `p-3 mb-3 rounded-lg border transition-all ${bgColor}`;
        
        element.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        ${statusIcon}
                        <span class="font-medium text-white">Test ${testNumber}</span>
                        <span class="text-sm font-semibold ${result.passed ? 'text-green-400' : 'text-red-400'}">
                            ${statusText}
                        </span>
                    </div>
                    ${result.description ? `
                        <p class="text-sm text-slate-300">${result.description}</p>
                    ` : ''}
                </div>
                
                <div class="space-y-2">
                    <div class="flex items-baseline gap-2">
                        <span class="text-sm text-slate-400 whitespace-nowrap">Temps d'exécution:</span>
                        <span class="text-sm font-mono">${result.execution_time_ms || 'N/A'}ms</span>
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-sm text-slate-400">Résultat:</span>
                        <code class="text-sm font-mono px-2 py-1 rounded bg-slate-900/50">
                            ${escapeHtml(result.actual_output || 'Aucune sortie')}
                        </code>
                    </div>
                </div>
            </div>
            
            ${!result.passed ? `
                <div class="mt-3 p-2 bg-red-500/20 rounded text-red-300 text-sm flex items-start gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                    <span>${escapeHtml(result.error || 'Erreur inconnue')}</span>
                </div>
            ` : ''}
        `;
    
        return element;
    }

    function createSubmissionTestElement(result, testNumber) {
        const passed = result.status === 'passed';
        const isPublic = result.is_public;

        const element = document.createElement('div');
        element.className = `test-result mb-3 p-3 rounded-lg border ${
            passed ? 'bg-green-500/10 border-green-500/30' : 'bg-red-500/10 border-red-500/30'
        }`;

        const statusIcon = passed ?
            '<i data-lucide="check" class="w-4 h-4 text-green-400"></i>' :
            '<i data-lucide="x" class="w-4 h-4 text-red-400"></i>';

        const statusText = passed ? 'RÉUSSI' : 'ÉCHOUÉ';
        const statusColor = passed ? 'text-green-400' : 'text-red-400';
        const testType = isPublic ? 'Public' : 'Privé';

        element.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    ${statusIcon}
                    <span class="font-medium text-white">Test ${testNumber} (${testType})</span>
                    <span class="text-sm ${statusColor} font-semibold">${statusText}</span>
                </div>
                <div class="text-sm text-slate-400">
                    ${result.execution_time || 0}ms | ${Math.round((result.memory_used || 0) / 1024)}KB
                </div>
            </div>
            ${result.description ? `<p class="text-sm text-slate-400 mt-2">${result.description}</p>` : ''}
            ${isPublic && result.actual_output ? `
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-slate-400">Sortie attendue:</span>
                        <pre class="mt-1 p-2 bg-slate-900/50 rounded text-slate-200 text-xs overflow-x-auto">${escapeHtml(result.expected_output || '')}</pre>
                    </div>
                    <div>
                        <span class="font-medium text-slate-400">Votre sortie:</span>
                        <pre class="mt-1 p-2 bg-slate-900/50 rounded text-slate-200 text-xs overflow-x-auto">${escapeHtml(result.actual_output || '')}</pre>
                    </div>
                </div>
            ` : !isPublic ? `
                <p class="mt-2 text-sm text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4 mr-1 inline"></i>
                    Les détails des tests privés ne sont pas affichés.
                </p>
            ` : ''}
            ${result.error_message ? `
                <div class="mt-2 p-2 bg-red-500/20 rounded text-red-400 text-sm">
                    <i data-lucide="alert-triangle" class="w-4 h-4 mr-1 inline"></i>
                    ${escapeHtml(result.error_message)}
                </div>
            ` : ''}
        `;

        return element;
    }

    function displayTestResults(response) {
        const testContainer = document.getElementById('testResults');
        if (!testContainer) return;

        testContainer.innerHTML = `
            <div class="test-header mb-4 p-3 bg-slate-800/50 rounded-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">Résultats des tests</h3>
                    <div class="text-sm text-slate-400">
                        Score: <span class="text-blue-400 font-bold">${response.score || 0}/${response.max_score || 0}</span>
                    </div>
                </div>
            </div>
        `;

        // Afficher les résultats si disponibles
        if (response.results && response.results.length > 0) {
            response.results.forEach((result, index) => {
                const testElement = createValidationTestElement(result, index + 1);
                testContainer.appendChild(testElement);
            });
        }

        testContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        lucide.createIcons();
    }

    // ===== FONCTIONS UTILITAIRES =====
    function updateLoadingState(isLoading, message = '') {
        AppState.isLoading = isLoading;
        
        // Mettre à jour les boutons
        const buttons = ['runCode', 'runAllTests', 'submitChallenge', 'resetCode'];
        buttons.forEach(id => {
            const button = document.getElementById(id);
            if (button) {
                button.disabled = isLoading;
                button.classList.toggle('opacity-50', isLoading);
                
                if (id === 'runCode' && isLoading) {
                    button.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin mr-2"></i>Exécution...';
                } else if (id === 'runCode' && !isLoading) {
                    button.innerHTML = '<i data-lucide="play" class="w-4 h-4"></i><span class="hidden sm:inline">Exécuter</span>';
                }
            }
        });

        // Afficher le message dans la console si fourni
        if (message && isLoading) {
            showProgress(message);
        } else if (!isLoading) {
            showProgress('');
        }
        
        lucide.createIcons();
    }

    function isAlgorithmicChallenge() {
        return AppState.challenge && 
               AppState.challenge.type === 'dev' && 
               AppState.challenge.category === 'algo';
    }

    function getMonacoLanguage(language) {
        const mapping = {
            'python': 'python',
            'javascript': 'javascript',
            'js': 'javascript',
            'java': 'java',
            'cpp': 'cpp',
            'c': 'c',
            'csharp': 'csharp',
            'php': 'php',
            'ruby': 'ruby',
            'go': 'go',
            'golang': 'go',
            'bash': 'shell',
            'typescript': 'typescript',
            'pascal': 'pascal'
        };
        return mapping[language] || 'plaintext';
    }

    function getDefaultTemplate(language) {
        const templates = {
            'python': '# Votre solution Python ici\n\n',
            'javascript': '// Votre solution JavaScript ici\n\n',
            'java': 'public class Solution {\n    public static void main(String[] args) {\n        // Votre solution Java ici\n    }\n}',
            'cpp': '#include <iostream>\nusing namespace std;\n\nint main() {\n    // Votre solution C++ ici\n    return 0;\n}',
            'c': '#include <stdio.h>\n\nint main() {\n    // Votre solution C ici\n    return 0;\n}',
            'csharp': 'using System;\n\nclass Program {\n    static void Main() {\n        // Votre solution C# ici\n        Console.WriteLine("Hello World");\n    }\n}',
            'php': '<?php\n// Votre solution PHP ici\necho "Hello World";\n?>',
            'ruby': '# Votre solution Ruby ici\nputs "Hello World"',
            'go': 'package main\n\nimport "fmt"\n\nfunc main() {\n    // Votre solution Go ici\n    fmt.Println("Hello World")\n}',
            'golang': 'package main\n\nimport "fmt"\n\nfunc main() {\n    // Votre solution Go ici\n    fmt.Println("Hello World")\n}',
            'bash': '#!/bin/bash\n# Votre solution Bash ici\necho "Hello World"',
            'typescript': '// Votre solution TypeScript ici\nconsole.log("Hello World");',
            'pascal': 'program Hello;\n\nbegin\n    WriteLn(\'Hello World\');\nend.'
        };
        return templates[language] || '// Votre code ici\n';
    }

    function showProgress(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="flex items-center justify-center p-4 text-blue-400">
                    <i data-lucide="loader" class="w-5 h-5 mr-2 animate-spin"></i>
                    <span>${message}</span>
                </div>
            `;
            lucide.createIcons();
        }
    }

    function showError(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="p-4 text-red-400 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <span>${escapeHtml(message)}</span>
                </div>
            `;
            lucide.createIcons();
        }
    }

    function showSuccess(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="p-4 text-green-400 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>${escapeHtml(message)}</span>
                </div>
            `;
            lucide.createIcons();
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showTooltip(e) {
        const tooltip = this.getAttribute('data-tooltip');
        if (!tooltip) return;

        const tooltipEl = document.createElement('div');
        tooltipEl.className = 'tooltip';
        tooltipEl.textContent = tooltip;

        // Positionnement
        const rect = this.getBoundingClientRect();
        tooltipEl.style.position = 'fixed';
        tooltipEl.style.left = `${rect.left + (rect.width / 2)}px`;
        tooltipEl.style.top = `${rect.top - 40}px`;
        tooltipEl.style.transform = 'translateX(-50%)';
        tooltipEl.style.zIndex = '1000';
        tooltipEl.style.pointerEvents = 'none';
        tooltipEl.classList.add('bg-slate-800', 'text-white', 'text-xs', 'px-2', 'py-1', 'rounded', 'shadow-lg', 'border', 'border-slate-700');

        document.body.appendChild(tooltipEl);
        this._tooltip = tooltipEl;
    }

    function hideTooltip() {
        if (this._tooltip) {
            this._tooltip.remove();
            this._tooltip = null;
        }
    }

    window.initMonaco = createMonacoEditor;
    window.challengeTemplates = AppState.challengeTemplates;

    console.log('Interface de challenge initialisée avec succès');
});