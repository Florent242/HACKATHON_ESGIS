<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Notification;
use PDO;

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

    public function create($data) {
        try {
            $this->validateMethod('POST');
    
            // Vérification du scope
            if (empty($data['scope']) || !in_array($data['scope'], ['user', 'team', 'hackathon', 'global'])) {
                throw new Exception("Scope invalide (user|team|hackathon|global)");
            }
    
            if (empty($data['message'])) {
                throw new Exception("Le message est requis");
            }
            if (empty($data['type']) || !in_array($data['type'], ['info', 'success', 'warning', 'error'])) {
                throw new Exception("Type de notification invalide");
            }
    
            // Résoudre les destinataires selon le scope
            $recipients = [];
            switch ($data['scope']) {
                case 'user':
                    if (empty($data['user_id'])) throw new Exception("user_id requis pour scope=user");
                    $recipients = [ (int)$data['user_id'] ];
                    break;
    
                case 'team':
                    if (empty($data['team_id'])) throw new Exception("team_id requis pour scope=team");
                    $recipients = $this->getTeamMemberIds($data['team_id']);
                    break;
    
                case 'hackathon':
                    if (empty($data['hackathon_id'])) throw new Exception("hackathon_id requis pour scope=hackathon");
                    $recipients = $this->getHackathonParticipantIds($data['hackathon_id']);
                    break;
    
                case 'global':
                    $recipients = $this->getAllActiveUserIds();
                    break;
            }
    
            if (empty($recipients)) {
                throw new Exception("Aucun destinataire trouvé");
            }
    
            // Fan-out via createBulk()
            $rows = [];
            foreach ($recipients as $uid) {
                $rows[] = [
                    'user_id' => $uid,
                    'title'   => $data['title'] ?? 'Notification',
                    'message' => $data['message'],
                    'type'    => $data['type'],
                ];
            }
            $this->notification->createBulk($rows);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    private function getTeamMemberIds(int $teamId): array {
        $sql = "SELECT DISTINCT user_id FROM team_members WHERE team_id = :tid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $teamId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function getHackathonParticipantIds(int $hackathonId): array {
        // privilégie la table hackathon_participants si elle existe
        $sql = "SELECT DISTINCT user_id FROM hackathon_participants WHERE hackathon_id = :hid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':hid' => $hackathonId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    private function getAllActiveUserIds(): array {
        $sql = "SELECT id FROM users WHERE status = 'active'";
        $stmt = $this->db->query($sql);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }
    
    /**
     * Récupère toutes les notifications d'un utilisateur
     * @param int $userId ID de l'utilisateur
     */
    public function listForCurrentUser($userId, $limit = 10, $offset = 0) {
        try {
            $this->validateMethod('GET');
            $uid = (int)$userId;
            $limit = max(1, min(50, (int)$limit));
            $offset = max(0, (int)$offset);

            $items = $this->notification->getAllByUser($uid, $limit, $offset);
            $unread = $this->notification->getUnreadCount($uid);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'items' => $items,
                    'unread_count' => $unread
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    /** 
     * Récupère une notification par son ID
     * @param int $id ID de la notification
     */
    public function getNotification($id) {
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
    public function update($id, $data) {
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

            $updatableFields = ['title', 'message', 'type', 'read_status'];
            $data = $this->filterData($data, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['type']) && !in_array($data['type'], ['info', 'success', 'warning', 'error'])) {
                throw new Exception('Type de notification invalide');
            }

            if (isset($data['read_status'])) {
                $data['read_status'] = (bool)$data['read_status'];
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

            $this->notification->markAsRead($id);

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
