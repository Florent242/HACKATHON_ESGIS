<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../vendor/autoload.php';

class ResetPasswordController extends Controller {
    private $user;
    private $db;
    private $mailer;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->user = new User($this->db);
        $this->mailer = new PHPMailer(true);
        $this->setupMailer();
    }

    private function setupMailer(): void {
        $this->mailer->isSMTP();
        $this->mailer->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['SMTP_USERNAME'] ?? '';
        $this->mailer->Password = $_ENV['SMTP_PASSWORD'] ?? '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $_ENV['SMTP_PORT'] ?? 587;
        $this->mailer->setFrom($_ENV['SMTP_FROM'] ?? 'noreply@esgis.com', 'ESGIS Hackathon');
        $this->mailer->CharSet = 'UTF-8';
    }

    public function requestReset(): void {
        try {
            $this->validateMethod('POST');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input === null) {
                throw new Exception('Format JSON invalide');
            }

            $this->validateRequiredFields($input, ['email']);
            $email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
            if (!$email) {
                throw new Exception('Email invalide');
            }

            $user = $this->user->findByEmail($email);
            if (!$user) {
                // Pour des raisons de sécurité, on ne révèle pas si l'email existe
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Si votre email est enregistré, vous recevrez les instructions de réinitialisation.'
                ]);
                return;
            }

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $this->user->updateUser($user['id'], [
                'reset_token' => $token,
                'reset_token_expiry' => $expiry
            ]);

            $resetLink = "http://localhost/reset-password?token=" . $token;
            
            $this->mailer->addAddress($email);
            $this->mailer->Subject = 'Réinitialisation de votre mot de passe - ESGIS Hackathon';
            $this->mailer->Body = "Bonjour {$user['prenom']},\n\n"
                . "Vous avez demandé la réinitialisation de votre mot de passe.\n"
                . "Cliquez sur le lien suivant pour définir un nouveau mot de passe :\n\n"
                . $resetLink . "\n\n"
                . "Ce lien expirera dans 1 heure.\n\n"
                . "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n"
                . "Cordialement,\n"
                . "L'équipe ESGIS Hackathon";

            $this->mailer->send();

            $this->jsonResponse([
                'success' => true,
                'message' => 'Si votre email est enregistré, vous recevrez les instructions de réinitialisation.'
            ]);

        } catch (MailerException $e) {
            throw new Exception('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function resetPassword(): void {
        try {
            $this->validateMethod('POST');
            
            $input = json_decode(file_get_contents('php://input'), true);
            if ($input === null) {
                throw new Exception('Format JSON invalide');
            }

            $this->validateRequiredFields($input, ['token', 'password', 'confirm_password']);

            if ($input['password'] !== $input['confirm_password']) {
                throw new Exception('Les mots de passe ne correspondent pas');
            }

            if (strlen($input['password']) < 8) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères');
            }

            $user = $this->user->findByResetToken($input['token']);
            if (!$user) {
                throw new Exception('Token invalide ou expiré');
            }

            $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
            $this->user->updateUser($user['id'], [
                'password' => $hashedPassword,
                'reset_token' => null,
                'reset_token_expiry' => null
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Votre mot de passe a été réinitialisé avec succès'
            ]);

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
