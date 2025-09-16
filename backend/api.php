<?php
// Protection : uniquement requêtes AJAX
if (
    !isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    !isAjaxRequest()
) {
    header('Location: /error403'); // Redirection vers une page propre
    exit;
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

// ✅ Inclure une seule fois le fichier de configuration
if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/includes/config.php';
}

// ✅ Inclure les fichiers contenant des fonctions
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/includes/functions.php';
}


// ✅ Inclure les classes seulement si elles n'existent pas déjà
$files = [
    'Database'            => '/models/Database.php',
    'Controller'          => '/controllers/Controller.php',
    'AuthController'      => '/controllers/AuthController.php',
    'UserController'      => '/controllers/UserController.php',
    'HackathonController' => '/controllers/HackathonController.php',
    'TeamController'      => '/controllers/TeamController.php',
    'ProjectController'   => '/controllers/ProjectController.php',
    'ChallengeController' => '/controllers/ChallengeController.php',
    'EvaluationController' => '/controllers/EvaluationController.php',
    'AdminController'     => '/controllers/AdminController.php',
    'TokenManager'        => '/models/TokenManager.php'
];

foreach ($files as $class => $path) {
    if (!class_exists($class)) {
        require_once __DIR__ . $path;
    }
}

// Configurer CORS pour toutes les requêtes API
configureCors();

// Initialisation de la base de données
$db = Database::getInstance()->getConnection();

$key = 'your-secret-key';

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

// Initialisation du gestionnaire de token
$tokenManager = new TokenManager($key, $db);

