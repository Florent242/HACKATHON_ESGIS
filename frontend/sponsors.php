<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Sponsors</title>
    <link rel="stylesheet" href="sponsors.css">
    <link rel="stylesheet" href="../public/css/styles/header.css">
    <link rel="stylesheet" href="../public/css/dist/output.css">
    <!-- Intégration du CDN Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
<?php require_once '../includes/header.php';?>

        <!-- En-tête -->
    <div class="newdiv">
        <h1>Nos Sponsors</h1>
        <p class="subtitle">Ils nous font confiance et soutiennent l'innovation</p>
    </div>


    <!-- Section Sponsors -->
    <section class="sponsors">
        <div class="cards">

            <!-- Carte Sponsor Platine -->
            <div class="card">
                <span class="badge platine">Sponsor Platine</span>
                <h3>TechCorp</h3>
                <p class="description">Leader mondial des solutions cloud et IA</p>
                <p class="amount">
                    <i data-lucide="trophy" class="icon"></i> 50,000,000 FCFA
                </p>
                <ul class="advantages">
                    <h1>Avantages</h1>
                    <li><i data-lucide="check-circle" class="icon"></i> Logo sur tous les supports</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Stand dédié</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Jury membre</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Présentation</li>
                </ul>
                <button class="profile-btn">
                    <i data-lucide="external-link"></i> Voir le profil
                </button>
            </div>

            <!-- Carte Sponsor Or -->
            <div class="card">
                <span class="badge gold">Sponsor Or</span>
                <h3>InnovTech</h3>
                <p class="description">Startup innovante en cybersécurité</p>
                <p class="amount">
                    <i data-lucide="trophy" class="icon"></i> 25,000,000 FCFA
                </p>
                <ul class="advantages">
                <h1>Avantages</h1>
                    <li><i data-lucide="check-circle" class="icon"></i> Logo sur site web</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Stand partagé</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Mentoring</li>
                </ul>
                <button class="profile-btn">
                    <i data-lucide="external-link"></i> Voir le profil
                </button>
            </div>

            <!-- Carte Sponsor Argent -->
            <div class="card">
                <span class="badge silver">Sponsor Argent</span>
                <h3>DataFlow</h3>
                <p class="description">Spécialiste en analyse de données</p>
                <p class="amount">
                    <i data-lucide="trophy" class="icon"></i> 10,000,000 FCFA
                </p>
                <ul class="advantages">
                <h1>Avantages</h1>
                    <li><i data-lucide="check-circle" class="icon"></i> Logo sur site web</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Présentation flash</li>
                    <li><i data-lucide="check-circle" class="icon"></i> Goodies</li>
                </ul>
                <button class="profile-btn">
                    <i data-lucide="external-link"></i> Voir le profil
                </button>
            </div>

        </div>
    </section>
    
    <section class="innov">
     <div class="entete">
        <h1>Devenez Sponsor</h1>
        <p>Rejoignez-nous dans notre mission de soutenir l'innovation et découvrez les talents de demain</p>
        <button class="down">Télécharger la brochure sponsoring</button>
    </div>

    <div class="cards">
            <div class="card">
                <i data-lucide="trophy" class="alias"></i>
                <h4>Visibilté Premium</h4>
                <p>Exposition maximale auprès des talents tech</p>
            </div>


            <div class="card">
                <i data-lucide="users" class="alias"></i>
                <h4>Recrutement</h4>
                <p>Accès privilégié aux meilleurs profils</p>
            </div>

            <div class="card">
                <i data-lucide="gift" class="alias"></i>
                <h4>Avantages Exclusifs</h4>
                <p>Package personnalisé selon vos besoins</p>
            </div>
        </div>
    </section>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>
