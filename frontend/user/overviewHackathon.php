<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overview Hackathon</title>
    <link rel="stylesheet" href="/css/dist/output.css">
    <link rel="stylesheet" href="/css/styles/user/header.css">
    <link rel="stylesheet" href="/css/styles/user/overviewHackathon.css">
    <script src="/js/user/overviewHackathon.js" defer></script>
    <script src="https://unpkg.com/lucide@latest">
        lucide.createIcons();
    </script>
</head>

<body>
    <?php require_once '../includes/user/header.php'; ?>

    <div class="container">
        <section id="header">

        </section>

        <main>

        </main>
    </div>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?? '' ?>">
</body>

</html>