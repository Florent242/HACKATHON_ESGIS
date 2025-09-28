<?php

namespace Auth\Controller;

use Exception;
use Auth\Model\Phase;
use Auth\Controller\Controller;

class PhaseController extends Controller
{
    private $phase;
    private $db;
    public $tokenManager;
    public $isPublicRoute;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->phase = new Phase($this->db);
        $this->tokenManager = $tokenManager;
    }

    public function getActivePhase($hackathonId, $userId)
    {
        try {
            $this->validateMethod('GET');
            $phase = $this->phase->getActiveForUser($hackathonId, $userId);
            $this->jsonResponse([
                'success' => true,
                'data' => $phase
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getAllPhases($hackathonId)
    {
        try {
            $this->validateMethod('GET');
            $phases = $this->phase->getAllForHackathon($hackathonId);
            $this->jsonResponse([
                'success' => true,
                'data' => $phases
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function get($id){
        try {
            $this->validateMethod('GET');
            $phase = $this->phase->get($id);
            $this->jsonResponse([
                'success' => true,
                'data' => $phase
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}