document.addEventListener('DOMContentLoaded', function() {
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
});