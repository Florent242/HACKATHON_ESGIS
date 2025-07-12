<?php

namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Challenge
{
    private $db;
    private $table = 'challenges';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function find($id)
    {
        try {
            $sql = "SELECT c.id,
            c.title,
            c.type,
            c.category,
            c.description,
            c.difficulty,
            c.url_path,
            c.resource_link,
            c.instructions,
            c.points,
            c.is_active,
            c.is_dynamic,
            c.created_at,
            c.updated_at,
            c.created_by,
            c.hackathon_id,
            u.username as created_by_username,
                    h.name as hackathon_titre
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    WHERE c.id = :id
                    GROUP BY c.id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $challenge = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$challenge) {
                return null;
            }

            $sqlSnippets = "SELECT * FROM snippets WHERE challenge_id = :challenge_id";
            $stmtSnippets = $this->db->prepare($sqlSnippets);
            $stmtSnippets->execute([':challenge_id' => $id]);
            $snippets = $stmtSnippets->fetchAll(PDO::FETCH_ASSOC);

            $challenge['snippets'] = $snippets;

            return $challenge;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche du challenge : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les challenges
     * @return array
     */
    public function getAll()
    {
        try {
            $sql = "SELECT c.id,
            c.title,
            c.type,
            c.category,
            c.description,
            c.difficulty,
            c.url_path,
            c.resource_link,
            c.instructions,
            c.points,
            c.is_active,
            c.is_dynamic,
            c.created_at,
            c.updated_at,
            c.created_by,
            c.hackathon_id,
            u.username as created_by_name,
                    h.name as hackathon_titre,
                    COUNT(DISTINCT p.id) as nombre_projects
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN hackathons h ON c.hackathon_id = h.id
                    LEFT JOIN projects p ON c.id = p.challenge_id
                    GROUP BY c.id
                    ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération de tous les challenges : " . $e->getMessage());
        }
    }

    /**
     * @param mixed $user_id
     * @param mixed $input
     * @throws \Exception
     * @return array{message: string, success: bool, validated_flag_id: mixed|array{message: string, success: bool, validated_flag_id: null}}
     */
    public function submitChallengeCTF($user_id, $input, $phase_id = null)
    {
        try {
            // Verifier si l'utiisateur est inscrit au hackathon
            if (!$this->isRegistered($user_id, $input['hackathon_id'])) {
                throw new Exception("L'utilisateur n'est pas inscrit au hackathon !");
            }
            $this->db->beginTransaction();

            if (!isset($input['challenge_id']) || !isset($input['flag_value']) || !isset($input['hackathon_id'])) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Flag ou challenge invalide.',
                    'validated_flag_id' => null
                ];
            }

            // Récupérer le flag et challenge
            $stmt = $this->db->prepare("SELECT * FROM flags WHERE challenge_id = :challenge_id FOR UPDATE");
            $stmt->execute([':challenge_id' => $input['challenge_id']]);
            $flag = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$flag) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Flag ou challenge invalide.',
                    'validated_flag_id' => null
                ];
            }

            // Récupérer l’équipe du joueur
            $teamStmt = $this->db->prepare("SELECT team_id FROM team_members WHERE user_id = :user_id LIMIT 1");
            $teamStmt->execute([':user_id' => $user_id]);
            $team_id = $teamStmt->fetchColumn();

            if (!$team_id) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => "Vous n'appartenez à aucune équipe.",
                    'validated_flag_id' => null
                ];
            }

            // Vérifier si un membre de l’équipe a déjà validé ce challenge
            $checkQuery = "
                SELECT vf.id 
                FROM validated_flags vf
                JOIN team_members tm ON tm.user_id = vf.user_id
                WHERE vf.challenge_id = :challenge_id
                AND tm.team_id = :team_id
                AND vf.is_valid = 1
            ";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([
                ':challenge_id' => $input['challenge_id'],
                ':team_id' => $team_id
            ]);
            if ($stmt->fetch()) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Ce flag a déjà été validé par un membre de votre équipe.',
                    'validated_flag_id' => null
                ];
            }

            // Vérification du flag
            $submittedHash = hash('sha256', $input['flag_value']);
            if ($submittedHash !== $flag['value']) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Flag incorrect.',
                    'validated_flag_id' => null
                ];
            }

            $stmt = $this->db->prepare("UPDATE flags SET solves = solves + 1 WHERE id = :flag_id");
            $stmt->execute([':flag_id' => $flag['id']]);

            // Récupère solve_count pour ce flag
            $stmt = $this->db->prepare("
                        SELECT COUNT(DISTINCT user_id)
                        FROM validated_flags
                        WHERE flag_id = :flag_id
                        AND is_valid = 1
                    ");
            $stmt->execute([':flag_id' => $flag['id']]);
            $solveCount = $stmt->fetchColumn();

            // Calcule les nouveaux points dynamiques
            $points = $this->calculateDynamicFlagPoints(
                (int)$flag['initial_points'],
                (int)$flag['min_points'],
                (int)$flag['decay'],
                (int)$solveCount
            );

            // Mise à jour des points du flag
            $stmt = $this->db->prepare("UPDATE flags SET points = :points WHERE id = :flag_id");
            $stmt->execute([
                ':points' => $points,
                ':flag_id' => $flag['id']
            ]);

            // Insertion de la validation
            $stmt = $this->db->prepare("
                INSERT INTO validated_flags (flag_id, user_id, challenge_id,points_gained, validated_at,flag_submitted, is_valid) 
                VALUES (:flag_id, :user_id, :challenge_id, :points_gained, NOW(), :flag_submitted, 1)
            ");
            $stmt->execute([
                ':flag_id' => $flag['id'],
                ':user_id' => $user_id,
                ':challenge_id' => $flag['challenge_id'],
                ':points_gained' => $points,
                ':flag_submitted' => $input['flag_value']
            ]);

            $validatedFlagId = $this->db->lastInsertId();
            if ($this->db->inTransaction()) $this->db->commit();

            // Vérifier si une ligne existe déjà
            $stmt = $this->db->prepare("
                SELECT id FROM scores 
                WHERE team_id = :team_id AND hackathon_id = :hackathon_id AND phase_id = :phase_id
            ");
            $stmt->execute([
                ':team_id' => $team_id,
                ':hackathon_id' => $input['hackathon_id'] ?? 1,
                ':phase_id' => $phase_id ?? 1
            ]);

            $scoreId = $stmt->fetchColumn();

            if ($scoreId) {
                // Update
                $stmt = $this->db->prepare("
                    UPDATE scores 
                    SET total_points = total_points + :points , last_update = NOW() 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':points' => $points,
                    ':id' => $scoreId
                ]);
            } else {
                // Insert
                $stmt = $this->db->prepare("
                    INSERT INTO scores (team_id, hackathon_id, phase_id, total_points)
                    VALUES (:team_id, :hackathon_id, :phase_id, :points)
                ");
                $stmt->execute([
                    ':team_id' => $team_id,
                    ':hackathon_id' => $input['hackathon_id'] ?? 1,
                    ':phase_id' => $phase_id ?? 1,
                    ':points' => $points
                ]);
            }


            return [
                'success' => true,
                'message' => "Flag validé avec succès ! Vous gagnez $points points.",
                'validated_flag_id' => $validatedFlagId,
                'points' => $points
            ];
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw new Exception(
                "Erreur lors de la soumission du challenge CTF !"
                // Pour debuger
                // . $e->getMessage()
            );
        }
    }

    public function calculateDynamicFlagPoints(int $initial, int $min, int $decay, int $solveCount): int
    {
        if ($decay <= 0) return $min;

        $value = (($min - $initial) / ($decay ** 2)) * ($solveCount ** 2) + $initial;
        $value = ceil($value);

        return max($value, $min);
    }

    public function getByHackathon($hackathonId)
    {
        try {
            $sql = "SELECT c.*, u.username as created_by_name,
                    COUNT(DISTINCT p.id) as nombre_projects
                    FROM {$this->table} c
                    LEFT JOIN users u ON c.created_by = u.id
                    LEFT JOIN projects p ON c.id = p.challenge_id
                    WHERE c.hackathon_id = :hackathon_id
                    GROUP BY c.id
                    ORDER BY c.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':hackathon_id' => $hackathonId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des challenges : " . $e->getMessage());
        }
    }

    public function getUserOngoingChallenges($userId, $type)
    {
        try {
            $query = "SELECT c.*
                      FROM {$this->table} c
                      INNER JOIN user_challenges uc ON c.id = uc.challenge_id
                      WHERE uc.user_id = :user_id AND c.type = :type AND uc.status = 'ongoing'"; // Adaptez 'status' si nécessaire
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            $challenges = $stmt->fetchAll();
            return $challenges;
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des défis en cours : ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère le nombre total de résolutions de challenges.
     *
     * @return int Le nombre total de résolutions.
     * @throws Exception Si une erreur de base de données survient.
     */
    public function getTotalSolvesCount(): int
    {
        try {
            $stmt = $this->db->query("
                SELECT COUNT(DISTINCT tm.team_id, vf.challenge_id) AS total_solves
                FROM validated_flags vf
                JOIN team_members tm ON vf.user_id = tm.user_id
                WHERE vf.is_valid = 1
            ");
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors du comptage total des résolutions !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    /**
     * Récupère le nombre de résolutions pour un challenge spécifique.
     *
     * @param int $challengeId L'ID du challenge.
     * @return int Le nombre de résolutions pour le challenge.
     * @throws Exception Si une erreur de base de données survient.
     */
    public function getSolvesCountByChallengeId(int $challengeId): int
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT tm.team_id) AS solve_count
                FROM validated_flags vf
                JOIN team_members tm ON vf.user_id = tm.user_id
                WHERE vf.challenge_id = :challenge_id AND vf.is_valid = 1
            ");
            $stmt->bindParam(':challenge_id', $challengeId, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors du comptage des résolutions pour le challenge $challengeId !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function getchallengeAlgo($hackathon_id, $user_id, $phase_id = null)
    {
        try {
            $sql = "SELECT 
            c.id,
            c.title,
            c.type,
            c.category,
            c.description,
            c.difficulty,
            c.url_path,
            c.points,
            c.is_active,
            c.created_at,
            c.updated_at,
            c.hackathon_id,

            -- Nombre d’équipes ayant résolu ce challenge
            (
                SELECT COUNT(DISTINCT tm.team_id)
                FROM challenge_solves cs2
                JOIN team_members tm ON tm.user_id = cs2.user_id
                WHERE cs2.challenge_id = c.id
            ) as solvers_count,

            -- Est-ce que l’équipe du user a déjà résolu ?
            EXISTS (
                SELECT 1
                FROM challenge_solves cs3
                JOIN team_members tm2 ON tm2.user_id = cs3.user_id
                WHERE cs3.challenge_id = c.id
                AND tm2.team_id = (
                    SELECT team_id FROM team_members WHERE user_id = :user_id LIMIT 1
                )
            ) as team_has_solved

        FROM 
            {$this->table} c
        WHERE 
            c.type = 'dev'
            AND c.category = 'algo'
            AND c.is_active = 1
            AND c.hackathon_id = :hackathon_id";

            if ($phase_id !== null) {
                $sql .= " AND c.phase_id = :phase_id";
            }

            $sql .= " GROUP BY c.id
                      ORDER BY c.difficulty, c.title";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':hackathon_id', $hackathon_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if ($phase_id !== null) {
                $stmt->bindParam(':phase_id', $phase_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération des challenges algorithmiques !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function getchallengeDev($hackathon_id, $team_id, $phase_id = null, $user_id = null)
    {
        try {
            // Verifier si l'utiisateur est inscrit au hackathon
            // if (!$this->isRegistered($user_id, $hackathon_id)) {
            //     throw new Exception("L'utilisateur n'est pas inscrit au hackathon !");
            // }
            $sql = "SELECT 
                c.id,
                c.title,
                c.type,
                c.category,
                c.description,
                c.difficulty,
                c.url_path,
                c.resource_link,
                c.instructions,
                c.points,
                c.is_active,
                c.is_dynamic,
                c.created_at,
                c.updated_at,
                c.created_by,
                c.hackathon_id,
    
                -- Soumission de l'équipe
                cs.id as submission_id,
                cs.status as submission_status,
                cs.points as submission_points,
                cs.submission_url,
                cs.feedback,
                cs.created_at as submitted_at,
    
                -- Technologies associées
                GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as technologies
    
            FROM 
                {$this->table} c
            LEFT JOIN challenge_technologies ct ON c.id = ct.challenge_id
            LEFT JOIN technologies t ON ct.technology_id = t.id
            LEFT JOIN challenge_submissions cs ON cs.challenge_id = c.id AND cs.team_id = :team_id
            WHERE 
                c.type = 'dev'
                AND c.is_active = 1
                AND c.hackathon_id = :hackathon_id";

            if ($phase_id !== null) {
                $sql .= " AND c.phase_id = :phase_id";
            }

            $sql .= " GROUP BY c.id
                      ORDER BY c.difficulty, c.title";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':hackathon_id', $hackathon_id, PDO::PARAM_INT);
            $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);
            if ($phase_id !== null) {
                $stmt->bindParam(':phase_id', $phase_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération des challenges Dev !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function getchallengeCtf($hackathon_id, $user_id, $phase_id = null)
    {
        try {
            // Verifier si l'utiisateur est inscrit au hackathon
            if (!$this->isRegistered($user_id, $hackathon_id)) {
                throw new Exception("L'utilisateur n'est pas inscrit au hackathon !");
            }
            $sql = "SELECT 
                c.id,
                c.title,
                c.type,
                c.category,
                c.description,
                c.difficulty,
                c.url_path,
                c.resource_link,
                c.points,
                c.is_active,
                c.is_dynamic,
                c.created_at,
                c.updated_at,
                c.created_by,
                c.hackathon_id,
                f.is_dynamic as flag_is_dynamic,
                vf.id as validation_id,
                vf.is_valid as is_validated,
                vf.validated_at as validated_at,
                (
                    SELECT COUNT(DISTINCT tm.team_id)
                    FROM validated_flags vf2
                    INNER JOIN flags f2 ON vf2.flag_id = f2.id
                    INNER JOIN team_members tm ON tm.user_id = vf2.user_id
                    WHERE f2.challenge_id = c.id
                    AND vf2.is_valid = 1
                ) as solvers_count
            FROM 
                {$this->table} c
            LEFT JOIN 
                flags f ON c.id = f.challenge_id
            LEFT JOIN 
                validated_flags vf ON f.id = vf.flag_id AND vf.user_id = :user_id
            WHERE 
                c.type = 'ctf'
                AND c.is_active = 1
                AND c.hackathon_id = :hackathon_id
            ";
            if ($phase_id !== null) {
                $sql .= " AND c.phase_id = :phase_id";
            }

            $sql .= " GROUP BY c.id
                      ORDER BY c.difficulty, c.title";


            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':hackathon_id', $hackathon_id, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if ($phase_id !== null) {
                $stmt->bindParam(':phase_id', $phase_id, PDO::PARAM_INT);
            }
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération des challenges CTF !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function isRegistered($user_id, $hackathon_id)
    {
        try {
            $sql = "SELECT * FROM hackathon_participants WHERE user_id = :user_id AND hackathon_id = :hackathon_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':hackathon_id', $hackathon_id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la vérification de l'inscription !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    /**
     * Verifier si c'est la periode de lancement des challenges
     */
    public function isChallengeLaunchPeriod(int $hackathon_id): bool
    {
        try {
            $sql = "SELECT 1 FROM hackathons WHERE id = :hackathon_id AND start_date <= NOW() AND end_date >= NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':hackathon_id', $hackathon_id, PDO::PARAM_INT);
            $stmt->execute();
            return (bool) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new Exception(
                "Erreur lors de la vérification de la période de lancement des challenges !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    /**
     * Récupère les performances de l'utilisateur
     */
    public function getUserPerformance($user_id, $hackathon_id, $phase_id = null)
    {
        try {
            $params = [
                ':user_id' => $user_id,
                ':hackathon_id' => $hackathon_id,
            ];
            $phaseCondition = '';
            if ($phase_id !== null) {
                $phaseCondition = 'AND c.phase_id = :phase_id';
                $params[':phase_id'] = $phase_id;
            }

            $sql = "
            SELECT 
                COUNT(DISTINCT c.id) AS total_challenges,
                COUNT(DISTINCT solved.challenge_id) AS total_solved_challenges,
                COALESCE(SUM(solved.points), 0) AS total_points
            FROM challenges c

            LEFT JOIN (
                -- ✅ 1. Algo / Projet / Finale : toutes soumissions acceptées
                SELECT cs.challenge_id, MAX(cs.points) AS points
                FROM challenge_submissions cs
                WHERE cs.user_id = :user_id
                AND cs.status = 'active'
                GROUP BY cs.challenge_id

                UNION

                -- ✅ 2. CTF : flags validés
                SELECT vf.challenge_id, SUM(vf.points_gained) AS points
                FROM validated_flags vf
                JOIN challenges ctf ON vf.challenge_id = ctf.id
                WHERE vf.user_id = :user_id
                AND vf.is_valid = 1
                AND ctf.type = 'ctf'
                GROUP BY vf.challenge_id
            ) AS solved ON solved.challenge_id = c.id

            WHERE c.is_active = 1
            AND c.hackathon_id = :hackathon_id
            $phaseCondition
            ";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            }
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des performances de l'utilisateur !");
        }
    }


    /**
     * Verifier si le challenge est ouvert
     * @param int $challenge_id
     * @return bool
     */
    public function isChallengeOpen(int $challenge_id): bool
    {
        try {
            $sql = "
            SELECT 1
            FROM challenges c
            JOIN hackathons h ON h.id = c.hackathon_id
            WHERE c.id = :challenge_id
            AND h.start_date <= NOW()
            AND h.end_date >= NOW()
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':challenge_id', $challenge_id, PDO::PARAM_INT);
            $stmt->execute();
            return (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la vérification de l'ouverture du challenge !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    /**
     * Verifier si la phase est active
     * @param int $hackathon_id
     * @param int $phase_id
     * @return bool
     */
    public function isPhaseActive(int $hackathon_id, int $phase_id): bool
    {
        try {
            $sql = "
        SELECT 1
        FROM phases
        WHERE hackathon_id = :hackathon_id
          AND id = :phase_id
          AND start_at <= NOW()
          AND end_at >= NOW()
    ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);
            return (bool) $stmt->fetchColumn();
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la vérification de l'activité de la phase !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }
}
