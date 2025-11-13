<?php

namespace Auth\Model;

use Exception;
use PDO;

class Score
{
    private $db;
    private $table = 'scores';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getLeaderboard($hackathon_id, $phase_id)
    {
        try {
            $sql = "
            SELECT 
                t.id AS team_id,
                t.name,
                s.total_points AS points,
                (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) AS members,
                COALESCE(MAX(vf.validated_at), MAX(cs.submitted_at)) AS lastSubmission
            FROM scores s
            JOIN teams t ON t.id = s.team_id
            LEFT JOIN validated_flags vf 
                ON vf.challenge_id IN (
                    SELECT id FROM challenges WHERE hackathon_id = s.hackathon_id AND type = 'ctf'
                )
                AND vf.user_id IN (
                    SELECT user_id FROM team_members WHERE team_id = t.id
                )
            LEFT JOIN challenge_submissions cs 
                ON cs.challenge_id IN ( 
                    SELECT id FROM challenges WHERE hackathon_id = s.hackathon_id AND type = 'dev'
                )
                AND cs.user_id IN (
                    SELECT user_id FROM team_members WHERE team_id = t.id
                )
            WHERE s.hackathon_id = :hackathon_id
              AND s.phase_id = :phase_id
              AND s.is_active = 1
            GROUP BY t.id
            ORDER BY points DESC, lastSubmission ASC
            LIMIT 100   
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération du leaderboard !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function getPhases($hackathon_id)
    {
        try {
            $sql = "
                SELECT 
                    *,
                    CASE 
                    WHEN frozen_leaderboard = 1 THEN 'frozen' 
                    WHEN start <= NOW() AND end >= NOW() THEN 'active' 
                    WHEN end < NOW() THEN 'ended' 
                    ELSE 'inactive' 
                END as status
                FROM phases
                WHERE hackathon_id = :hackathon_id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathon_id
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération des phases !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    // TODO : Instruction interdite aux participants
    public function updateScore($team_id, $hackathon_id, $phase_id, $points)
    {
        try {
            // Vérifie si une ligne existe
            $stmt = $this->db->prepare("
            SELECT id FROM scores 
            WHERE team_id = :team_id AND hackathon_id = :hackathon_id AND phase_id = :phase_id
        ");
            $stmt->execute([
                ':team_id' => $team_id,
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);

            $scoreId = $stmt->fetchColumn();

            if ($scoreId) {
                // Mise à jour
                $stmt = $this->db->prepare("
                UPDATE scores 
                SET total_points = total_points + :points 
                WHERE id = :id
            ");
                $stmt->execute([
                    ':points' => $points,
                    ':id' => $scoreId
                ]);
            } else {
                // Insertion
                $stmt = $this->db->prepare("
                INSERT INTO scores (team_id, hackathon_id, phase_id, total_points)
                VALUES (:team_id, :hackathon_id, :phase_id, :points)
            ");
                $stmt->execute([
                    ':team_id' => $team_id,
                    ':hackathon_id' => $hackathon_id,
                    ':phase_id' => $phase_id,
                    ':points' => $points
                ]);
            }
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la mise à jour du score !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function freezePhase($hackathon_id, $phase_id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE phases SET frozen_leaderboard = 1 WHERE hackathon_id = :hackathon_id AND id = :phase_id");
            $stmt->execute([
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la freeze de la phase !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function unfreezePhase($hackathon_id, $phase_id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE phases SET frozen_leaderboard = 0 WHERE hackathon_id = :hackathon_id AND id = :phase_id");
            $stmt->execute([
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la unfreeze de la phase !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

public function qualifyTeams($hackathon_id, $current_phase_id) {
    try {
        $this->db->beginTransaction();
        // 1. Vérifier si la qualification a déjà été effectuée
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM hackathon_qualifications 
            WHERE hackathon_id = :hackathon_id 
            AND phase_id = :phase_id
        ");
        $stmt->execute([
            ':hackathon_id' => $hackathon_id,
            ':phase_id' => $current_phase_id
        ]);
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            return [
                'success' => false,
                'message' => 'La qualification pour cette phase a déjà été effectuée.'
            ];
        }

        // 2. Récupérer les informations de la phase actuelle
        $stmt = $this->db->prepare("
            SELECT * FROM phases 
            WHERE hackathon_id = :hackathon_id 
            AND id = :phase_id
        ");
        $stmt->execute([
            ':hackathon_id' => $hackathon_id,
            ':phase_id' => $current_phase_id
        ]);
        $current_phase = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$current_phase) {
            return ['success' => false, 'message' => 'Phase non trouvée.'];
        }

        // 3. Récupérer la phase suivante
        $stmt = $this->db->prepare("
            SELECT * FROM phases 
            WHERE hackathon_id = :hackathon_id 
            AND phase_order > :current_order
            ORDER BY phase_order ASC
            LIMIT 1
        ");
        $stmt->execute([
            ':hackathon_id' => $hackathon_id,
            ':current_order' => $current_phase['phase_order']
        ]);
        $next_phase = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$next_phase) {
            return ['success' => false, 'message' => 'Aucune phase suivante trouvée.'];
        }

        // 4. Récupérer les meilleures équipes avec leurs scores
        $stmt = $this->db->prepare("
            SELECT 
                s.team_id,
                t.name as team_name,
                s.total_points as total_score
            FROM scores s
            JOIN teams t ON s.team_id = t.id
            WHERE s.hackathon_id = :hackathon_id
            AND s.phase_id = :phase_id
            AND s.is_active = 1
            ORDER BY s.total_points DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':hackathon_id', $hackathon_id, PDO::PARAM_INT);
        $stmt->bindValue(':phase_id', $current_phase_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $current_phase['teams_qualified'], PDO::PARAM_INT);
        $stmt->execute();
        
        $qualified_teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($qualified_teams)) {
            return ['success' => false, 'message' => 'Aucune équipe éligible pour la qualification.'];
        }

        // 5. Enregistrer les qualifications
        $insertStmt = $this->db->prepare("
            INSERT INTO hackathon_qualifications 
            (phase_id, hackathon_id, team_id, score, qualified_by)
            VALUES 
            (:phase_id, :hackathon_id, :team_id, :score, 'system')
        ");

        $updateTeamStmt = $this->db->prepare("
            UPDATE teams 
            SET
                updated_at = NOW()
            WHERE id = :team_id
        ");

        $this->db->beginTransaction();
        
        $qualified_team_ids = [];
        foreach ($qualified_teams as $team) {
            // Enregistrer la qualification
            $insertStmt->execute([
                ':phase_id' => $current_phase_id,
                ':hackathon_id' => $hackathon_id,
                ':team_id' => $team['team_id'],
                ':score' => $team['total_score']
            ]);

            // Mettre à jour l'équipe pour la phase suivante
            $updateTeamStmt->execute([
                ':team_id' => $team['team_id']
            ]);

            $qualified_team_ids[] = $team['team_id'];
        }

        // 6. Désactiver les scores des équipes non qualifiées - Attention risque de ne plus voir les equipes affichees dans le leaderboard
        // $deactivateStmt = $this->db->prepare("
        //     UPDATE scores 
        //     SET is_active = 0
        //     WHERE hackathon_id = :hackathon_id
        //     AND phase_id = :phase_id
        //     AND team_id NOT IN (" . implode(',', array_fill(0, count($qualified_team_ids), '?')) . ")
        // ");
        
        // $params = array_merge([$hackathon_id, $current_phase_id], $qualified_team_ids);
        // $deactivateStmt->execute($params);

        $this->db->commit();

        return [
            'success' => true,
            'message' => count($qualified_teams) . ' équipes ont été qualifiées pour la phase suivante.',
            'qualified_teams' => $qualified_teams
        ];

    } catch (Exception $e) {
        $this->db->rollBack();
        return [
            'success' => false,
            'message' => 'Erreur lors de la qualification des équipes: ' . $e->getMessage()
        ];
    }
}
}
