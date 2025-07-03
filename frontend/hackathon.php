<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Hackathons</title>
    <link rel="stylesheet" href="/css/styles/hackaton.css">
    <link rel="stylesheet" href="/css/styles/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
</head>
<body>
    <?php require_once '../includes/header.php'; ?>

    <main>
        <div class="hackathons-container">
            <div class="hackathons-header">
                <div>
                    <h1>Prochains Hackathons</h1>
                    <p>Participez à des hackathons passionnants et remportez des prix impressionnants</p>
                </div>
            </div>

            <div class="hackathons-grid">
                <!-- First Hackathon Card -->
                <div class="hackathon-card">
                    <div class="card-badge upcoming">À venir</div>
                    <h2>Esgis Global Hackathon 2025</h2>
                    <p>Rejoignez le plus grand hackathon de l'année ! Construisez des solutions innovantes pour des problèmes réels.</p>
                    
                    <div class="hackathon-details">
                        <div class="detail-item">
                            <i class="w-4 h-4 stroke-current" data-lucide="users"></i>
                            200 participants attendus
                        </div>
                        <div class="detail-item">
                            <i class="w-4 h-4 stroke-current" data-lucide="calendar"></i>
                            2025-07-10
                        </div>
                        <div class="detail-item">
                            <i class="w-4 h-4 stroke-current" data-lucide="badge-dollar-sign"></i>
                            $1,000
                        </div>
                    </div>
                    
                    <button class="view-details" onclick="window.location.href='/auth'">Voir les détails</button>
                </div>

                <!-- Second Hackathon Card -->
                <div class="hackathon-card">
                    <div class="card-badge upcoming">À venir</div>
                    <h2>HackSec(CTF)</h2>
                    <p>Un hackathon axés sur les défis de sécurité et les tests de pénétration.</p>
                    
                    <div class="hackathon-details">
                        <div class="detail-item">
                            <i class="w-4 h-4 stroke-current" data-lucide="users"></i>
                            200 participants attendus
                        </div>
                        <div class="detail-item">
                            <i class="w-4 h-4 stroke-current" data-lucide="calendar"></i>
                            2025-07-25
                        </div>
                        <div class="detail-item">
                            <i class="w-4 h-4 stroke-current" data-lucide="badge-dollar-sign"></i>
                            $5,000
                        </div>
                    </div>
                    
                    <button class="view-details" onclick="window.location.href='/auth'">Voir les détails</button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>