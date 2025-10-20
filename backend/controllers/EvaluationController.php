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

    public function __construct($db, $tokenManager) {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->evaluation = new \Auth\Model\Evaluation($this->db);
        $this->project = new \Auth\Model\Project($this->db);
    }

    public function create($data) {
        try {
            $this->validateMethod('POST');
            
            // Récupérer l'utilisateur actuel depuis le token
            $currentUserId = $this->tokenManager->getCurrentUserId();
            
            // Vérifier que c'est un juge
            if (!$this->isAdmin($currentUserId, 'judge')) {
                throw new Exception('Non autorisé - Réservé aux membres du jury');
            }

            
            $requiredFields = ['project_id', 'score', 'criteria', 'comments'];
            

            $this->validateRequiredFields($data, $requiredFields);

            // Validation du score (0-100 au lieu de 0-20)
            $score = (float)$data['score'];
            if ($score < 0 || $score > 100) {
                throw new Exception('Le score doit être compris entre 0 et 100');
            }

            // Validation du JSON des critères
            $criteria = $data['criteria'];
            if (!is_string($criteria) || !json_decode($criteria)) {
                throw new Exception('Format des critères invalide');
            }

            // Validation du JSON des commentaires
            $comments = $data['comments'];
            if (!is_string($comments) || !json_decode($comments)) {
                throw new Exception('Format des commentaires invalide');
            }

            // Préparer les données pour l'insertion
            $evaluationData = [
                'project_id' => (int)$data['project_id'],
                'judge_id' => $currentUserId,  // Utilise l'ID du token
                'score' => $score,
                'criteria' => $criteria,       // JSON string
                'comments' => $comments,       // JSON string
                'evaluated_at' => date('Y-m-d H:i:s')
            ];

            // Créer l'évaluation
            $evaluationId = $this->evaluation->create($evaluationData);

            // Traiter l'action sur le projet si spécifiée
            if (isset($data['action']) && isset($data['status'])) {
                $this->updateProjectStatus($data['project_id'], $data['status'], $data['action']);
            }

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

    /**
     * Méthode privée pour mettre à jour le statut du projet
     */
    private function updateProjectStatus($projectId, $status, $action) {
        try {
            $updateData = ['status' => $status];
            
            // Ajouter des métadonnées selon l'action
            switch ($action) {
                case 'validate':
                    $updateData['validated_at'] = date('Y-m-d H:i:s');
                    break;
                case 'reject':
                    $updateData['rejected_at'] = date('Y-m-d H:i:s');
                    break;
                case 'request_revision':
                    $updateData['revision_requested_at'] = date('Y-m-d H:i:s');
                    break;
            }
            
            $this->project->update($projectId, $updateData);
            
        } catch (Exception $e) {
            // Log l'erreur mais ne pas faire échouer l'évaluation
            error_log("Erreur mise à jour statut projet {$projectId}: " . $e->getMessage());
        }
    }
}
