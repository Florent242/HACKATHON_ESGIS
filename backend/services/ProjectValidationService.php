<?php
namespace Auth\Service;

use Auth\Service\Exception\ValidationException;

/**
 * Service de validation des projets soumis
 */
class ProjectValidationService {
    private array $rules;
    private array $errors = [];

    public function __construct(array $validationRules = []) {
        $this->rules = array_merge($this->getDefaultRules(), $validationRules);
    }

    private function getDefaultRules(): array {
        return [
            'required_files' => ['README.md', 'src/', 'docs/'],
            'max_size' => 50 * 1024 * 1024, // 50MB
            'allowed_extensions' => ['php', 'js', 'html', 'css', 'json', 'md'],
            'banned_extensions' => ['exe', 'dll', 'bat', 'sh', 'py'],
            'max_file_count' => 1000,
            'min_description_length' => 50,
            'max_description_length' => 2000,
            'allowed_mime_types' => [
                'application/zip',
                'application/x-zip',
                'application/x-zip-compressed'
            ]
        ];
    }

    /**
     * Valide un fichier uploadé
     */
    public function validateUploadedFile(array $file): void {
        $this->errors = [];

        // Vérification des erreurs d'upload
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new ValidationException('Paramètres de fichier invalides');
        }

        // Vérification des erreurs PHP
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new ValidationException('Le fichier dépasse la taille maximale autorisée');
            case UPLOAD_ERR_PARTIAL:
                throw new ValidationException('Le fichier n\'a été que partiellement téléchargé');
            case UPLOAD_ERR_NO_FILE:
                throw new ValidationException('Aucun fichier n\'a été téléchargé');
            default:
                throw new ValidationException('Erreur inconnue lors du téléchargement du fichier');
        }

        // Vérification du type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->rules['allowed_mime_types'], true)) {
            throw new ValidationException(
                'Format de fichier non supporté. Veuillez télécharger une archive ZIP valide.'
            );
        }

        // Vérification de la taille
        if ($file['size'] > $this->rules['max_size']) {
            $maxSizeMB = round($this->rules['max_size'] / (1024 * 1024), 2);
            throw new ValidationException(
                sprintf('La taille du fichier dépasse la limite de %s Mo', $maxSizeMB)
            );
        }
    }

    /**
     * Valide la structure du projet extrait
     */
    public function validateProjectStructure(string $extractPath): array {
        $this->errors = [];
        $requiredFiles = $this->rules['required_files'];
        $missingFiles = [];

        foreach ($requiredFiles as $file) {
            $filePath = rtrim($extractPath, '/') . '/' . ltrim($file, '/');
            if (!file_exists($filePath)) {
                $missingFiles[] = $file;
            }
        }

        if (!empty($missingFiles)) {
            $this->errors[] = sprintf(
                'Fichiers/dossiers requis manquants : %s',
                implode(', ', $missingFiles)
            );
        }

        // Vérification des fichiers interdits
        $this->scanForBannedFiles($extractPath);

        return $this->errors;
    }

    /**
     * Vérifie la présence de fichiers interdits
     */
    private function scanForBannedFiles(string $directory): void {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $fileCount = 0;
        $bannedFiles = [];

        foreach ($iterator as $file) {
            $fileCount++;
            
            // Vérification du nombre maximal de fichiers
            if ($fileCount > $this->rules['max_file_count']) {
                $this->errors[] = sprintf(
                    'Le projet contient trop de fichiers (max %d autorisés)',
                    $this->rules['max_file_count']
                );
                break;
            }

            // Vérification des extensions interdites
            if ($file->isFile()) {
                $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                if (in_array($extension, $this->rules['banned_extensions'], true)) {
                    $bannedFiles[] = $file->getFilename();
                }
            }
        }

        if (!empty($bannedFiles)) {
            $this->errors[] = sprintf(
                'Fichiers interdits détectés : %s',
                implode(', ', array_unique($bannedFiles))
            );
        }
    }

    /**
     * Valide l'URL d'un dépôt GitHub
     */
    public function validateRepositoryUrl(string $url): bool {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new ValidationException('URL du dépôt invalide');
        }

        $parsedUrl = parse_url($url);
        
        // Vérification du domaine GitHub
        if (!isset($parsedUrl['host']) || !in_array($parsedUrl['host'], ['github.com', 'www.github.com'], true)) {
            throw new ValidationException('Seuls les dépôts GitHub sont acceptés');
        }

        // Vérification du format de l'URL du dépôt
        if (!preg_match('#^/[^/]+/[^/]+$#', $parsedUrl['path'] ?? '')) {
            throw new ValidationException('Format d\'URL de dépôt GitHub invalide. Format attendu : https://github.com/username/repository');
        }

        return true;
    }

    /**
     * Valide les métadonnées du projet
     */
    public function validateMetadata(array $data): array {
        $this->errors = [];
        $data = array_map('trim', $data);

        // Validation du nom
        if (empty($data['name'] ?? '')) {
            $this->errors[] = 'Le nom du projet est requis';
        } elseif (mb_strlen($data['name']) > 100) {
            $this->errors[] = 'Le nom du projet ne doit pas dépasser 100 caractères';
        }

        // Validation de la description
        $description = $data['description'] ?? '';
        if (mb_strlen($description) < $this->rules['min_description_length']) {
            $this->errors[] = sprintf(
                'La description doit faire au moins %d caractères',
                $this->rules['min_description_length']
            );
        } elseif (mb_strlen($description) > $this->rules['max_description_length']) {
            $this->errors[] = sprintf(
                'La description ne doit pas dépasser %d caractères',
                $this->rules['max_description_length']
            );
        }

        // Validation de l'URL du dépôt (optionnelle)
        if (!empty($data['repository_url'])) {
            try {
                $this->validateRepositoryUrl($data['repository_url']);
            } catch (ValidationException $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        return $this->errors;
    }

    /**
     * Vérifie si le projet est valide
     */
    public function isValid(): bool {
        return empty($this->errors);
    }

    /**
     * Retourne les erreurs de validation
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Valide l'ensemble du projet
     */
    public function validateProject(array $file, array $metadata): bool {
        try {
            // Valider le fichier uploadé
            $this->validateUploadedFile($file);
            
            // Extraire et valider la structure
            $extractPath = $this->extractProject($file['tmp_name']);
            $this->validateProjectStructure($extractPath);
            
            // Nettoyer le dossier temporaire
            $this->removeDirectory($extractPath);
            
            // Valider les métadonnées
            $this->validateMetadata($metadata);
            
            return $this->isValid();
            
        } catch (ValidationException $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }
    
    /**
     * Extrait le projet dans un dossier temporaire
     */
    private function extractProject(string $zipPath): string {
        $extractPath = sys_get_temp_dir() . '/project_' . uniqid();
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new ValidationException('Impossible d\'ouvrir l\'archive ZIP');
        }
        
        if (!$zip->extractTo($extractPath)) {
            $zip->close();
            throw new ValidationException('Erreur lors de l\'extraction de l\'archive');
        }
        
        $zip->close();
        return $extractPath;
    }
    
    /**
     * Supprime un dossier et son contenu
     */
    private function removeDirectory(string $directory): bool {
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
}