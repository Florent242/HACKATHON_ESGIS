<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Challenge;
use Auth\Model\Hackathon;
use Auth\Model\TokenManager;
use Auth\Controller\Controller;
use PDO;
use Auth\Controller\NotificationController;
use Auth\Model\Phase;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Challenge')) {
    require_once __DIR__ . '/../models/Challenge.php';
}
if (!class_exists('Hackathon')) {
    require_once __DIR__ . '/../models/Hackathon.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('Phase')) {
    require_once __DIR__ . '/../models/Phase.php';
}
if (!class_exists('NotificationController')) {
    require_once __DIR__ . '/NotificationController.php';
}


class ChallengeController extends Controller
{
    private $challenge;
    private $hackathon;
    private $db;
    protected $tokenManager;
    protected $notification;
    protected $phase;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->challenge = new Challenge($db);
        $this->hackathon = new Hackathon($db);
        $this->tokenManager = $tokenManager;
        $this->notification = new NotificationController($db, $tokenManager);
        $this->phase = new Phase($db);
    }

    public function index($hackathonId)
    {
        try {
            $this->validateMethod('GET');

            $hackathon = $this->hackathon->find($hackathonId);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            $challenges = $this->challenge->getByHackathon($hackathonId);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'hackathon' => $hackathon,
                    'challenges' => $challenges
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function submitChallengeCTF($user_id, $input, $phase_id = null)
    {
        try {
            $this->validateMethod('POST');

            if (empty($user_id) || empty($input['flag_value'])) {
                throw new Exception('user_id et flag_value sont requis');
            }

            // Vérifier si la phase est active
            if ($phase_id !== null && !$this->challenge->isPhaseActive($input['hackathon_id'], $phase_id)) {
                throw new Exception("Cette phase n'est pas active actuellement !");
            }


            // Vérifier si la période du hackathon est active
            if (!$this->challenge->isChallengeLaunchPeriod($input['hackathon_id'])) {
                throw new Exception("Les challenges ne sont pas accessibles en dehors de la période de l'événement.");
            }

            // Vérifier s'il s'agit d'une phase pour qualifier
            if ($phase_id !== null && !$this->phase->checkQualification($user_id, $phase_id, $input['hackathon_id'])) {
                throw new Exception("L'utilisateur n'est pas qualifié pour cette phase !");
            }

            // Appel à la méthode qui gère toute la logique (valide ou non, dynamique, etc)
            $result = $this->challenge->submitChallengeCTF($user_id, $input, $phase_id);

            if ($result['success']) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $result['message'],
                    'validated_flag_id' => $result['validated_flag_id']
                ]);
            } else {
                // Flag incorrect ou déjà validé
                $this->jsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (Exception $e) {
            $this->tokenManager->logSecurityEvent(
                $user_id ?? 0,
                'submit_challenge_ctf_error',
                $e->getMessage(),
                isset($input['challenge_id']) ? ['challenge_id' => $input['challenge_id']] : []
            );
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getChallengeAlgo($hackathon_id, $user_id, $phase_id)
    {

        try {
            $this->validateMethod('GET');

            // Verifier si l'utiisateur est inscrit au hackathon
            if (!$this->isRegistered($user_id, $hackathon_id)) {
                throw new Exception("L'utilisateur n'est pas inscrit au hackathon !");
            }

            // Vérifier si la phase est active
            if ($phase_id !== null && !$this->challenge->isPhaseActive($hackathon_id, $phase_id)) {
                throw new Exception("Cette phase n'est pas active actuellement !");
            }

            // Vérifier si la période du hackathon est active
            if (!$this->challenge->isChallengeLaunchPeriod($hackathon_id)) {
                throw new Exception("Les challenges ne sont pas accessibles en dehors de la période de l'événement.");
            }

            // Vérifier si l'utilisateur est qualifié pour la phase
            if ($phase_id !== null && !$this->phase->checkQualification($user_id, $phase_id, $hackathon_id)) {
                throw new Exception("L'utilisateur n'est pas qualifié pour cette phase !");
            }

            $challenges = $this->challenge->getChallengeAlgo($hackathon_id, $user_id, $phase_id);

            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getChallengesDev($hackathon_id, $user_id, $phase_id = null)
    {
        try {
            $this->validateMethod('GET');
            if (!isset($hackathon_id) || !isset($user_id)) {
                throw new Exception('hackathon_id et user_id sont requis');
            }

            // Vérifier si la phase est active
            if ($phase_id !== null && !$this->challenge->isPhaseActive($hackathon_id, $phase_id)) {
                throw new Exception("Cette phase n'est pas active actuellement !");
            }

            // Vérifier si la période du hackathon est active
            if (!$this->challenge->isChallengeLaunchPeriod($hackathon_id)) {
                throw new Exception("Les challenges ne sont pas accessibles en dehors de la période de l'événement.");
            }

            // Vérifier si l'utilisateur est qualifié pour la phase
            if ($phase_id !== null && !$this->phase->checkQualification($user_id, $phase_id, $hackathon_id)) {
                throw new Exception("L'utilisateur n'est pas qualifié pour cette phase !");
            }

            // equipe de l'utilisateur
            $team = $this->challenge->getTeam($user_id);
            if (!$team) {
                throw new Exception("L'utilisateur n'est pas inscrit avec une équipe.");
            }
            $challenges = $this->challenge->getChallengeDev($hackathon_id, $team, $phase_id);
            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function findChallengeDev($user_id, $id)
    {
        try {
            $this->validateMethod('GET');
            $challenge = $this->challenge->find($id, $user_id);
            $this->jsonResponse([
                'success' => true,
                'data' => $challenge
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function getChallengesCTF($hackathon_id, $user_id, $phase_id = null)
    {
        try {
            $this->validateMethod('GET');

            if (!isset($hackathon_id) || !isset($user_id)) {
                throw new Exception('hackathon_id et user_id sont requis');
            }

            // Vérifier si la phase est active
            if ($phase_id !== null && !$this->challenge->isPhaseActive($hackathon_id, $phase_id)) {
                throw new Exception("Cette phase n'est pas active actuellement !");
            }

            // Vérifier si la période du hackathon est active
            if ($valid = !$this->challenge->isChallengeLaunchPeriod($hackathon_id)) {
                throw new Exception("Les challenges ne sont pas accessibles en dehors de la période de l'événement.(valid: " . ($valid ? 'true' : 'false') . ")");
            }

            // Vérifier si l'utilisateur est qualifié pour la phase
            if ($phase_id !== null && !$this->phase->checkQualification($user_id, $phase_id, $hackathon_id)) {
                throw new Exception("L'utilisateur n'est pas qualifié pour cette phase !");
            }

            $challenges = $this->challenge->getChallengeCTF($hackathon_id, $user_id, $phase_id);
            $this->jsonResponse([
                'success' => true,
                'data' => $challenges
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getUserPerformance($user_id, $hackathon_id, $phase_id = null)
    {
        try {
            if (!isset($user_id) || !isset($hackathon_id)) {
                throw new Exception('user_id et hackathon_id sont requis');
            }
            $this->validateMethod('GET');
            $performance = $this->challenge->getUserPerformance($user_id, $hackathon_id, $phase_id);
            $this->jsonResponse([
                'success' => true,
                'data' => $performance
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function get($id)
    {
        try {
            $this->validateMethod('GET');

            $userId = $this->tokenManager->getCurrentUserId();

            $challenge = $this->challenge->find($id, $userId);
            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }

            // Vérifier si l'utilisateur est qualifié pour la phase
            if (!$this->phase->checkQualification($userId, $challenge['phase_id'], $challenge['hackathon_id'])) {
                throw new Exception("L'utilisateur n'est pas qualifié pour cette phase !");
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $challenge
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }
    public function getSolvesCount()
    {
        try {
            $this->validateMethod('GET');
            $count = $this->challenge->getTotalSolvesCount();

            $this->jsonResponse([
                'success' => true,
                'count' => $count
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    /**
     * TODO: instruction non valide pour l'instant 
     * Récupère les challenges d'un hackathon
     * @param int $id ID du hackathon
     */
    // public function getByHackathon($id)
    // {
    //     try {
    //         $this->validateMethod('GET');

    //         $challenges = $this->challenge->getByHackathon($id);

    //         $this->jsonResponse([
    //             'success' => true,
    //             'data' => $challenges
    //         ]);
    //     } catch (Exception $e) {
    //         $this->jsonResponse([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 400);
    //     }
    // }

    public function isRegistered($user_id, $hackathon_id)
    {
        try {
            if (!isset($user_id) || !isset($hackathon_id)) {
                throw new Exception('user_id et hackathon_id sont requis');
            }
            $this->validateMethod('GET');
            $isRegistered = $this->challenge->isRegistered($user_id, $hackathon_id);

            return $isRegistered;
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * MÉTHODES POUR LES DÉFIS ALGORITHMIQUES
     */

    /**
     * Récupère un défi algorithmique avec ses cas de test publics
     * GET /api/challenges/algorithmic/{id}
     */
    public function getAlgorithmic($challengeId)
    {
        try {
            $this->validateMethod('GET');

            // Vérifier l'authentification (token Bearer ou session)
            $userId = $this->tokenManager->getCurrentUserId();

            // Si pas de token valide, essayer l'authentification par session
            if (!$userId) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                if (!isset($_SESSION['user_id'])) {
                    throw new Exception('Authentification requise', 401);
                }

                $userId = $_SESSION['user_id'];
            }

            // Récupérer le défi avec les cas de test publics seulement
            $challenge = $this->challenge->findAlgorithmic($challengeId, $userId, false);
            if (!$challenge) {
                throw new Exception('Défi algorithmique non trouvé', 404);
            }

            // Récupérer la meilleure soumission de l'utilisateur s'il y en a une
            $bestSubmission = $this->challenge->getBestSubmission($challengeId, $userId);

            // Récupérer l'historique des soumissions
            $history = $this->challenge->getSubmissionHistory($challengeId, $userId, 5);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'challenge' => $challenge,
                    'best_submission' => $bestSubmission,
                    'submission_history' => $history,
                    'user_id' => $userId
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Soumet une solution pour un défi algorithmique
     * POST /api/challenges/algorithmic/{id}/submit
     */
    public function submitAlgorithmic($challengeId, $input)
    {
        try {
            $this->validateMethod('POST');

            // Authentification JWT pure
            $userId = $this->tokenManager->getCurrentUserId();
            if (!$userId) {
                throw new Exception('Token manquant', 401);
            }

            // Valider les entree requis
            $this->validateRequiredFields([
                'code',
                'hackathon_id',
                'phase_id',
                'challenge_id',
                'user_id'
            ], $input);

            // Vérifier si l'utilisateur est inscrit au hackathon
            if (!$this->isRegistered($userId, $input['hackathon_id'])) {
                throw new Exception("L'utilisateur n'est pas inscrit au hackathon !");
            }

            // Vérifier si la phase est active
            if ($input['phase_id'] !== null && !$this->challenge->isPhaseActive($input['hackathon_id'], $input['phase_id'])) {
                throw new Exception("Cette phase n'est pas active actuellement !");
            }

            // Vérifier si la période du hackathon est active
            if (!$this->challenge->isChallengeLaunchPeriod($input['hackathon_id'])) {
                throw new Exception("Les challenges ne sont pas accessibles en dehors de la période de l'événement.");
            }

            // Vérifier si l'utilisateur est qualifié pour la phase
            if ($input['phase_id'] !== null && !$this->phase->checkQualification($userId, $input['phase_id'], $input['hackathon_id'])) {
                throw new Exception("L'utilisateur n'est pas qualifié pour cette phase !");
            }


            if (!isset($input['code']) || !isset($input['hackathon_id'])) {
                throw new Exception('Code source manquant', 400);
            }
            if (!isset($input['language']) || !isset($input['code']) || !isset($input['hackathon_id'])) {
                throw new Exception('Données de soumission incomplètes', 400);
            }

            $language = trim($input['language']);
            $sourceCode = trim($input['code']);
            $hackathonId = (int)$input['hackathon_id'];
            $userId = (int)$input['user_id'];
            // Validation basique du code
            if (empty($sourceCode)) {
                throw new Exception('Le code source ne peut pas être vide', 400);
            }

            if (strlen($sourceCode) > 50000) { // Limite de 50KB
                throw new Exception('Le code source est trop volumineux (max 50KB)', 400);
            }

            // Recuperer l'equipe de l'utilisateur
            $teamId = $this->challenge->getTeam($userId);
            if (!$teamId) {
                throw new Exception("Vous n'appartenez à aucune équipe.", 404);
            }

            // Verifier si un membre de l'équipe a déjà validé ce challenge
            $checkQuery = "
                SELECT cs.id
                FROM challenge_submissions cs
                WHERE cs.challenge_id = :challenge_id
                AND cs.team_id = :team_id
                AND cs.status = 'completed'
            ";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([
                ':challenge_id' => $challengeId,
                ':team_id' => $teamId
            ]);
            $result = $stmt->fetch();
            if ($result) {
                throw new Exception('Un membre de votre équipe a déjà validé ce challenge.', 400);
            }

            // Créer la soumission
            $submissionId = $this->challenge->createSubmission(
                $challengeId,
                $userId,
                $hackathonId,
                $language,
                $sourceCode,
                $teamId
            );

            // Démarrer l'évaluation en arrière-plan
            $results = $this->startEvaluation($submissionId);

            if ($results['success']) {
                //recuperer l'equipe
                $team_id = $this->challenge->getTeam($userId);
                // Vérifier si une ligne existe déjà
                $stmt = $this->db->prepare("
                    SELECT id FROM scores 
                    WHERE team_id = :team_id AND hackathon_id = :hackathon_id AND phase_id = :phase_id
                ");
                $stmt->execute([
                    ':team_id' => $team_id,
                    ':hackathon_id' => $input['hackathon_id'] ?? 2,
                    ':phase_id' => $phase_id ?? 2
                ]);

                $scoreId = $stmt->fetchColumn();

                if ($scoreId) {
                    // Update
                    $stmt = $this->db->prepare("
                        UPDATE scores 
                        SET total_points = total_points + :points , last_update = NOW() 
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':points' => $results['score'],
                        ':id' => $scoreId
                    ]);
                } else {
                    // Insert
                    $stmt = $this->db->prepare("
                        INSERT INTO scores (team_id, hackathon_id, phase_id, total_points)
                        VALUES (:team_id, :hackathon_id, :phase_id, :points)
                    ");
                    $stmt->execute([
                        ':team_id' => $team_id,
                        ':hackathon_id' => $input['hackathon_id'] ?? 2,
                        ':phase_id' => $phase_id ?? 2,
                        ':points' => $results['score']
                    ]);
                }
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Code soumis avec succès ! Évaluation en cours...',
                'data' => [
                    'submission_id' => $submissionId,
                    'status' => 'pending'
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Récupère les résultats d'une soumission
     * GET /api/challenges/submissions/{submissionId}/{user_id}
     */
    public function getSubmissionResults($submissionId, $userId)
    {
        try {
            $this->validateMethod('GET');

            // Vérifier l'authentification
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Token manquant', 401);
            }

            // Récupérer les détails de la soumission
            $submission = $this->challenge->getSubmissionDetails($submissionId, $userId);
            if (!$submission) {
                throw new Exception('Soumission non trouvée', 404);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $submission
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Récupère le classement d'un défi algorithmique
     * GET /api/challenges/algorithmic/{id}/leaderboard
     */
    public function getAlgorithmicLeaderboard($challengeId)
    {
        try {
            $this->validateMethod('GET');

            $leaderboard = $this->challenge->getLeaderboard($challengeId, 50);

            $this->jsonResponse([
                'success' => true,
                'data' => $leaderboard
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Démarre l'évaluation d'une soumission
     * @param int $submissionId
     */
    private function startEvaluation($submissionId)
    {
        try {
            $userId = $this->tokenManager->getCurrentUserId();
            // Verifier si la soumission existe
            $submission = $this->challenge->getSubmissionDetails($submissionId, $userId);
            if (!$submission) {
                throw new Exception('Soumission non trouvée', 404);
            }
            // Charger le service de validation
            require_once __DIR__ . '/../services/ChallengeValidationService.php';
            $validationService = new \Auth\Service\ChallengeValidationService($this->db, $this->challenge);

            // Marquer la soumission comme en cours d'évaluation
            $this->challenge->updateSubmissionResults($submissionId, 'running');

            // Lancer l'évaluation
            $results = $validationService->validateSubmission($submissionId);

            return $results;
        } catch (Exception $e) {
            // En cas d'erreur, marquer la soumission comme échouée
            $this->challenge->updateSubmissionResults(
                $submissionId,
                'error',
                0,
                null,
                null,
                0,
                0,
                'Erreur interne: ' . $e->getMessage()
            );
            throw $e;
        }
    }

    /**
     * Validation rapide du code (tests publics seulement)
     * POST /api/challenges/algorithmic/{id}/validate
     */
    public function validateCode($challengeId, $userId)
    {
        try {
            $this->validateMethod('POST');

            // Authentification JWT pure
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Token requis dans le header Authorization', 401);
            }


            // Récupérer les données
            $input = $this->getJsonInput();

            if (!isset($input['language']) || !isset($input['code'])) {
                throw new Exception('Langage et code requis', 400);
            }

            $language = trim($input['language']);
            $code = trim($input['code']);

            if (empty($code)) {
                throw new Exception('Le code ne peut pas être vide', 400);
            }

            // Charger le service de validation
            require_once __DIR__ . '/../services/ChallengeValidationService.php';
            $validationService = new \Auth\Service\ChallengeValidationService($this->db, $this->challenge);

            // Valider contre les tests publics
            $results = $validationService->validateCode($challengeId, $code, $language, $userId);

            $this->jsonResponse([
                'success' => $results['success'],
                'data' => $results
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * Récupère les données JSON de la requête
     */
    private function getJsonInput()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Si ce n'est pas du JSON valide, essayer $_POST
            return $_POST;
        }

        return $data ?: [];
    }
}
