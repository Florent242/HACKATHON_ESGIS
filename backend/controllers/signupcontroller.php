<?php
namespace Auth\Controller;

class SignupController
{
    private $authController;

    public function __construct(AuthController $authController)
    {
        $this->authController = $authController;
    }

    public function handleSignup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validation des données
                $this->validateSignupData();

                // Préparation des données utilisateur
                $userData = [
                    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                    'password' => $_POST['password']
                ];

                // Création de l'utilisateur
                $userId = $this->authController->signup($userData);

                if ($userId) {
                    // Connexion automatique après inscription
                    $tokens = $this->authController->login($userData, false);

                    // Configuration du cookie JWT
                    setcookie("jwt_token", $tokens['jwt'], [
                        "expires" => time() + 3600,
                        "path" => "/",
                        "httponly" => true,
                        "secure" => true,
                        "samesite" => "Strict"
                    ]);

                    header('Location: profil.php');
                    exit();
                }
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
        }
    }

    private function validateSignupData(): void
    {
        $errors = [];

        // Validation email
        if (!isset($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide";
        }

        // Validation mot de passe
        if (!isset($_POST['password']) || strlen($_POST['password']) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }

        // Validation confirmation mot de passe
        if (!isset($_POST['confirm_password']) || $_POST['password'] !== $_POST['confirm_password']) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }

        if (!empty($errors)) {
            throw new \Exception(implode(", ", $errors));
        }
    }
}