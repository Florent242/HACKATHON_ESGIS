<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Logs du système</h2>
    <button class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Exporter les logs
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3 class="stat-title">Total Logs</h3>
        <p class="stat-value">7</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Connections</h3>
        <p class="stat-value">1</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Actions Équipes</h3>
        <p class="stat-value">2</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Challenges</h3>
        <p class="stat-value">1</p>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <input type="text" placeholder="Rechercher dans les logs..." class="search-input" style="padding: 0.5rem; border-radius: 0.375rem; background-color: #2a2a36; border: none; color: white; width: 100%; max-width: 400px;">
        
        <div style="display: flex; gap: 1rem;">
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle" data-toggle="dropdown" data-target="#typeDropdown">
                    Tous les logs
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div id="typeDropdown" class="dropdown-menu">
                    <a href="#" class="dropdown-item">Tous les logs</a>
                    <a href="#" class="dropdown-item">Connexions</a>
                    <a href="#" class="dropdown-item">Actions</a>
                    <a href="#" class="dropdown-item">Erreurs</a>
                </div>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle" data-toggle="dropdown" data-target="#periodDropdown">
                    Toutes les périodes
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 0.5rem;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div id="periodDropdown" class="dropdown-menu">
                    <a href="#" class="dropdown-item">Toutes les périodes</a>
                    <a href="#" class="dropdown-item">Aujourd'hui</a>
                    <a href="#" class="dropdown-item">Cette semaine</a>
                    <a href="#" class="dropdown-item">Ce mois</a>
                </div>
            </div>
        </div>
    </div>
    
    <h3 class="card-title">Logs d'activité système</h3>
    <p style="text-align: right; font-size: 0.75rem; color: #a0a0b0; margin-bottom: 1rem;">Total: 7 entrées</p>
    
    <div class="activity-item">
        <div class="activity-avatar">LS</div>
        <div class="activity-content">
            <p class="activity-user">Lionel SISSO</p>
            <p class="activity-action">S'est connecté à la plateforme</p>
            <p class="activity-time">Il y a 1 heure</p>
            <p class="activity-details" style="font-size: 0.75rem; color: #6c6c7c; margin-top: 0.25rem;">IP: 192.168.1.1, Navigateur: Chrome</p>
        </div>
        <div style="margin-left: auto; color: #a0a0b0; font-size: 0.75rem;">Il y a 1 heure</div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar" style="background-color: #48bb78;">MD</div>
        <div class="activity-content">
            <p class="activity-user">Marie Dupont</p>
            <p class="activity-action">A soumis une solution pour le challenge 'API Security'</p>
            <p class="activity-details" style="font-size: 0.75rem; color: #6c6c7c; margin-top: 0.25rem;">Soumission #2567, Statut: En attente</p>
        </div>
        <div style="margin-left: auto; color: #a0a0b0; font-size: 0.75rem;">Il y a 2 heures</div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar" style="background-color: #ed8936;">JM</div>
        <div class="activity-content">
            <p class="activity-user">Jean Martin</p>
            <p class="activity-action">A créé une nouvelle équipe 'CodeMasters'</p>
            <p class="activity-details" style="font-size: 0.75rem; color: #6c6c7c; margin-top: 0.25rem;">3 membres initiaux</p>
        </div>
        <div style="margin-left: auto; color: #a0a0b0; font-size: 0.75rem;">Il y a 3 heures</div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar" style="background-color: #805ad5;">SL</div>
        <div class="activity-content">
            <p class="activity-user">Sophie Laurent</p>
            <p class="activity-action">S'est inscrite au hackathon 'ESGIS Hackathon 2024'</p>
            <p class="activity-details" style="font-size: 0.75rem; color: #6c6c7c; margin-top: 0.25rem;">En tant que participant individuel</p>
        </div>
        <div style="margin-left: auto; color: #a0a0b0; font-size: 0.75rem;">Il y a 1 jour</div>
    </div>
</div>

<?php include 'includes/admin_test/footer.php'; ?>
