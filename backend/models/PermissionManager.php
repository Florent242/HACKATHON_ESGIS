<?php
namespace Auth\Model;

use PDO;
use Exception;

/**
 * Gestionnaire de permissions
 * Gère les vérifications de permissions basées sur les actions spécifiques
 */
class PermissionManager {
    private $db;
    private $userPermissions = null;
    private $userId = null;
    private $userRole = null;
    private $permissionsCache = [];

    /**
     * Constructeur
     * @param PDO $db Instance de connexion à la base de données
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Définir l'utilisateur courant
     * @param int $userId ID de l'utilisateur
     * @param string $userRole Rôle de l'utilisateur
     * @return PermissionManager Instance pour chaînage
     */
    public function setUser($userId, $userRole) {
        $this->userId = $userId;
        $this->userRole = $userRole;
        $this->userPermissions = null; // Réinitialiser le cache
        $this->permissionsCache = []; // Réinitialiser le cache de vérifications
        return $this;
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     * @param string $permissionSlug Identifiant de la permission (slug)
     * @param int|null $resourceOwnerId ID du propriétaire de la ressource (pour les vérifications propriétaire)
     * @return bool True si l'utilisateur a la permission, sinon False
     */
    public function hasPermission($permissionSlug, $resourceOwnerId = null) {
        // Si l'utilisateur n'est pas défini, retourner false
        if (!$this->userId || !$this->userRole) {
            return false;
        }

        // Vérifier si nous avons déjà vérifié cette permission
        $cacheKey = $permissionSlug . ($resourceOwnerId ? '_' . $resourceOwnerId : '');
        if (isset($this->permissionsCache[$cacheKey])) {
            return $this->permissionsCache[$cacheKey];
        }

        // Vérifier les permissions spécifiques au propriétaire si l'utilisateur est le propriétaire
        if ($resourceOwnerId !== null && $resourceOwnerId == $this->userId) {
            $ownerPermissionSlug = $this->getOwnerVersionOfPermission($permissionSlug);
            if ($ownerPermissionSlug && $this->checkPermission($ownerPermissionSlug)) {
                $this->permissionsCache[$cacheKey] = true;
                return true;
            }
        }

        // Vérifier la permission normale
        $hasPermission = $this->checkPermission($permissionSlug);
        $this->permissionsCache[$cacheKey] = $hasPermission;
        return $hasPermission;
    }

    /**
     * Obtenir la version "propriétaire" d'une permission
     * @param string $permissionSlug Identifiant de la permission
     * @return string|null Version propriétaire de la permission ou null si non applicable
     */
    private function getOwnerVersionOfPermission($permissionSlug) {
        $ownSlugs = [
            'teams.update' => 'teams.update.own',
            'projects.update' => 'projects.update.own',
            'evaluations.update' => 'evaluations.update.own'
        ];

        return isset($ownSlugs[$permissionSlug]) ? $ownSlugs[$permissionSlug] : null;
    }

    /**
     * Vérifier une permission spécifique
     * @param string $permissionSlug Identifiant de la permission
     * @return bool True si l'utilisateur a la permission, sinon False
     */
    private function checkPermission($permissionSlug) {
        // Récupérer les permissions de l'utilisateur si ce n'est pas déjà fait
        if ($this->userPermissions === null) {
            $this->loadUserPermissions();
        }

        // Vérifier si la permission est dans les permissions de l'utilisateur
        return in_array($permissionSlug, $this->userPermissions);
    }

    /**
     * Charger les permissions de l'utilisateur courant
     */
    private function loadUserPermissions() {
        try {
            $this->userPermissions = [];

            // 1. Récupérer les permissions basées sur le rôle
            $rolePermissions = $this->loadRolePermissions($this->userRole);
            $this->userPermissions = array_merge($this->userPermissions, $rolePermissions);

            // 2. Appliquer les exceptions spécifiques à l'utilisateur
            $this->applyUserSpecificPermissions();

        } catch (Exception $e) {
            error_log('Erreur lors du chargement des permissions: ' . $e->getMessage());
            // En cas d'erreur, on considère que l'utilisateur n'a aucune permission
            $this->userPermissions = [];
        }
    }

    /**
     * Charger les permissions associées à un rôle
     * @param string $role Rôle de l'utilisateur
     * @return array Liste des slugs de permissions
     */
    private function loadRolePermissions($role) {
        $query = "SELECT p.slug
                 FROM permissions p
                 JOIN role_permissions rp ON p.id = rp.permission_id
                 WHERE rp.role = :role";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();

        $permissions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[] = $row['slug'];
        }

        return $permissions;
    }

