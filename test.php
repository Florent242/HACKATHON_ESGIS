<?php

require 'vendor/autoload.php'; // Assure-toi que firebase/php-jwt est installé via Composer
require_once __DIR__ . '/backend/models/TokenManager.php';
require_once __DIR__ . '/backend/models/Database.php';
require_once __DIR__ . '/backend/models/User.php';
use Auth\Model\TokenManager;
use Auth\Model\Database;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

var_dump(class_exists('Redis'));

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
$redis->auth('Qwertyui1?');
$redis->set('test_key', 'hello redis');
echo $redis->get('test_key')."\n";

// $maliciousToken = JWT::encode([
//     'iss' => 'hackathon.esgis.bj',
//     'iat' => time(),
//     'exp' => time() + 60,
//     'sub' => 42,
//     'jti' => '123456'
// ], 'wrong-secret-key', 'HS256');

// echo $maliciousToken;

// // 🔁 TEST EN COURS D’EXÉCUTION
// $db = Database::getInstance();
// $test = new TokenManager('your-secret-key', $db->getConnection());

// echo "=== Étape 1 : Génération du token ===\n";
// $token = $test->generateJwt(42, 7, true); // token de 15 secondes
// echo "Token : $token\n";

// echo "\n=== Étape 2 : Validation immédiate ===\n";
// $valid = $test->validateToken($token);
// echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
// echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";

// echo "\n=== Attente de 5 secondes... ===\n";
// sleep(5);

// echo "\n=== Étape 3 : Validation avant expiration ===\n";
// $valid = $test->validateToken($token);
// echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
// echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";

// echo "\n=== Étape 4 : Validation après expiration ===\n";
// echo "\n=== Attente de 5 secondes... ===\n";
// sleep(5);
// $valid = $test->validateToken($token);
// echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
// echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";


// $test->revokeToken($token, true);
// echo "\n=== Étape 5 : Validation après révocation ===\n";
// $valid = $test->validateToken($token);
// echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
// echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";

// echo "\n=== Étape X : Validation du token malveillant ===\n";
// $valid = $test->validateToken($maliciousToken);
// echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
// echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";

// echo "\n=== Étape Z : Token manipulé ===\n";
// $valid = $test->validateToken($maliciousToken);
// echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
// echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";

// $key = 'your-secret-key';      // même clé que celle utilisée dans ton système
// $algo = 'HS256';
// $domain = 'hackathon.esgis.bj';  // ou ton vrai domaine
// $expiryTime = 300;

// // Initialisation Redis
// $redis = new Redis();
// $redis->connect($_ENV['REDIS_HOST'] ?? '127.0.0.1', $_ENV['REDIS_PORT'] ?? 6379);
// $redis->auth($_ENV['REDIS_PASSWORD'] ?? 'Qwertyui1?');

// // 1. Création d'un token valide
// $userId = 42;
// $payload = [
//     "iss" => $domain,
//     "iat" => time(),
//     "exp" => time() + $expiryTime,
//     "sub" => $userId,
//     "jti" => bin2hex(random_bytes(16)),
//     "nbf" => time() - 1
// ];
// $validJwt = JWT::encode($payload, $key, $algo);
// echo "Token valide : $validJwt\n";

// $redis->setex("jwt:$payload[jti]", $expiryTime, $userId);
// echo "Token stocké dans Redis\n";

// // 2. Manipulation du token
// $decodedPayload = $payload;
// $decodedPayload["sub"] = 999; // ✨ Faux utilisateur injecté

// $manipulatedJwt = JWT::encode($decodedPayload, $key, $algo); // Re-signature malveillante

// // 3. Simulation de vérification
// try {
//     // $decoded = JWT::decode($manipulatedJwt, new Key($key, $algo));
//     // $storedUserId = $redis->get("jwt:$manipulatedJwt");

//     // echo "=== TEST DU TOKEN MANIPULÉ ===\n";
//     // if (!$storedUserId || (int)$storedUserId !== (int)$decoded->sub) {
//     //     throw new Exception('❌ Token invalide ou expiré dans Redis');
//     // }

//     // echo "✅ Token accepté (ERREUR : il n’aurait pas dû l’être)\n";

//     echo "\n=== Étape Z : Token manipulé ===\n";
//     $valid = $test->validateToken($manipulatedJwt);
//     echo "Validation : " . ($valid['valid'] ? 'OK' : 'KO') . "\n";
//     echo "Erreur : " . ($valid['error'] ?? 'Aucun détail') . "\n";
// } catch (Exception $e) {
//     echo "🎯 Système de détection : " . $e->getMessage() . "\n";
// }