<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Challenge.php';
require_once __DIR__ . '/../models/Hackathon.php';

class ChallengeController {
    private $challengeModel;
    private $hackathonModel;

    public function __construct() {
        $this->challengeModel = new Challenge();
        $this->hackathonModel = new Hackathon();
    }

    // Afficher la liste des challenges d'un hackathon
    public function index($hackathonId) {
        try {
            // Vérifier si le hackathon existe
            $hackathon = $this->hackathonModel->find($hackathonId);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            // Récupérer les challenges
            $challenges = $this->challengeModel->getByHackathon($hackathonId);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['challenges' => $challenges]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/challenge/index.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$hackathonId}");
        }
    }

    // Afficher un challenge spécifique
    public function show($id) {
        try {
            $challenge = $this->challengeModel->find($id);
            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }

            // Récupérer les projets associés
            $challenge['projets'] = $this->challengeModel->getProjets($id);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse($challenge);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/challenge/show.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 404);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/challenges');
        }
    }

    // Créer un nouveau challenge
    public function create($hackathonId) {
        try {
            // Vérifier si l'utilisateur est un organisateur
            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            // Vérifier si le hackathon existe
            $hackathon = $this->hackathonModel->find($hackathonId);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé');
            }

            // Vérifier si l'utilisateur est l'organisateur du hackathon
            if ($hackathon['created_by'] !== $_SESSION['user_id']) {
                throw new Exception('Non autorisé à créer des challenges pour ce hackathon');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Vérifier le token CSRF
                if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                    throw new Exception('Token CSRF invalide');
                }

                // Récupérer et nettoyer les données
                $data = [
                    'title' => cleanInput($_POST['title']),
                    'description' => cleanInput($_POST['description']),
                    'hackathon_id' => $hackathonId,
                    'criteres_evaluation' => cleanInput($_POST['criteres_evaluation']),
                    'ressources' => cleanInput($_POST['ressources'] ?? ''),
                    'created_by' => $_SESSION['user_id']
                ];

                // Créer le challenge
                $challengeId = $this->challengeModel->create($data);

                setFlashMessage('success', 'Challenge créé avec succès !');
                redirect("/challenges/{$challengeId}");
            }

            // Afficher le formulaire
            require_once VIEWS_PATH . '/challenge/create.php';
        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect("/hackathons/{$hackathonId}");
        }
    }

    // Mettre à jour un challenge
    public function update($id) {
        try {
            // Vérifier si l'utilisateur est un organisateur
            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            // Vérifier si le challenge existe
            $challenge = $this->challengeModel->find($id);
            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }

            // Vérifier si l'utilisateur est le créateur du challenge
            if ($challenge['created_by'] !== $_SESSION['user_id']) {
                throw new Exception('Non autorisé à modifier ce challenge');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Vérifier le token CSRF
                if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                    throw new Exception('Token CSRF invalide');
                }

                // Récupérer et nettoyer les données
                $data = [
                    'title' => cleanInput($_POST['title']),
                    'description' => cleanInput($_POST['description']),
                    'criteres_evaluation' => cleanInput($_POST['criteres_evaluation']),
                    'ressources' => cleanInput($_POST['ressources'] ?? '')
                ];

                // Mettre à jour le challenge
                $this->challengeModel->update($id, $data);

                setFlashMessage('success', 'Challenge mis à jour avec succès !');
                redirect("/challenges/{$id}");
            }

            // Afficher le formulaire
            require_once VIEWS_PATH . '/challenge/edit.php';
        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect("/challenges/{$id}");
        }
    }

    // Supprimer un challenge
    public function delete($id) {
        try {
            // Vérifier si l'utilisateur est un organisateur
            if (!isAuthenticated() || !hasRole('organisateur')) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Vérifier si le challenge existe
            $challenge = $this->challengeModel->find($id);
            if (!$challenge) {
                throw new Exception('Challenge non trouvé');
            }

            // Vérifier si l'utilisateur est le créateur du challenge
            if ($challenge['created_by'] !== $_SESSION['user_id']) {
                throw new Exception('Non autorisé à supprimer ce challenge');
            }

            // Supprimer le challenge
            $this->challengeModel->delete($id);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['message' => 'Challenge supprimé avec succès']);
            }

            setFlashMessage('success', 'Challenge supprimé avec succès !');
            redirect("/hackathons/{$challenge['hackathon_id']}");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/challenges/{$id}");
        }
    }
}
