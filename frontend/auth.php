<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Sign up</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/auth.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <!--link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css"-->
    <script defer src="/HACKATHON_ESGIS/public/js/auth.js"></script>
    <!-- Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.280.0/dist/umd/lucide.min.js"></script>



</head>
<body>
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
                <form>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="display">
                        <span><i data-lucide="mail"></i></span> 
                        <input type="email" id="email" placeholder="etudiant@esgis.bj">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="display">
                        <span><i data-lucide="key"></i></span>
                        <input type="password" id="password" placeholder="............">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn"> <i data-lucide="send"></i>Se connecter</button>
                </form>
            </div>

            <div class="auth-form hidden" id="registerForm">
                <h1>Inscription</h1>
                <p>Créez votre compte EsgisHub</p>
                <br>
                <br>
                <form>
                    <div class="form-group">
                        <label for="fullName">Nom complet</label>
                        <div class="display">
                        <span><i data-lucide="user"></i></span>
                        <input type="text" id="fullName" placeholder="Votre nom">
                        </div>
        
                       
                        
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="display">
                        <span><i data-lucide="mail"></i></span>
                        <input type="email" id="email" placeholder="etudiant@esgis.bj">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <div class="display">
                        <span><i data-lucide="key"></i></span>
                        <input type="password" id="password" placeholder="............">
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmer le mot de passe</label>
                        <div class="display">
                        <span> <i data-lucide="key"></i></span>
                        <input type="password" id="confirmPassword" placeholder="............">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn"> <i data-lucide="send"></i>S'inscrire</button>
                </form>
            </div>
        </div>
    </div>
   
    <script>
  window.addEventListener("load", function () {
    lucide.createIcons();
  });
</script>


</body>
</html>