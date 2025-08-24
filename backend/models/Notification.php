<?php
namespace Auth\Model;

use Exception;
use PDOException;
use PDO;
class Notification {
    private $db;
    private $table = 'notifications';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Validation des données
     * @param mixed $data
     * @throws \Exception
     * @return void
     */
    private function validate($data) {
        if (empty($data['user_id'])) {
            throw new Exception("L'ID de l'utilisateur est requis");
        }

        if (empty($data['message'])) {
            throw new Exception("Le message est requis");
        }

        if (empty($data['type']) || !in_array($data['type'], ['info', 'success', 'warning', 'error'])) {
            throw new Exception("Le type de notification n'est pas valide");
        }
    }

    /**
     * Création d'une notification
     * @param mixed $data
     * @throws \Exception
     * @return void
     */
    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} 
                    (user_id, title, message, type, read_status, created_at) 
                    VALUES (:user_id, :title, :message, :type, 0, NOW() )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':title'   => $data['title'] ?? 'Notification',
                ':message' => $data['message'],
                ':type'    => $data['type'] ?? 'info'
                ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la notification !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    public function createBulk(array $rows) {
        // $rows = [['user_id'=>1,'title'=>'','message'=>'','type'=>'info'], ...]
        if (empty($rows)) return 0;

        $sql = "INSERT INTO {$this->table} (user_id, title, message, type, read_status, created_at)
                VALUES ";
        $vals = [];
        $params = [];
        foreach ($rows as $i => $r) {
            $vals[] = "( :user_id_$i, :title_$i, :message_$i, :type_$i, 0, NOW() )";
            $params[":user_id_$i"] = (int)$r['user_id'];
            $params[":title_$i"]   = $r['title']   ?? 'Notification';
            $params[":message_$i"] = $r['message'];
            $params[":type_$i"]    = $r['type']    ?? 'info';
        }
        $sql .= implode(',', $vals);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Recherche d'une notification par son ID
     * @param mixed $id
     * @throws \Exception
     * @return void
     */
    public function find($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de la notification !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Récupération des notifications d'un utilisateur
     * @param mixed $userId
     * @param mixed $limit
     * @param mixed $offset
     * @throws \Exception
     * @return void
     */
    public function getAllByUser($userId, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} 
            WHERE user_id = :user_id 
            ORDER BY created_at DESC 
            LIMIT :limit 
            OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des notifications : " 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Marquage d'une notification comme lue
     * @param mixed $id
     * @throws \Exception
     * @return void
     */
    public function markAsRead($id) {
        try {
            $sql = "UPDATE {$this->table} 
            SET read_status = TRUE 
            WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du marquage de la notification : " 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Marquage de toutes les notifications d'un utilisateur comme lues
     * @param mixed $userId
     * @throws \Exception
     * @return void
     */
    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE {$this->table} 
            SET read_status = TRUE 
            WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du marquage des notifications !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Suppression d'une notification
     * @param mixed $id
     * @throws \Exception
     * @return void
     */
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} 
            WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la notification !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Mise à jour d'une notification
     * @param mixed $id
     * @param mixed $data
     * @throws \Exception
     * @return void
     */
    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];

            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "$key = :$key";
                    $params[":$key"] = $value;
                }
            }

            if (empty($fields)) {
                throw new Exception("Aucune donnée à mettre à jour");
            }

            $fields[] = "updated_at = :updated_at";
            $params[':updated_at'] = date('Y-m-d H:i:s');

            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de la notification !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Suppression de toutes les notifications d'un utilisateur
     * @param mixed $userId
     * @throws \Exception
     * @return void
     */
    public function deleteAllRead($userId) {
        try {
            $sql = "DELETE FROM {$this->table} 
            WHERE user_id = :user_id 
            AND read_status = TRUE";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression des notifications !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Comptage des notifications non lues d'un utilisateur
     * @param mixed $userId
     * @throws \Exception
     * @return void
     */
    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} 
            WHERE user_id = :user_id 
            AND read_status = FALSE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des notifications !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }

    /**
     * Suppression de toutes les notifications d'un utilisateur
     * @param mixed $userId
     * @throws \Exception
     * @return void
     */
    public function deleteByUser($userId) {
        try {
            $sql = "DELETE FROM {$this->table} 
            WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression des notifications !" 
            // Pour debuger
            //  . $e->getMessage()
            );
        }
    }
}
