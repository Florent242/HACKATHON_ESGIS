<?php

namespace Judge0;

/**
 * Classe pour représenter une requête vers l'API Judge0
 */
class Judge0Request
{
    private $languageId;
    private $source_code;
    private $stdin;
    private $args;
    private $cpu_time_limit;
    private $real_time_limit;
    private $memory_limit;
    private $expected_output;

    // Mapping des langages pour Judge0
    private static $languageIdMap = [
        'bash'        => 46,   // Bash (5.0.0)
        'c'           => 50,   // C (Clang 9.2.0)
        'cpp'         => 54,   // C++ (Clang 9.2.0)
        'java'        => 26,  // Java (OpenJDK 9 with Eclipse OpenJ9)
        'javascript'  => 63,  // Node.js (JavaScript 12.14.0)
        'python'      => 71,  // Python (3.8.1)
        'php'         => 68,  // PHP (7.4.1)
        'ruby'        => 72,  // Ruby (2.7.0)
        'go'          => 22,  // Go (1.9)
        'csharp'      => 51,   // C# (Mono 6.6.0.161)
        'typescript'  => 74,  // TypeScript (3.7.4)
        'rust'        => 73,  // Rust (1.40.0)
        'pascal'      => 67,  // Pascal (FPC 3.0.4)
    ];
    

    public function __construct($language, $sourceCode, $stdin = '', $args = [])
    {
        // Normaliser le langage
        $language = strtolower(trim($language));
        
        // Mapper le langage si nécessaire
        if (!isset(self::$languageIdMap[$language])) {
            throw new \InvalidArgumentException("Langage non supporté: $language");
        }

        $this->languageId = self::$languageIdMap[$language];
        
        // Configurer le fichier source
        $this->source_code = $sourceCode;
        
        // Traiter les nouvelles lignes échappées dans stdin
        $stdin = str_replace('\\n', "\n", $stdin);
        $this->stdin = $stdin;
        $this->args = $args;
        
        // Limites par défaut pour les défis algorithmiques
        $this->cpu_time_limit = 2.0;      // 5 secondes
        $this->real_time_limit = 128000; // 128MB
        $this->memory_limit = 128000;     // 128MB
    }

    /**
     * Détermine le nom de fichier selon le langage
     */
    private function getFileName($language)
    {
        $extensions = [
            'python' => 'main.py',
            'javascript' => 'main.js',
            'java' => 'Main.java',
            'cpp' => 'main.cpp',
            'c' => 'main.c',
            'csharp' => 'main.cs',
            'php' => 'main.php',
            'ruby' => 'main.rb',
            'go' => 'main.go',
            'bash' => 'main.sh'
        ];

        return $extensions[$language] ?? 'main.txt';
    }

    /**
     * Configure les limites d'exécution personnalisées
     */
    public function setLimits($runTimeout = null, $runMemory = null)
    {
        if ($runTimeout !== null) $this->cpu_time_limit = $runTimeout;
        if ($runMemory !== null) $this->memory_limit = $runMemory;
    }

    /**
     * Configure l'entrée standard (stdin)
     */
    public function setInput($input)
    {
        // Traiter les nouvelles lignes échappées
        $input = str_replace('\\n', "\n", $input);
        $this->stdin = $input;
        return $this;
    }

    /**
     * Configure les arguments de ligne de commande
     */
    public function setArgs($args)
    {
        $this->args = is_array($args) ? $args : [$args];
        return $this;
    }

    /**
     * Convertit la requête en tableau pour l'API Piston
     */
    public function toArray()
    {
        return [
            'language_id' => $this->languageId,
            'source_code' => $this->source_code,
            'stdin' => $this->stdin,
            'args' => $this->args,
            'cpu_time_limit' => $this->cpu_time_limit,
            'memory_limit' => $this->memory_limit
        ];
    }

    /**
     * Valide la sécurité du code
     */
    public function validateSecurity()
    {
        $sourceCode = $this->source_code;
        
        // Patterns dangereux par langage
        $dangerousPatterns = [
            'python' => [
                '/import\s+os/i',
                '/import\s+subprocess/i',
                '/import\s+sys/i',
                '/import\s+socket/i',
                '/import\s+requests/i',
                '/exec\s*\(/i',
                '/eval\s*\(/i',
                '/__import__/i',
                '/open\s*\(/i',
                '/file\s*\(/i'
            ],
            'javascript' => [
                '/require\s*\(/i',
                '/import\s+.*from/i',
                '/process\./i',
                '/fs\./i',
                '/child_process/i',
                '/eval\s*\(/i',
                '/Function\s*\(/i',
                '/XMLHttpRequest/i',
                '/fetch\s*\(/i'
            ],
            'java' => [
                '/import\s+java\.io/i',
                '/import\s+java\.net/i',
                '/import\s+java\.nio/i',
                '/Runtime\./i',
                '/ProcessBuilder/i',
                '/System\.exit/i',
                '/Class\.forName/i'
            ],
            'cpp' => [
                '/#include\s*<fstream>/i',
                '/#include\s*<cstdlib>/i',
                '/system\s*\(/i',
                '/exec/i',
                '/popen/i'
            ],
            'c' => [
                '/#include\s*<stdlib.h>/i', // Enlever stdio.h car il est nécessaire
                '/system\s*\(/i',
                '/exec/i',
                '/popen/i',
                '/fopen\s*\(/i',
                '/remove\s*\(/i'
            ]
        ];

        $patterns = $dangerousPatterns[$this->languageId] ?? [];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sourceCode)) {
                throw new \InvalidArgumentException("Code potentiellement dangereux détecté: pattern non autorisé");
            }
        }

        return true;
    }

    // Getters
    public function getLanguage() { return $this->languageId; }
    public function getSourceCode() { return $this->source_code; }
    public function getStdin() { return $this->stdin; }
    public function setStdin($input) { $this->stdin = $input; return $this; }
}

/**
 * Exception pour les problèmes de sécurité
 */
class SecurityException extends \Exception {}
