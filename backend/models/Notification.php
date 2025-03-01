<?php

class Notification {
    private $db;
    private $table = 'notifications';

    public function __construct($db) {
        $this->db = $db;
    }

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

    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} (user_id, title, message, type) 
                    VALUES (:user_id, :title, :message, :type)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':title' => $data['title'] ?? 'Notification',
                ':message' => $data['message'],
                ':type' => $data['type']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de la notification : " . $e->getMessage());
        }
    }

    public function find($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de la notification : " . $e->getMessage());
        }
    }

    public function getByUser($userId, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des notifications : " . $e->getMessage());
        }
    }

    public function markAsRead($id) {
        try {
            $sql = "UPDATE {$this->table} SET is_read = TRUE WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du marquage de la notification : " . $e->getMessage());
        }
    }

    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE {$this->table} SET is_read = TRUE WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du marquage des notifications : " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de la notification : " . $e->getMessage());
        }
    }

    public function deleteAllRead($userId) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND is_read = TRUE";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $userId]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression des notifications : " . $e->getMessage());
        }
    }

    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table} WHERE user_id = :user_id AND is_read = FALSE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des notifications : " . $e->getMessage());
        }
    }
}
