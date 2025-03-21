<?php
namespace Auth\Controller;

class LoginController
{
    private $authController;

    public function __construct(AuthController $authController)
    {
        $this->authController = $authController;
    }

    public function handleLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $credentials = [
                    'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                    'password' => $_POST['password']
                ];

                $rememberMe = isset($_POST['remember_me']);

                $tokens = $this->authController->login($credentials, $rememberMe);

                // Stocker les tokens en cookies
                setcookie("jwt_token", $tokens['jwt'], [
                    "expires" => time() + 3600,
                    "path" => "/",
                    "httponly" => true,
                    "secure" => true,
                    "samesite" => "Strict"
                ]);

                if ($tokens['long_term_token']) {
                    setcookie("long_term_token", $tokens['long_term_token']['token'], [
                        "expires" => strtotime($tokens['long_term_token']['expires_at']),
                        "path" => "/",
                        "httponly" => true,
                        "secure" => true,
                        "samesite" => "Strict"
                    ]);
                }

                header('Location: profil.php');
                exit();
            } catch (\Exception $e) {
                $_SESSION['notification'] = [
                    'message' => "Erreur de connexion",
                    'details' => $e->getMessage(),
                    'type' => 'error'
                ];
            }
        }
    }
}