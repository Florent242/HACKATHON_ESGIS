<?php
namespace Auth\Model;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PDO;

class TokenManager
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
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
        $expiryTime = time() + (30 * 24 * 60 * 60); // 30 jours
        $token = $this->generateJwt($userId, $expiryTime - time());

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
            } catch (\Exception $e) { 
                return [
                    'valid' => false,
                    'error' => 'Erreur inconnue: ' . $e->getMessage()
                ];
            }
        }
    
}