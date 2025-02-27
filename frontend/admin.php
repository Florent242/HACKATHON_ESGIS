<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esgis Admin</title>
    <link rel="stylesheet" href="admin.css?v=1.1">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.280.0/dist/umd/lucide.min.js"></script>

</head>
<body>
    <div class="ad-container">
    <div class="ad-card">
    <div class="ad-form" id="loginForm">
        <h1>Espace Administrateur</h1>
        <p>Connectez vous avez vos identifiants spéciaux</p>
        <br><br>
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
</div>
    <script>
  window.addEventListener("load", function () {
    lucide.createIcons();
  });
</script>

</body>
</html>