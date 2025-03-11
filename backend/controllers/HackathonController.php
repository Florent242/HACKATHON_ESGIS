<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Hackathon.php';
require_once __DIR__ . '/Controller.php';

class HackathonController extends Controller {
    private $hackathon;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->hackathon = new Hackathon($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['titre', 'description', 'date_debut', 'date_fin', 'max_participants'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'date_debut' => $_POST['date_debut'],
                'date_fin' => $_POST['date_fin'],
                'max_participants' => (int)$_POST['max_participants'],
                'statut' => $_POST['statut'] ?? 'brouillon',
                'created_by' => $_POST['created_by'] ?? null
            ];

            // Validation des dates
            if (strtotime($data['date_fin']) <= strtotime($data['date_debut'])) {
                throw new Exception('La date de fin doit être postérieure à la date de début');
            }

            $hackathonId = $this->hackathon->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon créé avec succès',
                'data' => ['id' => $hackathonId, 'titre' => $data['titre']]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getAll() {
        try {
            $this->validateMethod('GET');
            $hackathons = $this->hackathon->getAll();
            
            $this->jsonResponse([
                'success' => true,
                'data' => $hackathons
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
            
            $hackathon = $this->hackathon->find($id);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }
            
            $this->jsonResponse([
                'success' => true,
                'data' => $hackathon
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
            
            $updatableFields = ['titre', 'description', 'date_debut', 'date_fin', 'max_participants', 'statut'];
            $data = $this->filterData($_POST, $updatableFields);
            
            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['date_debut']) && isset($data['date_fin'])) {
                if (strtotime($data['date_fin']) <= strtotime($data['date_debut'])) {
                    throw new Exception('La date de fin doit être postérieure à la date de début');
                }
            }

            $this->hackathon->update($id, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon mis à jour avec succès'
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

            $this->hackathon->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
