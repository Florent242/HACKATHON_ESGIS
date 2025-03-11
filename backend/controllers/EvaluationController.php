<?php

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/Evaluation.php';
require_once __DIR__ . '/../models/Projet.php';
require_once __DIR__ . '/Controller.php';

class EvaluationController extends Controller {
    private $evaluation;
    private $projet;
    private $db;

    public function __construct($db) {
        parent::__construct();
        $this->db = $db;
        $this->evaluation = new Evaluation($this->db);
        $this->projet = new Projet($this->db);
    }

    public function create() {
        try {
            $this->validateMethod('POST');
            
            $requiredFields = ['projet_id', 'evaluateur_id', 'note', 'commentaire'];
            $this->validateRequiredFields($_POST, $requiredFields);

            // Validation de la note
            $note = (int)$_POST['note'];
            if ($note < 0 || $note > 20) {
                throw new Exception('La note doit être comprise entre 0 et 20');
            }

            // Vérifier si le projet existe
            $projet = $this->projet->find($_POST['projet_id']);
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }

            $data = [
                'projet_id' => (int)$_POST['projet_id'],
                'evaluateur_id' => (int)$_POST['evaluateur_id'],
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

    public function getByProjet($projetId) {
        try {
            $this->validateMethod('GET');
            
            // Vérifier si le projet existe
            $projet = $this->projet->find($projetId);
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }

            $evaluations = $this->evaluation->getByProjet($projetId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $evaluations
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update($id) {
        try {
            $this->validateMethod('POST');
            
            $updatableFields = ['note', 'commentaire'];
            $data = $this->filterData($_POST, $updatableFields);
            
            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            // Validation de la note si présente
            if (isset($data['note'])) {
                $note = (int)$data['note'];
                if ($note < 0 || $note > 20) {
                    throw new Exception('La note doit être comprise entre 0 et 20');
                }
                $data['note'] = $note;
            }

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
            
            // Vérifier si l'utilisateur a les droits
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

    public function getMoyenneProjet($projetId) {
        try {
            $this->validateMethod('GET');
            
            // Vérifier si le projet existe
            $projet = $this->projet->find($projetId);
            if (!$projet) {
                throw new Exception('Projet non trouvé');
            }

            $moyenne = $this->evaluation->getMoyenneProjet($projetId);
            
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'projet_id' => (int)$projetId,
                    'moyenne' => round($moyenne, 2)
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
