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
        document.getElementById('closeModalBtn').addEventListener('click', () => this.closeEvaluationModal());
        
        // Fermer modal en cliquant en dehors
        document.getElementById('evaluationModal').addEventListener('click', (e) => {
            if (e.target.id === 'evaluationModal') {
                this.closeEvaluationModal();
            }
        });

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
            console.log('📡 Response received:', response);
           
            // Vérifier si la réponse est un tableau (données directes) ou un objet avec success
            let projects = [];
            
            if (Array.isArray(response)) {
                // Réponse directe (tableau de projets)
                projects = response;
                console.log('📊 Projects (array format):', projects);
            } else if (response.success && response.data) {
                // Format standard avec success
                projects = response.data;
                console.log('📊 Projects (object format):', projects);
            } else if (response.success && Array.isArray(response.projects)) {
                // Alternative : response.projects
                projects = response.projects;
                console.log('📊 Projects (projects field):', projects);
            } else {
                throw new Error(response.error || 'Format de réponse invalide');
            }
            
            this.renderProjectsTable(projects);
            this.updateStatsDisplay({
                total: projects.length,
                pending: projects.filter(p => p.status === 'submitted').length,
                validated: projects.filter(p => p.status === 'validated').length,
                rejected: projects.filter(p => p.status === 'rejected').length
            });
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            this.showError('Erreur de connexion');
        } finally {
            this.hideLoading();
        }
    }

    renderProjectsTable(projects) {
        const tbody = document.getElementById('projectsTable');
        
        // Traiter les critères d'évaluation (convertir string en objet si nécessaire)
        projects = projects.map(project => {
            if (typeof project.evaluation_criteria === 'string') {
                try {
                    project.evaluation_criteria = JSON.parse(project.evaluation_criteria);
                } catch (e) {
                    console.warn('Erreur parsing evaluation_criteria pour projet', project.id, e);
                    project.evaluation_criteria = [];
                }
            }
            return project;
        });
        
        console.log('📋 Projets traités pour affichage:', projects);
        
        // Filtrer les projets évaluables (completed = soumis, ongoing = en cours mais évaluable pour test)
        const evaluableProjects = projects.filter(p => 
            ['ongoing', 'completed', 'submitted'].includes(p.status)
        );
        
        console.log('📊 Projets évaluables:', evaluableProjects);
        
        if (!evaluableProjects || evaluableProjects.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                            <h4>Aucun projet à évaluer</h4>
                            <p>Aucun projet disponible pour l'évaluation</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = evaluableProjects.map((project, index) => `
            <tr data-project-id="${project.id}" class="group hover:bg-gray-50 transition-all duration-200 ${this.getProjectRowClass(project.status)}">
                <td class="px-4 py-4 border-b border-gray-200">
                    <div class="max-w-sm">
                        <div class="flex items-center justify-between mb-2">
                            <h6 class="flex items-center text-sm font-semibold text-gray-900 mb-0">
                                <i class="fas fa-folder-open text-blue-500 mr-2"></i>
                                ${project.name}
                            </h6>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-purple-500 to-blue-600 text-white">
                                #${project.id}
                            </span>
                        </div>
                        ${project.description ? `
                            <p class="text-xs text-gray-600 leading-relaxed mb-0">
                                ${this.truncateText(project.description, 80)}
                            </p>
                        ` : ''}
                    </div>
                </td>
                <td class="px-4 py-4 border-b border-gray-200 text-center">
                    <div class="space-y-2">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium text-white ${this.getTailwindTeamBadgeColor(index)}">
                                <i class="fas fa-users mr-1"></i>
                                ${project.team_name}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-hashtag mr-1"></i>
                            Équipe ${project.team_id}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 border-b border-gray-200">
                    <div class="text-center space-y-2">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
                                <i class="fas fa-trophy mr-1"></i>
                                ${this.truncateText(project.challenge_title, 30)}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>
                            ${project.hackathon_name}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 border-b border-gray-200">
                    <div class="flex flex-wrap justify-center gap-1">
                        ${project.repository_url && project.repository_url.trim() !== '' ? `
                            <div class="flex items-center px-2 py-1 bg-gray-900 text-white rounded-md text-xs font-medium cursor-pointer hover:bg-gray-800 transition-colors" onclick="window.open('${project.repository_url.replace(/'/g, "\\'")}', '_blank')" title="Cliquer pour voir le code source">
                                <i class="fab fa-github mr-1"></i>
                                <span>GitHub</span>
                            </div>
                        ` : ''}
                        ${project.demo_url && project.demo_url.trim() !== '' ? `
                            <div class="flex items-center px-2 py-1 bg-green-600 text-white rounded-md text-xs font-medium cursor-pointer hover:bg-green-700 transition-colors" onclick="window.open('${project.demo_url.replace(/'/g, "\\'")}', '_blank')" title="Cliquer pour voir la démo">
                                <i class="fas fa-external-link-alt mr-1"></i>
                                <span>Démo</span>
                            </div>
                        ` : ''}
                        ${project.zip_path ? `
                            <div class="flex items-center px-2 py-1 bg-blue-600 text-white rounded-md text-xs font-medium cursor-pointer hover:bg-blue-700 transition-colors" onclick="validationManager.downloadProject(${project.id})" title="Cliquer pour télécharger">
                                <i class="fas fa-file-archive mr-1"></i>
                                <span>ZIP</span>
                            </div>
                        ` : ''}
                        ${project.documentation_url ? `
                            <div class="flex items-center px-2 py-1 bg-amber-600 text-white rounded-md text-xs font-medium">
                                <i class="fas fa-book mr-1"></i>
                                <span>Docs</span>
                            </div>
                        ` : ''}
                        ${!project.repository_url && !project.demo_url && !project.zip_path && !project.documentation_url ? `
                            <div class="flex items-center px-2 py-1 bg-gray-200 text-gray-600 rounded-md text-xs">
                                <i class="fas fa-minus-circle mr-1"></i>
                                <span>Aucun</span>
                            </div>
                        ` : ''}
                    </div>
                </td>
                <td class="px-4 py-4 border-b border-gray-200">
                    <div class="flex justify-center">
                        ${project.score ? `
                            <div class="w-16 h-16 rounded-full border-4 ${this.getTailwindScoreColor(project.score)} bg-white flex flex-col items-center justify-center shadow-sm">
                                <span class="text-lg font-bold ${this.getTailwindScoreTextColor(project.score)}">${project.score}</span>
                                <span class="text-xs opacity-70 ${this.getTailwindScoreTextColor(project.score)}">/100</span>
                            </div>
                        ` : `
                            <div class="flex flex-col items-center text-amber-600">
                                <i class="fas fa-clock text-xl mb-1"></i>
                                <span class="text-xs text-gray-500">En attente</span>
                            </div>
                        `}
                    </div>
                </td>
                <td class="px-4 py-4 border-b border-gray-200 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${this.getTailwindStatusColor(project.status)}">
                        ${this.getStatusIcon(project.status)}
                        <span class="ml-1 uppercase tracking-wider">${this.getStatusLabel(project.status)}</span>
                    </span>
                </td>
                <td class="px-4 py-4 border-b border-gray-200 text-center">
                    <div class="space-y-1">
                        <div class="text-sm font-medium text-gray-700">
                            ${this.formatDate(project.created_at)}
                        </div>
                        <div class="text-xs text-gray-500">
                            ${this.getTimeAgo(project.created_at)}
                        </div>
                    </div>
                </td>
                <td class="px-4 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick="validationManager.openEvaluationModal(${project.id})" 
                                class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white transition-colors duration-200 ${project.score ? 'bg-green-600 hover:bg-green-700' : 'bg-amber-600 hover:bg-amber-700'}" 
                                title="${project.score ? 'Modifier l\'évaluation' : 'Évaluer le projet'}">
                            <i class="fas ${project.score ? 'fa-edit' : 'fa-star'} mr-1"></i>
                            <span class="hidden sm:inline">${project.score ? 'Modifier' : 'Évaluer'}</span>
                        </button>
                        <button onclick="validationManager.viewProjectDetails(${project.id})" 
                                class="inline-flex items-center p-2 border border-gray-300 rounded-md text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-700 transition-colors duration-200" 
                                title="Voir les détails du projet">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="relative inline-block">
                            <button class="inline-flex items-center p-2 border border-gray-300 rounded-md text-gray-600 bg-white hover:bg-gray-50 transition-colors duration-200" 
                                    onclick="toggleDropdown(${project.id})">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div id="dropdown-${project.id}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                <div class="py-1">
                                    <a href="#" onclick="validationManager.downloadProject(${project.id})" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-download mr-2"></i>Télécharger
                                    </a>
                                    <a href="#" onclick="validationManager.viewHistory(${project.id})" 
                                       class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-history mr-2"></i>Historique
                                    </a>
                                </div>
                            </div>
                        </div>
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
            console.log("start");
            console.log(projectId)
            const response = await apiRequest(`/projects/${projectId}`);
            console.log("response", response);

            if (response.success) {
                console.log("succes");
                this.currentProject = response.data;
                this.populateEvaluationModal(response.data);
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
        document.getElementById('projectTitle').textContent = project.challenge_title;
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
        const noLinksMessage = document.getElementById('noLinksMessage');

        let hasLinks = false;

        if (project.repository_url && project.repository_url.trim() !== '') {
            repoLink.href = project.repository_url;
            repoLink.target = '_blank';
            repoLink.style.display = 'inline-flex';
            hasLinks = true;
        } else {
            repoLink.style.display = 'none';
        }

        if (project.demo_url && project.demo_url.trim() !== '') {
            demoLink.href = project.demo_url;
            demoLink.target = '_blank';
            demoLink.style.display = 'inline-flex';
            hasLinks = true;
        } else {
            demoLink.style.display = 'none';
        }

        if (project.zip_path && project.zip_path.trim() !== '') {
            // Utiliser l'endpoint API sécurisé pour télécharger le fichier
            downloadLink.href = '#';
            downloadLink.onclick = (e) => {
                e.preventDefault();
                this.downloadProjectFile(project.id, project.file_name || 'project.zip');
            };
            downloadLink.style.display = 'inline-flex';
            
            // Mettre à jour le texte du lien avec le nom du fichier
            const linkTextSpan = downloadLink.querySelector('span');
            if (linkTextSpan && project.file_name) {
                linkTextSpan.textContent = `Télécharger ${project.file_name}`;
            } else if (linkTextSpan) {
                linkTextSpan.textContent = 'Télécharger ZIP';
            }
            hasLinks = true;
        } else {
            downloadLink.style.display = 'none';
        }

        // Afficher le message "Aucun lien disponible" si aucun lien n'est disponible
        if (noLinksMessage) {
            if (!hasLinks) {
                noLinksMessage.style.display = 'inline-flex';
            } else {
                noLinksMessage.style.display = 'none';
            }
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
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-green-600', 'border-green-600', 'bg-green-50', 'text-orange-600', 'border-orange-600', 'bg-orange-50', 'text-blue-600', 'border-blue-600', 'bg-blue-50');
            btn.classList.add('text-gray-600');
            btn.classList.remove('border-b-2');
        });
        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('block');
            panel.classList.add('hidden');
        });

        // Activer l'onglet sélectionné
        const activeTab = document.querySelector(`[data-tab="${tabName}"]`);
        const activePanel = document.getElementById(`${tabName}-panel`);
        
        if (tabName === 'strengths') {
            activeTab.classList.add('text-green-600', 'border-b-2', 'border-green-600', 'bg-green-50');
        } else if (tabName === 'improvements') {
            activeTab.classList.add('text-orange-600', 'border-b-2', 'border-orange-600', 'bg-orange-50');
        } else if (tabName === 'general') {
            activeTab.classList.add('text-blue-600', 'border-b-2', 'border-blue-600', 'bg-blue-50');
        }
        
        activeTab.classList.remove('text-gray-600');
        activePanel.classList.remove('hidden');
        activePanel.classList.add('block');
    }

    /**
     * Actions de validation
     */
    async validateProject() {
        const evaluationData = await this.collectEvaluationData();
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
        const evaluationData = await this.collectEvaluationData();
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
        const evaluationData = await this.collectEvaluationData();
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

    async collectEvaluationData() {
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

        // Structure conforme au workflow
        return {
            project_id: this.currentProject.id,
            judge_id: await getUserId(),                   
            score: totalScore,                            
            criteria: JSON.stringify(criteriaScores),      
            comments: JSON.stringify(comments),            
            evaluated_at: new Date().toISOString(),

        };
    }

    async submitEvaluation(evaluationData) {
        try {
            let user_Id=getUserId();
            console.log("user_Id",user_Id);
            const response = await apiRequest('/evaluations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(evaluationData)
            });
            console.log('Response:', response);
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
        
        // Réinitialiser les classes
        notification.className = 'fixed top-4 right-4 max-w-sm rounded-lg shadow-lg border';
        
        // Ajouter les classes selon le type
        if (type === 'success') {
            notification.classList.add('bg-green-50', 'border-green-200');
            messageEl.classList.add('text-green-800');
        } else if (type === 'error') {
            notification.classList.add('bg-red-50', 'border-red-200');
            messageEl.classList.add('text-red-800');
        } else if (type === 'warning') {
            notification.classList.add('bg-yellow-50', 'border-yellow-200');
            messageEl.classList.add('text-yellow-800');
        } else {
            notification.classList.add('bg-blue-50', 'border-blue-200');
            messageEl.classList.add('text-blue-800');
        }
        
        notification.style.display = 'block';
        notification.style.zIndex = '10001';

        setTimeout(() => this.hideNotification(), 5000);
    }

    hideNotification() {
        const notification = document.getElementById('notification');
        const messageEl = document.getElementById('notificationMessage');
        
        notification.style.display = 'none';
        
        // Réinitialiser les classes de couleur
        messageEl.className = 'text-sm font-medium text-gray-800';
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
            'needs_revision': 'Révision demandée',
            'ongoing': 'En cours',
            'completed': 'Terminé'
        };
        return labels[status] || status;
    }

    getStatusIcon(status) {
        const icons = {
            'submitted': 'fas fa-paper-plane',
            'in_evaluation': 'fas fa-hourglass-half',
            'validated': 'fas fa-check-circle',
            'rejected': 'fas fa-times-circle',
            'needs_revision': 'fas fa-exclamation-triangle',
            'ongoing': 'fas fa-play-circle',
            'completed': 'fas fa-flag-checkered'
        };
        return `<i class="${icons[status] || 'fas fa-question-circle'}"></i>`;
    }

    getProjectRowClass(status) {
        // Fonction conservée pour compatibilité, mais utilise maintenant Tailwind
        return this.getProjectRowTailwind(status);
    }

    getProjectRowTailwind(status) {
        const classes = {
            'submitted': 'border-l-blue-500 bg-blue-50/30',
            'in_evaluation': 'border-l-yellow-500 bg-yellow-50/30',
            'validated': 'border-l-emerald-500 bg-emerald-50/30', 
            'rejected': 'border-l-red-500 bg-red-50/30',
            'needs_revision': 'border-l-orange-500 bg-orange-50/30',
            'ongoing': 'border-l-amber-500 bg-amber-50/30',
            'completed': 'border-l-green-500 bg-green-50/30'
        };
        return classes[status] || 'border-l-gray-300 bg-gray-50/30';
    }

    getTeamBadgeColor(index) {
        const colors = ['primary', 'success', 'info', 'warning', 'secondary', 'dark'];
        return colors[index % colors.length];
    }

    getTailwindTeamBadgeColor(index) {
        const colors = [
            'bg-blue-600',      // primary
            'bg-emerald-600',   // success  
            'bg-cyan-600',      // info
            'bg-amber-600',     // warning
            'bg-gray-600',      // secondary
            'bg-gray-800'       // dark
        ];
        return colors[index % colors.length];
    }

    getTailwindStatusColor(status) {
        const colors = {
            'submitted': 'bg-blue-100 text-blue-800 border border-blue-200',
            'in_evaluation': 'bg-yellow-100 text-yellow-800 border border-yellow-200',
            'validated': 'bg-emerald-100 text-emerald-800 border border-emerald-200',
            'rejected': 'bg-red-100 text-red-800 border border-red-200',
            'needs_revision': 'bg-orange-100 text-orange-800 border border-orange-200',
            'ongoing': 'bg-amber-100 text-amber-800 border border-amber-200',
            'completed': 'bg-green-100 text-green-800 border border-green-200'
        };
        return colors[status] || 'bg-gray-100 text-gray-800 border border-gray-200';
    }

    getTailwindScoreColor(score) {
        if (score >= 80) return 'border-emerald-500';
        if (score >= 60) return 'border-blue-500';
        if (score >= 40) return 'border-amber-500';
        return 'border-red-500';
    }

    getTailwindScoreTextColor(score) {
        if (score >= 80) return 'text-emerald-600';
        if (score >= 60) return 'text-blue-600';
        if (score >= 40) return 'text-amber-600';
        return 'text-red-600';
    }

    getScoreClass(score) {
        if (score >= 80) return 'excellent';
        if (score >= 60) return 'good';
        if (score >= 40) return 'average';
        return 'poor';
    }

    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }

    getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return "Aujourd'hui";
        if (diffDays === 1) return "Hier";
        if (diffDays < 7) return `Il y a ${diffDays} jours`;
        if (diffDays < 30) return `Il y a ${Math.floor(diffDays / 7)} semaines`;
        return `Il y a ${Math.floor(diffDays / 30)} mois`;
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

    // Nouvelles méthodes pour les fonctionnalités étendues
    downloadProject(projectId) {
        console.log('🔄 downloadProject appelé avec ID:', projectId);
        
        // Fermer le dropdown si ouvert
        const dropdown = document.getElementById(`dropdown-${projectId}`);
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
        
        if (this.currentProject && this.currentProject.id === projectId) {
            console.log('📂 Projet courant disponible:', this.currentProject.file_name);
            this.downloadProjectFile(projectId, this.currentProject.file_name || 'project.zip');
        } else {
            console.log('📂 Récupération des infos du projet...');
            // Récupérer les infos du projet si pas dans le modal
            this.downloadProjectFile(projectId);
        }
    }

    async downloadProjectFile(projectId, fileName = null) {
        console.log('💾 downloadProjectFile appelé:', { projectId, fileName });
        
        try {
            const token = this.getAuthToken();
            console.log('🔑 Token récupéré:', token ? 'Présent' : 'Absent');
            
            if (!token) {
                this.showError('Token d\'authentification manquant');
                return;
            }

            // Construire l'URL de téléchargement
            const downloadUrl = `/api/projects/${projectId}/download`;
            console.log('🌐 URL de téléchargement:', downloadUrl);
            
            // Créer une requête pour télécharger le fichier
            const response = await fetch(downloadUrl, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('📡 Réponse reçue:', response.status, response.statusText);

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Erreur lors du téléchargement');
            }

            // Récupérer le nom du fichier depuis les headers ou utiliser celui fourni
            const contentDisposition = response.headers.get('Content-Disposition');
            let downloadFileName = fileName;
            
            if (contentDisposition) {
                const fileNameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (fileNameMatch && fileNameMatch[1]) {
                    downloadFileName = fileNameMatch[1].replace(/['"]/g, '');
                }
            }

            if (!downloadFileName) {
                downloadFileName = `project_${projectId}.zip`;
            }

            // Créer un blob et déclencher le téléchargement
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = url;
            link.download = downloadFileName;
            document.body.appendChild(link);
            link.click();
            
            // Nettoyer
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            this.showSuccess(`Fichier "${downloadFileName}" téléchargé avec succès`);
            
        } catch (error) {
            console.error('Erreur lors du téléchargement:', error);
            this.showError(`Erreur lors du téléchargement: ${error.message}`);
        }
    }

    viewHistory(projectId) {
        console.log('Historique du projet:', projectId);
        // À implémenter
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

// Fonction globale pour gérer les dropdowns
function toggleDropdown(projectId) {
    console.log('🔽 toggleDropdown appelé pour projet:', projectId);
    
    // Fermer tous les autres dropdowns
    document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
        if (dropdown.id !== `dropdown-${projectId}`) {
            dropdown.classList.add('hidden');
        }
    });
    
    // Toggle le dropdown actuel
    const dropdown = document.getElementById(`dropdown-${projectId}`);
    if (dropdown) {
        dropdown.classList.toggle('hidden');
        console.log('🔽 Dropdown toggleé:', !dropdown.classList.contains('hidden') ? 'Ouvert' : 'Fermé');
    } else {
        console.error('❌ Dropdown non trouvé:', `dropdown-${projectId}`);
    }
}

// Fermer les dropdowns en cliquant ailleurs
document.addEventListener('click', (e) => {
    if (!e.target.closest('.relative')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
            dropdown.classList.add('hidden');
        });
    }
});

