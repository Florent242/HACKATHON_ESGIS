<?php

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/backend/models/Database.php';
require_once __DIR__ . '/backend/models/User.php';
require_once __DIR__ . '/backend/models/Hackathon.php';
require_once __DIR__ . '/backend/models/Equipe.php';
require_once __DIR__ . '/backend/models/Projet.php';
require_once __DIR__ . '/backend/models/Evaluation.php';
require_once __DIR__ . '/backend/models/Challenge.php';
require_once __DIR__ . '/backend/models/Notification.php';
require_once __DIR__ . '/backend/models/Participant.php';
require_once __DIR__ . '/backend/models/Ressource.php';
require_once __DIR__ . '/backend/controllers/Controller.php';
require_once __DIR__ . '/backend/controllers/AuthController.php';
require_once __DIR__ . '/backend/controllers/HackathonController.php';
require_once __DIR__ . '/backend/controllers/EquipeController.php';
require_once __DIR__ . '/backend/controllers/ProjetController.php';
require_once __DIR__ . '/backend/controllers/EvaluationController.php';
require_once __DIR__ . '/backend/controllers/NotificationController.php';
require_once __DIR__ . '/backend/controllers/ParticipantController.php';
require_once __DIR__ . '/backend/controllers/RessourceController.php';

// Initialiser la connexion à la base de données
$database = Database::getInstance();
$db = $database->getConnection();

echo "=== Tests des Modèles ===\n\n";

// Fonction utilitaire pour tester les contrôleurs
function testController($callback) {
    ob_start();
    try {
        $callback();
        $output = ob_get_clean();
        $result = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur de décodage JSON: " . $output);
        }
        return $result;
    } catch (Exception $e) {
        ob_end_clean();
        throw new Exception("Erreur lors de l'exécution du test: " . $e->getMessage());
    }
}

// Générer des valeurs uniques pour les tests
$timestamp = time();
$userData = [
    'username' => "testuser{$timestamp}",
    'email' => "test{$timestamp}@example.com",
    'password' => 'password123',
    'role' => 'participant',
    'full_name' => 'Test User',
    'bio' => 'A test user'
];

