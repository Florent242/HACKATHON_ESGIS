<?php
namespace Auth\Model;

use PDO;
use PDOException;
use Exception;

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $table = 'users';
    private $pdo;

    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function createUser(array $userData): int {
        try {
            $this->validate($userData);

<<<<<<< HEAD
            $sql = "INSERT INTO {$this->table} (username, email, password, role, created_at) 
                    VALUES (:username, :email, :password, :role, :created_at)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':role' => $data['role'] ?? 'participant',
                ':created_at' => $data['created_at'] ?? date('Y-m-d H:i:s')
=======
            $stmt = $this->pdo->prepare("INSERT INTO {$this->table} (email, password, nom, prenom, role, created_at) 
                                      VALUES (:email, :password, :nom, :prenom, :role, :created_at)");
            
            $stmt->execute([
                ':email' => $userData['email'],
                ':password' => $userData['password'], // Le mot de passe est déjà hashé dans AuthController::signup
                ':nom' => $userData['nom'] ?? '',
                ':prenom' => $userData['prenom'] ?? '',
                ':role' => $userData['role'] ?? 'participant',
                ':created_at' => $userData['created_at'] ?? date('Y-m-d H:i:s')
>>>>>>> d07363345c399c7a5b7c589f546ca407f849d0d3
            ]);

            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
        }
    }

    public function getUserById(int $id): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['password']); // Ne jamais renvoyer le mot de passe
                return $this->formatResponse($this->sanitizeOutput($user))['data'];
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
        }
    }

    public function authenticate(string $email, string $password): ?array {
        try {
            $stmt = $this->pdo->prepare("SELECT id, password FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return $this->formatResponse(['id' => $user['id']])['data'];
            }

            return null;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'authentification: " . $e->getMessage());
        }
    }

    public function findByEmail(string $email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['password']);
                return $this->formatResponse($this->sanitizeOutput($user))['data'];
            }
            return null;;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    public function findByResetToken($token) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_token_expiry > NOW()";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    public function updateUser(int $id, array $data): bool {
        try {
            $fields = [];
            $params = ['id' => $id];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id') {
                    $fields[] = "{$key} = ?";
                    $params[] = $value;
                }
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
        }
    }

    public function deleteUser(int $id): bool {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getByRole($role) {
        try {
<<<<<<< HEAD
            $sql = "SELECT id, username, email, role, created_at FROM {$this->table} WHERE role = :role";
            $stmt = $this->db->prepare($sql);
=======
            $sql = "SELECT id, nom, prenom, email, role, created_at FROM {$this->table} WHERE role = :role";
            $stmt = $this->pdo->prepare($sql);
>>>>>>> d07363345c399c7a5b7c589f546ca407f849d0d3
            $stmt->execute([':role' => $role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
        }
    }

    public function getAllUsers(int $page = 1, int $limit = 20): array {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT id, username, email, role, created_at 
                   FROM {$this->table} 
                   ORDER BY created_at DESC 
                   LIMIT :limit OFFSET :offset";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $users = $this->sanitizeOutput($stmt->fetchAll(PDO::FETCH_ASSOC));
            return $this->formatPaginatedResponse($users, $page, $limit, $this->count());
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
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', $searchTerm);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $users = $this->sanitizeOutput($stmt->fetchAll(PDO::FETCH_ASSOC));
            return $this->formatPaginatedResponse($users, $page, $limit, $this->countSearch($query));
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
            
            $stmt = $this->pdo->prepare($sql);
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
