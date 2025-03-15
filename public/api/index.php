<?php

use Auth\Controller\AuthController;
use Auth\Controller\HackathonController;
use Auth\Controller\EquipeController;
use Auth\Controller\EquipeMembreController;
use Auth\Controller\NotificationController;

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/cors.php';

// Configurer CORS pour toutes les requêtes API
configureCors();

// Définir la base URL de l'application (à adapter selon la structure de votre projet)
define('BASE_URL', '/HACKATHON_ESGIS/public'); // Modifiez en fonction du chemin d'accès à votre application

// Récupérer la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD']; // Récupère la méthode HTTP (GET, POST, PUT, DELETE)
$uri = $_SERVER['REQUEST_URI']; // Récupère l'URI complète
$scriptName = dirname($_SERVER['SCRIPT_NAME']); // Chemin du script

// Nettoyage de l'URI pour enlever le chemin du script
$uri = str_replace($scriptName, '', $uri);
$uri = trim($uri, '/');

// Découpage en segments
$segments = explode('/', $uri);

// Debug pour voir les segments
// var_dump($segments);

// Router API
try {
    switch ($segments[0]) {
        case 'auth':
            require_once __DIR__ . '/../../backend/controllers/AuthController.php';
            if (!file_exists(__DIR__ . '/../../backend/controllers/AuthController.php')) {
                die(json_encode(["error" => true, "message" => "Fichier AuthController introuvable"]));
            }
            
            $controller = new AuthController();

            switch ($segments[1] ?? '') {
                case 'login':
                    if ($method === 'POST') {
                        try {
                            $controller->login();
                            // La redirection est gérée dans le contrôleur
                            exit();
                        } catch (Exception $e) {
                            // Gérer l'erreur, par exemple en affichant un message d'erreur
                            error_log($e->getMessage());
                            // Rediriger vers la page de connexion avec un message d'erreur
                            header("Location: " . BASE_URL . "/auth?error=" . urlencode($e->getMessage()));
                            exit();
                        }
                    }
                    break;

                case 'register':
                    if ($method === 'POST') {
                        try {
                            $controller->register();
                            // Rediriger vers le profil après l'inscription
                            header("Location: " . BASE_URL . "/profile");
                            exit();
                        } catch (Exception $e) {
                            error_log($e->getMessage());
                            // Rediriger vers la page d'inscription avec un message d'erreur
                            header("Location: " . BASE_URL . "/register?error=" . urlencode($e->getMessage()));
                            exit();
                        }
                    }
                    break;

                case 'logout':
                    if ($method === 'POST') {
                        $controller->logout();
                        // Rediriger vers la page de connexion après la déconnexion
                        header("Location: " . BASE_URL . "/login");
                        exit();
                    }
                    break;

                default:
                    throw new Exception('Endpoint non trouvé', 404);
            }
            break;

        case 'hackathons':
            require_once __DIR__ . '/../../backend/controllers/HackathonController.php';
            $controller = new HackathonController();

            if (empty($segments[1])) {
                if ($method === 'GET') {
                    $controller->index();
                } elseif ($method === 'POST') {
                    $controller->create();
                }
            } else {
                $id = $segments[1];
                switch ($segments[2] ?? '') {
                    case '':
                        if ($method === 'GET') {
                            $controller->show($id);
                        } elseif ($method === 'PUT') {
                            $controller->update($id);
                        } elseif ($method === 'DELETE') {
                            $controller->delete($id);
                        }
                        break;

                    case 'participants':
                        require_once __DIR__ . '/../../backend/controllers/ParticipantController.php';
                        $participantController = new ParticipantController();
                        
                        if ($method === 'GET') {
                            $participantController->index($id);
                        } elseif ($method === 'POST') {
                            $participantController->register($id);
                        }
                        break;

                    default:
                        throw new Exception('Endpoint non trouvé', 404);
                }
            }
            break;

        case 'equipes':
            require_once __DIR__ . '/../../backend/controllers/EquipeController.php';
            $controller = new EquipeController();

            if (empty($segments[1])) {
                if ($method === 'GET') {
                    $controller->index();
                } elseif ($method === 'POST') {
                    $controller->create();
                }
            } else {
                $id = $segments[1];
                switch ($segments[2] ?? '') {
                    case '':
                        if ($method === 'GET') {
                            $controller->show($id);
                        } elseif ($method === 'PUT') {
                            $controller->update($id);
                        } elseif ($method === 'DELETE') {
                            $controller->delete($id);
                        }
                        break;

                    case 'membres':
                        require_once __DIR__ . '/../../backend/controllers/EquipeMembreController.php';
                        $membreController = new EquipeMembreController();
                        
                        if ($method === 'GET') {
                            $membreController->index($id);
                        } elseif ($method === 'POST') {
                            $membreController->add($id);
                        }
                        break;

                    default:
                        throw new Exception('Endpoint non trouvé', 404);
                }
            }
            break;

        case 'notifications':
            require_once __DIR__ . '/../../backend/controllers/NotificationController.php';
            $controller = new NotificationController();

            if (empty($segments[1])) {
                if ($method === 'GET') {
                    $controller->index();
                }
            } else {
                switch ($segments[1]) {
                    case 'unread-count':
                        if ($method === 'GET') {
                            $controller->getUnreadCount();
                        }
                        break;

                    case 'mark-all-read':
                        if ($method === 'POST') {
                            $controller->markAllAsRead();
                        }
                        break;

                    default:
                        $id = $segments[1];
                        if ($method === 'POST') {
                            switch ($segments[2] ?? '') {
                                case 'read':
                                    $controller->markAsRead($id);
                                    break;
                                case 'delete':
                                    $controller->delete($id);
                                    break;
                                default:
                                    throw new Exception('Action non trouvée', 404);
                            }
                        }
                }
            }
            break;

        default:
            throw new Exception('Endpoint non trouvé', 404);
    }
} catch (Exception $e) {
    handleApiError($e, $e->getCode() ?: 500);
}
