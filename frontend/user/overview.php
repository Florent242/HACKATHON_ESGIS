<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/css/styles/user/overview.css">
    <link rel="stylesheet" href="/css/dist/output.css">

    <!-- Ajout de Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/user/overview.js" defer></script>
</head>
<body>
    <button onclick="window.location.href='/user/teams'" class="flexDivIcon returnBtn">
        <i data-lucide="arrow-left"></i>
        Retour
    </button>
    <section id="teamInfo">
        <div id="teamAvatar">
            
        </div>
        <div id="teamStats">        
            <p class="name"> <strong></strong>  <span>[Catégorie]</span><p> 
            <div style="display:flex; gap:30px;">
                <div class="member flexDivIcon" style="gap:20px;">
                    <i data-lucide="users" class="icon" style=" background:#0859c61a; color:var(--blue);"></i>
                    <p>Membres <br> <span   ></span></p>
                </div> 
                <div class="score flexDivIcon" style="gap:20px;">
                    <i data-lucide="trophy" class="icon" style="gap:20px; background:#eab3081a; color:var(--yellow);"></i>
                    <p>Score <br> <span> 0 pts</span></p>
                </div>
            </div>                
                    
        </div>
        
    </section>

    <!-- Les bouttons de navigations en rapport avec les infos de la team -->
    <ul class="nav-bar">
        <li class="focus">
            <i data-lucide="info"></i>
            <span>Détails</span>            
        </li>

        <li>
            <i data-lucide="users"></i>
            <span>Membres</span>            
        </li>
        
    </ul>

    <!-- La section A propos de la team -->
    <section id="ulOptionContentZone" class="about" >    
        <div class="aboutFirstContainer">
            <p class="flexDivIcon"style="gap:20px;" >
                <i data-lucide="shield"></i>
                <strong>A propos de nous</strong>
            </p>      
            
            <button id="editBtn" class="flexDivIcon" type="button" style="gap:10px; color:white;">
                <i data-lucide="edit"></i>
                <span>Modifier</span>
            </button>
        </div>                

        
        <p id="aboutText"> </p>

    </section>

    <div id="csrfForm">
        <input type="hidden" hidden name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?> ">
    </div>
    
    <!-- Initialisation de Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>

