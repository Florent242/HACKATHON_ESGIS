<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Challenge;
use Auth\Model\Hackathon;
use Auth\Model\TokenManager;

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

class ChallengeController extends Controller
{
    private $challenge;
    private $hackathon;
    private $db;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->challenge = new Challenge($db);
        $this->hackathon = new Hackathon($db);
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

    /**
     * Récupère tous les challenges
     */
    public function getAll()
    {
        try {
            $this->validateMethod('GET');

            $challenges = $this->challenge->getAll();

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

    public function submitChallengeCTF($user_id, $input, $phase_id = null)
    {
        try {
            $this->validateMethod('POST');

            if (empty($user_id) || empty($input['flag_value'])) {
                throw new Exception('user_id et flag_value sont requis');
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
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getChallengeAlgo ($hackathon_id, $user_id, $phase_id = null){

        try {
            $this->validateMethod('GET');
            $challenges = $this->challenge->getchallengeAlgo($hackathon_id, $user_id, $phase_id);
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
            $challenges = $this->challenge->getchallengeDev($hackathon_id, $user_id, $phase_id);
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

    public function getChallengesCTF($hackathon_id, $user_id, $phase_id = null)
    {
        try {
            $this->validateMethod('GET');
            if (!isset($hackathon_id) || !isset($user_id)) {
                throw new Exception('hackathon_id et user_id sont requis');
            }
            $challenges = $this->challenge->getchallengeCTF($hackathon_id, $user_id, $phase_id);
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


    public function get($id)
    {
        try {
            $this->validateMethod('GET');

            $challenge = $this->challenge->find($id);
            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
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
            ]);
        }
    }
    /**
     * Récupère les challenges d'un hackathon
     * @param int $id ID du hackathon
     */
    public function getByHackathon($id)
    {
        try {
            $this->validateMethod('GET');

            $challenges = $this->challenge->getByHackathon($id);

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

    public function isRegistered($user_id, $hackathon_id)
    {
        try {
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
}
