
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EsgisHub - Leaderboard</title>
    <?php require_once '../includes/admin/head.php'; ?>
    <link rel="stylesheet" href="/css/styles/admin/classement.css">
    <link rel="stylesheet" href="/css/styles/admin/header.css">
    <link rel="stylesheet" href="/css/dist/output.css">
    <script src="/js/admin/classement.js"></script>
</head>
<body>
    <!-- Header -->
    <?php require_once '../includes/admin/header.php'; ?>

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