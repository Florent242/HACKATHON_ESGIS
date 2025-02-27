<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Sign in</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/connexion.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/connexion.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-tabs">
                <button href="connexion.html" class="auth-tab">Connexion</button>
                <button href="inscription.html"class="auth-tab active">Inscription</button>
            </div>
            
            <div class="auth-form">
                <h1>Connexion</h1>
                <p>Connectez-vous à votre compte EsgisHub</p>

                <form id="loginForm">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="exemple@esgis.bj">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password">
                    </div>

                    <button type="submit" class="submit-btn">Se connecter</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>