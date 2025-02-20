<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Leaderboard</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/classement.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/header.css">
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>

    <main>
        <div class="leaderboard-header">
            <h1>Global Leaderboard</h1>
            <p>Top performers in challenges and hackathons</p>
        </div>

        <div class="leaderboard-container" id="leaderboard">
            <!-- Les entrées du leaderboard seront ajoutées ici par JavaScript -->
        </div>
    </main>

    <script src="js/classement.js"></script>
</body>
</html>