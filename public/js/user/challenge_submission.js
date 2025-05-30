// Challenge Submission System
class ChallengeSubmission {
    constructor() {
        this.challengeId = this.getChallengeIdFromUrl();
        this.submissionType = 'github'; // Default to github
        this.challengeData = null;
        this.userRegistered = false;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadChallengeData();
        this.setupDragAndDrop();
        this.addAlertStyles();
    }

    getChallengeIdFromUrl() {
        const path = window.location.pathname;

        // TODO: ajouter la gestion des urls avec le format CHALL-[A-Za-z0-9]{8,}
        // const matches = path.match(/challenge_submission\/(CHALL-[A-Za-z0-9]{8,})$/);

        const matches = path.match(/challenge_submission\/(\d+)$/);
        if (!matches) {
            console.error('No challenge ID found in URL');
            return null;
        }
        return matches[1];
    }

    async loadChallengeData() {
        try {
            this.showLoading();

            // Simulate API call - replace with actual endpoint
            const response = await fetch(`/HACKATHON_ESGIS/public/api/challenges/${this.challengeId}`);
            if (!response.ok) {
                throw new Error('Failed to fetch challenge data');
            }
            const data = await response.json();

            this.challengeData = data.data;

            if (this.challengeData) {
                this.populateChallengeInfo();
                this.handleUserRegistrationStatus();
            }
            this.userRegistered = data.data.userRegistered;

            // Deja gerer dans handleUserRegistrationStatus mais a ajouter si besoin
            // if (!this.userRegistered) {
            //     this.showAlert('Vous devez vous inscrire à ce défi avant de pouvoir soumettre une solution', 'warning');
            // }

        } catch (error) {
            console.error('Error loading challenge data:', error);
            this.showAlert('Erreur lors du chargement des données du défi', 'error');
        } finally {
            this.hideLoading();
        }
    }

    populateChallengeInfo() {
        if (!this.challengeData) {
            this.showAlert('Défi non trouvé', 'error');
            document.querySelector('.challenge-info').classList.add('blur-sm');
            return;
        }

        const data = this.challengeData;

        console.log(data);
        // Mettre à jour les informations de base
        document.getElementById('challengeTitle').textContent = data.title;
        document.getElementById('challengeDescription').textContent = data.description;

        // Formater la date limite
        const deadlineDate = new Date(data.end_date);
        const options = { day: 'numeric', month: 'long', year: 'numeric' };
        document.getElementById('challengeDeadline').textContent = deadlineDate ? deadlineDate.toLocaleDateString('fr-FR', options) : 'Date limite non disponible';

        // Mettre à jour les technologies
        document.getElementById('challengeTechnologies').textContent = Array.isArray(data.technologies)
            ? data.technologies.join(', ')
            : data.technologies ?? 'Technologies non disponibles';

        // Mettre à jour les ressources si disponibles
        if (data.resources && data.resources.length > 0) {
            this.updateResources(data.resources);
        }

        // Ajouter des classes visuelles basées sur le statut et la difficulté
        const challengeInfo = document.querySelector('.challenge-info');
        if (data.status && data.status === 'closed') {
            challengeInfo.classList.add('opacity-50');
            this.showAlert('Ce défi est terminé', 'warning');
        }
    }

    updateResources(resources) {
        const resourcesContainer = document.querySelector('#challengeResources');
        resourcesContainer.innerHTML = resources.map(resource => `
            <a href="${resource.url}" target="_blank" class="flex items-center justify-between text-blue-400 hover:text-blue-300 text-sm transition-colors">
                <div class="flex items-center space-x-2 flex-row">
                    <i data-lucide="${resource.type === 'doc' ? 'file-text' : 'code-xml'}" class="w-3 h-3 mr-2"></i>
                    ${resource.name}
                </div>
                <i data-lucide="external-link" class="w-3 h-3 ml-2"></i>
            </a>
        `).join('');

        // Réinitialiser les icônes Lucide
        lucide.createIcons();
    }

