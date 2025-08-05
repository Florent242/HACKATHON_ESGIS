<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esgis Admin</title>
    <link rel="stylesheet" href="/css/styles/auth_admin.css">
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script defer src="/js/auth_admin.js"></script>

</head>

<body>
    <div id="notification-data" data-notification='<?= htmlspecialchars(json_encode($_SESSION['notification'] ?? null)) ?>'></div>

    <div class="ad-container">
        <div class="auth-form" id="loginForm">
            <h1>Espace Administrateur</h1> <br>
            <form action="/api/auth/login" method="POST" id="signinForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? null; ?>">
                <div class="form-group">
                    <label for="email_user">Email ou nom d'utilisateur</label>
                    <div class="display p-2 shadow-lg shadow-indigo-300/10">
                        <i data-lucide="mail"></i>
                        <input type="text" id="email_user" name="identifier" placeholder="etudiant@esgis.bj" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_user">Mot de passe</label>
                    <div class="display p-2 shadow-lg shadow-indigo-300/10">
                        <i data-lucide="key"></i>
                        <input type="password" id="password_user" name="password" placeholder="............" required>
                    </div>
                </div>

                <div class="mb-1">
                    <label for="remember_me">Rester connecté</label>
                    <input type="checkbox" class="text-white checked:bg-blue-500" name="remember_me" id="remember_me">
                </div>
                <button type="submit" class="submit-btn"> <i data-lucide="send"></i>Se connecter</button>
            </form>
        </div>
    </div>
    <script defer>
        window.addEventListener("load", function() {
            lucide.createIcons();
        });
    </script>

</body>

</html>