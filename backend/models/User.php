<?php
namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class User {
    private $db;
    private $table = 'users';
    private $passwordColumn = 'password'; // Renommer la colonne pour plus de sécurité

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Vérifie les identifiants d'un utilisateur
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe non hashé
     * @return array|bool Les données de l'utilisateur ou false si non trouvé
     */
    public function authenticate($email, $password) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                return false;
            }

            // Vérifier le statut de l'utilisateur
            if (isset($user['status']) && $user['status'] === 'inactive') {
                throw new Exception('Votre compte est désactivé. Veuillez contacter un administrateur.');
            }

            if (password_verify($password, $user['password'])) {
                unset($user['password']); // Ne pas renvoyer le mot de passe
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log('Erreur d\'authentification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouvel utilisateur
     * @param array $data Les données de l'utilisateur
     * @return int|bool L'ID du nouvel utilisateur ou false si erreur
     */
    public function create($Data) {
        $data= $Data;
        try {
            if (!isset($data) || !is_array($data)) {
                throw new Exception("Donnes d'inscription innexistante");
            }

            // Verification si le username existe déjà
            if (isset($data['username']) && $data['username']) {
                $query = "SELECT COUNT(*) FROM {$this->table} WHERE username = :username";
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':username', $data['username']);
                $stmt->execute();

                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Cet nom d\'utilisateur est déjà utilisé');
                }
            }

            // Verification pour le fullname
            if (empty($data['fullname'])) {
                throw new Exception("Le nom complet est requis");
            }

            // Vérification si l'email existe déjà
            if (isset($data['email']) && $data['email']) {
                $query = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
                $stmt = $this->db->prepare($query);
                $stmt->bindValue(':email', $data['email']);
                $stmt->execute();

                if ($stmt->fetchColumn() > 0) {
                    throw new Exception('Cet email est déjà utilisé');
                }
            }

            // Vérification des champs obligatoires
            if (empty($data['username']) || empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
                throw new Exception("Tous les champs sont obligatoires");
            }

            // Vérification du mot de passe
            if (strlen($data['password']) < 8) {
                throw new Exception("Le mot de passe doit contenir au moins 8 caractères");
            }

            // Hash du mot de passe
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Préparation de la requête
            $query = "INSERT INTO {$this->table} (
            username, 
            fullname, 
            school, 
            email, 
            password, 
            role, 
            status, 
            github_url, 
            linkedin_url, 
            bio, 
            profile_picture
            )
                    VALUES (
                    :username, 
                    :fullname, 
                    :school, 
                    :email, 
                    :password, 
                    :role, 
                    :status, 
                    :github_url, 
                    :linkedin_url, 
                    :bio, 
                    :profile_picture
                    )";

            // ici j'ai du utilise bindValue parce que bindParam exige une variable passée par référence, ce qui cause une erreur quand tu utilises des expressions comme $data['email'] ?? null de plus bindParam n'accepte pas les valeurs null et data est un array
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':username', $data['username']);
            $stmt->bindValue(':fullname', $data['fullname']);
            $stmt->bindValue(':school', $data['school'] ?? null);
            $stmt->bindValue(':email', $data['email']);
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':role', $data['role'] ?? 'participant');
            $stmt->bindValue(':status', $data['status'] ?? 'active');
            $stmt->bindValue(':github_url', $data['github_url'] ?? null);
            $stmt->bindValue(':linkedin_url', $data['linkedin_url'] ?? null);
            $stmt->bindValue(':bio', $data['bio'] ?? null);
            $stmt->bindValue(':profile_picture', $data['profile_picture'] ?? null);

            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            logActivity('create_error', $e->getMessage(), [
                'email' => $data['email'] ?? 'non fourni',
                'error' => $e->getMessage()
            ], 'error');
            error_log('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
            throw new Exception('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour un utilisateur
     * @param int $id ID de l'utilisateur
     * @param array $data Les données à mettre à jour
     * @return bool true si succès, sinon false
     */
    public function update($id, $data) {
        try {
            // Vérification si l'utilisateur existe
            $user = $this->find($id);
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }

            // Construction de la requête
            $fields = [];
            $params = [];

            // Champs à mettre à jour
            $allowedFields = ['username', 'fullname', 'school', 'email', 'role', 'status', 'github_url', 'linkedin_url', 'bio', 'profile_picture'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $fields[] = "{$field} = :{$field}";
                    $params[":{$field}"] = $data[$field];
                }
            }

            // Gestion spéciale du mot de passe
            if (isset($data['password']) && !empty($data['password'])) {
                $fields[] = "password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if (empty($fields)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $query = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
            throw new Exception('Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un utilisateur par son ID
     * @param int $id ID de l'utilisateur
     * @return array|bool Les données de l'utilisateur ou false si non trouvé
     */
    public function find($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user) {
                unset($user['password']); // Ne pas renvoyer le mot de passe
            }

            return $user;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son email
     * @param string $email Email de l'utilisateur
     * @return array|bool Les données de l'utilisateur ou false si non trouvé
     */
public function findByUsername($username) {
    try {
        $sql = "SELECT id, username, email, role FROM {$this->table} WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    } catch (PDOException $e) {
        throw new Exception(message: 'Erreur lors de la recherche par username: ' . $e->getMessage());
    }
}

public function findByEmail($email) {
    try {
        $query = "SELECT id, username, email, role FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
    } catch (PDOException $e) {
        throw new Exception('Erreur lors de la recherche par email: ' . $e->getMessage());
    }
}
    /**
     * Récupère tous les utilisateurs
     * @return array Liste des utilisateurs
     */
    public function getAll() {
        try {
            $query = "SELECT id, username, fullname, school, email, role, status, github_url, linkedin_url, bio, profile_picture, created_at, updated_at FROM {$this->table}";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des utilisateurs: ' . $e->getMessage());            
            throw new Exception("Cette adresse email est déjà utilisée. User");
        }
    }

    /**
     * Supprime un utilisateur
     * @param int $id ID de l'utilisateur
     * @return bool true si succès, sinon false
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change le statut d'un utilisateur
     * @param int $id ID de l'utilisateur
     * @param string $status Nouveau statut ('active' ou 'inactive')
     * @return bool true si succès, sinon false
     */
    public function updateStatus($id, $status) {
        try {
            if (!in_array($status, ['active', 'inactive'])) {
                throw new Exception('Statut invalide');
            }

            $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Erreur lors de la mise à jour du statut de l\'utilisateur: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtient des statistiques sur les utilisateurs
     * @return array Statistiques des utilisateurs
     */
    public function getStats() {
        try {
            $stats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'roles' => [
                    'admin' => 0,
                    'organizer' => 0,
                    'judge' => 0,
                    'participant' => 0
                ]
            ];

            // Total des utilisateurs
            $query = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $stats['total'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Utilisateurs par statut
            $query = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $statusStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($statusStats as $stat) {
                if ($stat['status'] === 'active' || $stat['status'] === null) {
                    $stats['active'] += (int)$stat['count'];
                } else if ($stat['status'] === 'inactive') {
                    $stats['inactive'] += (int)$stat['count'];
                }
            }

            // Utilisateurs par rôle
            $query = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $roleStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($roleStats as $stat) {
                if (isset($stats['roles'][$stat['role']])) {
                    $stats['roles'][$stat['role']] = (int)$stat['count'];
                }
            }

            return $stats;
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des statistiques utilisateurs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les utilisateurs par rôle
     * @param string $role Rôle des utilisateurs à récupérer
     * @return array Liste des utilisateurs ayant le rôle spécifié
     */
    public function getByRole($role) {
        try {
            $query = "SELECT id, username, fullname, school, email, role, status, github_url, linkedin_url, bio, profile_picture, created_at, updated_at
                     FROM {$this->table}
                     WHERE role = :role
                     ORDER BY fullname";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération des utilisateurs par rôle: ' . $e->getMessage());
            return [];
        }
    } 
    
    public function getById(int $id): array|false
    {
        try {
            $query = "SELECT id, username, fullname, email, role, status, school, github_url, linkedin_url, bio, profile_picture 
                     FROM {$this->table} 
                     WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage());
            return false;
        }
    }
    
}
