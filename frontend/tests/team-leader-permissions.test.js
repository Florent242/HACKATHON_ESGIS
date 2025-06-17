const axios = require('axios');
const { v4: uuidv4 } = require('uuid');

// Configuration de base
describe('Permissions du leader d\'équipe', () => {
    let baseUrl = 'http://hackathon.esgis.bj/api.php';
    let hackathonId;
    let teamId;
    let leaderId;
    let memberId;
    let member2Id;

    // Configuration commune pour tous les tests
    beforeEach(async () => {
        // Créer un hackathon de test
        const hackathonResponse = await axios.post(`${baseUrl}/hackathons`, {
            name: `Hackathon Test ${uuidv4()}`,
            description: 'Hackathon de test pour les permissions',
            start_date: new Date().toISOString(),
            end_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(), // +1 semaine
            rules: 'Règles de test'
        });
        hackathonId = hackathonResponse.data.id;

        // Créer un utilisateur leader
        const leaderResponse = await axios.post(`${baseUrl}/users`, {
            email: `leader${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Leader Test'
        });
        leaderId = leaderResponse.data.id;

        // Créer un utilisateur membre
        const memberResponse = await axios.post(`${baseUrl}/users`, {
            email: `member${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Member Test'
        });
        memberId = memberResponse.data.id;

        // Créer une équipe avec le leader
        const teamResponse = await axios.post(`${baseUrl}/teams`, {
            nom: `Équipe Test ${uuidv4()}`,
            hackathon_id: hackathonId,
            leader_id: leaderId
        });
        teamId = teamResponse.data.id;
    });

    // Nettoyage après chaque test
    afterEach(async () => {
        try {
            // Supprimer les utilisateurs
            await axios.delete(`${baseUrl}/users/${leaderId}`);
            await axios.delete(`${baseUrl}/users/${memberId}`);
            await axios.delete(`${baseUrl}/users/${member2Id}`);
            
            // Supprimer l'équipe
            await axios.delete(`${baseUrl}/teams/${teamId}`);
            
            // Supprimer le hackathon
            await axios.delete(`${baseUrl}/hackathons/${hackathonId}`);
        } catch (error) {
            console.error('Erreur lors du nettoyage:', error);
        }
    });

    // Tests
    test('Le leader peut accepter une demande d\'adhésion', async () => {
        // Créer une demande d'adhésion
        const requestResponse = await axios.post(`${baseUrl}/teams/${teamId}/requests`, {
            user_id: memberId
        });
        expect(requestResponse.status).toBe(200);

        // Le leader accepte la demande
        const acceptResponse = await axios.post(`${baseUrl}/teams/${teamId}/leader/accept/${memberId}`);
        expect(acceptResponse.status).toBe(200);
        expect(acceptResponse.data.message).toBe('Demande d\'adhésion acceptée avec succès');
    });

    test('Un membre non-leader ne peut pas accepter une demande', async () => {
        // Créer un deuxième membre
        const member2Response = await axios.post(`${baseUrl}/users`, {
            email: `member2${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Member 2 Test'
        });
        member2Id = member2Response.data.id;

        // Créer une demande d'adhésion
        const requestResponse = await axios.post(`${baseUrl}/teams/${teamId}/requests`, {
            user_id: member2Id
        });
        expect(requestResponse.status).toBe(200);

        // Le membre non-leader essaie d'accepter la demande
        try {
            await axios.post(`${baseUrl}/teams/${teamId}/leader/accept/${member2Id}`);
            expect(true).toBe(false); // Devrait échouer
        } catch (error) {
            expect(error.response.status).toBe(403);
            expect(error.response.data.message).toBe('Accès non autorisé');
        }
    });

    test('Le leader peut ajouter des membres', async () => {
        // Créer un deuxième membre
        const member2Response = await axios.post(`${baseUrl}/users`, {
            email: `member2${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Member 2 Test'
        });
        member2Id = member2Response.data.id;

        // Le leader ajoute le membre
        const addMemberResponse = await axios.post(`${baseUrl}/teams/${teamId}/members/add`, {
            user_id: member2Id
        });
        expect(addMemberResponse.status).toBe(200);
        expect(addMemberResponse.data.message).toBe('Membre ajouté avec succès');
    });

    test('Un membre non-leader ne peut pas ajouter de membres', async () => {
        // Créer un deuxième membre
        const member2Response = await axios.post(`${baseUrl}/users`, {
            email: `member2${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Member 2 Test'
        });
        member2Id = member2Response.data.id;

        // Le membre non-leader essaie d'ajouter un membre
        try {
            await axios.post(`${baseUrl}/teams/${teamId}/members/add`, {
                user_id: member2Id
            });
            expect(true).toBe(false); // Devrait échouer
        } catch (error) {
            expect(error.response.status).toBe(403);
            expect(error.response.data.message).toBe('Accès non autorisé');
        }
    });

    test('Le leader peut changer de leader', async () => {
        // Créer un deuxième membre
        const member2Response = await axios.post(`${baseUrl}/users`, {
            email: `member2${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Member 2 Test'
        });
        member2Id = member2Response.data.id;

        // Le leader change de leader
        const changeLeaderResponse = await axios.post(`${baseUrl}/teams/${teamId}/leader/change`, {
            new_leader_id: member2Id
        });
        expect(changeLeaderResponse.status).toBe(200);
        expect(changeLeaderResponse.data.message).toBe('Leader changé avec succès');
    });

    test('Un membre non-leader ne peut pas changer de leader', async () => {
        // Créer un deuxième membre
        const member2Response = await axios.post(`${baseUrl}/users`, {
            email: `member2${uuidv4()}@test.com`,
            password: 'password123',
            fullname: 'Member 2 Test'
        });
        member2Id = member2Response.data.id;

        // Le membre non-leader essaie de changer de leader
        try {
            await axios.post(`${baseUrl}/teams/${teamId}/leader/change`, {
                new_leader_id: member2Id
            });
            expect(true).toBe(false); // Devrait échouer
        } catch (error) {
            expect(error.response.status).toBe(403);
            expect(error.response.data.message).toBe('Accès non autorisé');
        }
    });
});
