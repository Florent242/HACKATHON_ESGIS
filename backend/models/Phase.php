<?php

namespace Auth\Model;

use Exception;
use PDO;

class Phase {

    private $db;
    private $table = 'phases';

    public function __construct($db) {
        $this->db = $db;
    }

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
            . $e->getMessage()
            );
        }
    }
}
