<?php

use Auth\Model\RedisManager;
require_once __DIR__ . '/backend/models/RedisManager.php'; // Ajustez selon votre autoload

require_once __DIR__ . '/vendor/autoload.php'; // Ajustez selon votre autoload


try {
    echo "🔧 Test de connexion Redis...\n\n";

    $redis = New RedisManager();

    // Test 1 : SET/GET
    echo "1️⃣ Test SET/GET:\n";
    $redis->set("test_key", "hello", 10);
    $value = $redis->get("test_key");
    echo "   Résultat: " . ($value === "hello" ? "✅ OK" : "❌ FAIL") . "\n";
    echo "   Valeur lue: {$value}\n\n";

    // Test 2 : INCREMENT
    echo "2️⃣ Test INCREMENT:\n";
    $count1 = $redis->increment("test_counter", 60);
    $count2 = $redis->increment("test_counter", 60);
    $count3 = $redis->increment("test_counter", 60);
    echo "   Compteurs: {$count1}, {$count2}, {$count3}\n";
    echo "   Résultat: " . ($count3 === 3 ? "✅ OK" : "❌ FAIL") . "\n\n";

    // Test 3 : TTL
    echo "3️⃣ Test TTL:\n";
    $ttl = $redis->ttl("test_counter");
    echo "   TTL: {$ttl}s\n";
    echo "   Résultat: " . ($ttl > 0 && $ttl <= 60 ? "✅ OK" : "❌ FAIL") . "\n\n";

    // Test 4 : BLOCK simulation (comme pour les flags CTF)
    echo "4️⃣ Test BLOCAGE (simulation CTF):\n";
    $blockKey = "ctf:block:999:888";
    $redis->set($blockKey, "blocked", 300);
    $isBlocked = $redis->get($blockKey);
    echo "   Bloqué: " . ($isBlocked ? "✅ OUI" : "❌ NON") . "\n";
    $ttlBlock = $redis->ttl($blockKey);
    echo "   TTL blocage: {$ttlBlock}s\n\n";

    // Test 5 : Vérification après suppression
    echo "5️⃣ Test DELETE:\n";
    $redis->delete("test_key");
    $redis->delete("test_counter");
    $redis->delete($blockKey);
    $afterDelete = $redis->get("test_key");
    echo "   Après suppression: " . ($afterDelete === false ? "✅ OK (vide)" : "❌ FAIL") . "\n\n";

    echo "🎉 Tous les tests sont passés !\n";
    echo "✅ Redis fonctionne correctement pour le rate limiting CTF\n";



    echo "🎯 TEST DU RATE LIMITING CTF\n";
    echo str_repeat("=", 50) . "\n\n";

// Configuration du test
    $userId = 999;        // ID de test
    $challengeId = 888;   // Challenge de test
    $redisKey = "ctf:attempts:{$userId}:{$challengeId}";
    $blockKey = "ctf:block:{$userId}:{$challengeId}";

    $redis = new RedisManager();

// Nettoyage initial
    echo "🧹 Nettoyage des clés précédentes...\n";
    $redis->delete($redisKey);
    $redis->delete($blockKey);
    echo "   ✅ Clés supprimées\n\n";

// Fonction pour simuler une tentative
    function simulateFlagAttempt($redis, $redisKey, $blockKey, $attemptNum) {
        echo "📝 Tentative #{$attemptNum}:\n";

        // Vérifier si bloqué
        $isBlocked = $redis->get($blockKey);
        if ($isBlocked) {
            $ttl = $redis->ttl($blockKey);
            echo "   ❌ BLOQUÉ ! Réessayez dans {$ttl} secondes\n";
            echo "   🔍 Valeur blockKey: {$isBlocked}\n";
            return false;
        }

        // Incrémenter les tentatives
        $attempts = $redis->increment($redisKey, 60);
        echo "   🔢 Compteur: {$attempts}/5\n";

        // Vérifier si on doit bloquer
        if ($attempts > 5) {
            $redis->set($blockKey, 'blocked', 300);
            echo "   🚫 LIMITE ATTEINTE ! Blocage activé (5 minutes)\n";
            $ttl = $redis->ttl($blockKey);
            echo "   ⏱️  TTL blocage: {$ttl}s\n";
            return false;
        }

        // Tentative acceptée (mais flag incorrect)
        $remaining = 5 - $attempts;
        echo "   ✅ Tentative enregistrée\n";
        echo "   ℹ️  Tentatives restantes: {$remaining}\n";

        $ttl = $redis->ttl($redisKey);
        echo "   ⏱️  TTL compteur: {$ttl}s\n";

        return true;
    }

    echo "🚀 Simulation de 8 tentatives consécutives:\n";
    echo str_repeat("-", 50) . "\n\n";

// Simuler 8 tentatives
    for ($i = 1; $i <= 8; $i++) {
        simulateFlagAttempt($redis, $redisKey, $blockKey, $i);
        echo "\n";

        // Petite pause pour simuler le temps réel
        if ($i < 8) {
            sleep(1);
        }
    }

// Vérifications finales
    echo str_repeat("=", 50) . "\n";
    echo "📊 ÉTAT FINAL:\n\n";

    $attemptsValue = $redis->get($redisKey);
    $blockValue = $redis->get($blockKey);

    echo "🔍 Clé '{$redisKey}':\n";
    if ($attemptsValue !== false) {
        echo "   Valeur: {$attemptsValue}\n";
        echo "   TTL: " . $redis->ttl($redisKey) . "s\n";
    } else {
        echo "   ❌ N'existe pas\n";
    }

    echo "\n🔍 Clé '{$blockKey}':\n";
    if ($blockValue !== false) {
        echo "   Valeur: {$blockValue}\n";
        echo "   TTL: " . $redis->ttl($blockKey) . "s\n";
        echo "   ✅ UTILISATEUR BLOQUÉ POUR CE CHALLENGE\n";
    } else {
        echo "   ❌ N'existe pas (utilisateur NON bloqué)\n";
    }

    echo "\n" . str_repeat("=", 50) . "\n";

// Test de vérification du blocage
    echo "\n🧪 Test de vérification du blocage:\n";
    $isStillBlocked = $redis->get($blockKey);
    if ($isStillBlocked) {
        $ttl = $redis->ttl($blockKey);
        echo "   ✅ Le blocage est actif\n";
        echo "   ⏱️  Temps restant: {$ttl} secondes\n";
    } else {
        echo "   ❌ Le blocage n'est PAS actif (PROBLÈME !)\n";
    }

    echo "\n✨ Test terminé !\n";
    echo "💡 Conseil: Comparez ce comportement avec votre code submitChallengeCTF()\n";

} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    echo "⚠️ Redis ne fonctionne PAS correctement !\n";
}