    /**
     * Appliquer les exceptions de permissions spécifiques à l'utilisateur
     */
    private function applyUserSpecificPermissions() {
        $query = "SELECT p.slug, up.granted
                 FROM permissions p
                 JOIN user_permissions up ON p.id = up.permission_id
                 WHERE up.user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $this->userId);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['granted'] == 1) {
                // Ajouter la permission si elle n'existe pas déjà
                if (!in_array($row['slug'], $this->userPermissions)) {
                    $this->userPermissions[] = $row['slug'];
                }
            } else {
                // Retirer la permission si elle existe
                $key = array_search($row['slug'], $this->userPermissions);
                if ($key !== false) {
                    unset($this->userPermissions[$key]);
                }
            }
        }
    }

    /**
     * Obtenir toutes les permissions disponibles
     * @param bool $groupByCategory Si true, regroupe les permissions par catégorie
     * @return array Liste des permissions
     */
    public function getAllPermissions($groupByCategory = false) {
        $query = "SELECT id, name, slug, description, category FROM permissions ORDER BY category, name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($groupByCategory) {
            $grouped = [];
            foreach ($permissions as $permission) {
                $category = $permission['category'] ?? 'Other';
                if (!isset($grouped[$category])) {
                    $grouped[$category] = [];
                }
                $grouped[$category][] = $permission;
            }
            return $grouped;
        }

        return $permissions;
    }

    /**
     * Obtenir les permissions d'un rôle
     * @param string $role Nom du rôle
     * @return array Liste des slugs de permissions
     */
    public function getRolePermissions($role) {
        return $this->loadRolePermissions($role);
    }

    /**
     * Obtenir les permissions d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array Liste des slugs de permissions
     */
    public function getUserPermissions($userId) {
        $user = $this->getUser($userId);
        if (!$user) {
            return [];
        }

        $tempUserId = $this->userId;
        $tempUserRole = $this->userRole;

        $this->setUser($userId, $user['role']);
        $this->loadUserPermissions();
        $permissions = $this->userPermissions;

        // Restaurer les valeurs précédentes
        $this->setUser($tempUserId, $tempUserRole);

        return $permissions;
    }

    /**
     * Obtenir les informations d'un utilisateur
     * @param int $userId ID de l'utilisateur
     * @return array|bool Données de l'utilisateur ou false si non trouvé
     */
    private function getUser($userId) {
        $query = "SELECT id, role FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Ajouter une permission spécifique à un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param string $permissionSlug Slug de la permission
     * @return bool True si succès, sinon False
     */
    public function grantPermissionToUser($userId, $permissionSlug) {
        return $this->setUserPermission($userId, $permissionSlug, true);
    }

    /**
     * Retirer une permission spécifique à un utilisateur
     * @param int $userId ID de l'utilisateur
     * @param string $permissionSlug Slug de la permission
     * @return bool True si succès, sinon False
     */
    public function revokePermissionFromUser($userId, $permissionSlug) {
        return $this->setUserPermission($userId, $permissionSlug, false);
    }

    /**
     * Définir une permission utilisateur (accorder ou révoquer)
     * @param int $userId ID de l'utilisateur
     * @param string $permissionSlug Slug de la permission
     * @param bool $granted True pour accorder, False pour révoquer
     * @return bool True si succès, sinon False
     */
    private function setUserPermission($userId, $permissionSlug, $granted) {
        try {
            // Récupérer l'ID de la permission
            $query = "SELECT id FROM permissions WHERE slug = :slug LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':slug', $permissionSlug);
            $stmt->execute();

            $permissionId = $stmt->fetch(PDO::FETCH_COLUMN);
            if (!$permissionId) {
                return false;
            }

            // Vérifier si l'entrée existe déjà
            $query = "SELECT id FROM user_permissions WHERE user_id = :user_id AND permission_id = :permission_id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':permission_id', $permissionId);
            $stmt->execute();

            $existingId = $stmt->fetch(PDO::FETCH_COLUMN);

            if ($existingId) {
                // Mise à jour de l'existant
                $query = "UPDATE user_permissions SET granted = :granted WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':granted', $granted, PDO::PARAM_BOOL);
                $stmt->bindParam(':id', $existingId);
            } else {
                // Création d'une nouvelle entrée
                $query = "INSERT INTO user_permissions (user_id, permission_id, granted) VALUES (:user_id, :permission_id, :granted)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':user_id', $userId);
                $stmt->bindParam(':permission_id', $permissionId);
                $stmt->bindParam(':granted', $granted, PDO::PARAM_BOOL);
            }

            return $stmt->execute();
        } catch (Exception $e) {
            error_log('Erreur lors de la définition de la permission utilisateur: ' . $e->getMessage());
            return false;
        }
    }
}
