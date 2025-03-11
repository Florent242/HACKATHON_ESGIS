<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Projet.php';
require_once __DIR__ . '/Controller.php';

class ProjetController extends Controller {
    private $projet;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->projet = new Projet($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['titre', 'description', 'equipe_id', 'hackathon_id'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'technologies' => $_POST['technologies'] ?? '',
                'equipe_id' => (int)$_POST['equipe_id'],
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'statut' => 'en_cours',
                'repo_url' => null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $projetId = $this->projet->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet créé avec succès',
                'data' => ['id' => $projetId, 'titre' => $data['titre']]
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
            
            $projet = $this->projet->find($id);
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }
            
            // Récupérer les évaluations si disponibles
            $evaluations = $this->projet->getEvaluations($id);
            $projet['evaluations'] = $evaluations;
            
            $this->jsonResponse([
                'success' => true,
                'data' => $projet
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
            
            $updatableFields = ['titre', 'description', 'technologies', 'repo_url', 'statut'];
            $data = $this->filterData($_POST, $updatableFields);
            
            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $this->projet->update($id, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function submitProject($id) {
        try {
            $this->validateMethod('POST');
            
            if (empty($_POST['repo_url'])) {
                throw new Exception('URL du dépôt requise');
            }

            $data = [
                'repo_url' => $_POST['repo_url'],
                'statut' => 'soumis',
                'submitted_at' => date('Y-m-d H:i:s')
            ];

            $this->projet->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet soumis avec succès'
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
            $projets = $this->projet->getByHackathon($hackathonId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $projets
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getByEquipe($equipeId) {
        try {
            $this->validateMethod('GET');
            $projet = $this->projet->getByEquipe($equipeId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $projet
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

            $this->projet->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getStats($hackathonId) {
        try {
            $this->validateMethod('GET');
            
            $stats = [
                'total' => $this->projet->countByStatus($hackathonId, null),
                'en_cours' => $this->projet->countByStatus($hackathonId, 'en_cours'),
                'soumis' => $this->projet->countByStatus($hackathonId, 'soumis'),
                'termine' => $this->projet->countByStatus($hackathonId, 'termine')
            ];
            
            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
