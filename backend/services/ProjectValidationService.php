<?php

namespace Auth\Service;

use Auth\Service\Exception\ValidationException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;

/**
 * Service de validation des projets soumis
 */
class ProjectValidationService
{
    private array $rules;
    private array $errors = [];

    public function __construct(array $validationRules = [])
    {
        $this->rules = array_merge($this->getDefaultRules(), $validationRules);
    }

    private function getDefaultRules(): array
    {
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
    public function validateUploadedFile($fileData): void
    {
        // Vérifier les erreurs d'upload PHP
        if (!isset($fileData['error']) || is_array($fileData['error'])) {
            throw new ValidationException('Paramètres de fichier invalides');
        }

        // Gérer les erreurs d'upload PHP
        switch ($fileData['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new ValidationException('Le fichier dépasse la taille maximale autorisée');
            case UPLOAD_ERR_PARTIAL:
                throw new ValidationException('Le fichier n\'a été que partiellement téléchargé');
            case UPLOAD_ERR_NO_FILE:
                throw new ValidationException('Aucun fichier n\'a été téléchargé');
            case UPLOAD_ERR_NO_TMP_DIR:
                throw new ValidationException('Dossier temporaire manquant');
            case UPLOAD_ERR_CANT_WRITE:
                throw new ValidationException('Échec de l\'écriture du fichier sur le disque');
            case UPLOAD_ERR_EXTENSION:
                throw new ValidationException('Une extension PHP a arrêté le téléchargement du fichier');
            default:
                throw new ValidationException('Erreur inconnue lors du téléchargement du fichier');
        }

        // Vérifier que le fichier a bien été uploadé via HTTP POST
        if (!is_uploaded_file($fileData['tmp_name'])) {
            error_log('File upload validation failed - Not an uploaded file: ' . $fileData['tmp_name']);
            error_log('File info: ' . print_r($fileData, true));
            error_log('Upload temp dir: ' . ini_get('upload_tmp_dir'));
            error_log('TMP dir exists: ' . (is_dir(ini_get('upload_tmp_dir')) ? 'Yes' : 'No'));
            error_log('File exists: ' . (file_exists($fileData['tmp_name']) ? 'Yes' : 'No'));

            throw new ValidationException('Le fichier n\'a pas été correctement téléchargé');
        }

        // Vérifier la taille du fichier (50 Mo max)
        $maxSize = 50 * 1024 * 1024; // 50 Mo en octets
        if ($fileData['size'] > $maxSize) {
            throw new ValidationException('La taille du fichier ne doit pas dépasser 50 Mo');
        }

        // Vérifier l'extension
        $fileExtension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));
        if ($fileExtension !== 'zip') {
            throw new ValidationException('Le fichier doit être au format ZIP');
        }

        // Vérifier le type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            throw new ValidationException('Impossible d\'ouvrir l\'extension fileinfo');
        }

        $mimeType = finfo_file($finfo, $fileData['tmp_name']);
        finfo_close($finfo);

        $allowedMimeTypes = [
            'application/zip',
            'application/x-zip',
            'application/x-zip-compressed',
            'application/octet-stream',
            'application/x-compress',
            'application/x-compressed',
            'multipart/x-zip'
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new ValidationException('Le type de fichier n\'est pas autorisé. Type détecté: ' . $mimeType);
        }
    }

    /**
     * Valide la structure du projet extrait
     */
    public function validateProjectStructure(string $extractPath): array
    {
        $this->errors = [];
        $requiredFiles = $this->rules['required_files'];
        $missingFiles = [];

        // Détection automatique de la structure du projet
        $projectRoot = $this->detectProjectRoot($extractPath);

        // Vérification des fichiers requis
        foreach ($requiredFiles as $file) {
            $filePath = rtrim($projectRoot, '/') . '/' . ltrim($file, '/');

            if (!file_exists($filePath)) {
                $missingFiles[] = $file;
            }
        }

        if (!empty($missingFiles)) {
            $this->errors[] = sprintf(
                'Fichiers/dossiers requis manquants dans le zip : %s',
                implode(', ', $missingFiles)
            );
        }

        // Vérification des fichiers interdits dans le dossier racine détecté
        $this->scanForBannedFiles($projectRoot);

        return $this->errors;
    }

    /**
     * Détecte automatiquement le dossier racine du projet
     */
    private function detectProjectRoot(string $basePath): string
    {
        $items = array_diff(scandir($basePath), ['.', '..']);

        // Si un seul dossier est présent, on l'utilise comme racine
        if (count($items) === 1) {
            $potentialRoot = $basePath . '/' . reset($items);
            if (is_dir($potentialRoot)) {
                return $potentialRoot;
            }
        }

        // Sinon on utilise le dossier d'extraction comme racine
        return $basePath;
    }

    /**
     * Affiche la structure du dossier pour le débogage
     */
    private function debugDirectoryStructure(string $path, int $depth = 0): void
    {
        $items = array_diff(scandir($path), ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $path . '/' . $item;
            error_log(str_repeat('    ', $depth) . $item);

            if (is_dir($itemPath)) {
                error_log("/");
            }

            error_log("\n");

            // Limite la profondeur de récursion pour éviter les boucles infinies
            if (is_dir($itemPath) && $depth < 3) {
                $this->debugDirectoryStructure($itemPath, $depth + 1);
            }
        }
    }

    /**
     * Vérifie la présence de fichiers interdits
     */
    private function scanForBannedFiles(string $directory): void
    {
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
    public function validateRepositoryUrl(string $url): bool
    {
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
    public function validateMetadata(array $data): array
    {
        $this->errors = [];
        
        // Nettoyer les valeurs en gérant les cas null
        $data = array_map(function($value) {
            return $value === null ? '' : trim((string)$value);
        }, $data);

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
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Retourne les erreurs de validation
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Valide l'ensemble du projet
     */
    public function validateProject(array $file, array $metadata): bool
    {
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
    private function extractProject(string $zipPath): string
    {
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
}
