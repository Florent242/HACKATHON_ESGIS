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
    private $algorithm = 'HS256'; // Au lieu de hardcoder dans les méthodes
    private $domain = 'your-domain.com';
    private $shortTermExpiry = 3600; // 1 heure
    private $longTermExpiry = 2592000; // 30 jours

    // Dans le constructeur
    public function __construct(string $key, PDO $db, array $config = [])
    {
        $this->key = $key;
        $this->db = $db;

        // Configuration personnalisable
        $this->algorithm = $config['algorithm'] ?? 'HS256';
        $this->shortTermExpiry = $config['shortTermExpiry'] ?? 3600;
        $this->longTermExpiry = $config['longTermExpiry'] ?? 2592000;

        // Valider que l'algorithme est supporté
        if (!in_array($this->algorithm, ['HS256', 'HS384', 'HS512', 'RS256'])) {
            throw new Exception('Algorithme non supporté');
        }
    }

    public function generateJwt(int $userId, int $expiryTime = 3600): string
    {
        $expiryTime = $expiryTime ?? $this->shortTermExpiry;
        $payload = [
            "iss" => $this->domain,
            "iat" => time(),
            "exp" => time() + $expiryTime,
            "sub" => $userId,
            "jti" => bin2hex(random_bytes(16)), // Identifiant unique pour le token
            "nbf" => time() - 1 // "Not Before" - empêche l'utilisation immédiate
        ];

        return JWT::encode($payload, $this->key, $this->algorithm);
    }

    public function generateLongTermToken(int $userId): array
    {
        $token = $this->generateJwt($userId, $this->longTermExpiry);
        $expiryTime = time() + $this->longTermExpiry;
        $refreshToken = bin2hex(random_bytes(32));

        try {
            $this->db->beginTransaction();

            // D'abord révoquer les anciens tokens
            $stmt = $this->db->prepare(
                "UPDATE user_tokens SET revoked = 1 WHERE user_id = :user_id AND revoked = 0"
            );
            $stmt->execute([':user_id' => $userId]);

            // Puis stocker le nouveau
            $stmt = $this->db->prepare(
                "INSERT INTO user_tokens 
            (user_id, token, refresh_token, expires_at, ip_address, user_agent) 
            VALUES (:user_id, :token, :refresh_token, :expires_at, :ip, :ua)"
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':token' => $token,
                ':refresh_token' => $refreshToken,
                ':expires_at' => date('Y-m-d H:i:s', $expiryTime),
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            $this->db->commit();

            return [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'expires_at' => date('Y-m-d H:i:s', $expiryTime)
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Erreur de génération de token: ' . $e->getMessage());
            throw new Exception('Génération de token impossible: ' . $e->getMessage());
        }
    }

    public function getCurrentUserId(): ?int
    {
        try {
            $token = $this->getBearerToken();
            if (!$token) {
                throw new Exception('Token manquant', 401);
            }
            $tokenValidation = $this->validateToken($token);
            if (!$tokenValidation['valid']) {
                throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? 'Aucun détail'), 401);
            }
            return (int) $tokenValidation['user_id'];
        } catch (Exception $e) {
            error_log("Erreur dans getCurrentUserId: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
            throw $e;
        }
    }

    
    protected function getBearerToken(): ?string
    {
        $headers = $this->getAuthorizationHeader();
        if (!empty($headers) && preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
        if (isset($_COOKIE['long_term_token'])) {
            return $_COOKIE['long_term_token'];
        }
        if (isset($_COOKIE['jwt_token'])) {
            return $_COOKIE['jwt_token'];
        }
        return null;
    }

    /**
     * Récupère le header Authorization
     */
    public function getAuthorizationHeader(): ?string
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(
                array_map('ucwords', array_keys($requestHeaders)),
                array_values($requestHeaders)
            );
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
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
    public function validateToken(string $token): array
    {
        try {

            $decoded = JWT::decode($token, new Key($this->key, $this->algorithm));

            // Vérifications supplémentaires
            if ($decoded->iss !== $this->domain) {
                throw new Exception('Invalid issuer');
            }

            // Vérifier l'expiration
            if ($decoded->exp < time()) {
                throw new Exception('Token expiré');
            }

            // Pour les tokens long terme
            if ($decoded->exp > time() + 86400) { // > 24h
                $stmt = $this->db->prepare(
                    "SELECT * FROM user_tokens 
                WHERE token = :token 
                AND user_id = :user_id 
                AND expires_at > NOW() 
                AND revoked = 0"
                );

                $stmt->execute([
                    ':token' => $token,
                    ':user_id' => $decoded->sub
                ]);

                $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$tokenData) {
                    throw new Exception('Token revoked or not found !');
                }

                // Vérification de sécurité supplémentaire
                $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
                $currentUa = $_SERVER['HTTP_USER_AGENT'] ?? '';

                if (
                    $tokenData['ip_address'] !== $currentIp ||
                    $tokenData['user_agent'] !== $currentUa
                ) {
                    // Log de sécurité
                    $this->logSecurityEvent($decoded->sub, 'token_validation_failed', [
                        'reason' => 'ip_or_ua_mismatch',
                        'stored_ip' => $tokenData['ip_address'],
                        'current_ip' => $currentIp
                    ]);

                    throw new Exception('Security validation failed');
                }
            }else if($decoded->exp < time() + 86400) // < 24h
            {
                // $refreshedToken = $this->refreshToken($decoded->jti);
                // return [
                //     'valid' => true,
                //     'user_id' => $decoded->sub,
                //     'payload' => (array)$decoded,
                //     'refreshed_longTermToken' => $refreshedToken
                // ];
            }

            return [
                'valid' => true,
                'user_id' => $decoded->sub,
                'payload' => (array)$decoded
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e)
            ];
        } catch (\Firebase\JWT\ExpiredException $e) {
            return [
                'valid' => false,
                'error' => 'Token expiré: ' . $e->getMessage()
            ];
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return [
                'valid' => false,
                'error' => 'Signature invalide: ' . $e->getMessage()
            ];
        } catch (\UnexpectedValueException $e) {
            return [
                'valid' => false,
                'error' => 'Token invalide: ' . $e->getMessage()
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
        } catch (Exception $e) {
            error_log('Erreur lors de la révocation du token: ' . $e->getMessage());
            return false;
        }
    }

    public function refreshToken(string $refreshToken): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT user_id FROM user_tokens 
            WHERE refresh_token = :refresh_token 
            AND expires_at > NOW() 
            AND revoked = 0"
            );

            $stmt->execute([':refresh_token' => $refreshToken]);
            $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tokenData) {
                throw new Exception('Invalid or expired refresh token');
            }

            // Révoquer l'ancien token
            $this->revokeTokenByRefreshToken($refreshToken);

            // Générer un nouveau token
            return $this->generateLongTermToken($tokenData['user_id']);
        } catch (Exception $e) {
            error_log('Refresh token error: ' . $e->getMessage());
            throw new Exception('Could not refresh token');
        }
    }

    private function revokeTokenByRefreshToken(string $refreshToken): bool
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE user_tokens SET revoked = 1 
            WHERE refresh_token = :refresh_token"
            );
            return $stmt->execute([':refresh_token' => $refreshToken]);
        } catch (Exception $e) {
            error_log('Token revocation error: ' . $e->getMessage());
            return false;
        }
    }

    private function logSecurityEvent(int $userId, string $eventType, array $details = [])
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO security_logs 
            (user_id, event_type, ip_address, user_agent, details, created_at) 
            VALUES (:user_id, :event_type, :ip, :ua, :details, NOW())"
            );

            $stmt->execute([
                ':user_id' => $userId,
                ':event_type' => $eventType,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                ':details' => json_encode($details)
            ]);
        } catch (Exception $e) {
            error_log('Failed to log security event: ' . $e->getMessage());
        }
    }
}
