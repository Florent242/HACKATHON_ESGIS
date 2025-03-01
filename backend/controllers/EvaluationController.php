<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Evaluation.php';
require_once __DIR__ . '/../models/Projet.php';

class EvaluationController extends Controller {
    private $evaluation;
    private $projet;
    private $db;

    public function __construct() {
        parent::__construct();
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->evaluation = new Evaluation($this->db);
        $this->projet = new Projet($this->db);
    }

    // Afficher la liste des évaluations d'un projet
    public function index($projetId) {
        try {
            // Vérifier si le projet existe
            $projet = $this->projet->find($projetId);
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }

            // Récupérer les évaluations
            $evaluations = $this->evaluation->getByProjet($projetId);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['evaluations' => $evaluations]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/evaluation/index.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/projets/{$projetId}");
        }
    }

    // Afficher une évaluation spécifique
    public function show($id) {
        try {
            $evaluation = $this->evaluation->find($id);
            if (!$evaluation) {
                throw new Exception('Évaluation non trouvée');
            }

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse($evaluation);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/evaluation/show.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 404);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/evaluations');
        }
    }

    // Créer une nouvelle évaluation
    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            $data = [
                'projet_id' => $_POST['projet_id'] ?? null,
                'juge_id' => $_POST['juge_id'] ?? null,
                'score' => $_POST['score'] ?? null,
                'commentaire' => $_POST['commentaire'] ?? null
            ];

            if (!$data['projet_id']) {
                throw new Exception('ID du projet requis');
            }

            if (!$data['juge_id']) {
                throw new Exception('ID du juge requis');
            }

            if (!$data['score']) {
                throw new Exception('Score requis');
            }

            // Vérifier si le projet existe
            $projet = $this->projet->find($data['projet_id']);
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }

            // Créer l'évaluation
            $evaluationId = $this->evaluation->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Évaluation créée avec succès',
                'data' => [
                    'id' => $evaluationId
                ]
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    // Mettre à jour une évaluation
    public function update($id) {
        try {
            // Vérifier si l'utilisateur est un juge
            if (!isAuthenticated() || !hasRole('juge')) {
                throw new Exception('Non autorisé');
            }

            // Vérifier si l'évaluation existe
            $evaluation = $this->evaluation->find($id);
            if (!$evaluation) {
                throw new Exception('Évaluation non trouvée');
            }

            // Vérifier si l'utilisateur est l'auteur de l'évaluation
            if ($evaluation['juge_id'] !== $_SESSION['user_id']) {
                throw new Exception('Non autorisé à modifier cette évaluation');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Vérifier le token CSRF
                if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                    throw new Exception('Token CSRF invalide');
                }

                // Récupérer et nettoyer les données
                $data = [
                    'score' => (int)$_POST['score'],
                    'commentaire' => cleanInput($_POST['commentaire'])
                ];

                // Mettre à jour l'évaluation
                $this->evaluation->update($id, $data);

                setFlashMessage('success', 'Évaluation mise à jour avec succès !');
                redirect("/evaluations/{$id}");
            }

            // Afficher le formulaire
            require_once VIEWS_PATH . '/evaluation/edit.php';
        } catch (Exception $e) {
            setFlashMessage('error', $e->getMessage());
            redirect("/evaluations/{$id}");
        }
    }

    // Supprimer une évaluation
    public function delete($id) {
        try {
            // Vérifier si l'utilisateur est un juge
            if (!isAuthenticated() || !hasRole('juge')) {
                throw new Exception('Non autorisé');
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Méthode non autorisée');
            }

            // Vérifier le token CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                throw new Exception('Token CSRF invalide');
            }

            // Vérifier si l'évaluation existe
            $evaluation = $this->evaluation->find($id);
            if (!$evaluation) {
                throw new Exception('Évaluation non trouvée');
            }

            // Vérifier si l'utilisateur est l'auteur de l'évaluation
            if ($evaluation['juge_id'] !== $_SESSION['user_id']) {
                throw new Exception('Non autorisé à supprimer cette évaluation');
            }

            // Supprimer l'évaluation
            $this->evaluation->delete($id);

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['message' => 'Évaluation supprimée avec succès']);
            }

            setFlashMessage('success', 'Évaluation supprimée avec succès !');
            redirect("/projets/{$evaluation['projet_id']}");
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
            setFlashMessage('error', $e->getMessage());
            redirect("/evaluations/{$id}");
        }
    }

    // Afficher les évaluations d'un juge
    public function myEvaluations() {
        try {
            // Vérifier si l'utilisateur est un juge
            if (!isAuthenticated() || !hasRole('juge')) {
                throw new Exception('Non autorisé');
            }

            // Récupérer les évaluations
            $evaluations = $this->evaluation->getByJuge($_SESSION['user_id']);

            // Si c'est une requête AJAX, renvoyer JSON
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['evaluations' => $evaluations]);
            }

            // Sinon, afficher la vue
            require_once VIEWS_PATH . '/evaluation/my_evaluations.php';
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                jsonResponse(['error' => $e->getMessage()], 500);
            }
            setFlashMessage('error', $e->getMessage());
            redirect('/dashboard');
        }
    }
}
