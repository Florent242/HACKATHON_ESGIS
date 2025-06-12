<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Métadonnées et liens CSS principaux -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    <link rel="stylesheet" href="/css/styles/user/hackaton.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <!-- Dépendances externes : Tailwind, Monaco Editor, Remixicon, Google Fonts -->
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        secondary: '#2563EB'
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        :where([class^="ri-"])::before { content: ""; } /* icons cachés */
        @media (max-width: 640px) {
            .editor-container { height: 300px; }
            #consoleOutput { height: 150px; }
        }
        @media (min-width: 641px) and (max-width: 1024px) {
            .editor-container { height: 400px; }
            #consoleOutput { height: 180px; }
        }
        .fullscreen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1000;
            background: var(--background);
            padding: 20px;
            box-sizing: border-box;
        }
        .fullscreen #consoleOutput {
            height: calc(100vh - 100px);
            overflow-y: auto;
        }
        #consoleOutput {
            height: 200px;
            overflow-y: auto;
        }
        #consoleOutput::-webkit-scrollbar {
            width: 8px;
        }
        #consoleOutput::-webkit-scrollbar-track {
            background: transparent;
        }
        #consoleOutput::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 4px;
        }
        .editor-container {
            height: 500px;
            width: 100%;
        }
        .fullscreen .editor-container {
            height: calc(100vh - 100px);
        }
        :root {
            --background: #030B20;
            --card-bg: linear-gradient(135deg, #030B20 0%, #030F2A 100%);
            --card-hover: #1E293B;
            --border: #1E293B;
            --text: #FFFFFF;
            --text-secondary: #94A3B8;
            --blue: #3B82F6;
            --blue-opac: #2564eb25;
            --primary: #3B82F6;
            --primary-hover: #2563EB;
            --green: #22C55E;
            --yellow: #EAB308;
            --red: #EF4444;
        }
        body {
            background-color: var(--background);
            color: var(--text);
            font-family: 'Inter', sans-serif;
        }
        .code-editor {
            font-family: 'Fira Code', monospace;
        }
        .highlighted {
            background-color: var(--yellow);
            color: var(--background);
            padding: 0 4px;
            border-radius: 2px;
        }
        .comment { color: var(--green); }
        .keyword { color: #C678DD; }
        .string { color: #E5C07B; }
        .line-numbers {
            color: var(--text-secondary);
            text-align: right;
            padding-right: 12px;
            user-select: none;
        }
        .danger-indicator {
            background-color: rgba(239, 68, 68, 0.2);
            border-left: 4px solid var(--red);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Inclusion du header utilisateur -->
    <?php require_once '../includes/user/header.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-2 sm:p-4 min-h-screen max-w-[1920px] mx-auto">
        <!-- SECTION : Règles et Objectif du challenge -->
        <div class="bg-gradient-to-br from-[#030B20] to-[#030F2A] rounded p-4 border border-[#1E293B]">
            <!-- Objectif et règles expliqués à l’utilisateur -->
            <div class="space-y-4">
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full bg-[#3B82F6] flex items-center justify-center mr-2">
                        <i class="ri-target-line text-black"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white">Objectif</h2>
                </div>
                <!-- Texte ajouté après Objectif -->
                <div class="mt-2 text-white text-sm">
                    Dans ce challenge, votre mission est de défendre votre base contre des vagues de vaisseaux ennemis. 
                    Analysez les informations fournies à chaque tour pour prendre la meilleure décision et neutraliser la menace la plus proche. 
                    Utilisez les variables à disposition pour identifier et cibler l’ennemi prioritaire.
                </div>
                <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full bg-[#22C55E] flex items-center justify-center mr-2">
                        <i class="ri-check-line text-black"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-white">Règles</h2>
                </div>
            </div>
            <div class="text-white space-y-4">
                <p>Les vaisseaux ennemis approchent en ligne droite vers votre canon.</p>
                <p>
                    À chaque début d'un tour de jeu (dans la boucle <span class="italic">game loop</span>), vous obtenez les
                    informations des deux ennemis les plus proches :
                </p>
                <ul class="list-disc pl-6 space-y-2">
                    <li>variable <span class="highlighted">enemy1</span> : le nom de l'ennemi 1.</li>
                    <li>variable <span class="highlighted">dist1</span> : la distance à laquelle se trouve l'ennemi 1.</li>
                    <li>variable <span class="highlighted">enemy2</span> : le nom de l'ennemi 2.</li>
                    <li>variable <span class="highlighted">dist2</span> : la distance à laquelle se trouve l'ennemi 2.</li>
                </ul>
                <p>
                    Avant la fin du tour (fin de la boucle), vous devez indiquer en sortie le nom de l'ennemi
                    le plus proche. Pour afficher le nom de l'ennemi le plus proche, vous devez utiliser la
                    variable <span class="highlighted">enemy1</span> ou <span class="highlighted">enemy2</span>.
                </p>
            </div>
        </div>
        <!-- SECTION : Éditeur de code interactif -->
        <div class="bg-[#030B20] rounded p-4 border border-[#1E293B]">
            <!-- Barre d’actions : sélection du langage, boutons run/reset/fullscreen -->
            <div class="flex justify-between items-center mb-4">
                <div class="relative">
                    <button id="languageSelector" class="bg-[#1E293B] rounded px-3 py-1.5 flex items-center min-w-[120px] hover:bg-[#2D3B4E] transition-colors">
                        <span class="text-[#3B82F6] text-sm">Bash</span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </button>
                    <div id="languageDropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-[#1E293B] rounded shadow-lg border border-[#2D3B4E] z-10">
                        <div class="py-1">
                            <button class="w-full px-4 py-2 text-sm text-white hover:bg-[#2D3B4E] flex items-center" data-language="javascript">
                                <i class="ri-javascript-line mr-2"></i>JavaScript
                            </button>
                            <button class="w-full px-4 py-2 text-sm text-white hover:bg-[#2D3B4E] flex items-center" data-language="python">
                                <i class="ri-python-line mr-2"></i>Python
                            </button>
                            <button class="w-full px-4 py-2 text-sm text-white hover:bg-[#2D3B4E] flex items-center" data-language="java">
                                <i class="ri-code-line mr-2"></i>Java
                            </button>
                            <button class="w-full px-4 py-2 text-sm text-white hover:bg-[#2D3B4E] flex items-center" data-language="cpp">
                                <i class="ri-code-line mr-2"></i>C++
                            </button>
                            <button class="w-full px-4 py-2 text-sm text-white hover:bg-[#2D3B4E] flex items-center" data-language="bash">
                                <i class="ri-terminal-line mr-2"></i>Bash
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <button id="runCode" class="w-8 h-8 rounded flex items-center justify-center border border-[#22C55E] bg-[#22C55E] text-white hover:bg-[#1EA34A] transition-colors">
                        <i class="ri-play-fill"></i>
                    </button>
                    <button class="w-8 h-8 rounded flex items-center justify-center border border-[#1E293B] hover:bg-[#2D3B4E] transition-colors">
                        <i class="ri-refresh-line"></i>
                    </button>
                    <button id="editorFullscreenBtn" class="w-8 h-8 rounded flex items-center justify-center border border-[#1E293B] hover:bg-[#2D3B4E] transition-colors">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                </div>
            </div>
            <div id="monaco-editor" class="editor-container"></div>
        </div>
        <!-- SECTION : Console de sortie et tests -->
        <div id="consoleSection" class="bg-[#030B20] rounded p-4 border border-[#1E293B] relative col-span-2 mt-4">
            <!-- En-tête console avec boutons minimiser/fullscreen -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-sm font-medium text-white">Sortie console</h2>
                    <div class="flex items-center bg-[#1E293B] rounded px-3 py-1 cursor-pointer">
                        <span class="text-sm text-white">Informations de jeu, entrées, sorties</span>
                        <i class="ri-arrow-down-s-line ml-2"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="consoleMinimizeBtn" class="w-8 h-8 flex items-center justify-center text-white hover:bg-[#1E293B] rounded transition-colors">
                        <i class="ri-subtract-line"></i>
                    </button>
                    <button id="consoleFullscreenBtn" class="w-8 h-8 flex items-center justify-center text-white hover:bg-[#1E293B] rounded transition-colors">
                        <i class="ri-fullscreen-line"></i>
                    </button>
                </div>
            </div>
            <div id="consoleOutput" class="font-mono text-sm space-y-4 bg-[#030B20] rounded">
                <!-- Affichage des sorties console et messages d’erreur -->
                <div class="border-b border-[#1E293B] pb-4">
                    <div class="text-[#94A3B8]">Informations :</div>
                    <div class="text-[#E5E7EB]">15 threats approaching fast !</div>
                    <div class="text-[#94A3B8]">Threats within range:</div>
                    <div class="text-[#E5E7EB]">Rock 70m</div>
                </div>
                <div>
                    <div class="text-[#94A3B8]">Informations :</div>
                    <div class="text-[#EF4444]">Timeout: your program did not provide an input in due time.</div>
                    <div class="text-[#94A3B8]">Threats within range:</div>
                    <div class="text-[#E5E7EB]">Rock 70m</div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
                <!-- Bloc : Jeu de tests (feedback sur les tests) -->
                <div class="bg-[#030B20] rounded p-4 border border-[#1E293B]">
                    <h2 class="text-lg font-semibold text-white mb-4">Jeu de tests</h2>
                    <div class="danger-indicator rounded p-3 flex items-center">
                        <div class="bg-[#EF4444] text-white rounded-full w-8 h-8 flex items-center justify-center mr-3">
                            01
                        </div>
                        <div class="text-[#EF4444] font-medium flex-1">
                            Danger imminent
                        </div>
                        <button class="bg-[#1E293B] text-[#3B82F6] px-4 py-2 rounded !rounded-button whitespace-nowrap flex items-center">
                            <i class="ri-play-fill mr-2"></i>
                            RÉESSAYER
                        </button>
                    </div>
                </div>
                <!-- Bloc : Actions (lancer tous les tests, soumettre) -->
                <div class="bg-[#030B20] rounded p-4 border border-[#1E293B]">
                    <h2 class="text-lg font-semibold text-white mb-4">Actions</h2>
                    <div class="space-y-4">
                        <button class="w-full bg-[#1E293B] text-[#3B82F6] px-4 py-3 rounded !rounded-button whitespace-nowrap flex items-center justify-center">
                            <i class="ri-play-fill mr-2"></i>
                            TOUS LES TESTS
                        </button>
                        <button class="w-full bg-[#EAB308] text-[#030B20] px-4 py-3 rounded !rounded-button whitespace-nowrap flex items-center justify-center font-medium">
                            <i class="ri-check-line mr-2"></i>
                            SOUMETTRE
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- SCRIPT PRINCIPAL : gestion de l’éditeur, des boutons, du responsive -->
    <script id="codeEditorScript">
        document.addEventListener('DOMContentLoaded', function() {
            // --- Gestion du sélecteur de langage ---
            const languageSelector = document.getElementById('languageSelector');
            const languageDropdown = document.getElementById('languageDropdown');
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
                    if (editor) {
                        monaco.editor.setModelLanguage(editor.getModel(), selectedLang);
                    }
                }
            });
            // --- Gestion du bouton minimiser la console ---
            const consoleMinimizeBtn = document.getElementById('consoleMinimizeBtn');
            const consoleOutput = document.getElementById('consoleOutput');
            let isConsoleMinimized = false;
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
            // --- Gestion du bouton run (exécution du code) ---
            const runCodeBtn = document.getElementById('runCode');
            runCodeBtn.addEventListener('click', function() {
                const code = editor.getValue();
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
            // --- Gestion du fullscreen console ---
            const consoleSection = document.getElementById('consoleSection');
            const consoleFullscreenBtn = document.getElementById('consoleFullscreenBtn');
            consoleFullscreenBtn.addEventListener('click', function() {
                consoleSection.classList.toggle('fullscreen');
                if (consoleSection.classList.contains('fullscreen')) {
                    this.querySelector('i').classList.replace('ri-fullscreen-line', 'ri-fullscreen-exit-line');
                } else {
                    this.querySelector('i').classList.replace('ri-fullscreen-exit-line', 'ri-fullscreen-line');
                }
            });
            // --- Initialisation de Monaco Editor ---
            require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' }});
            require(['vs/editor/editor.main'], function() {
                // Création de l’éditeur Monaco
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
                // Gestion du fullscreen éditeur
                const editorFullscreenBtn = document.getElementById('editorFullscreenBtn');
                const editorContainer = document.getElementById('monaco-editor').parentElement;
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
            });
        });
    </script>
</body>
</html>