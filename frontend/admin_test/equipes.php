<?php include 'includes/admin_test/header.php'; ?>

<div class="page-header">
    <h2 class="page-title">Gestion des Équipes</h2>
    <button class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="btn-icon"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Créer une équipe
    </button>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3 class="stat-title">Total Équipes</h3>
        <p class="stat-value">4</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Membres</h3>
        <p class="stat-value">18</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Participations</h3>
        <p class="stat-value">8</p>
    </div>
    <div class="stat-card">
        <h3 class="stat-title">Défis réalisés</h3>
        <p class="stat-value">33</p>
    </div>
</div>

<div style="margin-bottom: 1.5rem;">
    <input type="text" placeholder="Rechercher une équipe..." class="search-input" style="padding: 0.5rem; border-radius: 0.375rem; background-color: #2a2a36; border: none; color: white; width: 100%;">
</div>

<div class="table-container">
    <table class="table">
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
            <tr>
                <td>DevOps Masters</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #a0a0b0; margin-right: 0.25rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        5
                    </div>
                </td>
                <td>2</td>
                <td>7</td>
                <td><span class="badge badge-green">Actif</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Frontend Wizards</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #a0a0b0; margin-right: 0.25rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        3
                    </div>
                </td>
                <td>1</td>
                <td>5</td>
                <td><span class="badge badge-green">Actif</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Data Science Squad</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #a0a0b0; margin-right: 0.25rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        4
                    </div>
                </td>
                <td>3</td>
                <td>12</td>
                <td><span class="badge badge-yellow">Suspendu</span></td>
                <td>
                    <button class="btn btn-icon-only">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                    </button>
                </td>
            </tr>
            <tr>
                <td>Cloud Innovators</td>
                <td>
                    <div style="display: flex; align-items: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #a0a0b0; margin-right: 0.25rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        6
                    </div>
                </td>
                <td>2</td>
                <td>9</td>
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
    <h3 class="card-title">Équipes en vedette</h3>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <div class="card" style="background-color: #2a2a36;">
            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background-color: #8257e6; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div>
                    <h4>DevOps Masters</h4>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">5 membres</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                <div>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">Hackathons</p>
                    <p style="font-weight: 600;">2</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">Défis</p>
                    <p style="font-weight: 600;">7</p>
                </div>
            </div>
            
            <div style="text-align: right; margin-top: 1rem;">
                <span class="badge badge-green">Actif</span>
            </div>
        </div>
        
        <div class="card" style="background-color: #2a2a36;">
            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background-color: #8257e6; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div>
                    <h4>Frontend Wizards</h4>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">3 membres</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                <div>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">Hackathons</p>
                    <p style="font-weight: 600;">1</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">Défis</p>
                    <p style="font-weight: 600;">5</p>
                </div>
            </div>
            
            <div style="text-align: right; margin-top: 1rem;">
                <span class="badge badge-green">Actif</span>
            </div>
        </div>
        
        <div class="card" style="background-color: #2a2a36;">
            <div style="display: flex; align-items: center; margin-bottom: 1rem;">
                <div style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; background-color: #8257e6; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <div>
                    <h4>Data Science Squad</h4>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">4 membres</p>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.5rem;">
                <div>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">Hackathons</p>
                    <p style="font-weight: 600;">3</p>
                </div>
                <div>
                    <p style="font-size: 0.75rem; color: #a0a0b0;">Défis</p>
                    <p style="font-weight: 600;">12</p>
                </div>
            </div>
            
            <div style="text-align: right; margin-top: 1rem;">
                <span class="badge badge-yellow">Suspendu</span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/admin_test/footer.php'; ?>
