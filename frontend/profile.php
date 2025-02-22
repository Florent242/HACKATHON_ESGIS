<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Profil</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/profil.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/profil.js"></script>
    <!-- Lucide Icons -->
    <!-- <script src="https://unpkg.com/lucide@latest"></script> -->
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <!-- Header Profile -->
        <div class="profile-header">
            <div class="avatar">
                <img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/WhatsApp%20Image%202025-02-17%20at%2020.01.19(1)-DrGVjho3UwMBPBbqACcTldIIzG4fjN.jpeg" alt="SISSO Lionel">
            </div>
            <div class="profile-info">
                <h1>SISSO Lionel</h1>
                <p>Membre depuis Janvier 2024</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-grid">
            <!-- Statistics Card -->
            <div class="card">
                <h2>Statistiques</h2>
                <p class="subtitle">Vos accomplissements sur EsgisHub</p>
                
                <div class="stats-list">
                    <div class="stat-item">
                        <span>Défis complétés</span>
                        <span class="value">12</span>
                    </div>
                    <div class="stat-item">
                        <span>Points</span>
                        <span class="value">1500</span>
                    </div>
                    <div class="stat-item">
                        <span>Rang</span>
                        <span class="value">Top 10</span>
                    </div>
                </div>
            </div>

            <!-- Badges Card -->
            <div class="card">
                <h2>Badges</h2>
                <p class="subtitle">Vos badges obtenus</p>
                
                <div class="badges-list">
                    <div class="badge">
                        <span class="badge-icon">🏆</span>
                        <span>Champion</span>
                    </div>
                    <div class="badge">
                        <span class="badge-icon">🚀</span>
                        <span>Innovateur</span>
                    </div>
                    <div class="badge">
                        <span class="badge-icon">💻</span>
                        <span>Code Master</span>
                    </div>
                    <div class="badge">
                        <span class="badge-icon">🔒</span>
                        <span>Security Pro</span>
                    </div>
                </div>
            </div>

            <!-- Edit Profile Card -->
            <div class="card full-width">
                <h2>Modifier le profil</h2>
                <p class="subtitle">Mettez à jour vos informations personnelles</p>

                <form id="profileForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName">Nom complet</label>
                            <input type="text" id="fullName" value="SISSO Lionel">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" value="sisso.lionel@esgis.bj">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="specialization">Spécialisation</label>
                            <input type="text" id="specialization" value="Développement Web">
                        </div>
                        <div class="form-group">
                            <label for="github">GitHub</label>
                            <input type="text" id="github" value="@sissolionel">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio</label>
                        <textarea id="bio">Passionné par le développement web et la cybersécurité. Étudiant à ESGIS.</textarea>
                    </div>

                    <button type="submit" class="save-btn">Sauvegarder les modifications</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>