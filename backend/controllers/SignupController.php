<?php
namespace Auth\Controller;

use Exception;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/AuthController.php';

class SignupController extends Controller
{
    private $authController;

    public function __construct(AuthController $authController)
    {
        parent::__construct();
        $this->authController = $authController;
    }

    public function handleSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Récupération des données JSON
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input === null) {
                    throw new Exception('Format JSON invalide');
                }

                // Validation des données
                $this->validateSignupData($input);

                // Préparation des données utilisateur
                $userData = [
                    'nom' => htmlspecialchars($input['nom']),
                    'prenom' => htmlspecialchars($input['prenom']),
                    'email' => filter_var($input['email'], FILTER_SANITIZE_EMAIL),
                    'password' => $input['password'],
                    'role' => 'participant',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Création de l'utilisateur
                $userId = $this->authController->signup($userData);

                if ($userId) {
                    // Connexion automatique après inscription
                    $loginData = [
                        'email' => $input['email'],
                        'password' => $input['password']
                    ];
                    $result = $this->authController->login($loginData);

                    // Configuration du cookie JWT selon la mémoire du projet
                    setcookie("jwt_token", $result['jwt'], [
                        "expires" => time() + 3600,
                        "path" => "/",
                        "httponly" => true,
                        "secure" => true,
                        "samesite" => "Strict"
                    ]);

                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Inscription réussie',
                        'data' => $result
                    ]);
                }
            } catch (Exception $e) {
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], 400);
            }
        } else {
            $this->jsonResponse([
                'success' => false,
                'error' => 'Méthode non autorisée'
            ], 405);
        }
    }

    private function validateSignupData(array $data): void
    {
        $requiredFields = ['nom', 'prenom', 'email', 'password', 'confirm_password'];
        $this->validateRequiredFields($data, $requiredFields);

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email invalide');
        }

        if (strlen($data['password']) < 8) {
            throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
        }

        if ($data['password'] !== $data['confirm_password']) {
            throw new Exception('Les mots de passe ne correspondent pas');
        }
    }
}