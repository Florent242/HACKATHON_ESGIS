<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Gestion des Utilisateurs</h2>
    <button class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Ajouter utilisateur
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3 class="stat-title">Total Utilisateurs</h3>
        <p class="stat-value">4</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Administrateurs</h3>
        <p class="stat-value">1</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Utilisateurs Actifs</h3>
        <p class="stat-value">3</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Utilisateurs Suspendus</h3>
        <p class="stat-value">1</p>
    </div>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Lionel SISSO</td>
                <td>sisso.lionel@esgis.bj</td>
                <td><span class="badge badge-purple">Admin</span></td>
                <td><span class="badge badge-green">Actif</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Marie Dupont</td>
                <td>marie.dupont@example.com</td>
                <td><span class="badge">Utilisateur</span></td>
                <td><span class="badge badge-green">Actif</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Jean Martin</td>
                <td>jean.martin@example.com</td>
                <td><span class="badge">Utilisateur</span></td>
                <td><span class="badge badge-yellow">Suspendu</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Sophie Laurent</td>
                <td>sophie.laurent@example.com</td>
                <td><span class="badge badge-blue">Modérateur</span></td>
                <td><span class="badge badge-green">Actif</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3 class="card-title">Activités récentes</h3>
    
    <div class="activity-item">
        <div class="activity-avatar">LS</div>
        <div class="activity-content">
            <p class="activity-user">Lionel SISSO</p>
            <p class="activity-action">S'est connecté à la plateforme</p>
            <p class="activity-time">Il y a 1 heure</p>
        </div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar" style="background-color: #8257e6;">LS</div>
        <div class="activity-content">
            <p class="activity-user">Lionel SISSO</p>
            <p class="activity-action">A soumis une solution pour le challenge 'API Security'</p>
            <p class="activity-time">Il y a 2 heures</p>
        </div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar" style="background-color: #8257e6;">LS</div>
        <div class="activity-content">
            <p class="activity-user">Lionel SISSO</p>
            <p class="activity-action">S'est inscrit au hackathon ESGIS Hackathon 2024</p>
            <p class="activity-time">Il y a 1 jour</p>
        </div>
    </div>
</div>

<?php include 'includes/admin_test/footer.php'; ?>
