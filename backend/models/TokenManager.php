<?php
namespace Auth\Model;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;
use PDOException;
use Exception;


if (!class_exists('Firebase\JWT\JWT')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

class TokenManager
{
    private $key;
    private $db;

    public function __construct(string $key, PDO $db)
    {
        $this->key = $key;
        $this->db = $db; // Cette ligne était manquante et causait l'erreur
    }

    public function generateJwt(int $userId, int $expiryTime = 3600): string
    {
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + $expiryTime,
            "sub" => $userId
        ];

        return JWT::encode($payload, $this->key, 'HS256');
    }

    public function generateLongTermToken(int $userId): array
    {
        if (!$this->db) {
            throw new Exception('Database connection not initialized');
        }

        $expiryTime = time() + (30 * 24 * 60 * 60); // 30 jours
        $token = $this->generateJwt($userId, $expiryTime - time());

        // Stocker le token en base de données
        try {
            $stmt = $this->db->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':expires_at' => date('Y-m-d H:i:s', $expiryTime)
            ]);
        } catch (PDOException $e) {
            error_log('Erreur lors de l\'enregistrement du token: ' . $e->getMessage());
            throw new Exception('Erreur lors de la génération du token');
        }

        return [
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', $expiryTime)
        ];
    }


    public function verifyToken(string $token): bool
    {
        try {
            JWT::decode($token, new Key($this->key, 'HS256'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    //Verifie si un token existe et est valide sur une page
    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
            
            // Vérifier si c'est un token long terme et s'il existe en base
            if ($decoded->exp > time() + (24 * 60 * 60)) { // Si expiration > 24h
                $stmt = $this->db->prepare("SELECT * FROM user_tokens WHERE token = :token AND user_id = :user_id AND expires_at > NOW()");
                $stmt->execute([
                    ':token' => $token,
                    ':user_id' => $decoded->sub
                ]);
                
                if ($stmt->fetch() === false) {
                    return [
                        'valid' => false,
                        'error' => 'Token invalide ou expiré'
                    ];
                }
            }

            return [
                'valid' => true,
                'user_id' => $decoded->sub
            ];
        } catch (\Firebase\JWT\ExpiredException $e) {
            return [
                'valid' => false,
                'error' => 'Token expiré'
            ];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return [
                'valid' => false,
                'error' => 'Signature invalide'
            ];
        } catch (\UnexpectedValueException $e) {
            return [
                'valid' => false,
                'error' => 'Token invalide'
            ];
        } catch (Exception $e) { 
            return [
                'valid' => false,
                'error' => 'Erreur inconnue: ' . $e->getMessage()
            ];
        }
    }

    public function revokeToken(string $token): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM user_tokens WHERE token = :token");
            return $stmt->execute([':token' => $token]);
        } catch (PDOException $e) {
            error_log('Erreur lors de la révocation du token: ' . $e->getMessage());
            return false;
        }
    }

}