// Fonction de debug pour tester le téléchargement
window.debugDownload = function(projectId) {
    console.log('🔧 Debug téléchargement pour projet:', projectId);
    if (validationManager) {
        validationManager.downloadProject(projectId);
    } else {
        console.error('❌ validationManager non initialisé');
    }
};

// Fonction pour tester manuellement l'API
window.testAPI = async function(projectId = 12) {
    console.log('🧪 Test API pour projet:', projectId);
    
    const token = localStorage.getItem('auth_token');
    console.log('🔑 Token:', token ? 'Présent' : 'Absent');
    
    if (!token) {
        console.error('❌ Aucun token trouvé dans localStorage');
        return;
    }
    
    const url = `/api/projects/${projectId}/download`;
    console.log('🌐 URL testée:', url);
    
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('📡 Réponse:', response.status, response.statusText);
        console.log('📋 Headers de réponse:', [...response.headers.entries()]);
        
        if (response.ok) {
            console.log('✅ L\'API répond correctement');
            const contentType = response.headers.get('Content-Type');
            if (contentType && contentType.includes('application/zip')) {
                console.log('📦 Contenu ZIP détecté');
            } else {
                const text = await response.text();
                console.log('📄 Contenu reçu:', text.substring(0, 200));
            }
        } else {
            const error = await response.text();
            console.error('❌ Erreur API:', error);
        }
    } catch (error) {
        console.error('💥 Erreur réseau:', error);
    }
};