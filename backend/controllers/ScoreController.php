<?php

namespace Auth\Controller;

use Auth\Model\Score;
use Auth\Controller\Controller;
use Auth\Model\Database;
use PDO;
use Exception;
use PDOException;
use Auth\Model\TokenManager;

if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Score')) {
    require_once __DIR__ . '/../models/Score.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('Auth\Model\TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}

// Vérifier si la classe Database existe, sinon l'inclure
if (!class_exists('Auth\Model\Database')) {
    require_once __DIR__ . '/../models/Database.php';
}

class ScoreController extends Controller
{
    protected $db;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
    }

    public function getLeaderboard($hackathon_id, $phase_id)
    {
        try {
            $this->validateMethod('GET');

            $score = new Score($this->db);
            $leaderboard = $score->getLeaderboard($hackathon_id, $phase_id);

            jsonResponse([
                'success' => true,
                'leaderboard' => $leaderboard
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getPhases($hackathon_id)
    {
        try {
            $this->validateMethod('GET');
            $score = new Score($this->db);
            $phases = $score->getPhases((int)$hackathon_id);

            jsonResponse([
                'success' => true,
                'phases' => $phases
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // TODO : Instruction interdite aux participants
    // public function updateScore($team_id, $hackathon_id, $phase_id, $input)
    // {
    //     try {
    //         $this->validateMethod('POST');
    //         $score = new Score($this->db);
    //         $score->updateScore($team_id, $hackathon_id, $phase_id, $input);

    //         jsonResponse([
    //             'success' => true,
    //             'message' => 'Score mis à jour avec succès'
    //         ]);
    //     } catch (Exception $e) {
    //         $this->jsonResponse([
    //             'success' => false,
    //             'error' => $e->getMessage()
    //         ], 400);
    //     }
    // }
}
