console.log('interfacechallenge.js loaded');
document.addEventListener('DOMContentLoaded', async function() {
    const challenge_id = window.location.pathname.split('/').pop();

    // Charger le défi
    try {
        console.log('Chargement du défi ID:', challenge_id);
        
        // D'abord essayer de charger comme défi classique
        const response = await apiRequest(`/challenges/${challenge_id}`, {
            method: "GET",
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        console.log('Réponse défi:', response);
        
        if (response && (response.data || response.challenge)) {
            const challengeData = response.data || response.challenge;
            
            // Vérifier si c'est un défi algorithmique (type=dev, category=algo)
            if (challengeData.type === 'dev' && challengeData.category === 'algo') {
                // Pour les défis algorithmiques, on a besoin de données supplémentaires
                await loadAlgorithmicChallengeData(challenge_id, challengeData);
            } else {
                initializeClassicInterface(response);
            }
        } else {
            throw new Error('Aucune donnée de défi reçue');
        }
    } catch (error) {
        console.error('Erreur lors du chargement du défi:', error);
        showError('Impossible de charger le défi: ' + error.message);
        
        // Essayer une dernière fois avec une approche plus simple
        try {
            console.log('Tentative de récupération simple...');
            const simpleResponse = await fetch(`/api/challenges/${challenge_id}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (simpleResponse.ok) {
                const data = await simpleResponse.json();
                console.log('Réponse simple:', data);
                if (data && data.data) {
                    initializeClassicInterface(data);
                }
            } else {
                console.error('Erreur HTTP:', simpleResponse.status, simpleResponse.statusText);
            }
        } catch (fallbackError) {
            console.error('Erreur fallback:', fallbackError);
        }
    }

    /**
     * Charge les données supplémentaires pour un défi algorithmique
     */
    async function loadAlgorithmicChallengeData(challengeId, challengeData) {
        try {
            // Ici on pourrait charger des données supplémentaires comme les cas de test,
            // l'historique des soumissions, etc. via des routes spécifiques
            
            // Pour l'instant, initialisons avec les données de base
            const algorithmicData = {
                challenge: challengeData,
                best_submission: null,
                submission_history: [],
                test_cases: [] // Les cas de test pourraient être dans le challenge lui-même
            };
            
            initializeAlgorithmicInterface(algorithmicData);
        } catch (error) {
            // console.error('Erreur lors du chargement des données algorithmiques:', error);
            // Fallback vers l'interface classique
            initializeClassicInterface({ data: challengeData });
        }
    }

    /**
     * Initialise l'interface pour les défis algorithmiques
     */
    function initializeAlgorithmicInterface(data) {
        const challenge = data.challenge;
        const bestSubmission = data.best_submission;
        const history = data.submission_history;

        // Afficher les informations du défi
        document.getElementById('challenge-title').textContent = challenge.title;
        document.getElementById('challenge-difficulty').textContent = challenge.difficulty.toUpperCase();
        document.getElementById('challenge-time').textContent = (challenge.time_limit / 1000) + 's';
        document.getElementById('challenge-memory').textContent = challenge.memory_limit + 'MB';
        document.getElementById('challenge-description').innerHTML = challenge.instructions || challenge.description;

        // Configurer les langages autorisés
        setupLanguageSelector(challenge.allowed_languages.split(','));

        // Afficher les cas de test publics
        displayPublicTestCases(challenge.test_cases);

        // Afficher l'historique si disponible
        if (history && history.length > 0) {
            displaySubmissionHistory(history);
        }

        // Afficher le meilleur score si disponible
        if (bestSubmission) {
            displayBestSubmission(bestSubmission);
        }

        // Configurer les boutons spécifiques aux défis algorithmiques
        setupAlgorithmicButtons();
    }

    /**
     * Initialise l'interface classique (non algorithmique)
     */
    function initializeClassicInterface(response) {
        console.log('Initialisation interface classique:', response);
        const challenge = response.data || response;
        
        // Afficher les informations du défi
        if (challenge.title) {
            const titleElement = document.getElementById('challenge-title');
            if (titleElement) titleElement.textContent = challenge.title;
        }
        
        if (challenge.difficulty) {
            const difficultyElement = document.getElementById('challenge-difficulty');
            if (difficultyElement) difficultyElement.textContent = challenge.difficulty.toUpperCase();
        }
        
        if (challenge.description) {
            const descElement = document.getElementById('challenge-description');
            if (descElement) descElement.innerHTML = challenge.description;
        }
        
        // Gestion des snippets pour l'interface classique
        setupClassicSnippets(challenge);
    }
    
    /**
     * Configure les snippets pour l'interface classique
     */
    function setupClassicSnippets(challenge) {
        console.log('Configuration des snippets:', challenge);
        
        // Attendre que Monaco soit initialisé
        setTimeout(() => {
            // Vérifier s'il y a des snippets
            if (challenge && challenge.snippets && challenge.snippets.length > 0) {
                const snippet = challenge.snippets[0];
                const availableLangs = Object.keys(snippet).filter(lang =>
                    ['bash','java','js','python','c','cpp','csharp','php','ruby','typescript','pascal','golang'].includes(lang)
                    && snippet[lang] && snippet[lang].trim() !== ''
                );

                console.log('Langages disponibles:', availableLangs);

                // Génère les boutons dans le menu déroulant
                const dropdownOptions = document.getElementById('languageDropdownOptions') || document.getElementById('languageDropdown');
                if (dropdownOptions) {
                    dropdownOptions.innerHTML = availableLangs.map(lang =>
                        `<button class="w-full px-4 py-2 text-sm text-white hover:bg-[#2D3B4E] flex items-center" data-language="${lang}">
                            <i class="ri-code-line mr-2"></i>${lang.toUpperCase()}
                        </button>`
                    ).join('');
                }

                // Met à jour le mapping des templates pour Monaco
                const templates = {};
                availableLangs.forEach(lang => {
                    // Remap 'js' en 'javascript' pour Monaco si besoin
                    if (lang === 'js') {
                        templates['javascript'] = snippet[lang];
                    } else {
                        templates[lang] = snippet[lang];
                    }
                });
                window.challengeTemplates = templates;

                // Met à jour le label du sélecteur avec le premier langage dispo
                const selectorLabel = document.querySelector('#languageSelector span');
                if (selectorLabel && availableLangs.length > 0) {
                    selectorLabel.textContent = availableLangs[0].toUpperCase();
                    // Initialise Monaco avec ce template
                    if (window.initMonaco) {
                        const monacoLang = getMonacoLanguage(availableLangs[0]);
                        window.initMonaco(monacoLang, templates[availableLangs[0]] || '');
                    }
                }

                // Ajoute les listeners sur les nouveaux boutons
                setupLanguageOptions(templates);
            } else {
                console.log('Aucun snippet trouvé, utilisation des langages par défaut');
                // Utiliser une configuration par défaut
                setupDefaultLanguages();
            }
        }, 100);
    }

    /**
     * Configure le sélecteur de langage pour les défis algorithmiques
     */
    function setupLanguageSelector(allowedLanguages) {
        const selector = document.getElementById('languageSelector');
        const dropdown = document.getElementById('languageDropdown');
        
        if (!selector || !dropdown) {
            // Créer le sélecteur s'il n'existe pas
            createLanguageSelector(allowedLanguages);
            return;
        }

        // Nettoyer les options existantes
        dropdown.innerHTML = '';

        // Ajouter les langages autorisés
        allowedLanguages.forEach(lang => {
            const option = document.createElement('button');
            option.className = 'w-full px-4 py-2 z-10 text-sm text-white hover:bg-[#2D3B4E] flex items-center';
            option.setAttribute('data-language', lang.trim());
            option.innerHTML = `<i class="ri-code-line mr-2"></i>${lang.trim().toUpperCase()}`;
            
            option.addEventListener('click', function() {
                selectLanguage(lang.trim());
                dropdown.classList.add('hidden');
            });
            
            dropdown.appendChild(option);
        });

        // Sélectionner le premier langage par défaut
        if (allowedLanguages.length > 0) {
            selectLanguage(allowedLanguages[0].trim());
        }

        // Gérer le toggle du dropdown
        selector.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function() {
            dropdown.classList.add('hidden');
        });
    }

    /**
     * Crée le sélecteur de langage s'il n'existe pas
     */
    function createLanguageSelector(allowedLanguages) {
        // Logique pour créer le sélecteur dynamiquement
        const editorContainer = document.querySelector('.bg-\\[\\#030B20\\].rounded.p-4.border.border-\\[\\#1E293B\\]');
        if (!editorContainer) return;

        const selectorHtml = `
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-white">Éditeur de Code</h2>
                <div class="relative">
                    <button id="languageSelector" class="bg-[#2D3B4E] text-white px-4 py-2 rounded flex items-center space-x-2 hover:bg-[#3B4B5C] transition-colors">
                        <i class="ri-code-line"></i>
                        <span>${allowedLanguages[0]?.toUpperCase() || 'PYTHON'}</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                    <div id="languageDropdown" class="absolute right-0 mt-2 w-48 bg-[#2D3B4E] border border-[#3B4B5C] rounded-lg shadow-lg z-10 hidden">
                        ${allowedLanguages.map(lang => `
                            <button class="w-full px-4 py-2 text-sm text-white hover:bg-[#3B4B5C] flex items-center" data-language="${lang.trim()}">
                                <i class="ri-code-line mr-2"></i>${lang.trim().toUpperCase()}
                            </button>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        const existingHeader = editorContainer.querySelector('.flex.justify-between.items-center.mb-4');
        if (existingHeader) {
            existingHeader.outerHTML = selectorHtml;
        } else {
            editorContainer.insertAdjacentHTML('afterbegin', selectorHtml);
        }

        // Reconfigurer les événements
        setupLanguageSelector(allowedLanguages);
    }

    /**
     * Sélectionne un langage de programmation
     */
    function selectLanguage(language) {
        const label = document.querySelector('#languageSelector span');
        if (label) {
            label.textContent = language.toUpperCase();
        }

        // Initialiser Monaco avec le bon langage
        const monacoLang = getMonacoLanguage(language);
        const template = getLanguageTemplate(language);
        window.initMonaco(monacoLang, template);
    }

    /**
     * Retourne le langage Monaco correspondant
     */
    function getMonacoLanguage(language) {
        const langMap = getMonacoLanguageMap();
        return langMap[language] || 'plaintext';
    }

    /**
     * Retourne un template par défaut pour un langage
     */
    function getLanguageTemplate(language) {
        const templates = {
            python: '# Votre code Python ici\nprint("Hello World")',
            javascript: '// Votre code JavaScript ici\nconsole.log("Hello World");',
            java: 'public class Solution {\n    public static void main(String[] args) {\n        System.out.println("Hello World");\n    }\n}',
            cpp: '#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << "Hello World" << endl;\n    return 0;\n}',
            bash: '#!/bin/bash\n# Votre script bash ici\necho "Hello World"'
        };
        return templates[language] || '// Template non disponible pour ce langage';
    }
    
    /**
     * Configuration par défaut des langages
     */
    function setupDefaultLanguages() {
        const defaultLangs = ['python', 'javascript', 'java', 'cpp', 'bash'];
        const templates = {};
        
        defaultLangs.forEach(lang => {
            templates[lang] = getLanguageTemplate(lang);
        });
        
        window.challengeTemplates = templates;
        setupLanguageOptions(templates);
    }
    
    /**
     * Configure les options de langage dans le dropdown
     */
    function setupLanguageOptions(templates) {
        const options = document.querySelectorAll('#languageDropdownOptions button[data-language], #languageDropdown button[data-language]');
        const label = document.querySelector('#languageSelector span');
        
        options.forEach(option => {
            option.addEventListener('click', function () {
                const langKey = this.getAttribute('data-language');
                const monacoLang = getMonacoLanguage(langKey);
                const template = templates[langKey] || getLanguageTemplate(langKey);
                if (label) {
                    label.textContent = langKey.toUpperCase();
                }
                if (window.initMonaco) {
                    window.initMonaco(monacoLang, template);
                }
                document.getElementById('languageDropdown')?.classList.add('hidden');
            });
        });
    }
    
    /**
     * Retourne le mapping Monaco Editor
     */
    function getMonacoLanguageMap() {
        return {
            bash: 'shell',
            java: 'java',
            js: 'javascript',
            javascript: 'javascript',
            python: 'python',
            c: 'c',
            cpp: 'cpp',
            csharp: 'csharp',
            php: 'php',
            ruby: 'ruby',
            typescript: 'typescript',
            pascal: 'pascal',
            golang: 'go'
        };
    }

    /**
     * Récupère le langage actuellement sélectionné
     */
    function getCurrentLanguage() {
        const langElement = document.getElementById('languageSelector')?.querySelector('span');
        return langElement ? langElement.textContent.trim().toLowerCase() : 'python';
    }

    /**
     * Récupère les données du challenge depuis l'interface
     */
    function getCurrentChallenge() {
        const challengeId = window.location.pathname.split('/').pop();
        const title = document.getElementById('challenge-title')?.textContent || '';
        const difficulty = document.getElementById('challenge-difficulty')?.textContent || '';
        
        return {
            id: challengeId,
            title: title,
            difficulty: difficulty.toLowerCase(),
            hackathon_id: 2 // Valeur par défaut, pourrait être récupérée autrement
        };
    }

    /**
     * Vérifie si le challenge actuel est algorithmique
     */
    function isAlgorithmicChallenge() {
        // On peut détecter cela via l'interface ou l'URL
        const hasAlgoElements = document.getElementById('runAllTests') !== null;
        return hasAlgoElements;
    }
    function displayPublicTestCases(testCases) {
        const publicTests = testCases.filter(tc => tc.is_public == 1);
        if (publicTests.length === 0) return;

        // Trouver le container des informations
        const infoContainer = document.getElementById('objectif-regles');
        if (!infoContainer) return;

        // Ajouter une section pour les exemples
        const examplesHtml = `
            <div class="examples-card bg-[#10101a] border border-[#232e39] rounded-xl p-4 mt-4">
                <h3 class="text-lg font-semibold text-[#3B82F6] mb-3">
                    <i class="ri-code-box-line mr-2"></i>Exemples
                </h3>
                <div class="space-y-4">
                    ${publicTests.map((test, index) => `
                        <div class="example bg-[#030B20] rounded-lg p-3 border border-[#1E293B]">
                            <h4 class="text-sm font-medium text-[#94A3B8] mb-2">Exemple ${index + 1}${test.description ? ': ' + test.description : ''}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-xs text-[#94A3B8] font-medium">Entrée:</span>
                                    <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs font-mono overflow-x-auto">${escapeHtml(test.input_data)}</pre>
                                </div>
                                <div>
                                    <span class="text-xs text-[#94A3B8] font-medium">Sortie attendue:</span>
                                    <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs font-mono overflow-x-auto">${escapeHtml(test.expected_output)}</pre>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        infoContainer.insertAdjacentHTML('beforeend', examplesHtml);
    }

    /**
     * Configure les boutons pour les défis algorithmiques
     */
    function setupAlgorithmicButtons() {
        // Bouton "Tous les tests" - validation rapide
        const runTestsBtn = document.getElementById('runAllTests');
        if (runTestsBtn) {
            runTestsBtn.addEventListener('click', async () => {
                await runQuickValidation();
            });
        }

        // Bouton "Soumettre" - soumission finale
        const submitBtn = document.getElementById('submitChallenge');
        if (submitBtn) {
            submitBtn.addEventListener('click', async () => {
                await submitFinalSolution();
            });
        }
    }

    // Initialisation de Monaco Editor
    let editor;
    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' } });

    window.initMonaco = function(language = 'shell', value = '') {
        require(['vs/editor/editor.main'], function () {
            if (editor) {
                editor.dispose();
            }
            editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: value || '',
                language: language,
                theme: 'vs-dark',
                automaticLayout: true,
                fontSize: 16,
                minimap: { enabled: false }
            });
        });
    };

    // Initialisation par défaut (shell/bash)
    setTimeout(() => {
        const templates = window.challengeTemplates || {};
        window.initMonaco('shell', templates['bash'] || '#!/bin/bash\n# Votre script bash ici\necho "Hello World"');
    }, 100);

    // Gestionnaires des événements pour les boutons
    const runCodeBtn = document.getElementById('runCode');
    if (runCodeBtn) {
        runCodeBtn.addEventListener('click', async () => {
            const code = editor.getValue();
            const langKey = getCurrentLanguage();

            const result = await apiRequest('/piston', {
                method: 'POST',
                body: JSON.stringify({
                    language: langKey,
                    code: code
                })
            });

            console.log(result);
            document.getElementById('consoleOutput').innerHTML = `
                <pre>${result.output || result.error || 'Aucune sortie'}</pre>
            `;
        });
    }

    /**
     * Exécute tous les tests du challenge
     */
    async function runAllTests() {
        if (!editor) {
            showError('Éditeur non initialisé');
            console.log('Éditeur non initialisé');
            return;
        }

        const challenge = getCurrentChallenge();
        
        const code = editor.getValue();
        if (!code.trim()) {
            showError('Veuillez saisir du code avant de lancer les tests');
            return;
        }

        const langKey = getCurrentLanguage();
        if (!langKey) {
            showError('Veuillez sélectionner un langage');
            return;
        }

        // Désactiver les boutons pendant l'exécution
        setButtonsDisabled(true);
        showProgress('Exécution des tests en cours...');

        try {
            // Utiliser la route appropriée selon le type de défi
            if (isAlgorithmicChallenge()) {
                console.log('Exécution de la validation rapide pour le défi:', challenge.id);
                await runQuickValidation();
            } else {
                const response = await apiRequest(`/challenges/dev/${challenge.id}/1`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        code: code,
                        language: langKey,
                        hackathon_id: challenge.hackathon_id || 1
                    })
                });

                if (response.success) {
                    displayTestResults(response);
                    showSuccess(`Tests terminés ! Score: ${response.score}/${response.max_score}`);
                } else {
                    showError(response.error || 'Erreur lors de l\'exécution des tests');
                }
            }

        } catch (error) {
            console.error('Erreur lors de l\'exécution:', error);
            showError('Erreur de communication avec le serveur');
        } finally {
            setButtonsDisabled(false);
            hideProgress();
        }
    }

    /**
     * Soumet le challenge selon son type
     */
    async function submitChallenge() {
        if (isAlgorithmicChallenge()) {
            await submitFinalSolution();
            console.log('Soumission du défi algorithmique');
        } else {
            await runAllTests();
        }
    }

    /**
     * Exécute une validation rapide (tests publics seulement)
     */
    async function runQuickValidation() {
        if (!editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const challenge = getCurrentChallenge();
        const code = editor.getValue();
        if (!code.trim()) {
            showError('Veuillez saisir du code avant de lancer les tests');
            return;
        }

        setButtonsDisabled(true);
        showProgress('Validation en cours...');

        try {
            console.log('Exécution de la validation rapide pour le défi:', challenge.id);
            const user_id= await getUserId();
            console.log('ID utilisateur:', user_id);
            const response = await apiRequest(`/challenges/dev/${challenge.hackathon_id || 2}/${user_id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    
                    challenge_id: challenge.id,
                    code: code,
                    language: getCurrentLanguage(),
                    action: 'validate',
                    csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                })
            });

            console.log('Réponse de validation reçue:', response);

            if (response && response.success) {
                if (response.data && response.data.success) {
                    displayValidationResults(response.data);
                } else {
                    // Gérer le cas où response.data pourrait être null
                    const errorMessage = (response.data && response.data.error) || response.error || 'Erreur lors de la validation';
                    showError(errorMessage);
                }
            } else {
                // Gérer le cas où response pourrait être null ou success false
                const errorMessage = (response && response.error) || 'Erreur lors de la validation';
                showError(errorMessage);
            }

        } catch (error) {
            console.error('Erreur lors de la validation:', error);
            showError('Erreur de communication avec le serveur');
        } finally {
            setButtonsDisabled(false);
        }
    }

    /**
     * Soumet la solution finale
     */
    async function submitFinalSolution() {
        user_id = await getUserId();
        if (!editor) {
            showError('Éditeur non initialisé');
            return;
        }

        const challenge = getCurrentChallenge();
        const code = editor.getValue();
        if (!code.trim()) {
            showError('Veuillez saisir du code avant de soumettre');
            return;
        }

        // Confirmation de soumission
        if (!confirm('Êtes-vous sûr de vouloir soumettre cette solution ? Elle sera évaluée contre tous les cas de test.')) {
            return;
        }

        setButtonsDisabled(true);
        showProgress('Soumission en cours...');
        console.log(code);

        try {
            const response = await apiRequest(`/challenges/dev/${challenge.hackathon_id || 2}/1`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    user_id: user_id,
                    hackathon_id: challenge.hackathon_id || 2,
                    challenge_id: challenge.id,
                    code: code,
                    language: getCurrentLanguage(),
                    action: 'submit',
                    csrf_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
        } finally {
            setButtonsDisabled(false);
        }
    }

    /**
     * Vérifie les résultats d'une soumission
     */
    async function checkSubmissionResults(submissionId) {
        try {
            const response = await apiRequest(`/challenges/submissions/${submissionId}/${await getUserId()}`, {
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

    /**
     * Affiche les résultats de validation rapide
     */
    function displayValidationResults(data) {
        const testContainer = document.getElementById('testResults');
        if (!testContainer) return;

        const results = data.results || [];
        const summary = data.summary || {};

        testContainer.innerHTML = `
            <div class="validation-header mb-4 p-3 bg-[#1e293b] rounded-lg">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-white">Validation rapide</h3>
                    <div class="text-sm text-[#94a3b8]">
                        Score: <span class="text-[#3b82f6] font-bold">${data.score || 0} pts</span>
                        ${data.max_score ? `<span class="text-[#64748b]">/ ${data.max_score} pts</span>` : ''} |
                        Tests: <span class="text-[#22c55e] font-bold">${data.passed_tests || 0}/${data.total_tests || 0}</span>
                    </div>
                </div>
                <p class="text-sm text-[#94a3b8] mt-2">
                    <i class="ri-information-line mr-1"></i>
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
    }

    /**
     * Affiche les résultats complets d'une soumission
     */
    function displaySubmissionResults(submission) {
        const testContainer = document.getElementById('testResults');
        if (!testContainer) return;

        testContainer.innerHTML = `
            <div class="submission-header mb-4 p-4 bg-[#1e293b] rounded-lg">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold text-white">Résultats de soumission</h3>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold ${
                            submission.status === 'completed' ? 'bg-[#22c55e] text-white' : 
                            submission.status === 'error' ? 'bg-[#ef4444] text-white' : 
                            'bg-[#eab308] text-black'
                        }">
                            ${submission.status.toUpperCase()}
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-[#94a3b8]">Score:</span>
                        <div class="text-[#3b82f6] font-bold text-lg">${submission.total_score || 0} pts</div>
                        <div class="text-[#64748b] text-xs">sur ${submission.max_score || 0} pts</div>
                    </div>
                    <div>
                        <span class="text-[#94a3b8]">Tests réussis:</span>
                        <div class="text-[#22c55e] font-bold">${submission.tests_passed || 0}/${submission.total_tests || 0}</div>
                    </div>
                    <div>
                        <span class="text-[#94a3b8]">Temps total:</span>
                        <div class="text-[#eab308] font-bold">${submission.execution_time_ms || 0}ms</div>
                    </div>
                    <div>
                        <span class="text-[#94a3b8]">Mémoire max:</span>
                        <div class="text-[#a855f7] font-bold">${submission.memory_used_bytes ? Math.round(submission.memory_used_bytes / 1024) : 0}KB</div>
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
    }

    /**
     * Crée un élément de résultat pour la validation rapide
     */
    function createValidationTestElement(result, testNumber) {
        const passed = result.passed;
        const element = document.createElement('div');
        element.className = `test-result mb-3 p-3 rounded-lg border ${
            passed ? 'bg-[#22c55e]/10 border-[#22c55e]/30' : 'bg-[#ef4444]/10 border-[#ef4444]/30'
        }`;

        const statusIcon = passed ? 
            '<i class="ri-check-line text-[#22c55e]"></i>' : 
            '<i class="ri-close-line text-[#ef4444]"></i>';

        const statusText = passed ? 'RÉUSSI' : 'ÉCHOUÉ';
        const statusColor = passed ? 'text-[#22c55e]' : 'text-[#ef4444]';

        element.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    ${statusIcon}
                    <span class="font-medium text-white">Test public ${testNumber}</span>
                    <span class="text-sm ${statusColor} font-semibold">${statusText}</span>
                </div>
                <div class="text-sm text-[#94a3b8]">
                    ${result.execution_time_ms}ms
                </div>
            </div>
            ${result.description ? `<p class="text-sm text-[#94a3b8] mb-2">${result.description}</p>` : ''}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="font-medium text-[#94a3b8]">Entrée:</span>
                    <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs overflow-x-auto">${escapeHtml(result.input || '')}</pre>
                </div>
                <div>
                    <span class="font-medium text-[#94a3b8]">Sortie attendue:</span>
                    <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs overflow-x-auto">${escapeHtml(result.expected_output || '')}</pre>
                </div>
                <div class="md:col-span-2">
                    <span class="font-medium text-[#94a3b8]">Votre sortie:</span>
                    <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs overflow-x-auto">${escapeHtml(result.actual_output || '')}</pre>
                </div>
            </div>
            ${result.error ? `
                <div class="mt-2 p-2 bg-[#ef4444]/20 rounded text-[#ef4444] text-sm">
                    <i class="ri-error-warning-line mr-1"></i>
                    ${escapeHtml(result.error)}
                </div>
            ` : ''}
        `;

        return element;
    }

    /**
     * Crée un élément de résultat pour une soumission complète
     */
    function createSubmissionTestElement(result, testNumber) {
        const passed = result.status === 'passed';
        const isPublic = result.is_public;

        const element = document.createElement('div');
        element.className = `test-result mb-3 p-3 rounded-lg border ${
            passed ? 'bg-[#22c55e]/10 border-[#22c55e]/30' : 'bg-[#ef4444]/10 border-[#ef4444]/30'
        }`;

        const statusIcon = passed ? 
            '<i class="ri-check-line text-[#22c55e]"></i>' : 
            '<i class="ri-close-line text-[#ef4444]"></i>';

        const statusText = passed ? 'RÉUSSI' : 'ÉCHOUÉ';
        const statusColor = passed ? 'text-[#22c55e]' : 'text-[#ef4444]';
        const testType = isPublic ? 'Public' : 'Privé';

        element.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    ${statusIcon}
                    <span class="font-medium text-white">Test ${testNumber} (${testType})</span>
                    <span class="text-sm ${statusColor} font-semibold">${statusText}</span>
                </div>
                <div class="text-sm text-[#94a3b8]">
                    ${result.execution_time}ms | ${Math.round((result.memory_used || 0) / 1024)}KB
                </div>
            </div>
            ${result.description ? `<p class="text-sm text-[#94a3b8] mt-2">${result.description}</p>` : ''}
            ${isPublic && result.actual_output ? `
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="font-medium text-[#94a3b8]">Sortie attendue:</span>
                        <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs overflow-x-auto">${escapeHtml(result.expected_output || '')}</pre>
                    </div>
                    <div>
                        <span class="font-medium text-[#94a3b8]">Votre sortie:</span>
                        <pre class="mt-1 p-2 bg-[#0f172a] rounded text-[#e2e8f0] text-xs overflow-x-auto">${escapeHtml(result.actual_output || '')}</pre>
                    </div>
                </div>
            ` : !isPublic ? `
                <p class="mt-2 text-sm text-[#94a3b8]">
                    <i class="ri-lock-line mr-1"></i>
                    Les détails des tests privés ne sont pas affichés.
                </p>
            ` : ''}
            ${result.error_message ? `
                <div class="mt-2 p-2 bg-[#ef4444]/20 rounded text-[#ef4444] text-sm">
                    <i class="ri-error-warning-line mr-1"></i>
                    ${escapeHtml(result.error_message)}
                </div>
            ` : ''}
        `;

        return element;
    }

    /**
     * Affiche l'historique des soumissions
     */
    function displaySubmissionHistory(history) {
        const historyContainer = document.getElementById('submissionHistory');
        if (!historyContainer || !history || history.length === 0) return;

        const historyHtml = `
            <div class="history-card bg-[#10101a] border border-[#232e39] rounded-xl p-4 mt-4">
                <h3 class="text-lg font-semibold text-[#3B82F6] mb-3">
                    <i class="ri-history-line mr-2"></i>Historique des soumissions
                </h3>
                <div class="space-y-3">
                    ${history.map(submission => `
                        <div class="submission bg-[#030B20] rounded-lg p-3 border border-[#1E293B]">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm text-[#94A3B8]">
                                        ${new Date(submission.created_at).toLocaleString()}
                                    </span>
                                    <span class="ml-2 text-xs px-2 py-1 rounded ${
                                        submission.status === 'completed' ? 'bg-[#22c55e] text-white' : 
                                        submission.status === 'error' ? 'bg-[#ef4444] text-white' : 
                                        'bg-[#eab308] text-black'
                                    }">
                                        ${submission.status.toUpperCase()}
                                    </span>
                                </div>
                                <div class="text-[#3B82F6] font-bold">
                                    ${submission.total_score || 0}%
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-[#94A3B8]">
                                Langage: ${submission.language} | 
                                Tests: ${submission.tests_passed}/${submission.total_tests} | 
                                Temps: ${submission.execution_time_ms}ms
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        const infoContainer = document.getElementById('objectif-regles');
        if (infoContainer) {
            infoContainer.insertAdjacentHTML('beforeend', historyHtml);
        }
    }

    /**
     * Affiche le meilleur score
     */
    function displayBestSubmission(bestSubmission) {
        const scoreContainer = document.getElementById('bestScore');
        if (!scoreContainer || !bestSubmission) return;

        const scoreHtml = `
            <div class="best-score-card bg-[#10101a] border border-[#232e39] rounded-xl p-4 mt-4">
                <h3 class="text-lg font-semibold text-[#22c55e] mb-3">
                    <i class="ri-trophy-line mr-2"></i>Meilleur score
                </h3>
                <div class="bg-[#030B20] rounded-lg p-3 border border-[#1E293B]">
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-2xl font-bold text-[#22c55e]">
                            ${bestSubmission.total_score || 0}%
                        </div>
                        <div class="text-sm text-[#94A3B8]">
                            ${new Date(bestSubmission.created_at).toLocaleString()}
                        </div>
                    </div>
                    <div class="text-sm text-[#94A3B8]">
                        Langage: ${bestSubmission.language} | 
                        Tests: ${bestSubmission.tests_passed}/${bestSubmission.total_tests} | 
                        Temps: ${bestSubmission.execution_time_ms}ms
                    </div>
                </div>
            </div>
        `;

        const infoContainer = document.getElementById('objectif-regles');
        if (infoContainer) {
            infoContainer.insertAdjacentHTML('beforeend', scoreHtml);
        }
    }

    /**
     * Fonctions utilitaires
     */
    function getMonacoLanguage(language) {
        const mapping = {
            'python': 'python',
            'javascript': 'javascript',
            'java': 'java',
            'cpp': 'cpp',
            'c': 'c',
            'csharp': 'csharp',
            'php': 'php',
            'ruby': 'ruby',
            'go': 'go',
            'bash': 'shell'
        };
        return mapping[language] || 'plaintext';
    }

    function getLanguageTemplate(language) {
        const templates = {
            'python': '# Votre solution Python ici\n\n',
            'javascript': '// Votre solution JavaScript ici\n\n',
            'java': 'public class Solution {\n    public static void main(String[] args) {\n        // Votre solution Java ici\n    }\n}',
            'cpp': '#include <iostream>\nusing namespace std;\n\nint main() {\n    // Votre solution C++ ici\n    return 0;\n}',
            'c': '#include <stdio.h>\n\nint main() {\n    // Votre solution C ici\n    return 0;\n}',
            'go': 'package main\n\nimport "fmt"\n\nfunc main() {\n    // Votre solution Go ici\n}',
            'bash': '#!/bin/bash\n# Votre solution Bash ici\n'
        };
        return templates[language] || '// Votre code ici\n';
    }

    /**
     * Échappe le HTML pour éviter les injections XSS
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Active/désactive les boutons d'action
     */
    function setButtonsDisabled(disabled) {
        const buttons = ['runAllTests', 'submitChallenge', 'runCode'];
        buttons.forEach(id => {
            const button = document.getElementById(id);
            if (button) {
                button.disabled = disabled;
                button.classList.toggle('opacity-50', disabled);
                button.classList.toggle('cursor-not-allowed', disabled);
            }
        });
    }

    /**
     * Affiche un indicateur de progression
     */
    function showProgress(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="flex items-center gap-3 text-[#3b82f6]">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-[#3b82f6]"></div>
                    <span>${message}</span>
                </div>
            `;
        }
    }

    /**
     * Masque l'indicateur de progression
     */
    function hideProgress() {
        // La fonction displayTestResults ou showError s'occupera de remplacer le contenu
    }

    /**
     * Affiche un message d'erreur
     */
    function showError(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="text-[#ef4444] flex items-center gap-2">
                    <i class="ri-error-warning-line"></i>
                    <span>${escapeHtml(message)}</span>
                </div>
            `;
        }
    }

    /**
     * Affiche un message de succès
     */
    function showSuccess(message) {
        const consoleOutput = document.getElementById('consoleOutput');
        if (consoleOutput) {
            consoleOutput.innerHTML = `
                <div class="text-[#22c55e] flex items-center gap-2">
                    <i class="ri-check-line"></i>
                    <span>${escapeHtml(message)}</span>
                </div>
            `;
        }
    }

    // Gestionnaires d'événements pour les boutons d'action
    document.getElementById('runAllTests')?.addEventListener('click', async () => {
        await runAllTests();
    });

    document.getElementById('submitChallenge')?.addEventListener('click', async () => {
        await submitChallenge();
    });

    // Configuration initiale du sélecteur de langage s'il existe
    const selector = document.getElementById('languageSelector');
    const dropdown = document.getElementById('languageDropdown');
    
    if (selector && dropdown) {
        // Toggle dropdown
        selector.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // Fermer si clic en dehors
        document.addEventListener('click', function () {
            dropdown.classList.add('hidden');
        });
    }

});