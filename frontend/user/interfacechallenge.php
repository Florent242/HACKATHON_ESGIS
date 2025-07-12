<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - IDE de Programmation</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="/css/styles/user/hackaton.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    
    <!-- Fonts et Icônes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    
    <!-- Scripts Externes -->
    <script src="https://cdn.tailwindcss.com/3.4.16" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.js" defer></script>
    
    <style>
        :where([class^="ri-"])::before { content: ""; }
        
        /* Styles Responsives */
        @media (max-width: 640px) {
            .editor-container { height: 300px; }
            #consoleOutput { height: 150px; }
        }
        @media (min-width: 641px) and (max-width: 1024px) {
            .editor-container { height: 400px; }
            #consoleOutput { height: 180px; }
        }
        
        /* Plein écran */
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
        
        /* Console */
        #consoleOutput {
            height: 200px;
            overflow-y: auto;
            font-family: 'Fira Code', monospace;
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
        
        /* Éditeur */
        .editor-container {
            height: 500px;
            width: 100%;
        }
        .fullscreen .editor-container {
            height: calc(100vh - 100px);
        }
        
        /* Variables CSS */
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
        
        .highlighted {
            background-color: var(--yellow);
            color: var(--background);
            padding: 0 4px;
            border-radius: 2px;
        }
        .comment { color: var(--green); }
        .keyword { color: #C678DD; }
        .string { color: #E5C07B; }
        .danger-indicator {
            background-color: rgba(239, 68, 68, 0.2);
            border-left: 4px solid var(--red);
        }

        /* Pour la div des instructions */
        #objectif-regles, .objectif-regles {
            min-height: 320px;
            height: 350px; /* ou la taille souhaitée */
            max-height: 400px;
            overflow-y: auto;
        }

        /* Pour l'éditeur */
        .editor-container {
            height: 260px !important; /* ou h-64 en Tailwind */
            min-height: 200px;
            max-height: 300px;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <?php require_once '../includes/user/header.php'; ?>
    
    <?php
    $challengeTemplates = [];

    // Templates de code par défaut
    // $challengeTemplates = [
    //     "bash" => "#!/bin/bash\n# Votre code Bash ici\necho \"Hello World\"",
    //     "python" => "# Votre code Python ici\nprint(\"Hello World\")",
    //     "java" => "// Votre code Java ici\npublic class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Hello World\");\n    }\n}",
    //     "javascript" => "// Votre code JavaScript ici\nconsole.log(\"Hello World\");",
    //     "c" => "// Votre code C ici\n#include <stdio.h>\n\nint main() {\n    printf(\"Hello World\");\n    return 0;\n}",
    //     "cpp" => "// Votre code C++ ici\n#include <iostream>\n\nint main() {\n    std::cout << \"Hello World\";\n    return 0;\n}",
    //     "php" => "<?php\n// Votre code PHP ici\necho \"Hello World\";",
    //     "ruby" => "# Votre code Ruby ici\nputs \"Hello World\"",
    //     "typescript" => "// Votre code TypeScript ici\nconsole.log(\"Hello World\");",
    //     "pascal" => "// Votre code Pascal ici\nprogram HelloWorld;\nbegin\n  writeln('Hello World');\nend.",
    //     "golang" => "// Votre code Go ici\npackage main\n\nimport \"fmt\"\n\nfunc main() {\n    fmt.Println(\"Hello World\")\n}"
    // ];
    ?>
    <script>
        window.challengeTemplates = <?php echo json_encode($challengeTemplates, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>

    <div class="main-grid grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Colonne gauche -->
        <div class="flex flex-col gap-4">
            <!-- Objectif & Règles -->
            <div id="objectif-regles" class="bg-gradient-to-br from-[#030B20] to-[#030F2A] rounded p-4 border border-[#1E293B] min-h-[320px] h-[350px] max-h-[400px] overflow-y-auto">
                <!-- Nom du challenge -->
                <h1 id="challenge-title" class="text-2xl font-bold text-[#3B82F6] mb-4"></h1>
                <div class="space-y-4">
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-[#3B82F6] flex items-center justify-center mr-2">
                            <i class="ri-target-line text-black"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-white">Objectif</h2>
                    </div>
                    <div id="challenge-description" class="mt-2 text-white text-sm"></div>
                    <div class="flex items-center">
                        <div class="w-6 h-6 rounded-full bg-[#22C55E] flex items-center justify-center mr-2">
                            <i class="ri-check-line text-black"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-white">Règles</h2>
                    </div>
                    <div id="challenge-instructions" class="text-white space-y-4"></div>
                </div>
            </div>
            <!-- Console de Sortie -->
            <div id="consoleSection" class="bg-[#030B20] rounded p-4 border border-[#1E293B] relative mt-4 lg:col-span-2 order-last lg:order-none">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <h2 class="text-sm font-medium text-white">Sortie console</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        
                    </div>
                </div>
                <div id="consoleOutput" class="font-mono text-sm space-y-4 bg-[#030B20] rounded p-2 min-h-[150px]">
                    <div class="text-[#94A3B8]">Prêt à exécuter du code...</div>
                </div>
                
            </div>
        </div>
        <!-- Colonne droite -->
        <div class="flex flex-col gap-4">
            <!-- Éditeur de Code -->
            <div class="bg-[#030B20] rounded p-4 border border-[#1E293B]">
                <div class="flex justify-between items-center mb-4">
                    <div class="relative">
                        <button id="languageSelector" class="bg-[#1E293B] rounded px-3 py-1.5 flex items-center min-w-[120px] hover:bg-[#2D3B4E] transition-colors">
                            <span class="text-[#3B82F6] text-sm">Bash</span>
                            <i class="ri-arrow-down-s-line ml-2"></i>
                        </button>
                        <div id="languageDropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-[#1E293B] rounded shadow-lg border border-[#2D3B4E] z-10">
                            <div class="py-1" id="languageDropdownOptions">
                                <!-- Les boutons seront injectés en JS -->
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button id="runCode" class="w-8 h-8 rounded flex items-center justify-center border border-[#22C55E] bg-[#22C55E] text-white hover:bg-[#1EA34A] transition-colors">
                            <i class="ri-play-fill"></i>
                        </button>
                        <button id="resetCode" class="w-8 h-8 rounded flex items-center justify-center border border-[#1E293B] hover:bg-[#2D3B4E] transition-colors">
                            <i class="ri-refresh-line"></i>
                        </button>
                        
                    </div>
                </div>
                <div id="monaco-editor" class="editor-container h-[260px] min-h-[200px] max-h-[300px]"></div>
            </div>
            <div class="flex gap-4">
  <!-- Jeu de tests -->
  <div class="flex-1 bg-[#232e39] rounded border border-[#2d3b4e] p-4 min-w-[300px]">
    <h2 class="text-base font-semibold text-[#94a3b8] mb-4">Jeu de tests</h2>
    <div class="bg-[#26323e] rounded flex items-center p-2 mb-2">
      <span class="bg-[#3b4b5c] text-[#22c55e] font-bold rounded px-2 py-1 mr-3">01</span>
      <span class="text-[#22c55e] flex-1">Danger imminent</span>
      <button class="ml-auto bg-[#26323e] text-[#22c55e] border border-[#22c55e] rounded px-3 py-1 text-xs flex items-center hover:bg-[#1e293b] transition-colors">
        <i class="ri-play-fill mr-1"></i>LANCER LE TEST
      </button>
    </div>
  </div>
  <!-- Actions -->
  <div class="w-64 bg-[#232e39] rounded border border-[#2d3b4e] p-4 flex flex-col gap-4">
    <h2 class="text-base font-semibold text-[#94a3b8] mb-4">Actions</h2>
    <button class="w-full bg-[#2d3b4e] text-[#3b82f6] px-4 py-3 rounded whitespace-nowrap flex items-center justify-center font-medium mb-2">
      <i class="ri-play-fill mr-2"></i>TOUS LES TESTS
    </button>
    <button class="w-full bg-[#3b4b5c] text-[#eab308] px-4 py-3 rounded whitespace-nowrap flex items-center justify-center font-medium">
      <i class="ri-check-line mr-2 text-[#eab308]"></i>SOUMETTRE
    </button>
  </div>
</div>
        </div>
    </div>

    <!-- Script Principal -->
    <script src="/js/user/interfacechallenge.js" defer></script>
</body>
</html>