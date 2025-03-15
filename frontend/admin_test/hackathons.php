<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Gestion des Hackathons</h2>
    <button class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nouveau Hackathon
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Date</th>
                <th>Participants</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>ESGIS Hackathon 2024</td>
                <td>15 Mars 2024</td>
                <td>120</td>
                <td><span class="badge badge-blue">À venir</span></td>
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
        <div class="activity-avatar" style="background-color: #805ad5;">A</div>
        <div class="activity-content">
            <p class="activity-user">Admin</p>
            <p class="activity-action">A créé un nouveau hackathon : ESGIS Hackathon 2024</p>
            <p class="activity-time">Il y a 2 heures</p>
        </div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar" style="background-color: #ed8936;">EC</div>
        <div class="activity-content">
            <p class="activity-user">Équipe CodeMasters</p>
            <p class="activity-action">S'est inscrite au hackathon ESGIS Hackathon 2024</p>
            <p class="activity-details" style="font-size: 0.75rem; color: #6c6c7c; margin-top: 0.25rem;">5 membres</p>
            <p class="activity-time">Il y a 3 heures</p>
        </div>
    </div>
    
    <div class="activity-item">
        <div class="activity-avatar">JD</div>
        <div class="activity-content">
            <p class="activity-user">Jean Durand</p>
            <p class="activity-action">S'est connecté à la plateforme</p>
            <p class="activity-time">Il y a 4 heures</p>
        </div>
    </div>
</div>

<?php include 'includes/admin_test/footer.php'; ?>

