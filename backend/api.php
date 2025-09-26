<?php
// Protection : uniquement requêtes AJAX
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    !isAjaxRequest()
) {
    header('Location: /error403'); // Redirection vers une page propre
    exit;
}

use Auth\Model\Database;
use Auth\Controller\AuthController;
use Auth\Controller\HackathonController;
use Auth\Controller\TeamController;
use Auth\Controller\ProjectController;
use Auth\Controller\UserController;
use Auth\Controller\NotificationController;
use Auth\Controller\ChallengeController;
use Auth\Controller\EvaluationController;
use Auth\Controller\AdminController;
use Auth\Model\TokenManager;
use Piston\PistonRequest;
use Piston\PistonExecutor;
use Auth\Controller\ParticipantController;
use Auth\Controller\ScoreController;
use Auth\Controller\PhaseController;
use Auth\Service\InputInspectionService;

try {

    // Inclure le fichier autoload de Composer pour charger les variables d'environnement
    require_once __DIR__ . '/../vendor/autoload.php';

    // Charger les variables d'environnement
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Inclure une seule fois le fichier de configuration
    if (!defined('CONFIG_INCLUDED')) {
        require_once __DIR__ . '/includes/config.php';
    }

    // Inclure les fichiers contenant des fonctions
    if (!defined('FUNCTIONS_INCLUDED')) {
        require_once __DIR__ . '/includes/functions.php';
    }


    // Inclure les classes seulement si elles n'existent pas déjà
    $files = [
        'AdminController'     => '/controllers/AdminController.php',
        'AuthController'      => '/controllers/AuthController.php',
        'Auth\\Service\\ChallengeValidationService' => '/services/ChallengeValidationService.php',
        'ChallengeController' => '/controllers/ChallengeController.php',
        'Controller'          => '/controllers/Controller.php',
        'Database'            => '/models/Database.php',
        'EvaluationController' => '/controllers/EvaluationController.php',
        'HackathonController' => '/controllers/HackathonController.php',
        'NotificationController' => '/controllers/NotificationController.php',
        'ParticipantController' => '/controllers/ParticipantController.php',
        'PistonRequest'       => '/services/PistonRequest.php',
        'PistonExecutor'      => '/services/PistonExecutor.php',
        'PistonResponse'      => '/services/PistonResponse.php',
        'PhaseController'     => '/controllers/PhaseController.php',
        'ProjectController'   => '/controllers/ProjectController.php',
        'ScoreController'     => '/controllers/ScoreController.php',
        'TeamController'      => '/controllers/TeamController.php',
        'TokenManager'        => '/models/TokenManager.php',
        'UserController'      => '/controllers/UserController.php',
        'InputInspectionService' => '/services/InputInspectionService.php',
    ];

    foreach ($files as $class => $path) {
        if (file_exists(__DIR__ . $path) && !class_exists($class)) {
            require_once __DIR__ . $path;
        }
    }

    // Configurer CORS pour toutes les requêtes API
    configureCors();

    // Initialisation de la base de données
    $db = Database::getInstance()->getConnection();

    $key = $_ENV['JWT_SECRET'] ?? 'your-secret-key';

    // Pour les requêtes OPTIONS, renvoyer directement une réponse
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    // Récupération de la méthode HTTP et de l'URL
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = str_replace('/api/', '/', $uri); // Nettoyer l'URI
    $request = explode('/', trim($uri, '/'));

    // Extraction des composants de l'URL
    // /endpoint/id/action
    $endpoint = $request[0] ?? '';
    $id = $request[1] ?? null;
    $action = $request[2] ?? null;



    // Lecture des données du corps de la requête
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    // Si c'est une requête POST et que le JSON n'est pas valide, utiliser $_POST
    if ($input === null && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = $_POST;
    }

    $isAuth = $method === 'POST' && in_array($id, ['check-email', 'check-username', 'login', 'register']);
    $inputInspectionService = new InputInspectionService();
    // Inspection et sanitation des entrées utilisateur (après fallback éventuel vers $_POST)
    try {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $input = $inputInspectionService->inspectInput($input, [
            'method' => $method,
            'headers' => $headers,
            'raw' => $rawInput,
            'max_body_bytes' => 1024 * 1024,
        ], $isAuth);
    } catch (Exception $e) {
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            http_response_code($e->getCode() ?: 400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } else {
            setFlashMessage('error', 'Entrée invalide', $e->getMessage());
            header('Location: ' . '/');
        }
        exit();
    }

    // Initialisation du gestionnaire de token
    $tokenManager = new TokenManager($db);

    switch ($endpoint) {
        case 'auth':
            $controller = new AuthController($db);
            switch ($request[1] ?? '') {
                case 'check':
                    $controller->checkAuth();
                    break;

                case 'login':
                    try {
                        $controller->login($input);
                    } catch (Exception $e) {
                        if (isAjaxRequest()) {
                            header('Content-Type: application/json');
                            http_response_code(400);
                            echo json_encode(['error' => $e->getMessage()]);
                        } else {
                            setFlashMessage('error', 'Connexion echouée', $e->getMessage());
                            header('Location: ' . '/auth');
                        }
                        exit();
                    }
                    break;

                case 'check-email':
                    if ($method === 'POST') {
                        $email = $input['email'] ?? '';
                        $controller->checkEmail($email);
                    }
                    break;

                case 'check-username':
                    if ($method === 'POST') {
                        $username = $input['username'] ?? '';
                        $controller->checkUsername($username);
                    }
                    break;

                case 'register':
                    try {
                        $controller->register($input);
                    } catch (Exception $e) {
                        if (isAjaxRequest()) {
                            header('Content-Type: application/json');
                            http_response_code(400);
                            echo json_encode(['error' => $e->getMessage()]);
                        } else
                            setFlashMessage('error', 'Inscription echouée', $e->getMessage());

                        //redirection vers la page d'inscription
                        header('Location: ' . '/auth');
                        exit();
                    }
                    break;
                case 'logout':
                    $controller->logout();
                    break;
                case 'forgot-password':
                    $controller->forgotPassword();
                    break;
                case 'reset-password':
                    $controller->resetPassword();
                    break;
                default:
                    throw new Exception('Endpoint non trouvé.', 404);
            }
            break;

        case 'participants':
            $controller = new ParticipantController($db, $tokenManager);
            // Route /api/participant
            if ($method !== 'POST') {
                throw new Exception('Méthode non autorisée', 405);
            }
            if ($id && $action === 'register-team' && $method === 'POST') {
                // /api/participants/{hackathon_id}/register-team
                $controller->registerTeam((int)$id, $input);
            }
            break;

        case 'phases':
            $controller = new PhaseController($db, $tokenManager);
            if ($method !== 'GET') {
                throw new Exception('Méthode non autorisée', 405);
            }

            if ($id === 'active-phase') {
                $controller->getActivePhase($id, $userId);
            } elseif ($id === 'all-phases') {
                $controller->getAllPhases($id);
            } elseif (is_numeric($id)) {
                $controller->get($id);
            }
            break;
        case 'check-qualification':
            $controller = new HackathonController($db, $tokenManager);
            
            if ($method !== 'POST') {
                throw new Exception('Méthode non autorisée', 405);
            }
            
            // Vérifier que les paramètres nécessaires sont présents
            if (!isset($input['hackathon_id']) || !isset($input['phase_id'])) {
                jsonResponse([
                    'success' => false,
                    'error' => 'Les paramètres hackathon_id et phase_id sont requis'
                ], 400);
            }
            
            // Appeler la méthode du contrôleur
            $controller->checkQualification($input['hackathon_id'], $input['phase_id']);
            break;
        case 'check-participation':
            // Route /api/check-participation
            $controller = new HackathonController($db, $tokenManager);

            if ($method !== 'POST') {
                throw new Exception('Méthode non autorisée', 405);
            }

            // Vérification du token JWT
            try {
                $token = getBearerToken();
                if (!$token) {
                    throw new Exception('Token manquant', 401);
                }

                // Valider le token et récupérer l'utilisateur
                $tokenValidation = $tokenManager->validateToken($token);
                if (!$tokenValidation['valid']) {
                    throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? ''), 401);
                }

                $userId = $tokenValidation['user_id'];
            } catch (Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 401);
                exit();
            }

            // Vérifier la participation
            try {
                $hackathon_id = isset($input['hackathon_id']) ? (int)$input['hackathon_id'] : 0;
                if (!$hackathon_id) {
                    throw new Exception('ID du hackathon manquant', 400);
                }

                $isParticipant = $controller->checkParticipation($userId, $hackathon_id);
                jsonResponse([
                    'success' => $isParticipant['success'],
                    'message' => $isParticipant['message']
                ], 200);
            } catch (Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 400);
            }
            break;
        case 'scores':

            $controller = new ScoreController($db, $tokenManager);

            if ($method === 'GET' && is_numeric($id) && isset($id)) {
                // Route /api/scores/{hackathon_id}/{action}
                if (isset($action)) {

                    switch ($action) {
                        case 'phases':
                            $controller->getPhases((int)$id);
                            break;
                        case 'leaderboard':
                            // Route /api/scores/{hackathon_id}/{action}/{phase_id}
                            if (isset($request[3]) && is_numeric($request[3])) {
                                $controller->getLeaderboard((int)$id, (int)$request[3]);
                            } else {
                                return jsonResponse([
                                    'success' => false,
                                    'error' => 'Phase ID requis.'
                                ], 400);
                            }
                            break;
                        default:
                            jsonResponse([
                                'success' => false,
                                'error' => 'Méthode non autorisée !!!!'
                            ], 405);
                    }
                } else {
                    jsonResponse([
                        'success' => false,
                        'error' => 'Hackathon ID et phase ID requis.' . print_r($request, true)
                    ], 400);
                }
            } 
            // TODO : Instruction interdite aux participants
            // elseif ($method === 'POST' && is_numeric($id) && isset($id)) {
            //     // Route /api/scores/{hackathon_id}/{phase_id}
            //     if (isset($id) && is_numeric($id) && isset($action) && is_numeric($action) && isset($input['team_id']) && is_numeric($input['team_id'])) {
            //         $controller->updateScore($input['team_id'], (int)$id, (int)$action, $input);
            //     } else {
            //         jsonResponse([
            //             'success' => false,
            //             'error' => 'Hackathon ID et phase ID requis.'
            //         ], 400);
            //     }
            // }
            break;
        case 'users':
            $controller = new UserController($db, $tokenManager);
            // Vérification du token JWT pour toutes les routes sauf OPTIONS
            if ($method !== 'OPTIONS') {
                try {
                    $token = $controller->getBearerToken();
                    if (!$token) {
                        throw new Exception('Token manquant', 401);
                    }

                    // Valider le token et récupérer l'utilisateur
                    $tokenValidation = $controller->validateToken($token);
                    if (!$tokenValidation['valid']) {
                        throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? ''), 401);
                    }

                    // Stocker l'ID utilisateur pour les vérifications ultérieures
                    $currentUserId = $tokenValidation['user_id'];
                } catch (Exception $e) {
                    if (isAjaxRequest()) {
                        jsonResponse([
                            'success' => false,
                            'error' => 'Erreur au niveau de l\'API ' . $e->getMessage()
                        ], $e->getCode() ?: 401);
                    } else {
                        setFlashMessage('error', 'Erreur de connexion', $e->getMessage());
                        header('Location: ' . '/user');
                        exit();
                    }
                }
            }

            if (!is_numeric($id)) {
                // Route /api/users
                switch ($method) {
                    case 'GET':
                        if ($request[1] === 'me') {
                            // Vérifier l'authentification
                            if (!$currentUserId) {
                                jsonResponse(['error' => 'Non authentifié - api'], 401);
                                return;
                            }

                            // Récupérer les informations de l'utilisateur
                            try {
                                $controller->get($currentUserId);
                                $controller->getUserStats($currentUserId);
                            } catch (Exception $e) {
                                if (isAjaxRequest()) {
                                    jsonResponse([
                                        'success' => false,
                                        'error' => 'Erreur au niveau de l\'API ' . $e->getMessage()
                                    ], $e->getCode() ?: 404);
                                } else {
                                    setFlashMessage('error', 'Erreur de connexion', $e->getMessage());
                                    header('Location: ' . '/user');
                                    exit();
                                }
                            }
                        }else if ($request[1] === 'refresh-csrf-token') {
                            // GET /api/users/refresh-csrf-token
                            $controller->refreshCsrfToken();
                        }

                        break;

                    case 'OPTIONS':
                        // Gestion des pré-vol CORS
                        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
                        header('Access-Control-Allow-Headers: Authorization, Content-Type');
                        exit;

                    default:
                        jsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/{id}
                if ($action === null) {
                    switch ($method) {
                        case 'GET':
                            // Un utilisateur peut voir son propre profil ou un admin peut voir n'importe quel profil
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                if (isAjaxRequest()) {
                                    jsonResponse(['success' => false, 'error' => "Accès non autorisé "], 403);
                                    return;
                                }
                                setFlashMessage('error', 'Erreur de connexion', "Accès non autorisé ");
                                header('Location: ' . '/user');
                                exit();
                            }
                            $controller->get($id);
                            break;

                        case 'POST':
                        case 'PUT':
                            // Un utilisateur peut mettre à jour son propre profil ou un admin peut mettre à jour n'importe quel profil
                            if ($currentUserId != $id) {
                                if (isAjaxRequest()) {
                                    jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                                    return;
                                }
                                setFlashMessage('error', 'Erreur de mise à jour', 'Accès non autorisé');
                                header('Location: ' . '/user');
                                exit();
                            }
                            $controller->update($id, $token);
                            break;

                        default:
                            if (isAjaxRequest()) {
                                jsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
                                return;
                            }
                            setFlashMessage('error', 'Erreur de connexion', 'Méthode non autorisée');
                            header('Location: ' . '/user');
                            exit();
                    }
                } else {
                    // Routes avec action spécifique /api/users/{id}/{action}
                    switch ($action) {
                        case 'password':
                            // Un utilisateur peut changer son propre mot de passe
                            if ($currentUserId != $id) {
                                if (isAjaxRequest()) {
                                    jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                                    return;
                                }
                                setFlashMessage('error', 'Erreur de modification', 'Accès non autorisé');
                                header('Location: ' . '/user');
                                exit();
                            }
                            $controller->updatePassword($id, $token);
                            break;

                        case 'stats':
                            // Un utilisateur peut voir ses propres stats ou un admin peut voir n'importe quelles stats
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                if (isAjaxRequest()) {
                                    jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                                    return;
                                }
                                setFlashMessage('error', 'Erreur de connexion', 'Accès non autorisé');
                                header('Location: ' . '/user');
                                exit();
                            }
                            $controller->getUserStats($id);
                            break;

                        case 'hackathons':
                            // Un utilisateur peut voir ses propres hackathons ou un admin peut voir n'importe quels hackathons
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getUserHackathons($id, $token);
                            break;

                        case 'teams':
                            // GET /api/users/{id}/teams
                            // Un utilisateur peut voir sa propre équipe ou un admin peut voir n'importe quelles équipes
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                if (isAjaxRequest()) {
                                    jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                                    return;
                                }
                                setFlashMessage('error', 'Erreur de connexion', 'Accès non autorisé');
                                header('Location: ' . '/user');
                                exit();
                            }
                            $controller->getUserTeams($id);
                            break;

                        case 'ongoing-challenges':
                            // GET /api/users/{id}/ongoing-challenges
                            // Un utilisateur peut voir ses propres défis en cours ou un admin peut voir ceux des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            // Before calling $controller->getOngoingChallenges($id);
                            if (!method_exists($controller, 'getOngoingChallenges')) {
                                jsonResponse(['success' => false, 'error' => 'Endpoint not implemented'], 501);
                                return;
                            }
                            $controller->getOngoingChallenges($id, $token);
                            break;
                        case 'current-challenges':
                            // GET /api/users/{id}/current-challenges
                            // Vérification d'autorisation (similaire à ongoing-challenges)
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            // Route GET /api/users/{id}/current-challenges
                            $controller->getCurrentChallenges($id, $token);
                            break;
                        case 'current-hackathons':
                            // GET /api/users/{id}/current-hackathons
                            // Un utilisateur peut voir ses propres hackathons ou un admin peut voir n'importe quels hackathons
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getCurrentHackathons($id, $token);
                            break;

                        case 'recent-activities':
                            // GET /api/users/{id}/recent-activities
                            // Un utilisateur peut voir sa propre activité récente ou un admin peut voir celle des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getRecentActivities($id, $token);
                            break;
                        case 'next-event':
                            // GET /api/users/{id}/next-event
                            // Un utilisateur peut voir sa propre activité récente ou un admin peut voir celle des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getNextEvent($id, $token);
                            break;
                        case 'notifications':
                            // GET /api/users/{id}/notifications
                            // Un utilisateur peut voir sa propre activité récente ou un admin peut voir celle des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getNotifications($id, $token);
                            break;
                        case 'completed-challenges':
                            // GET /api/users/{id}/completed-challenges
                            // Vérification d'autorisation
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getCompletedChallenges($id, $token);
                            break;
                        case 'all-activities':
                            // GET /api/users/{id}/all-activities
                            // Vérification d'autorisation
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getAllActivities($id, $token);
                            break;
                        default:
                            if (isAjaxRequest()) {
                                jsonResponse(['success' => false, 'error' => 'Action non reconnue'], 404);
                                return;
                            }
                            setFlashMessage('error', 'Erreur de connexion', 'Action non reconnue');
                            header('Location: ' . '/user');
                            exit();
                    }
                }
            } else {
                jsonResponse(['success' => false, 'error' => 'Identifiant invalide: ' . $id], 400);
            }
            break;
        case 'hackers':
            $controller = new UserController($db, $tokenManager); // Ou créez un HackerController si nécessaire
            if ($id === 'top' && $method === 'GET') {
                $controller->getTopHackers();
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint non trouvé ou méthode non autorisée pour /hackers/top']);
            }
            break;

        case 'hackathons':
            // Route /api/hackathons
            $controller = new HackathonController($db, $tokenManager);
            if ($id === null) {
                // Route /api/hackathons
                if ($method === 'GET') {
                    // GET /api/hackathons
                    $controller->getAll();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/hackathons/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        // GET /api/hackathons/{id}
                        $controller->get($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    // Route /api/hackathons/{id}/{action}
                    switch ($action) {
                        case 'active-phase':
                            $controller->getActivePhase($id);
                            break;
                        case 'teams':
                            $controller->getTeams($id);
                            break;
                        case 'projects':
                            $controller->getProjects($id);
                            break;
                        case 'stats':
                            $controller->getStats($id);
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } elseif ($id === 'active') {
                $controller->getActive();
            } elseif ($id === 'past') {
                $controller->getPast();
            } elseif ($id === 'future') {
                $controller->getFuture();
            } elseif ($id === 'public') {
                $controller->getPublicAll();
            } else {
                throw new Exception('ID non valide pour /hackathons', 400);
            }
            break;

        case 'teams':
            $controller = new TeamController($db, $tokenManager);
            error_log("Route teams appelée: uri=$uri, id=" . var_export($id, true) . ", action=" . var_export($action, true));
            if ($id === null) {
                // Route /api/teams
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create($input);
                } else {
                    throw new Exception('Méthode non autorisée');
                }
            } elseif ($id === 'join' && $method === 'POST') {
                // Route /api/teams/join
                error_log("Route join appelée avec input: " . print_r($input, true));
                $controller->joinTeamViaCode($input['invitation_code'] ?? '');
            } elseif ($id === 'request' && $method === 'POST') {
                // Route /api/teams/request
                error_log("Route request appelée avec input: " . print_r($input, true));
                $teamName = $input['team_name'] ?? $input['team_name'] ?? null;
                error_log("teamName extrait: " . var_export($teamName, true));

                if (!$teamName) {
                    jsonResponse(['success' => false, 'error' => 'team_name manquant']);
                    return;
                }

                // Nettoyer teamName
                $teamName = trim($teamName);

                // Convertir team_name en team_id
                $query = "SELECT id FROM teams WHERE name = :name LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':name', $teamName, PDO::PARAM_STR);
                $stmt->execute();
                $team = $stmt->fetch(PDO::FETCH_ASSOC);

                error_log("Résultat de la recherche pour teamName $teamName: " . var_export($team, true));

                if (!$team) {
                    jsonResponse(['success' => false, 'error' => 'Équipe non trouvée']);
                    return;
                }

                $teamId = $team['id'];
                error_log("teamId final: " . var_export($teamId, true));

                $controller->teamRequest((int)$teamId);
            } elseif (is_numeric($id)) {
                // Route /api/teams/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée');
                    }
                } else {
                    switch ($action) {
                        case 'members':
                            //Route /api/teams/{id}/members
                            if ($method === 'GET' && !isset($request[3])) {
                                $controller->getMembers($id);
                            } elseif (isset($request[3]) && $request[3] === 'add') {
                                //Route /api/teams/{id}/members/add
                                $controller->addMember($id);
                            } elseif (isset($request[3]) && $request[3] === 'remove') {
                                //Route /api/teams/{id}/members/remove
                                $controller->removeMember($id);
                            } elseif (isset($request[3]) && $request[3] === 'requests') {
                                //Route /api/teams/{id}/members/requests
                                $controller->getAllTeamRequests($id);
                            } else {
                                throw new Exception('Action non reconnue');
                            }
                            break;
                        case 'leader':
                            //Route /api/teams/{id}/leader
                            error_log("Route leader appelée pour teamId: $id, request[3]: " . ($request[3] ?? 'null'));
                            if (isset($request[3])) {
                                if ($request[3] === 'change') {
                                    //Route /api/teams/{id}/leader/change
                                    if ($method === 'POST') {
                                        $controller->changeLeader($id);
                                    } else {
                                        throw new Exception('Méthode non autorisée');
                                    }
                                } elseif ($request[3] === 'accept') {
                                    //Route /api/teams/{id}/leader/accept
                                    if ($method === 'POST') {
                                        $token = getBearerToken();
                                        if (!$token) {
                                            throw new Exception('Token manquant');
                                        }
                                        $tokenValidation = $tokenManager->validateToken($token);
                                        if (!$tokenValidation['valid']) {
                                            throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? ''));
                                        }
                                        $userId = $tokenValidation['user_id'];
                                        error_log("Utilisateur authentifié: userId $userId");
                                        $controller->acceptRequest($id, $input['user_id'] ?? null);
                                    } else {
                                        throw new Exception('Méthode non autorisée');
                                    }
                                } elseif ($request[3] === 'reject') {
                                    //Route /api/teams/{id}/leader/reject
                                    if ($method === 'POST') {
                                        $token = getBearerToken();
                                        if (!$token) {
                                            throw new Exception('Token manquant');
                                        }
                                        $tokenValidation = $tokenManager->validateToken($token);
                                        if (!$tokenValidation['valid']) {
                                            throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? ''));
                                        }
                                        $userId = $tokenValidation['user_id'];
                                        error_log("Utilisateur authentifié: userId $userId");
                                        $controller->rejectRequest($id, $input['user_id'] ?? null);
                                    } else {
                                        throw new Exception('Méthode non autorisée');
                                    }
                                } else {
                                    throw new Exception('Action non reconnue .');
                                }
                            } else {
                                throw new Exception('Action non reconnue !');
                            }
                            break;
                        case 'invit':
                            //Route /api/teams/{id}/invit
                            // Requete pour mettre a jour le code d'invitation
                            error_log("Route invit appelée pour teamId: $id, request[3]: " . ($request[3] ?? 'null'));
                            if (isset($request[3]) && $request[3] === 'update') {
                                // Route /api/teams/{id}/invit/update
                                if ($method === 'POST') {
                                    error_log("Appel de updateTeamCode pour teamId: $id");
                                    $token = getBearerToken();
                                    if (!$token) {
                                        throw new Exception('Token manquant');
                                    }
                                    $tokenValidation = $tokenManager->validateToken($token);
                                    if (!$tokenValidation['valid']) {
                                        throw new Exception('Token invalide: ' . ($tokenValidation['error'] ?? ''));
                                    }
                                    $userId = $tokenValidation['user_id'];
                                    error_log("Utilisateur authentifié: userId $userId");
                                    $controller->updateTeamCode($id);
                                } else {
                                    throw new Exception('Méthode non autorisée');
                                }
                            } else {
                                throw new Exception('Action non reconnue');
                            }
                            break;
                        case 'join':
                            //Route /api/teams/{id}/join
                            if ($method === 'POST') {
                                error_log("Route join appelée avec input: " . print_r($input, true));
                                $controller->joinTeamViaCode($input['invitation_code'] ?? $_POST['invitation_code'] ?? '');
                            } else {
                                throw new Exception('Méthode non autorisée');
                            }
                            break;
                        default:
                            throw new Exception('Action non reconnue');
                    }
                }
            } elseif ($id === 'hackathon' && is_numeric($action)) {
                // GET /api/teams/hackathon/{hackathon_id} - Récupère toutes les équipes d'un hackathon
                $controller->getByHackathon($action);
            } elseif ($id === 'user') {
                // GET /api/teams/user/{user_id} - Récupère l'équipe de l'utilisateur
                $controller->getByUser($action);
            } else {
                throw new Exception('ID non valide pour /teams');
            }
            break;
        case 'challenges':
            $controller = new ChallengeController($db, $tokenManager);
            if ($id === 'algo') {
                // GET /api/challenges/algo/{hackathon_id}/{user_id}/{phase_id}
                if ($method === 'GET' && isset($request[2]) && is_numeric($request[2]) && isset($request[3]) && is_numeric($request[3]) && isset($request[4]) && is_numeric($request[4])) {
                    $controller->getChallengeAlgo($request[2], $request[3], $request[4]);
                } else {
                    throw new Exception('Méthode non autorisée ou paramètres invalides', 400);
                }
            } elseif ($id === 'dev') {
                // GET /api/challenges/dev/{user_id}/{c_id} or code_name
                if ($method === 'GET' && isset($request[2]) && is_numeric($request[2]) && isset($request[3]) && (is_numeric($request[3]) || is_string($request[3]))) {
                    $controller->findChallengeDev($request[2], $request[3]);
                } elseif ($method === 'GET' && isset($request[2]) && is_numeric($request[2]) && isset($request[3]) && is_numeric($request[3]) && isset($request[4]) && is_numeric($request[4])) {
                    // GET /api/challenges/dev/{hackathon_id}/{user_id}/{phase_id}
                    $controller->getChallengesDev($request[2], $request[3], $request[4]);
                }
                // POST /api/challenges/dev/{hackathon_id}/{user_id} - pour validation et soumission algorithmique
                elseif ($method === 'POST' && isset($request[2]) && is_numeric($request[2]) && isset($request[3]) && is_numeric($request[3])) {
                    // Vérifier si c'est une validation ou soumission
                    $action = $input['action'] ?? '';
                    $challengeId = $input['challenge_id'] ?? null;

                    // POST /api/challenges/dev/{hackathon_id}/{user_id}
                    if ($action === 'validate' && $challengeId) {
                        $controller->validateCode($challengeId, $request[3], $request[2]);
                    } elseif ($action === 'submit' && $challengeId) {
                        $controller->submitAlgorithmic($challengeId, $input);
                    } else {
                        throw new Exception('Action non reconnue pour les défis dev', 400);
                    }
                } else {
                    throw new Exception('Méthode non autorisée ou paramètres invalides', 400);
                }
            } elseif ($id === 'ctf') {
                // GET /api/challenges/ctf/{hackathon_id}/{user_id}
                if ($method === 'GET' && isset($action) && is_numeric($action) && isset($request[3]) && is_numeric($request[3]) && isset($request[4]) && is_numeric($request[4])) {

                    $controller->getChallengesCTF($action, $request[3], $request[4]);
                } else if ($method === 'POST' && isset($action) && $action === 'submit' && isset($request[3]) && is_numeric($request[3])) {

                    if (!isset($request[3]) || !is_numeric($request[3]) || !isset($input['phase_id']) || !is_numeric($input['phase_id'])) {
                        throw new Exception('ID utilisateur ou phase manquants', 400);
                    }

                    // POST /api/challenges/ctf/submit/{user_id}
                    $controller->submitChallengeCTF($request[3], $input, $input['phase_id']);
                } else {
                    throw new Exception('Méthode non autorisée ou paramètres invalides', 400);
                }
            } elseif ($id === 'submissions' && is_numeric($action)) {
                // GET /api/challenges/submissions/{submission_id}/{user_id}
                if ($method === 'GET') {
                    $controller->getSubmissionResults($action, $request[3]);
                } else {
                    throw new Exception('Méthode non autorisée pour les soumissions', 405);
                }
            } elseif ($id === 'solves') {
                // GET /api/challenges/solves
                if ($method === 'GET') {
                    // GET /api/challenges/solves
                    $controller->getSolvesCount();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // GET /api/challenges/{id}
                switch ($action) {
                    case null:
                        if ($method === 'GET') {
                            // GET /api/challenges/{id}
                            $controller->get($id);
                        } else {
                            throw new Exception('Méthode non autorisée', 405);
                        }
                        break;
                    default:
                        throw new Exception('Action non reconnue', 400);
                }
            }
            // TODO: insruction non valide pour l'instant
            // elseif ($id === 'hackathon' && is_numeric($action)) {
            //     // GET /api/challenges/hackathon/{id}
            //     $controller->getByHackathon($action);
            // }
            else {
                throw new Exception('ID non valide pour challenges', 400);
            }
            break;

        case 'evaluations':
            $controller = new EvaluationController($db, $tokenManager);
            if ($id === null) {
                // GET /api/evaluations
                if ($method === 'GET') {
                    // GET /api/evaluations
                    $controller->getAll();
                } elseif ($method === 'POST' || $method === 'PUT') {
                    // POST || PUT /api/evaluations
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // GET /api/evaluations/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        // GET /api/evaluations/{id}
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        // POST || PUT /api/evaluations/{id}
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        // DELETE /api/evaluations/{id}
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                }
            } elseif ($id === 'project' && is_numeric($action)) {
                // GET /api/evaluations/project/{id}
                $controller->getByProject($action);
            } elseif ($id === 'judge' && is_numeric($action)) {
                // GET /api/evaluations/judge/{id}
                $controller->getByJudge($action);
            } else {
                throw new Exception('ID non valide pour /evaluations', 400);
            }
            break;

        case 'notifications':
            $controller = new NotificationController($db, $tokenManager);
            if ($id === 'user' && is_numeric($action)) {
                if ($method === 'GET') {
                    // GET /api/notifications/user/{user_id}
                    $controller->listForCurrentUser($action);
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif ($id === 'unread-count') {
                // GET /api/notifications/unread-count/{user_id}
                if ($method === 'GET' && !empty($action) && is_numeric($action)) {
                    $controller->getUnreadCount($action);
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif ($id === 'mark-all-read') {
                // POST /api/notifications/mark-all-read/{user_id}
                if ($method === 'POST' && !empty($action) && is_numeric($action)) {
                    $controller->markAllAsRead($action);
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // GET /api/notifications/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        // GET /api/notifications/{id}
                        $controller->getNotification($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } elseif ($action === 'mark-as-read' && $method === 'POST') {
                    // POST /api/notifications/{id}/markAsRead
                    $controller->markAsRead($id);
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } else {
                throw new Exception('Route non reconnue ou invalide', 400);
            }
            break;
        case 'projects':
            $controller = new ProjectController($db, $tokenManager);

            // POST /api/projects - Soumettre un nouveau projet
            if ($id === null) {
                if ($method === 'POST') {
                    $controller->submit($input);
                    exit;
                }
                
                // GET /api/projects - Liste des projets (avec filtres optionnels)
                if ($method === 'GET') {
                    try {
                        // Validation des paramètres de filtre
                        $filters = [
                            'hackathon_id' => filter_input(INPUT_GET, 'hackathon_id', FILTER_VALIDATE_INT, [
                                'options' => ['min_range' => 1]
                            ]) ?: null,
                            'team_id' => filter_input(INPUT_GET, 'team_id', FILTER_VALIDATE_INT, [
                                'options' => ['min_range' => 1]
                            ]) ?: null,
                            'challenge_id' => filter_input(INPUT_GET, 'challenge_id', FILTER_VALIDATE_INT, [
                                'options' => ['min_range' => 1]
                            ]) ?: null,
                            'status' => filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?: null
                        ];
                        
                        $controller->getAll($filters);
                    } catch (Exception $e) {
                        jsonResponse([
                            'success' => false,
                            'error' => 'Erreur lors de la récupération des projets',
                            'details' => $e->getMessage()
                        ], 500);
                    }
                    exit;
                }
                
                jsonResponse([
                    'success' => false,
                    'error' => 'Méthode non autorisée',
                    'allowed_methods' => ['GET', 'POST']
                ], 405);
            }
            
            // Vérifier si l'ID est numérique et valide
            if (!is_numeric($id) || $id <= 0) {
                jsonResponse([
                    'success' => false,
                    'error' => 'ID de projet invalide',
                    'expected' => 'Un entier positif non nul'
                ], 400);
                exit;
            }
            
            $id = (int)$id; // Conversion en entier
            
            // Routes avec ID de projet spécifique
            try {
                switch ($action) {
                    case null:
                        // GET /api/projects/{id} - Récupérer un projet spécifique
                        if ($method === 'GET') {
                            $controller->get($id);
                            exit;
                        }
                        
                        // PUT /api/projects/{id} - Mettre à jour un projet
                        if ($method === 'PUT') {
                            $controller->update($id, $input);
                            exit;
                        }
                        
                        // DELETE /api/projects/{id} - Supprimer un projet
                        if ($method === 'DELETE') {
                            $controller->delete($id);
                            exit;
                        }
                        
                        jsonResponse([
                            'success' => false,
                            'error' => 'Méthode non autorisée',
                            'allowed_methods' => ['GET', 'PUT', 'DELETE']
                        ], 405);
                        break;
                        
                    case 'download':
                        // GET /api/projects/{id}/download - Télécharger le fichier du projet
                        if ($method === 'GET') {
                            $controller->download($id);
                            exit;
                        }
                        
                        jsonResponse([
                            'success' => false,
                            'error' => 'Méthode non autorisée',
                            'allowed_methods' => ['GET']
                        ], 405);
                        break;
                        
                    default:
                        jsonResponse([
                            'success' => false,
                            'error' => 'Action non reconnue',
                            'available_actions' => ['download']
                        ], 400);
                }
            } catch (Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => 'Erreur lors du traitement de la requête',
                    'details' => $e->getMessage()
                ], 500);
            }
            break;
        case 'piston':
            require_once __DIR__ . '/Piston/PistonRequest.php';
            require_once __DIR__ . '/Piston/PistonExecutor.php';
            require_once __DIR__ . '/Piston/PistonResponse.php';

            $language = $input['language'] ?? '';
            $code = $input['code'] ?? '';

            try {
                $request = new PistonRequest($language, $code);
                $executor = new PistonExecutor();
                $response = $executor->execute($request);

                echo json_encode($response->toArray());
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode([
                    'output' => null,
                    'error' => $e->getMessage(),
                    'exitCode' => 1
                ]);
            }
            break;

        default:
            throw new Exception('Endpoint non trouvé', 404);
    }
} catch (Exception $e) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        $statusCode = $e->getCode() ?: 500;
        jsonResponse([
            'success' => false,
            'error' => "Une erreure est survenue au niveau de l'API. Veuillez contacter le support technique !",
            // pour debug
            'debug_message' => $e->getMessage(),
            // 'debug_file' => $e->getFile(),
            // 'debug_line' => $e->getLine(),
            // 'debug_trace' => $e->getTraceAsString()
        ]);

        return;
    }
    setFlashMessage(
        'error',
        'Erreur API',
        "Une erreure est survenue au niveau de l'API. Veuillez contacter le support technique !"
        // pour debug
        // . ' | Debug: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
    );
    header('Location: /');
    exit();
}
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
