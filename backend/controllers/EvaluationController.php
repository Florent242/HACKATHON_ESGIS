<?php
namespace Auth\Controller;

use Exception;

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../models/Evaluation.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/Controller.php';

class EvaluationController extends Controller {
    private $evaluation;
    private $project;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->evaluation = new \Auth\Model\Evaluation($this->db);
        $this->project = new \Auth\Model\Project($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');

            if (!hasRole('jury')) {
                throw new Exception('Non autorisé - Réservé aux membres du jury');
            }

            $requiredFields = ['project_id', 'jury_id', 'note', 'commentaire'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $note = (float)$_POST['note'];
            if ($note < 0 || $note > 20) {
                throw new Exception('La note doit être comprise entre 0 et 20');
            }

            $data = [
                'project_id' => (int)$_POST['project_id'],
                'jury_id' => (int)$_POST['jury_id'],
                'note' => $note,
                'commentaire' => $_POST['commentaire'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $evaluationId = $this->evaluation->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Évaluation créée avec succès',
                'data' => ['id' => $evaluationId]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère toutes les évaluations
     */
    public function getAll() {
        try {
            $this->validateMethod('GET');

            // Vérifier si l'utilisateur a les droits
            if (!hasRole('admin') && !hasRole('jury')) {
                throw new Exception('Non autorisé');
            }

            $evaluations = $this->evaluation->getAll();

            $this->jsonResponse([
                'success' => true,
                'data' => $evaluations
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function get($id) {
        try {
            $this->validateMethod('GET');

            $evaluation = $this->evaluation->find($id);
            if (!$evaluation) {
                throw new Exception('Évaluation non trouvée');
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $evaluation
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function getByProject($projectId) {
        try {
            $this->validateMethod('GET');

            $evaluations = $this->evaluation->getByProject($projectId);

            $this->jsonResponse([
                'success' => true,
                'data' => $evaluations
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getByJudge($juryId) {
        try {
            $this->validateMethod('GET');

            $evaluations = $this->evaluation->getByJudge($juryId);

            $this->jsonResponse([
                'success' => true,
                'data' => $evaluations
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function update($id) {
        try {
            $this->validateMethod('POST');

            if (!hasRole('jury')) {
                throw new Exception('Non autorisé - Réservé aux membres du jury');
            }

            $updatableFields = ['note', 'commentaire'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            if (isset($data['note'])) {
                $note = (float)$data['note'];
                if ($note < 0 || $note > 20) {
                    throw new Exception('La note doit être comprise entre 0 et 20');
                }
                $data['note'] = $note;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->evaluation->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Évaluation mise à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function delete($id) {
        try {
            $this->validateMethod('POST');

            if (!hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            $this->evaluation->delete($id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Évaluation supprimée avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getAverageScore($projectId) {
        try {
            $this->validateMethod('GET');

            $average = $this->evaluation->getAverageScore($projectId);

            $this->jsonResponse([
                'success' => true,
                'data' => ['moyenne' => $average]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getStats($hackathonId) {
        try {
            $this->validateMethod('GET');

            $stats = [
                'total_evaluations' => $this->evaluation->countByHackathon($hackathonId),
                'moyenne_generale' => $this->evaluation->getAverageScoreByHackathon($hackathonId),
                'projets_evalues' => $this->evaluation->countEvaluatedProjects($hackathonId),
                'projets_non_evalues' => $this->evaluation->countNonEvaluatedProjects($hackathonId)
            ];

            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function getMoyenneProjet($projectId) {
        try {
            $this->validateMethod('GET');

            // Vérifier si le projet existe
            $project = $this->project->find($projectId);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            $stats = $this->evaluation->getMoyenneProjet($projectId);

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'project_id' => (int)$projectId,
                    'moyenne' => $stats ? $stats['moyenne_score'] : 0
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
