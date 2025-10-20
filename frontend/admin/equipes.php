<html lang='fr'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once '../includes/admin/head.php'; ?>
    <title>Gestion des Équipes</title>
    <link rel="stylesheet" href="/css/styles/admin/teams.css">
    <script defer src="/js/admin/team.js"></script>
</head>

<body>
    <?php require_once '../includes/admin/header.php'; ?>

    <main>
        <h1 class="page-title">Gestion des Équipes</h1>

        <div class="card-header" style="justify-content: flex-end; margin-bottom: 20px;">
            <button class="btn btn-primary">
                <i class="fas fa-users btn-icon"></i> Créer une équipe
            </button>
        </div>


        <!-- <form id="edit-form" enctype="multipart/form-data">
            <label for="logo" class="logo">
                <input type="file" id="logo" name="logo" hidden>
            </label>
            <div>
                <label for="team-name"> Nom de l'équipe</label><br>
                <input type="text" id="team-name" name="team-name" placeholder="Entrez un nom"/>
            </div>
            <div>
                <label>Description</label><br>
                <textarea name="description" id="description" placeholder="Entrez une description"></textarea>
            </div>
            
            
            <ul class="team-members">Equipe
                <li>
                    <img class="profil rounded" src="/assets/Esgislogo.png" width="45" height="45" alt="profil"/>
                    <div style="display: flex; gap: 10px; align-items: flex-start; width: 100%;">
                        <div>
                           <p>maumau</p>
                           <p>Frontend</p> 
                        </div>                    
                        <div class="lead-flag flex" style="gap:5px; transform: scale(0.8);">
                            <p>Leader</p>
                            <i data-lucide="crown" width="15" height="15" style="transform:translateY(1px);"></i>
                        </div>
                    </div>
                    <div class="flex buttons-form">
                        <button type="button" class="promote-leader flex"><span>Promouvoir</span> <i data-lucide="crown" width="15" height="15"></i></button>
                        <button type="button" class="remove-member flex" ><span>Retirer</span> <i data-lucide="trash-2" width="15" height="15"></i></button>
                    </div>                    
                </li>
                <div class="view-more">Voir tout</div>
            </ul>

            <div style="position: relative;">
                <label>Intégrer des membres</label>
                <input type="search" placeholder="Rechercher des participants" style="padding-left: 30px;"/>
                <i data-lucide="search" style="position: absolute; left: 10px; top: 58%;" width="15" height="15"></i>
            </div>
            
            
            <button type="submit" style="width: 49.5%; padding-block: 5px; border-radius: 5px; background: rgba(17, 102, 249, 0.84);">Valider</button>
            <button type="submit" style="width: 49.5%; padding-block: 5px; border-radius: 5px; background: rgba(188, 188, 188, 0.84);">Annuler</button>

        </form> -->

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-info teams-count">
                    <h3>Total Équipes</h3>
                    <div class="number"></div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info members-count">
                    <h3>Membres</h3>
                    <div class="number"></div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info hackathons-count">
                    <h3>Hackathons</h3>
                    <div class="number"></div>
                </div>
                <div class="stat-icon green">
                    <i class="fas fa-trophy"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info challenges-count">
                    <h3>Défis réalisés</h3>
                    <div class="number"></div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-tasks"></i>
                </div>
            </div>
        </div>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input" placeholder="Rechercher une équipe..." data-table="teamsTable">
            <i data-lucide="sliders-horizontal" class="filter-icon standard-hover" width="30" height="25"></i>
            <div class="filter-menu card">
                <h2>Filtres</h2>
                <hr style="width:calc(100% + 3rem); transform: translateX(-1.5rem); margin-block: 10px; border: solid 0.1px rgba(222, 220, 220, 0.27)">
                <div>
                    <h3 >Par Hackathons</h3>
                    <!-- <div>   
                        <h4>Type</h4>   
                        <ul class="options-filter flexDiv">
                            <li class="option selected">Tous</li>
                            <li class="option">Dev</li>
                            <li class="option">Securité</li>
                        </ul>                  
                    </div> -->
                    <div>   
                        <!-- <h4>Evènement</h4>   -->
                        <select name="hackathon" id="hackathon">
                            <option value="" selected hidden>Liste des hackathons (Tous)</option>
                            <option value="">DevSec</option>
                        </select>
                    </div>

                </div>
                <hr style="width:calc(100% + 3rem); transform: translateX(-1.5rem); margin-block: 10px; border: solid 0.1px rgba(222, 220, 220, 0.27);">

                <div>
                    <h3>Par Statut</h3>
                    <ul class="options-filter flexDiv">
                        <li class="option selected">Tous</li>
                        <li class="option">Actif</li>
                        <li class="option">Supspendu</li>
                        <li class="option">Inactif</li>
                    </ul>
                    
                </div>
                <hr style="width:calc(100% + 3rem); transform: translateX(-1.5rem); margin-block: 10px; border: solid 0.1px rgba(222, 220, 220, 0.27);">

                <div>
                    <h3>Par Taille</h3>
                    <ul class="options-filter flexDiv">
                       <li class="flexDiv option"> <i data-lucide="arrow-up-narrow-wide"></i> Ascendant</li>
                        <li class="flexDiv option"> <i data-lucide="arrow-down-wide-narrow"></i> Descendant</li>                  
                    </ul>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-container">
                <table id="teamsTable">
                    <thead>
                        <tr>
                            <th>Nom de l'équipe</th>
                            <th>Membres</th>
                            <th>Hackathons</th>
                            <th>Challenges</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top: 30px;">
            <div class="card-header">
                <div class="card-title">Équipes en vedette</div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; padding-block: 20px;">
                <div class="team-card" style="background-color: var(--background-lighter); border-radius: 0.5rem; padding: 20px;">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                            <i class="fas fa-users" style="color: #6d28d9;"></i>
                        </div>
                        <h3>DevOps Masters</h3>
                    </div>
                    <p>5 membres</p>

                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
                            <div style="font-weight: bold;">2</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
                            <div style="font-weight: bold;">7</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
                            <div style="font-weight: bold; color: #10b981;">Actif</div>
                        </div>
                    </div>
                </div>

                <div class="team-card" style="background-color: var(--background-lighter); border-radius: 0.5rem; padding: 20px;">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                            <i class="fas fa-users" style="color: #6d28d9;"></i>
                        </div>
                        <h3>Frontend Wizards</h3>
                    </div>
                    <p>3 membres</p>

                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
                            <div style="font-weight: bold;">1</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
                            <div style="font-weight: bold;">5</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
                            <div style="font-weight: bold; color: #10b981;">Actif</div>
                        </div>
                    </div>
                </div>

                <div class="team-card" style="background-color: var(--background-lighter); border-radius: 0.5rem; padding: 20px;">
                    <div style="display: flex; align-items: center; margin-bottom: 15px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(109, 40, 217, 0.2); display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                            <i class="fas fa-users" style="color: #6d28d9;"></i>
                        </div>
                        <h3>Data Science Squad</h3>
                    </div>
                    <p>4 membres</p>

                    <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Hackathons</div>
                            <div style="font-weight: bold;">3</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Défis</div>
                            <div style="font-weight: bold;">12</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);">Statut</div>
                            <div style="font-weight: bold; color: #f59e0b;">Suspendu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</body>

</html>