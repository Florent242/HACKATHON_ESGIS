<?php

class ProjetController extends Controller {
    private $projet;
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->projet = new Projet($this->db);
    }

    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'technologies' => $_POST['technologies'] ?? '',
                'equipe_id' => $_POST['equipe_id'] ?? null,
                'hackathon_id' => $_POST['hackathon_id'] ?? null
            ];

            $projetId = $this->projet->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet créé avec succès',
                'data' => [
                    'id' => $projetId,
                    'titre' => $data['titre']
                ]
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
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $repoUrl = $_POST['repo_url'] ?? null;
            if (!$repoUrl) {
                throw new Exception('URL du dépôt requise');
            }

            $this->projet->submitProject($id, $repoUrl);

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

    public function get($id) {
        try {
            $projet = $this->projet->find($id);
            
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }
            
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
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [];
            $fields = ['titre', 'description', 'technologies'];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

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

    public function delete($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
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
}
