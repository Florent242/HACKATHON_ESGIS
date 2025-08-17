<!DOCTYPE html>
<html lang="fr">
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
        <div class="flex gap-5">
            <div id="teamAvatar">
                
            </div>
    
            <div id="teamStats">        
                <p class="name flex gap-5">
                    <strong></strong>  
                    <span class="team-type flex justify-center items-center"></span>
                </p> 
                <div class="flex gap-5">
                    <div class="member flexDivIcon gap-5">
                        <i data-lucide="users" class="icon" style=" background:#0859c61a; color:var(--blue);"></i>
                        <div class="w-full size-fit">Membres<br><span></span></div>
                    </div> 
                    <div class="score flexDivIcon gap-5">
                        <i data-lucide="trophy" class="icon" style="gap:20px; background:#eab3081a; color:var(--yellow);"></i>
                        <div class="w-full size-fit">Score<br><span class="flex flex-row justify-center items-center whitespace-nowrap"> 0 pts</span></div>
                    </div>
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
                <strong class="flex justify-center items-center text-medium">A propos de nous</strong>
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

