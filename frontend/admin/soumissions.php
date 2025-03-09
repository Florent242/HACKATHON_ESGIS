<?php require_once '../includes/admin/header.php'; ?>

<h1 class="page-title">Gestion des Soumissions</h1>

<div class="card-header" style="justify-content: flex-end; margin-bottom: 20px;">
    <button class="btn btn-primary">
        <i class="fas fa-download btn-icon"></i> Exporter
    </button>
</div>

<div class="stats-container">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Total Soumissions</h3>
            <div class="number">5</div>
        </div>
        <div class="stat-icon purple">
            <i class="fas fa-file-alt"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Points attribués</h3>
            <div class="number">230</div>
        </div>
        <div class="stat-icon green">
            <i class="fas fa-star"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>En attente</h3>
            <div class="number">2</div>
        </div>
        <div class="stat-icon orange">
            <i class="fas fa-clock"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-info">
            <h3>Taux d'approbation</h3>
            <div class="number">40%</div>
        </div>
        <div class="stat-icon green">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>
</div>

<div class="search-container">
    <i class="fas fa-search search-icon"></i>
    <input type="text" class="search-input" placeholder="Rechercher une soumission..." data-table="submissionsTable">
</div>

<div class="filter-container" style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
    <select class="dropdown-toggle" style="width: auto;">
        <option value="all">Tous les statuts</option>
        <option value="approved">Approuvé</option>
        <option value="pending">En attente</option>
        <option value="rejected">Rejeté</option>
    </select>
</div>

<div class="card">
    <div class="table-container">
        <table id="submissionsTable">
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
                    <td><span class="badge badge-warning">150</span></td>
                    <td>11/05/2024</td>
                    <td><span class="badge badge-success">Approuvé</span></td>
                    <td>
                        <a href="#" class="action-button" data-action="view" data-id="1">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>Marie Dupont</td>
                    <td>Solo</td>
                    <td>Front-end Performance</td>
                    <td><span class="badge badge-warning">100</span></td>
                    <td>10/05/2024</td>
                    <td><span class="badge badge-warning">En attente</span></td>
                    <td>
                        <a href="#" class="action-button" data-action="view" data-id="2">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="action-button" data-action="approve" data-id="2">
                            <i class="fas fa-check" style="color: var(--secondary);"></i>
                        </a>
                        <a href="#" class="action-button" data-action="reject" data-id="2">
                            <i class="fas fa-times" style="color: var(--danger);"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>Jean Durand</td>
                    <td>Data Science Squad</td>
                    <td>Database Optimization</td>
                    <td><span class="badge badge-warning">120</span></td>
                    <td>09/05/2024</td>
                    <td><span class="badge badge-danger">Rejeté</span></td>
                    <td>
                        <a href="#" class="action-button" data-action="view" data-id="3">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>Sophie Laurent</td>
                    <td>DevOps Masters</td>
                    <td>Cloud Infrastructure</td>
                    <td><span class="badge badge-warning">200</span></td>
                    <td>08/05/2024</td>
                    <td><span class="badge badge-warning">En attente</span></td>
                    <td>
                        <a href="#" class="action-button" data-action="view" data-id="4">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="#" class="action-button" data-action="approve" data-id="4">
                            <i class="fas fa-check" style="color: var(--secondary);"></i>
                        </a>
                        <a href="#" class="action-button" data-action="reject" data-id="4">
                            <i class="fas fa-times" style="color: var(--danger);"></i>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td>Thomas Petit</td>
                    <td>Solo</td>
                    <td>Mobile Responsive Design</td>
                    <td><span class="badge badge-warning">80</span></td>
                    <td>07/05/2024</td>
                    <td><span class="badge badge-success">Approuvé</span></td>
                    <td>
                        <a href="#" class="action-button" data-action="view" data-id="5">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <div class="card-title">Détails des soumissions récentes</div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px;">
        <div class="submission-detail">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3>API Security Challenge</h3>
                <span class="badge badge-success">Approuvé</span>
            </div>
            <p>Soumis par Pierre Martin (Frontend Wizards)</p>
            <div style="display: flex; align-items: center; margin-top: 10px;">
                <i class="fas fa-star" style="color: #f59e0b; margin-right: 5px;"></i>
                <span>150 points</span>
                <span style="margin-left: auto;">11/05/2024</span>
            </div>
        </div>
        
        <div class="submission-detail">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <h3>Front-end Performance</h3>
                <span class="badge badge-warning">En attente</span>
            </div>
            <p>Soumis par Marie Dupont (Solo)</p>
            <div style="display: flex; align-items: center; margin-top: 10px;">
                <i class="fas fa-star" style="color: #f59e0b; margin-right: 5px;"></i>
                <span>100 points</span>
                <span style="margin-left: auto;">10/05/2024</span>
            </div>
        </div>
    </div>
</div>
