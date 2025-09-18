<?php

namespace Auth\Controller;



if (!defined('CONFIG_INCLUDED')) {
    require_once __DIR__ . '/../includes/config.php';
}
if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}
if (!class_exists('Project')) {
    require_once __DIR__ . '/../models/Project.php';
}
if (!class_exists('Controller')) {
    require_once __DIR__ . '/Controller.php';
}
if (!class_exists('Team')) {
    require_once __DIR__ . '/../models/Team.php';
}
if (!class_exists('Challenge')) {
    require_once __DIR__ . '/../models/Challenge.php';
}
if (!class_exists('Hackathon')) {
    require_once __DIR__ . '/../models/Hackathon.php';
}
if (!class_exists('ProjectValidationService')) {
    require_once __DIR__ . '/../services/ProjectValidationService.php';
}
if (!class_exists('AdminController')) {
    require_once __DIR__ . '/AdminController.php';
}
if (!class_exists('TokenManager')) {
    require_once __DIR__ . '/../models/TokenManager.php';
}
if (!class_exists('ValidationException')) {
    require_once __DIR__ . '/../services/exceptions/ValidationException.php';
}
if (!class_exists('NotificationController')) {
    require_once __DIR__ . '/NotificationController.php';
}

use Exception;
use PDO;
use Auth\Model\Project;
use Auth\Model\Team;
use Auth\Model\Challenge;
use Auth\Model\Hackathon;
use Auth\Service\ProjectValidationService;
use Auth\Service\Exception\ValidationException;
use Auth\Controller\AdminController;
use Auth\Model\TokenManager;
use Auth\Controller\NotificationController;

class ProjectController extends Controller
{
    private $project;
    private $team;
    private $challenge;
    private $hackathon;
    private $validationService;
    protected $tokenManager;
    protected $db;
    protected $admin;
    protected $notification;

    public function __construct($db, $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->project = new Project($db);
        $this->team = new Team($db);
        $this->challenge = new Challenge($db);
        $this->hackathon = new Hackathon($db);
        $this->validationService = new ProjectValidationService();
        $this->tokenManager = new TokenManager($db);
        $this->db = $db;
        $this->admin = new AdminController($db, $tokenManager);
        $this->notification = new NotificationController($db, $tokenManager);
    }

