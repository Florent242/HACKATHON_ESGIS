<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$errorMessage = isset($_GET['error']) ? urldecode($_GET['error']) : null;
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
    <div id="notification-data" data-notification='<?= htmlspecialchars(json_encode($_SESSION['notification'] ?? null)) ?>'></div>
    <div class="auth-container">
        <!-- Onglets pour basculer entre connexion et inscription -->
        <div class="auth-tabs">
            <button class="auth-tab active" id="tab-login">Utilisateur</button>
            <button class="auth-tab" id="tab-register">Inscription</button>
        </div>

        <!-- Formulaires -->
        <div class="auth-card">
            <div class="auth-form" id="loginForm">
                <h1>Espace Utilisateur</h1>
                <p>Connectez-vous à votre compte étudiant</p> <br><br>
                <form action="/HACKATHON_ESGIS/public/api/auth/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="email_user">Email</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="mail"></i>
                            <input type="email" id="email_user" name="email" placeholder="etudiant@esgis.bj" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_user">Mot de passe</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
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
                <form action="/HACKATHON_ESGIS/public/api/auth/register" method="POST" id="registrationForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">

                        <label for="fullName" class="label after:ml-1 after:text-red-500 after:content-['*']">Nom complet</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="user"></i>
                            <input type="text" id="fullname" name="fullname" placeholder="Votre nom" required>
                        </div>
                        <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="fullNameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="username" class="label after:ml-1 after:text-red-500 after:content-['*']">Nom d'utilisateur</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="user"></i>
                            <input type="text" id="username" name="username" placeholder="Votre nom d'utilisateur" required>
                        </div>
                        <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="usernameError"></span>
                    </div>
                    <div class="form-group">
                        <label for="email" class="label after:ml-1 after:text-red-500 after:content-['*']">Email</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="mail"></i>
                            <input type="email" id="email" name="email" placeholder="etudiant@esgis.bj" required>
                        </div>
                        <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="emailError"></span>
                    </div>
                    <div class="form-group">
                        <label for="password" class="label after:ml-1 after:text-red-500 after:content-['*']">Mot de passe</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="key"></i>
                            <input type="password" id="password" name="password" placeholder="............" required>
                        </div>
                        <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="passwordError"></span>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword" class="label after:ml-1 after:text-red-500 after:content-['*']">Confirmer le mot de passe</label>
                        <div class="display p-1 focus:border-blue-500 border border-indigo-400/40 shadow-lg shadow-indigo-300/10">
                            <i data-lucide="key"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" placeholder="............" required>
                        </div>
                        <span class="error-message absolute top-full text-red-500 text-xs mt-1 hidden" id="confirmPasswordError"></span>
                    </div>
                    <button type="submit" class="submit-btn"><i data-lucide="send"></i>S'inscrire</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener("load", function() {
            lucide.createIcons();
        });
    </script>

</body>

</html>