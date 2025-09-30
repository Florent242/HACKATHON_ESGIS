<?php

namespace Auth\Model;

use Exception;
use PDO;

if (!defined('FUNCTIONS_INCLUDED')) {
    require_once __DIR__ . '/../includes/functions.php';
}

class Phase {

    private $db;
    private $table = 'phases';

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère la phase active pour un utilisateur
     * @param mixed $hackathonId
     * @param mixed $userId
     * @throws \Exception
     */
    public function getActiveForUser($hackathonId, $userId) {

        try {
            $now = date('Y-m-d H:i:s');

            $sql = "
                SELECT p.*
                    FROM {$this->table} p
                    LEFT JOIN hackathon_qualifications hq 
                        ON hq.phase_id = p.id AND hq.user_id = :uid
                    WHERE p.hackathon_id = :hid
                    AND p.start <= :now
                    AND p.end >= :now
                    AND (
                        p.phase_type = 'open'
                        OR (p.phase_type = 'qualified' AND hq.user_id IS NOT NULL)
                    )
                    ORDER BY p.start ASC
                    LIMIT 1
                    ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'hid' => $hackathonId,
                'uid' => $userId,
                'now' => $now
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de la phase active !"
            // pour debug
            // . $e->getMessage()
            );
        }
    }

    /**
     * Récupère toutes les phases d'un hackathon
     * @param mixed $hackathonId
     * @throws \Exception
     */
    public function getAllForHackathon($hackathonId) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE hackathon_id = :hid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['hid' => $hackathonId]);
            $phases = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $phases;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de toutes les phases !"
            // pour debug
            // . $e->getMessage()
            );
        }
    }

    /**
     * Ajoute une nouvelle phase à un hackathon
     */
    public function addPhase($hackathonId, $data) {
        try {
            // Validation des données
            $required = ['name', 'start_date', 'end_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Le champ $field est requis");
                }
            }

            $query = "INSERT INTO hackathon_phases 
                     (hackathon_id, name, description, start_date, end_date, status) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([
                $hackathonId,
                $data['name'],
                $data['description'] ?? null,
                $data['start_date'],
                $data['end_date'],
                $data['status'] ?? 'planned'
            ]);

            if ($success) {
                $phaseId = $this->db->lastInsertId();
                return $phaseId;
            } else {
                throw new Exception("Erreur lors de l'ajout de la phase");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'ajout de la phase !"
            // pour debug
            // . $e->getMessage()
            );
        }
    }

    /**
     * Met à jour une phase existante
     */
    public function updatePhase($hackathonId, $phaseId, $data) {
        try {
            // Vérifier que la phase appartient bien au hackathon
            $checkQuery = "SELECT id FROM phases WHERE id = ? AND hackathon_id = ?";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->execute([$phaseId, $hackathonId]);
            
            if ($checkStmt->rowCount() === 0) {
                throw new Exception("Phase non trouvée pour ce hackathon");
            }

            $updates = [];
            $params = [];
            
            // Construire dynamiquement la requête avec les champs fournis
            $allowedFields = ['name', 'description', 'start', 'end', 'status'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updates)) {
                throw new Exception("Aucune donnée à mettre à jour");
            }
            
            $params[] = $phaseId;
            $params[] = $hackathonId;
            
            $query = "UPDATE phases SET " . implode(', ', $updates) . 
                    " WHERE id = ? AND hackathon_id = ?";
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute($params);
            
            return (bool)$success;
            
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de la phase !"
            // pour debug
            . $e->getMessage()
            );
        }
    }

    /**
     * Supprime une phase
     */
    public function deletePhase($hackathonId, $phaseId) {
        try {
            $query = "DELETE FROM phases WHERE id = ? AND hackathon_id = ?";
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([$phaseId, $hackathonId]);
            
            return (bool)$success;
            
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la suppression de la phase !"
            // pour debug
            // . $e->getMessage()
            );
        }
    }

    /**
     * Récupère une phase par son id
     * @param mixed $id
     * @throws \Exception
     */
    public function get($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de la phase !"
            // pour debug
            // . $e->getMessage()
            );
        }
    }

    /**
     * Vérifie si un utilisateur est qualifié pour une phase spécifique
     * @param int $userId ID de l'utilisateur
     * @param int $phaseId ID de la phase
     * @param int $hackathonId ID du hackathon
     * @return bool True si l'utilisateur est qualifié, false sinon
     * @throws \Exception En cas d'erreur lors de la vérification
     */
    public function checkQualification($userId, $phaseId, $hackathonId)
    {
        try {
            // Récupérer les informations de la phase
            $phase = $this->get($phaseId);
            if (!$phase) {
                throw new Exception("Phase non trouvée");
            }

            // Si la phase est ouverte, tout le monde est qualifié
            if ($phase['phase_type'] === 'open') {
                return true;
            }

            // Pour les phases qualifiantes, vérifier la qualification dans la table hackathon_qualifications
            $sql = "SELECT COUNT(*) FROM hackathon_qualifications 
                   WHERE user_id = :user_id 
                   AND phase_id = :phase_id
                   AND hackathon_id = :hackathon_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'phase_id' => $phaseId,
                'hackathon_id' => $hackathonId
            ]);

            return (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification de la qualification : " 
            // pour debug
            // . $e->getMessage()
            );
        }
    }
}