    handleUserRegistrationStatus() {
        const formContainer = document.getElementById('formContainer');

        if (!this.userRegistered) {
            // Show registration required message
            this.showAlert(
                'Vous devez vous inscrire à ce défi avant de pouvoir soumettre une solution.',
                'warning',
                5000
            );

            formContainer.parentElement.querySelectorAll('button').forEach(button => {
                button.disabled = true;
            });
            // Disable form
            formContainer.style.opacity = '0.5';
            formContainer.style.pointerEvents = 'none';

            // Add blur effect
            formContainer.classList.add('blur-sm');

        } else {
            // Enable form with animation
            formContainer.classList.add('slide-in-up');

            formContainer.parentElement.querySelectorAll('button').forEach(button => {
                button.disabled = false;
            });
        }
    }

    setupEventListeners() {
        // Submission type buttons
        document.getElementById('githubBtn').addEventListener('click', () => {
            this.switchSubmissionType('github');
        });

        document.getElementById('zipBtn').addEventListener('click', () => {
            this.switchSubmissionType('zip');
        });

        // File input change
        document.getElementById('zipFile').addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files[0]);
        });

        // Form submission
        document.getElementById('githubSubmissionForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmission();
        });

        document.getElementById('zipSubmissionForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmission();
        });

        // Real-time validation
        this.setupRealTimeValidation();

        // Auto-save draft
        this.setupAutoSave();
    }

    switchSubmissionType(type) {
        this.submissionType = type;

        const githubBtn = document.getElementById('githubBtn');
        const zipBtn = document.getElementById('zipBtn');
        const githubContent = document.getElementById('githubSubmissionForm');
        const zipContent = document.getElementById('zipSubmissionForm');

        // Reset button styles with animation
        githubBtn.className = 'flex-1 flex items-center justify-center px-4 py-3 rounded-lg transition-all duration-300 transform hover:scale-105';
        zipBtn.className = 'flex-1 flex items-center justify-center px-4 py-3 rounded-lg transition-all duration-300 transform hover:scale-105';

        if (type.toLowerCase() === 'github') {
            githubBtn.className += ' bg-blue-600 text-white shadow-lg shadow-blue-500/25';
            zipBtn.className += ' bg-gray-700 text-gray-300 hover:bg-gray-600';

            // Show/hide content with smooth transition
            this.slideOut(zipContent, () => {
                zipContent.classList.add('hidden');
                githubContent.classList.remove('hidden');
                this.slideIn(githubContent);
            });

        } else if (type.toLowerCase() === 'zip') {
            zipBtn.className += ' bg-blue-600 text-white shadow-lg shadow-blue-500/25';
            githubBtn.className += ' bg-gray-700 text-gray-300 hover:bg-gray-600';

            this.slideOut(githubContent, () => {
                githubContent.classList.add('hidden');
                zipContent.classList.remove('hidden');
                this.slideIn(zipContent);
            });

        }
    }

    slideOut(element, callback) {
        element.style.transform = 'translateX(-20px)';
        element.style.opacity = '0';
        setTimeout(callback, 200);
    }

    slideIn(element) {
        setTimeout(() => {
            element.style.transform = 'translateX(0)';
            element.style.opacity = '1';
        }, 50);
    }

    addRippleEffect(button) {
        const ripple = document.createElement('span');
        ripple.className = 'absolute inset-0 rounded-lg bg-white opacity-20 scale-0 animate-ping transition-all duration-300 ease-in-out z-10 pointer-events-none cursor-pointer hover:scale-105 ripple';
        button.style.position = 'relative';
        button.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    }

    setupDragAndDrop() {
        const dropZone = document.querySelector('.border-dashed');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('border-blue-500', 'bg-blue-500/10', 'scale-105');
                dropZone.classList.remove('border-gray-600');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('border-blue-500', 'bg-blue-500/10', 'scale-105');
                dropZone.classList.add('border-gray-600');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.handleFileSelect(files[0]);
            }
        }, false);
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    handleFileSelect(file) {
        if (!file) return;

        // Validate file type and size
        if (!file.name.endsWith('.zip')) {
            this.showAlert('Veuillez sélectionner un fichier ZIP valide.', 'error');
            return;
        }

        if (file.size > 50 * 1024 * 1024) { // 50MB
            this.showAlert('Le fichier est trop volumineux. La taille maximale est de 50MB.', 'error');
            return;
        }

        // Assign the file to the input element
        const fileInput = document.getElementById('zipFile');
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Update UI to show selected file
        document.querySelector('#dropZone').classList.add('hidden');
        const dropZone = document.querySelector('.border-dashed');
        dropZone.classList.remove('hidden');
        const div = document.createElement('div');
        div.className = 'inputSelected flex items-center justify-center space-x-4';
        div.innerHTML = `
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-green-400 font-medium">${file.name}</p>
                    <p class="text-gray-400 text-sm">${this.formatFileSize(file.size)}</p>
                </div>
                <button type="button" class="text-red-400 hover:text-red-300" id="clearFileBtn">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
        `;
        dropZone.appendChild(div);
        // Add event listener for clear button
        document.getElementById('clearFileBtn').addEventListener('click', () => {
            this.clearFileSelection();
        });

        // Add success animation
        dropZone.classList.add('animate-pulse');
        setTimeout(() => dropZone.classList.remove('animate-pulse'), 3600);
    }

    clearFileSelection() {
        const input = document.getElementById('zipFile');
        const dropZone = document.querySelector('#dropZone');
        const inputSelected = document.querySelector('.inputSelected');
        console.log(input.files.length)
        if (input.files.length > 0) {
            input.value = '';
            dropZone.classList.remove('hidden');
            inputSelected.remove();
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Validation in real time
    setupRealTimeValidation() {
        const requiredFields = ['githubUrl', 'description'];

        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => {
                    this.validateField(field);
                });

                field.addEventListener('blur', () => {
                    this.validateField(field);
                });
            }
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const isRequired = field.parentElement.querySelector('.text-red-400');

        // Remove existing validation states
        field.classList.remove('border-red-500', 'border-green-500');

        if (isRequired && !value) {
            field.classList.add('border-red-500');
            this.showFieldError(field, 'Ce champ est requis');
        } else if (value) {
            field.classList.add('border-green-500');
            this.hideFieldError(field);

            // Additional validation for URL fields
            if (field.type === 'url' && value && !this.isValidUrl(value)) {
                field.classList.remove('border-green-500');
                field.classList.add('border-red-500');
                this.showFieldError(field, 'URL invalide');
            }
        } else {
            this.hideFieldError(field);
        }
    }

    showFieldError(field, message) {
        let errorElement = field.parentElement.querySelector('.field-error');
        if (!errorElement) {
            errorElement = document.createElement('p');
            errorElement.className = 'field-error text-red-400 text-sm mt-1';
            field.parentElement.appendChild(errorElement);
        }
        errorElement.textContent = message;
    }

    hideFieldError(field) {
        const errorElement = field.parentElement.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    setupAutoSave() {
        const formData = {};
        const fields = ['githubUrl', 'demoUrl', 'description', 'notes'];

        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('input', () => {
                    formData[fieldId] = field.value;
                    // Auto-save to memory (replace with actual storage if needed)
                    this.saveFormData(formData);
                });
            }
        });

        // Load saved data on init
        this.loadFormData();
    }

    saveFormData(data) {
        // Store in memory for this session
        this.formData = { ...data };

        // Visual feedback for auto-save
        this.showAutoSaveIndicator();
    }

    loadFormData() {
        // Load from memory if available
        if (this.formData) {
            Object.keys(this.formData).forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && this.formData[fieldId]) {
                    field.value = this.formData[fieldId];
                }
            });
        }
    }

    // Show auto-save indicator
    showAutoSaveIndicator() {
        // Create or update auto-save indicator
        let indicator = document.getElementById('autoSaveIndicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.id = 'autoSaveIndicator';
            indicator.className = 'fixed top-4 right-4 bg-green-600 text-white px-3 py-1 rounded-lg text-sm opacity-0 transition-opacity z-50';
            indicator.textContent = 'Sauvegarde automatique...';
            document.body.appendChild(indicator);
        }

        indicator.classList.remove('opacity-0');
        indicator.classList.add('opacity-100');

        setTimeout(() => {
            indicator.classList.remove('opacity-100');
            indicator.classList.add('opacity-0');
        }, 2000);
    }

    // Handle form submission
    async handleSubmission() {
        // if (!this.userRegistered) {
        //     this.showAlert('Vous devez vous inscrire à ce défi pour soumettre une solution.', 'error');
        //     return;
        // }

        // Validate form
        if (!this.validateForm()) {
            return;
        }
        // Show loading state
        const submitBtn = document.querySelector('#' + this.submissionType + 'SubmissionForm button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = `
            <i data-lucid="loader" class="fa-solid fa-spinner animate-spin"></i>
            Soumission en cours...
        `;
        submitBtn.disabled = true;

        try {
            const formData = new FormData();
            const data = this.collectFormData();

            if (!this.userRegistered) {
                this.showAlert('Vous devez vous inscrire à ce défi pour soumettre une solution.', 'error');
                return;
            }
            // Add all form fields to FormData
            Object.keys(data).forEach(key => {
                if (key !== 'zipFile') {
                    formData.append(key, data[key]);
                }
            });

            // If it's a ZIP submission, add the file
            if (this.submissionType === 'zip') {
                const zipFile = document.getElementById('zipFile').files[0];
                formData.append('zipFile', zipFile);
            }

            console.log(formData.forEach((value, key) => {
                console.log(key, value);
            }));
            return;
            // Submit to API
            const response = await fetch(`/api/challenges/${this.challengeId}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                this.showSuccessMessage();
                // Clear form data
                this.formData = {};
                // Redirect after success
                setTimeout(() => {
                    window.location.href = '/challenge_dev';
                }, 3000);
            } else {
                throw new Error(result.message || 'Erreur lors de la soumission');
            }

        } catch (error) {
            console.error('Submission error:', error);
            this.showAlert('Erreur lors de la soumission: ' + error.message, 'error');
        } finally {
            // Restore button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    validateForm() {
        const errors = [];

        // Check submission type specific requirements
        if (this.submissionType === 'github') {
            const githubUrl = document.getElementById('githubUrl').value.trim();
            if (!githubUrl) {
                errors.push('L\'URL du dépôt GitHub est requise');
            } else if (!this.isValidUrl(githubUrl)) {
                errors.push('L\'URL du dépôt GitHub n\'est pas valide');
            }
            // Check description
            const description = document.getElementById('githubDescription').value.trim();
            if (!description) {
                errors.push('La description de votre solution est requise');
            }
        } else if (this.submissionType === 'zip') {
            const zipFile = document.getElementById('zipFile').files[0];
            if (!zipFile) {
                errors.push('Le fichier ZIP est requis');
            }
            // Check description
            const description = document.getElementById('zipDescription').value.trim();
            if (!description) {
                errors.push('La description de votre solution est requise');
            }
        }


        if (errors.length > 0) {
            this.showAlert('Veuillez corriger les erreurs suivantes:<br>• ' + errors.join('<br>• '), 'error', true);
            return false;
        }

        return true;
    }

    /**
     * Collects form data and returns it as an object.
     * 
     * The following fields are collected:
     * - challengeId: the ID of the challenge
     * - submissionType: the type of submission (github or zip)
     * - description: the description of the solution
     * - notes: any additional notes
     * - demoUrl: the URL to a demo of the solution
     * - githubUrl: the URL of the GitHub repository (only for github submissions)
     * - fileName: the name of the ZIP file (only for zip submissions)
     * - fileSize: the size of the ZIP file in bytes (only for zip submissions)
     * 
     * @return {Object} the collected form data
     */
    collectFormData() {
        const formData = {
            challengeId: this.challengeId,
            submissionType: this.submissionType,
        };

        if (this.submissionType === 'github') {
            formData.description = document.getElementById('githubDescription').value.trim();
            formData.notes = document.getElementById('githubNotes').value.trim();
            formData.githubDemoUrl = document.getElementById('githubDemoUrl').value.trim();
            formData.githubUrl = document.getElementById('githubUrl').value.trim();
        } else if (this.submissionType === 'zip') {
            formData.description = document.getElementById('zipDescription').value.trim();
            formData.notes = document.getElementById('zipNotes').value.trim();
            formData.demoUrl = document.getElementById('zipDemoUrl').value.trim();
            const zipFile = document.getElementById('zipFile').files[0];
            formData.fileName = zipFile.name;
            formData.fileSize = zipFile.size;
        }

        return formData;
    }

    showSuccessMessage() {
        const successHtml = `
            <div class="text-center py-8">
                <div class="animate-bounce mb-4">
                    <svg class="w-16 h-16 text-green-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-green-400 mb-2">Solution soumise avec succès!</h3>
                <p class="text-gray-400 mb-4">Votre solution a été envoyée et sera évaluée par notre équipe.</p>
                <p class="text-sm text-gray-500">Redirection en cours...</p>
            </div>
        `;

        const formContainer = document.querySelector('.lg\\:col-span-2 .bg-gray-800');
        formContainer.innerHTML = successHtml;
    }

    showAlert(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');

        if (type === 'error') {
            // Création d'une superposition plein écran pour les erreurs
            notification.className = 'fixed inset-0 flex items-center justify-center bg-black/80 backdrop-blur-sm z-50';
            const alertContent = document.createElement('div');
            alertContent.className = 'bg-gray-900/90 border border-red-500/30 p-6 rounded-lg shadow-lg shadow-black/30 max-w-lg w-full mx-4 text-white text-center transform scale-100 transition-all duration-300';

            // Contenu de l'alerte d'erreur
            const iconContainer = document.createElement('div');
            iconContainer.className = 'mb-4';
            const icon = document.createElement('i');
            icon.setAttribute('data-lucide', 'x-circle');
            icon.className = 'w-12 h-12 text-red-400 mx-auto';
            iconContainer.appendChild(icon);

            alertContent.appendChild(iconContainer);
            alertContent.innerHTML += `
                <div class="text-2xl font-medium mb-4">${message}</div>
                <div class="text-gray-300/90 text-sm countdown mb-2"></div>
            `;
            notification.appendChild(alertContent);

            const formContainer = document.getElementById('formContainer');

            formContainer.parentElement.querySelectorAll('button').forEach(button => {
                button.disabled = true;
            });
            // Disable form
            formContainer.style.opacity = '0.5';
            formContainer.style.pointerEvents = 'none';

            // Add blur effect
            formContainer.classList.add('blur-sm');

            // Compte à rebours et redirection
            let countdown = 5;
            const countdownElement = alertContent.querySelector('.countdown');
            const timer = setInterval(() => {
                countdown--;
                if (countdownElement) {
                    countdownElement.textContent = `Redirection dans ${countdown} secondes...`;
                }
                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = '/HACKATHON_ESGIS/public/user/challenge_dev';
                }
            }, 1000);
        } else {
            // Pour les autres types de notifications (empilables)
            const notifications = document.querySelectorAll('.notification-alert');
            const offset = notifications.length * 4.5; // ~72px spacing

            // Style similaire à showNotification de main.js
            notification.className = `notification-alert fixed right-4 transform translate-x-0 max-w-md w-auto bg-gray-900/90 backdrop-blur-sm border ${type === 'warning' ? 'border-yellow-500/30' : 'border-blue-500/30'
                } rounded-lg shadow-lg shadow-black/30 p-3 flex items-start justify-between gap-3 z-[1000] cursor-pointer animate-fade-in`;

            // Conteneur d'icône
            const iconContainer = document.createElement('div');
            iconContainer.className = 'flex-shrink-0 pt-0.5';
            const icon = document.createElement('i');
            icon.setAttribute('data-lucide', type === 'warning' ? 'alert-triangle' : 'info');
            icon.className = `w-5 h-5 ${type === 'warning' ? 'text-yellow-400' : 'text-blue-400'}`;
            iconContainer.appendChild(icon);

            // Contenu du message
            const textContainer = document.createElement('div');
            textContainer.className = 'flex-1';
            const messageElement = document.createElement('p');
            messageElement.className = 'text-white font-medium text-sm';
            messageElement.textContent = message;
            textContainer.appendChild(messageElement);

            // Bouton de fermeture
            const closeContainer = document.createElement('div');
            closeContainer.className = 'flex-shrink-0 pt-0.5';
            const closeButton = document.createElement('button');
            closeButton.className = 'text-gray-400 hover:text-white transition-colors focus:outline-none';
            const closeIcon = document.createElement('i');
            closeIcon.setAttribute('data-lucide', 'x');
            closeIcon.className = 'w-4 h-4';
            closeButton.appendChild(closeIcon);

            // Assemblage des éléments
            notification.appendChild(iconContainer);
            notification.appendChild(textContainer);
            closeContainer.appendChild(closeButton);
            notification.appendChild(closeContainer);

            // Position initiale
            notification.style.top = `${offset + 1}rem`;

            // Gestionnaires d'événements
            closeButton.addEventListener('click', (e) => {
                e.stopPropagation();
                hideNotification(notification);
            });

            notification.addEventListener('click', () => {
                hideNotification(notification);
            });
        }

        // Ajouter la notification au DOM
        document.body.appendChild(notification);

        // Initialiser les icônes Lucide
        if (window.lucide) {
            window.lucide.createIcons();
        }

        // Gestion de la disparition pour les notifications non-erreur
        if (type !== 'error') {
            setTimeout(() => {
                hideNotification(notification);
            }, duration);
        }
    }

    // Fonction pour masquer les notifications avec animation
    hideNotification(notification) {
        notification.classList.add('animate-fade-out');
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';

        setTimeout(() => {
            notification.remove();
            // Réajuster la position des autres notifications
            const notifications = document.querySelectorAll('.notification-alert');
            notifications.forEach((notif, index) => {
                notif.style.top = `${(index * 4.5 + 1)}rem`;
            });
        }, 3000);
    }

    addAlertStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }

    showLoading() {
        // Add loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
        overlay.innerHTML = `
            <div class="bg-gray-800 rounded-lg p-6 flex items-center space-x-4">
                <svg class="animate-spin w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-white">Chargement...</span>
            </div>
        `;
        document.body.appendChild(overlay);
    }

    hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.remove();
        }
    }

    animateElementsIn() {
        const elements = document.querySelectorAll('.bg-gray-800');
        elements.forEach((element, index) => {
            setTimeout(() => {
                element.classList.add('animate-fade-in');
            }, index * 200);
        });
    }
}

// Utility functions
function goBack(page) {
    if (page) {
        window.location.href = '/HACKATHON_ESGIS/public/user/' + page;
    } else {
        window.location.href = '/HACKATHON_ESGIS/public/user/challenge_dev';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    let challengeSubmission = new ChallengeSubmission();
});

// Add global styles for smooth transitions
document.addEventListener('DOMContentLoaded', () => {
    const globalStyles = document.createElement('style');
    globalStyles.textContent = `
        * {
            transition: all 0.3s ease;
        }
        
        input:focus, textarea:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        
        button:hover {
            transform: translateY(-1px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .border-dashed {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    `;
    document.head.appendChild(globalStyles);
});