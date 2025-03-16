<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Equipe;

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Equipe.php';
require_once __DIR__ . '/Controller.php';

class EquipeController extends Controller {
    private $equipe;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->equipe = new Equipe($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['nom', 'hackathon_id', 'created_by'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'nom' => $_POST['nom'],
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'created_by' => (int)$_POST['created_by'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $equipeId = $this->equipe->create($data);

            // Ajouter automatiquement le créateur comme leader
            if ($data['created_by']) {
                $this->equipe->addMembre($equipeId, $data['created_by'], 'leader');
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe créée avec succès',
                'data' => ['id' => $equipeId, 'nom' => $data['nom']]
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
            
            $equipe = $this->equipe->find($id);
            if (!$equipe) {
                throw new Exception('Équipe non trouvée');
            }
            
            // Récupérer les membres de l'équipe
            $membres = $this->equipe->getMembres($id);
            $equipe['membres'] = $membres;
            
            $this->jsonResponse([
                'success' => true,
                'data' => $equipe
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update($id) {
        try {
            $this->validateMethod('POST');
            
            $updatableFields = ['nom'];
            $data = $this->filterData($_POST, $updatableFields);
            
            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $this->equipe->update($id, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe mise à jour avec succès'
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
            
            // Vérifier si l'utilisateur a les droits
            if (!hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            $this->equipe->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe supprimée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getByHackathon($hackathonId) {
        try {
            $this->validateMethod('GET');
            
            $equipes = $this->equipe->getByHackathon($hackathonId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $equipes
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
    public function index() {
        try {
            $this->validateMethod('GET');
            $equipes = $this->equipe->getAll(); // Assurez-vous que cette méthode existe dans votre modèle
            
            $this->jsonResponse([
                'success' => true,
                'data' => $equipes
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function addMember($equipeId) {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['user_id', 'role'];
            $this->validateRequiredFields($_POST, $requiredFields);

            if (!in_array($_POST['role'], ['member', 'leader'])) {
                throw new Exception('Rôle invalide');
            }

            $this->equipe->addMembre(
                $equipeId,
                (int)$_POST['user_id'],
                $_POST['role']
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Membre ajouté avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function removeMember($equipeId) {
        try {
            $this->validateMethod('POST');
            
            if (empty($_POST['user_id'])) {
                throw new Exception('ID utilisateur requis');
            }

            $this->equipe->removeMembre($equipeId, (int)$_POST['user_id']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Membre retiré avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
