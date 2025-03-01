<?php

class HackathonController extends Controller {
    private $hackathon;
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->hackathon = new Hackathon($this->db);
    }

    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'start_date' => $_POST['start_date'] ?? null,
                'end_date' => $_POST['end_date'] ?? null,
                'max_participants' => $_POST['max_participants'] ?? null,
                'status' => $_POST['status'] ?? 'draft',
                'created_by' => $_POST['created_by'] ?? null
            ];

            $hackathonId = $this->hackathon->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Hackathon créé avec succès',
                'data' => [
                    'id' => $hackathonId,
                    'title' => $data['title']
                ]
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

    public function getActive() {
        try {
            $hackathons = $this->hackathon->getActive();
            
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
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [];
            $fields = ['title', 'description', 'start_date', 'end_date', 'max_participants', 'status'];
            
            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
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
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                throw new Exception('Méthode non autorisée');
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
