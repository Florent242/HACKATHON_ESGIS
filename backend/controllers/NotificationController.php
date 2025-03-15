<?php
namespace Auth\Controller;

use Exception;

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/Controller.php';

class NotificationController extends Controller {
    private $notification;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->notification = new Notification($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['user_id', 'message', 'type'];
            $this->validateRequiredFields($_POST, $requiredFields);

            if (!in_array($_POST['type'], ['info', 'success', 'warning', 'error'])) {
                throw new Exception('Type de notification invalide');
            }

            $data = [
                'user_id' => (int)$_POST['user_id'],
                'titre' => $_POST['titre'] ?? 'Notification',
                'message' => $_POST['message'],
                'type' => $_POST['type'],
                'created_at' => date('Y-m-d H:i:s'),
                'lu' => false
            ];

            $notificationId = $this->notification->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification créée avec succès',
                'data' => ['id' => $notificationId]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getByUser($userId) {
        try {
            $this->validateMethod('GET');
            
            $notifications = $this->notification->getByUser($userId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function markAsRead($id) {
        try {
            $this->validateMethod('POST');
            
            $this->notification->update($id, ['lu' => true]);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification marquée comme lue'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function markAllAsRead($userId) {
        try {
            $this->validateMethod('POST');
            
            $this->notification->markAllAsRead($userId);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Toutes les notifications ont été marquées comme lues'
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
            
            $this->notification->delete($id);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification supprimée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteAll($userId) {
        try {
            $this->validateMethod('POST');
            
            $this->notification->deleteByUser($userId);
            
            $this->jsonResponse([
                'success' => true,
                'message' => 'Toutes les notifications ont été supprimées'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
