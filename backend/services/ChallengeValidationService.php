<?php

namespace Auth\Service;

use Exception;
use Piston\PistonExecutor;
use Piston\PistonRequest;

require_once __DIR__ . '/../Piston/PistonExecutor.php';

/**
 * Service de validation des défis algorithmiques
 * Ce service orchestre l'exécution du code et la validation contre les cas de test
 */
class ChallengeValidationService
{
    private $db;
    private $challenge;
    private $pistonExecutor;

    public function __construct($db, $challenge)
    {
        $this->db = $db;
        $this->challenge = $challenge;
        $this->pistonExecutor = new PistonExecutor();
    }

    /**
     * Valide une soumission de code contre tous les cas de test
     * @param int $submissionId
     * @return array Résultats de l'évaluation
     */
    public function validateSubmission($submissionId)
    {
        try {
            // Récupérer les détails de la soumission
            $submission = $this->getSubmissionDetails($submissionId);
            if (!$submission) {
                throw new Exception("Soumission non trouvée");
            }

            // Récupérer le défi avec tous les cas de test (publics et privés)
            $challenge = $this->challenge->findAlgorithmic($submission['challenge_id'], $submission['user_id'], true);
            if (!$challenge) {
                throw new Exception("Défi algorithmique non trouvé");
            }

            // Marquer comme en cours d'exécution
            $this->challenge->updateSubmissionResults($submissionId, 'running');

            // Valider la sécurité du code
            if (!$this->validateCodeSecurity($submission['code'], $submission['language'])) {
                throw new Exception("Code non conforme aux règles de sécurité");
            }

            // Exécuter contre tous les cas de test
            $results = $this->pistonExecutor->executeAllTestCases(
                $submission['language'],
                $submission['code'],
                $challenge['test_cases'],
                $challenge['points']
            );
 
            // Sauvegarder les résultats détaillés
            $this->saveTestCaseResults($submissionId, $results['results']);

            // Calculer le score basé sur les points des tests réussis
            $scoreData = $this->challenge->calculateSubmissionScore($submissionId);

            // Après avoir obtenu $scoreData
            if (!$scoreData['is_successful']) {
                $testType = $scoreData['public_success_rate'] < 80 ? 'publics' : 
                         ($scoreData['success_rate'] < 60 ? 'globaux' : '');
                $errorMessage = "Nombre insuffisant de tests $testType réussis pour valider le challenge";
                
                $this->challenge->updateSubmissionResults(
                    $submissionId, 
                    'rejected', 
                    $scoreData['total_score'],
                    null,
                    null,
                    $scoreData['passed_tests'],
                    $scoreData['total_tests'],
                    $errorMessage
                );
                
                return [
                    'success' => false,
                    'error' => $errorMessage
                ];
            }

            // Mettre à jour la soumission avec le score calculé
            $this->challenge->updateSubmissionResults(
                $submissionId,
                'completed',
                $scoreData['total_score'],
                $scoreData['total_execution_time_ms'],
                0, // Pas de données de mémoire pour l'instant
                $scoreData['passed_tests'],
                $scoreData['total_tests'],
                null
            );

            return [
                'success' => true,
                'score' => $scoreData['total_score'],
                'max_score' => $scoreData['max_possible_score'],
                'execution_time_ms' => $scoreData['total_execution_time_ms'],
                'memory_used_mb' => 0, // Pas de données de mémoire pour l'instant
                'passed_tests' => $scoreData['passed_tests'],
                'total_tests' => $scoreData['total_tests'],
                'status' => 'completed',
                'results' => $results['results']
            ];
        } catch (Exception $e) {
            // Marquer comme échoué en cas d'erreur
            $this->challenge->updateSubmissionResults(
                $submissionId,
                'error',
                0,
                null,
                null,
                0,
                0,
                $e->getMessage()
            );

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 'error'
            ];
        }
    }

    /**
     * Valide le code et retourne des résultats préliminaires (tests publics seulement)
     * @param int $challengeId
     * @param string $code
     * @param string $language
     * @return array
     */
    public function validateCode($challengeId, $code, $language, $userId)
    {
        try {
            // Récupérer le défi avec les cas de test publics seulement
            $challenge = $this->challenge->findAlgorithmic($challengeId, $userId, false);
            if (!$challenge) {
                throw new Exception("Défi algorithmique non trouvé");
            }

            // Valider la sécurité
            if (!$this->validateCodeSecurity($code, $language)) {
                throw new Exception("Code non conforme aux règles de sécurité");
            }

            // Exécuter contre les cas de test publics
            $publicTests = array_filter($challenge['test_cases'], function ($tc) {
                return $tc['is_public'] == 1;
            });

            if (empty($publicTests)) {
                throw new Exception("Aucun cas de test public disponible");
            }

            $results = $this->pistonExecutor->executeAllTestCases($language, $code, $publicTests, $challenge['points']);

            return [
                'success' => true,
                'results' => $results['results'],
                'summary' => $results['summary']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Exécute le code contre un cas de test spécifique
     * @param string $code
     * @param string $language
     * @param array $testCase
     * @return array
     */
    public function executeTestCase($code, $language, $testCase)
    {
        try {
            return $this->pistonExecutor->executeTestCase(
                $language,
                $code,
                $testCase['input_data'],
                $testCase['expected_output']
            );
        } catch (Exception $e) {
            return [
                'passed' => false,
                'actual_output' => '',
                'expected_output' => $testCase['expected_output'],
                'execution_time_ms' => 0,
                'memory_used_bytes' => 0,
                'error' => $e->getMessage(),
                'is_timeout' => false,
                'is_memory_limit' => false
            ];
        }
    }

    /**
     * Valide la sécurité du code soumis
     * @param string $code
     * @param string $language
     * @return bool
     */
    public function validateCodeSecurity($code, $language)
    {
        try {
            $request = new PistonRequest($language, $code);
            $request->validateSecurity();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère les détails d'une soumission
     * @param int $submissionId
     * @return array|null
     */
    private function getSubmissionDetails($submissionId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM challenge_submissions 
                WHERE id = :submission_id
            ");
            $stmt->execute([':submission_id' => $submissionId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            throw new Exception("Erreur lors de la récupération de la soumission: " . $e->getMessage());
        }
    }

    /**
     * Sauvegarde les résultats détaillés des cas de test
     * @param int $submissionId
     * @param array $results
     */
    private function saveTestCaseResults($submissionId, $results)
    {
        try {
            foreach ($results as $result) {
                $status = $result['passed'] ? 'passed' : 'failed';

                if ($result['is_timeout']) {
                    $status = 'timeout';
                } elseif ($result['is_memory_limit']) {
                    $status = 'error';
                }

                $this->challenge->saveTestCaseResult(
                    $submissionId,
                    $result['test_case_id'],
                    $status,
                    $result['actual_output'],
                    $result['execution_time_ms'],
                    $result['memory_used_bytes'],
                    $result['error'] ?? null
                );
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la sauvegarde des résultats: " . $e->getMessage());
        }
    }

    /**
     * Teste la connectivité avec l'API Piston
     * @return bool
     */
    public function testPistonConnection()
    {
        return $this->pistonExecutor->testConnection();
    }
}
