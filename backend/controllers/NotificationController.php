<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Notification;

if(!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if(!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if(!class_exists('Notification')) {
    require_once __DIR__ . '/../models/Notification.php';
}
if(!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class NotificationController extends Controller {
    private $notification;
    private $db;
    
    public function __construct($db, $tokenManager) {
        parent::__construct($tokenManager);
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

    /** 
     * Récupère une notification par son ID
     * @param int $id ID de la notification
     */
    public function getNotifications($id) {
        try {
            $this->validateMethod('GET');

            $notification = $this->notification->find($id);
            if (!$notification) {
                throw new Exception('Notification non trouvée');
            }

            // Vérifier si l'utilisateur est le destinataire ou un admin
            if ($notification['user_id'] != $_SESSION['user_id'] && !hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $notification
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Met à jour une notification
     * @param int $id ID de la notification
     */
    public function update($id) {
        try {
            $this->validateMethod('POST');

            $notification = $this->notification->find($id);
            if (!$notification) {
                throw new Exception('Notification non trouvée');
            }

            // Vérifier si l'utilisateur est un admin
            if (!hasRole('admin')) {
                throw new Exception('Non autorisé - Réservé aux administrateurs');
            }

            $updatableFields = ['titre', 'message', 'type', 'lu'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['type']) && !in_array($data['type'], ['info', 'success', 'warning', 'error'])) {
                throw new Exception('Type de notification invalide');
            }

            if (isset($data['lu'])) {
                $data['lu'] = (bool)$data['lu'];
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->notification->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Notification mise à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getUnreadCount($userId) {
        try {
            $this->validateMethod('GET');
            $count = $this->notification->getUnreadCount($userId);
            $this->jsonResponse([
                'success' => true,
                'data' => ['unread_count' => $count]
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
