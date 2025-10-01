<?php
require_once __DIR__ . '/backend/includes/config.php';

use Auth\Model\Database;
use Auth\Model\Project;

try {
    echo "🧪 Test rapide du modèle Project..." . PHP_EOL;
    
    $db = Database::getInstance()->getConnection();
    $project = new Project($db);
    
    // Test getAll()
    $projects = $project->getAll();
    echo "✅ Projets trouvés: " . count($projects) . PHP_EOL;
    
    if (count($projects) > 0) {
        echo "Premier projet: " . $projects[0]['name'] . PHP_EOL;
    }
    
    // Test avec filtre status
    $submittedProjects = $project->getAll(['status' => 'submitted']);
    echo "📨 Projets soumis: " . count($submittedProjects) . PHP_EOL;
    
    $ongoingProjects = $project->getAll(['status' => 'ongoing']);
    echo "⏳ Projets en cours: " . count($ongoingProjects) . PHP_EOL;
    
    echo "🎉 Test réussi !" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . PHP_EOL;
    echo "Stack: " . $e->getTraceAsString() . PHP_EOL;
}
?>