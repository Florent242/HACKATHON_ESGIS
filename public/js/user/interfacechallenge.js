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
        const challengeData = await loadChallengeData(challengeId);
        if (!challengeData.success) {
            showInterfaceError(challengeData.error || challengeData.message || 'Challenge non accessible');
            return;
        }

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

    function showInterfaceError(message) {
        // Vérifier si la modale existe déjà
        if (document.getElementById('interface-error-modal')) return;
    
        // Créer l’overlay qui bloque tout
        const overlay = document.createElement('div');
        overlay.id = 'interface-error-overlay';
        overlay.className = 'interface-error-overlay';
    
        // Créer la modale
        const modal = document.createElement('div');
        modal.id = 'interface-error-modal';
        modal.className = 'interface-error-modal fade-in-up';
    
        // Icône ou spinner (Lucide ou CSS natif)
        const icon = document.createElement('div');
        icon.className = 'interface-error-icon flex items-center justify-center bg-red-500/20 rounded-lg p-2 border border-red-500/20';
        icon.innerHTML = `
        <i data-lucide="triangle-alert" class="w-10 h-10 text-red-500"></i>
        `;
    
        // Message
        const msg = document.createElement('div');
        msg.className = 'interface-error-message';
        msg.textContent = message;

        // Assembler la modale
        modal.appendChild(icon);
        modal.appendChild(msg);
    
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
    
        // Désactiver le scroll sur le body
        document.body.style.overflow = 'hidden';
        lucide.createIcons();
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

            if (!response || !response.success || (!response.data && !response.challenge)) {
                return response??false;
            }

            const challengeData = response.data || response.challenge;
            AppState.challenge = challengeData;

            // Initialiser l'interface selon le type de challenge
            if (challengeData.type === 'dev' && challengeData.category === 'algo') {
                await initializeAlgorithmicInterface(challengeData);
            } else {
                await initializeClassicInterface(challengeData);
            }

            return response;

        } catch (error) {
            console.error('Erreur lors du chargement du défi:', error);
            showNotification('Erreur !', error.message || error.error || 'Une erreur est survenue lors du chargement du défi', 'error');
        } finally {
            updateLoadingState(false);
            resetConsole();
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
        const supportedLanguages = ['bash', 'java', 'javascript', 'python', 'c', 'cpp', 'csharp', 'php', 'ruby', 'typescript', 'pascal', 'golang'];

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
        const defaultLangs = ['python', 'javascript', 'java', 'cpp', 'shell', 'php', 'ruby', 'go', 'c', 'csharp', 'typescript', 'pascal'];
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
            // Vérifier si Monaco est déjà chargé
            if (window.monaco && window.monaco.editor) {
                try {
                    initEditor();
                    resolve();
                } catch (error) {
                    console.error('Erreur lors de l\'initialisation de Monaco:', error);
                    fallbackToTextarea();
                    resolve(); // On résout quand même pour ne pas bloquer l'application
                }
                return;
            }

            // Charger Monaco depuis CDN
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.js';
            script.async = true;
            script.onload = () => {
                require.config({
                    paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' },
                    waitSeconds: 15 // Timeout de 15 secondes
                });

                require(['vs/editor/editor.main'], () => {
                    try {
                        initEditor();
                        resolve();
                    } catch (error) {
                        console.error('Erreur lors de l\'initialisation de Monaco:', error);
                        fallbackToTextarea();
                        resolve(); // On résout quand même pour ne pas bloquer l'application
                    }
                }, (error) => {
                    console.error('Erreur de chargement de Monaco Editor:', error);
                    fallbackToTextarea();
                    resolve(); // On résout quand même pour ne pas bloquer l'application
                });
            };

            script.onerror = (error) => {
                console.error('Erreur de chargement du script Monaco:', error);
                fallbackToTextarea();
                resolve(); // On résout quand même pour ne pas bloquer l'application
            };

            document.head.appendChild(script);
        });

        function initEditor() {
            const initialLanguage = AppState.currentLanguage;
            const initialTemplate = AppState.challengeTemplates[initialLanguage] || getDefaultTemplate(initialLanguage);
            createMonacoEditor(getMonacoLanguage(initialLanguage), initialTemplate);
            console.log('Monaco Editor initialisé avec succès');
        }
    }

    // Fallback vers un textarea simple
    function fallbackToTextarea() {
        console.warn('Utilisation du fallback textarea');
        const container = document.getElementById('monaco-editor');
        if (!container) return;

        // Créer un textarea simple
        const textarea = document.createElement('textarea');
        textarea.id = 'fallback-editor';
        textarea.style.width = '100%';
        textarea.style.height = '500px';
        textarea.style.fontFamily = 'monospace';
        textarea.style.padding = '10px';
        textarea.style.borderRadius = '4px';
        textarea.style.backgroundColor = '#1e1e1e';
        textarea.style.color = '#d4d4d4';
        textarea.style.border = '1px solid #333';
        
        // Remplacer le conteneur Monaco par le textarea
        container.innerHTML = '';
        container.appendChild(textarea);
        
        // Mettre à jour l'API de l'éditeur pour la compatibilité
        AppState.editor = {
            getValue: () => textarea.value,
            setValue: (value) => { textarea.value = value; },
            dispose: () => { /* noop */ },
            onDidChangeModelContent: (callback) => {
                textarea.addEventListener('input', callback);
                return { dispose: () => textarea.removeEventListener('input', callback) };
            }
        };
        
        // Afficher un message d'avertissement
        const warning = document.createElement('div');
        warning.style.color = '#ff9800';
        warning.style.marginBottom = '10px';
        warning.textContent = 'Note: L\'éditeur avancé n\'a pas pu être chargé. Utilisation du mode de compatibilité.';
        container.prepend(warning);
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
            
            // Vérification que nous avons bien reçu une réponse
            if (result) {
                displayExecutionResult(result);
            } else {
                showError('Aucune réponse du serveur');
            }
        } catch (error) {
            console.error('Erreur lors de l\'exécution:', error);
            
            // Créer un objet d'erreur standardisé pour l'affichage
            const errorResult = {
                success: false,
                error: error.message || 'Erreur de communication avec le serveur',
                data: {
                    language: AppState.currentLanguage
                }
            };
            
            displayExecutionResult(errorResult);
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
        if (!code.trim()) {
            showError('Veuillez saisir du code avant de lancer les tests');
            return;
        }

        updateLoadingState(true, 'Tests en cours...');
        showTestPending();

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
        showTestPending();

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
    console.log('Réponse de l\'API:', response);

    function displayExecutionResult(result) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (!consoleOutput) return;

        console.log('Résultat d\'exécution:', result);

        // Vérification de sécurité pour éviter les erreurs
        if (!result) {
            consoleOutput.innerHTML = `
                <div class="p-4 text-red-400 flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    <span>Aucun résultat reçu du serveur</span>
                </div>
            `;
            lucide.createIcons();
            return;
        }

        if (result.success) {
            // Exécution réussie
            const output = result.output || result.data?.output || '';
            const stderr = result.run_info?.stderr || result.data?.stderr || '';
            
            consoleOutput.innerHTML = `
                <div class="p-4 space-y-2">
                    <div class="flex items-center text-green-400">
                        <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                        <span class="font-medium">Exécution réussie (${result.language || result.data?.language || 'Inconnu'})</span>
                    </div>
                    ${output ? `
                        <div class="mt-2">
                            <div class="text-xs text-slate-400 mb-1">Sortie :</div>
                            <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto">${escapeHtml(output)}</pre>
                        </div>
                    ` : ''}
                    ${stderr ? `
                        <div class="mt-2">
                            <div class="text-xs text-slate-400 mb-1">Avertissements :</div>
                            <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto text-amber-300">${escapeHtml(stderr)}</pre>
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

            // Affichage de l'erreur principale - avec vérifications de sécurité
            const mainError = result.error || 
                result.data?.error ||
                result.run_info?.stderr ||
                result.compile_info?.stderr ||
                result.data?.stderr ||
                result.message ||
                'Erreur d\'exécution inconnue';

            errorDetails += `
                <div class="mt-2">
                    <div class="text-xs text-red-400 mb-1">Détails de l'erreur :</div>
                    <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto text-red-300">${escapeHtml(mainError)}</pre>
                </div>
            `;

            // Si il y a une sortie malgré l'erreur
            const partialOutput = result.data?.output || result.data?.stderr || result.output;
            if (partialOutput) {
                errorDetails += `
                    <div class="mt-2">
                        <div class="text-xs text-slate-400 mb-1">Sortie partielle :</div>
                        <pre class="bg-slate-800/50 p-3 rounded-lg overflow-auto">${escapeHtml(partialOutput)}</pre>
                    </div>
                `;
            }

            const language = result.data?.language || result.language || 'Inconnu';
            const version = result.data?.version || result.version || '';
            const exitCode = result.data?.exit_code || result.exit_code;

            consoleOutput.innerHTML = `
                <div class="p-4 space-y-2">
                    <div class="flex items-center text-red-400">
                        <i data-lucide="circle-x" class="w-5 h-5 mr-2"></i>
                        <span class="font-medium">Erreur d'exécution (${language}${version ? ' ' + version : ''})</span>
                        ${exitCode ? `<span class="ml-2 text-xs bg-red-500/20 px-2 py-1 rounded whitespace-nowrap">Code: ${exitCode}</span>` : ''}
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
                    resetConsole(); // Déplacer ici
                } else {
                    const errorMessage = (response.data && response.data.error) || response.error || 'Erreur lors de la validation';
                    showError(errorMessage);
                }
            } else {
                const errorMessage = (response && response.data && response.data.data && response.data.data.error) || 
                                   (response && response.error) || 'Erreur lors de la validation';
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
            console.log(AppState.challenge.phase_id);
            const response = await apiRequest(`/challenges/dev/${AppState.challenge.hackathon_id || 2}`, {
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
                    csrf_token: AppState.userData.csrf_token,
                    phase_id: AppState.challenge.phase_id
                })
            });

            if (response && response.success) {
                
                // Attendre un peu puis récupérer les résultats si disponible
                if (response.data && response.data.submission_id) {
                    updateLoadingState(true, 'Récupération des résultats...');
                    setTimeout(async () => {
                        await checkSubmissionResults(response.data.submission_id);
                        showSuccess('Solution soumise avec succès !');
                    }, 3000);
                }
            } else {
                const errorMessage = (response && response.message) || (response && response.error) || 'Erreur lors de la soumission';
                showNotification('Erreur !', errorMessage, 'error');
                showError(errorMessage);
                resetTestPending();
            }

        } catch (error) {
            console.error('Erreur lors de la soumission:', error);
            showError('Erreur de communication avec le serveur');
        } finally {
            updateLoadingState(false);
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
    
        const score = summary.score || 0;
        const maxScore = summary.max_score || null;
        const passed = summary.passed_tests || 0;
        const total = summary.total_tests || 0;
    
        testContainer.innerHTML = `
            <div class="validation-header mb-4 p-3 bg-slate-800/50 rounded-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">Validation rapide</h3>
                    <div class="text-sm text-slate-400">
                        Score: <span class="text-blue-400 font-bold">${score} %</span>
                        ${maxScore ? `<span class="text-slate-500">/ ${maxScore} %</span>` : ''}
                        &nbsp;|&nbsp;
                        Tests: <span class="text-green-400 font-bold">${passed}/${total}</span>
                    </div>
                </div>
                <p class="text-sm text-slate-400 mt-2">
                    <i data-lucide="info" class="w-4 h-4 mr-1 inline"></i>
                    Ces résultats concernent uniquement les cas de test publics.
                </p>
            </div>
        `;
    
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
    
        const passRate = Math.round((submission.tests_passed / submission.total_tests) * 100) || 0;
    
        const execTime = submission.execution_time_ms != null ? `${submission.execution_time_ms}ms` : '—';
        const memoryUsed = submission.memory_used_bytes != null ? `${Math.round(submission.memory_used_bytes / 1024)} KB` : '—';
    
        testContainer.innerHTML = `
            <div class="submission-result bg-slate-800/50 rounded-xl border border-slate-700/50 overflow-hidden mb-6">
                <!-- En-tête -->
                <div class="p-5 border-b border-slate-700/50">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                                <i data-lucide="clipboard-list" class="w-5 h-5 text-slate-400"></i>
                                Résultats de la soumission
                            </h3>
                            <p class="text-sm text-slate-400 mt-1">
                                ${new Date(submission.submitted_at).toLocaleString()} • 
                                ${submission.language || 'Bash'}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-emerald-400">
                                    ${submission.total_score} pts
                                </div>
                                <div class="text-xs text-slate-400">sur ${submission.max_score} pts</div>
                            </div>
                            <div class="h-10 w-px bg-slate-700/50"></div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-emerald-400">
                                    ${submission.tests_passed}/${submission.total_tests}
                                </div>
                                <div class="text-xs text-slate-400">tests réussis</div>
                            </div>
                        </div>
                    </div>
    
                    ${submission.error_message ? `
                        <div class="mt-4 p-3 bg-red-500/10 rounded text-red-400 text-sm">
                            <i data-lucide="alert-triangle" class="w-4 h-4 mr-1 inline"></i>
                            ${escapeHtml(submission.error_message)}
                        </div>
                    ` : ''}
                </div>
    
                <!-- Métriques de performance -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-5 bg-slate-800/30">
                    <div class="metric-card flex items-center gap-3 p-3 bg-slate-800/20 rounded-lg border border-slate-700/50 hover:bg-slate-700/30 hover:border-slate-600/50 transition-all duration-300">
                        <div class="metric-icon w-8 h-8 rounded-lg flex items-center justify-center bg-blue-500/10 text-blue-400">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="metric-value text-base font-semibold text-white">${execTime}</div>
                            <div class="metric-label text-xs text-slate-400">Temps d'exécution</div>
                        </div>
                    </div>
                    <div class="metric-card flex items-center gap-3 p-3 bg-slate-800/20 rounded-lg border border-slate-700/50 hover:bg-slate-700/30 hover:border-slate-600/50 transition-all duration-300">
                        <div class="metric-icon w-8 h-8 rounded-lg flex items-center justify-center bg-purple-500/10 text-purple-400">
                            <i data-lucide="cpu" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="metric-value text-base font-semibold text-white">${memoryUsed}</div>
                            <div class="metric-label text-xs text-slate-400">Mémoire utilisée</div>
                        </div>
                    </div>
                    <div class="metric-card flex items-center gap-3 p-3 bg-slate-800/20 rounded-lg border border-slate-700/50 hover:bg-slate-700/30 hover:border-slate-600/50 transition-all duration-300">
                        <div class="metric-icon w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-500/10 text-emerald-400">
                            <i data-lucide="percent" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="metric-value text-base font-semibold text-white">${passRate}%</div>
                            <div class="metric-label text-xs text-slate-400">Taux de réussite</div>
                        </div>
                    </div>
                    <div class="metric-card flex items-center gap-3 p-3 bg-slate-800/20 rounded-lg border border-slate-700/50 hover:bg-slate-700/30 hover:border-slate-600/50 transition-all duration-300">
                        <div class="metric-icon w-8 h-8 rounded-lg flex items-center justify-center bg-slate-500/10 text-slate-400">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="metric-value text-base font-semibold text-white">${new Date(submission.submitted_at).toLocaleDateString()}</div>
                            <div class="metric-label text-xs text-slate-400">Date de soumission</div>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- En-tête des tests -->
            <div class="flex items-center justify-between mb-4 mt-6">
                <h4 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">
                    <i data-lucide="flask-conical" class="w-4 h-4 mr-2 inline-block"></i>
                    Détail des tests
                </h4>
                <div class="text-xs text-slate-500">
                    ${submission.tests_passed} sur ${submission.total_tests} tests réussis
                </div>
            </div>
        `;
    
        const testResultsContainer = document.createElement('div');
        testResultsContainer.className = 'space-y-3';
    
        if (submission.test_results?.length > 0) {
            submission.test_results.forEach((test, index) => {
                const testElement = createSubmissionTestElement(test, index + 1);
                testResultsContainer.appendChild(testElement);
            });
        } else {
            testResultsContainer.innerHTML = `
                <div class="text-center py-8 text-slate-500">
                    <i data-lucide="info" class="w-8 h-8 mx-auto mb-2"></i>
                    <p>Aucun résultat de test disponible pour cette soumission.</p>
                </div>
            `;
        }
    
        testContainer.appendChild(testResultsContainer);
        testContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        lucide.createIcons();
    }

    function createValidationTestElement(result, testNumber) {
        const passed = result.passed;
        const statusIcon = passed
            ? `<i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>`
            : `<i data-lucide="circle-x" class="w-4 h-4 text-red-500"></i>`;
    
        const statusText = passed ? "Réussi" : "Échoué";
        const bgColor = passed
            ? 'bg-green-500/10 border-green-500/30 hover:border-green-500/50'
            : 'bg-red-500/10 border-red-500/30 hover:border-red-500/50';
    
        const execTime = result.execution_time_ms != null ? `${result.execution_time_ms}ms` : 'N/A ms';
    
        const element = document.createElement('div');
        element.className = `p-3 mb-3 rounded-lg border transition-all ${bgColor}`;
    
        const expectedSection = result.expected_output != null ? `
            <div class="flex items-baseline gap-2">
                <span class="text-sm text-slate-400">Sortie attendue:</span>
                <code class="text-sm font-mono px-2 py-1 rounded bg-slate-900/50">
                    ${escapeHtml(result.expected_output)}
                </code>
            </div>
        ` : '';
    
        element.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        ${statusIcon}
                        <span class="font-medium text-white">Test ${testNumber}</span>
                        <span class="text-sm font-semibold ${passed ? 'text-green-400' : 'text-red-400'}">${statusText}</span>
                    </div>
                    ${result.description ? `
                        <p class="text-sm text-slate-300">${result.description}</p>
                    ` : ''}
                </div>
    
                <div class="space-y-2">
                    <div class="flex items-baseline gap-2">
                        <span class="text-sm text-slate-400 whitespace-nowrap">Temps d'exécution:</span>
                        <span class="text-sm font-mono">${execTime}</span>
                    </div>
                    ${expectedSection}
                    <div class="flex items-baseline gap-2">
                        <span class="text-sm text-slate-400">Votre sortie:</span>
                        <code class="text-sm font-mono px-2 py-1 rounded bg-slate-900/50">
                            ${escapeHtml(result.actual_output || 'Aucune sortie')}
                        </code>
                    </div>
                </div>
            </div>
    
            ${!passed ? `
                <div class="mt-3 p-2 bg-red-500/20 rounded text-red-300 text-sm flex items-start gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
                    <span>${escapeHtml(result.error || 'Erreur inconnue ou non existante')}</span>
                </div>
            ` : ''}
        `;
    
        return element;
    }

    function createSubmissionTestElement(result, testNumber) {
        const passed = result.status === 'passed';
        const isPublic = result.is_public;
    
        const element = document.createElement('div');
        element.className = `test-result mb-3 p-3 rounded-lg border ${passed ? 'bg-green-500/10 border-green-500/30' : 'bg-red-500/10 border-red-500/30'}`;
    
        const statusIcon = passed
            ? '<i data-lucide="check" class="w-4 h-4 text-green-400"></i>'
            : '<i data-lucide="x" class="w-4 h-4 text-red-400"></i>';
    
        const statusText = passed ? 'RÉUSSI' : 'ÉCHOUÉ';
        const statusColor = passed ? 'text-green-400' : 'text-red-400';
        const testType = isPublic ? 'Public' : 'Privé';
    
        const executionTime = result.execution_time_ms !== null ? `${result.execution_time_ms}ms` : '— ms';
        const memoryUsed = result.memory_used_bytes !== null ? `${Math.round(result.memory_used_bytes / 1024)}KB` : '— KB';
    
        let outputSection = '';
    
        if (isPublic && result.actual_output) {
            // Si expected_output existe, affiche les deux
            if (result.expected_output) {
                outputSection = `
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <span class="font-medium text-slate-400">Sortie attendue:</span>
                            <pre class="mt-1 p-2 bg-slate-900/50 rounded text-slate-200 text-xs overflow-x-auto">
    ${escapeHtml(result.expected_output)}
                            </pre>
                        </div>
                        <div>
                            <span class="font-medium text-slate-400">Votre sortie:</span>
                            <pre class="mt-1 p-2 bg-slate-900/50 rounded text-slate-200 text-xs overflow-x-auto">
    ${escapeHtml(result.actual_output)}
                            </pre>
                        </div>
                    </div>
                `;
            } else {
                // Sinon, affiche uniquement la sortie utilisateur
                outputSection = `
                    <div class="mt-3 text-sm">
                        <span class="font-medium text-slate-400">Votre sortie:</span>
                        <pre class="mt-1 p-2 bg-slate-900/50 rounded text-slate-200 text-xs overflow-x-auto">
    ${escapeHtml(result.actual_output)}
                        </pre>
                    </div>
                `;
            }
        } else if (!isPublic) {
            outputSection = `
                <p class="mt-2 text-sm text-slate-400">
                    <i data-lucide="lock" class="w-4 h-4 mr-1 inline"></i>
                    Les détails des tests privés ne sont pas affichés.
                </p>
            `;
        }
    
        const errorSection = result.error_message ? `
            <div class="mt-2 p-2 bg-red-500/20 rounded text-red-400 text-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4 mr-1 inline"></i>
                ${escapeHtml(result.error_message)}
            </div>
        ` : '';
    
        element.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    ${statusIcon}
                    <span class="font-medium text-white">Test ${testNumber} (${testType})</span>
                    <span class="text-sm ${statusColor} font-semibold">${statusText}</span>
                </div>
                <div class="text-sm text-slate-400">
                    ${executionTime} | ${memoryUsed}
                </div>
            </div>
            ${outputSection}
            ${errorSection}
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
        }

        lucide.createIcons();
    }

    function isAlgorithmicChallenge() {
        return AppState.challenge &&
            AppState.challenge.type === 'dev' &&
            AppState.challenge.category === 'algo';
    }

    function getMonacoLanguage(language) {
        // Gestion des cas spéciaux avant même de vérifier Monaco
        const specialMappings = {
            'bash': 'shell',     // Bash utilise le mode shell dans Monaco
            'golang': 'go',      // Alias pour Go
            'js': 'javascript',  // Alias pour JavaScript
            'ts': 'typescript'   // Alias pour TypeScript
        };
    
        // Vérifier d'abord les mappages spéciaux
        const lowerLang = language.toLowerCase();
        if (specialMappings[lowerLang]) {
            return specialMappings[lowerLang];
        }
    
        // Vérifier si Monaco est chargé
        if (typeof monaco === 'undefined') {
            console.warn('Monaco Editor non chargé, utilisation du mapping par défaut');
            return getDefaultMonacoLanguage(language);
        }
    
        try {
            // Essayer de récupérer le langage directement depuis Monaco
            const monacoLang = monaco.languages.getLanguages().find(
                lang => lang.id.toLowerCase() === lowerLang ||
                       (lang.aliases && lang.aliases.some(
                           alias => alias.toLowerCase() === lowerLang
                       ))
            );
    
            return monacoLang ? monacoLang.id : getDefaultMonacoLanguage(language);
        } catch (error) {
            console.warn('Erreur lors de la récupération des langages Monaco:', error);
            return getDefaultMonacoLanguage(language);
        }
    }

    // Fonction de secours avec le mapping par défaut
    function getDefaultMonacoLanguage(language) {
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
            'sh': 'shell',
            'typescript': 'typescript',
            'ts': 'typescript',
            'pascal': 'pascal'
        };
        return mapping[language.toLowerCase()] || 'plaintext';
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

    function showTestPending() {
        const consoleOutput = document.getElementById('testResults');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="flex items-center justify-center p-4 text-blue-400">
                    <i data-lucide="loader" class="w-5 h-5 mr-2 animate-spin"></i>
                    <span>En attente des résultats...</span>
                </div>
            `;
            lucide.createIcons();
        }
    }

    function resetConsole() {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="h-full flex flex-col items-center justify-center text-center text-slate-500 text-sm">
                    <i data-lucide="terminal" class="w-6 h-6 mb-2"></i>
                    <span>Console prête pour l'exécution...</span>
                </div>
            `;
            lucide.createIcons();
        }
    }

    function resetTestPending() {
        const consoleOutput = document.getElementById('testResults');
        if (consoleOutput) {
            consoleOutput.innerHTML = `<div class="h-40 flex flex-col items-center justify-center text-center text-slate-500">
                            <i data-lucide="flask-conical" class="w-8 h-8 mb-3"></i>
                            <span class="font-medium text-slate-300 mb-1">Prêt pour les tests</span>
                            <p class="text-sm">Exécutez votre code pour voir les résultats</p>
                        </div>`;
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

    window.initMonaco = createMonacoEditor;
    window.challengeTemplates = AppState.challengeTemplates;

    console.log('Interface de challenge initialisée avec succès');
});