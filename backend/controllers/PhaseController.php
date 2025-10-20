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

    public function addPhase($hackathonId, $data){
        try {
            $this->validateMethod('POST');
            $phase = $this->phase->addPhase($hackathonId, $data);
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

    public function updatePhase($hackathonId, $phaseId, $data){
        try {
            $this->validateMethod('PUT');
            $phase = $this->phase->updatePhase($hackathonId, $phaseId, $data);
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

    public function deletePhase($hackathonId, $phaseId){
        try {
            $this->validateMethod('DELETE');
            $phase = $this->phase->deletePhase($hackathonId, $phaseId);
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

    public function checkQualification($userId, $phaseId, $hackathonId){
        try {
            $this->validateMethod('GET');
            $phase = $this->phase->checkQualification($userId, $phaseId, $hackathonId) || $this->isAdmin($userId);
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