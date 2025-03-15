<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Challenge;
use Auth\Model\Hackathon;

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Challenge.php';
require_once __DIR__ . '/../models/Hackathon.php';
require_once __DIR__ . '/Controller.php';

class ChallengeController extends Controller {
    private $challenge;
    private $hackathon;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->challenge = new Challenge($db);
        $this->hackathon = new Hackathon($db);
    }

    public function index($hackathonId) {
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

    public function create() {
        try {
            $this->validateMethod('POST');
            
            if (!hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            $requiredFields = ['titre', 'description', 'hackathon_id', 'points'];
            $this->validateRequiredFields($_POST, $requiredFields);

            if (!is_numeric($_POST['points']) || $_POST['points'] < 0) {
                throw new Exception('Le nombre de points doit être un nombre positif');
            }

            $data = [
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'points' => (int)$_POST['points'],
                'created_by' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $challengeId = $this->challenge->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Challenge créé avec succès',
                'data' => ['id' => $challengeId]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update($id) {
        try {
            $this->validateMethod('POST');
            
            if (!hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            $updatableFields = ['titre', 'description', 'points'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['points']) && (!is_numeric($data['points']) || $data['points'] < 0)) {
                throw new Exception('Le nombre de points doit être un nombre positif');
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->challenge->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Challenge mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function delete($id) {
        try {
            $this->validateMethod('POST');
            
            if (!hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            $this->challenge->delete($id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Challenge supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function get($id) {
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
}
