<?php

namespace Auth\Model;

use PDO;
use Exception;

class ActivityLog
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Enregistre une activité dans les logs
     */
    public function log($userId, $action, $description, $data = null, $level = 'info')
    {
        try {
            $sql = "INSERT INTO activity_logs 
                    (user_id, action, description, data, ip_address, user_agent, level) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $userId,
                $action,
                $description,
                is_array($data) ? json_encode($data) : $data,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $level
            ]);

            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Erreur lors de l'enregistrement du log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les logs avec filtres et pagination
     */
    public function getAll($filters = [])
    {
        try {
            $sql = "SELECT al.*, u.username, u.fullname, u.profile_picture
                    FROM activity_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    WHERE 1=1";
            
            $params = [];

            // Filtre par action
            if (!empty($filters['action'])) {
                $sql .= " AND al.action = ?";
                $params[] = $filters['action'];
            }

            // Filtre par niveau
            if (!empty($filters['level'])) {
                $sql .= " AND al.level = ?";
                $params[] = $filters['level'];
            }

            // Filtre par utilisateur
            if (!empty($filters['user_id'])) {
                $sql .= " AND al.user_id = ?";
                $params[] = $filters['user_id'];
            }

            // Filtre par période
            if (!empty($filters['date_from'])) {
                $sql .= " AND al.created_at >= ?";
                $params[] = $filters['date_from'];
            }

            if (!empty($filters['date_to'])) {
                $sql .= " AND al.created_at <= ?";
                $params[] = $filters['date_to'];
            }

            // Recherche
            if (!empty($filters['search'])) {
                $sql .= " AND (al.action LIKE ? OR al.description LIKE ? OR u.username LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $sql .= " ORDER BY al.created_at DESC";

            // Pagination
            if (isset($filters['limit'])) {
                $sql .= " LIMIT ?";
                $params[] = (int)$filters['limit'];
                
                if (isset($filters['offset'])) {
                    $sql .= " OFFSET ?";
                    $params[] = (int)$filters['offset'];
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des logs: " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre total de logs
     */
    public function count($filters = [])
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM activity_logs al
                    LEFT JOIN users u ON al.user_id = u.id
                    WHERE 1=1";
            
            $params = [];

            if (!empty($filters['action'])) {
                $sql .= " AND al.action = ?";
                $params[] = $filters['action'];
            }

            if (!empty($filters['level'])) {
                $sql .= " AND al.level = ?";
                $params[] = $filters['level'];
            }

            if (!empty($filters['user_id'])) {
                $sql .= " AND al.user_id = ?";
                $params[] = $filters['user_id'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (al.action LIKE ? OR al.description LIKE ? OR u.username LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (Exception $e) {
            throw new Exception("Erreur lors du comptage des logs: " . $e->getMessage());
        }
    }

    /**
     * Récupère les statistiques des logs
     */
    public function getStats()
    {
        try {
            $sql = "SELECT 
                    COUNT(*) as total_logs,
                    SUM(CASE WHEN action LIKE '%login%' THEN 1 ELSE 0 END) as connections,
                    SUM(CASE WHEN action LIKE '%team%' THEN 1 ELSE 0 END) as team_actions,
                    SUM(CASE WHEN action LIKE '%challenge%' OR action LIKE '%flag%' THEN 1 ELSE 0 END) as challenges,
                    SUM(CASE WHEN level = 'error' THEN 1 ELSE 0 END) as errors,
                    SUM(CASE WHEN level = 'warning' THEN 1 ELSE 0 END) as warnings
                    FROM activity_logs";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des statistiques: " . $e->getMessage());
        }
    }

    /**
     * Récupère les actions distinctes
     */
    public function getDistinctActions()
    {
        try {
            $sql = "SELECT DISTINCT action FROM activity_logs ORDER BY action";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des actions: " . $e->getMessage());
        }
    }

    /**
     * Exporte les logs en CSV
     */
    public function exportToCSV($filters = [])
    {
        $logs = $this->getAll($filters);
        
        $output = fopen('php://temp', 'w');
        
        // En-têtes
        fputcsv($output, ['ID', 'Utilisateur', 'Action', 'Description', 'IP', 'Navigateur', 'Niveau', 'Date']);
        
        // Données
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['username'] ?? 'N/A',
                $log['action'],
                $log['description'],
                $log['ip_address'],
                $log['user_agent'],
                $log['level'],
                $log['created_at']
            ]);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}