<?php
// Corriger les données de test avec les vraies nouvelles lignes

require_once __DIR__ . '/backend/models/Database.php';

try {
    $db = \Auth\Model\Database::getInstance()->getConnection();
    
    echo "🔧 Correction des données de test pour le défi 47\n";
    echo "===============================================\n";
    
    // Corriger les entrées avec des vraies nouvelles lignes
    $corrections = [
        ['2\n3', '5'],
        ['0\n0', '0'],
        ['-1\n5', '4'],
        ['100\n200', '300'],
        ['-50\n-30', '-80'],
        ['1000000\n2000000', '3000000'],
        ['-999\n1000', '1']
    ];
    
    // Récupérer tous les tests du défi 47
    $stmt = $db->prepare("SELECT id, input_data, expected_output FROM challenge_algo_tests WHERE challenge_id = 47 ORDER BY id");
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $db->prepare("UPDATE challenge_algo_tests SET input_data = ? WHERE id = ?");
    
    foreach ($tests as $index => $test) {
        if (isset($corrections[$index])) {
            $correctInput = $corrections[$index][0];
            
            // Vérifier si la correction est nécessaire
            if ($test['input_data'] !== $correctInput) {
                $updateStmt->execute([$correctInput, $test['id']]);
                echo "✅ Corrigé test ID {$test['id']}: '{$test['input_data']}' -> '{$correctInput}'\n";
            } else {
                echo "⚡ Test ID {$test['id']} déjà correct: '{$correctInput}'\n";
            }
        }
    }
    
    echo "\n🎯 Correction terminée!\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
?>
