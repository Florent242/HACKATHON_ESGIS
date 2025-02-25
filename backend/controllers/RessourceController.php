<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Ressource.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/Controller.php';

class RessourceController extends Controller {
    private $ressource;
    private $notification;
    private $db;

    public function __construct() {
        parent::__construct();
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->ressource = new Ressource($this->db);
        $this->notification = new Notification($this->db);
    }

    // Afficher les ressources d'un hackathon
    public function index($hackathonId) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer le type filtré si présent
            $type = isset($_GET['type']) ? cleanInput($_GET['type']) : null;

            // Récupérer la recherche si présente
            $search = isset($_GET['q']) ? cleanInput($_GET['q']) : null;

            // Récupérer les ressources
            $ressources = $search 
                ? $this->ressource->search($hackathonId, $search)
                : $this->ressource->getByHackathon($hackathonId, $type);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => [
                        'ressources' => $ressources
                    ]
                ]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/ressource/index.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$hackathonId}");
        }
    }

    // Créer une nouvelle ressource
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            $data = [
                'hackathon_id' => $_POST['hackathon_id'] ?? null,
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'url' => $_POST['url'] ?? null,
                'type' => $_POST['type'] ?? 'document'
            ];

            // Créer la ressource
            $ressourceId = $this->ressource->create($data);

            // Notifier les participants
            $this->notification->create([
                'user_id' => $_SESSION['user_id'],
                'title' => 'Nouvelle ressource disponible',
                'message' => "Une nouvelle ressource a été ajoutée : {$data['title']}",
                'type' => 'info'
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Ressource créée avec succès',
                'data' => [
                    'id' => $ressourceId
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Mettre à jour une ressource
    public function update($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            // Vérifier si la ressource existe
            $ressource = $this->ressource->find($id);
            if (!$ressource) {
                throw new Exception('Ressource non trouvée');
            }

            $data = [
                'title' => $_POST['title'] ?? $ressource['title'],
                'description' => $_POST['description'] ?? $ressource['description'],
                'url' => $_POST['url'] ?? $ressource['url'],
                'type' => $_POST['type'] ?? $ressource['type']
            ];

            // Mettre à jour la ressource
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

    // Supprimer une ressource
    public function delete($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            // Vérifier si la ressource existe
            $ressource = $this->ressource->find($id);
            if (!$ressource) {
                throw new Exception('Ressource non trouvée');
            }

            // Supprimer la ressource
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

    // Afficher mes ressources
    public function myRessources() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les ressources
            $ressources = $this->ressource->getByCreator($_SESSION['user_id']);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse(['ressources' => $ressources]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/ressource/my-ressources.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse(['error' => $e->getMessage()], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/dashboard');
        }
    }
}
