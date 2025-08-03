<?php

namespace Judge0;

/**
 * Classe pour représenter une réponse de l'API Judge0
 */
class Judge0Response
{
    private $stdout;
    private $time;
    private $memory;
    private $stderr;
    private $token;
    private $compile_output;
    private $message;
    private $status;
    private $allData;
    private $success;
    private $executionTime;
    private $memoryUsed;
    private $other;

    public function __construct($data)
    {
        $this->stdout = $data['stdout'] ?? '';
        $this->time = $data['time'] ?? '0.000';
        $this->memory = $data['memory'] ?? 0;
        $this->stderr = $data['stderr'] ?? null;
        $this->token = $data['token'] ?? '';
        $this->compile_output = $data['compile_output'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->status = $data['status'] ?? ['id' => 0, 'description' => 'Unknown'];
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
        if ($this->compile_output && $this->compile_output !== '') {
            return false;
        }

        // Échec d'exécution
        if ($this->stderr && $this->stderr !== '') {
            return false;
        }

        return true;
    }

    /**
     * Extrait les métriques de performance
     */
    private function extractMetrics()
    {
        $this->memoryUsed = $this->memory;

        if ($this->time) {
            // Convertir en millisecondes
            $this->executionTime = round(floatval($this->time) * 1000);
        }
    }

    /**
     * Récupère la sortie du programme
     */
    public function getOutput()
    {
        return trim($this->stdout ?? '');
    }

    /**
     * Récupère les erreurs
     */
    public function getError()
    {
        $errors = [];

        // Erreurs de compilation
        if ($this->compile_output && !empty($this->compile_output)) {
            $errors[] = "Erreur de compilation:\n" . $this->compile_output;
        }

        // Erreurs d'exécution
        if ($this->stderr && !empty($this->stderr)) {
            $errors[] = "Erreur d'exécution:\n" . $this->stderr;
        }

        return implode("\n\n", $errors);
    }

    /**
     * Récupère le code de sortie
     */
    public function getExitCode()
    {
        if ($this->status && $this->status['id'] !== 0) {
            return $this->status['id'];
        }

        return 0;
    }

    /**
     * Vérifie si c'est un timeout
     */
    public function isTimeout()
    {
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
            'stdout' => $this->stdout,
            'time' => $this->time,
            'memory' => $this->memory,
            'stderr' => $this->stderr,
            'token' => $this->token,
            'compile_output' => $this->compile_output,
            'message' => $this->message,
            'status' => $this->status,
            'success' => $this->success,
            'output' => $this->getOutput(),
            'error' => $this->getError(),
            'exit_code' => $this->getExitCode(),
            'execution_time_ms' => $this->executionTime,
            'memory_used_bytes' => $this->memoryUsed,
            'is_timeout' => $this->isTimeout(),
            'is_memory_limit' => $this->isMemoryLimit(),
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
    public function isSuccess()
    {
        return $this->success;
    }
    public function getExecutionTime()
    {
        return $this->executionTime;
    }
    public function getMemoryUsed()
    {
        return $this->memoryUsed;
    }
}