    /**
     * Soumet un nouveau projet
     */
    public function submit($data)
    {
        try {
            $this->validateMethod('POST');

            // Récupérer l'utilisateur connecté
            $userId = $this->tokenManager->getCurrentUserId();
            $isAdmin = isAdmin($userId);
            if (!$userId) {
                throw new Exception('Utilisateur non connecté', 401);
            }

            // Valider les données requises
            $requiredFields = ['name', 'description', 'hackathon_id', 'challenge_id'];
            $this->validateRequiredFields($data, $requiredFields);

            // Vérifier si le hackathon existe et est actif
            $hackathon = $this->hackathon->find($data['hackathon_id']);
            if (!$hackathon) {
                throw new Exception('Hackathon non trouvé', 404);
            }

            // Vérifier si le challenge existe et est actif
            $challenge = $this->challenge->find($data['challenge_id'], $userId);
            if (!$challenge) {
                throw new Exception('Challenge non trouvé ou inactif', 404);
            }

            // Passer les criteres d'evaluation
            $data['evaluation_criteria'] = $challenge['evaluation_criteria'];

            // Vérifier si l'utilisateur fait partie d'une équipe pour ce hackathon
            $team = $this->team->getByUser($userId);
            $team = $team[0];
            $isRegistredToHackathon = $this->team->getByHackathon($team['id']);
            if ((!$team || !$isRegistredToHackathon) && !$isAdmin ) {
                throw new Exception('Vous devez faire partie d\'une équipe pour ce hackathon', 403);
            }

            // Vérifier si l'équipe a déjà soumis un projet pour ce challenge
            if ($this->project->hasTeamSubmittedForChallenge($team['id'], $data['challenge_id'])) {
                throw new Exception('Votre équipe a déjà soumis un projet pour ce challenge', 400);
            }

            // Valider le fichier uploadé s'il y en a un
            $fileData = null;
            $hasFileUpload = isset($_FILES['zip_file']) && $_FILES['zip_file']['error'] !== UPLOAD_ERR_NO_FILE;
            $hasRepositoryUrl = !empty($data['repository_url']);
            
            // Vérifier qu'au moins un des deux est fourni
            if (!$hasFileUpload && !$hasRepositoryUrl) {
                throw new ValidationException('Vous devez fournir soit un fichier ZIP, soit une URL de dépôt GitHub');
            }

            // Traitement du fichier ZIP s'il est fourni
            if ($hasFileUpload) {
                // Valider le fichier avec le service de validation AVANT de le déplacer
                $this->validationService->validateUploadedFile($_FILES['zip_file']);
                
                // Extraire et valider la structure du projet
                $extractPath = $this->extractProject($_FILES['zip_file']['tmp_name']);
                $structureErrors = $this->validationService->validateProjectStructure($extractPath);

                // Nettoyer le dossier temporaire
                $this->removeDirectory($extractPath);

                if (!empty($structureErrors)) {
                    throw new ValidationException('La structure du projet n\'est pas valide', $structureErrors);
                }

                // Maintenant que tout est validé, on peut déplacer le fichier
                $fileData = $this->handleFileUpload($_FILES['zip_file']);
            }

            // Valider les métadonnées
            $metadata = [
                'name' => $data['name'],
                'description' => $data['description'],
                'repository_url' => $data['repository_url'] ?? null,
                'demo_url' => $data['demo_url'] ?? null,
                'documentation_url' => $data['documentation_url'] ?? null
            ];

            $validationErrors = $this->validationService->validateMetadata($metadata);
            if (!empty($validationErrors)) {
                throw new ValidationException('Validation des métadonnées échouée', $validationErrors);
            }

            // Préparer les données du projet
            $projectData = [
                'team_id' => $team['id'],
                'hackathon_id' => $data['hackathon_id'],
                'challenge_id' => $data['challenge_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'repository_url' => $data['repository_url'] ?? null,
                'demo_url' => $data['demo_url'] ?? null,
                'documentation_url' => $data['documentation_url'] ?? null,
                'additional_notes' => $data['additional_notes'] ?? null,
                'evaluation_criteria' => $data['evaluation_criteria'] ?? null,
                'status' => 'ongoing',
                'created_by' => $userId,
                'rule_compliance' => empty($structureErrors)
            ];

            // Ajouter les informations du fichier si présent
            if ($fileData) {
                $projectData['file_path'] = $fileData['file_path'];
                $projectData['file_name'] = $fileData['file_name'];
            }

            // Créer le projet
            $projectId = $this->project->create($projectData);

            // Journaliser l'action
            $this->tokenManager->logSecurityEvent(
                $userId,
                'project_submitted',
                [
                    'message' => 'Nouveau projet soumis',
                    'project_id' => $projectId,
                    'team_id' => $team['id'],
                    'challenge_id' => $data['challenge_id']
                ]
            );

            // Créer une notification de soumission
            $this->notification->create(
                [
                    'scope' => 'team',
                    'user_id' => $userId,
                    'title' => 'Soumission de projet',
                    'message' => 'Projet soumis par votre équipe',
                    'project_id' => $projectId,
                    'team_id' => $team['id'],
                    'challenge_id' => $data['challenge_id'],
                    'type' => 'success'
                ]
            );

            // Réponse de succès
            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet soumis avec succès',
                'data' => [
                    'project_id' => $projectId,
                    'team_id' => $team['id']
                ]
            ]);
        } catch (ValidationException $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'validation_errors' => $e->getErrors()
            ], 400);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Gère l'upload d'un fichier
     */
    private function handleFileUpload(array $file): array
    {
        $uploadDir = __DIR__ . '/../../storage/project_submissions/' . date('Y-m-d');

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            throw new Exception('Impossible de créer le répertoire de destination');
        }

        // Générer un nom de fichier unique
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('proj_') . '.' . $fileExtension;
        $filePath = $uploadDir . '/' . $fileName;

        // Déplacer le fichier uploadé
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception('Échec du téléchargement du fichier');
        }

        return [
            'file_name' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $file['type']
        ];
    }

    /**
     * Extrait un projet ZIP dans un dossier temporaire
     */
    private function extractProject(string $zipPath): string
    {
        $extractPath = sys_get_temp_dir() . '/project_' . uniqid();

        if (!is_dir($extractPath) && !mkdir($extractPath, 0755, true)) {
            throw new Exception('Impossible de créer le répertoire d\'extraction');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Impossible d\'ouvrir l\'archive ZIP');
        }

        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            throw new Exception('Erreur lors de l\'extraction de l\'archive');
        }

        $zip->close();
        return $extractPath;
    }

    /**
     * Supprime récursivement un dossier
     */
    private function removeDirectory(string $directory): bool
    {
        if (!file_exists($directory)) {
            return true;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        return rmdir($directory);
    }

    /**
     * Récupère un projet par son ID
     */
    public function get($id)
    {
        try {
            $project = $this->project->find($id);

            if (!$project) {
                throw new Exception('Projet non trouvé', 404);
            }

            $this->jsonResponse([
                'success' => true,
                'data' => $project
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Récupère les projets d'une équipe
     */
    public function getByTeam($teamId)
    {
        try {
            $projects = $this->project->getByTeam($teamId);

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les projets d'un hackathon
     */
    public function getByHackathon($hackathonId)
    {
        try {
            $projects = $this->project->getByHackathon($hackathonId);

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un projet existant
     */
    public function update($id, $data)
    {
        try {
            $this->validateMethod('PUT');

            // Récupérer l'utilisateur connecté
            $userId = $this->tokenManager->getCurrentUserId();
            if (!$userId) {
                throw new Exception('Utilisateur non connecté', 401);
            }

            // Vérifier si le projet existe
            $project = $this->project->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé', 404);
            }

            // Vérifier les permissions (seul le créateur ou un admin peut modifier)
            if ($project['created_by'] != $userId && !$this->admin->isAdmin($userId)) {
                throw new Exception('Non autorisé', 403);
            }

            // Mettre à jour uniquement les champs autorisés
            $allowedFields = ['name', 'description', 'repository_url', 'status'];
            $updateData = array_intersect_key($data, array_flip($allowedFields));

            // Mettre à jour le projet
            $success = $this->project->update($id, $updateData);

            if (!$success) {
                throw new Exception('Échec de la mise à jour du projet');
            }

            // Journaliser l'action
            $this->tokenManager->logSecurityEvent(
                $userId,
                'project_updated',
                [
                    'message' => 'Projet mis à jour',
                    'project_id' => $id
                ]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet mis à jour avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Supprime un projet
     */
    public function delete($id)
    {
        try {
            $this->validateMethod('DELETE');

            // Récupérer l'utilisateur connecté
            $userId = $this->tokenManager->getCurrentUserId();
            if (!$userId) {
                throw new Exception('Utilisateur non connecté', 401);
            }

            // Vérifier si le projet existe
            $project = $this->project->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé', 404);
            }

            // Vérifier les permissions (seul un admin peut supprimer)
            if (!$this->admin->isAdmin($userId)) {
                throw new Exception('Non autorisé', 403);
            }

            // Supprimer le projet
            $success = $this->project->delete($id);

            if (!$success) {
                throw new Exception('Échec de la suppression du projet');
            }

            // Journaliser l'action
            $this->tokenManager->logSecurityEvent(
                $userId,
                'project_deleted',
                [
                    'message'=>'Projet supprimé',
                    'project_id' => $id
                ]
            );

            $this->jsonResponse([
                'success' => true,
                'message' => 'Projet supprimé avec succès'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Télécharge le fichier d'un projet
     * 
     * @param int $id ID du projet
     * @throws Exception Si le projet ou le fichier n'existe pas
     */
    public function download($id)
    {
        try {
            $this->validateMethod('GET');
            
            // Récupérer les détails du projet
            $project = $this->project->find($id);
            if (!$project) {
                throw new Exception('Projet non trouvé', 404);
            }

            // Vérifier si un fichier est associé
            if (empty($project['file_path']) || !file_exists($project['file_path'])) {
                throw new Exception('Aucun fichier trouvé pour ce projet', 404);
            }

            // Vérifier que l'utilisateur a le droit de télécharger le fichier
            $userId = $this->tokenManager->getCurrentUserId();
            if (!$this->hasProjectAccess($userId, $id) && !$this->admin->isAdmin($userId)) {
                throw new Exception('Accès non autorisé', 403);
            }

            // Envoyer le fichier
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($project['file_name']).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($project['file_path']));
            readfile($project['file_path']);
            exit;

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Récupère tous les projets avec filtres
     * 
     * @param array $filters Filtres de recherche
     * @return void
     */
    public function getAll($filters = [])
    {
        try {
            $this->validateMethod('GET');
            
            // Valider et nettoyer les filtres
            $validFilters = [];
            
            if (isset($filters['hackathon_id'])) {
                $validFilters['hackathon_id'] = (int)$filters['hackathon_id'];
            }
            
            if (isset($filters['team_id'])) {
                $validFilters['team_id'] = (int)$filters['team_id'];
            }
            
            if (isset($filters['challenge_id'])) {
                $validFilters['challenge_id'] = (int)$filters['challenge_id'];
            }
            
            if (isset($filters['status'])) {
                $validFilters['status'] = filter_var($filters['status'], FILTER_SANITIZE_STRING);
            }

            // Si l'utilisateur n'est pas admin, on filtre par ses projets
            $userId = $this->tokenManager->getCurrentUserId();
            if (!$this->admin->isAdmin($userId)) {
                $validFilters['user_id'] = $userId;
            }

            // Récupérer les projets
            $projects = $this->project->getAll($validFilters);

            $this->jsonResponse([
                'success' => true,
                'data' => $projects
            ]);

        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Vérifie si un utilisateur a accès à un projet
     * 
     * @param int $userId ID de l'utilisateur
     * @param int $projectId ID du projet
     * @return bool
     */
    private function hasProjectAccess($userId, $projectId)
    {
        // Vérifier si l'utilisateur est membre de l'équipe du projet
        $project = $this->project->find($projectId);
        if (!$project || !isset($project['team_id'])) {
            return false;
        }

        return $this->team->isMember($project['team_id'], $userId);
    }
}
