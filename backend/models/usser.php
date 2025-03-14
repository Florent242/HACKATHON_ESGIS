<?php
namespace Auth\Model;

use PDO;
use PDOException;

class User
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function authenticate(string $email, string $password): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                return ['id' => $user['id']];
            }

            return null;
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de l'authentification: " . $e->getMessage());
        }
    }

    public function getUserById(int $id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
        }
    }

    public function createUser(array $userData): int
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->execute([$userData['email'], password_hash($userData['password'], PASSWORD_DEFAULT)]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new \Exception("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
        }
    }
}