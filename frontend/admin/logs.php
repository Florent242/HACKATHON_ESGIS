<?php require_once '../includes/admin/header.php'; ?>


<h1 class="page-title">Logs du système</h1>

<div class="card-header" style="justify-content: flex-end; margin-bottom: 20px;">
    <button class="btn btn-primary">
        <i class="fas fa-download btn-icon"></i> Exporter les logs
    </button>
</div>

<div class="stats-container">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Logs</h3>
            <div class="number">7</div>
        </div>
        <div class="stat-icon purple">
            <i class="fas fa-history"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Connections</h3>
            <div class="number">1</div>
        </div>
        <div class="stat-icon green">
            <i class="fas fa-sign-in-alt"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Actions Équipes</h3>
            <div class="number">2</div>
        </div>
        <div class="stat-icon orange">
            <i class="fas fa-users"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Challenges</h3>
            <div class="number">1</div>
        </div>
        <div class="stat-icon purple">
            <i class="fas fa-trophy"></i>
        </div>
    </div>
</div>

<div class="search-container">
    <i class="fas fa-search search-icon"></i>
    <input type="text" class="search-input" placeholder="Rechercher dans les logs..." data-table="logsTable">
</div>

<div style="display: flex; gap: 10px; margin-bottom: 20px;">
    <div class="dropdown" style="flex: 1;">
        <button class="dropdown-toggle" style="width: 100%; justify-content: space-between;">
            <span>Tous les logs</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="dropdown-menu">
            <a href="#" class="dropdown-item">Tous les logs</a>
            <a href="#" class="dropdown-item">Connexions</a>
            <a href="#" class="dropdown-item">Actions utilisateurs</a>
            <a href="#" class="dropdown-item">Modifications système</a>
        </div>
    </div>
    
    <div class="dropdown" style="flex: 1;">
        <button class="dropdown-toggle" style="width: 100%; justify-content: space-between;">
            <span>Toutes les périodes</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <div class="dropdown-menu">
            <a href="#" class="dropdown-item">Toutes les périodes</a>
            <a href="#" class="dropdown-item">Aujourd'hui</a>
            <a href="#" class="dropdown-item">Cette semaine</a>
            <a href="#" class="dropdown-item">Ce mois</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Logs d'activité système</div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">Total: 7 entrées</div>
    </div>
    
    <div class="activity-feed">
        <div class="activity-item">
            <div class="activity-icon" style="background-color: rgba(59, 130, 246, 0.2); color: #3b82f6;">
                <i class="fas fa-user"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Lionel SISSO</div>
                <div class="activity-subtitle">S'est connecté à la plateforme</div>
                <div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">IP: 192.168.1.1, Navigateur: Chrome</div>
            </div>
            <div class="activity-time">
                Il y a 1 heure
            </div>
        </div>
        
        <div class="activity-item">
            <div class="activity-icon" style="background-color: rgba(16, 185, 129, 0.2); color: #10b981;">
                <i class="fas fa-file-code"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Marie Dupont</div>
                <div class="activity-subtitle">A soumis une solution pour le challenge 'API Security'</div>
                <div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">Soumission #2067, Statut: En attente</div>
            </div>
            <div class="activity-time">
                Il y a 2 heures
            </div>
        </div>
        
        <div class="activity-item">
            <div class="activity-icon" style="background-color: rgba(245, 158, 11, 0.2); color: #f59e0b;">
                <i class="fas fa-users"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Jean Martin</div>
                <div class="activity-subtitle">A créé une nouvelle équipe 'CodeMasters'</div>
                <div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">3 membres initiaux</div>
            </div>
            <div class="activity-time">
                Il y a 3 heures
            </div>
        </div>
        
        <div class="activity-item">
            <div class="activity-icon" style="background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Sophie Laurent</div>
                <div class="activity-subtitle">S'est inscrite au hackathon 'ESGIS Hackathon 2024'</div>
                <div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">En tant que participant individuel</div>
            </div>
            <div class="activity-time">
                Il y a 1 jour
            </div>
        </div>
        
        <div class="activity-item">
            <div class="activity-icon" style="background-color: rgba(109, 40, 217, 0.2); color: #6d28d9;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="activity-content">
                <div class="activity-title">Administrateur</div>
                <div class="activity-subtitle">A ajouté une nouvelle ressource 'Guide de sécurité API'</div>
                <div class="activity-subtitle" style="font-size: 0.75rem; color: #6b7280;">Catégorie: Sécurité, Type: Document PDF</div>
            </div>
            <div class="activity-time">
                Il y a 2 jours
            </div>
        </div>
    </div>
</div>
