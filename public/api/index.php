<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/cors.php';

// Configurer CORS pour toutes les requêtes API
configureCors();

// Récupérer la méthode HTTP et l'URL
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api/', '', $uri);
$segments = explode('/', $uri);

// Router API
try {
    switch ($segments[0]) {
        case 'auth':
            require_once __DIR__ . '/../../backend/controllers/AuthController.php';
            $controller = new AuthController();

            switch ($segments[1] ?? '') {
                case 'login':
                    if ($method === 'POST') {
                        $controller->login();
                    }
                    break;

                case 'register':
                    if ($method === 'POST') {
                        $controller->register();
                    }
                    break;

                case 'logout':
                    if ($method === 'POST') {
                        $controller->logout();
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
