<?php

namespace Piston;

/**
 * Classe pour représenter une réponse de l'API Piston
 */
class PistonResponse
{
    private $language;
    private $version;
    private $compile;
    private $run;
    private $success;
    private $executionTime;
    private $memoryUsed;
    private $allData;
    private $other;

    public function __construct($data)
    {
        $this->language = $data['language'] ?? '';
        $this->version = $data['version'] ?? '';
        $this->compile = $data['compile'] ?? null;
        $this->run = $data['run'] ?? null;
        $this->allData = $data;
        $this->other = $data['other'] ?? null;
        
        // Déterminer le succès
        $this->success = $this->determineSuccess();
        
        // Extraire les métriques de performance
        $this->extractMetrics();
    }

    /**
     * Détermine si l'exécution a été un succès
     */
    private function determineSuccess()
    {
        // Échec de compilation
        if ($this->compile && $this->compile['code'] !== 0) {
            return false;
        }

        // Échec d'exécution
        if ($this->run && $this->run['code'] !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Extrait les métriques de performance
     */
    private function extractMetrics()
    {
        if ($this->run && isset($this->run['memory'])) {
            $this->memoryUsed = $this->run['memory'];
        }

        if ($this->run && isset($this->run['cpu_time'])) {
            // Convertir en millisecondes
            $this->executionTime = round($this->run['cpu_time'] * 1000);
        }
    }

    /**
     * Récupère la sortie du programme
     */
    public function getOutput()
    {
        if (!$this->run) {
            return '';
        }

        return trim($this->run['stdout'] ?? '');
    }

    /**
     * Récupère les erreurs
     */
    public function getError()
    {
        $errors = [];

        // Erreurs de compilation
        if ($this->compile && !empty($this->compile['stderr'])) {
            $errors[] = "Erreur de compilation:\n" . $this->compile['stderr'];
        }

        // Erreurs d'exécution
        if ($this->run && !empty($this->run['stderr'])) {
            $errors[] = "Erreur d'exécution:\n" . $this->run['stderr'];
        }

        return implode("\n\n", $errors);
    }

    /**
     * Récupère le code de sortie
     */
    public function getExitCode()
    {
        if ($this->compile && $this->compile['code'] !== 0) {
            return $this->compile['code'];
        }

        if ($this->run) {
            return $this->run['code'];
        }

        return 0;
    }

    /**
     * Vérifie si c'est un timeout
     */
    public function isTimeout()
    {
        // Vérifier le signal (SIGTERM = 15, SIGKILL = 9)
        if ($this->run && isset($this->run['signal'])) {
            return in_array($this->run['signal'], [9, 15]);
        }

        // Vérifier les messages d'erreur typiques de timeout
        $error = $this->getError();
        return stripos($error, 'timeout') !== false || 
               stripos($error, 'time limit') !== false ||
               stripos($error, 'killed') !== false;
    }

    /**
     * Vérifie si c'est un dépassement de mémoire
     */
    public function isMemoryLimit()
    {
        $error = $this->getError();
        return stripos($error, 'memory') !== false ||
               stripos($error, 'out of memory') !== false ||
               stripos($error, 'segmentation fault') !== false;
    }

    /**
     * Compare la sortie avec une sortie attendue
     */
    public function compareOutput($expectedOutput, $strict = true)
    {
        $actualOutput = $this->getOutput();
        
        if ($strict) {
            return trim($actualOutput) === trim($expectedOutput);
        } else {
            // Comparaison plus flexible (ignore les espaces supplémentaires)
            $actual = preg_replace('/\s+/', ' ', trim($actualOutput));
            $expected = preg_replace('/\s+/', ' ', trim($expectedOutput));
            return $actual === $expected;
        }
    }

    /**
     * Convertit la réponse en tableau pour l'API
     */
    public function toArray()
    {
        return [
            'language' => $this->language,
            'version' => $this->version,
            'success' => $this->success,
            'output' => $this->getOutput(),
            'error' => $this->getError(),
            'exit_code' => $this->getExitCode(),
            'execution_time_ms' => $this->executionTime,
            'memory_used_bytes' => $this->memoryUsed,
            'is_timeout' => $this->isTimeout(),
            'is_memory_limit' => $this->isMemoryLimit(),
            'compile_info' => $this->compile,
            'run_info' => $this->run,
            // 'other' => $this->other
        ];
    }

    /**
     * Retourne une version simplifiée pour les tests
     */
    public function toTestResult($expectedOutput = null)
    {
        $result = [
            'success' => $this->success,
            'output' => $this->getOutput(),
            'execution_time_ms' => $this->executionTime ?? 0,
            'memory_used_bytes' => $this->memoryUsed ?? 0
        ];

        if ($expectedOutput !== null) {
            $result['expected_output'] = $expectedOutput;
            $result['output_matches'] = $this->compareOutput($expectedOutput);
        }

        if (!$this->success) {
            $result['error'] = $this->getError();
            $result['exit_code'] = $this->getExitCode();
            
            if ($this->isTimeout()) {
                $result['error_type'] = 'timeout';
            } elseif ($this->isMemoryLimit()) {
                $result['error_type'] = 'memory_limit';
            } else {
                $result['error_type'] = 'runtime_error';
            }
        }

        return $result;
    }

    // Getters
    public function isSuccess() { return $this->success; }
    public function getExecutionTime() { return $this->executionTime; }
    public function getMemoryUsed() { return $this->memoryUsed; }
    public function getLanguage() { return $this->language; }
    public function getVersion() { return $this->version; }
    public function getOther() { return $this->other; }
}
