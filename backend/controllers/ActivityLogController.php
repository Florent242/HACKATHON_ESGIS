<?php

namespace Auth\Controller;

use Auth\Model\ActivityLog;
use Auth\Model\TokenManager;
use Exception;

class ActivityLogController extends Controller
{
    private $activityLog;
    private $db;

    public function __construct($db, TokenManager $tokenManager)
    {
        parent::__construct($tokenManager);
        
        $this->db = $db;
        $this->activityLog = new ActivityLog($db);
    }

    /**
     * Enregistre une activité
     */
    protected function logActivity($action, $description, $data, $level, $ip_address, $user_agent)
    {
        $userId = $_SESSION['user_id'] ?? null;
        return $this->activityLog->log($userId, $action, $description, $data, $level);
    }

    /**
     * Récupère tous les logs avec filtres
     */
    public function getAll()
    {
        try {
            // Récupérer les paramètres de requête
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
            $action = $_GET['action'] ?? '';
            $level = $_GET['level'] ?? '';
            $userId = $_GET['user_id'] ?? '';
            $search = $_GET['search'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';

            // Construire les filtres
            $filters = [
                'limit' => $perPage,
                'offset' => ($page - 1) * $perPage
            ];

            if (!empty($action)) $filters['action'] = $action;
            if (!empty($level)) $filters['level'] = $level;
            if (!empty($userId)) $filters['user_id'] = $userId;
            if (!empty($search)) $filters['search'] = $search;
            if (!empty($dateFrom)) $filters['date_from'] = $dateFrom;
            if (!empty($dateTo)) $filters['date_to'] = $dateTo;

            // Récupérer les logs et le total
            $logs = $this->activityLog->getAll($filters);
            $total = $this->activityLog->count($filters);

            // Formater les logs
            $formattedLogs = array_map(function($log) {
                return [
                    'id' => $log['id'],
                    'user' => [
                        'id' => $log['user_id'],
                        'username' => $log['username'] ?? 'N/A',
                        'fullname' => $log['fullname'] ?? 'N/A',
                        'profile_picture' => $log['profile_picture']
                    ],
                    'action' => $log['action'],
                    'description' => $log['description'],
                    'data' => $log['data'] ? json_decode($log['data'], true) : null,
                    'ip_address' => $log['ip_address'],
                    'user_agent' => $this->parseUserAgent($log['user_agent']),
                    'level' => $log['level'],
                    'created_at' => $log['created_at'],
                    'relative_time' => $this->getRelativeTime($log['created_at'])
                ];
            }, $logs);

            $this->jsonResponse([
                'success' => true,
                'data' => $formattedLogs,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($total / $perPage)
                ]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques
     */
    public function getStats()
    {
        try {
            $stats = $this->activityLog->getStats();

            $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les actions distinctes
     */
    public function getActions()
    {
        try {
            $actions = $this->activityLog->getDistinctActions();

            $this->jsonResponse([
                'success' => true,
                'data' => $actions
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporte les logs en CSV
     */
    public function export()
    {
        try {
            // Récupérer les filtres
            $filters = [];
            if (!empty($_GET['action'])) $filters['action'] = $_GET['action'];
            if (!empty($_GET['level'])) $filters['level'] = $_GET['level'];
            if (!empty($_GET['user_id'])) $filters['user_id'] = $_GET['user_id'];
            if (!empty($_GET['search'])) $filters['search'] = $_GET['search'];
            if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
            if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];

            $csv = $this->activityLog->exportToCSV($filters);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="logs_' . date('Y-m-d_H-i-s') . '.csv"');
            echo $csv;
            exit;
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse le user agent pour extraire le navigateur
     */
    private function parseUserAgent($userAgent)
    {
        if (!$userAgent) return 'Inconnu';

        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'Vivaldi') !== false) return 'Vivaldi';
        if (strpos($userAgent, 'Yandex') !== false) return 'Yandex';
        if (strpos($userAgent, 'Brave') !== false) return 'Brave';

        return 'Autre';
    }

    /**
     * Calcule le temps relatif
     */
    private function getRelativeTime($datetime)
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) return 'Il y a quelques secondes';
        if ($diff < 3600) return 'Il y a ' . floor($diff / 60) . ' minute(s)';
        if ($diff < 86400) return 'Il y a ' . floor($diff / 3600) . ' heure(s)';
        if ($diff < 604800) return 'Il y a ' . floor($diff / 86400) . ' jour(s)';
        if ($diff < 2592000) return 'Il y a ' . floor($diff / 604800) . ' semaine(s)';
        
        return 'Il y a ' . floor($diff / 2592000) . ' mois';
    }
}