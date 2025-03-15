<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Gestion des Challenges</h2>
    <button class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Nouveau Challenge
    </button>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Difficulté</th>
                <th>Participants</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>API Security Challenge</td>
                <td><span class="badge badge-purple">Avancé</span></td>
                <td>45</td>
                <td><span class="badge badge-blue">En cours</span></td>
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
    <h3 class="card-title">Points des défis soumis</h3>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Défi</th>
                    <th>Points</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Pierre Martin</td>
                    <td>API Security Challenge</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            150
                        </div>
                    </td>
                    <td>11/05/2024</td>
                </tr>
                <tr>
                    <td>Marie Dupont</td>
                    <td>Front-end Performance</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            100
                        </div>
                    </td>
                    <td>10/05/2024</td>
                </tr>
                <tr>
                    <td>Jean Durand</td>
                    <td>Database Optimization</td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                            120
                        </div>
                    </td>
                    <td>09/05/2024</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/admin_test/footer.php'; ?>

