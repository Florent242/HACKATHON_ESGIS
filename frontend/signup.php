<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Sign up</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/inscription.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <script defer src="/HACKATHON_ESGIS/public/js/inscription.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-tabs">
                <button class="auth-tab">Connexion</button>
                <button class="auth-tab active">Inscription</button>
            </div>
            
            <div class="auth-form">
                <h1>Inscription</h1>
                <p>Créez votre compte EsgisHub</p>

                <form id="registerForm">
                    <div class="form-group">
                        <label for="fullName">Nom complet</label>
                        <input type="text" id="fullName" placeholder="Votre nom">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="exemple@esgis.bj">
                    </div>

                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password">
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirmer le mot de passe</label>
                        <input type="password" id="confirmPassword">
                    </div>

                    <button type="submit" class="submit-btn">S'inscrire</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>