
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Leaderboard</title>
    <link rel="stylesheet" href="../public/css/styles/classement.css">
    <link rel="stylesheet" href="../public/css/styles/header.css">
    <link rel="stylesheet" href="../public/css/dist/output.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script defer src="../public/js/classement.js"></script>
</head>
<body>
    <!-- Header -->
    <?php require_once '../includes/header.php'; ?>

    <main>
        <div class="leaderboard-header">
            <h1>Global Leaderboard</h1>
            <p>Top performers in challenges and hackathons</p>
            <div class="leaderboard-stats">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <span id="total-participants">n</span> participants
                </div>
                <div class="stat-item">
                    <i class="fas fa-trophy"></i>
                    <span id="total-points">n</span> points distribués
                </div>
                <div class="stat-item">
                    <i class="fas fa-medal"></i>
                    <span id="total-badges">n</span> badges gagnés
                </div>
            </div>
        </div>

        <div class="leaderboard-container" id="leaderboard">
            <!-- Les entrées du leaderboard seront ajoutées ici par JavaScript -->
        </div>

        
    </main>

</body>
</html>