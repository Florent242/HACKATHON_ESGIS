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

            // Vérifier si l'utilisateur fait partie d'une équipe pour ce hackathon
            $team = $this->team->getByUser($userId);
            $isRegistredToHackathon = $this->team->getByHackathon($team['hackathon_id']);
            if (!$team || !$isRegistredToHackathon) {
                throw new Exception('Vous devez faire partie d\'une équipe pour ce hackathon', 403);
            }

            // Vérifier si l'équipe a déjà soumis un projet pour ce challenge
            if ($this->project->hasTeamSubmittedForChallenge($team['id'], $data['challenge_id'])) {
                throw new Exception('Votre équipe a déjà soumis un projet pour ce challenge', 400);
            }

            // Valider le fichier uploadé s'il y en a un
            $fileData = null;
            if (isset($_FILES['project_file'])) {
                $fileData = $this->handleFileUpload($_FILES['project_file']);

                // Valider le fichier avec le service de validation
                $this->validationService->validateUploadedFile($_FILES['project_file']);

                // Extraire et valider la structure du projet
                $extractPath = $this->extractProject($_FILES['project_file']['tmp_name']);
                $structureErrors = $this->validationService->validateProjectStructure($extractPath);

                // Nettoyer le dossier temporaire
                $this->removeDirectory($extractPath);

                if (!empty($structureErrors)) {
                    throw new ValidationException('La structure du projet n\'est pas valide', $structureErrors);
                }
            } elseif (empty($data['repository_url'])) {
                throw new ValidationException('Vous devez fournir soit un fichier ZIP, soit une URL de dépôt GitHub');
            }

            // Valider les métadonnées
            $metadata = [
                'name' => $data['name'],
                'description' => $data['description'],
                'repository_url' => $data['repository_url'] ?? null
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
                'status' => 'pending',
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
        $uploadDir = __DIR__ . '/../../storage/project_submissions/' . date('Y/m/d');

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
}
