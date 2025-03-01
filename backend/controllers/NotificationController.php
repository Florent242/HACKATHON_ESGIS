<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/Controller.php';

class NotificationController extends Controller {
    private $notification;
    private $db;

    public function __construct() {
        parent::__construct();
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->notification = new Notification($this->db);
    }

    // Afficher les notifications de l'utilisateur connecté
    public function index() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les paramètres de pagination
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

            // Récupérer les notifications
            $result = $this->notification->getByUser($_SESSION['user_id'], $limit, $offset);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'data' => $result
                ]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/notification/index.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/dashboard');
        }
    }

    // Créer une notification
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [
                'user_id' => $_POST['user_id'] ?? null,
                'type' => $_POST['type'] ?? null,
                'message' => $_POST['message'] ?? null
            ];

            // Créer la notification
            $notificationId = $this->notification->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification créée avec succès',
                'data' => [
                    'id' => $notificationId
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Marquer une notification comme lue
    public function markAsRead($id) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Vérifier si la notification existe et appartient à l'utilisateur
            $notification = $this->notification->find($id);
            if (!$notification || $notification['user_id'] !== $_SESSION['user_id']) {
                throw new Exception('Notification non trouvée');
            }

            // Marquer comme lue
            $this->notification->markAsRead($id);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Notification marquée comme lue'
                ]);
            }

            redirect('/notifications');
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/notifications');
        }
    }

    // Marquer toutes les notifications comme lues
    public function markAllAsRead() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Marquer toutes comme lues
            $this->notification->markAllAsRead($_SESSION['user_id']);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Toutes les notifications ont été marquées comme lues'
                ]);
            }

            redirect('/notifications');
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/notifications');
        }
    }

    // Supprimer une notification
    public function delete($id) {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Vérifier si la notification existe et appartient à l'utilisateur
            $notification = $this->notification->find($id);
            if (!$notification || $notification['user_id'] !== $_SESSION['user_id']) {
                throw new Exception('Notification non trouvée');
            }

            // Supprimer la notification
            $this->notification->delete($id);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Notification supprimée'
                ]);
            }

            redirect('/notifications');
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/notifications');
        }
    }

    // Supprimer toutes les notifications lues
    public function deleteAllRead() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Supprimer toutes les notifications lues
            $this->notification->deleteAllRead($_SESSION['user_id']);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Toutes les notifications lues ont été supprimées'
                ]);
            }

            redirect('/notifications');
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/notifications');
        }
    }

    // Récupérer le nombre de notifications non lues (pour le badge)
    public function getUnreadCount() {
        try {
            // Vérifier si l'utilisateur est connecté
            if (!isAuthenticated()) {
                throw new Exception('Non autorisé');
            }

            $count = $this->notification->getUnreadCount($_SESSION['user_id']);
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