// Test du modèle User
try {
    echo "Test du modèle User...\n";
    $user = new User($db);
    
    // Vérifier si l'utilisateur existe déjà
    $existingUser = $user->findByEmail($userData['email']);
    if ($existingUser) {
        echo "✓ Utilisateur existe déjà, utilisation de cet ID: {$existingUser['id']}\n";
        $userId = $existingUser['id'];
    } else {
        $userId = $user->create($userData);
        echo "✓ Utilisateur créé avec succès (ID: $userId)\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du modèle Hackathon
try {
    echo "\nTest du modèle Hackathon...\n";
    $hackathon = new Hackathon($db);
    
    $hackathonData = [
        'title' => "Test Hackathon {$timestamp}",
        'description' => 'Un hackathon de test',
        'start_date' => '2025-03-01 00:00:00',
        'end_date' => '2025-03-03 23:59:59',
        'max_participants' => 50,
        'status' => 'published',
        'created_by' => $userId
    ];
    
    $hackathonId = $hackathon->create($hackathonData);
    echo "✓ Hackathon créé avec succès (ID: $hackathonId)\n";
    
    $foundHackathon = $hackathon->find($hackathonId);
    echo "✓ Hackathon trouvé avec succès\n";
    
    $updateData = ['description' => 'Description mise à jour'];
    $hackathon->update($hackathonId, $updateData);
    echo "✓ Hackathon mis à jour avec succès\n";
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du modèle Equipe
try {
    echo "\nTest du modèle Equipe...\n";
    $equipe = new Equipe($db);
    
    $equipeData = [
        'name' => "Team Test {$timestamp}",
        'hackathon_id' => $hackathonId,
        'created_by' => $userId
    ];
    
    $equipeId = $equipe->create($equipeData);
    echo "✓ Équipe créée avec succès (ID: $equipeId)\n";
    
    $equipe->addMember($equipeId, $userId);
    echo "✓ Membre ajouté à l'équipe avec succès\n";
    
    $membres = $equipe->getMembers($equipeId);
    echo "✓ Liste des membres récupérée avec succès\n";
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du modèle Challenge
try {
    echo "\nTest du modèle Challenge...\n";
    $challenge = new Challenge($db);
    
    $challengeData = [
        'hackathon_id' => $hackathonId,
        'title' => "Challenge Test {$timestamp}",
        'description' => 'Un challenge de test',
        'max_teams' => 10,
        'points' => 100
    ];
    
    $challengeId = $challenge->create($challengeData);
    echo "✓ Challenge créé avec succès (ID: $challengeId)\n";
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du modèle Projet
try {
    echo "\nTest du modèle Projet...\n";
    $projet = new Projet($db);
    
    $projetData = [
        'title' => "Projet Test {$timestamp}",
        'description' => 'Un projet de test',
        'equipe_id' => $equipeId,
        'challenge_id' => $challengeId,
        'repository_url' => 'https://github.com/test/repo',
        'demo_url' => 'https://demo.test.com',
        'status' => 'draft'
    ];
    
    $projetId = $projet->create($projetData);
    echo "✓ Projet créé avec succès (ID: $projetId)\n";
    
    $projet->submitProject($projetId, 'https://github.com/test/repo');
    echo "✓ Projet soumis avec succès\n";
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du modèle Evaluation
try {
    echo "\nTest du modèle Evaluation...\n";
    $evaluation = new Evaluation($db);
    
    $evaluationData = [
        'projet_id' => $projetId,
        'juge_id' => $userId,
        'score' => 8.5,
        'commentaire' => 'Très bon projet'
    ];
    
    $evaluationId = $evaluation->create($evaluationData);
    echo "✓ Évaluation créée avec succès (ID: $evaluationId)\n";
    
    $moyennes = $evaluation->getProjectScores($projetId);
    echo "✓ Moyennes des notes récupérées avec succès\n";
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

echo "\n=== Tests des Contrôleurs ===\n\n";

// Test du contrôleur Hackathon
try {
    echo "Test du contrôleur Hackathon...\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST = [
        'title' => "Test Hackathon {$timestamp}",
        'description' => 'Un hackathon de test',
        'start_date' => '2025-03-01 00:00:00',
        'end_date' => '2025-03-03 23:59:59',
        'max_participants' => 50,
        'status' => 'published',
        'created_by' => $userId
    ];
    
    $hackathonController = new HackathonController();
    $response = testController(function() use ($hackathonController) {
        $hackathonController->create();
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Création de hackathon via API réussie\n";
    } else {
        echo "✗ Erreur lors de la création du hackathon via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du contrôleur Equipe
try {
    echo "\nTest du contrôleur Equipe...\n";
    $_POST = [
        'name' => "Team Test {$timestamp}",
        'hackathon_id' => $hackathonId,
        'created_by' => $userId
    ];
    
    $equipeController = new EquipeController();
    $response = testController(function() use ($equipeController) {
        $equipeController->create();
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Création d'équipe via API réussie\n";
    } else {
        echo "✗ Erreur lors de la création de l'équipe via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du contrôleur Projet
try {
    echo "\nTest du contrôleur Projet...\n";
    $_POST = [
        'title' => "Projet Test {$timestamp}",
        'description' => 'Un projet de test',
        'equipe_id' => $equipeId,
        'challenge_id' => $challengeId,
        'repository_url' => 'https://github.com/test/repo',
        'demo_url' => 'https://demo.test.com',
        'status' => 'draft'
    ];
    
    $projetController = new ProjetController();
    $response = testController(function() use ($projetController) {
        $projetController->create();
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Création de projet via API réussie\n";
    } else {
        echo "✗ Erreur lors de la création du projet via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

// Test du contrôleur Evaluation
try {
    echo "\nTest du contrôleur Evaluation...\n";
    $_POST = [
        'projet_id' => $projetId,
        'juge_id' => $userId,
        'score' => 8.5,
        'commentaire' => 'Très bon projet'
    ];
    
    $evaluationController = new EvaluationController();
    $response = testController(function() use ($evaluationController) {
        $evaluationController->create();
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Création d'évaluation via API réussie\n";
    } else {
        echo "✗ Erreur lors de la création de l'évaluation via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
    die("Test terminé avec erreur.\n");
}

echo "\n=== Tests de Validation ===\n\n";

// Tests de validation du modèle User
try {
    echo "Test de validation User...\n";
    $user = new User($db);
    
    // Test email invalide
    try {
        $invalidUserData = $userData;
        $invalidUserData['email'] = 'email_invalide';
        $user->create($invalidUserData);
        echo "✗ La validation de l'email a échoué\n";
    } catch (Exception $e) {
        echo "✓ Email invalide détecté\n";
    }
    
    // Test mot de passe trop court
    try {
        $invalidUserData = $userData;
        $invalidUserData['password'] = '123';
        $user->create($invalidUserData);
        echo "✗ La validation du mot de passe a échoué\n";
    } catch (Exception $e) {
        echo "✓ Mot de passe trop court détecté\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

// Tests de validation du modèle Hackathon
try {
    echo "\nTest de validation Hackathon...\n";
    $hackathon = new Hackathon($db);
    
    // Test dates invalides
    try {
        $invalidData = $hackathonData;
        $invalidData['end_date'] = '2024-03-01';  // Date de fin avant date de début
        $hackathon->create($invalidData);
        echo "✗ La validation des dates a échoué\n";
    } catch (Exception $e) {
        echo "✓ Dates invalides détectées\n";
    }
    
    // Test max_participants invalide
    try {
        $invalidData = $hackathonData;
        $invalidData['max_participants'] = -1;
        $hackathon->create($invalidData);
        echo "✗ La validation du nombre max de participants a échoué\n";
    } catch (Exception $e) {
        echo "✓ Nombre max de participants invalide détecté\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

echo "\n=== Tests des Relations ===\n\n";

// Test de suppression en cascade
try {
    echo "Test de suppression en cascade...\n";
    
    // Créer un nouveau hackathon pour le test
    $hackathon = new Hackathon($db);
    $cascadeHackathonId = $hackathon->create([
        'title' => "Cascade Test {$timestamp}",
        'description' => 'Test de suppression en cascade',
        'start_date' => '2025-04-01 00:00:00',
        'end_date' => '2025-04-03 23:59:59',
        'max_participants' => 50,
        'status' => 'published',
        'created_by' => $userId
    ]);
    
    // Créer une équipe pour ce hackathon
    $equipe = new Equipe($db);
    $cascadeEquipeId = $equipe->create([
        'name' => "Cascade Team {$timestamp}",
        'hackathon_id' => $cascadeHackathonId,
        'created_by' => $userId
    ]);
    
    // Créer un projet pour cette équipe
    $projet = new Projet($db);
    $cascadeProjetId = $projet->create([
        'title' => "Cascade Project {$timestamp}",
        'description' => 'Test de suppression en cascade',
        'equipe_id' => $cascadeEquipeId,
        'challenge_id' => $challengeId,
        'status' => 'draft'
    ]);
    
    // Supprimer le hackathon
    $hackathon->delete($cascadeHackathonId);
    
    // Vérifier que l'équipe a été supprimée
    try {
        $equipe->find($cascadeEquipeId);
        echo "✗ L'équipe n'a pas été supprimée en cascade\n";
    } catch (Exception $e) {
        echo "✓ L'équipe a été supprimée en cascade\n";
    }
    
    // Vérifier que le projet a été supprimé
    try {
        $projet->find($cascadeProjetId);
        echo "✗ Le projet n'a pas été supprimé en cascade\n";
    } catch (Exception $e) {
        echo "✓ Le projet a été supprimé en cascade\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

echo "\n=== Tests des Contrôleurs Manquants ===\n\n";

// Test du contrôleur Auth
try {
    echo "Test du contrôleur Auth...\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Test d'inscription
    $_POST = [
        'username' => "testauth{$timestamp}",
        'email' => "testauth{$timestamp}@example.com",
        'password' => 'password123',
        'full_name' => 'Test Auth User'
    ];
    
    $authController = new AuthController();
    
    ob_start();
    $authController->register();
    $result = ob_get_clean();
    
    $response = json_decode($result, true);
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Inscription via API réussie\n";
    } else {
        echo "✗ Erreur lors de l'inscription via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
    // Test de connexion
    $_POST = [
        'email' => "testauth{$timestamp}@example.com",
        'password' => 'password123'
    ];
    
    ob_start();
    $authController->login();
    $result = ob_get_clean();
    
    $response = json_decode($result, true);
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Connexion via API réussie\n";
    } else {
        echo "✗ Erreur lors de la connexion via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

// Test du contrôleur Notification
try {
    echo "\nTest du contrôleur Notification...\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    $notificationController = new NotificationController();
    
    // Créer une notification
    $_POST = [
        'user_id' => $userId,
        'message' => 'Test notification',
        'type' => 'info'
    ];
    
    $response = testController(function() use ($notificationController) {
        $notificationController->create();
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Création de notification via API réussie\n";
        $notificationId = $response['data']['id'];
        
        // Marquer comme lue
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $response = testController(function() use ($notificationController, $notificationId) {
            $notificationController->markAsRead($notificationId);
        });
        
        if ($response && isset($response['success']) && $response['success']) {
            echo "✓ Notification marquée comme lue via API\n";
        } else {
            echo "✗ Erreur lors du marquage de la notification comme lue via API\n";
            if (isset($response['error'])) {
                echo "  Message: " . $response['error'] . "\n";
            }
        }
    } else {
        echo "✗ Erreur lors de la création de la notification via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

// Test du contrôleur Participant
try {
    echo "\nTest du contrôleur Participant...\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    $participantController = new ParticipantController();
    
    // Inscrire un participant
    $_POST = [
        'hackathon_id' => $hackathonId,
        'user_id' => $userId
    ];
    
    $response = testController(function() use ($participantController, $hackathonId) {
        $participantController->register($hackathonId);
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Inscription participant via API réussie\n";
        $participantId = $response['data']['participant_id'];
        
        // Mettre à jour le statut
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['status'] = 'confirmed';
        
        $response = testController(function() use ($participantController, $participantId) {
            $participantController->updateStatus($participantId);
        });
        
        if ($response && isset($response['success']) && $response['success']) {
            echo "✓ Mise à jour du statut via API réussie\n";
        } else {
            echo "✗ Erreur lors de la mise à jour du statut via API\n";
            if (isset($response['error'])) {
                echo "  Message: " . $response['error'] . "\n";
            }
        }
    } else {
        echo "✗ Erreur lors de l'inscription du participant via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

// Test du contrôleur Ressource
try {
    echo "\nTest du contrôleur Ressource...\n";
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    $ressourceController = new RessourceController();
    
    // Créer une ressource
    $_POST = [
        'hackathon_id' => $hackathonId,
        'title' => 'Test Ressource',
        'description' => 'Une ressource de test',
        'url' => 'https://example.com/resource',
        'type' => 'document'
    ];
    
    $response = testController(function() use ($ressourceController) {
        $ressourceController->create();
    });
    
    if ($response && isset($response['success']) && $response['success']) {
        echo "✓ Création de ressource via API réussie\n";
        $ressourceId = $response['data']['id'];
        
        // Mettre à jour la ressource
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'title' => 'Test Ressource (modifié)',
            'description' => 'Une ressource de test modifiée',
            'url' => 'https://example.com/resource-modified',
            'type' => 'document'
        ];
        
        $response = testController(function() use ($ressourceController, $ressourceId) {
            $ressourceController->update($ressourceId);
        });
        
        if ($response && isset($response['success']) && $response['success']) {
            echo "✓ Mise à jour de ressource via API réussie\n";
            
            // Supprimer la ressource
            $_SERVER['REQUEST_METHOD'] = 'POST';
            
            $response = testController(function() use ($ressourceController, $ressourceId) {
                $ressourceController->delete($ressourceId);
            });
            
            if ($response && isset($response['success']) && $response['success']) {
                echo "✓ Suppression de ressource via API réussie\n";
            } else {
                echo "✗ Erreur lors de la suppression de la ressource via API\n";
                if (isset($response['error'])) {
                    echo "  Message: " . $response['error'] . "\n";
                }
            }
        } else {
            echo "✗ Erreur lors de la mise à jour de la ressource via API\n";
            if (isset($response['error'])) {
                echo "  Message: " . $response['error'] . "\n";
            }
        }
    } else {
        echo "✗ Erreur lors de la création de la ressource via API\n";
        if (isset($response['error'])) {
            echo "  Message: " . $response['error'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Erreur : " . $e->getMessage() . "\n";
}

// Nettoyage
try {
    echo "\nNettoyage de la base de données...\n";
    
    // Supprimer dans l'ordre pour respecter les contraintes de clé étrangère
    $evaluation->delete($evaluationId);
    $projet->delete($projetId);
    $challenge->delete($challengeId);
    $equipe->delete($equipeId);
    $hackathon->delete($hackathonId);
    $user->delete($userId);
    
    echo "✓ Nettoyage terminé avec succès\n";
} catch (Exception $e) {
    echo "✗ Erreur lors du nettoyage : " . $e->getMessage() . "\n";
}
