<?php
namespace Auth\Controller; // Cela doit être le namespace correct pour RessourceController

use Exception;
use Auth\Model\Ressource;
use Auth\Controller\Controller;

if(!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if(!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if(!class_exists('Ressource')) {
    require_once __DIR__ . '/../models/Ressource.php';
}
if(!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class RessourceController extends Controller {
    private $ressource;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->ressource = new Ressource($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            if (!hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            $requiredFields = ['titre', 'description', 'hackathon_id', 'type'];
            $this->validateRequiredFields($_POST, $requiredFields);

            if (!in_array($_POST['type'], ['document', 'video', 'lien'])) {
                throw new Exception('Type de ressource invalide');
            }

            $data = [
                'titre' => $_POST['titre'],
                'description' => $_POST['description'],
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'type' => $_POST['type'],
                'url' => $_POST['url'] ?? null,
                'created_by' => $_SESSION['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $ressourceId = $this->ressource->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Ressource créée avec succès',
                'data' => ['id' => $ressourceId]
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
            
            $ressource = $this->ressource->find($id);
            if (!$ressource) {
                throw new Exception('Ressource non trouvée');
            }
            
            $this->jsonResponse([
                'success' => true,
                'data' => $ressource
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function getByHackathon($hackathonId) {
        try {
            $this->validateMethod('GET');
            
            $ressources = $this->ressource->getByHackathon($hackathonId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $ressources
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

            $updatableFields = ['titre', 'description', 'url', 'type'];
            $data = $this->filterData($_POST, $updatableFields);
            
            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['type']) && !in_array($data['type'], ['document', 'video', 'lien'])) {
                throw new Exception('Type de ressource invalide');
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->ressource->update($id, $data);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Ressource mise à jour avec succès'
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

            $this->ressource->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Ressource supprimée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function search($hackathonId) {
        try {
            $this->validateMethod('GET');
            
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? null;
            
            if (empty($query)) {
                throw new Exception('Terme de recherche requis');
            }

            if ($type && !in_array($type, ['document', 'video', 'lien'])) {
                throw new Exception('Type de ressource invalide');
            }

            $ressources = $this->ressource->search($hackathonId, $query, $type);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $ressources
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
