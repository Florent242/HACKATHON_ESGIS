<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Leaderboard</title>
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/classement.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/styles/user/header.css">
    <link rel="stylesheet" href="/HACKATHON_ESGIS/public/css/dist/output.css">
    <script defer src="/HACKATHON_ESGIS/public/js/user/classement.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <!-- Header -->
    <?php require_once '../includes/user/header.php'; ?>

    <main>
        <div class="leaderboard-header">
            <h1>Global Leaderboard</h1>
            <p>Top performers in challenges and hackathons</p>
        </div>

        <div class="leaderboard-container" id="leaderboard">
            <!-- Les entrées du leaderboard seront ajoutées ici par JavaScript -->
        </div>
    </main>

</body>
</html>