<?php
namespace Auth\Model;

use PDO;
use PDOException;
use Exception;

class User {
    private $db;
    private $table = 'users';
    private $passwordColumn = 'mot_de_passe'; // Renommer la colonne pour plus de sécurité

    public function __construct($db) {
        $this->db = $db;
    }

    // Créer un nouvel utilisateur
    public function create($data) {
        try {
            // Validation des données
            $this->validate($data);

            // Hashage du mot de passe
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            $sql = "INSERT INTO {$this->table} (nom, email, {$this->passwordColumn}, role) 
                    VALUES (:nom, :email, :mot_de_passe, :role)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nom' => $data['username'],
                ':email' => $data['email'],
                ':mot_de_passe' => $data['password'],
                ':role' => $data['role'] ?? 'participant'
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }

    // Trouver un utilisateur par son ID
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

    // Trouver un utilisateur par son email
    public function findByEmail($email) {
        try {
            $sql = "SELECT id, username, email, {$this->passwordColumn}, role FROM {$this->table} WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    // Mettre à jour un utilisateur
    public function update($id, $data) {
        try {
            // Construire la requête de mise à jour dynamiquement
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

    // Supprimer un utilisateur
    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
        }
    }

    // Authentifier un utilisateur
    public function authenticate($email, $password) {
        try {
            $user = $this->findByEmail($email);
            
            if ($user) {
                // Vérifier la force du mot de passe avant l'authentification
                if (strlen($password) < 8) {
                    throw new Exception("Le mot de passe doit contenir au moins 8 caractères");
                }

                if (!preg_match('/[A-Z]/', $password)) {
                    throw new Exception("Le mot de passe doit contenir au moins une majuscule");
                }

                if (!preg_match('/[a-z]/', $password)) {
                    throw new Exception("Le mot de passe doit contenir au moins une minuscule");
                }

                if (!preg_match('/[0-9]/', $password)) {
                    throw new Exception("Le mot de passe doit contenir au moins un chiffre");
                }

                if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
                    throw new Exception("Le mot de passe doit contenir au moins un caractère spécial");
                }

                // Vérifier le hash
                if (password_verify($password, $user[$this->passwordColumn])) {
                    unset($user[$this->passwordColumn]); // Ne pas retourner le hash
                    return [
                        'id' => $user['id'],
                        'username' => $user['nom'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ];
                }
            }
            
            return false;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'authentification : " . $e->getMessage());
        }
    }

    // Valider les données de l'utilisateur
    private function validate($data) {
        // Vérifier que les champs requis sont présents
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception("Tous les champs requis doivent être remplis");
        }

        // Valider l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse email n'est pas valide");
        }

        // Vérifier que l'email n'est pas déjà utilisé
        $existingUser = $this->findByEmail($data['email']);
        if ($existingUser) {
            throw new Exception("Cette adresse email est déjà utilisée");
        }

        // Valider le mot de passe
        if (strlen($data['password']) < 8) {
            throw new Exception("Le mot de passe doit contenir au moins 8 caractères");
        }

        // Valider le rôle
        $validRoles = ['participant', 'organisateur', 'juge', 'admin'];
        if (!empty($data['role']) && !in_array($data['role'], $validRoles)) {
            throw new Exception("Le rôle spécifié n'est pas valide");
        }

        return true;
    }

    // Récupérer tous les utilisateurs
    public function getAll($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'users' => $stmt->fetchAll(),
                'page' => $page,
                'limit' => $limit,
                'total' => $this->count()
            ];
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
        }
    }

    // Compter le nombre total d'utilisateurs
    private function count() {
        try {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            return $this->db->query($sql)->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors du comptage des utilisateurs : " . $e->getMessage());
        }
    }
    public function getByRole($role) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE role = :role";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':role' => $role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des utilisateurs par rôle : " . $e->getMessage());
        }
    }

}