try {
    switch ($endpoint) {
        case 'auth':
            $controller = new AuthController($db);
            switch ($request[1] ?? '') {
                case 'check':
                    $controller->checkAuth();
                    break;

                case 'login':
                    try {
                        $controller->login();
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
                        $controller->register();
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
                    throw new Exception('Endpoint non trouvé. - api ' . var_dump($uri), 404);
            }
            break;

        case 'admin':
            $controllerAdmin = new AdminController($db, $tokenManager);

            // Vérification du token JWT pour toutes les routes sauf OPTIONS
            if ($method !== 'OPTIONS') {
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

                    // Stocker l'ID utilisateur pour les vérifications ultérieures
                    $AdminUserId = $tokenValidation['user_id'];

                    // Vérifier si l'utilisateur est admin
                    if (!$controllerAdmin->isAdmin($AdminUserId)) {
                        throw new Exception('Accès non autorisé', 403);
                    }
                } catch (Exception $e) {
                    jsonResponse([
                        'success' => false,
                        'error' => $e->getMessage()
                    ], $e->getCode() ?: 401);
                    exit();
                }
            }

            // Routage des endpoints administrateur
            $adminAction = $request[1] ?? null;


            // Gestion des routes admin directes
            switch ($adminAction) {
                case 'stats':
                    $controllerAdmin->getStats();
                    break;
                case 'activity':
                    $controllerAdmin->getActivity();
                    break;
                case 'upcoming-hackathons':
                    $controllerAdmin->getUpcomingHackathons();
                    break;
                case 'popular-challenges':
                    $controllerAdmin->getPopularChallenges();
                    break;
                case 'teams':
                    $controllerAdmin->getAllTeams();
                    break;
                case 'notifications':
                    $controllerAdmin->getAdminNotifications();
                    break;
                case 'users':
                    $controllerAdmin->getAllUsers();
                    break;
                case 'hackathons':
                    // GET /api/admin/hackathons
                    $controllerAdmin->getAllHackathons();
                    break;
                case 'challenges':
                    // GET /api/admin/challenges
                    if ($method === 'GET' && !isset($request[2])) {
                        $controllerAdmin->getChallenges();
                    } elseif ($method === 'GET' && isset($request[2]) && $request[2] === 'stats') {
                        // GET /api/admin/challenges/stats
                        $controllerAdmin->getChallengesStats();
                    } elseif ($method === 'POST' && isset($request[2]) && $request[2] === 'create') {
                        // POST /api/admin/challenges/create
                        $controllerAdmin->createChallenge();
                    } elseif (isset($request[2]) && is_numeric($request[2])) {
                        $challengeId = $request[2];
                        switch ($method) {
                            case 'GET':
                                // GET /api/admin/challenges/{id}
                                $controllerAdmin->getChallenge($challengeId);
                                break;
                            case 'PUT':
                                // PUT /api/admin/challenges/{id}
                                $controllerAdmin->updateChallenge($challengeId);
                                break;
                            case 'DELETE':
                                // DELETE /api/admin/challenges/{id}
                                $controllerAdmin->deleteChallenge($challengeId);
                                break;
                            default:
                                throw new Exception('Méthode non autorisée', 405);
                        }
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                    break;
                case 'technologies':
                    if ($method === 'GET') {
                        // GET /api/admin/technologies
                        $controllerAdmin->getAllTechnologies();
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                    break;
                case 'hackathon-phases':
                    // GET /api/admin/hackathon-phases/{id}
                    if ($method === 'GET' && isset($request[2]) && is_numeric($request[2])) {
                        $hackathonId = $request[2];
                        $controllerAdmin->getHackathonPhases($hackathonId);
                    } else {
                        throw new Exception('ID de hackathon requis', 400);
                    }
                    break;
                case 'submissions':
                    // GET /api/admin/submissions
                    $controllerAdmin->getAllSubmissions();
                    break;
                case 'submission-stats':
                    // GET /api/admin/submission-stats
                    $controllerAdmin->getSubmissionStats();
                    break;
                case 'team-stats':
                    // GET /api/admin/team-stats
                    $controllerAdmin->getTeamStats();
                    break;
                case 'dashboard-stats':
                    // GET /api/admin/dashboard-stats
                    $controllerAdmin->getDashboardStats();
                    break;
                case 'me':
                    // GET /api/admin/me
                    $controllerAdmin->getAdmin($AdminUserId);
                    break;
                default:
                    // Si ce n'est pas une route directe, vérifier si c'est un ID utilisateur
                    // GET /api/admin/{id}
                    if (is_numeric($adminAction)) {
                        $id = $adminAction;
                        $action = $request[2] ?? null;

                        if ($action === null) {
                            switch ($method) {
                                case 'GET':
                                    $controllerAdmin->getAdmin($id);
                                    break;
                                case 'POST':
                                case 'PUT':
                                    $controllerAdmin->update($id, $token);
                                    break;
                                case 'DELETE':
                                    $controllerAdmin->delete($id, $token);
                                    break;
                                default:
                                    jsonResponse(['success' => false, 'error' => 'Méthode non autorisée'], 405);
                                    break;
                            }
                        } else {
                            // Gestion des actions spécifiques pour un utilisateur
                            switch ($action) {
                                case 'role':
                                    $controllerAdmin->updateRole($id);
                                    break;
                                default:
                                    jsonResponse(['success' => false, 'error' => 'Action non reconnue'], 404);
                                    break;
                            }
                        }
                    } else if ($adminAction === 'hackathon-stats' && isset($_GET['id'])) {
                        // GET /api/admin/hackathon-stats/{id}
                        $hackathonId = $_GET['id'];
                        $controllerAdmin->getHackathonStats($hackathonId);
                    } else if ($adminAction === 'challenge-stats' && isset($_GET['id'])) {
                        // GET /api/admin/challenge-stats/{id}
                        $challengeId = $_GET['id'];
                        $controllerAdmin->getChallengeStats($challengeId);
                    } else {
                        jsonResponse(['success' => false, 'error' => 'Endpoint admin non trouvé: ' . $adminAction], 404);
                    }
                    break;
            }
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
                    'success' => $isParticipant,
                    'message' => $isParticipant ? 'Accès autorisé' : 'Accès non autorisé'
                ], 200);
            } catch (Exception $e) {
                jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 400);
            }
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
                            'error' => 'api.php ' . $e->getMessage()
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
                                jsonResponse(['error' => 'Non authentifié. api'], 401);
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
                                        'error' => 'api.php ' . $e->getMessage()
                                    ], $e->getCode() ?: 404);
                                } else {
                                    setFlashMessage('error', 'Erreur de connexion', $e->getMessage());
                                    header('Location: ' . '/user');
                                    exit();
                                }
                            }
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
                // Route /api/users/{id}
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
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
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
                            // Vérification d'autorisation (similaire à ongoing-challenges)
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            // **Assurez-vous que vous avez une méthode `getCurrentChallenges()` dans UserController**
                            $controller->getCurrentChallenges($id, $token);
                            break;
                        case 'current-hackathons':
                            // Un utilisateur peut voir ses propres hackathons ou un admin peut voir n'importe quels hackathons
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getCurrentHackathons($id, $token);
                            break;

                        case 'recent-activities':
                            // Un utilisateur peut voir sa propre activité récente ou un admin peut voir celle des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getRecentActivities($id, $token);
                            break;
                        case 'next-event':
                            // Un utilisateur peut voir sa propre activité récente ou un admin peut voir celle des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getNextEvent($id, $token);
                            break;
                        case 'notifications':
                            // Un utilisateur peut voir sa propre activité récente ou un admin peut voir celle des autres
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getNotifications($id, $token);
                            break;
                        case 'completed-challenges':
                            // Vérification d'autorisation
                            if ($currentUserId != $id && !$controller->isAdmin($currentUserId)) {
                                jsonResponse(['success' => false, 'error' => 'Accès non autorisé'], 403);
                            }
                            $controller->getCompletedChallenges($id, $token);
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
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/hackathons/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
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
            } else {
                throw new Exception('ID non valide pour /hackathons', 400);
            }
            break;

        case 'teams':
            $controller = new TeamController($db, $tokenManager);
            if ($id === null) {
                // Route /api/teams
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
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
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
                        case 'members':
                            if ($method === 'GET') {
                                $controller->getMembers($id);
                            } elseif (isset($request[3]) && $request[3] === 'add') {
                                $controller->addMember($id);
                            } elseif (isset($request[3]) && $request[3] === 'remove') {
                                $controller->removeMember($id);
                            } else {
                                throw new Exception('Action non reconnue', 404);
                            }
                            break;
                        case 'leader':
                            if (isset($request[3]) && $request[3] === 'change') {
                                $controller->changeLeader($id);
                            } else {
                                throw new Exception('Action non reconnue', 404);
                            }
                            //verifier que c'est le leader
                            if ($controller->isLeader($id, $userId)) {
                                throw new Exception('Accès non autorisé', 403);
                            } else {
                                if (isset($request[3]) && $request[3] === 'accept') {
                                    $controller->acceptRequest($id, $userId);
                                } elseif (isset($request[3]) && $request[3] === 'reject') {
                                    $controller->rejectRequest($id, $userId);
                                } else {
                                    throw new Exception('Action non reconnue', 404);
                                }
                            }
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } elseif ($id === 'hackathon' && is_numeric($action)) {
                $controller->getByHackathon($action);
            } elseif ($id === 'user' && is_numeric($action)) {
                $controller->getByUser($action);
            } else {
                throw new Exception('ID non valide pour /teams', 400);
            }
            break;

        case 'projects':
            $controller = new ProjectController($db, $tokenManager);
            if ($id === null) {
                // Route /api/projects
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/projects/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                } else {
                    switch ($action) {
                        case 'status':
                            $controller->updateStatus($id);
                            break;
                        case 'score':
                            $controller->updateScore($id);
                            break;
                        case 'version':
                            $controller->updateVersion($id);
                            break;
                        case 'evaluations':
                            $controller->getEvaluations($id);
                            break;
                        default:
                            throw new Exception('Action non reconnue', 404);
                    }
                }
            } elseif ($id === 'team' && is_numeric($action)) {
                $controller->getByTeam($action);
            } elseif ($id === 'hackathon' && is_numeric($action)) {
                $controller->getByHackathon($action);
            } else {
                throw new Exception('ID non valide pour /projects', 400);
            }
            break;

        case 'challenges':
            $controller = new ChallengeController($db, $tokenManager);
            if ($id === null) {
                // Route /api/challenges
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif ($id === 'dev') {
                // Nouvelle route /api/challenges/dev/{hackathon_id}
                if ($method === 'GET' && isset($input['hackathon_id']) && is_numeric($input['hackathon_id'])) {
                    $controller->getChallengesDev($input['hackathon_id']);
                } else {
                    throw new Exception('Méthode non autorisée ou ID invalide', 400);
                }
            } elseif ($id === 'solves') {
                // Nouvelle route /api/challenges/solves
                if ($method === 'GET') {
                    $controller->getSolvesCount();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/challenges/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                }
            } else {
                throw new Exception('ID non valide pour /challenges', 400);
            }
            break;

        case 'evaluations':
            $controller = new EvaluationController($db, $tokenManager);
            if ($id === null) {
                // Route /api/evaluations
                if ($method === 'GET') {
                    $controller->getAll();
                } elseif ($method === 'POST') {
                    $controller->create();
                } else {
                    throw new Exception('Méthode non autorisée', 405);
                }
            } elseif (is_numeric($id)) {
                // Route /api/evaluations/{id}
                if ($action === null) {
                    if ($method === 'GET') {
                        $controller->get($id);
                    } elseif ($method === 'POST' || $method === 'PUT') {
                        $controller->update($id);
                    } elseif ($method === 'DELETE') {
                        $controller->delete($id);
                    } else {
                        throw new Exception('Méthode non autorisée', 405);
                    }
                }
            } elseif ($id === 'project' && is_numeric($action)) {
                $controller->getByProject($action);
            } elseif ($id === 'judge' && is_numeric($action)) {
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
                } elseif ($method === 'POST' || $method === 'PUT') {
                    // POST || PUT /api/notifications/user/{user_id}
                    $controller->create($input);
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
                    } elseif ($method === 'DELETE') {
                        // DELETE /api/notifications/{id}
                        $controller->delete($id);
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

        default:
            throw new Exception('Endpoint non trouvé', 404);
    }
} catch (Exception $e) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json');
        $statusCode = $e->getCode() ?: 500;
        jsonResponse(['success' => false, 'error' => $e->getMessage()], $statusCode);
        echo json_encode([
            'debug' => print_r($request, true)
        ]);
        return;
    }
    setFlashMessage('error', 'Erreur API', $e->getMessage());
    header('Location: /auth');
    exit();
}
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
