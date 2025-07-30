<?php

namespace Piston;

/**
 * Classe pour représenter une requête vers l'API Piston
 */
class PistonRequest
{
    private $language;
    private $version;
    private $files;
    private $stdin;
    private $args;
    private $compile_timeout;
    private $run_timeout;
    private $compile_memory_limit;
    private $run_memory_limit;

    // Mapping des langages pour Piston
    private static $languageMap = [
        'python' => ['language' => 'python', 'version' => '3.10.0'],
        'javascript' => ['language' => 'javascript', 'version' => '18.15.0'],
        'java' => ['language' => 'java', 'version' => '15.0.2'],
        'cpp' => ['language' => 'cpp', 'version' => '10.2.0'],
        'c' => ['language' => 'c', 'version' => '10.2.0'],
        'csharp' => ['language' => 'csharp', 'version' => '6.12.0'],
        'php' => ['language' => 'php', 'version' => '8.2.3'],
        'ruby' => ['language' => 'ruby', 'version' => '3.0.1'],
        'go' => ['language' => 'go', 'version' => '1.16.2'],
        'bash' => ['language' => 'bash', 'version' => '5.2.0']
    ];

    public function __construct($language, $sourceCode, $stdin = '', $args = [])
    {
        // Normaliser le langage
        $language = strtolower(trim($language));
        
        // Mapper le langage si nécessaire
        if (!isset(self::$languageMap[$language])) {
            throw new \InvalidArgumentException("Langage non supporté: $language");
        }

        $langConfig = self::$languageMap[$language];
        $this->language = $langConfig['language'];
        $this->version = $langConfig['version'];
        
        // Configurer le fichier source
        $this->files = [
            [
                'name' => $this->getFileName($language),
                'content' => $sourceCode
            ]
        ];
        
        // Traiter les nouvelles lignes échappées dans stdin
        $stdin = str_replace('\\n', "\n", $stdin);
        $this->stdin = $stdin;
        $this->args = $args;
        
        // Limites par défaut pour les défis algorithmiques
        $this->compile_timeout = 10000; // 10 secondes
        $this->run_timeout = 5000;      // 5 secondes
        $this->compile_memory_limit = 128000000; // 128MB
        $this->run_memory_limit = 128000000;     // 128MB
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
    public function setLimits($compileTimeout = null, $runTimeout = null, $compileMemory = null, $runMemory = null)
    {
        if ($compileTimeout !== null) $this->compile_timeout = $compileTimeout;
        if ($runTimeout !== null) $this->run_timeout = $runTimeout;
        if ($compileMemory !== null) $this->compile_memory_limit = $compileMemory;
        if ($runMemory !== null) $this->run_memory_limit = $runMemory;
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
            'language' => $this->language,
            'version' => $this->version,
            'files' => $this->files,
            'stdin' => $this->stdin,
            'args' => $this->args,
            'compile_timeout' => $this->compile_timeout,
            'run_timeout' => $this->run_timeout,
            'compile_memory_limit' => $this->compile_memory_limit,
            'run_memory_limit' => $this->run_memory_limit
        ];
    }

    /**
     * Valide la sécurité du code
     */
    public function validateSecurity()
    {
        $sourceCode = $this->files[0]['content'];
        
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
                '/#include\s*<stdio.h>/i', // Autoriser stdio pour printf/scanf
                '/#include\s*<stdlib.h>/i',
                '/system\s*\(/i',
                '/exec/i',
                '/popen/i'
            ]
        ];

        $patterns = $dangerousPatterns[$this->language] ?? [];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sourceCode)) {
                throw new \SecurityException("Code potentiellement dangereux détecté: pattern $pattern");
            }
        }

        return true;
    }

    // Getters
    public function getLanguage() { return $this->language; }
    public function getVersion() { return $this->version; }
    public function getSourceCode() { return $this->files[0]['content']; }
    public function getStdin() { return $this->stdin; }
}

/**
 * Exception pour les problèmes de sécurité
 */
class SecurityException extends \Exception {}
