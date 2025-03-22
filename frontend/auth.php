<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - S'inscrire</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/auth.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/auth.js"></script>
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.280.0/dist/umd/lucide.min.js"></script>
</head>

<body>
    <div id="notification-data" data-notification='<?= json_encode($_SESSION['notification'] ?? null) ?>'></div>
    <div class="auth-container">
        <!-- Onglets pour basculer entre connexion et inscription -->
        <div class="auth-tabs">
            <button class="auth-tab active" id="tab-login">Utilisateur</button>
            <button class="auth-tab" id="tab-register">Inscription</button>
        </div>

        <!-- Formulaires -->
        <div class="auth-card bg">
            <div class="auth-form" id="loginForm">
                <h1>Espace Utilisateur</h1>
                <p>Connectez-vous à votre compte étudiant</p> <br><br>
                <form action="/HACKATHON_ESGIS/public/api/auth/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="email_user">Email</label>
                        <div class="display">
                            <i data-lucide="mail"></i>
                            <input type="email" id="email_user" name="email" placeholder="etudiant@esgis.bj" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_user">Mot de passe</label>
                        <div class="display">
                            <i data-lucide="key"></i>
                            <input type="password" id="password_user" name="password" placeholder="............" required>
                        </div>
                    </div>

                    <div>
                        <label for="remember_me">Rester connecté</label>
                        <input type="checkbox" name="remember_me" id="remember_me">
                    </div>
                    <button type="submit" class="submit-btn"> <i data-lucide="send"></i>Se connecter</button>
                </form>
            </div>

            <div class="auth-form" id="registerForm">
                <h1>Inscription</h1>
                <p>Créez votre compte EsgisHub</p>
                <br>
                <br>
                <form action="/HACKATHON_ESGIS/public/api/auth/register" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="fullName">Nom complet</label>
                        <div class="display">
                            <i data-lucide="user"></i>
                            <input type="text" id="fullName" name="fullName" placeholder="Votre nom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <div class="display">
                            <i data-lucide="user"></i>
                            <input type="text" id="username" name="username" placeholder="Votre nom d'utilisateur" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="display">
                            <i data-lucide="mail"></i>
                            <input type="email" id="email" name="email" placeholder="etudiant@esgis.bj" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="display">
                            <i data-lucide="key"></i>
                            <input type="password" id="password" name="password" placeholder="............" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmer le mot de passe</label>
                        <div class="display">
                            <i data-lucide="key"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="............" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn"> <i data-lucide="send"></i>S'inscrire</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener("load", function() {
            lucide.createIcons();
        });
    </script>

    <style>
        .error-message {
            position: fixed;
            top: 2rem;
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #dc2626;
            padding: 1rem;
            margin: 1rem auto;
            border-radius: 0.5rem;
            max-width: 80%;
            text-align: center;
        }
    </style>
</body>

</html>