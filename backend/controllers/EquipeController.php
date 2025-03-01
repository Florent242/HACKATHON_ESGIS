<?php

class EquipeController extends Controller {
    private $equipe;
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->equipe = new Equipe($this->db);
    }

    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [
                'name' => $_POST['name'] ?? null,
                'hackathon_id' => $_POST['hackathon_id'] ?? null,
                'created_by' => $_POST['created_by'] ?? null
            ];

            $equipeId = $this->equipe->create($data);

            // Ajouter automatiquement le créateur comme leader
            if ($data['created_by']) {
                $this->equipe->addMember($equipeId, $data['created_by'], 'leader');
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Équipe créée avec succès',
                'data' => [
                    'id' => $equipeId,
                    'name' => $data['name']
                ]
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
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $userId = $_POST['user_id'] ?? null;
            if (!$userId) {
                throw new Exception('ID utilisateur requis');
            }

            $this->equipe->addMember($equipeId, $userId);

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

    public function getMembers($equipeId) {
        try {
            $membres = $this->equipe->getMembers($equipeId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $membres
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

    public function get($id) {
        try {
            $equipe = $this->equipe->find($id);
            
            if (!$equipe) {
                throw new Exception('Équipe non trouvée');
            }
            
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
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [];
            $fields = ['name'];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

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
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                throw new Exception('Méthode non autorisée');
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
}
