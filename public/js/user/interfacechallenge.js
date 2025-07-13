console.log('interfacechallenge.js loaded');
document.addEventListener('DOMContentLoaded', async function() {
    const challenge_id = window.location.pathname.split('/').pop();
    const challenge = await apiRequest(`/challenges/${challenge_id}`, {
        method: "GET",
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    });

    console.log('Challenge:', challenge);
    // Pour accéder à la liste des snippets :
    if (challenge.snippets) {
        challenge.snippets.forEach(snippet => {
            console.log('Snippet:', snippet);
            // Affiche ou utilise le snippet dans l'interface
        });
    }
    // Mapping Monaco Editor
    const monacoLangMap = {
        bash: 'shell',
        java: 'java',
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

    const templates = window.challengeTemplates || {};
    // Initialisation par défaut (bash)
    window.initMonaco('shell', templates['bash'] || '');

    const selector = document.getElementById('languageSelector');
    const dropdown = document.getElementById('languageDropdown');
    const options = dropdown.querySelectorAll('button[data-language]');
    const label = selector.querySelector('span');

    options.forEach(option => {
        option.addEventListener('click', function () {
            const langKey = this.getAttribute('data-language');
            const monacoLang = monacoLangMap[langKey] || 'plaintext';
            const template = templates[langKey] || '// Pas de template pour ce langage';
            label.textContent = this.textContent.trim();
            window.initMonaco(monacoLang, template);
            dropdown.classList.add('hidden');
        });
    });

    // Toggle dropdown
    selector.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    // Fermer si clic en dehors
    document.addEventListener('click', function () {
        dropdown.classList.add('hidden');
    });

    if (challenge && challenge.data && challenge.data.snippets && challenge.data.snippets.length > 0) {
        const snippet = challenge.data.snippets[0];
        const availableLangs = Object.keys(snippet).filter(lang =>
            ['bash','java','js','python','c','cpp','csharp','php','ruby','typescript','pascal'].includes(lang)
            && snippet[lang] && snippet[lang].trim() !== ''
        );

        // Génère les boutons dans le menu déroulant
        const dropdownOptions = document.getElementById('languageDropdownOptions');
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
        const label = document.getElementById('languageSelector').querySelector('span');
        if (label && availableLangs.length > 0) {
            label.textContent = availableLangs[0].toUpperCase();
            // Initialise Monaco avec ce template
            const monacoLangMap = {
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
            window.initMonaco(monacoLangMap[availableLangs[0]] || 'plaintext', templates[availableLangs[0]] || '');
        }

        // Ajoute les listeners sur les nouveaux boutons
        const options = document.querySelectorAll('#languageDropdownOptions button[data-language]');
        options.forEach(option => {
            option.addEventListener('click', function () {
                const langKey = this.getAttribute('data-language');
                const monacoLangMap = {
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
                const monacoLang = monacoLangMap[langKey] || 'plaintext';
                const template = templates[langKey] || templates['javascript'] || '// Pas de template pour ce langage';
                label.textContent = langKey.toUpperCase();
                window.initMonaco(monacoLang, template);
                document.getElementById('languageDropdown').classList.add('hidden');
            });
        });
    }

    document.getElementById('runCode').addEventListener('click', async () => {
        const code = editor.getValue();
        const langKey = document.getElementById('languageSelector').querySelector('span').textContent.trim().toLowerCase();

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

    if (challenge && challenge.data) {
        // Titre
        const challengeTitle = document.getElementById('challenge-title');
        if (challengeTitle) challengeTitle.textContent = challenge.data.title || '';

        // Difficulté
        const challengeDifficulty = document.getElementById('challenge-difficulty');
        if (challengeDifficulty) {
            let diff = challenge.data.difficulty || '';
            if (diff === 'easy') diff = 'facile';
            else if (diff === 'medium') diff = 'moyen';
            else if (diff === 'hard') diff = 'difficile';
            challengeDifficulty.textContent = diff;
        }

        // Description (objectif)
        const challengeDescription = document.getElementById('challenge-description');
        if (challengeDescription) challengeDescription.textContent = challenge.data.description || '';

        // Instructions (règles)
        const challengeInstructions = document.getElementById('challenge-instructions');
        if (challengeInstructions) {
            // Si instructions contient des sauts de ligne ou des points, on split
            let rules = challenge.data.instructions || '';
            let items = [];
            if (rules.includes('\n')) {
                items = rules.split(/\n+/).map(s => s.trim()).filter(Boolean);
            } else if (rules.includes('. ')) {
                items = rules.split(/\. /).map(s => s.trim()).filter(Boolean);
            } else if (rules.includes(';')) {
                items = rules.split(';').map(s => s.trim()).filter(Boolean);
            } else {
                items = [rules];
            }
            challengeInstructions.innerHTML = items.map(rule => `<li>${rule}</li>`).join('');
        }
    }

    // --- Ajout du toggle console ici ---
    const toggleBtn = document.getElementById('toggleConsole');
    const toggleIcon = document.getElementById('toggleConsoleIcon');
    const consoleOutput = document.getElementById('consoleOutput');
    let isCollapsed = false;

    if (!toggleBtn) console.warn('toggleConsole button not found');
    if (!toggleIcon) console.warn('toggleConsoleIcon not found');
    if (!consoleOutput) console.warn('consoleOutput not found');

    if (toggleBtn && toggleIcon && consoleOutput) {
        toggleBtn.addEventListener('click', function() {
            isCollapsed = !isCollapsed;
            if (isCollapsed) {
                consoleOutput.style.display = 'none';
                toggleIcon.classList.remove('ri-subtract-line');
                toggleIcon.classList.add('ri-add-line');
            } else {
                consoleOutput.style.display = '';
                toggleIcon.classList.remove('ri-add-line');
                toggleIcon.classList.add('ri-subtract-line');
            }
        });
    }
});