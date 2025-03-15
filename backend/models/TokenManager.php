<?php
namespace Auth\Model;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
}