<?php
namespace Auth\Model;

use PDO;
use PDOException;

abstract class Model {
    protected $db;
    protected $table;

    public function __construct($db) {
        $this->db = $db;
    }

    protected function formatResponse($data, $message = null) {
        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return $response;
    }

    protected function formatPaginatedResponse($data, $page, $limit, $total) {
        return $this->formatResponse([
            'items' => $data,
            'pagination' => [
                'page' => (int)$page,
                'limit' => (int)$limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
    }

    protected function formatErrorResponse($error) {
        return [
            'success' => false,
            'error' => $error
        ];
    }

    protected function sanitizeOutput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $data[$key] = $this->sanitizeOutput($value);
                } else {
                    // Convertir les valeurs numériques en nombres
                    if (is_numeric($value)) {
                        $data[$key] = $value + 0;
                    }
                    // Convertir les dates en format ISO 8601
                    elseif ($this->isDateTimeString($value)) {
                        $data[$key] = date('c', strtotime($value));
                    }
                }
            }
        }
        return $data;
    }

    private function isDateTimeString($string) {
        if (!is_string($string)) return false;
        $date = date_parse($string);
        return $date['error_count'] === 0 && $date['warning_count'] === 0;
    }
}
