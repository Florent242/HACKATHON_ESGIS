document.addEventListener('DOMContentLoaded', function() {
    const languageSelector = document.getElementById('languageSelector');
    const languageDropdown = document.getElementById('languageDropdown');
    const consoleMinimizeBtn = document.getElementById('consoleMinimizeBtn');
    const consoleOutput = document.getElementById('consoleOutput');
    let isConsoleMinimized = false;
    if (consoleMinimizeBtn) {
        consoleMinimizeBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (!isConsoleMinimized) {
                consoleOutput.style.height = '0px';
                icon.classList.replace('ri-subtract-line', 'ri-add-line');
            } else {
                consoleOutput.style.height = '';
                icon.classList.replace('ri-add-line', 'ri-subtract-line');
            }
            isConsoleMinimized = !isConsoleMinimized;
        });
    }
    const runCodeBtn = document.getElementById('runCode');
    const editorFullscreenBtn = document.getElementById('editorFullscreenBtn');
    const editorContainer = document.getElementById('monaco-editor')?.parentElement;
    if (languageSelector) {
        languageSelector.addEventListener('click', function(e) {
            e.stopPropagation();
            languageDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', function() {
            languageDropdown.classList.add('hidden');
        });
        languageDropdown.addEventListener('click', function(e) {
            if (e.target.closest('button[data-language]')) {
                const selectedLang = e.target.closest('button[data-language]').dataset.language;
                const langText = e.target.closest('button[data-language]').textContent.trim();
                languageSelector.querySelector('span').textContent = langText;
                if (window.editor) {
                    monaco.editor.setModelLanguage(window.editor.getModel(), selectedLang);
                }
            }
        });
    }
    if (runCodeBtn) {
        runCodeBtn.addEventListener('click', function() {
            const code = window.editor ? window.editor.getValue() : '';
            console.log('Running code:', code);
            const newOutput = document.createElement('div');
            newOutput.innerHTML = `
<div class="border-b border-[#1E293B] pb-4">
<div class="text-[#94A3B8]">Output:</div>
<div class="text-[#E5E7EB]">Running code...</div>
</div>
`;
            consoleOutput.insertBefore(newOutput, consoleOutput.firstChild);
        });
    }
    const consoleSection = document.getElementById('consoleSection');
    const consoleFullscreenBtn = document.getElementById('consoleFullscreenBtn');
    if (consoleFullscreenBtn) {
        consoleFullscreenBtn.addEventListener('click', function() {
            consoleSection.classList.toggle('fullscreen');
            if (consoleSection.classList.contains('fullscreen')) {
                this.querySelector('i').classList.replace('ri-fullscreen-line', 'ri-fullscreen-exit-line');
            } else {
                this.querySelector('i').classList.replace('ri-fullscreen-exit-line', 'ri-fullscreen-line');
            }
        });
    }
    if (typeof require !== "undefined") {
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }});
        require(['vs/editor/editor.main'], function() {
            window.editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: `# game loop
while true; do
# enemy1: name of enemy 1
read enemy1
# dist1: distance to enemy 1
read dist1
# enemy2: name of enemy 2
read enemy2
# dist2: distance to enemy 2
read dist2
# Write an action using echo
# Enter the code here
done`,
                language: 'shell',
                theme: 'vs-dark',
                automaticLayout: true
            });
            if (editorFullscreenBtn && editorContainer) {
                editorFullscreenBtn.addEventListener('click', function() {
                    editorContainer.classList.toggle('fullscreen');
                    const icon = this.querySelector('i');
                    if (editorContainer.classList.contains('fullscreen')) {
                        icon.classList.replace('ri-fullscreen-line', 'ri-fullscreen-exit-line');
                    } else {
                        icon.classList.replace('ri-fullscreen-exit-line', 'ri-fullscreen-line');
                    }
                    if (window.editor) {
                        window.editor.layout();
                    }
                });
            }
        });
    }
});