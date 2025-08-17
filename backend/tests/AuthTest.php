<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Auth\Model\User;
use Auth\Model\UserModel;
use Auth\Controller\AuthController;
use Auth\Controller\SignupController;
use Auth\Controller\ResetPasswordController;
use PDO;

class AuthTest extends TestCase
{
    private $db;
    private $user;
    private $authController;
    private $signupController;
    private $resetController;

    protected function setUp(): void
    {
        // Utiliser une base de données SQLite en mémoire pour les tests
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer les tables nécessaires
        $this->db->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
        $this->db->exec(file_get_contents(__DIR__ . '/../database/migrations/002_add_reset_password_fields.sql'));

        // Initialiser les modèles et contrôleurs
        $this->user = new User($this->db);
        $this->authController = new AuthController($this->db);
        $this->signupController = new SignupController($this->authController);
        $this->resetController = new ResetPasswordController($this->db);
    }

    public function testSignupSuccess()
    {
        $userData = [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $jsonData = json_encode($userData);
        file_put_contents('php://input', $jsonData);

        ob_start();
        $this->signupController->handleSignup();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('jwt', $result['data']);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertEquals('test@example.com', $result['data']['user']['email']);
    }

    public function testSignupDuplicateEmail()
    {
        $userData = [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $jsonData = json_encode($userData);
        file_put_contents('php://input', $jsonData);

        // Première inscription
        ob_start();
        $this->signupController->handleSignup();
        ob_clean();

        // Deuxième inscription avec le même email
        ob_start();
        $this->signupController->handleSignup();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertFalse($result['success']);
        $this->assertEquals('Cet email est déjà utilisé', $result['error']);
    }

    public function testLoginSuccess()
    {
        // Créer d'abord un utilisateur
        $userData = [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'login@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $jsonData = json_encode($userData);
        file_put_contents('php://input', $jsonData);

        // Inscription
        ob_start();
        $this->signupController->handleSignup();
        ob_clean();

        // Tester la connexion
        $credentials = [
            'email' => 'login@example.com',
            'password' => 'password123'
        ];

        $jsonData = json_encode($credentials);
        file_put_contents('php://input', $jsonData);

        ob_start();
        $this->authController->login();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('jwt', $result['data']);
        $this->assertArrayHasKey('user', $result['data']);
        $this->assertEquals('login@example.com', $result['data']['user']['email']);
    }

    public function testLoginInvalidCredentials()
    {
        $credentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $jsonData = json_encode($credentials);
        file_put_contents('php://input', $jsonData);

        ob_start();
        $this->authController->login();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertFalse($result['success']);
        $this->assertEquals('Email ou mot de passe incorrect', $result['error']);
    }

    public function testPasswordResetRequest()
    {
        // Créer d'abord un utilisateur
        $userData = [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'reset@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $jsonData = json_encode($userData);
        file_put_contents('php://input', $jsonData);

        // Inscription
        ob_start();
        $this->signupController->handleSignup();
        ob_clean();

        // Tester la demande de réinitialisation
        $resetData = [
            'email' => 'reset@example.com'
        ];

        $jsonData = json_encode($resetData);
        file_put_contents('php://input', $jsonData);

        ob_start();
        $this->resetController->requestReset();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertEquals(
            'Si votre email est enregistré, vous recevrez les instructions de réinitialisation.',
            $result['message']
        );

        // Vérifier que le token a été généré
        $user = $this->user->findByEmail('reset@example.com');
        $this->assertNotNull($user['reset_token']);
        $this->assertNotNull($user['reset_token_expiry']);
    }

    public function testPasswordReset()
    {
        // Créer un utilisateur et générer un token de réinitialisation
        $userData = [
            'nom' => 'Test',
            'prenom' => 'User',
            'email' => 'reset2@example.com',
            'password' => 'password123',
            'confirm_password' => 'password123'
        ];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $jsonData = json_encode($userData);
        file_put_contents('php://input', $jsonData);

        // Inscription
        ob_start();
        $this->signupController->handleSignup();
        ob_clean();

        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $user = $this->user->findByEmail('reset2@example.com');
        $this->user->updateUser($user['id'], [
            'reset_token' => $token,
            'reset_token_expiry' => $expiry
        ]);

        // Tester la réinitialisation du mot de passe
        $resetData = [
            'token' => $token,
            'password' => 'newpassword123',
            'confirm_password' => 'newpassword123'
        ];

        $jsonData = json_encode($resetData);
        file_put_contents('php://input', $jsonData);

        ob_start();
        $this->resetController->resetPassword();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
        $this->assertEquals('Votre mot de passe a été réinitialisé avec succès', $result['message']);

        // Vérifier que le nouveau mot de passe fonctionne
        $loginData = [
            'email' => 'reset2@example.com',
            'password' => 'newpassword123'
        ];

        $jsonData = json_encode($loginData);
        file_put_contents('php://input', $jsonData);

        ob_start();
        $this->authController->login();
        $output = ob_get_clean();

        $result = json_decode($output, true);
        $this->assertTrue($result['success']);
    }

    protected function tearDown(): void
    {
        // Nettoyer la base de données après chaque test
        $this->db->exec('DROP TABLE IF EXISTS users');
        $this->db = null;
    }
}
