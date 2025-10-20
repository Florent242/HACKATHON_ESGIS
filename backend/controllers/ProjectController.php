<?php
namespace Auth\Controller;

use Exception;
use Auth\Model\Project;

if(!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if(!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if(!class_exists('Project')) {
    require_once __DIR__ . '/../models/Project.php';
}
if(!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}

class ProjectController extends Controller {
    private $project;
    private $db;

    public function __construct($db, $tokenManager) {
        parent::__construct($tokenManager);
        $this->db = $db;
        $this->project = new Project($this->db);
    }

    /**
     * Récupère tous les projets
     */
    public function getAll() {
        try {
            $this->validateMethod('GET');
            $projects = $this->project->getAll();

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère un projet par son ID
     * @param int $id ID du projet
     */
    public function get($id) {
        try {
            $this->validateMethod('GET');

            $project = $this->project->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé');
            }

            // Ajouter les évaluations (uniquement pour les juges et admins)
            if (hasRole('judge') || hasRole('admin')) {
                $project['evaluations'] = $this->project->getEvaluations($id);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $project
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Crée un nouveau projet
     */
    public function create() {
        try {
            $this->validateMethod('POST');

            $requiredFields = ['name', 'description', 'team_id', 'hackathon_id'];
            $this->validateRequiredFields($_POST, $requiredFields);

            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'team_id' => (int)$_POST['team_id'],
                'hackathon_id' => (int)$_POST['hackathon_id'],
                'repository_url' => $_POST['repository_url'] ?? null,
                'demo_url' => $_POST['demo_url'] ?? null,
                'documentation_url' => $_POST['documentation_url'] ?? null,
                'technologies' => $_POST['technologies'] ?? null,
                'status' => $_POST['status'] ?? 'ongoing'
            ];

            $projectId = $this->project->create($data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet créé avec succès',
                'data' => ['id' => $projectId, 'name' => $data['name']]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour un projet
     * @param int $id ID du projet
     */
    public function update($id) {
        try {
            $this->validateMethod('POST');

            $updatableFields = ['name', 'description', 'status', 'repository_url', 'demo_url', 'documentation_url',
                               'technologies', 'version', 'rule_compliance', 'security_issues'];
            $data = $this->filterData($_POST, $updatableFields);

            if (empty($data)) {
                throw new Exception('Aucune donnée à mettre à jour');
            }

            $this->project->update($id, $data);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Supprime un projet
     * @param int $id ID du projet
     */
    public function delete($id) {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits
            if (!hasRole('admin') && !hasRole('organizer')) {
                throw new Exception('Non autorisé');
            }

            $this->project->delete($id);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les projets d'une équipe
     * @param int $teamId ID de l'équipe
     */
    public function getByTeam($teamId) {
        try {
            $this->validateMethod('GET');

            $projects = $this->project->getByTeam($teamId);

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les projets d'un hackathon
     * @param int $hackathonId ID du hackathon
     */
    public function getByHackathon($hackathonId) {
        try {
            $this->validateMethod('GET');

            $projects = $this->project->getByHackathon($hackathonId);

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour le statut d'un projet
     * @param int $id ID du projet
     */
    public function updateStatus($id) {
        try {
            $this->validateMethod('POST');

            if (empty($_POST['status'])) {
                throw new Exception('Statut requis');
            }

            $status = $_POST['status'];
            $this->project->updateStatus($id, $status);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Statut du projet mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour le score d'un projet
     * @param int $id ID du projet
     */
    public function updateScore($id) {
        try {
            $this->validateMethod('POST');

            // Vérifier si l'utilisateur a les droits
            if (!hasRole('judge') && !hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            if (!isset($_POST['score'])) {
                throw new Exception('Score requis');
            }

            $score = (int)$_POST['score'];
            $judgesComments = $_POST['judges_comments'] ?? null;
            $evaluationCriteria = $_POST['evaluation_criteria'] ?? null;

            $this->project->updateScore($id, $score, $judgesComments, $evaluationCriteria);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Score du projet mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Met à jour la version d'un projet
     * @param int $id ID du projet
     */
    public function updateVersion($id) {
        try {
            $this->validateMethod('POST');

            if (empty($_POST['version'])) {
                throw new Exception('Version requise');
            }

            $version = $_POST['version'];
            $this->project->updateVersion($id, $version);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Version du projet mise à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Récupère les évaluations d'un projet
     * @param int $id ID du projet
     */
    public function getEvaluations($id) {
        try {
            $this->validateMethod('GET');

            // Vérifier si l'utilisateur a les droits
            if (!hasRole('judge') && !hasRole('admin')) {
                throw new Exception('Non autorisé');
            }

            $evaluations = $this->project->getEvaluations($id);

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

    /**
     * Télécharge le fichier ZIP d'un projet (sécurisé pour les juges)
     */
    public function downloadFile($id) {
        try {
            $this->validateMethod('GET');
            
            // Vérifier l'authentification
            $this->validateAuth();
            $currentUser = $this->getCurrentUser();
            
            // Vérifier que l'utilisateur a les permissions (admin ou juge)
            if (!in_array($currentUser['role'], ['admin', 'judge'])) {
                throw new Exception('Accès non autorisé - Permissions insuffisantes', 403);
            }
            
            // Récupérer le projet
            $project = $this->project->get($id);
            if (!$project) {
                throw new Exception('Projet non trouvé', 404);
            }
            
            // Vérifier que le fichier ZIP existe
            if (empty($project['zip_path']) || !file_exists($project['zip_path'])) {
                throw new Exception('Fichier ZIP non disponible', 404);
            }
            
            $filePath = $project['zip_path'];
            $fileName = $project['file_name'] ?: basename($filePath);
            
            // Vérifications de sécurité du fichier
            if (!is_readable($filePath)) {
                throw new Exception('Fichier non accessible', 500);
            }
            
            // Déterminer le type MIME
            $mimeType = 'application/zip';
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detectedMime = finfo_file($finfo, $filePath);
                if ($detectedMime && strpos($detectedMime, 'zip') !== false) {
                    $mimeType = $detectedMime;
                }
                finfo_close($finfo);
            }
            
            // Envoyer les headers pour le téléchargement
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . addslashes($fileName) . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Lire et envoyer le fichier par chunks pour les gros fichiers
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                throw new Exception('Impossible de lire le fichier', 500);
            }
            
            while (!feof($handle)) {
                echo fread($handle, 8192);
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            }
            
            fclose($handle);
            exit(); // Important : arrêter l'exécution après l'envoi du fichier
            
        } catch (Exception $e) {
            // En cas d'erreur, envoyer une réponse JSON si pas déjà en cours de téléchargement
            if (!headers_sent()) {
                header('Content-Type: application/json');
                $this->jsonResponse([
                    'success' => false,
                    'error' => $e->getMessage()
                ], $e->getCode() ?: 500);
            }
        }
    }
}
