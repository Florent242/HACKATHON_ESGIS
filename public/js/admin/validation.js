/**
 * Gestionnaire de Validation et Évaluation des Projets
 * Page dédiée pour l'évaluation avancée des projets soumis
 */

class ProjectValidationManager {
    constructor() {
        this.currentProject = null;
        this.criteria = {
            innovation: { weight: 25, max: 25 },
            technical: { weight: 30, max: 30 },
            functionality: { weight: 25, max: 25 },
            presentation: { weight: 20, max: 20 }
        };
        this.init();
    }

    init() {
        this.initializeEventListeners();
        this.loadValidationStats();
        this.loadProjectsToValidate();
        this.setupRealTimeUpdates();
    }

    initializeEventListeners() {
        // Filtres et recherche
        document.getElementById('statusFilter').addEventListener('change', () => this.applyFilters());
        document.getElementById('hackathonFilter').addEventListener('change', () => this.applyFilters());
        document.getElementById('priorityFilter').addEventListener('change', () => this.applyFilters());
        document.getElementById('searchInput').addEventListener('input', () => this.applyFilters());

        // Actions principales
        document.getElementById('refreshBtn').addEventListener('click', () => this.loadProjectsToValidate());
        document.getElementById('exportBtn').addEventListener('click', () => this.exportEvaluations());

        // Gestion des sliders de score
        document.querySelectorAll('.score-slider').forEach(slider => {
            slider.addEventListener('input', (e) => this.updateCriterionScore(e.target));
        });

        // Onglets des commentaires
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.switchCommentTab(e.target.dataset.tab));
        });

        // Actions de validation
        document.getElementById('validateProjectBtn').addEventListener('click', () => this.validateProject());
        document.getElementById('requestRevisionBtn').addEventListener('click', () => this.requestRevision());
        document.getElementById('rejectProjectBtn').addEventListener('click', () => this.rejectProject());

        // Fermeture des modaux
        document.getElementById('closeEvaluationBtn').addEventListener('click', () => this.closeEvaluationModal());

        // Notifications
        document.querySelector('.notification-close')?.addEventListener('click', () => this.hideNotification());
    }

    /**
     * Chargement des statistiques de validation
     */
    async loadValidationStats() {
        try {
            const response = await fetch('/api/admin/validation-stats');
            const data = await response.json();

            if (data.success) {
                this.updateStatsDisplay(data.stats);
            }
        } catch (error) {
            console.error('Erreur lors du chargement des statistiques:', error);
        }
    }

    updateStatsDisplay(stats) {
        document.getElementById('pendingEvaluations').textContent = stats.pending_evaluations || 0;
        document.getElementById('averageScore').textContent = `${stats.average_score || 0}/100`;
        document.getElementById('validatedProjects').textContent = stats.validated_projects || 0;
        document.getElementById('validationRate').textContent = `${stats.validation_rate || 0}%`;
    }

    /**
     * Chargement des projets à valider
     */
    async loadProjectsToValidate() {
        this.showLoading();
        
        try {
            const response = await apiRequest('/projects');
            console.log(response);
            const data = await response.json();

            if (data.success) {
                this.renderProjectsTable(data.projects);
            } else {
                this.showError('Erreur lors du chargement des projets');
            }
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            this.showError('Erreur de connexion');
        } finally {
            this.hideLoading();
        }
    }

    renderProjectsTable(projects) {
        const tbody = document.getElementById('projectsTable');
        
        if (!projects || projects.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                            <h4>Aucun projet à évaluer</h4>
                            <p>Tous les projets soumis ont été traités</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = projects.map(project => `
            <tr data-project-id="${project.id}">
                <td>
                    <div class="project-info">
                        <strong>${project.title}</strong>
                        ${project.description ? `<br><small class="text-muted">${this.truncateText(project.description, 60)}</small>` : ''}
                    </div>
                </td>
                <td>
                    <div class="team-info">
                        <span class="team-badge">${project.team_name}</span>
                        <br><small>${project.team_members_count} membres</small>
                    </div>
                </td>
                <td>
                    <span class="challenge-badge ${project.challenge_difficulty}">
                        ${project.challenge_title}
                    </span>
                </td>
                <td>
                    <div class="deliverables">
                        ${project.repository_url ? '<i class="fab fa-github text-primary" title="Repository GitHub"></i>' : ''}
                        ${project.demo_url ? '<i class="fas fa-external-link-alt text-success" title="Démo en ligne"></i>' : ''}
                        ${project.zip_path ? '<i class="fas fa-file-archive text-info" title="Archive ZIP"></i>' : ''}
                        ${project.documentation_url ? '<i class="fas fa-book text-warning" title="Documentation"></i>' : ''}
                    </div>
                </td>
                <td>
                    <div class="score-display">
                        ${project.current_score ? `
                            <span class="score-badge ${this.getScoreClass(project.current_score)}">${project.current_score}/100</span>
                        ` : '<span class="text-muted">Non évalué</span>'}
                    </div>
                </td>
                <td>
                    <span class="status-badge ${project.status}">${this.getStatusLabel(project.status)}</span>
                </td>
                <td>
                    <small>${this.formatDate(project.submitted_at)}</small>
                </td>
                <td>
                    <div class="action-buttons">
                        <button onclick="validationManager.openEvaluationModal(${project.id})" 
                                class="btn btn-primary btn-sm" 
                                title="Évaluer le projet">
                            <i class="fas fa-star"></i>
                        </button>
                        <button onclick="validationManager.viewProjectDetails(${project.id})" 
                                class="btn btn-outline-info btn-sm" 
                                title="Voir détails">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    /**
     * Ouverture du modal d'évaluation
     */
    async openEvaluationModal(projectId) {
        try {
            const response = await fetch(`/api/projects/${projectId}/details`);
            const data = await response.json();

            if (data.success) {
                this.currentProject = data.project;
                this.populateEvaluationModal(data.project);
                this.showEvaluationModal();
            } else {
                this.showError('Erreur lors du chargement du projet');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur de chargement');
        }
    }

    populateEvaluationModal(project) {
        // Informations du projet
        document.getElementById('projectTitle').textContent = project.title;
        document.getElementById('teamName').textContent = project.team_name;
        document.getElementById('projectDescription').textContent = project.description || 'Aucune description disponible';

        // Liens vers les livrables
        this.updateProjectLinks(project);

        // Charger les scores existants si disponibles
        if (project.current_evaluation) {
            this.loadExistingEvaluation(project.current_evaluation);
        } else {
            this.resetEvaluationForm();
        }
    }

    updateProjectLinks(project) {
        const repoLink = document.getElementById('repositoryLink');
        const demoLink = document.getElementById('demoLink');
        const downloadLink = document.getElementById('downloadLink');

        if (project.repository_url) {
            repoLink.href = project.repository_url;
            repoLink.style.display = 'inline-block';
        } else {
            repoLink.style.display = 'none';
        }

        if (project.demo_url) {
            demoLink.href = project.demo_url;
            demoLink.style.display = 'inline-block';
        } else {
            demoLink.style.display = 'none';
        }

        if (project.zip_path) {
            downloadLink.href = project.zip_path;
            downloadLink.style.display = 'inline-block';
        } else {
            downloadLink.style.display = 'none';
        }
    }

    loadExistingEvaluation(evaluation) {
        if (evaluation.criteria) {
            const criteria = JSON.parse(evaluation.criteria);
            Object.keys(criteria).forEach(criterion => {
                const slider = document.getElementById(`${criterion}_score`);
                if (slider && criteria[criterion].score) {
                    slider.value = criteria[criterion].score;
                    this.updateCriterionScore(slider);
                }
            });
        }

        // Charger les commentaires existants
        if (evaluation.comments) {
            try {
                const comments = JSON.parse(evaluation.comments);
                document.getElementById('strengthsComment').value = comments.strengths || '';
                document.getElementById('improvementsComment').value = comments.improvements || '';
                document.getElementById('generalComment').value = comments.general || '';
            } catch (e) {
                document.getElementById('generalComment').value = evaluation.comments;
            }
        }
    }

    resetEvaluationForm() {
        // Réinitialiser tous les sliders
        document.querySelectorAll('.score-slider').forEach(slider => {
            slider.value = 0;
            this.updateCriterionScore(slider);
        });

        // Vider les commentaires
        document.getElementById('strengthsComment').value = '';
        document.getElementById('improvementsComment').value = '';
        document.getElementById('generalComment').value = '';
    }

    /**
     * Mise à jour des scores en temps réel
     */
    updateCriterionScore(slider) {
        const criterion = slider.dataset.criterion;
        const value = parseInt(slider.value);
        
        // Mettre à jour l'affichage du score
        const scoreDisplay = slider.parentElement.querySelector('.score-value');
        scoreDisplay.textContent = value;

        // Recalculer le score total
        this.calculateTotalScore();

        // Animation visuelle
        slider.style.background = `linear-gradient(to right, #4CAF50 0%, #4CAF50 ${(value / slider.max) * 100}%, #ddd ${(value / slider.max) * 100}%, #ddd 100%)`;
    }

    calculateTotalScore() {
        let totalScore = 0;
        
        Object.keys(this.criteria).forEach(criterion => {
            const slider = document.getElementById(`${criterion}_score`);
            if (slider) {
                totalScore += parseInt(slider.value);
            }
        });

        // Mettre à jour l'affichage
        document.getElementById('totalScore').textContent = totalScore;
        
        // Mettre à jour la barre de progression
        const scoreFill = document.getElementById('scoreFill');
        const percentage = (totalScore / 100) * 100;
        scoreFill.style.width = `${percentage}%`;

        // Mettre à jour le grade
        this.updateGradeDisplay(totalScore);
    }

    updateGradeDisplay(score) {
        const gradeBadge = document.getElementById('gradeBadge');
        const gradeText = document.getElementById('gradeText');

        let grade, text, className;

        if (score >= 90) {
            grade = 'A+';
            text = 'Excellent';
            className = 'grade-excellent';
        } else if (score >= 80) {
            grade = 'A';
            text = 'Très bien';
            className = 'grade-very-good';
        } else if (score >= 70) {
            grade = 'B+';
            text = 'Bien';
            className = 'grade-good';
        } else if (score >= 60) {
            grade = 'B';
            text = 'Assez bien';
            className = 'grade-fair';
        } else if (score >= 50) {
            grade = 'C';
            text = 'Passable';
            className = 'grade-pass';
        } else {
            grade = 'D';
            text = 'Insuffisant';
            className = 'grade-fail';
        }

        gradeBadge.textContent = grade;
        gradeText.textContent = text;
        gradeBadge.className = `grade-badge ${className}`;
    }

    /**
     * Gestion des onglets de commentaires
     */
    switchCommentTab(tabName) {
        // Désactiver tous les onglets
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.remove('active'));

        // Activer l'onglet sélectionné
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        document.getElementById(`${tabName}-panel`).classList.add('active');
    }

    /**
     * Actions de validation
     */
    async validateProject() {
        const evaluationData = this.collectEvaluationData();
        evaluationData.action = 'validate';

        const confirmation = await this.showConfirmation(
            'Valider le projet',
            `Êtes-vous sûr de vouloir valider ce projet avec un score de ${evaluationData.totalScore}/100 ?`,
            `Le projet sera marqué comme validé et l'équipe sera notifiée.`
        );

        if (confirmation) {
            await this.submitEvaluation(evaluationData);
        }
    }

    async requestRevision() {
        const evaluationData = this.collectEvaluationData();
        evaluationData.action = 'request_revision';

        const confirmation = await this.showConfirmation(
            'Demander une révision',
            'Demander à l\'équipe de réviser son projet ?',
            'L\'équipe recevra vos commentaires et pourra soumettre une nouvelle version.'
        );

        if (confirmation) {
            await this.submitEvaluation(evaluationData);
        }
    }

    async rejectProject() {
        const evaluationData = this.collectEvaluationData();
        evaluationData.action = 'reject';

        const confirmation = await this.showConfirmation(
            'Rejeter le projet',
            'Êtes-vous sûr de vouloir rejeter définitivement ce projet ?',
            'Cette action est irréversible. L\'équipe sera notifiée du rejet.'
        );

        if (confirmation) {
            await this.submitEvaluation(evaluationData);
        }
    }

    collectEvaluationData() {
        const criteriaScores = {};
        let totalScore = 0;

        Object.keys(this.criteria).forEach(criterion => {
            const slider = document.getElementById(`${criterion}_score`);
            const score = parseInt(slider.value);
            criteriaScores[criterion] = {
                score: score,
                weight: this.criteria[criterion].weight
            };
            totalScore += score;
        });

        const comments = {
            strengths: document.getElementById('strengthsComment').value,
            improvements: document.getElementById('improvementsComment').value,
            general: document.getElementById('generalComment').value
        };

        return {
            project_id: this.currentProject.id,
            criteria_scores: criteriaScores,
            totalScore: totalScore,
            comments: comments,
            evaluated_by: this.getCurrentUserId(),
            evaluated_at: new Date().toISOString()
        };
    }

    async submitEvaluation(evaluationData) {
        try {
            const response = await fetch('/api/admin/evaluate-project', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify(evaluationData)
            });

            const result = await response.json();

            if (result.success) {
                this.showSuccess(`Projet ${evaluationData.action === 'validate' ? 'validé' : evaluationData.action === 'reject' ? 'rejeté' : 'envoyé en révision'} avec succès`);
                this.closeEvaluationModal();
                this.loadProjectsToValidate();
                this.loadValidationStats();
            } else {
                this.showError(result.message || 'Erreur lors de l\'évaluation');
            }
        } catch (error) {
            console.error('Erreur:', error);
            this.showError('Erreur lors de l\'envoi de l\'évaluation');
        }
    }

    /**
     * Méthodes utilitaires
     */
    showEvaluationModal() {
        document.getElementById('evaluationModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    closeEvaluationModal() {
        document.getElementById('evaluationModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        this.currentProject = null;
    }

    async showConfirmation(title, message, details) {
        return new Promise((resolve) => {
            document.getElementById('confirmationTitle').textContent = title;
            document.getElementById('confirmationMessage').textContent = message;
            document.getElementById('confirmationDetails').textContent = details;

            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'flex';

            const confirmBtn = document.getElementById('confirmActionBtn');
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

            newConfirmBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                resolve(true);
            });
        });
    }

    closeConfirmationModal() {
        document.getElementById('confirmationModal').style.display = 'none';
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type = 'info') {
        const notification = document.getElementById('notification');
        const messageEl = document.getElementById('notificationMessage');
        
        messageEl.textContent = message;
        notification.className = `notification ${type}`;
        notification.style.display = 'block';

        setTimeout(() => this.hideNotification(), 5000);
    }

    hideNotification() {
        document.getElementById('notification').style.display = 'none';
    }

    showLoading() {
        document.getElementById('global-loading-spinner').style.display = 'flex';
    }

    hideLoading() {
        document.getElementById('global-loading-spinner').style.display = 'none';
    }

    // Méthodes utilitaires pour l'affichage
    truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    getScoreClass(score) {
        if (score >= 80) return 'score-excellent';
        if (score >= 60) return 'score-good';
        if (score >= 40) return 'score-average';
        return 'score-poor';
    }

    getStatusLabel(status) {
        const labels = {
            'submitted': 'Soumis',
            'in_evaluation': 'En évaluation',
            'validated': 'Validé',
            'rejected': 'Rejeté',
            'needs_revision': 'Révision demandée'
        };
        return labels[status] || status;
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    getCurrentUserId() {
        // À implémenter selon votre système d'authentification
        return 1; // Placeholder
    }

    getAuthToken() {
        // À implémenter selon votre système d'authentification
        return localStorage.getItem('auth_token') || '';
    }

    applyFilters() {
        // À implémenter - filtrage des projets
        console.log('Applying filters...');
    }

    async exportEvaluations() {
        // À implémenter - export des évaluations
        console.log('Exporting evaluations...');
    }

    setupRealTimeUpdates() {
        // À implémenter - mises à jour en temps réel
        setInterval(() => {
            this.loadValidationStats();
        }, 30000); // Mise à jour toutes les 30 secondes
    }

    viewProjectDetails(projectId) {
        // Ouvrir les détails du projet dans un nouvel onglet ou modal
        window.open(`/admin/project-details/${projectId}`, '_blank');
    }
}

// Initialisation lors du chargement de la page
let validationManager;
document.addEventListener('DOMContentLoaded', () => {
    validationManager = new ProjectValidationManager();
});

// Fonctions globales pour les événements inline
function closeEvaluationModal() {
    if (validationManager) {
        validationManager.closeEvaluationModal();
    }
}

function closeConfirmationModal() {
    if (validationManager) {
        validationManager.closeConfirmationModal();
    }
}