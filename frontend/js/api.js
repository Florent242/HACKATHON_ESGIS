// Configuration de l'API
const API_BASE_URL = '/api';

// Classe pour gérer les appels API
class Api {
    // Méthode générique pour faire des requêtes
    static async fetch(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        const defaultHeaders = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        const config = {
            headers: {
                ...defaultHeaders,
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Une erreur est survenue');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // API Hackathons
    static async getHackathons() {
        return this.fetch('/hackathons');
    }

    static async getHackathon(id) {
        return this.fetch(`/hackathons/${id}`);
    }

    static async createHackathon(data) {
        return this.fetch('/hackathons', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updateHackathon(id, data) {
        return this.fetch(`/hackathons/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static async deleteHackathon(id) {
        return this.fetch(`/hackathons/${id}`, {
            method: 'DELETE'
        });
    }

    // API Équipes
    static async getEquipes() {
        return this.fetch('/equipes');
    }

    static async getEquipe(id) {
        return this.fetch(`/equipes/${id}`);
    }

    static async createEquipe(data) {
        return this.fetch('/equipes', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updateEquipe(id, data) {
        return this.fetch(`/equipes/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static async deleteEquipe(id) {
        return this.fetch(`/equipes/${id}`, {
            method: 'DELETE'
        });
    }

    // API Projets
    static async getProjets() {
        return this.fetch('/projets');
    }

    static async getProjet(id) {
        return this.fetch(`/projets/${id}`);
    }

    static async createProjet(data) {
        return this.fetch('/projets', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updateProjet(id, data) {
        return this.fetch(`/projets/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static async submitProjet(id, data) {
        return this.fetch(`/projets/${id}/submit`, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async deleteProjet(id) {
        return this.fetch(`/projets/${id}`, {
            method: 'DELETE'
        });
    }

    // API Évaluations
    static async getEvaluations() {
        return this.fetch('/evaluations');
    }

    static async getEvaluation(id) {
        return this.fetch(`/evaluations/${id}`);
    }

    static async createEvaluation(data) {
        return this.fetch('/evaluations', {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    static async updateEvaluation(id, data) {
        return this.fetch(`/evaluations/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    static async deleteEvaluation(id) {
        return this.fetch(`/evaluations/${id}`, {
            method: 'DELETE'
        });
    }

    static async getProjetMoyenne(projetId) {
        return this.fetch(`/evaluations/projet/${projetId}/moyenne`);
    }
}
