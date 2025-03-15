<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Gestion des Soumissions</h2>
    <button class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Exporter
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3 class="stat-title">Total Soumissions</h3>
        <p class="stat-value">5</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Points attribués</h3>
        <p class="stat-value">230</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">En attente</h3>
        <p class="stat-value">2</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Taux d'approbation</h3>
        <p class="stat-value">40%</p>
    </div>
</div>

<div style="margin-bottom: 1.5rem;">
    <input type="text" placeholder="Rechercher une soumission..." class="search-input" style="padding: 0.5rem; border-radius: 0.375rem; background-color: #2a2a36; border: none; color: white; width: 100%;">
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Équipe</th>
                <th>Défi</th>
                <th>Points</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Pierre Martin</td>
                <td>Frontend Wizards</td>
                <td>API Security Challenge</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        150
                    </div>
                </td>
                <td>11/05/2024</td>
                <td><span class="badge badge-green">Approuvé</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Marie Dupont</td>
                <td>Solo</td>
                <td>Front-end Performance</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        100
                    </div>
                </td>
                <td>10/05/2024</td>
                <td><span class="badge badge-yellow">En attente</span></td>
                <td>
                    <div style="display: flex; gap: 0.5rem;">
                        <button class="btn btn-icon-only">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </button>
                        <button class="btn btn-icon-only" style="color: #48bb78;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </button>
                        <button class="btn btn-icon-only" style="color: #f56565;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                </td>
            </tr>
            <tr>
                <td>Jean Durand</td>
                <td>Data Science Squad</td>
                <td>Database Optimization</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        120
                    </div>
                </td>
                <td>09/05/2024</td>
                <td><span class="badge badge-red">Rejeté</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="card" style="margin-top: 2rem;">
    <h3 class="card-title">Détails des soumissions récentes</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
        <div class="card" style="background-color: #2a2a36;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <h4>API Security Challenge</h4>
                <span class="badge badge-green">Approuvé</span>
            </div>
            <p style="font-size: 0.875rem; color: #a0a0b0; margin-bottom: 0.5rem;">Soumis par Pierre Martin (Frontend Wizards)</p>
            <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <span>150 points</span>
            </div>
            <p style="font-size: 0.75rem; color: #a0a0b0; text-align: right;">11/05/2024</p>
        </div>
        
        <div class="card" style="background-color: #2a2a36;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <h4>Front-end Performance</h4>
                <span class="badge badge-yellow">En attente</span>
            </div>
            <p style="font-size: 0.875rem; color: #a0a0b0; margin-bottom: 0.5rem;">Soumis par Marie Dupont (Solo)</p>
            <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #f6e05e; margin-right: 0.25rem;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                <span>100 points</span>
            </div>
            <p style="font-size: 0.75rem; color: #a0a0b0; text-align: right;">10/05/2024</p>
        </div>
    </div>
</div>

<?php include 'includes/admin_test/footer.php'; ?>

