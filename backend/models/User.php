<?php

class User {
    private $db;
    private $table = 'users';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            $this->validate($data);

            $sql = "INSERT INTO {$this->table} (username, email, password, role, created_at) 
                    VALUES (:username, :email, :password, :role, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':role' => $data['role'] ?? 'participant',
                ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }

    public function find($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    public function findByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    public function findByResetToken($token) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_token_expiry > NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    public function update($id, $data) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "{$key} = :{$key}";
                    $params[":{$key}"] = $value;
                }
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getByRole($role) {
        try {
            $sql = "SELECT id, username, email, role, created_at FROM {$this->table} WHERE role = :role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role' => $role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
        }
    }

    public function getAll($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT id, username, email, role, created_at 
                   FROM {$this->table} 
                   ORDER BY created_at DESC 
                   LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'users' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'page' => $page,
                'limit' => $limit,
                'total' => $this->count()
            ];
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
        }
    }

    public function search($query, $page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            $searchTerm = "%{$query}%";
            
            $sql = "SELECT id, username, email, role, created_at 
                   FROM {$this->table} 
                   WHERE username LIKE :search 
                   OR email LIKE :search 
                   ORDER BY created_at DESC 
                   LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':search', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'users' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'page' => $page,
                'limit' => $limit,
                'total' => $this->countSearch($query)
            ];
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche des utilisateurs : " . $e->getMessage());
        }
    }

    private function count() {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            return $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des utilisateurs : " . $e->getMessage());
        }
    }

    private function countSearch($query) {
        try {
            $searchTerm = "%{$query}%";
            $sql = "SELECT COUNT(*) FROM {$this->table} 
                   WHERE username LIKE :search 
                   OR email LIKE :search";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':search', $searchTerm);
            $stmt->execute();
            
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des utilisateurs : " . $e->getMessage());
        }
    }

    private function validate($data) {
        if (empty($data['username']) || empty($data['email'])) {
            throw new Exception("Les champs nom, prénom et email sont obligatoires");
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse email n'est pas valide");
        }

        if (isset($data['password']) && strlen($data['password']) < 8) {
            throw new Exception("Le mot de passe doit contenir au moins 8 caractères");
        }

        $validRoles = ['participant', 'jury', 'organisateur', 'admin'];
        if (!empty($data['role']) && !in_array($data['role'], $validRoles)) {
            throw new Exception("Le rôle spécifié n'est pas valide");
        }

        return true;
    }
}
