<link rel="stylesheet" href="/css/styles/user/header.css">
<link rel="stylesheet" href="/css/dist/output.css">
<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
<link rel="shortcut icon" href="/assets/20ans-gold.png" type="image/x-icon">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<script defer>
    // Rendre le token CSRF disponible globalement pour JavaScript
    window.csrfToken = '<?php echo $_SESSION['csrf_token']; ?>';
</script>
<script defer type="module" src="/js/user/header.js"></script>
<script defer src="/js/lucide.min.js"></script>